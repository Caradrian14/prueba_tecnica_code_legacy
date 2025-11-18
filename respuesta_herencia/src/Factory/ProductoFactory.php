<?php
namespace respuesta_herencia\src\Factory;
use respuesta_herencia\src\productos\Producto;


class ProductoFactory {
    /**
     * Crea un objetode tipo Producto
     * 
     * @param  array $items_de_producto
     * @return Producto producto_carrito
     */
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

    /**
     * Crea un objetode tipo Producto en base a la los array que vienen de los datos o bbdd
     * 
     * @param  array $items_de_producto
     * @return Producto producto_carrito
     */
    public static function fromArrayData(array $items_de_producto): Producto {
        $sku = $items_de_producto["sku"] ?? "SKU_DEFAULT";
        $name = $items_de_producto["name"] ?? "Nombre por defecto";
        $quantity = $items_de_producto["quantity"] ?? 1;
        $price = $items_de_producto["price"] ?? 0.00;
        $tag = $items_de_producto["tag"] ?? ($items_de_producto["tags"][0] ?? "SIN_TAG");

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