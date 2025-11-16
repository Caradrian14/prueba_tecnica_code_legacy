<?php

namespace respuesta_herencia\src\calculadora_carrito;

//Aqui ira logica del negocio 
//como si fuera el "calculate_legacy_price" del script legalicy

class Calculadora {
    private $carrito;
    private $cupones;

    public function __construct($carrito, $cupones) {
        $this->carrito = $carrito;
        $this->cupones = $cupones;
    }


    // Vamos a empezar con que las reglas de negocio son funciones, una vez ya funcional las pasamos a objetos.
    public function inicio_carrito() {
        //Definimos variables base
        $raw_subtotal = 0;   // Subtotal antes de CUALQUIER descuento
        $total_items = 0;    // Cantidad total de items
        $bogo_discount = 0;  // Descuento acumulado por BOGO

        //Obtenemos los datos de la bbdd
        $PRODUCTOS_DB = require __DIR__ . "/datos/productos.php";


        //1. Calcular subtotal bruto
        foreach ($this->carrito as $item) {
            $sku = $item['sku'];
            $quantity = $item['quantity'];
            
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
            $bogo_discount += $bogoRule->regla_bogo($product_data, $quantity);
        }

        $subtotal_after_bogo = $raw_subtotal - $bogo_discount;

        // 2. Aplicar Descuento por Volumen (Regla 2)
        $volume_discount = $this->regla_volumen($total_items, $subtotal_after_bogo);

        // Subtotal final (BOGO + Volumen)
        $final_subtotal = $subtotal_after_bogo - $volume_discount;
        
        //------------Logica cupones------------
        $has_1euros_coupon = in_array('1EUROS', $coupons);
        $has_2euros_coupon = in_array('2EUROS', $coupons);
        $has_10euros_coupon = in_array('10EUROS', $coupons);
        $has_blackfriday_coupon = in_array('BLACKFRIDAY', $coupons);

        if($has_1euros_coupon){
            $final_subtotal -= 1.00;
        } else if($has_2euros_coupon){
            $final_subtotal -= 2.00;
        } else if($has_10euros_coupon){
            $final_subtotal -= 10.00;
        } else if($has_blackfriday_coupon && date('Y-m-d') >= '2025-11-20' && date('Y-m-d') <= '2025-11-30'){
            // Aplicar 20% de descuento adicional en Black Friday
            $final_subtotal *= 0.80;
        }
        //------------Fin Logica cupones------------

        return false;
    }

    function regla_bogo(array $producto, int $quantity): float {
        if (in_array('BOGO', $producto['tags'])) {
            $free_items = floor($quantity / 2);
            return $free_items * $producto['price'];
        }
        return 0.0;
    }

    public function regla_volumen(int $total_items, float $subtotal_after_bogo): float
    {
        // Regla: Si hay 5 o más items, se aplica 10% de descuento
        if ($total_items >= 5) {
            return $subtotal_after_bogo * 0.10; // descuento correcto
        }

        return 0.0;
    }
}