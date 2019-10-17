<?php
/**
 * Created by PhpStorm.
 * User: pc-01
 * Date: 21/08/2019
 * Time: 12:35
 */
require_once (APP_PATH."model/Budget.php");
require_once ("ControlAccount.php");

class ControlBudget extends Valida
{
    private $b;
    private $ctrlAccount;
    private $pagina = 0;

    private $pk_Budget = array();

    private $where = "";
    private $select = "";
    private $table = "";

    public function __construct()
    {
        $this -> b = new Budget();
    }

    private function condition_pk_Budget($isQuery, $alias){
        return (!$isQuery) ? " AND $alias.id_account = " . $this -> pk_Budget["id_account"] . " AND $alias.id_category = " . $this -> pk_Budget["id_category"] : "";
    }

    public function buscarBudgetsBackup($isQuery = true) {
        if ($isQuery) {
            $this -> pk_Budget["id_backup"] = Form::getValue('idBack');
            $this -> pagina = Form::getValue("pagina");
            $this -> pagina = $this -> pagina * $this -> limit;
        }
        $exixstIndexUnique = $this -> b -> verifyIfExistsIndexUnique($this -> b -> nameTable);
        if ($exixstIndexUnique["indice"]) {

        } else {
            $this -> select = "bd.*, (SELECT symbolCurrency(" . $this -> pk_Budget["id_backup"] . ", '', bd.id_account)) AS symbol, (SELECT nameAccount(" . $this -> pk_Budget["id_backup"] . ", bd.id_account)) AS nameAccount, (SELECT nameCategory(" . $this -> pk_Budget["id_backup"] . ", bd.id_category)) as nameCategory,  COUNT(bd.id_backup) repeated";
            $this -> table = "backup_budgets bd";
            $this -> where = "bd.id_backup = " . $this -> pk_Budget["id_backup"] . $this -> condition_pk_Budget($isQuery, "bd") . " GROUP BY " . $this -> namesColumns($this -> b -> columnsTableIndexUnique, "bd.") . " HAVING COUNT( * ) >= 1 " . (($isQuery) ? "limit $this->pagina,$this->limit": "");
        }
        $select = $this -> b -> mostrar($this -> where, $this -> select, $this -> table);
        $arreglo = array();
        $arreglo["consultaSQL"] = $this -> consultaSQL($this -> select, $this -> table, $this -> where);
        if ($select) {
            $arreglo["error"] = false;
            $arreglo["budgets"] = $select;
            $arreglo["titulo"] = "¡ BUDGETS ENCONTRADOS !";
            $arreglo["msj"] = "Se encontraron budgets del Respaldo con id_backup: " . $this -> pk_Budget["id_backup"];
            if ($isQuery && $this -> pagina == 0) {
                $this -> ctrlAccount = new ControlAccount($this -> pk_Budget["id_backup"]);
                $arreglo["accountsBackup"] = $this -> ctrlAccount -> obtAccountsBackup(false, "'-'");
            }
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ BUDGETS NO ENCONTRADOS !";
            $arreglo["msj"] = "No se encontraron budgets del Respaldo con id_backup: " . $this -> pk_Budget["id_backup"];
        }
        return $arreglo;
    }

    public function inconsistenciaBudget() {
        $data = json_decode(Form::getValue('dataUser', false, false));
        $this -> pagina = Form::getValue('pagina');
        $backups = json_decode(Form::getValue('backups', false, false));
        $arreglo = array();

        $this -> pagina = $this -> pagina * $this -> limit_Inconsistencia;
        $select = "bd.*, COUNT(bd.id_backup) cantidadRepetida";
        $table = "backup_budgets bd, backups b";
        $where = "b.id_backup = bd.id_backup ". $this -> condicionarConsulta($data -> id, "b.id_user", 0) . $this -> inBackups($backups, "bd.id_backup") . " GROUP BY ". $this -> namesColumns($this -> b -> columnsTableIndexUnique, "bd.") ." HAVING COUNT( * ) >= $this->having_Count limit $this->pagina, $this->limit_Inconsistencia";
        $arreglo["consultaSQL"] = $this -> consultaSQL($select, $table, $where);
        $consulta = $this -> b -> mostrar($where, $select, $table);
        if ($consulta) {
            $arreglo["error"] = false;
            $arreglo["budgets"] = $consulta;
            $arreglo["titulo"] = "¡ INCONSISTENCIAS ENCONTRADOS !";
            $arreglo["msj"] = "Se encontraron duplicidades de registros en la tabla Budgets ". (($data -> email != "Generales") ? "del usuario: $data->email" : "");
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ INCONSISTENCIAS NO ENCONTRADOS !";
            $arreglo["msj"] = "No se encontraron duplicidades de registros en la tabla Budgets ". (($data -> email != "Generales") ? "del usuario: $data->email" : "");
        }
        return $arreglo;
    }
    public function corregirInconsitencia() {
        $indices = $this -> b -> ejecutarCodigoSQL("SHOW INDEX from " . $this -> b -> nameTable);
        $arreglo = array();
        $arreglo["indice"] = false;
        foreach ($indices as $key => $value) {
            if ($value -> Key_name == "indiceUnico") { //Ya existe el indice unico... Entonces la tabla ya se encuentra corregida
                $arreglo["indice"] = true;
                $arreglo["msj"] = "Ya existe el campo unico en la tabla Budgets, por lo tanto ya se ha realizado la corrección de datos inconsistentes anteriormente.";
                $arreglo["titulo"] = "¡ TABLA CORREGIDA ANTERIORMENTE !";
                return $arreglo;
            }
        }
        $sql = $this -> sentenciaInconsistenicaSQL($this -> b -> nameTable, $this -> b -> columnsTableIndexUnique, "id_backup");
        $operacion = $this -> b -> ejecutarMultSentMySQLi($sql);
        $arreglo["SenteciasSQL"] = $sql;
        $arreglo["Result"] = $operacion;
        return $arreglo;
    }

    public function verifyExistsIndexUnique($newBudget, $isUpdate = false) {
        $arreglo = array();
        $arreglo["error"] = false;

        $arreglo["sqlVerfiyIndexUnique"] = $this -> conditionVerifyExistsUniqueIndex($newBudget, $this -> b -> columnsTableIndexUnique);
        $result = $this -> b -> mostrar($arreglo["sqlVerfiyIndexUnique"]);
        if ($result) {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ REGISTRO EXISTENTE !";
            $arreglo["msj"] = "NO se puede " . (($isUpdate) ? "actualizar el" : "registrar el nuevo") . " presupuesto, puesto que ya existe un registro en la BD con los mismos datos del mismo backup. Porfavor verifica y vuleva a intentarlo";

        }
        return $arreglo;
    }

    public function agregarBudget() {
        $budget = json_decode(Form::getValue("budget", false, false));
        $arreglo = array();
        $arreglo = $this -> verifyExistsIndexUnique($budget);
        if ($arreglo["error"]) return $arreglo;

        $insert = $this -> b -> agregar($budget);
        if ($insert) {
            $arreglo["error"] = false;
            $arreglo["titulo"] = "¡ BUDGET AGREGADO !";
            $arreglo["msj"] = "Se agrego correctamente el nuevo presupuesto con id_account: $budget->id_account e id_category: $budget->id_category del Respaldo con id_backup: $budget->id_backup";

            $this -> pk_Budget["id_backup"] = $budget -> id_backup;
            $this -> pk_Budget["id_account"] = $budget -> id_account;
            $this -> pk_Budget["id_category"] = $budget -> id_category;
            $queryBudgetNew = $this -> buscarBudgetsBackup(false);
            $arreglo["budget"]["error"]  = $queryBudgetNew["error"];
            $arreglo["budget"]["titulo"] = $queryBudgetNew["titulo"];
            $arreglo["budget"]["msj"]    = $queryBudgetNew["msj"];
            if (!$arreglo["budget"]["error"])
                $arreglo["budget"]["new"]  = $queryBudgetNew["budgets"][0];
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ BUDGET NO AGREGADO !";
            $arreglo["msj"] = "Ocurrio un error al ingresar el nuevo presupuesto con id_account: $budget->id_account e id_category: $budget->id_category del Respaldo con id_backup: $budget->id_backup";
        }
        return $arreglo;
    }
    public function actualizarBudget() {
        $budget = json_decode(Form::getValue("budget", false, false));
        $indexUnique = json_decode(Form::getValue("indexUnique", false, false));
        $arreglo = array();

        if (($budget -> id_backup != $indexUnique -> id_backup)
            || ($budget -> id_account != $indexUnique -> id_account)
            || ($budget -> id_category != $indexUnique -> id_category)
            || ($budget -> period != $indexUnique -> period)
            || ($budget -> amount != $indexUnique -> amount)
            || ($budget -> budget != $indexUnique -> budget)) {
            $arreglo = $this -> verifyExistsIndexUnique($budget, true);
            if ($arreglo["error"]) return $arreglo;
        }

        $update = $this -> b -> actualizar($budget, $indexUnique);
        if ($update) {
            $arreglo["error"] = false;
            $arreglo["titulo"] = "¡ BUDGET ACTUALIZADA !";
            $arreglo["msj"] = "El presupusto con id_account: $indexUnique->id_account e id_category: $indexUnique->id_category del Respaldo con id_backup: $indexUnique->id_backup se ha actuaizado correctamente";

            $this -> pk_Budget["id_backup"] = $budget -> id_backup;
            $this -> pk_Budget["id_account"] = $budget -> id_account;
            $this -> pk_Budget["id_category"] = $budget -> id_category;
            $queryBudgetUpdate = $this -> buscarBudgetsBackup(false);
            $arreglo["budget"]["error"]  = $queryBudgetUpdate["error"];
            $arreglo["budget"]["titulo"] = $queryBudgetUpdate["titulo"];
            $arreglo["budget"]["msj"]    = $queryBudgetUpdate["msj"];
            if (!$arreglo["budget"]["error"])
                $arreglo["budget"]["update"]  = $queryBudgetUpdate["budgets"][0];
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ BUDGET NO ACTUALIZADA !";
            $arreglo["msj"] = "Ocurrio un error al intentar actualizar el presupusto con id_account: $indexUnique->id_account e id_category: $indexUnique->id_category del Respaldo con id_backup: $indexUnique->id_backup";
        }
        return $arreglo;
    }
    public function eliminarBudget() {
        $indexUnique = json_decode(Form::getValue("indexUnique", false, false));
        $arreglo = array();
        $delete = $this -> b -> eliminar($indexUnique);
        if ($delete) {
            $arreglo["error"] = false;
            $arreglo["titulo"] = "¡ BUDGET ELIMINADA !";
            $arreglo["msj"] = "El presupuesto con id_account: $indexUnique->id_account e id_category: $indexUnique->id_category del Respaldo con id_backup: $indexUnique->id_backup ha sido eliminado correctamente";
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ BUDGET NO ELIMINADA !";
            $arreglo["msj"] = "El presupuesto con id_account: $indexUnique->id_account e id_category: $indexUnique->id_category del Respaldo con id_backup: $indexUnique->id_backup no ha sido eliminado correctamente";
        }
        return $arreglo;
    }

}