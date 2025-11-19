<?php
namespace respuesta_herencia\src\Rules;


class ReglaDescuentoVolumen {
    /**
     * Funcion para aplicar la regla de los volumenes, comprobando el numero de items y aplicando el descuento pertinente. 
     * 
     * @param  int $total_items 
     * @param  float $raw_subtotal array del objeto Cupones que son los que ha introducido el cliente
     * @return float $raw_subtotal * 0.10 ó 0.0;
     */
    public static function volume_rule(int $total_items, float $raw_subtotal): float
    {
        $DISCOUNT = 0.10;
        $NUMBER_LIMIT_ITEMS = 5;
        // Regla: Si hay 5 o más items, se aplica 10% de descuento
        if ($total_items >= $NUMBER_LIMIT_ITEMS) {
            return $raw_subtotal * $DISCOUNT;
        }
        return 0.0;
    }
}