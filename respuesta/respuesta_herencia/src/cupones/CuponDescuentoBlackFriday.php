<?php
namespace respuesta_herencia\src\cupones;

class CuponDescuentoBlackFriday extends Cupon {
    public static function calculate_coupon_discount_blackfriday(float $final_subtotal): float
    {
        return $final_subtotal - 1.00;
    }
}
?>