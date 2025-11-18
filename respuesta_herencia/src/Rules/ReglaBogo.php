<?php
namespace respuesta_herencia\src\Rules;

class ReglaBogo {
    /**
     * Funcion para aplicar la regla del bogo, es decir compras 2, uno te sale gratis, si compras 3, 
     * pues uno gratis(por qu eno vamos a ir por ahi regalando demasiadas cosas)
     * 
     * @param  array $product_from_db producto que vendria directo de la bbdd
     * @param  int $quantity precio del subtotal
     * @return float free_items * $product_from_db['price'] o 0,0 dependiendo si el producto tiene el tag de bogo
     */
    public static function bogo_rule(array $product_from_db, int $quantity): float {
        if (in_array('BOGO', $product_from_db['tags'])) {
            $free_items = floor($quantity / 2);
            return $free_items * $product_from_db['price'];
        }
        return 0.0;
    }
}