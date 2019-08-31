<?php
/**
 * Created by PhpStorm.
 * User: pc-01
 * Date: 21/08/2019
 * Time: 12:47
 */
require_once (APP_PATH."model/CardView.php");
class ControlCardView
{
    private $cv;
    public function __construct()
    {
        $this -> cv = new CardView();
    }

    public function buscarCardviewsBackup() {
        $idBackup = Form::getValue('idBack');
        $select = $this -> cv -> mostrar("id_backup = $idBackup");
        $arreglo = array();
        if ($select) {
            $arreglo["error"] = false;
            $arreglo["cardviews"] = $select;
            $arreglo["titulo"] = "¡ CARDVIEWS ENCONTRADOS !";
            $arreglo["msj"] = "Se encontraron cardviews del respaldo solicitado.";
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ CARDVIEWS NO ENCONTRADOS !";
            $arreglo["msj"] = "No se encontraron cardviews del respaldo solicitado.";
        }
        return $arreglo;
    }
}