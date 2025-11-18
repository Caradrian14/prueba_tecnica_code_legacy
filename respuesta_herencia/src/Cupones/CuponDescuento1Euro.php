<?php
namespace respuesta_herencia\src\cupones;

class CuponDescuento1Euro extends Cupon {
    /**
     * Funcion simple que solo hace que restar 1 euro al precio. Esta clase se ha creado deido a que, aunque en la prueba es sencilla,
     * en un entrono profesional sule haber muchas variables mas como llamadas a api, consultas a base de datos etc... por lo que se necesita
     * desarrollar estas funciones en una clase separada y para no haber y no hacer el temido codigo espageti.
     * 
     * @param  float $final_subtotal precio del subtotal
     * @return float $final_subtotal - 1.00
     */
    public static function calculate_coupon_discount_1euro(float $final_subtotal): float
    {
        return $final_subtotal - 1.00;
    }
}
?>