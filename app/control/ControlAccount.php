<?php
/**
 * Created by Salatiel Montero.
 * User: pc-hp
 * Date: 20/08/2019
 * Time: 11:40 PM
 */
require_once(APP_PATH.'model/Account.php');

class ControlAccount extends Valida
{
    private $a;
    private $pagina = 0;
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
        $data = json_decode(Form::getValue('dataUser', false, false));
        $this -> pagina = Form::getValue('pagina');
        $backups = json_decode(Form::getValue('backups', false, false));
        $arreglo = array();
        //----------------------------------------------------------------
        $this -> pagina = $this -> pagina * $this -> limit_Inconsistencia;
        $select = "ba.*, COUNT(ba.id_backup) cantidadRepetida";
        $table = "backup_accounts ba, backups b";
        $where = "b.id_backup = ba.id_backup ". $this -> condicionarConsulta($data -> id, "b.id_user", 0) . $this -> inBackups($backups) . " GROUP BY ". $this -> namesColumns($this -> a -> nameColumns, "ba.") ." HAVING COUNT( * ) >= $this->having_Count ORDER BY ba.id_backup DESC  limit $this->pagina, $this->limit_Inconsistencia";
        $arreglo["consultaSQL"] = $this -> consultaSQL($select, $table, $where);
        $consulta = $this -> a -> mostrar($where, $select, $table);
        if ($consulta) {
            $arreglo["error"] = false;
            $arreglo["accounts"] = $consulta;
            $arreglo["titulo"] = "¡ INCONSISTENCIAS ENCONTRADOS !";
            $arreglo["msj"] = "Se encontraron duplicidades de registros en la tabla Accounts ". (($data -> email != "Generales") ? "del usuario: $data->email" : "");
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ INCONSISTENCIAS NO ENCONTRADOS !";
            $arreglo["msj"] = "No se encontraron duplicidades de registros en la tabla Accounts ". (($data -> email != "Generales") ? "del usuario: $data->email" : "");
        }
        return $arreglo;
    }

    public function corregirInconsitencia() {
        $indices = $this -> a -> ejecutarCodigoSQL("SHOW INDEX from " . $this -> a -> nameTable);
        $arreglo = array();
        $arreglo["indice"] = false;
        foreach ($indices as $key => $value) {
            if ($value -> Key_name == "indiceUnico") { //Ya existe el indice unico... Entonces la tabla ya se encuentra corregida
                $arreglo["indice"] = true;
                $arreglo["msj"] = "Ya existe el campo unico en la tabla Accounts, por lo tanto ya se ha realizado la corrección de datos inconsistentes anteriormente.";
                $arreglo["titulo"] = "¡ TABLA CORREGIDA ANTERIORMENTE !";
                return $arreglo;
            }
        }
        $sql = $this -> sentenciaInconsistenicaSQL($this -> a -> nameTable, ['id_backup', 'id_account'], "id_backup");
        $operacion = $this -> a -> ejecutarMultSentMySQLi($sql);
        $arreglo["SenteciasSQL"] = $sql;
        $arreglo["Result"] = $operacion;
        return $arreglo;
    }

}