<?php
/**
 * Created by PhpStorm.
 * User: pc-01
 * Date: 21/08/2019
 * Time: 12:25
 */
require_once (APP_PATH."model/Automatic.php");
require_once ("ControlAccount.php");
require_once ("ControlCategory.php");
class ControlAutomatic extends Valida
{

    private $a;
    private $ctrlCategory;
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

        /*$select = $this -> a -> mostrar("1 ORDER BY CC.initial_date",
            "CC.*",
            "(SELECT ba.*, bc.symbol, bac.name as account, bcat.name as category FROM backup_automatics ba, backup_currencies bc, backup_accounts bac, backup_categories bcat WHERE ba.id_backup = bc.id_backup AND ba.id_backup = bac.id_backup AND ba.id_account = bac.id_account AND ba.id_backup = bcat.id_backup AND ba.id_category = bcat.id_category AND ba.id_backup = $idBackup
            UNION
            SELECT ba.*, bc.symbol, bac.name as account, '' as category FROM backup_automatics ba, backup_currencies bc, backup_accounts bac WHERE ba.id_backup = bc.id_backup AND ba.id_backup = bac.id_backup AND ba.id_account = bac.id_account AND ba.id_category >= 10000 AND ba.id_backup = $idBackup) as CC");
        */
        // $selectMiddle = "SELECT ba.*, bc.symbol, bac.name as account, bcat.name as category FROM backup_automatics ba, backup_currencies bc, backup_accounts bac, backup_categories bcat WHERE ba.id_backup = bc.id_backup AND ba.id_backup = bac.id_backup AND ba.id_account = bac.id_account AND ba.id_backup = bcat.id_backup AND ba.id_category = bcat.id_category AND ba.id_backup = $idBackup";
        // $selectFull = "SELECT ba.*, bc.symbol, bac.name as account, '' as category FROM backup_automatics ba, backup_currencies bc, backup_accounts bac WHERE ba.id_backup = bc.id_backup AND ba.id_backup = bac.id_backup AND ba.id_account = bac.id_account AND ba.id_category >= 10000 AND ba.id_backup = $idBackup";
        // $sqlDone = "SELECT ba.*, bc.symbol, (SELECT nombreCuenta(179, ba.id_account)) AS ACCOUNT, (SELECT nombreCategoria(179, ba.id_category)) as category FROM backup_automatics ba, backup_currencies bc WHERE ba.id_backup = bc.id_backup AND ba.id_backup = 179"
        if ($select) {
            $arreglo["error"] = false;
            $arreglo["automatics"] = $select;
            $arreglo["titulo"] = "¡ AUTOMATICS ENCONTRADOS !";
            $arreglo["msj"] = "Se encontraron automatics del Respaldo con id_backup: $this->id_backup.";
            if ($isQuery) {
                $this -> ctrlAccount = new ControlAccount($this -> id_backup);
                $this -> ctrlCategory = new ControlCategory($this -> id_backup);
                $arreglo["accountsBackup"] = $this -> ctrlAccount -> obtAccountsBackup();
                $arreglo["categoriesBackup"] = $this -> ctrlCategory -> obtCategoriesBackup();
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
        $this -> id_backup = Form::getValue("idBack");
        $arreglo = array();
        $queryIdMaxOperation = $this -> a -> mostrar("id_backup = $this->id_backup", "max(id_operation) as max");
        if ($queryIdMaxOperation) {
            $newId_Operation = $queryIdMaxOperation[0] -> max + 1;
            $arreglo["newId_Operation"] = $newId_Operation;
            $arreglo["error"] = false;
            $arreglo["titulo"] = "¡ ID OPERATION CALCULADO !";
            $arreglo["msj"] = "Se calculo correctamente el id_operation de la nueva configuración automática a ingresar";

            $this -> ctrlAccount = new ControlAccount($this -> id_backup);
            $this -> ctrlCategory = new ControlCategory($this -> id_backup);
            $arreglo["accountsBackup"] = $this -> ctrlAccount -> obtAccountsBackup();
            $arreglo["categoriesBackup"] = $this -> ctrlCategory -> obtCategoriesBackup();
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ ID OPERATION NO CALCULADO !";
            $arreglo["msj"] = "NO se calculo correctamente el id_operation de la nueva configuración automática a ingresar";

        }
        return $arreglo;
    }

    public function agregarAutomatic() {

    }

    public function actualizarAutomatic() {

    }

    public function eliminarAutomatic() {

    }
}