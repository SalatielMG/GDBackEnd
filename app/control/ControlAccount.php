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
        $email = Form::getValue('email');
        $this -> pagina = Form::getValue('pagina');
        $idUser = 0;
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
            $where = "email = " . "'".$email."'";
            $select = "id_user, email";
            $table = "users";
            $consultaUser = $this -> a -> mostrar($where, $select, $table);
            if (!$consultaUser) {
                $arreglo["error"] = true;
                $arreglo["titulo"] = "¡ USUARIO NO ENCONTRADO !";
                $arreglo["msj"] = " El usuario solicitado no se encuentra registrado en la base de datos ";
                return $arreglo;
            } else {
                $idUser = $consultaUser[0] -> id_user;
            }
        }
        //----------------------------------------------------------------
        //Buscar id_backups del usuario solicitado: (iUser == 0) ? "Usuarios en General": "Usuario en particular";
        $where = "" . (($idUser == 0) ? "1" : "id_user = $idUser"). " ORDER BY id_backup DESC";
        $select = "id_backup";
        $table = "backups";
        $backupsUser =  $this -> a -> mostrar($where, $select, $table);
        if (!$backupsUser) {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ BACKUPS NO ENCONTRADOS !";
            $arreglo["msj"] = ($idUser == 0) ? "No se lograron encontrar respaldos en la base de datos de ningun usuario" : " El usuario solicitado no ha realizado respaldos";
            return $arreglo;
        }
        array_unshift($backupsUser, array('id_backup' => '0'));
        $arreglo["backupsUser"] = $backupsUser;

        //----------------------------------------------------------------
        $this -> pagina = $this -> pagina * $this -> limit_Inconsistencia;
        $select = "ba.*, COUNT(ba.id_backup) cantidadRepetida";
        $table = "backup_accounts ba, backups b";
        $where = "b.id_backup = ba.id_backup ". $this -> condicionarConsulta($idUser, "b.id_user", 0) ." GROUP BY ". $this -> namesColumns($this -> a -> nameColumns, "ba.") ." HAVING COUNT( * ) >= $this->having_Count ORDER BY ba.id_backup DESC  limit $this->pagina, $this->limit_Inconsistencia";
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
        $sql = $this -> senetenciaInconsistenicaSQL($this -> a -> nameTable, ['id_backup', 'id_account'], "id_backup");
        $operacion = $this -> a -> ejecutarMultSentMySQLi($sql);
        $arreglo = array(
            "SenteciasSQL" => $sql,
            "Result" => $operacion
        );
        return $arreglo;
    }

}