<?php
namespace respuesta_herencia\src\cupones;

class CuponDescuento1Euro extends Cupon {
    private $descuento;

    public function __construct($codigo, $fechaInicio, $fechaFin, $descuento) {
        parent::__construct($codigo, $fechaInicio, $fechaFin);
        $this->descuento = $descuento;
    }

    public function aplicarDescuento(float $subtotal): float {
        return $this->esValido() ? max(0, $subtotal - $this->descuento) : $subtotal;
    }
}
?>