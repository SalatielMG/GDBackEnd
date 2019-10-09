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
    private $where = "";
    private $select = "";
    private $table = "";

    public function __construct()
    {
        $this -> a = new Automatic();
    }

    public function buscarAutomaticsBackup($isQuery = true) {
        if ($isQuery) {
            $this -> id_backup = Form::getValue('idBack');
            $this -> pagina = Form::getValue("pagina");
            $this -> pagina = $this -> pagina * $this -> limit;
        }
        $exixstIndexUnique = $this -> a -> verifyIfExistsIndexUnique($this -> a -> nameTable);
        if ($exixstIndexUnique["indice"]) {

        } else {
            $this -> select = "ba.*, (SELECT symbolCurrency($this->id_backup, '', ba.id_account)) AS symbol, (SELECT nameAccount($this->id_backup, ba.id_account)) AS nameAccount, (SELECT nameCategory($this->id_backup, ba.id_category)) as nameCategory,  COUNT(ba.id_backup) cantidadRepetida";
            $this -> table = "backup_automatics ba";
            $this -> where = "ba.id_backup = $this->id_backup GROUP BY " . $this -> namesColumns($this -> a -> nameColumnsIndexUnique, "ba.") . " HAVING COUNT( * ) >= 1 " . (($isQuery) ? "limit $this->pagina,$this->limit": "");
        }
        $select = $this -> a -> mostrar($this -> where, $this -> select, $this -> table);
        $arreglo = array();
        $arreglo["consultaSQL"] = $this -> consultaSQL($this -> select, $this -> table, $this -> where);
        if ($select) {
            $arreglo["error"] = false;
            $arreglo["automatics"] = $select;
            $arreglo["titulo"] = "¡ AUTOMATICS ENCONTRADOS !";
            $arreglo["msj"] = "Se encontraron automatics del Respaldo con id_backup: $this->id_backup.";
            if ($isQuery && $this -> pagina == 0) {
                $this -> ctrlAccount = new ControlAccount($this -> id_backup);
                $arreglo["accountsBackup"] = $this -> ctrlAccount -> obtAccountsBackup(false);
            }
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ AUTOMATICS NO ENCONTRADOS !";
            $arreglo["msj"] = "No se encontraron automatics del Respaldo con id_backup: $this->id_backup.";
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

    public function agregarAutomatic() {
        $automatic = json_decode(Form::getValue("automatic", false, false));
        // Buscar si existe el indexUnique
        return compact("automatic");
    }

    public function actualizarAutomatic() {
        $automatic = json_decode(Form::getValue("automatic", false, false));
        $indexUnique = json_decode(Form::getValue("indexUnique", false, false));
        // Buscar si existe el indexUnique

        return compact("automatic", "indexUnique");
    }

    public function eliminarAutomatic() {
        $indexUnique = json_decode(Form::getValue("indexUnique", false, false));
        return compact("indexUnique");
    }
}