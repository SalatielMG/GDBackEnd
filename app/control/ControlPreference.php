<?php
/**
 * Created by PhpStorm.
 * User: pc-01
 * Date: 21/08/2019
 * Time: 14:47
 */
require_once (APP_PATH."model/Preference.php");

class ControlPreference
{
    private $p;
    public function __construct()
    {
        $this -> p = new Preference();
    }
    public function buscarPreferencesBackup() {
        $idBackup = Form::getValue('idBack');
        $select = $this -> p -> mostrar("id_backup = $idBackup");
        $arreglo = array();
        if ($select) {
            $arreglo["error"] = false;
            $arreglo["preferences"] = $select;
            $arreglo["titulo"] = "¡ PREFERNCES ENCONTRADOS !";
            $arreglo["msj"] = "Se encontraron preferences del respaldo solicitado.";
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ PREFERNCES NO ENCONTRADOS !";
            $arreglo["msj"] = "No se encontraron preferences del respaldo solicitado.";
        }
        return $arreglo;
    }
}