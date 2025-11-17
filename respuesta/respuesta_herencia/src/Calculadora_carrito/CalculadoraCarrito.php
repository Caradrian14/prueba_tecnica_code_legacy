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
            $final_subtotal = $this->aplicar_descuentos_cupones($final_subtotal, $this->cupones);
        }

         // 4. Calcular coste de envío
        // $shipping_cost = $this->calcular_coste_envio($final_subtotal, $this->cupones);
        $shipping_cost = ReglaCostesEnvio::regla_coste_envio($final_subtotal, $this->cupones);
        // Total final
        $total_price = $final_subtotal + $shipping_cost;

        return round($total_price, 2);
    }

    public function regla_volumen(int $total_items, float $raw_subtotal): float
    {
        // Regla: Si hay 5 o más items, se aplica 10% de descuento
        if ($total_items >= 5) {
            return $raw_subtotal * 0.10;
        }

        return 0.0;
    }
    public function aplicar_descuentos_cupones(float $final_subtotal, $cupones): float {
        //la idea es hacer un cupon que sea no acumulable. Hay que encontrar ese cupon
        $cuponNoAcumulable = true;
        $CuponNoAcumulable = null;
        $hasCuponNoAcumulable = array_filter($cupones, function($cupon) {
            return $cupon->isAcumulative() === false;
        });      

        if($hasCuponNoAcumulable) {
            $cupones = $hasCuponNoAcumulable;
        }

        foreach ($cupones as $cupon) {
            //----comprobamos las fechas----
            $start_coupon = $cupon->getStartDate();
            $finish_coupon = $cupon->getFinishDate();
            $start_coupon_date = new DateTime($start_coupon);
            $finish_coupon_date = new DateTime($finish_coupon);
            $today = new DateTime();
            if ($today < $start_coupon_date || $today > $finish_coupon_date) {
                continue;
            }
            //----Fin comprobamos las fechas----
            
            $nombre_cupon = $cupon->getName();
            $has_freeshipping_coupon = false;
            //Se podria haber hecho con un caso de array, pero mejor switch case (?)
            switch ($nombre_cupon) {
                case "1EUROS":
                    //llamada a funciones de cada hijo cupon?
                    $final_subtotal -= 1.00;
                    break;
                case "2EUROS":
                    $final_subtotal -= 2.00;
                    break;
                case "10EUROS":
                    $final_subtotal -= 10.00;
                    break;
                case "BLACKFRIDAY":
                    $final_subtotal *= 0.80;
                    break;
            }
        }
        return $final_subtotal;
    }


    public function calcular_coste_envio(float $subtotal, $cupones): float {
        // Poner en otra funcion
        $has_freeshipping_coupon = false;
        if($cupones != NULL) {
            foreach ($cupones as $cupon) {
                if($cupon->getName() == "FREESHIPPING") {
                    $has_freeshipping_coupon = true;
                }
            }
        }
        
        if ($subtotal >= 50.00) {
            return 0.0; // Envío gratis por superar 50€
        } else {
            return $has_freeshipping_coupon ? 0.0 : 5.00; // Envío estándar si no hay cupón
        }
    }
}