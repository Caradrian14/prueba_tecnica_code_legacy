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

class Calculadora {
    private $carrito;
    private $cupones;

    public function __construct($carrito, $cupones) {
        $this->creacion_objetos($carrito, $cupones);
    }
    // Pasar a un fctory?
    public function creacion_objetos($carrito, $cupones) {
        // Usamos el array a modoo de bbdd pero aqui habria que hacer una consulta select where a la bbdd 
        $PRODUCTOS_DB = require __DIR__ . "/../../datos/productos.php";
        $COUPONS_DB = require __DIR__ . "/../../datos/cupones.php";
        foreach ($carrito as $item_carrito) {
            if (!isset($PRODUCTOS_DB[$item_carrito['sku']])) {
                continue; // Ignorar productos que no existen
            }

            $sku = $item_carrito['sku'];
            $name  = $PRODUCTOS_DB[$sku]['name']  ?? '';
            $quantity  = $item_carrito['quantity']  ?? '';
            $price = $PRODUCTOS_DB[$sku]['price'] ?? 0;
            $tag   = $PRODUCTOS_DB[$sku]['tag']   ?? [];
            $items_de_producto =["sku" => $sku, "name" => $name, "quantity" => $quantity, "price"=>$price, "tag"=>$tag];
            $producto_carrito = ProductoFactory::fromArray($items_de_producto);
            // Asignar los valores correspondientes del array a su variable
            $this->carrito[] = $producto_carrito;
        }

        foreach ($cupones as $item_cupon) {
            if (!isset($COUPONS_DB[$item_cupon])) {
                continue; // Ignorar productos que no existen
            }
            $name = $item_cupon;
            $start_date = $COUPONS_DB[$name][0]; //Revisar para que no haya fallos
            $finish_date   = $COUPONS_DB[$name][1]; // revisar para qu eno haya fallos
            $acumulative = $COUPONS_DB[$name][2] ?? true; // acumulativos
            // Asignar los valores correspondientes del array a su variable
            $items_de_cupon =["name" => $name, "start_date" => $start_date, "finish_date"=>$finish_date, "acumulative"=>$acumulative];
            
            $cupon_carrito = CuponFactory::fromArray($items_de_cupon);
            $this->cupones[] = $cupon_carrito;
        }
    }

    // Vamos a empezar con que las reglas de negocio son funciones, una vez ya funcional las pasamos a objetos.
    public function inicio_carrito() {
        //Definimos variables base
        $raw_subtotal = 0;   // Subtotal antes de CUALQUIER descuento
        $total_items = 0;    // Cantidad total de items
        $bogo_discount = 0;  // Descuento acumulado por BOGO

        //Obtenemos los datos de la bbdd
        $PRODUCTOS_DB = require __DIR__ . "/../../datos/productos.php";

        //1. Calcular subtotal bruto
        foreach ($this->carrito as $item) {
            $sku = $item->getSku();
            $quantity = $item->getQuantity();
            //Hay que comprobar que los productos existan ( esto seria un select where en la bbdd)
            if (!isset($PRODUCTOS_DB[$sku])) {
                continue; // Ignorar productos que no existen
            }
    
            $product_data = $PRODUCTOS_DB[$sku];
            $price = $product_data['price'];
    
            // Acumular subtotal bruto y total de items
            $raw_subtotal += $quantity * $price;
            $total_items += $quantity;
    
            // Aplicar regla BOGO usando la clase BogoRule
            $bogo_discount += ReglaBogo::regla_bogo($product_data, $quantity);

            // $bogo_discount += $this->regla_bogo($product_data, $quantity);

        }

        $subtotal_after_bogo = $raw_subtotal - $bogo_discount;
        // 2. Aplicar Descuento por Volumen (Regla 2)
        // $volume_discount = $this->regla_volumen($total_items, $raw_subtotal);
        $volume_discount = ReglaDescuentoVolumen::regla_volumen($total_items, $raw_subtotal);


        // Subtotal final (BOGO + Volumen)
        $final_subtotal = $subtotal_after_bogo - $volume_discount;

        // 3. Aplicar descuentos de cupones
        if($this->cupones != NULL){
            // $final_subtotal = $this->aplicar_descuentos_cupones($final_subtotal, $this->cupones);
            $final_subtotal = ReglaCupones::aplicar_descuentos_cupones($final_subtotal, $this->cupones);
        }

         // 4. Calcular coste de envío
        // $shipping_cost = $this->calcular_coste_envio($final_subtotal, $this->cupones);
        $shipping_cost = ReglaCostesEnvio::regla_coste_envio($final_subtotal, $this->cupones);
        // Total final
        $total_price = $final_subtotal + $shipping_cost;

        return round($total_price, 2);
    }
}