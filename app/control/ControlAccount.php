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
        $email = Form::getValue('email');
        $arreglo = array();
        if ($email != "Generales") {
            $form = new Form();
            $form -> validarDatos($email, 'Correo electronico', 'email');
            if (count($form -> errores) > 0) {
                $arreglo["error"] = true;
                $arreglo["titulo"] = "¡ ERROR DE VALIDACIÓN !";
                $arreglo["msj"] = $form -> errores;
                return $arreglo;
            }
        }
        $select = "ba.*, COUNT(ba.id_backup) cantidadRepetida";
        $table = "backup_accounts ba, users u, backups b";
        $where = "b.id_user = u.id_user AND b.id_backup = ba.id_backup ". $this -> condicionarConsulta("'$email'", "u.email", "'Generales'") ." GROUP BY ". $this -> namesColumns($this -> a -> nameColumns, "ba.") ." HAVING COUNT( * ) >= $this->having_Count limit 1, $this->limit";
        $arreglo["consultaSQL"] = $this -> consultaSQL($select, $table, $where);
        $consulta = $this -> a -> mostrar($where, $select, $table);
        if ($consulta) {
            $arreglo["error"] = false;
            $arreglo["accounts"] = $consulta;
            $arreglo["titulo"] = "¡ INCONSISTENCIAS ENCONTRADOS !";
            $arreglo["msj"] = "Se encontraron duplicidades de registros en la tabla Accounts ". (($email != "Generales") ? "del usuario: $email" : "");
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ INCONSISTENCIAS NO ENCONTRADOS !";
            $arreglo["msj"] = "No se encontraron duplicidades de registros en la tabla Accounts ". (($email != "Generales") ? "del usuario: $email" : "");
        }
        return $arreglo;
    }

    public function corregirInconsitencia() {
        $sql = $this -> senetenciaInconsistenicaSQL($this -> a -> nameTable, $this -> a -> nameColumns, "id_backup");
        $operacion = $this -> a -> ejecutarMultSentMySQLi($sql);
        $arreglo = array(
            "SenteciasSQL" => $sql,
            "Result" => $operacion
        );
        return $arreglo;
    }

}