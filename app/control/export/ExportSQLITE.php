<?php
/**
 * Created by PhpStorm.
 * User: pc-01
 * Date: 30/10/2019
 * Time: 10:09 AM
 */
require_once (APP_PATH . "model/DBSQLITE.php");
require_once (APP_PATH . "control/ControlMovement.php");
require_once (APP_PATH . "control/ControlCurrency.php");
require_once (APP_PATH . "control/ControlCardView.php");
require_once (APP_PATH . "control/ControlCategory.php");
require_once (APP_PATH . "control/ControlBudget.php");
require_once (APP_PATH . "control/ControlAutomatic.php");
require_once (APP_PATH . "control/ControlAccount.php");
require_once (APP_PATH . "control/ControlPreference.php");

class ExportSQLITE
{

    private $id_backup;

    public function __construct($id_backup)
    {
        $this -> id_backup = $id_backup;
    }

    public function sqliteExport(){
        $arreglo = array();
        $dbSqlite = new DBSQLITE();
        $dbSqlite -> generateSchema();
        $error = 0;

        // ------------------------------ Insert Movements ------------------------------ //
        $control = new ControlMovement();
        $control -> setPk_Movement($this -> id_backup);
        $query = $control -> buscarMovementsBackup(false, true);
        if (!$query["error"]) {
            $insert = $dbSqlite -> insertMultipleData($control -> getMovementModel() -> nameTableSQLITE, $query["movements"], $control -> getMovementModel() -> columnsTableSQLITE);
            $arreglo["insertMovements"] = $insert;
            if (!$insert) {
                $error++;
                $arreglo["errorInsert"][$error]["error"] = true;
                $arreglo["errorInsert"][$error]["titulo"] = "";
                $arreglo["errorInsert"][$error]["msj"] = "Ocurrio un error al intentar ingresar los registros en la tabla \"" . $control -> getMovementModel() -> nameTableSQLITE . "\" sobre el fichero database.sqlite";
            }
        }
        // ------------------------------ Insert Movements ------------------------------ //

        // ------------------------------ Insert Currencies ------------------------------ //
        $control = new ControlCurrency($this -> id_backup);
        $query = $control -> obtCurrenciesGralBackup(false, true);
        $insert = $dbSqlite -> insertMultipleData($control -> getCurrencyModel() -> nameTableSQLITE, $query["currencies"], $control -> getCurrencyModel() -> columnsTableSQLITE);
        $arreglo["insertCurrencies"] = $insert;
        if (!$insert) {
            $error++;
            $arreglo["errorInsert"][$error]["error"] = true;
            $arreglo["errorInsert"][$error]["titulo"] = "";
            $arreglo["errorInsert"][$error]["msj"] = "Ocurrio un error al intentar ingresar los registros en la tabla \"" . $control -> getCurrencyModel() -> nameTableSQLITE . "\" sobre el fichero database.sqlite";
        }
        // ------------------------------ Insert Currencies ------------------------------ //

        // ------------------------------ Insert Cardviews ------------------------------ //
        $control = new ControlCardView($this -> id_backup);
        $query = $control -> obtCardViewsGralBackup(false, true);
        //$arreglo["queryCardView"] = $query;
        if (!$query["error"]) {
            $insert = $dbSqlite -> insertMultipleData($control -> getCardViewModel() -> nameTableSQLITE, $query["cardviews"], $control -> getCardViewModel() -> columnsTableSQLITE);
            $arreglo["insertCardViews"] = $insert;
            if (!$insert) {
                $error++;
                $arreglo["errorInsert"][$error]["error"] = true;
                $arreglo["errorInsert"][$error]["titulo"] = "";
                $arreglo["errorInsert"][$error]["msj"] = "Ocurrio un error al intentar ingresar los registros en la tabla \"" . $control -> getCardViewModel() -> nameTableSQLITE . "\" sobre el fichero database.sqlite";
            }
        }
        // ------------------------------ Insert Cardviews ------------------------------ //

        // ------------------------------ Insert Categories ------------------------------ //
        $control = new ControlCategory($this -> id_backup);
        $query = $control ->  buscarCategoriesBackup(false, true);
        if (!$query["error"]) {
            $insert = $dbSqlite -> insertMultipleData($control -> getCategoryModel() -> nameTableSQLITE, $query["categories"], $control -> getCategoryModel() -> columnsTableSQLITE);
            $arreglo["insertCategories"] = $insert;
            if (!$insert) {
                $error++;
                $arreglo["errorInsert"][$error]["error"] = true;
                $arreglo["errorInsert"][$error]["titulo"] = "";
                $arreglo["errorInsert"][$error]["msj"] = "Ocurrio un error al intentar ingresar los registros en la tabla \"" . $control -> getCategoryModel() -> nameTableSQLITE . "\" sobre el fichero database.sqlite";
            }
        }
        // ------------------------------ Insert Categories ------------------------------ //

        // ------------------------------ Insert Budgets ------------------------------ //
        $control = new ControlBudget($this -> id_backup);
        $query = $control ->  buscarBudgetsBackup(false, true);
        if (!$query["error"]) {
            $insert = $dbSqlite -> insertMultipleData($control -> getBudgetModel() -> nameTableSQLITE, $query["budgets"], $control -> getBudgetModel() -> columnsTableSQLITE);
            $arreglo["insertBudgets"] = $insert;
            if (!$insert) {
                $error++;
                $arreglo["errorInsert"][$error]["error"] = true;
                $arreglo["errorInsert"][$error]["titulo"] = "";
                $arreglo["errorInsert"][$error]["msj"] = "Ocurrio un error al intentar ingresar los registros en la tabla \"" . $control -> getBudgetModel() -> nameTableSQLITE . "\" sobre el fichero database.sqlite";
            }
        }
        // ------------------------------ Insert Budgets ------------------------------ //

        // ------------------------------ Insert Automatics ------------------------------ //
        $control = new ControlAutomatic($this -> id_backup);
        $query = $control ->  buscarAutomaticsBackup(false, true);
        if (!$query["error"]) {
            $insert = $dbSqlite -> insertMultipleData($control -> getAutomaticModel() -> nameTableSQLITE, $query["automatics"], $control -> getAutomaticModel() -> columnsTableSQLITE);
            $arreglo["insertAutomatics"] = $insert;
            if (!$insert) {
                $error++;
                $arreglo["errorInsert"][$error]["error"] = true;
                $arreglo["errorInsert"][$error]["titulo"] = "";
                $arreglo["errorInsert"][$error]["msj"] = "Ocurrio un error al intentar ingresar los registros en la tabla \"" . $control -> getAutomaticModel() -> nameTableSQLITE . "\" sobre el fichero database.sqlite";
            }
        }
        // ------------------------------ Insert Automatics ------------------------------ //

        // ------------------------------ Insert Accounts ------------------------------ //
        $control = new ControlAccount($this -> id_backup);
        $query = $control ->  buscarAccountsBackup(false, true);
        if (!$query["error"]) {
            $insert = $dbSqlite -> insertMultipleData($control -> getAccountModel() -> nameTableSQLITE, $query["accounts"], $control -> getAccountModel() -> columnsTableSQLITE);
            $arreglo["insertAccounts"] = $insert;
            if (!$insert) {
                $error++;
                $arreglo["errorInsert"][$error]["error"] = true;
                $arreglo["errorInsert"][$error]["titulo"] = "";
                $arreglo["errorInsert"][$error]["msj"] = "Ocurrio un error al intentar ingresar los registros en la tabla \"" . $control -> getAccountModel() -> nameTableSQLITE . "\" sobre el fichero database.sqlite";
            }
        }
        // ------------------------------ Insert Accounts ------------------------------ //

        // ------------------------------ Insert Preferences ------------------------------ //
        $control = new ControlPreference($this -> id_backup);
        $query = $control ->  buscarPreferencesBackup(false, true);
        if (!$query["error"]) {
            $insert = $dbSqlite -> insertMultipleData($control -> getPreferenceModel() -> nameTableSQLITE, $query["preferences"], $control -> getPreferenceModel() -> columnsTableSQLITE);
            $arreglo["insertPreferences"] = $insert;
            if (!$insert) {
                $error++;
                $arreglo["errorInsert"][$error]["error"] = true;
                $arreglo["errorInsert"][$error]["titulo"] = "";
                $arreglo["errorInsert"][$error]["msj"] = "Ocurrio un error al intentar ingresar los registros en la tabla \"" . $control -> getPreferenceModel() -> nameTableSQLITE . "\" sobre el fichero database.sqlite";
            }
        }
        // ------------------------------ Insert Preferences ------------------------------ //

        if ($error == 0) {
            $arreglo["error"] = false;
            $arreglo["titulo"] = "¡ Exportación terminada !";
            $arreglo["msj"] = "Se creo correctamente el fichero SQLITE del Respaldo con id_backup: " . $this -> id_backup;
        } else {
            $arreglo["error"] = "warning";
            $arreglo["titulo"] = "¡ Ecportación no terminada !";
            $arreglo["msj"] = "Ocurrieron errores al crear el fichero SQLITE del Respaldo con id_backup: " . $this -> id_backup;
        }
        return $arreglo;
    }

}