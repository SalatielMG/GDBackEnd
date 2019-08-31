<?php
/**
 * Created by PhpStorm.
 * User: pc-01
 * Date: 21/08/2019
 * Time: 12:35
 */
require_once (APP_PATH."model/Budget.php");
class ControlBudget
{
    private $b;
    public function __construct()
    {
        $this -> b = new Budget();
    }

    public function buscarBudgetsBackup() {
        $idBackup = Form::getValue('idBack');

        $select = $this -> b -> mostrar("1",
            "CC.*",
            "(SELECT bd.*, bc.symbol, bac.name as account, bcat.name as category FROM backup_budgets bd, backup_currencies bc, backup_accounts bac, backup_categories bcat WHERE bd.id_backup = bc.id_backup AND bd.id_backup = bac.id_backup AND bd.id_account = bac.id_account AND bd.id_backup = bcat.id_backup AND bd.id_category = bcat.id_category AND bd.id_backup = $idBackup
            UNION
            SELECT bd.*, bc.symbol, 'Cuenta no encontrada' as account, 'Categoria no encontrada' as category FROM backup_budgets bd, backup_currencies bc WHERE bd.id_backup = bc.id_backup AND (bd.id_account >= 10000 OR bd.id_category >= 10000) AND bd.id_backup = $idBackup) as CC");
        /*$select = $this -> b -> mostrar("bd.id_backup = bc.id_backup AND bd.id_backup = bac.id_backup AND bd.id_account = bac.id_account AND bd.id_backup = bcat.id_backup AND bd.id_category = bcat.id_category AND bd.id_backup = $idBackup",
            "bd.*, bc.symbol, bac.name as account, bcat.name as category",
            "backup_budgets bd, backup_currencies bc, backup_accounts bac, backup_categories bcat");*/
        $arreglo = array();
        if ($select) {
            $arreglo["error"] = false;
            $arreglo["budgets"] = $select;
            $arreglo["titulo"] = "¡ BUDGETS ENCONTRADOS !";
            $arreglo["msj"] = "Se encontraron budgets del respaldo solicitado.";
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ BUDGETS NO ENCONTRADOS !";
            $arreglo["msj"] = "No se encontraron budgets del respaldo solicitado.";
        }
        return $arreglo;
    }
}