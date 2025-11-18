<?php
namespace respuesta_herencia\src\cupones;

class CuponDescuento1Euro extends Cupon {
    public static function calculate_coupon_discount_1euro(float $final_subtotal): float
    {
        return $final_subtotal - 1.00;;
    }
}
?>