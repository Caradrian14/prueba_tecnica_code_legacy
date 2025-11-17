<?php
namespace respuesta_herencia\src\cupones;

class CuponDescuentoBlackFriday extends Cupon {
    public static function calculo_cupon_descuento_blackfriday(float $final_subtotal): float
    {
        return $final_subtotal - 1.00;
    }
}
?>