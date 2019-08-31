<?php
/**
 * Created by Salatiel Montero.
 * User: pc-hp
 * Date: 20/08/2019
 * Time: 11:40 PM
 */
require_once(APP_PATH.'model/Account.php');

class ControlAccount
{
    private $a;
    public function __construct()
    {
        $this -> a = new Account();
    }

    public function buscarAccountsBackup() {
        $idBackup = Form::getValue('idBack');
        $select = $this -> a -> mostrar("ba.id_backup = bc.id_backup AND ba.id_backup = $idBackup 
             GROUP by ba.`id_backup`,ba.`id_account`,ba.`name`,ba.`detail`,ba.`sign`,ba.`income`,ba.`expense`,ba.`initial_balance`,ba.`final_balance`,ba.`month`,ba.`year`,ba.`positive_limit`,ba.`negative_limit`,ba.`positive_max`,ba.`negative_max`,ba.`iso_code`,ba.`selected`,ba.`value_type`,ba.`include_total`,ba.`rate`,ba.`icon_name` HAVING COUNT( * ) >= 1 ",
                "ba.*, bc.symbol, COUNT(ba.id_backup) cantidadRepetida", "backup_accounts ba, backup_currencies bc");
        // $sql = "SELECT ba.*, bc.symbol FROM backup_accounts ba, backup_currencies bc WHERE ba.id_backup = bc.id_backup AND ba.id_backup = $idBackup";
            $arreglo = array();
        if ($select) {
            $arreglo["error"] = false;
            $arreglo["accounts"] = $select;
            $arreglo["titulo"] = "¡ ACCOUNTS ENCONTRADOS !";
            $arreglo["msj"] = "Se encontraron accounts del respaldo solicitado.";
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ ACCOUNTS NO ENCONTRADOS !";
            $arreglo["msj"] = "No se encontraron accounts del respaldo solicitado.";
        }
        return $arreglo;
    }

    public function inconsistenciaAccounts() {
        $select = $this -> a -> mostrar("1 GROUP by  `id_backup`, `id_account`, `name`, `detail`, `sign`, `income`, `expense`, `initial_balance`, `final_balance`, `month`, `year`, `positive_limit`, `negative_limit`, `positive_max`, `negative_max`, `iso_code`, `selected`, `value_type`, `include_total`, `rate`, `icon_name` HAVING COUNT( * ) >= 2 limit 1, 10",
            "*, COUNT(id_backup) cantidadRepetida");
        $arreglo = array();
        if ($select) {
            $arreglo["error"] = false;
            $arreglo["accounts"] = $select;
            $arreglo["titulo"] = "¡ INCONSISTENCIAS ENCONTRADOS !";
            $arreglo["msj"] = "Se encontraron duplicidades de registros en la tabla Accounts";
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ INCONSISTENCIAS NO ENCONTRADOS !";
            $arreglo["msj"] = "No se encontraron duplicidades de registros en la tabla Accounts";
        }
        return $arreglo;
    }

}