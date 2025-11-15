<?php

class Cupones {
    public $id
    public $name;
    public $start_date;
    public $finish_date;

    public function __construct($name,$start_date, $finish_date) {
        $this->name = $name;
        $this->start_date = $start_date;
        $this->finish_date = $finish_date;
    }

    // Como añadir reglas de negocio? 
    // cada cupon una funcion <----
    // integrarlo en el array general? demasiado complejo y usran ddbb

    //SOLUCION: crear objetos cupones hijos para subdividir la responsabilidad del negocio 
}
