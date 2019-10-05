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
    private $where = "";
    private $select = "";
    private $table = "";
    private $id_backup = 0;
    private $id_account = 0;
    // private $limitt = 3;
    public function __construct()
    {
        $this -> a = new Account();
    }
    private function condicionId_Account($isQuery, $alias) {
        return (!$isQuery) ? "AND " . $alias . "id_account = $this->id_account" : "";
    }
    public function buscarAccountsBackup($isQuery = true) {
        if ($isQuery) {
            $this -> id_backup = Form::getValue('idBack');
            $this -> pagina = Form::getValue("pagina");
            /*$this -> where = "ba.id_backup = bc.id_backup AND ba.id_backup = 18342";
            $this -> select = "DISTINCTROW ba.*, bc.symbol";
            $this -> table = "backup_accounts ba, backup_currencies bc";*/
            $this -> pagina = $this -> pagina * $this -> limit;
        }

        $this -> where = "ba.id_backup = bc.id_backup AND ba.id_backup = $this->id_backup " . $this -> condicionId_Account($isQuery, "ba.") . " GROUP by " . $this -> namesColumns($this -> a -> nameColumnsIndexUnique, "ba.") . " HAVING COUNT( * ) >= 1 " . (($isQuery) ? "limit $this->pagina,$this->limit": "");
        $this -> select = "ba.*, bc.symbol, COUNT(ba.id_backup) cantidadRepetida";
        $this -> table = "backup_accounts ba, backup_currencies bc";

        $select = $this -> a -> mostrar($this -> where, $this -> select, $this -> table);
        $arreglo = array();
        $arreglo["consultaSQL"] = $this -> consultaSQL($this -> select, $this -> table, $this -> where);

        if ($select) {
            $arreglo["error"] = false;
            $arreglo["accounts"] = $select;
            $arreglo["titulo"] = ($isQuery) ? "¡ ACCOUNTS ENCONTRADOS !" : "¡ ACCOUNT ENCONTRADO !";
            $arreglo["msj"] = ($isQuery) ? "Se encontraron accounts del Respaldo con id_backup: $this->id_backup." : "Se recupero la Cuenta con id_account: $this->id_account del Respaldo con id_backup: $this->id_backup";
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = ($isQuery) ?"¡ ACCOUNTS NO ENCONTRADOS !" : "¡ ACCOUNT NO ENCONTRADO !";
            $arreglo["msj"] = ($isQuery) ? "No se encontraron accounts del Respaldo con id_backup: $this->id_backup.": "No se recupero la Cuenta con id_account: $this->id_account del Respaldo con id_backup: $this->id_backup";
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
        $where = "b.id_backup = ba.id_backup ". $this -> condicionarConsulta($data -> id, "b.id_user", 0) . $this -> inBackups($backups) . " GROUP BY ". $this -> namesColumns($this -> a -> nameColumnsIndexUnique, "ba.") ." HAVING COUNT( * ) >= $this->having_Count ORDER BY ba.id_backup DESC  limit $this->pagina, $this->limit_Inconsistencia";
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
        $sql = $this -> sentenciaInconsistenicaSQL($this -> a -> nameTable, $this -> a -> nameColumnsIndexUnique, "id_backup");
        $operacion = $this -> a -> ejecutarMultSentMySQLi($sql);
        $arreglo["SenteciasSQL"] = $sql;
        $arreglo["Result"] = $operacion;
        return $arreglo;
    }
    public function obtNewId_account() {
        $this -> id_backup = Form::getValue("idBack");
        $arreglo = array();
        $query = $this -> a -> mostrar("id_backup = $this->id_backup", "max(id_account) as max");
        if ($query) {
            $newId_Account = $query[0] -> max + 1;
            $arreglo["newId_account"] = $newId_Account;
            $arreglo["error"] = false;
            $arreglo["titulo"] = "¡ ID ACCOUNT CALCULADO !";
            $arreglo["msj"] = "Se calculo correctamente el id_ account de la nueva cuenta a ingresar";
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ ID ACCOUNT NO CALCULADO !";
            $arreglo["msj"] = "NO se calculo correctamente el id_ account de la nueva cuenta a ingresar";
        }
        return $arreglo;
    }
    public function agregarAccount() {
        $account = json_decode(Form::getValue("account", false, false));
        $insert = $this -> a -> agregar($account);
        $arreglo = array();
        if ($insert) {
            $arreglo["error"] = false;
            $arreglo["titulo"] = "¡ ACCOUNT AGREGADO !";
            $arreglo["msj"] = "Se agrego correctamente la cuenta con id_account: $account->id_account del Respaldo con id_backup: $account->id_backup";

            $this -> id_backup = $account -> id_backup;
            $this -> id_account = $account -> id_account;
            $queryAccountNew = $this -> buscarAccountsBackup(false);
            $arreglo["account"]["error"] =  $queryAccountNew["error"];
            $arreglo["account"]["titulo"] = $queryAccountNew["titulo"];
            $arreglo["account"]["msj"] = $queryAccountNew["msj"];
            if (!$arreglo["account"]["error"])
                $arreglo["account"]["new"] = $queryAccountNew["accounts"][0];
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ ACCOUNT NO AGREGADO !";
            $arreglo["msj"] = "Ocurrio un error al agregar la cuenta con id_account: $account->id_account del Respaldo con id_backup: $account->id_backup";
        }
        return $arreglo;
    }
    public function actualizarAccount() {
        $account = json_decode(Form::getValue("account", false, false));
        $update = $this -> a -> actualizar($account);
        $arreglo = array();
        if ($update) {
            $arreglo["error"] = false;
            $arreglo["titulo"] = "¡ ACCOUNT ACTUALIZADO !";
            $arreglo["msj"] = "La Cuenta con id_account: $account->id_account del Respaldo con id_backup: $account->id_backup se actualizo correctamente";

            $this -> id_backup = $account -> id_backup;
            $this -> id_account = $account -> id_account;
            $queryAccountUpdate = $this -> buscarAccountsBackup(false);
            $arreglo["account"]["error"] =  $queryAccountUpdate["error"];
            $arreglo["account"]["titulo"] = $queryAccountUpdate["titulo"];
            $arreglo["account"]["msj"] = $queryAccountUpdate["msj"];
            if (!$arreglo["account"]["error"])
                $arreglo["account"]["update"] = $queryAccountUpdate["accounts"][0];
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ ACCOUNT NO ACTUALIZADO !";
            $arreglo["msj"] = "La Cuenta con id_account: $account->id_account del Respaldo con id_backup: $account->id_backup no se actualizo correctamente";
        }
        return $arreglo;
    }
    public function eliminarAccount() {
        $this -> id_backup = Form::getValue("id_backup");
        $this -> id_account = Form::getValue("id_account");
        $arreglo = array();
        $delete = $this -> a -> eliminar($this -> id_backup, $this -> id_account);
        if ($delete) {
            $arreglo["error"] = false;
            $arreglo["titulo"] = "¡ ACCOUNT ELIMINADA !";
            $arreglo["msj"] = "La cuenta con id_account: $this->id_account del Respaldo con id_backup: $this->id_backup ha sido eliminado correctamente";
        } else {
            $arreglo["error"] = false;
            $arreglo["titulo"] = "¡ ACCOUNT NO ELIMINADA !";
            $arreglo["msj"] = "La cuenta con id_account: $this->id_account del Respaldo con id_backup: $this->id_backup no ha sido eliminado correctamente";
        }
        return $arreglo;
    }

}