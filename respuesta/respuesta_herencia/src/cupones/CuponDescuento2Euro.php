<?php
namespace respuesta_herencia\src\cupones;

class CuponDescuento2Euro extends Cupon {
    public static function calculo_cupon_descuento_2euro(float $final_subtotal): float
    {
        return $final_subtotal - 2.00;
    }
}
?>