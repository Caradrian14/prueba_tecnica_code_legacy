<?php

namespace respuesta_herencia\src\Calculadora_carrito;
use respuesta_herencia\src\productos\Producto;
use respuesta_herencia\src\cupones\Cupon;
use Datetime;
use respuesta_herencia\src\Factory\ProductoFactory;
use respuesta_herencia\src\Factory\CuponFactory;
use respuesta_herencia\src\Rules\ReglaBogo;
use respuesta_herencia\src\Rules\ReglaDescuentoVolumen;
use respuesta_herencia\src\Rules\ReglaCostesEnvio;
use respuesta_herencia\src\Rules\ReglaCupones;

class CartCalculator {
    private $cart;
    private $coupons;

    public function __construct($cart, $coupons) {
        $this->object_creation($cart, $coupons);
    }
    // Pasar a un fctory?
    public function object_creation($cart, $coupons) {
        // Usamos el array a modoo de bbdd pero aqui habria que hacer una consulta select where a la bbdd 
        $PRODUCTS_DB = require __DIR__ . "/../../datos/productos.php";
        $COUPONS_DB = require __DIR__ . "/../../datos/cupones.php";
        foreach ($cart as $item_cart) {
            if (!isset($PRODUCTS_DB[$item_cart['sku']])) {
                continue; // Ignorar productos que no existen
            }

            $sku = $item_cart['sku'];
            $name  = $PRODUCTS_DB[$sku]['name']  ?? '';
            $quantity  = $item_cart['quantity']  ?? '';
            $price = $PRODUCTS_DB[$sku]['price'] ?? 0;
            $tag   = $PRODUCTS_DB[$sku]['tag']   ?? [];
            $items_from_product =["sku" => $sku, "name" => $name, "quantity" => $quantity, "price"=>$price, "tag"=>$tag];
            $product_cart = ProductoFactory::fromArray($items_from_product);
            // Asignar los valores correspondientes del array a su variable
            $this->cart[] = $product_cart;
        }

        foreach ($coupons as $item_cupon) {
            if (!isset($COUPONS_DB[$item_cupon])) {
                continue; // Ignorar productos que no existen
            }
            $name = $item_cupon;
            $start_date = $COUPONS_DB[$name][0]; //Revisar para que no haya fallos
            $finish_date   = $COUPONS_DB[$name][1]; // revisar para qu eno haya fallos
            $acumulative = $COUPONS_DB[$name][2] ?? true; // acumulativos
            // Asignar los valores correspondientes del array a su variable
            $items_from_coupon =["name" => $name, "start_date" => $start_date, "finish_date"=>$finish_date, "acumulative"=>$acumulative];
            
            $cupon_carrito = CuponFactory::fromArray($items_from_coupon);
            $this->coupons[] = $cupon_carrito;
        }
    }

    public function start_calculator_cart() {
        $raw_subtotal = 0;   // Subtotal antes de CUALQUIER descuento
        $total_items = 0;    // Cantidad total de items
        $bogo_discount = 0;  // Descuento acumulado por BOGO

        //Obtenemos los datos de la bbdd
        $PRODUCTS_DB = require __DIR__ . "/../../datos/productos.php";

        //1. Calcular subtotal bruto
        foreach ($this->cart as $item) {
            $sku = $item->getSku();
            $quantity = $item->getQuantity();
            //Hay que comprobar que los productos existan ( esto seria un select where en la bbdd)
            if (!isset($PRODUCTS_DB[$sku])) {
                continue; // Ignorar productos que no existen
            }
    
            $product_data = $PRODUCTS_DB[$sku];
            $price = $product_data['price'];
    
            // Acumular subtotal bruto y total de items
            $raw_subtotal += $quantity * $price;
            $total_items += $quantity;
    
            // Aplicar regla BOGO usando la clase BogoRule
            $bogo_discount += ReglaBogo::bogo_rule($product_data, $quantity);

        }

        $subtotal_after_bogo = $raw_subtotal - $bogo_discount;
        // 2. Aplicar Descuento por Volumen (Regla 2)
        $volume_discount = ReglaDescuentoVolumen::volume_rule($total_items, $raw_subtotal);


        // Subtotal final (BOGO + Volumen)
        $final_subtotal = $subtotal_after_bogo - $volume_discount;

        // 3. Aplicar descuentos de cupones
        if($this->coupons != NULL){
            $final_subtotal = ReglaCupones::aplicar_descuentos_cupones($final_subtotal, $this->coupons);
        }

         // 4. Calcular coste de envío
        $shipping_cost = ReglaCostesEnvio::rule_shipping_cost($final_subtotal, $this->coupons);
        // Total final
        $total_price = $final_subtotal + $shipping_cost;

        return round($total_price, 2);
    }
}