<?php
/**
 * Created by Salatiel Montero.
 * User: pc-hp
 * Date: 20/08/2019
 * Time: 11:40 PM
 */
require_once(APP_PATH . 'model/Account.php');
// require_once(APP_PATH . 'model/Currency.php');
require_once('ControlCategory.php');
require_once('ControlCurrency.php');
class ControlAccount extends Valida
{
    // private $currency;
    private $a;
    private $pagina = 0;
    private $where = "";
    private $select = "";
    private $table = "";
    private $categorieSearch = 1;
    private $symbolName = 1;
    private $id_backup = 0;
    private $id_account = 0;
    private $ctrlCategory;
    private $ctrlCurrency;

    public function __construct($id_backup = 0, $categorieSearch = 1, $symbolName = 1)
    {
        // $this -> currency = new Currency();
        $this -> a = new Account();
        $this -> id_backup = $id_backup;
        $this -> categorieSearch = $categorieSearch;
        $this -> symbolName = $symbolName;
    }

    public function setId_Backup($id_backup) {
        $this -> id_backup = $id_backup;
    }

    public function getId_Backup() {
        return $this -> id_backup;
    }

    private function condicionId_Account($isQuery, $alias) {
        return (!$isQuery) ? "AND " . $alias . "id_account = $this->id_account" : "";
    }

    public function obtAccountsBackup($isQuery = true, $signCategories = "both") {
        if ($isQuery) {
            $this -> id_backup = Form::getValue("id_backup");
            $this -> categorieSearch = Form::getValue("categoriesSearch");
            $signCategories = Form::getValue("signCategories");
            if ($signCategories != "both") {
                $signCategories = ($signCategories == 0) ? "'-'": "'+'";
            }
        }
        $arreglo = array();
        $this -> select = "id_account, name, sign";
        $this -> where = "id_backup = $this->id_backup GROUP BY " . $this -> namesColumns($this -> a -> columnsTableIndexUnique, "") . " HAVING COUNT( * ) >= 1 ORDER BY id_account";
        $accountsBackup = $this -> a -> mostrar($this -> where, $this -> select);
        if ($accountsBackup) {
            $arreglo["accounts"] = $accountsBackup;
            if ($this -> categorieSearch == 1) {
                $arrayAccounts = [];
                $this -> ctrlCategory = new ControlCategory();
                $this -> ctrlCategory -> setId_Backup($this -> id_backup);
                foreach ($accountsBackup as $key => $value) {
                    $this -> ctrlCategory -> setId_Account($value -> id_account);
                    $categoriesAccount = $this -> ctrlCategory -> obtCategoriesAccountBackup(false, $signCategories);

                    $arrayAccounts[$key]["id_account"] = $value -> id_account;
                    $arrayAccounts[$key]["name"] = $value -> name;
                    $arrayAccounts[$key]["categoriesAccount"] = (!$categoriesAccount["error"]) ? $categoriesAccount["categories"]: [];

                }
                $arreglo["accounts"] = $arrayAccounts;
            }

            $arreglo["error"] = false;
            $arreglo["titulo"] = "¡ ACCOUNTS ENCONTRADOS !";
            $arreglo["msj"] = "Se encontraron accounts del Respaldo con id_backup: $this->id_backup.";
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ ACCOUNTS NO ENCONTRADOS !";
            $arreglo["msj"] = "No se encontraron accounts del Respaldo con id_backup: $this->id_backup.";
        }
        //sleep(5);
        return $arreglo;
    }

    public function buscarAccountsBackup($isQuery = true) {
        if ($isQuery) {
            $this -> id_backup = Form::getValue('idBack');
            $this -> pagina = Form::getValue("pagina");
            $this -> symbolName = Form::getValue("symbolName");
            $this -> pagina = $this -> pagina * $this -> limit;
        }
        $exixstIndexUnique = $this -> a -> verifyIfExistsIndexUnique($this -> a -> nameTable);
        if ($exixstIndexUnique["indice"]) {
            $this -> where = "ba.id_backup = bc.id_backup AND ba.id_backup = $this->id_backup AND ba.iso_code = bc.iso_code" . $this -> condicionId_Account($isQuery, "ba.") . " GROUP by " . $this -> namesColumns($this -> a -> columnsTableIndexUnique, "ba.") . " HAVING COUNT( * ) >= 1 ORDER BY id_account " . (($isQuery) ? "limit $this->pagina,$this->limit": "");
            $this -> select = "ba.*, bc.symbol, COUNT(ba.id_backup) cantidadRepetida";
            $this -> table = "backup_accounts ba, backup_currencies bc";
        } else {
            $this -> select = "ba.*, (SELECT symbolCurrency($this->id_backup, ba.iso_code, 0)) as symbol, COUNT(ba.id_backup) cantidadRepetida";
            $this -> table = "backup_accounts ba";
            $this -> where = "ba.id_backup = $this->id_backup " . $this -> condicionId_Account($isQuery, "ba.") . " GROUP BY " . $this -> namesColumns($this -> a -> columnsTableIndexUnique, "ba.") . " HAVING COUNT( * ) >= 1 ORDER BY id_account " . (($isQuery) ? "limit $this->pagina,$this->limit": "");
        }

        $select = $this -> a -> mostrar($this -> where, $this -> select, $this -> table);
        $arreglo = array();
        $arreglo["consultaSQL"] = $this -> consultaSQL($this -> select, $this -> table, $this -> where);

        if ($select) {
            $arreglo["error"] = false;
            $arreglo["accounts"] = $select;
            $arreglo["titulo"] = ($isQuery) ? "¡ ACCOUNTS ENCONTRADOS !" : "¡ ACCOUNT ENCONTRADO !";
            $arreglo["msj"] = ($isQuery) ? "Se encontraron accounts del Respaldo con id_backup: $this->id_backup." : "Se recupero la Cuenta con id_account: $this->id_account del Respaldo con id_backup: $this->id_backup";
            if ($isQuery && $this -> pagina == 0) {
                $this -> categorieSearch = 0;
                $arreglo["accountsBackup"] = $this -> obtAccountsBackup(false);
                // $arreglo["currenciesJSON"] = $this -> currency -> Currencies;
                if ($this -> symbolName == 1) {
                    $this -> ctrlCurrency = new ControlCurrency($this -> id_backup, 1);
                    $arreglo["currencies"] = $this -> ctrlCurrency -> buscarCurrenciesBackup(false);
                }
            }
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
        $where = "b.id_backup = ba.id_backup ". $this -> condicionarConsulta($data -> id, "b.id_user", 0) . $this -> inBackups($backups) . " GROUP BY ". $this -> namesColumns($this -> a -> columnsTableIndexUnique, "ba.") ." HAVING COUNT( * ) >= $this->having_Count ORDER BY ba.id_backup DESC  limit $this->pagina, $this->limit_Inconsistencia";
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
    public function verifyExistsNameAccount($newAccount, $isUpdate = false, $id_account = 0) {
        $arreglo = array();
        $arreglo["error"] = false;
        $isDifferentId_Account = true;
        if ($isUpdate) $isDifferentId_Account = ($newAccount -> id_account != $id_account);
        if ($isDifferentId_Account) {
            $result = $this -> a -> mostrar("id_backup = $newAccount->id_backup AND id_account = $newAccount->id_account");
            if ($result) {
                $arreglo["error"] = true;
                $arreglo["titulo"] = "¡ REGISTRO EXISTENTE !";
                $arreglo["msj"] = "NO se puede " . (($isUpdate) ? "actualizar la" : "registrar la nueva") . " nueva cuenta, puesto que ya existe un registro en la BD con el mismo ID_ACCOUNT del mismo backup. Porfavor verifique el id e intente cambiarlo";
                return $arreglo;
            }
        }

        $arreglo["sqlVerfiyIndexUnique"] = $this -> conditionVerifyExistsUniqueIndex($newAccount, $this -> a -> columnsTableIndexUnique);
        $result = $this -> a -> mostrar($arreglo["sqlVerfiyIndexUnique"]);
        if ($result) {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ REGISTRO EXISTENTE !";
            $arreglo["msj"] = "NO se puede " . (($isUpdate) ? "actualizar la" : "registrar la nueva") . " cuenta, puesto que ya existe un registro en la BD con el mismo nombre del mismo backup. Porfavor cambie l nombre y vuleva a intentarlo";

        }
        return $arreglo;
    }
    public function agregarAccount() {
        $account = json_decode(Form::getValue("account", false, false));
        $arreglo = array();

        // --- Verifiy Index --- //
        $arreglo = $this -> verifyExistsNameAccount($account);
        if ($arreglo["error"]) return $arreglo;
        // --- Verifiy Index --- //

        $insert = $this -> a -> agregar($account);
        if ($insert) {
            $arreglo["error"] = false;
            $arreglo["titulo"] = "¡ ACCOUNT AGREGADO !";
            $arreglo["msj"] = "Se agrego correctamente la cuenta : $account->name del Respaldo con id_backup: $account->id_backup";

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
        $indexUnique = json_decode(Form::getValue("indexUnique", false, false));
        $arreglo = array();

        if (($account -> id_account != $indexUnique -> id_account)
            || (strtoupper($account -> name) != strtoupper($indexUnique -> name))) {
            $arreglo = $this -> verifyExistsNameAccount($account, true, $indexUnique -> id_account);
            if ($arreglo["error"]) return $arreglo;
        }
        $update = $this -> a -> actualizar($account, $indexUnique);
        if ($update) {
            $arreglo["error"] = false;
            $arreglo["titulo"] = "¡ ACCOUNT ACTUALIZADO !";
            $arreglo["msj"] = "La Cuenta con id_account: $indexUnique->id_account del Respaldo con id_backup: $indexUnique->id_backup se actualizo correctamente";

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
            $arreglo["msj"] = "La Cuenta con id_account: $indexUnique->id_account del Respaldo con id_backup: $indexUnique->id_backup no se actualizo correctamente";
        }
        return $arreglo;
    }
    public function eliminarAccount() {
        $indexUnique = json_decode(Form::getValue("indexUnique", false, false));
        $arreglo = array();
        $delete = $this -> a -> eliminar($indexUnique);
        if ($delete) {
            $arreglo["error"] = false;
            $arreglo["titulo"] = "¡ ACCOUNT ELIMINADA !";
            $arreglo["msj"] = "La cuenta con id_account: $indexUnique->id_account del Respaldo con id_backup: $indexUnique->id_backup ha sido eliminado correctamente";
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ ACCOUNT NO ELIMINADA !";
            $arreglo["msj"] = "La cuenta con id_account: $indexUnique->id_account del Respaldo con id_backup: $indexUnique->id_backup no ha sido eliminado correctamente";
        }
        return $arreglo;
    }

}