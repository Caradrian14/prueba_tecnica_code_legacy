<?php
namespace respuesta_herencia\src\cupones;


class CuponFreeshipping {
    /**
     * Funcion simple que solo hace que responder que que el precio es 0, por que es gratis. Esta clase se ha creado deido a que, aunque en la prueba es sencilla,
     * en un entrono profesional sule haber muchas variables mas como llamadas a api, consultas a base de datos etc... por lo que se necesita
     * desarrollar estas funciones en una clase separada y para no haber y no hacer el temido codigo espageti.
     * 
     * @return float 0.00
     */
    public static function get_freeshipping(): float
    {
        return 0.0; // es gratis!!
    }
}