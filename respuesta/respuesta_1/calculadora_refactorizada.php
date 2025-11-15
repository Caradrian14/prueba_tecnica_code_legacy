<?php

/**
* calculadora_refactorizada
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
// --- Fin Base de Datos Simulada ---