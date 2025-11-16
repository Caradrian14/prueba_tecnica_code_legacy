<?php

//llamamos a nuestra base de datos 
$PRODUCTOS_DB = require __DIR__ . "/datos/productos.php";
$COUPONS = require __DIR__ . "/datos/cupones.php";

//ahora pasariamos a la logica de negocio
require __DIR__ . '/src/Cupones/Cupon.php';
require __DIR__ . '/src/Cupones/CuponDescuento1Euro.php';
//y demas require

//creamos el carrito y agregamos el producto de la prueba
$carrito = new Calcuadora();
//el carrito ha de aceptar el array que le mandamos

