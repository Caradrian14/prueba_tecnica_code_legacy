<?php

//llamamos a nuestra base de datos 
$PRODUCTOS_DB = require __DIR__ . "/datos/productos.php";
$COUPONS = require __DIR__ . "/datos/cupones.php";

//ahora pasariamos a la logica de negocio
require __DIR__ . '/src/Cupones/Cupon.php';
require __DIR__ . '/src/Cupones/CuponDescuento1Euro.php';
require __DIR__ . '/src/calculadora_carrito/calculadora.php';
//y demas require

use respuesta_herencia\src\calculadora_carrito\Calculadora; // Importar el namespace

//creamos el carrito y agregamos el producto de la prueba
$cart3 = [['sku' => 'PLT', 'quantity' => 4], ['sku' => 'MAN', 'quantity' => 2]];
$coupons3 = [];
$total3 = new Calculadora($cart3, $coupons3);
echo "Escenario 3 (BOGO + Volumen BUG): " . $total3 . " (Esperado: 9.20)\n";
//el carrito ha de aceptar el array que le mandamos

