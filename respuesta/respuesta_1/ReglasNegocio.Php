<?php
namespace prueba_tecnica_romeu\respuesta\respuesta1\ReglasNegocio;
class ReglasNegocio {
    // Constructor: método que se ejecuta al crear un objeto
    public function __construct($sku, $nombre, $precio, $etiquetas = []) {
    
    }

    public function regla_bogo() {
        return false;
    }

    public function regla_descuento_por_volumen() {
        return false;
    }

    public function regla_cupones() {
        return false;
    }

    public function regla_costes_envio() {
        return false;
    }
}
