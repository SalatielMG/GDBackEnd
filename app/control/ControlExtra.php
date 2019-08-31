<?php
/**
 * Created by PhpStorm.
 * User: pc-01
 * Date: 21/08/2019
 * Time: 14:39
 */
require_once (APP_PATH."model/Extra.php");

class ControlExtra
{
    private $e;
    public function __construct()
    {
        $this -> e = new Extra();
    }
    public function buscarExtrasBackup() {
        $idBackup = Form::getValue('idBack');
        $select = $this -> e -> mostrar("id_backup = $idBackup");
        $arreglo = array();
        if ($select) {
            $arreglo["error"] = false;
            $arreglo["extras"] = $select;
            $arreglo["titulo"] = "¡ EXTRAS ENCONTRADOS !";
            $arreglo["msj"] = "Se encontraron extras del respaldo solicitado.";
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ EXTRAS NO ENCONTRADOS !";
            $arreglo["msj"] = "No se encontraron extras del respaldo solicitado.";
        }
        return $arreglo;
    }
}