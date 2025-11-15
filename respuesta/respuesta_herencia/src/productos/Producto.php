<?php

class Producto {
    public $id
    public $sku;
    public $name;
    public $price;
    public $tag;

    public function __construct($sku, $name, $price, $tag = []) {
        $this->sku = $sku;
        $this->name = $name;
        $this->price = $price;
        $this->tag = $tag;
    }

    public function calcularDescuentoBOGO($cantidad) {
    }

    // gettes y setters
    //$id
    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    //$sku
    public function getSku() {
        return $this->sku;
    }

    public function setSku($sku) {
        $this->sku = $sku;
    }

    //$name
    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;
    }

    //$price
    public function getPrice() {
        return $this->price;
    }

    public function setPrice($price) {
        $this->price = $price;
    }

    //$tag
    public function getTag() {
        return $this->tag;
    }

    public function setTag($tag) {
        $this->tag = $tag;
    }
}
