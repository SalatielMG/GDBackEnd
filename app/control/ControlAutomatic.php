<?php
/**
 * Created by PhpStorm.
 * User: pc-01
 * Date: 21/08/2019
 * Time: 12:25
 */
require_once (APP_PATH."model/Automatic.php");
require_once ("ControlAccount.php");
class ControlAutomatic extends Valida
{

    private $a;
    private $ctrlAccount;
    private $pagina = 0;
    private $id_backup = 0;

    private $pk_Automatic = array();

    private $where = "";
    private $select = "";
    private $table = "";

    public function __construct()
    {
        $this -> a = new Automatic();
    }

    private function condition_pk_Automatic($isQuery, $alias){
        return (!$isQuery) ? " AND $alias.id_operation = " . $this -> pk_Automatic["id_operation"] . " AND $alias.id_account = " . $this -> pk_Automatic["id_account"] . " AND $alias.id_category = " . $this -> pk_Automatic["id_category"] : "";
    }
    public function buscarAutomaticsBackup($isQuery = true) {
        if ($isQuery) {
            $this -> pk_Automatic["id_backup"] = Form::getValue('idBack');
            $this -> pagina = Form::getValue("pagina");
            $this -> pagina = $this -> pagina * $this -> limit;
        }
        $exixstIndexUnique = $this -> a -> verifyIfExistsIndexUnique($this -> a -> nameTable);
        if ($exixstIndexUnique["indice"]) {

        } else {
            $this -> select = "ba.*, (SELECT symbolCurrency(" . $this -> pk_Automatic["id_backup"] . ", '', ba.id_account)) AS symbol, (SELECT nameAccount(" . $this -> pk_Automatic["id_backup"] . ", ba.id_account)) AS nameAccount, (SELECT nameCategory(" . $this -> pk_Automatic["id_backup"] . ", ba.id_category)) as nameCategory,  COUNT(ba.id_backup) cantidadRepetida";
            $this -> table = "backup_automatics ba";
            $this -> where = "ba.id_backup = " . $this -> pk_Automatic["id_backup"] . $this -> condition_pk_Automatic($isQuery, "ba") . " GROUP BY " . $this -> namesColumns($this -> a -> nameColumnsIndexUnique, "ba.") . " HAVING COUNT( * ) >= 1 " . (($isQuery) ? "limit $this->pagina,$this->limit": "");
        }
        $select = $this -> a -> mostrar($this -> where, $this -> select, $this -> table);
        $arreglo = array();
        $arreglo["consultaSQL"] = $this -> consultaSQL($this -> select, $this -> table, $this -> where);
        if ($select) {
            $arreglo["error"] = false;
            $arreglo["automatics"] = $select;
            $arreglo["titulo"] = "¡ AUTOMATICS ENCONTRADOS !";
            $arreglo["msj"] = "Se encontraron automatics del Respaldo con id_backup: " . $this -> pk_Automatic["id_backup"];
            if ($isQuery && $this -> pagina == 0) {
                $this -> ctrlAccount = new ControlAccount($this -> pk_Automatic["id_backup"]);
                $arreglo["accountsBackup"] = $this -> ctrlAccount -> obtAccountsBackup(false);
            }
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ AUTOMATICS NO ENCONTRADOS !";
            $arreglo["msj"] = "No se encontraron automatics del Respaldo con id_backup: " . $this -> pk_Automatic["id_backup"];
        }
        return $arreglo;
    }

    public function inconsistenciaAutomatics() {
        $data = json_decode(Form::getValue('dataUser', false, false));
        $this -> pagina = Form::getValue('pagina');
        $backups = json_decode(Form::getValue('backups', false, false));
        $arreglo = array();

        $this -> pagina = $this -> pagina * $this -> limit_Inconsistencia;
        $select = "ba.*, COUNT(ba.id_backup) cantidadRepetida";
        $table = "backup_automatics ba, backups b";
        $where = "b.id_backup = ba.id_backup ". $this -> condicionarConsulta($data -> id, "b.id_user", 0) . $this -> inBackups($backups) . " GROUP BY ". $this -> namesColumns($this -> a -> nameColumnsIndexUnique, "ba.") ." HAVING COUNT( * ) >= $this->having_Count limit $this->pagina, $this->limit_Inconsistencia";
        $arreglo["consultaSQL"] = $this -> consultaSQL($select, $table, $where);
        $consulta = $this -> a -> mostrar($where, $select, $table);
        if ($consulta) {
            $arreglo["error"] = false;
            $arreglo["automatics"] = $consulta;
            $arreglo["titulo"] = "¡ INCONSISTENCIAS ENCONTRADOS !";
            $arreglo["msj"] = "Se encontraron duplicidades de registros en la tabla Automatics ". (($data -> email != "Generales") ? "del usuario: $data->email" : "");
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ INCONSISTENCIAS NO ENCONTRADOS !";
            $arreglo["msj"] = "No se encontraron duplicidades de registros en la tabla Automatics ". (($data -> email != "Generales") ? "del usuario: $data->email" : "");
        }
        return $arreglo;
    }

    public function corregirInconsitencia() {
        $arreglo = array();
        $exixstIndexUnique = $this -> a -> verifyIfExistsIndexUnique($this -> a -> nameTable);
        if ($exixstIndexUnique["indice"]) {
            return $arreglo = $exixstIndexUnique;
        }
        $sql = $this -> sentenciaInconsistenicaSQL($this -> a -> nameTable, $this -> a -> nameColumnsIndexUnique, "id_backup");
        $operacion = $this -> a -> ejecutarMultSentMySQLi($sql);
        $arreglo["SenteciasSQL"] = $sql;
        $arreglo["Result"] = $operacion;
        return $arreglo;
    }

    public function obtNewId_OperationAccountsCategories() {
        $this -> id_backup = Form::getValue("id_backup");
        $arreglo = array();
        $queryIdMaxOperation = $this -> a -> mostrar("id_backup = $this->id_backup", "max(id_operation) as max");
        if ($queryIdMaxOperation) {
            $newId_Operation = $queryIdMaxOperation[0] -> max + 1;
            $arreglo["newId_Operation"] = $newId_Operation;
            $arreglo["error"] = false;
            $arreglo["titulo"] = "¡ ID OPERATION CALCULADO !";
            $arreglo["msj"] = "Se calculo correctamente el id_operation de la nueva configuración automática a ingresar";

            $this -> ctrlAccount = new ControlAccount($this -> id_backup);
            $arreglo["accountsBackup"] = $this -> ctrlAccount -> obtAccountsBackup();
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ ID OPERATION NO CALCULADO !";
            $arreglo["msj"] = "NO se calculo correctamente el id_operation de la nueva configuración automática a ingresar";

        }
        return $arreglo;
    }

    public function verifyExistsIndexUnique($indexUnique) {
        $isExists = false;
        $this -> where = "id_backup = $indexUnique->id_backup AND id_operation = $indexUnique->id_operation AND id_account = $indexUnique->id_account AND id_category = $indexUnique->id_category";
        $result = $this -> a -> mostrar($this -> where);
        if ($result)  // if exissts => No Insert Or No Update
            $isExists = true;
        return $isExists;
    }

    public function agregarAutomatic() {
        $arreglo = array();
        $automatic = json_decode(Form::getValue("automatic", false, false));
        if (!$this -> verifyExistsIndexUnique($automatic)) { //Insert :)
            $insert = $this -> a -> agregar($automatic);
            if ($insert) {
                $arreglo["error"] = false;
                $arreglo["titulo"] = "¡ AUTOMATIC AGREGADO !";
                $arreglo["msj"] = "Se agrego correctamente la operación automática con id_operation: $automatic->id_operation, id_account: $automatic->id_account e id_category: $automatic->id_category del Respaldo con id_backup: $automatic->id_backup";

                $this -> pk_Automatic["id_backup"] = $automatic -> id_backup;
                $this -> pk_Automatic["id_operation"] = $automatic -> id_operation;
                $this -> pk_Automatic["id_account"] = $automatic -> id_account;
                $this -> pk_Automatic["id_category"] = $automatic -> id_category;
                $queryAutomaticNew = $this -> buscarAutomaticsBackup(false);
                $arreglo["automatic"]["error"]  = $queryAutomaticNew["error"];
                $arreglo["automatic"]["titulo"] = $queryAutomaticNew["titulo"];
                $arreglo["automatic"]["msj"]    = $queryAutomaticNew["msj"];
                if (!$arreglo["automatic"]["error"])
                    $arreglo["automatic"]["new"] = $queryAutomaticNew["automatics"][0];
            } else {
                $arreglo["error"] = true;
                $arreglo["titulo"] = "¡ AUTOMATIC NO AGREGADO !";
                $arreglo["msj"] = "Ocurrio un error al ingresar la operación automática con id_operation: $automatic->id_operation, id_account: $automatic->id_account e id_category: $automatic->id_category del Respaldo con id_backup: $automatic->id_backup";
            }
        } else { // NO Insert :(
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ REGISTRO EXISTENTE !";
            $arreglo["msj"] = "NO se puede registrar la nueva operación automática, puesto que ya existe un registro en la BD con el mismo id_operation, id_account e id_category. Porfavor cambie estos valores y vuelva a intentarlo.";
        }
        return $arreglo;
    }

    public function actualizarAutomatic() {
        $automatic = json_decode(Form::getValue("automatic", false, false));
        $indexUnique = json_decode(Form::getValue("indexUnique", false, false));
        // Buscar si existe el indexUnique
        $arreglo = array();
        $isExistsIndexUnique = false;
        if (($automatic -> id_operation != $indexUnique -> id_operation) && ($automatic -> id_account != $indexUnique -> id_account ) && ($automatic -> id_category != $indexUnique -> id_category)) { // Verify IndexUnique
            $isExistsIndexUnique = $this -> verifyExistsIndexUnique($indexUnique);
        }
        if ($isExistsIndexUnique) { // No Update :(
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ REGISTRO EXISTENTE !";
            $arreglo["msj"] = "NO se puede actualizar la operación automática, puesto que ya existe un registro en la BD con el mismo id_operation, id_account e id_category. Porfavor cambie estos valores y vuelva a intentarlo.";
        } else { // Update :)
            $update = $this -> a -> actualizar($automatic, $indexUnique);
            if ($update) {
                $arreglo["error"] = false;
                $arreglo["titulo"] = "¡ AUTOMATIC ACTUALIZADO !";
                $arreglo["msj"] = "La operación automática con id_operation: $indexUnique->id_operation, id_account: $indexUnique->id_account e id_category: $indexUnique->id_category del Respaldo con id_backup: $indexUnique->id_backup se ha actualizado correctamente";

                $this -> pk_Automatic["id_backup"] = $automatic -> id_backup;
                $this -> pk_Automatic["id_operation"] = $automatic -> id_operation;
                $this -> pk_Automatic["id_account"] = $automatic -> id_account;
                $this -> pk_Automatic["id_category"] = $automatic -> id_category;
                $queryAutomaticUpdate = $this -> buscarAutomaticsBackup(false);
                $arreglo["automatic"]["error"]  = $queryAutomaticUpdate["error"];
                $arreglo["automatic"]["titulo"] = $queryAutomaticUpdate["titulo"];
                $arreglo["automatic"]["msj"]    = $queryAutomaticUpdate["msj"];
                if (!$arreglo["automatic"]["error"])
                    $arreglo["automatic"]["update"] = $queryAutomaticUpdate["automatics"][0];
            } else {
                $arreglo["error"] = true;
                $arreglo["titulo"] = "¡ AUTOMATIC NO ACTUALIZADA !";
                $arreglo["msj"] = "Ocurrio un error al intentar actualizar la operación automática con id_operation: $indexUnique->id_operation, id_account: $indexUnique->id_account e id_category: $indexUnique->id_category del Respaldo con id_backup: $indexUnique->id_backup";
            }
        }
        return $arreglo;
    }

    public function eliminarAutomatic() {
        $indexUnique = json_decode(Form::getValue("indexUnique", false, false));
        $arreglo = array();
        $delete = $this -> a -> eliminar($indexUnique);
        if ($delete) {
            $arreglo["error"] = false;
            $arreglo["titulo"] = "¡ AUTOMATIC ELIMINADA !";
            $arreglo["msj"] = "La operación automática con id_operation: $indexUnique->id_operation, id_account: $indexUnique->id_account e id_category: $indexUnique->id_category del Respaldo con id_backup: $indexUnique->id_backup ha sido eliminado correctamente";
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ AUTOMATIC NO ELIMINADA !";
            $arreglo["msj"] = "La operación automática con id_operation: $indexUnique->id_operation, id_account: $indexUnique->id_account e id_category: $indexUnique->id_category del Respaldo con id_backup: $indexUnique->id_backup no ha sido eliminado correctamente";
        }
        return $arreglo;
    }
}