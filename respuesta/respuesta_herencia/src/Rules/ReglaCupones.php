<?php
namespace respuesta_herencia\src\Rules;
use Datetime;
use respuesta_herencia\src\cupones\CuponDescuento1Euro;
use respuesta_herencia\src\cupones\CuponDescuento2Euro;
use respuesta_herencia\src\cupones\CuponDescuento10Euro;
use respuesta_herencia\src\cupones\CuponDescuentoBlackFriday;
class ReglaCupones {
    public static function aplicar_descuentos_cupones(float $final_subtotal, $cupones): float {
        //la idea es hacer un cupon que sea no acumulable. Hay que encontrar ese cupon
        $cuponNoAcumulable = true;
        $CuponNoAcumulable = null;
        $hasCuponNoAcumulable = array_filter($cupones, function($cupon) {
            return $cupon->isAcumulative() === false;
        });      

        if($hasCuponNoAcumulable) {
            $cupones = $hasCuponNoAcumulable;
        }

        foreach ($cupones as $cupon) {
            //----comprobamos las fechas----
            $start_coupon = $cupon->getStartDate();
            $finish_coupon = $cupon->getFinishDate();
            $start_coupon_date = new DateTime($start_coupon);
            $finish_coupon_date = new DateTime($finish_coupon);
            $today = new DateTime();
            if ($today < $start_coupon_date || $today > $finish_coupon_date) {
                continue;
            }
            //----Fin comprobamos las fechas----
            
            $nombre_cupon = $cupon->getName();
            $has_freeshipping_coupon = false;
            //Se podria haber hecho con un caso de array, pero mejor switch case
            switch ($nombre_cupon) {
                case "1EUROS":
                    $final_subtotal = CuponDescuento1Euro::calculo_cupon_descuento_1euro($final_subtotal);
                    break;
                case "2EUROS":
                    $final_subtotal = CuponDescuento2Euro::calculo_cupon_descuento_2euro($final_subtotal);
                    break;
                case "10EUROS":
                    $final_subtotal = CuponDescuento10Euro::calculo_cupon_descuento_10euro($final_subtotal);
                    break;
                case "BLACKFRIDAY":
                    $final_subtotal = CuponDescuentoBlackFriday::calculo_cupon_descuento_blackfriday($final_subtotal);
                    break;
                default:
                    break;
            }
        }
        return $final_subtotal;
    }
}