<?php
declare(strict_types=1);

/**
 * refactor_calculator.php
 *
 * Refactorización orientada a OOP del script legacy_calculator.php
 * - Corrige el bug del descuento por volumen (se aplica sobre el subtotal tras BOGO)
 * - Separa responsabilidades en clases y reglas de precio
 * - Elimina variables globales
 * - Añade documentación y ejemplo de uso
 *
 * Requisitos: PHP 8.0+
 */

namespace Store;

use DateTimeImmutable;

// --------------------------
// Domain objects
// --------------------------

final class Product
{
    public function __construct(
        private string $sku,
        private string $name,
        private float $price,
        private array $tags = []
    ) {}

    public function getSku(): string { return $this->sku; }
    public function getName(): string { return $this->name; }
    public function getPrice(): float { return $this->price; }
    public function hasTag(string $tag): bool { return in_array($tag, $this->tags, true); }
}

final class ProductCatalog
{
    /** @var array<string, Product> */
    private array $products = [];

    public function addProduct(Product $product): void
    {
        $this->products[$product->getSku()] = $product;
    }

    public function get(string $sku): ?Product
    {
        return $this->products[$sku] ?? null;
    }
}

final class CartItem
{
    public function __construct(private Product $product, private int $quantity) {}

    public function getProduct(): Product { return $this->product; }
    public function getQuantity(): int { return $this->quantity; }
    public function getSubtotal(): float { return $this->product->getPrice() * $this->quantity; }
}

final class Cart
{
    /** @var CartItem[] */
    private array $items = [];

    public function addItem(Product $product, int $quantity): void
    {
        if ($quantity <= 0) {
            return;
        }

        $this->items[] = new CartItem($product, $quantity);
    }

    /** @return CartItem[] */
    public function getItems(): array { return $this->items; }

    public function totalItems(): int
    {
        $sum = 0;
        foreach ($this->items as $item) {
            $sum += $item->getQuantity();
        }
        return $sum;
    }

    public function rawSubtotal(): float
    {
        $sum = 0.0;
        foreach ($this->items as $item) {
            $sum += $item->getSubtotal();
        }
        return $sum;
    }
}

// --------------------------
// Coupons
// --------------------------

final class Coupon
{
    public function __construct(private string $code, private DateTimeImmutable $validFrom, private DateTimeImmutable $validTo) {}

    public function getCode(): string { return $this->code; }

    public function isValidOn(DateTimeImmutable $date): bool
    {
        return $date >= $this->validFrom && $date <= $this->validTo;
    }
}

final class CouponRepository
{
    /** @var array<string, Coupon> */
    private array $coupons = [];

    public function add(Coupon $coupon): void
    {
        $this->coupons[$coupon->getCode()] = $coupon;
    }

    public function existsAndValid(string $code, DateTimeImmutable $date): bool
    {
        if (!isset($this->coupons[$code])) {
            return false;
        }
        return $this->coupons[$code]->isValidOn($date);
    }
}

// --------------------------
// Pricing rules (Strategy)
// --------------------------

interface PricingRule
{
    /**
     * Aplica la regla y devuelve el nuevo subtotal (no incluye envío).
     * La regla recibe el subtotal actual y el carrito completo para poder calcular impactos complejos.
     */
    public function apply(float $currentSubtotal, Cart $cart): float;
}

/**
 * Regla BOGO: "Buy One Get One Free" para productos con tag 'BOGO'.
 * Implementación: por cada 2 unidades de un SKU con BOGO, una es gratis.
 */
final class BogoRule implements PricingRule
{
    public function apply(float $currentSubtotal, Cart $cart): float
    {
        $discount = 0.0;
        foreach ($cart->getItems() as $item) {
            $product = $item->getProduct();
            if (!$product->hasTag('BOGO')) {
                continue;
            }
            $free = intdiv($item->getQuantity(), 2); // floor(quantity / 2)
            $discount += $free * $product->getPrice();
        }
        return max(0.0, $currentSubtotal - $discount);
    }
}

/**
 * Discounto por volumen: si total items >= 5, aplicar X% de descuento.
 * Nota: se calcula sobre el subtotal que llega a esta regla (CORRECCIÓN del bug).
 */
final class VolumeDiscountRule implements PricingRule
{
    private float $thresholdItems;
    private float $rate;

    public function __construct(float $thresholdItems = 5, float $rate = 0.10)
    {
        $this->thresholdItems = $thresholdItems;
        $this->rate = $rate;
    }

    public function apply(float $currentSubtotal, Cart $cart): float
    {
        if ($cart->totalItems() >= $this->thresholdItems) {
            return $currentSubtotal * (1 - $this->rate);
        }
        return $currentSubtotal;
    }
}

/**
 * Regla de cupones: aplica cupones de valor fijo (1,2,10 euros) o BlackFriday (20%).
 * Reglas de negocio reproducidas (pero con comportamiento más consistente):
 * - Si hay varios cupones de valor fijo, se aplica el de mayor valor.
 * - Si existe BLACKFRIDAY válido, aplica 20% adicional (multiplicativo después de descuentos fijos).
 */
final class CouponRule implements PricingRule
{
    private CouponRepository $repo;
    private array $appliedCodes;
    private DateTimeImmutable $today;

    /**
     * @param CouponRepository $repo Repositorio para validar validez de cupones
     * @param array $appliedCodes Lista de códigos solicitados por el usuario
     * @param DateTimeImmutable|null $today Fecha de referencia (útil para tests)
     */
    public function __construct(CouponRepository $repo, array $appliedCodes, ?DateTimeImmutable $today = null)
    {
        $this->repo = $repo;
        $this->appliedCodes = $appliedCodes;
        $this->today = $today ?? new DateTimeImmutable('now');
    }

    public function apply(float $currentSubtotal, Cart $cart): float
    {
        // 1) Determinar el mayor cupón de valor fijo disponible y válido
        $fixedCoupons = ['1EUROS' => 1.0, '2EUROS' => 2.0, '10EUROS' => 10.0];
        $bestFixed = 0.0;
        foreach ($fixedCoupons as $code => $value) {
            if (in_array($code, $this->appliedCodes, true) && $this->repo->existsAndValid($code, $this->today)) {
                if ($value > $bestFixed) {
                    $bestFixed = $value;
                }
            }
        }

        $subtotalAfterFixed = max(0.0, $currentSubtotal - $bestFixed);

        // 2) Black Friday: si aplicado y válido, aplicar 20% sobre el subtotal resultante
        if (in_array('BLACKFRIDAY', $this->appliedCodes, true) && $this->repo->existsAndValid('BLACKFRIDAY', $this->today)) {
            $subtotalAfterFixed *= 0.80; // -20%
        }

        return $subtotalAfterFixed;
    }
}

// --------------------------
// Shipping rule (separada porque no modifica subtotal)
// --------------------------

final class ShippingCalculator
{
    private CouponRepository $repo;
    private array $appliedCodes;
    private DateTimeImmutable $today;

    public function __construct(CouponRepository $repo, array $appliedCodes, ?DateTimeImmutable $today = null)
    {
        $this->repo = $repo;
        $this->appliedCodes = $appliedCodes;
        $this->today = $today ?? new DateTimeImmutable('now');
    }

    /**
     * Calcula coste de envío según reglas:
     * - Si subtotal >= 50 => envío gratis
     * - Si cupón FREESHIPPING válido => envío gratis
     * - En otro caso => 5.00
     */
    public function calculate(float $subtotal): float
    {
        if ($subtotal >= 50.0) {
            return 0.0;
        }

        if (in_array('FREESHIPPING', $this->appliedCodes, true) && $this->repo->existsAndValid('FREESHIPPING', $this->today)) {
            return 0.0;
        }

        return 5.0;
    }
}

// --------------------------
// Calculator orchestrator
// --------------------------

final class PriceCalculator
{
    /** @var PricingRule[] */
    private array $rules = [];
    private ShippingCalculator $shippingCalculator;

    public function __construct(array $rules, ShippingCalculator $shippingCalculator)
    {
        $this->rules = $rules;
        $this->shippingCalculator = $shippingCalculator;
    }

    public function calculateTotal(Cart $cart): float
    {
        // Aplicar reglas de forma secuencial sobre el subtotal (inmutabilidad por valor flotante)
        $subtotal = $cart->rawSubtotal();

        foreach ($this->rules as $rule) {
            $subtotal = $rule->apply($subtotal, $cart);
        }

        $shipping = $this->shippingCalculator->calculate($subtotal);

        return round(max(0.0, $subtotal + $shipping), 2);
    }
}

// --------------------------
// Factory helper para crear un entorno similar al del script original
// --------------------------

function buildDefaultCatalog(): ProductCatalog
{
    $catalog = new ProductCatalog();

    $catalog->addProduct(new Product('PLT', 'Plátano', 1.50, ['BOGO']));
    $catalog->addProduct(new Product('MAN', 'Manzana', 1.00, []));
    $catalog->addProduct(new Product('AGU', 'Aguacate', 2.00, []));
    $catalog->addProduct(new Product('FRM', 'Frambuesas', 4.50, ['BOGO']));

    return $catalog;
}

function buildDefaultCoupons(): CouponRepository
{
    $repo = new CouponRepository();

    // Using DateTimeImmutable for ranges
    $repo->add(new Coupon('FREESHIPPING', new DateTimeImmutable('2025-03-10'), new DateTimeImmutable('2030-08-10')));
    $repo->add(new Coupon('BLACKFRIDAY', new DateTimeImmutable('2025-11-20'), new DateTimeImmutable('2025-11-30')));
    $repo->add(new Coupon('1EUROS', new DateTimeImmutable('2025-01-01'), new DateTimeImmutable('2025-12-31')));
    $repo->add(new Coupon('2EUROS', new DateTimeImmutable('2025-01-01'), new DateTimeImmutable('2025-12-31')));
    $repo->add(new Coupon('10EUROS', new DateTimeImmutable('2025-01-01'), new DateTimeImmutable('2025-12-31')));

    return $repo;
}

// --------------------------
// Example usage & equivalence with los escenarios de prueba
// --------------------------
echo "--- Ejecutando Pruebas OOP Refactor ---\n\n";

$catalog = buildDefaultCatalog();
$couponRepo = buildDefaultCoupons();

$today = new DateTimeImmutable('now');

// Helper to build calculator with given applied coupons
$makeCalculator = function(array $appliedCodes) use ($couponRepo, $today) : PriceCalculator {
    $rules = [
        new BogoRule(),
        new VolumeDiscountRule(),
        new CouponRule($couponRepo, $appliedCodes, $today),
    ];
    $shipping = new ShippingCalculator($couponRepo, $appliedCodes, $today);
    return new PriceCalculator($rules, $shipping);
};

// Escenario 1: BOGO (3 Plátanos -> paga 2) => Total esperado 8.00
$cart1 = new Cart();
$cart1->addItem($catalog->get('PLT'), 3);
$calc1 = $makeCalculator([]);
echo "Escenario 1 (BOGO):\t\t" . $calc1->calculateTotal($cart1) . " (Esperado: 8.00)\n <br>";

// Escenario 2: Volumen (3 MAN + 3 AGU) => Total esperado 13.10
$cart2 = new Cart();
$cart2->addItem($catalog->get('MAN'), 3);
$cart2->addItem($catalog->get('AGU'), 3);
$calc2 = $makeCalculator([]);
echo "Escenario 2 (Volumen):\t\t" . $calc2->calculateTotal($cart2) . " (Esperado: 13.10)\n<br>";

// Escenario 3: BOGO + Volumen (4 PLT + 2 MAN) => Esperado 9.20
$cart3 = new Cart();
$cart3->addItem($catalog->get('PLT'), 4);
$cart3->addItem($catalog->get('MAN'), 2);
$calc3 = $makeCalculator([]);
echo "Escenario 3 (BOGO + Volumen):\t" . $calc3->calculateTotal($cart3) . " (Esperado: 9.20)\n<br>";

// Escenario 4: Cupón Envío (2 MAN, FREESHIPPING) => 2.00
$cart4 = new Cart();
$cart4->addItem($catalog->get('MAN'), 2);
$calc4 = $makeCalculator(['FREESHIPPING']);
echo "Escenario 4 (Cupón Envío):\t" . $calc4->calculateTotal($cart4) . " (Esperado: 2.00)\n<br>";

// Escenario 5: Envío Gratis (>50) + Cupón irrelevante (30 AGU)
$cart5 = new Cart();
$cart5->addItem($catalog->get('AGU'), 30);
$calc5 = $makeCalculator(['FREESHIPPING']);
echo "Escenario 5 (Envío Gratis > 50):\t" . $calc5->calculateTotal($cart5) . " (Esperado: 54.00)\n<br>";

echo "\n--- Fin de las Pruebas ---\n";
