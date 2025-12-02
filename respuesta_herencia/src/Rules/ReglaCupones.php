<?php
namespace respuesta_herencia\src\Rules;
use Datetime;
use respuesta_herencia\src\cupones\CuponDescuento1Euro;
use respuesta_herencia\src\cupones\CuponDescuento2Euro;
use respuesta_herencia\src\cupones\CuponDescuento10Euro;
use respuesta_herencia\src\cupones\CuponDescuentoBlackFriday;
class ReglaCupones {
    /**
     * Funcion para aplicar la regla de los cupones, 
     * se hace una comprobacion de los cupones que hay y si son o no acumlables, 
     * de no ser acumulables, solo se selecciona el no acumulable. 
     * 
     * @param  float $final_subtotal 
     * @param  array $coupons array del objeto Cupones que son los que ha introducido el cliente
     * @return float $final_subtotal 
     */
    public static function apply_discount_coupons(float $final_subtotal, $coupons): float {
        $cuponNoAcumulable = true;
        $CuponNoAcumulable = null;
        $hasCuponNoAcumulable = array_filter($coupons, function($coupon) {
            return $coupon->isAcumulative() === true;
        });

        if($hasCuponNoAcumulable) {
            $coupons = $hasCuponNoAcumulable;
        }
        foreach ($coupons as $coupon) {
            $has_a_valid_date = $coupon->is_date_valid();
            if (!$has_a_valid_date) {
                continue;
            }
            $name_coupon = $coupon->getName();
            $has_freeshipping_coupon = false;
            //Se podria haber hecho con un caso de array, pero mejor switch case
            switch ($name_coupon) {
                case "1EUROS":
                    $final_subtotal = CuponDescuento1Euro::calculate_coupon_discount_1euro($final_subtotal);
                    break;
                case "2EUROS":
                    $final_subtotal = CuponDescuento2Euro::calculate_coupon_discount_2euro($final_subtotal);
                    break;
                case "10EUROS":
                    $final_subtotal = CuponDescuento10Euro::calculate_coupon_discount_10euro($final_subtotal);
                    break;
                case "BLACKFRIDAY":
                    $final_subtotal = CuponDescuentoBlackFriday::calculate_coupon_discount_blackfriday($final_subtotal);
                    break;
                default:
                    break;
            }
        }
        return $final_subtotal;
    }
}