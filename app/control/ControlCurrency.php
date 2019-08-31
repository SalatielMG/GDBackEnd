<?php
/**
 * Created by PhpStorm.
 * User: pc-01
 * Date: 21/08/2019
 * Time: 14:32
 */
require_once (APP_PATH."model/Currency.php");
class ControlCurrency
{
    private $c;
    public function __construct()
    {
        $this -> c = new Currency();
    }
    public function buscarCurrenciesBackup() {
        $idBackup = Form::getValue('idBack');
        $select = $this -> c -> mostrar("id_backup = $idBackup");
        $arreglo = array();
        if ($select) {
            $arreglo["error"] = false;
            $arreglo["currencies"] = $select;
            $arreglo["titulo"] = "¡ CURRENCIES ENCONTRADOS !";
            $arreglo["msj"] = "Se encontraron currencies del respaldo solicitado.";
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ CURRENCIES NO ENCONTRADOS !";
            $arreglo["msj"] = "No se encontraron currencies del respaldo solicitado.";
        }
        return $arreglo;
    }
}