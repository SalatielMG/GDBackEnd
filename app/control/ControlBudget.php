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

    public function __construct($id_backup = 0)
    {
        $this -> b = new Budget();
        $this -> pk_Budget["id_backup"] = $id_backup;
    }

    public function getBudgetModel() {
        return $this -> b;
    }

    public function buscarBudgetsBackup($isQuery = true, $isExport = false, $typeExport = "sqlite") {
        if ($isQuery) {
            $this -> pk_Budget["id_backup"] = Form::getValue('idBack');
            $this -> pagina = Form::getValue("pagina");
            $this -> pagina = $this -> pagina * $this -> limit;
        }
        if ($isExport)
            if ($typeExport == "sqlite")
                $this -> select = "(SELECT nameAccount(" . $this -> pk_Budget["id_backup"] . ", bd.id_account)) AS account, (SELECT nameCategory(" . $this -> pk_Budget["id_backup"] . ", bd.id_category)) as category, bd.period, bd.amount, bd.budget, DATE_FORMAT(bd.initial_date, '%d/%m/%Y') as initial_date,  DATE_FORMAT(bd.final_date, '%d/%m/%Y') as final_date, 0 as show_item, bd.number, 0 as selected";
            else
                $this -> select = "(SELECT nameAccount(" . $this -> pk_Budget["id_backup"] . ", bd.id_account)) AS account, (SELECT nameCategory(" . $this -> pk_Budget["id_backup"] . ", bd.id_category)) as category, bd.period, bd.amount, bd.budget, (SELECT symbolCurrency(" . $this -> pk_Budget["id_backup"] . ", '', bd.id_account)) AS symbol";
        else
            $this -> select = "bd.*, (SELECT symbolCurrency(" . $this -> pk_Budget["id_backup"] . ", '', bd.id_account)) AS symbol, (SELECT nameAccount(" . $this -> pk_Budget["id_backup"] . ", bd.id_account)) AS nameAccount, (SELECT nameCategory(" . $this -> pk_Budget["id_backup"] . ", bd.id_category)) as nameCategory,  COUNT(bd.id_backup) repeated";

        $this -> table = $this -> b -> nameTable . " bd";
        $this -> where = (($isQuery || $isExport) ? "bd.id_backup = " . $this -> pk_Budget["id_backup"] : $this -> conditionVerifyExistsUniqueIndex($this -> pk_Budget, $this -> b -> columnsTableIndexUnique, false, "bd.")) . " GROUP BY " . $this -> namesColumns($this -> b -> columnsTableIndexUnique, "bd.") . " HAVING COUNT( * ) >= 1 " . (($isQuery) ? "limit $this->pagina,$this->limit": "");
        $arreglo["consultaSQL"] = $this -> consultaSQL($this -> select, $this -> table, $this -> where);
        $select = $this -> b -> mostrar($this -> where, $this -> select, $this -> table);
        $arreglo = array();
        $arreglo["consultaSQL"] = $this -> consultaSQL($this -> select, $this -> table, $this -> where);
        if ($select) {
            $arreglo["error"] = false;
            $arreglo["budgets"] = $select;
            $arreglo["titulo"] = "¡ Budgets encontrados !";
            $arreglo["msj"] = "Se encontraron budgets del Respaldo con " . $this -> keyValueArray($this -> pk_Budget);
            if ($isQuery && $this -> pagina == 0) {
                $this -> ctrlAccount = new ControlAccount($this -> pk_Budget["id_backup"]);
                $arreglo["accountsBackup"] = $this -> ctrlAccount -> obtAccountsBackup(false, "'-'");
            }
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ Budgets no encontrados !";
            $arreglo["msj"] = "No se encontraron budgets del Respaldo con id_backup: " . $this -> keyValueArray($this -> pk_Budget);
        }
        return $arreglo;
    }

    public function inconsistenciaBudget() {
        $data = json_decode(Form::getValue('dataUser', false, false));
        $this -> pagina = Form::getValue('pagina');
        $backups = json_decode(Form::getValue('backups', false, false));
        $arreglo = array();

        $this -> pagina = $this -> pagina * $this -> limit_Inconsistencia;
        $this -> select = "bd.*, (SELECT symbolCurrency(bd.id_backup, '', bd.id_account)) AS symbol, (SELECT nameAccount(bd.id_backup, bd.id_account)) AS nameAccount, (SELECT nameCategory(bd.id_backup, bd.id_category)) as nameCategory,  COUNT(bd.id_backup) repeated";
        $this -> table = $this -> b -> nameTable . " bd, backups b";
        $this -> where = "b.id_backup = bd.id_backup ". $this -> condicionarConsulta($data -> id, "b.id_user", 0) . $this -> inBackups($backups, "bd.id_backup") . " GROUP BY ". $this -> namesColumns($this -> b -> columnsTableIndexUnique, "bd.") ." HAVING COUNT( * ) >= $this->having_Count limit $this->pagina, $this->limit_Inconsistencia";
        $arreglo["consultaSQL"] = $this -> consultaSQL($this -> select, $this -> table, $this -> where);
        $consulta = $this -> b -> mostrar($this -> where, $this -> select, $this -> table);
        if ($consulta) {
            $arreglo["error"] = false;
            $arreglo["budgets"] = $consulta;
            $arreglo["titulo"] = "¡ Inconsistencias encontradas !";
            $arreglo["msj"] = "Se encontraron duplicidades de registros en la tabla Budgets ". (($data -> email != "Generales") ? "del usuario: $data->email" : "");
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ Inconsistencias no encontradas !";
            $arreglo["msj"] = "No se encontraron duplicidades de registros en la tabla Budgets ". (($data -> email != "Generales") ? "del usuario: $data->email" : "");
        }
        return $arreglo;
    }
    public function corregirInconsitencia() {
        $this -> verificarPermiso(PERMISO_MNTINCONSISTENCIA);

        $arreglo = array();
        $exixstIndexUnique = $this -> b -> verifyIfExistsIndexUnique($this -> b -> nameTable);
        if ($exixstIndexUnique["indice"]) {
            return $arreglo = $exixstIndexUnique;
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
            $arreglo["titulo"] = "¡ Registro existente !";
            $arreglo["msj"] = "NO se puede " . (($isUpdate) ? "actualizar el" : "registrar el nuevo") . " presupuesto, porque ya existe un registro en la BD con los mismos datos del mismo backup. Porfavor verifica y vuleva a intentarlo";

        }
        return $arreglo;
    }

    public function agregarBudget() {
        $this -> verificarPermiso(PERMISO_INSERT);

        $budget = json_decode(Form::getValue("budget", false, false));
        $arreglo = array();
        $arreglo = $this -> verifyExistsIndexUnique($budget);
        if ($arreglo["error"]) return $arreglo;

        $insert = $this -> b -> agregar($budget);
        if ($insert) {
            $arreglo["error"] = false;
            $arreglo["titulo"] = "¡ Budget agregado !";
            $arreglo["msj"] = "Se agrego correctamente el nuevo presupuesto.";

            $this -> pk_Budget["id_backup"] = $budget -> id_backup;
            $this -> pk_Budget["id_account"] = $budget -> id_account;
            $this -> pk_Budget["id_category"] = $budget -> id_category;
            $this -> pk_Budget["period"] = $budget -> period;
            $this -> pk_Budget["amount"] = $budget -> amount;
            $this -> pk_Budget["budget"] = $budget -> budget;
            $queryBudgetNew = $this -> buscarBudgetsBackup(false);
            $arreglo["budget"]["error"]  = $queryBudgetNew["error"];
            $arreglo["budget"]["titulo"] = $queryBudgetNew["titulo"];
            $arreglo["budget"]["msj"]    = $queryBudgetNew["msj"];
            if (!$arreglo["budget"]["error"])
                $arreglo["budget"]["new"]  = $queryBudgetNew["budgets"][0];
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ Budget no agregado !";
            $arreglo["msj"] = "Ocurrio un error al ingresar el nuevo presupuesto.";
        }
        return $arreglo;
    }
    public function actualizarBudget() {
        $this -> verificarPermiso(PERMISO_UPDATE);

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
            $arreglo["titulo"] = "¡ Budget actualizada !";
            $arreglo["msj"] = "El presupuesto con " . $this -> keyValueArray($indexUnique) . " se ha actualizado correctamente";

            $this -> pk_Budget["id_backup"] = $budget -> id_backup;
            $this -> pk_Budget["id_account"] = $budget -> id_account;
            $this -> pk_Budget["id_category"] = $budget -> id_category;
            $this -> pk_Budget["period"] = $budget -> period;
            $this -> pk_Budget["amount"] = $budget -> amount;
            $this -> pk_Budget["budget"] = $budget -> budget;

            $queryBudgetUpdate = $this -> buscarBudgetsBackup(false);
            $arreglo["budget"]["error"]  = $queryBudgetUpdate["error"];
            $arreglo["budget"]["titulo"] = $queryBudgetUpdate["titulo"];
            $arreglo["budget"]["msj"]    = $queryBudgetUpdate["msj"];
            if (!$arreglo["budget"]["error"])
                $arreglo["budget"]["update"]  = $queryBudgetUpdate["budgets"][0];
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ Budget no actualizada !";
            $arreglo["msj"] = "Ocurrio un error al intentar actualizar el presupusto con " . $this -> keyValueArray($indexUnique);
        }
        return $arreglo;
    }
    public function eliminarBudget() {
        $this -> verificarPermiso(PERMISO_DELETE);

        $indexUnique = json_decode(Form::getValue("indexUnique", false, false));
        $arreglo = array();
        $delete = $this -> b -> eliminar($indexUnique);
        if ($delete) {
            $arreglo["error"] = false;
            $arreglo["titulo"] = "¡ Budget eliminada !";
            $arreglo["msj"] = "El presupuesto " . $this -> keyValueArray($indexUnique) . " ha sido eliminado correctamente";
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ Budget no eliminada !";
            $arreglo["msj"] = "Ocurrio un error al intentar eliminar el presupuesto con " . $this -> keyValueArray($indexUnique);
        }
        return $arreglo;
    }

}