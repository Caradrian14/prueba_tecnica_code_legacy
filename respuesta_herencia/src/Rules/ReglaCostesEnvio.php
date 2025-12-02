<?php
namespace respuesta_herencia\src\Rules;
use respuesta_herencia\src\cupones\CuponFreeShipping;


class ReglaCostesEnvio {
    /**
     * Funcion para aplicar la regla del shipping, es decir que si tiene el cupon de freeshipping o tiene mas de 50 euros en el pago el envio es gratis.
     * 
     * @param  float $subtotal
     * @param  array $cupones array del objeto Cupones que son los que ha introducido el cliente
     * @return float 0,0 el precio del shipping en base a la logica y si hay o no cupon
     */
    public static function rule_shipping_cost(float $subtotal, $cupones): float {
        $LIMIT_SUBTOTAL = 50;
        $SHIPPING_ADDITION = 5.00;
        $has_freeshipping_coupon = false;
        if($cupones != NULL) {
            foreach ($cupones as $cupon) {
                // hay que comprobar si hay mas cupones no acumulables
                if ($cupon->isAcumulative() && $cupon->getName() != "FREESHIPPING") {
                    $has_freeshipping_coupon = false;
                    break;
                }
                if($cupon->getName() == "FREESHIPPING" && $cupon->is_date_valid()) {
                    $has_freeshipping_coupon = true;
                }
            }
        } 
        if ($subtotal >= $LIMIT_SUBTOTAL) {
            return 0.0; // Envio gratis por superar 50€
        } else {
            return $has_freeshipping_coupon ? CuponFreeshipping::get_freeshipping() : $SHIPPING_ADDITION;
        }
    }
}