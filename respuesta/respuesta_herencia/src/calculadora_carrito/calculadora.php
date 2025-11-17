<?php

namespace respuesta_herencia\src\calculadora_carrito;
use respuesta_herencia\src\productos\Producto;
use respuesta_herencia\src\cupones\Cupon;
class Calculadora {
    private $carrito;
    private $cupones;

    public function __construct($carrito, $cupones) {
        // $this->carrito = $carrito;
        // $this->cupones = $cupones;
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
            // Asignar los valores correspondientes del array a su variable
            $producto_carrito = new Producto(
                sku:   $sku,
                name:  $name,
                quantity: $quantity,
                price: $price,
                tag:   $tag
            );
            $this->carrito[] = $producto_carrito;
        }

        foreach ($cupones as $item_cupon) {
            if (!isset($COUPONS_DB[$item_cupon])) {
                continue; // Ignorar productos que no existen
            }
            $name = $item_cupon;
            $start_date = $COUPONS_DB[$name][0]; //Revisar para que no haya fallos
            $finish_date   = $COUPONS_DB[$name][1]; // revisar para qu eno haya fallos
            $acumulative = $COUPONS_DB[$name][1] ?? true; // acumulativos
            // Asignar los valores correspondientes del array a su variable
            $cupones_carrito = new Cupon(
                name:  $name,
                start_date: $start_date,
                finish_date:   $finish_date
            );
            $this->cupones[] = $cupones_carrito;
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
            $bogo_discount += $this->regla_bogo($product_data, $quantity);
        }

        $subtotal_after_bogo = $raw_subtotal - $bogo_discount;
        // 2. Aplicar Descuento por Volumen (Regla 2)
        $volume_discount = $this->regla_volumen($total_items, $raw_subtotal);

        // Subtotal final (BOGO + Volumen)
        $final_subtotal = $subtotal_after_bogo - $volume_discount;

        // 3. Aplicar descuentos de cupones
        $final_subtotal = $this->aplicar_descuentos_cupones($final_subtotal, $this->cupones);

         // 4. Calcular coste de envío
        $shipping_cost = $this->calcular_coste_envio($final_subtotal, $this->cupones);

        // Total final
        $total_price = $final_subtotal + $shipping_cost;

        return round($total_price, 2);
    }

    function regla_bogo(array $producto, int $quantity): float {
        if (in_array('BOGO', $producto['tags'])) {
            $free_items = floor($quantity / 2);
            return $free_items * $producto['price'];
        }
        return 0.0;
    }

    public function regla_volumen(int $total_items, float $raw_subtotal): float
    {
        // Regla: Si hay 5 o más items, se aplica 10% de descuento
        if ($total_items >= 5) {
            return $raw_subtotal * 0.10; // descuento correcto
        }

        return 0.0;
    }

    //----REFCTORIZAR CUPONES----
    public function aplicar_descuentos_cupones(float $subtotal, array $cupones): float {
        $has_1euros_coupon = in_array('1EUROS', $cupones);
        $has_2euros_coupon = in_array('2EUROS', $cupones);
        $has_10euros_coupon = in_array('10EUROS', $cupones);
        $has_blackfriday_coupon = in_array('BLACKFRIDAY', $cupones);

        if ($has_1euros_coupon) {
            $subtotal -= 1.00;
        } else if ($has_2euros_coupon) {
            $subtotal -= 2.00;
        } else if ($has_10euros_coupon) {
            $subtotal -= 10.00;
        } else if ($has_blackfriday_coupon && date('Y-m-d') >= '2025-11-20' && date('Y-m-d') <= '2025-11-30') {
            // Aplicar 20% de descuento adicional en Black Friday
            $subtotal *= 0.80;
        }

        return $subtotal;
    }

    public function calcular_coste_envio(float $subtotal, array $cupones): float {
        $has_freeshipping_coupon = in_array('FREESHIPPING', $cupones);

        if ($subtotal >= 50.00) {
            return 0.0; // Envío gratis por superar 50€
        } else {
            return $has_freeshipping_coupon ? 0.0 : 5.00; // Envío estándar si no hay cupón
        }
    }
}