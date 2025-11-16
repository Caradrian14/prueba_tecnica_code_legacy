<?php

namespace respuesta_herencia\src\cupones;

//Aqui ira logica del negocio 
//como si fuera el "calculate_legacy_price" del script legalicy

class Calculadora {
    private $carrito;
    private $cupones;

    public function __construct($carrito, $cupones) {
        $this->carrito = $carrito;
        $this->cupones = $cupones;
    }


    // Vamos a empezar con que las reglas de negocio son funciones, una vez ya funcional las pasamos a objetos.
    
}