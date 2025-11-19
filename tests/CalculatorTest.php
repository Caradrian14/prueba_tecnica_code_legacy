<?php
use PHPUnit\Framework\TestCase;
use respuesta_herencia\src\Calculadora_carrito\CartCalculator;
use respuesta_herencia\src\Productos\Producto;
use respuesta_herencia\src\Cupones\Cupon;
use Datetime;
use respuesta_herencia\src\Factory\ProductoFactory;
use respuesta_herencia\src\Factory\CuponFactory;
use respuesta_herencia\src\Rules\ReglaBogo;
use respuesta_herencia\src\Rules\ReglaDescuentoVolumen;
use respuesta_herencia\src\Rules\ReglaCostesEnvio;
use respuesta_herencia\src\Rules\ReglaCupones;
require __DIR__ . '/../vendor/autoload.php';

class CalculatorTest extends TestCase
{
    public function test1()
    {
        $cart1 = [['sku' => 'PLT', 'quantity' => 3]];
        $coupons1 = [];
        $cart_object1 = new CartCalculator($cart1, $coupons1);
        $result = $cart_object1->start_calculator_cart();
        $this->assertEquals(8.00, $result);
    }
}
