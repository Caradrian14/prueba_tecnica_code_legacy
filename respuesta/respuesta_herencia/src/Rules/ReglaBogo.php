<?php
namespace respuesta_herencia\src\Rules;

class ReglaBogo {
    public static function regla_bogo(array $producto, int $quantity): float {
        if (in_array('BOGO', $producto['tags'])) {
            $free_items = floor($quantity / 2);
            return $free_items * $producto['price'];
        }
        return 0.0;
    }
}