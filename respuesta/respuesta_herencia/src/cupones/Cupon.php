<?php
namespace respuesta_herencia\src\cupones;
use Datetime;

class Cupon {
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

    public function is_date_valid2(): bool {
        $today = date('Y-m-d');
        if ($today>= $this->start_date && $hoy <= $this->fechaFin) {
            return true;
        }
        return false;
    }

    public function is_date_valid(): bool {
        $start_coupon = $this->getStartDate();
        $finish_coupon = $this->getFinishDate();
        $start_coupon_date = new DateTime($start_coupon);
        $finish_coupon_date = new DateTime($finish_coupon);
        $today = new DateTime();
        if ($today < $start_coupon_date || $today > $finish_coupon_date) {
            return false;
        }
        return true;
    }

    // Revisar par alos hijos si los hay
    //abstract public function aplicarDescuento(float $subtotal): float;

    // --- GETTERS ---

    public function getName(): string
    {
        return $this->name;
    }

    public function getStartDate(): string
    {
        return $this->start_date;
    }

    public function getFinishDate(): string
    {
        return $this->finish_date;
    }

    public function isAcumulative(): bool
    {
        return $this->acumulative;
    }

    // --- SETTERS ---

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function setStartDate(string $start_date): self
    {
        $this->start_date = $start_date;
        return $this;
    }

    public function setFinishDate(string $finish_date): self
    {
        $this->finish_date = $finish_date;
        return $this;
    }

    public function setAcumulative(bool $acumulative): self
    {
        $this->acumulative = $acumulative;
        return $this;
    }
}
