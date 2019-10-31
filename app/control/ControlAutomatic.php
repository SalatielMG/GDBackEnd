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

    private $pk_Automatic = array();

    private $where = "";
    private $select = "";
    private $table = "";

    public function __construct($id_backup = 0)
    {
        $this -> a = new Automatic();
        $this -> pk_Automatic["id_backup"] = $id_backup;
    }

    public function getAutomaticModel() {
        return $this -> a;
    }

    public function buscarAutomaticsBackup($isQuery = true, $isExport = false, $typeExport = "sqlite") {
        if ($isQuery) {
            $this -> pk_Automatic["id_backup"] = Form::getValue('idBack');
            $this -> pagina = Form::getValue("pagina");
            $this -> pagina = $this -> pagina * $this -> limit;
        }
        $exixstIndexUnique = $this -> a -> verifyIfExistsIndexUnique($this -> a -> nameTable);

        if ($exixstIndexUnique["indice"]) {

        } else {
            if ($isExport)
                if ($typeExport == "sqlite")
                    $this -> select = "ba.id_operation as _id, (SELECT nameAccount(" . $this -> pk_Automatic["id_backup"] . ", ba.id_account)) AS account, CONCAT('Operación ', ba.id_operation) as title, ba.period, ba.each_number, ba.repeat_number, ba.counter, DATE_FORMAT(ba.initial_date, '%d/%m/%Y') as initial_date, DATE_FORMAT(ba.next_date, '%d/%m/%Y') as next_date, ba.operation_code as code, (SELECT nameCategory(" . $this -> pk_Automatic["id_backup"] . ", ba.id_category)) as category, ba.amount, ba.sign, ba.detail, ba.enabled, 0 as selected";
                else
                    $this -> select = "CONCAT('Operación ', ba.id_operation) as title, ba.period, ba.each_number, ba.repeat_number, ba.counter, DATE_FORMAT(ba.initial_date, '%d/%m/%Y') as initial_date, DATE_FORMAT(ba.next_date, '%d/%m/%Y') as next_date, ba.enabled, (SELECT nameAccount(" . $this -> pk_Automatic["id_backup"] . ", ba.id_account)) AS account, (SELECT nameCategory(" . $this -> pk_Automatic["id_backup"] . ", ba.id_category)) as category, ba.amount, ba.sign, (SELECT symbolCurrency(" . $this -> pk_Automatic["id_backup"] . ", '', ba.id_account)) AS symbol";
            else
                $this -> select = "ba.*, (SELECT symbolCurrency(" . $this -> pk_Automatic["id_backup"] . ", '', ba.id_account)) AS symbol, (SELECT nameAccount(" . $this -> pk_Automatic["id_backup"] . ", ba.id_account)) AS nameAccount, (SELECT nameCategory(" . $this -> pk_Automatic["id_backup"] . ", ba.id_category)) as nameCategory,  COUNT(ba.id_backup) cantidadRepetida";

            $this -> table = "backup_automatics ba";
            $this -> where = (($isQuery || $isExport) ? "ba.id_backup = " . $this -> pk_Automatic["id_backup"] : $this -> conditionVerifyExistsUniqueIndex($this -> pk_Automatic, $this -> a -> columnsTableIndexUnique, false, "ba.") . " AND ba.id_operation = " . $this -> pk_Automatic["id_operation"]) . " GROUP BY " . $this -> namesColumns($this -> a -> columnsTableIndexUnique, "ba.") . " HAVING COUNT( * ) >= 1 ORDER BY ba.id_operation " . (($isQuery) ? "limit $this->pagina,$this->limit": "");
        }
        $arreglo = array();
        $arreglo["consultaSQL"] = $this -> consultaSQL($this -> select, $this -> table, $this -> where);
        //return $arreglo;

        $select = $this -> a -> mostrar($this -> where, $this -> select, $this -> table);

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
        $where = "b.id_backup = ba.id_backup ". $this -> condicionarConsulta($data -> id, "b.id_user", 0) . $this -> inBackups($backups) . " GROUP BY ". $this -> namesColumns($this -> a -> columnsTableIndexUnique, "ba.") ." HAVING COUNT( * ) >= $this->having_Count limit $this->pagina, $this->limit_Inconsistencia";
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
        $sql = $this -> sentenciaInconsistenicaSQL($this -> a -> nameTable, $this -> a -> columnsTableIndexUnique, "id_backup");
        $operacion = $this -> a -> ejecutarMultSentMySQLi($sql);
        $arreglo["SenteciasSQL"] = $sql;
        $arreglo["Result"] = $operacion;
        return $arreglo;
    }

    public function obtNewId_OperationAccountsCategories() {
        $this -> pk_Automatic["id_backup"] = Form::getValue("id_backup");
        $arreglo = array();
        $this -> where = "id_backup = " . $this -> pk_Automatic["id_backup"];
        $this -> select = "max(id_operation) as max";
        $queryIdMaxOperation = $this -> a -> mostrar($this -> where, $this -> select);
        $arreglo["consultaSQL"] = $this -> consultaSQL($this -> select, $this -> a -> nameTable, $this -> where);
        if ($queryIdMaxOperation) {
            $newId_Operation = $queryIdMaxOperation[0] -> max + 1;
            $arreglo["newId_Operation"] = $newId_Operation;
            $arreglo["error"] = false;
            $arreglo["titulo"] = "¡ ID OPERATION CALCULADO !";
            $arreglo["msj"] = "Se calculo correctamente el id_operation de la nueva operación automática a ingresar";

            $this -> ctrlAccount = new ControlAccount($this -> pk_Automatic["id_backup"]);
            $arreglo["accountsBackup"] = $this -> ctrlAccount -> obtAccountsBackup(false);
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ ID OPERATION NO CALCULADO !";
            $arreglo["msj"] = "NO se calculo correctamente el id_operation de la nueva operación automática a ingresar";
        }
        return $arreglo;
    }

    public function verifyExistsIndexUnique($newAutomatic, $isUpdate = false, $id_operation = 0) {
        $arreglo = array();
        $arreglo["error"] = false;
        $isDifferentId_Operation = true;
        if ($isUpdate) $isDifferentId_Operation = ($newAutomatic -> id_operation != $id_operation);
        if ($isDifferentId_Operation) {
            $result = $this -> a -> mostrar("id_backup = $newAutomatic->id_backup AND id_operation = $newAutomatic->id_operation");
            if ($result) {
                $arreglo["error"] = true;
                $arreglo["titulo"] = "¡ REGISTRO EXISTENTE !";
                $arreglo["msj"] = "NO se puede " . (($isUpdate) ? "actualizar la" : "registrar la nueva") . " operación automatica, puesto que ya existe un registro en la BD con el mismo ID_OPERATION del mismo backup. Porfavor verifique el id e intente cambiarlo";
                return $arreglo;
            }
        }

        $arreglo["sqlVerfiyIndexUnique"] = $this -> conditionVerifyExistsUniqueIndex($newAutomatic, $this -> a -> columnsTableIndexUnique);
        $result = $this -> a -> mostrar($arreglo["sqlVerfiyIndexUnique"]);
        if ($result) {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ REGISTRO EXISTENTE !";
            $arreglo["msj"] = "NO se puede " . (($isUpdate) ? "actualizar la" : "registrar la nueva") . " operación automática, puesto que ya existe un registro en la BD con los mismos datos del mismo backup. Porfavor verifique y vuelta a intentarlo";

        }
        return $arreglo;
    }

    public function agregarAutomatic() {
        $arreglo = array();
        $automatic = json_decode(Form::getValue("automatic", false, false));

        $arreglo = $this -> verifyExistsIndexUnique($automatic);
        if ($arreglo["error"]) return $arreglo;

        $insert = $this -> a -> agregar($automatic);
        if ($insert) {

            $this -> pk_Automatic["id_backup"] = $automatic -> id_backup;
            $this -> pk_Automatic["id_operation"] = $automatic -> id_operation;
            $this -> pk_Automatic["id_account"] = $automatic -> id_account;
            $this -> pk_Automatic["id_category"] = $automatic -> id_category;
            $this -> pk_Automatic["period"] = $automatic -> period;
            $this -> pk_Automatic["repeat_number"] = $automatic -> repeat_number;
            $this -> pk_Automatic["each_number"] = $automatic -> each_number;
            $this -> pk_Automatic["amount"] = $automatic -> amount;
            $this -> pk_Automatic["sign"] = $automatic -> sign;
            $this -> pk_Automatic["detail"] = $automatic -> detail;
            $this -> pk_Automatic["initial_date"] = $automatic -> initial_date;
            $queryAutomaticNew = $this -> buscarAutomaticsBackup(false);
            $arreglo["automatic"]["error"]  = $queryAutomaticNew["error"];
            $arreglo["automatic"]["titulo"] = $queryAutomaticNew["titulo"];
            $arreglo["automatic"]["msj"]    = $queryAutomaticNew["msj"];
            if (!$arreglo["automatic"]["error"])
                $arreglo["automatic"]["new"] = $queryAutomaticNew["automatics"][0];

            $arreglo["error"] = false;
            $arreglo["titulo"] = "¡ AUTOMATIC AGREGADO !";
            $arreglo["msj"] = "Se agrego correctamente la operación automática con " . $this -> keyValueArray($this -> pk_Automatic);
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ AUTOMATIC NO AGREGADO !";
            $arreglo["msj"] = "Ocurrio un error al ingresar la operación automática con " . $this -> keyValueArray($automatic);
        }
        return $arreglo;
    }

    public function actualizarAutomatic() {
        $automatic = json_decode(Form::getValue("automatic", false, false));
        $indexUnique = json_decode(Form::getValue("indexUnique", false, false));

        $arreglo = array();
        if (($automatic -> id_operation != $indexUnique -> id_operation)
            || ($automatic -> id_account != $indexUnique -> id_account)
            || ($automatic -> id_category != $indexUnique -> id_category)
            || ($automatic -> period != $indexUnique -> period)
            || ($automatic -> repeat_number != $indexUnique -> repeat_number)
            || ($automatic -> each_number != $indexUnique -> each_number)
            || ($automatic -> amount != $indexUnique -> amount)
            || ($automatic -> sign != $indexUnique -> sign)
            || ($automatic -> detail != $indexUnique -> detail)
            || ($automatic -> initial_date != $indexUnique -> initial_date)) {
            $arreglo = $this -> verifyExistsIndexUnique($automatic, true, $indexUnique -> id_operation);
            if ($arreglo["error"]) return $arreglo;
        }
        $update = $this -> a -> actualizar($automatic, $indexUnique);
        if ($update) {
            $arreglo["error"] = false;
            $arreglo["titulo"] = "¡ AUTOMATIC ACTUALIZADO !";
            $arreglo["msj"] = "La operación automática con " . $this -> keyValueArray($indexUnique) . " se ha actualizado correctamente";

            $this -> pk_Automatic["id_backup"] = $automatic -> id_backup;
            $this -> pk_Automatic["id_operation"] = $automatic -> id_operation;
            $this -> pk_Automatic["id_account"] = $automatic -> id_account;
            $this -> pk_Automatic["id_category"] = $automatic -> id_category;
            $this -> pk_Automatic["period"] = $automatic -> period;
            $this -> pk_Automatic["repeat_number"] = $automatic -> repeat_number;
            $this -> pk_Automatic["each_number"] = $automatic -> each_number;
            $this -> pk_Automatic["amount"] = $automatic -> amount;
            $this -> pk_Automatic["sign"] = $automatic -> sign;
            $this -> pk_Automatic["detail"] = $automatic -> detail;
            $this -> pk_Automatic["initial_date"] = $automatic -> initial_date;
            $queryAutomaticUpdate = $this -> buscarAutomaticsBackup(false);
            $arreglo["automatic"]["error"]  = $queryAutomaticUpdate["error"];
            $arreglo["automatic"]["titulo"] = $queryAutomaticUpdate["titulo"];
            $arreglo["automatic"]["msj"]    = $queryAutomaticUpdate["msj"];
            if (!$arreglo["automatic"]["error"])
                $arreglo["automatic"]["update"] = $queryAutomaticUpdate["automatics"][0];
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ AUTOMATIC NO ACTUALIZADA !";
            $arreglo["msj"] = "Ocurrio un error al intentar actualizar la operación automática con " . $this -> keyValueArray($indexUnique);
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
            $arreglo["msj"] = "La operación automática con " . $this -> keyValueArray($indexUnique) . " ha sido eliminado correctamente";
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ AUTOMATIC NO ELIMINADA !";
            $arreglo["msj"] = "Ocurrio un error al intentar eliminar la operación automática con " . $this -> keyValueArray($indexUnique);
        }
        return $arreglo;
    }
}