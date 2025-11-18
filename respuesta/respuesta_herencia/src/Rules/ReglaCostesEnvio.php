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
        // Poner en otra funcion
        $has_freeshipping_coupon = false;
        if($cupones != NULL) {
            foreach ($cupones as $cupon) {
                if($cupon->getName() == "FREESHIPPING" && $cupon->is_date_valid()) {
                    $has_freeshipping_coupon = true;
                }
            }
        } 
        if ($subtotal >= 50.00) {
            return 0.0; // Envío gratis por superar 50€
        } else {
            return $has_freeshipping_coupon ? CuponFreeshipping::get_freeshipping() : 5.00; // Envío estándar si no hay cupón
        }
    }
}