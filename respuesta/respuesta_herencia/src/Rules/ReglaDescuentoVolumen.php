<?php
namespace respuesta_herencia\src\Rules;


class ReglaDescuentoVolumen {
    public static function regla_volumen(int $total_items, float $raw_subtotal): float
    {
        // Regla: Si hay 5 o más items, se aplica 10% de descuento
        if ($total_items >= 5) {
            return $raw_subtotal * 0.10;
        }
        return 0.0;
    }
}