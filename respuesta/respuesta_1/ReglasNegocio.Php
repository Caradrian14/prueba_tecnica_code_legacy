<?php

class ReglasNegocio {
    public $sku;
    public $nombre;
    public $precio;
    public $etiquetas;

    // Constructor: método que se ejecuta al crear un objeto
    public function __construct($sku, $nombre, $precio, $etiquetas = []) {
        $this->sku = $sku;
        $this->nombre = $nombre;
        $this->precio = $precio;
        $this->etiquetas = $etiquetas;
    }

    // Método para calcular descuento BOGO
    public function calcularDescuentoBOGO($cantidad) {
        if (in_array('BOGO', $this->etiquetas)) {
            $itemsGratis = floor($cantidad / 2);
            return $itemsGratis * $this->precio;
        }
        return 0;
    }
}