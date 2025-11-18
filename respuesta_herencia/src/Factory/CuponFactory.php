<?php
namespace respuesta_herencia\src\Factory;
use respuesta_herencia\src\Cupones\Cupon;

class CuponFactory {
    /**
     * Crea un objetode tipo Cupon
     * 
     * @param  array $items_de_cupon
     * @return Cupon cupon_carrito
     */
    public static function fromArray(array $items_de_cupon): Cupon {
        $cupon_carrito = new Cupon(
            name:  $items_de_cupon["name"],
            start_date: $items_de_cupon["start_date"],
            finish_date: $items_de_cupon["finish_date"],
            acumulative:   $items_de_cupon["acumulative"]
        );
        return $cupon_carrito;
    }
}