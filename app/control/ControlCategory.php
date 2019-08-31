<?php
/**
 * Created by PhpStorm.
 * User: pc-01
 * Date: 21/08/2019
 * Time: 14:05
 */
require_once (APP_PATH."model/Category.php");
class ControlCategory
{
    private $c;
    public function __construct()
    {
        $this -> c = new Category();
    }
    public function buscarCategoriesBackup() {
        $idBackup = Form::getValue('idBack');
        $select = $this -> c -> mostrar("bc.id_backup = ba.id_backup AND bc.id_account = ba.id_account AND bc.id_backup = $idBackup", "bc.*, ba.name as account", "backup_categories bc, backup_accounts ba");
        $arreglo = array();
        if ($select) {
            $arreglo["error"] = false;
            $arreglo["categories"] = $select;
            $arreglo["titulo"] = "¡ CARDVIEWS ENCONTRADOS !";
            $arreglo["msj"] = "Se encontraron categories del respaldo solicitado.";
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ CARDVIEWS NO ENCONTRADOS !";
            $arreglo["msj"] = "No se encontraron categories del respaldo solicitado.";
        }
        return $arreglo;
    }
}