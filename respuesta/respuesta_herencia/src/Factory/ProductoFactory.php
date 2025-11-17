<?php
namespace respuesta_herencia\src\Factory;
use respuesta_herencia\src\productos\Producto;


class ProductoFactory {
    public static function fromArray(array $items_de_producto): Producto {
        $producto_carrito = new Producto(
            sku:   $items_de_producto["sku"],
            name:  $items_de_producto["name"],
            quantity: $items_de_producto["quantity"],
            price: $items_de_producto["price"],
            tag:   $items_de_producto["tag"]
        );
        return $producto_carrito;
    }
}