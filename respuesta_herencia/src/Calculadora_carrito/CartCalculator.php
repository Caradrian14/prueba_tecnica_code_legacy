<?php

namespace respuesta_herencia\src\Calculadora_carrito;
use respuesta_herencia\src\Productos\Producto;
use respuesta_herencia\src\Cupones\Cupon;
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

    /**
     * Constructor del carrito de la compra, acepta el carrito y los cupones tal cual llegan de la bbdd, 
     * es decir en la prueba los arrays. Llama a la funcion object_creation($cart, $coupons)
     *
     * @param  array    $cart       array asociativocon los sku, descripcionesy demas
     * @param  array    $coupons    array asociativocon de los cupones     
     * @return void
     */
    public function __construct($cart, $coupons) {
        $this->object_creation($cart, $coupons);
    }

    /**
     *  Contruye los objetos (cupones y productos) pasados por el constructor, realiza un filtro de validez, 
     *  y si son validos los pasa al Factory. 
     *  Una vez construidos los pasamos a un array que se queda como los parametros de este objeto $cart y $coupons.
     *  Dado que no hay BBBDD usaremos las llamadas al array, en un sitio profesional se comprobaria mediante consultas sql a la bbdd 
     *
     * @param  array    $cart       array asociativocon los sku, descripcionesy demas
     * @param  array    $coupons    array asociativocon de los cupones
     * @return void
     */
    public function object_creation($cart, $coupons) {
        // Usamos el array a modoo de bbdd pero aqui habria que hacer una consulta select where a la bbdd 
        $PRODUCTS_DB = require __DIR__ . "/../../datos/productos.php";
        $COUPONS_DB = require __DIR__ . "/../../datos/cupones.php";
        foreach ($cart as $item_cart) {
            if (!isset($PRODUCTS_DB[$item_cart['sku']])) {
                continue;
            }

            $sku = $item_cart['sku'];
            $name  = $PRODUCTS_DB[$sku]['name']  ?? '';
            $quantity  = $item_cart['quantity']  ?? '';
            $price = $PRODUCTS_DB[$sku]['price'] ?? 0;
            $tag   = $PRODUCTS_DB[$sku]['tag']   ?? [];
            $items_from_product =["sku" => $sku, "name" => $name, "quantity" => $quantity, "price"=>$price, "tag"=>$tag];
            $product_cart = ProductoFactory::fromArray($items_from_product);
            $this->cart[] = $product_cart;
        }

        foreach ($coupons as $item_cupon) {
            if (!isset($COUPONS_DB[$item_cupon])) {
                continue;
            }
            $name = $item_cupon;
            $start_date = $COUPONS_DB[$name][0];
            $finish_date   = $COUPONS_DB[$name][1];
            $acumulative = $COUPONS_DB[$name][2] ?? true;

            $items_from_coupon =["name" => $name, "start_date" => $start_date, "finish_date"=>$finish_date, "acumulative"=>$acumulative];
            
            $cupon_carrito = CuponFactory::fromArray($items_from_coupon);
            $this->coupons[] = $cupon_carrito;
        }
    }

    /**
     * Inicia la logica de la calculadora del carrito, inicializa las variables de cada componente del calculo y 
     * las llama a las distintas funcionalidades correspondientes 
     * Dado que usa los parametros del propio objeto ya inicializado, no se requiere pasarle parametros 
     * 
     * @return float $total_price   precio final tras haber realizado todo el calculo, redondeado a dos digitos.
     */
    public function start_calculator_cart() {
        $raw_subtotal = 0;   // Subtotal antes de CUALQUIER descuento
        $total_items = 0;    // Cantidad total de items
        $bogo_discount = 0;  // Descuento acumulado por BOGO

        $PRODUCTS_DB = require __DIR__ . "/../../datos/productos.php";

        //1. Calcular subtotal bruto
        foreach ($this->cart as $item) {
            $sku = $item->getSku();
            $quantity = $item->getQuantity();
    
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
            $final_subtotal = ReglaCupones::apply_discount_coupons($final_subtotal, $this->coupons);
        }

         // 4. Calcular coste de envío
        $shipping_cost = ReglaCostesEnvio::rule_shipping_cost($final_subtotal, $this->coupons);
        // Total final
        $total_price = $final_subtotal + $shipping_cost;

        return round($total_price, 2);
    }
}