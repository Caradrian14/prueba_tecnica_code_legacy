<?php
 
/**
* legacy_calculator.php
*
* El objetivo de la prueba es refactorizar este código.
*/
 
// --- Base de Datos Simulada ---
// Productos disponibles en la tienda.
$PRODUCTOS_DB = [
    'PLT' => ['name' => 'Plátano', 'price' => 1.50, 'tags' => ['BOGO']],
    'MAN' => ['name' => 'Manzana', 'price' => 1.00, 'tags' => []],
    'AGU' => ['name' => 'Aguacate', 'price' => 2.00, 'tags' => []],
    'FRM' => ['name' => 'Frambuesas', 'price' => 4.50, 'tags' => ['BOGO']],
];

// Cupones
$COUPONS = [
    'FREESHIPPING' => ['2025-03-10', '2030-08-10'],
    'BLACKFRIDAY' => ['2025-11-20', '2025-11-30'],
    '1EUROS' => ['2025-01-01', '2025-12-31'],
    '2EUROS' => ['2025-01-01', '2025-12-31'],
    '10EUROS' => ['2025-01-01', '2025-12-31'],
];

/**
* Función principal que calcula el precio total del carrito.
* Contiene toda la lógica de negocio mezclada.
*
* @param array $cart_items Array de productos en el carrito.
* Ej: [['sku' => 'PLT', 'quantity' => 3], ['sku' => 'MAN', 'quantity' => 2]]
* @param array $coupons Array de strings de códigos de cupón.
* Ej: ['FREESHIPPING']
* @return float El precio final redondeado a 2 decimales.
*/
function calculate_legacy_price(array $cart_items, array $coupons): float
{
    global $PRODUCTOS_DB, $COUPONS;
 
    $raw_subtotal = 0;   // Subtotal antes de CUALQUIER descuento
    $total_items = 0;    // Cantidad total de items
    $bogo_discount = 0;  // Descuento acumulado por BOGO
 
    // 1. Calcular subtotal bruto, total de items y descuento BOGO
    foreach ($cart_items as $item) {
        $sku = $item['sku'];
        $quantity = $item['quantity'];
 
        if (!isset($PRODUCTOS_DB[$sku])) {
            continue; // Ignorar productos que no existen
        }
 
        $product_data = $PRODUCTOS_DB[$sku];
        $price = $product_data['price'];
 
        // Acumular subtotal bruto y total de items
        $raw_subtotal += $quantity * $price;
        $total_items += $quantity;
 
        // Regla 1 (BOGO): "Buy One, Get One Free"
        if (in_array('BOGO', $product_data['tags'])) {
            // Por cada 2, 1 es gratis.
            $free_items = floor($quantity / 2);
            $bogo_discount += $free_items * $price;
        }
    }
 
    $subtotal_after_bogo = $raw_subtotal - $bogo_discount;
 
    // 2. Aplicar Descuento por Volumen (Regla 2)
    $volume_discount = 0;
    if ($total_items >= 5) {
        $volume_discount = $raw_subtotal * 0.10;
    }
 
    // Aplicamos el descuento por volumen al subtotal que ya tenía BOGO
    $final_subtotal = $subtotal_after_bogo - $volume_discount;
 
    $has_1euros_coupon = in_array('1EUROS', $coupons);
    $has_2euros_coupon = in_array('2EUROS', $coupons);
    $has_10euros_coupon = in_array('10EUROS', $coupons);
    $has_blackfriday_coupon = in_array('BLACKFRIDAY', $coupons);

    if($has_1euros_coupon){
        $final_subtotal -= 1.00;
    } else if($has_2euros_coupon){
        $final_subtotal -= 2.00;
    } else if($has_10euros_coupon){
        $final_subtotal -= 10.00;
    } else if($has_blackfriday_coupon && date('Y-m-d') >= '2025-11-20' && date('Y-m-d') <= '2025-11-30'){
        // Aplicar 20% de descuento adicional en Black Friday
        $final_subtotal *= 0.80;
    }

    // 3. Calcular Coste de Envío (Regla 3)
    $shipping_cost = 0;

    if ($final_subtotal < 50.00) {
        $has_freeshipping_coupon = in_array('FREESHIPPING', $coupons);
        if ($has_freeshipping_coupon) {
            $shipping_cost = 0; // Cupón aplica envío gratis
        } else {
            $shipping_cost = 5.00; // Envío estándar
        }
    } else {
        // El envío ya es gratis por superar 50.00
        // El cupón 'FREESHIPPING' no hace nada en este caso.
        $shipping_cost = 0;
    }
 
    $total_price = $final_subtotal + $shipping_cost;
 
    return round($total_price, 2);
}
 
 
// ==================================================================
// --- EJECUCIÓN DE PRUEBA ---
// Puedes ejecutar este script con `php legacy_calculator.php`
// ==================================================================
 
   
    echo "--- Ejecutando Pruebas de Lógica Legacy --- \n\n";
 
    // Escenario 1: BOGO (3 Plátanos -> paga 2)
    // 3 x 1.50 = 4.50. BOGO da 1 gratis (-1.50).
    // Raw Subtotal = 4.50. Subtotal BOGO = 3.00.
    // Items = 3 (< 5), sin descuento volumen.
    // Final Subtotal = 3.00.
    // Envío = 5.00 (porque 3.00 < 50.00).
    // Total = 8.00
    $cart1 = [['sku' => 'PLT', 'quantity' => 3]];
    $coupons1 = [];
    $total1 = calculate_legacy_price($cart1, $coupons1);
    echo "Escenario 1 (BOGO): \t\t" . $total1 . " (Esperado: 8.00)\n";
   
    // Escenario 2: Descuento Volumen (SIN BOGO)
    // 3 Manzanas (3.00) + 3 Aguacates (6.00)
    // Raw Subtotal = 9.00. Subtotal BOGO = 9.00.
    // Items = 6 (>= 5), 10% descuento volumen.
    // BUG: 10% de Raw Subtotal (9.00) = 0.90.
    // Final Subtotal = 9.00 - 0.90 = 8.10.
    // Envío = 5.00 (porque 8.10 < 50.00).
    // Total = 13.10
    $cart2 = [['sku' => 'MAN', 'quantity' => 3], ['sku' => 'AGU', 'quantity' => 3]];
    $coupons2 = [];
    $total2 = calculate_legacy_price($cart2, $coupons2);
    echo "Escenario 2 (Volumen): \t\t" . $total2 . " (Esperado: 13.10)\n";
 
    // Escenario 3: BOGO + Volumen (¡EL BUG!)
    // 4 Plátanos (BOGO) (6.00) + 2 Manzanas (2.00)
    // Raw Subtotal = 8.00
    // Items = 6 (>= 5)
    // BOGO: 4 Plátanos -> 2 gratis (-3.00)
    // Subtotal BOGO = 8.00 - 3.00 = 5.00
    // Descuento Volumen (BUG): 10% de *Raw Subtotal* (8.00) = 0.80
    // Final Subtotal = 5.00 - 0.80 = 4.20
    // Envío = 5.00 (porque 4.20 < 50.00)
    // Total = 9.20
    $cart3 = [['sku' => 'PLT', 'quantity' => 4], ['sku' => 'MAN', 'quantity' => 2]];
    $coupons3 = [];
    $total3 = calculate_legacy_price($cart3, $coupons3);
    echo "Escenario 3 (BOGO + Volumen BUG): " . $total3 . " (Esperado: 9.20)\n";
 
    // Escenario 4: Cupón Envío
    // 2 Manzanas = 2.00
    // Raw Subtotal = 2.00. Subtotal BOGO = 2.00.
    // Items = 2 (< 5), sin descuento volumen.
    // Final Subtotal = 2.00.
    // Envío = 0.00 (porque 2.00 < 50.00 y tiene cupón 'FREESHIPPING').
    // Total = 2.00
    $cart4 = [['sku' => 'MAN', 'quantity' => 2]];
    $coupons4 = ['FREESHIPPING'];
    $total4 = calculate_legacy_price($cart4, $coupons4);
    echo "Escenario 4 (Cupón Envío): \t" . $total4 . " (Esperado: 2.00)\n";
 
    // Escenario 5: Envío Gratis (Supera 50) + Cupón (no hace nada)
    // 30 Aguacates = 60.00
    // Raw Subtotal = 60.00. Subtotal BOGO = 60.00.
    // Items = 30 (>= 5), 10% descuento volumen.
    // BUG: 10% de 60.00 = 6.00
    // Final Subtotal = 60.00 - 6.00 = 54.00
    // Envío = 0.00 (porque 54.00 >= 50.00). El cupón es irrelevante.
    // Total = 54.00
    $cart5 = [['sku' => 'AGU', 'quantity' => 30]];
    $coupons5 = ['FREESHIPPING'];
    $total5 = calculate_legacy_price($cart5, $coupons5);
    echo "Escenario 5 (Envío Gratis > 50): \t" . $total5 . " (Esperado: 54.00)\n";
 
    echo "\n--- Fin de las Pruebas ---\n";
