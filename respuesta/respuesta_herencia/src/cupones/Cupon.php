<?php
namespace respuesta_herencia\src\cupones;
class Cupon {
    public $id;
    public $name;
    public $start_date;
    public $finish_date;
    public $acumulative; // bool

    public function __construct($name,$start_date="", $finish_date="", $acumulative = true) {
        $this->name = $name;
        $this->start_date = $start_date;
        $this->finish_date = $finish_date;
        $this->acumulative = $acumulative;
    }

    public function esValido(): bool {
        $today = date('Y-m-d');
        if ($today>= $this->start_date && $hoy <= $this->fechaFin) {
            return true;
        }
        return false;
    }

    // Revisar par alos hijos si los hay
    //abstract public function aplicarDescuento(float $subtotal): float;
}
