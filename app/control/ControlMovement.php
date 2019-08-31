<?php
/**
 * Created by PhpStorm.
 * User: pc-01
 * Date: 21/08/2019
 * Time: 14:43
 */
require_once (APP_PATH."model/Movement.php");

class ControlMovement
{
    private $m;
    public function __construct()
    {
        $this -> m = new Movement();
    }
    public function buscarMovementsBackup() {
        $idBackup = Form::getValue('idBack');
        $select = $this -> m -> mostrar("1 ORDER BY CC.date_record",
            "CC.*",
            "(SELECT bm.*, bc.symbol, bac.name as account, bcat.name as category FROM backup_movements bm, backup_currencies bc, backup_accounts bac, backup_categories bcat WHERE bm.id_backup = bc.id_backup AND bm.id_backup = bac.id_backup AND bm.id_account = bac.id_account AND bm.id_backup = bcat.id_backup AND (bm.id_category = bcat.id_category) AND bm.id_backup = $idBackup
                    UNION
                   SELECT bm.*, bc.symbol, bac.name as account, '' AS category FROM backup_movements bm, backup_currencies bc, backup_accounts bac WHERE bm.id_backup = bc.id_backup AND bm.id_backup = bac.id_backup AND bm.id_account = bac.id_account AND bm.id_backup = $idBackup AND bm.id_category >= 10000) AS CC");

        /*$select = $this -> m -> mostrar("bm.id_backup = bc.id_backup AND bm.id_backup = $idBackup", "bm.*, bc.symbol", "backup_movements bm, backup_currencies bc");
        $select = $this -> m -> mostrar("bm.id_backup = bc.id_backup AND bm.id_backup = bac.id_backup AND bm.id_account = bac.id_account AND bm.id_backup = bcat.id_backup AND (bm.id_category = bcat.id_category OR bm.id_category >= 10000) AND bm.id_backup = $idBackup",
            "bm.*, bc.symbol, bac.name as account, bcat.name as category",
            "backup_movements bm, backup_currencies bc, backup_accounts bac, backup_categories bcat");*/

        $arreglo = array();
        if ($select) {
            $arreglo["error"] = false;
            $arreglo["movements"] = $select;
            $arreglo["titulo"] = "¡ MOVEMENTS ENCONTRADOS !";
            $arreglo["msj"] = "Se encontraron movements del respaldo solicitado.";
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ MOVEMENTS NO ENCONTRADOS !";
            $arreglo["msj"] = "No se encontraron movements del respaldo solicitado.";
        }
        return $arreglo;
    }
}