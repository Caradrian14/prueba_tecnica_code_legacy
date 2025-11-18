<?php
namespace respuesta_herencia\src\Rules;

class ReglaBogo {
    public static function bogo_rule(array $product_from_db, int $quantity): float {
        if (in_array('BOGO', $product_from_db['tags'])) {
            $free_items = floor($quantity / 2);
            return $free_items * $product_from_db['price'];
        }
        return 0.0;
    }
}