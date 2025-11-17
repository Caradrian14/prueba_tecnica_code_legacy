<?php
namespace respuesta_herencia\src\Rules;


class ReglaCostesEnvio {
    public static function regla_coste_envio(float $subtotal, $cupones): float {
        // Poner en otra funcion
        $has_freeshipping_coupon = false;
        if($cupones != NULL) {
            foreach ($cupones as $cupon) {
                if($cupon->getName() == "FREESHIPPING") {
                    $has_freeshipping_coupon = true;
                }
            }
        } 
        if ($subtotal >= 50.00) {
            return 0.0; // Envío gratis por superar 50€
        } else {
            return $has_freeshipping_coupon ? 0.0 : 5.00; // Envío estándar si no hay cupón
        }
    }
}