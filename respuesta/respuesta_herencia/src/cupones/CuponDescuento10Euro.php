<?php
namespace respuesta_herencia\src\cupones;

class CuponDescuento10Euro extends Cupon {
    public static function calculate_coupon_discount_10euro(float $final_subtotal): float
    {
        return $final_subtotal - 10.00;;
    }
}
?>