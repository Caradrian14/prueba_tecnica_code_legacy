<?php

//ahora pasariamos a la logica de negocio
require_once __DIR__ . '/src/Cupones/Cupon.php';
require_once __DIR__ . '/src/Productos/Producto.php';
require_once __DIR__ . '/src/Calculadora_carrito/CalculadoraCarrito.php';
require_once __DIR__ . '/src/Factory/ProductoFactory.php';
require_once __DIR__ . '/src/Factory/CuponFactory.php';
require_once __DIR__ . '/src/Rules\ReglaBogo.php';
require_once __DIR__ . '/src/Rules\ReglaDescuentoVolumen.php';
require_once __DIR__ . '/src/Rules\ReglaCostesEnvio.php';
require_once __DIR__ . '/src/Rules\ReglaCupones.php';
require_once __DIR__ . '/src/Cupones\CuponDescuento1Euro.php';
require_once __DIR__ . '/src/Cupones\CuponDescuento2Euro.php';
require_once __DIR__ . '/src/Cupones\CuponDescuento10Euro.php';
require_once __DIR__ . '/src/Cupones\CuponDescuentoBlackFriday.php';
use respuesta_herencia\src\Calculadora_carrito\Calculadora;

$cart1 = [['sku' => 'PLT', 'quantity' => 3]];
$coupons1 = [];
$carrito1 = new Calculadora($cart1, $coupons1);
$total1 = $carrito1->inicio_carrito();
echo "Escenario 1 (BOGO): \t\t" . $total1 . " (Esperado: 8.00)\n <br>";

$cart2 = [['sku' => 'MAN', 'quantity' => 3], ['sku' => 'AGU', 'quantity' => 3]];
$coupons2 = [];
$carrito2 = new Calculadora($cart2, $coupons2);
$total2 = $carrito2->inicio_carrito();
echo "Escenario 2 (Volumen): \t\t" . $total2 . " (Esperado: 13.10)\n <br>";

$cart3 = [['sku' => 'PLT', 'quantity' => 4], ['sku' => 'MAN', 'quantity' => 2]];
$coupons3 = [];
$carrito3 = new Calculadora($cart3, $coupons3);
$total3 = $carrito3->inicio_carrito(); 
echo "Escenario 3 (BOGO + Volumen BUG): " . $total3 . " (Esperado: 9.20)\n <br>";

$cart4 = [['sku' => 'MAN', 'quantity' => 2]];
$coupons4 = ['FREESHIPPING'];
$carrito4 = new Calculadora($cart4, $coupons4);
$total4 = $carrito4->inicio_carrito(); 
echo "Escenario 4 (Cupón Envío): \t" . $total4 . " (Esperado: 2.00)\n <br>";

$cart5 = [['sku' => 'AGU', 'quantity' => 30]];
$coupons5 = ['FREESHIPPING'];
$carrito5 = new Calculadora($cart5, $coupons5);
$total5 = $carrito5->inicio_carrito(); 
echo "Escenario 5 (Envío Gratis > 50): \t" . $total5 . " (Esperado: 54.00)\n <br>";
//el carrito ha de aceptar el array que le mandamos

