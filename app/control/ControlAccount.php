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
    private $pk_Account = array();
    private $ctrlCategory;
    private $ctrlCurrency;

    public function __construct($id_backup = 0, $categorieSearch = 1, $symbolName = 1)
    {
        $this -> a = new Account();
        $this -> pk_Account["id_backup"] = $id_backup;
        $this -> categorieSearch = $categorieSearch;
        $this -> symbolName = $symbolName;
    }

    public function getAccountModel() {
        return $this -> a;
    }

    public function obtAccountsBackup($isQuery = true, $signCategories = "both") {
        if ($isQuery) {
            $this -> pk_Account["id_backup"] = Form::getValue("id_backup");
            $this -> categorieSearch = Form::getValue("categoriesSearch");
            $signCategories = Form::getValue("signCategories");
            if ($signCategories != "both") {
                $signCategories = ($signCategories == 0) ? "'-'": "'+'";
            }
        }
        $arreglo = array();
        $this -> select = "id_account, name, sign, iso_code";
        $this -> where = "id_backup = " . $this -> pk_Account["id_backup"] . " GROUP BY " . $this -> namesColumns($this -> a -> columnsTableIndexUnique, "") . " HAVING COUNT( * ) >= 1 ORDER BY id_account";
        $accountsBackup = $this -> a -> mostrar($this -> where, $this -> select);
        if ($accountsBackup) {
            $arreglo["accounts"] = $accountsBackup;
            if ($this -> categorieSearch == 1) {
                $arrayAccounts = [];
                $this -> ctrlCategory = new ControlCategory($this -> pk_Account["id_backup"]);
                foreach ($accountsBackup as $key => $value) {
                    $this -> ctrlCategory -> setId_Account($value -> id_account);
                    $categoriesAccount = $this -> ctrlCategory -> obtCategoriesAccountBackup(false, $signCategories);

                    $arrayAccounts[$key]["id_account"] = $value -> id_account;
                    $arrayAccounts[$key]["name"] = $value -> name;
                    $arrayAccounts[$key]["iso_code"] = $value -> iso_code;
                    $arrayAccounts[$key]["categoriesAccount"] = (!$categoriesAccount["error"]) ? $categoriesAccount["categories"]: [];

                }
                $arreglo["accounts"] = $arrayAccounts;
            }

            $arreglo["error"] = false;
            $arreglo["titulo"] = "¡ Accounts encontrados !";
            $arreglo["msj"] = "Se encontraron cuentas con " . $this -> keyValueArray($this -> pk_Account);
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ Accounts no encontrados !";
            $arreglo["msj"] = "No se encontraron cuentas con " . $this -> keyValueArray($this -> pk_Account);
        }
        //sleep(5);
        return $arreglo;
    }

    public function buscarAccountsBackup($isQuery = true, $isExport = false, $typeExport = "sqlite") {
        if ($isQuery) {
            $this->pk_Account["id_backup"] = Form::getValue('idBack');
            $this->pagina = Form::getValue("pagina");
            $this->symbolName = Form::getValue("symbolName");
            $this->pagina = $this->pagina * $this->limit;
        }

        if ($isExport)
            if ($typeExport == "sqlite")
                $this -> select = "ba.id_account as _id, ba.name as account, ba.detail, ba.initial_balance, ba.sign, ba.icon_name as icon, ba.income, ba.expense, ba.final_balance as balance, ba.month, ba.year, ba.negative_max, ba.positive_max, ba.iso_code, ba.rate, ba.include_total, ba.value_type, ba.selected";
            else
                $this -> select = "ba.name as account, ba.detail, ba.initial_balance, ba.sign, ba.income, ba.expense, ba.final_balance as balance, ba.iso_code, (SELECT symbolCurrency(" . $this -> pk_Account["id_backup"] . ", ba.iso_code, 0)) as symbol";
        else
            $this -> select = "ba.*, (SELECT symbolCurrency(" . $this -> pk_Account["id_backup"] . ", ba.iso_code, 0)) as symbol, COUNT(ba.id_backup) repeated";
        $this -> table = $this -> a -> nameTable . " ba";
        $this -> where = (($isQuery || $isExport) ? "ba.id_backup = " . $this -> pk_Account["id_backup"] : $this -> conditionVerifyExistsUniqueIndex($this -> pk_Account, $this -> a -> columnsTableIndexUnique, false, "ba.") . " AND ba.id_account = " . $this -> pk_Account["id_account"]) . " GROUP BY " . $this -> namesColumns($this -> a -> columnsTableIndexUnique, "ba.") . " HAVING COUNT( * ) >= 1 ORDER BY ba.id_account " . (($isQuery) ? "limit $this->pagina,$this->limit": "");

        $select = $this -> a -> mostrar($this -> where, $this -> select, $this -> table);
        $arreglo = array();
        $arreglo["consultaSQL"] = $this -> consultaSQL($this -> select, $this -> table, $this -> where);

        if ($select) {
            $arreglo["error"] = false;
            $arreglo["accounts"] = $select;
            $arreglo["titulo"] = ($isQuery) ? "¡ Accounts encontrados !" : "¡ Account encontrado !";
            $arreglo["msj"] = (($isQuery) ? "Se encontraron cuentas con " : "Se recupero la Cuenta con ") . $this -> keyValueArray($this -> pk_Account);
            if ($isQuery && $this -> pagina == 0) {
                $this -> categorieSearch = 0;
                $arreglo["accountsBackup"] = $this -> obtAccountsBackup(false);
                // $arreglo["currenciesJSON"] = $this -> currency -> Currencies;
                if ($this -> symbolName == 1) {
                    $this -> ctrlCurrency = new ControlCurrency($this -> pk_Account["id_backup"], 1);
                    $arreglo["currencies"] = $this -> ctrlCurrency -> obtCurrenciesGralBackup(false);
                }
            }
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = ($isQuery) ?"¡ Accounts no encontrados !" : "¡ Account no encontrado !";
            $arreglo["msj"] = (($isQuery) ? "No se encontraron cuentas con ": "No se recupero la Cuenta con ") . $this -> keyValueArray($this -> pk_Account);
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
        $this -> select = "ba.*, (SELECT symbolCurrency(ba.id_backup, ba.iso_code, 0)) as symbol, COUNT(ba.id_backup) repeated";
        $this -> table = $this -> a -> nameTable . " ba, backups b";
        $this -> where = "b.id_backup = ba.id_backup ". $this -> condicionarConsulta($data -> id, "b.id_user", 0) . $this -> inBackups($backups) . " GROUP BY ". $this -> namesColumns($this -> a -> columnsTableIndexUnique, "ba.") ." HAVING COUNT( * ) >= $this->having_Count ORDER BY ba.id_backup DESC  limit $this->pagina, $this->limit_Inconsistencia";
        $arreglo["consultaSQL"] = $this -> consultaSQL($this -> select, $this -> table, $this -> where);
        $consulta = $this -> a -> mostrar($this -> where, $this -> select, $this -> table);
        if ($consulta) {
            $arreglo["error"] = false;
            $arreglo["accounts"] = $consulta;
            $arreglo["titulo"] = "¡ Inconsistencias encontradas !";
            $arreglo["msj"] = "Se encontraron incosistencias de registros en la tabla Accounts ". (($data -> email != "Generales") ? "del usuario: $data->email" : "");
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ Inconsistencias no encontradas !";
            $arreglo["msj"] = "No se encontraron incosistencias de registros en la tabla Accounts ". (($data -> email != "Generales") ? "del usuario: $data->email" : "");
        }
        return $arreglo;
    }

    public function obtSizeTable() {
        $this -> verificarPermiso(PERMISO_MNTINCONSISTENCIA);
        $arreglo = array();
        $exixstIndexUnique = $this -> a -> verifyIfExistsIndexUnique($this -> a -> nameTable);
        if ($exixstIndexUnique["indice"]) {
            return $arreglo = $exixstIndexUnique;
        }
        $size = $this -> a -> sizeTable($this -> a -> nameTable);
        if ($size) {
            $arreglo["size"] = $size[0];
            $arreglo["error"] = false;
            $arreglo["titulo"] = "¡ Tamaño calculado !";
            $arreglo["msj"] = "Se calculo correctamente el tamaño de la tabla de datos: " . $this -> a -> nameTable;
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ Tamaño no calculado !";
            $arreglo["msj"] = "No se pudo calcular correctamente el tamaño de la tabla de datos: " . $this -> a -> nameTable;
        }
        return $arreglo;
    }

    public function corregirInconsistenciaRegistro() {
        $this -> verificarPermiso(PERMISO_MNTINCONSISTENCIA);

        $indexUnique = json_decode(Form::getValue("indexUnique", false, false));
        $arreglo = array();
        $this -> pk_Account["id_backup"] = $indexUnique -> id_backup;
        $this -> pk_Account["id_account"] = $indexUnique -> id_account;
        $this -> pk_Account["name"] = $indexUnique -> name;
        $account = $this -> buscarAccountsBackup(false);
        //var_dump($account); return;
        if ($account) {
            $correcion = $this -> a -> eliminar($indexUnique);
            if ($correcion) {
                $insertAccount = $this -> a -> agregar($account["accounts"][0]);
                if ($insertAccount) {
                    $arreglo["error"] = false;
                    $arreglo["titulo"] = "¡ Cuenta corregida !";
                    $arreglo["msj"] = "Se corrigio correcamente la cuenta con " . $this -> keyValueArray($this -> pk_Account);
                    $arreglo["account"] = $this -> buscarAccountsBackup(false);
                } else {
                    $arreglo["error"] = true;
                    $arreglo["titulo"] = "¡ Error al corregir !";
                    $arreglo["msj"] = "No se pudo corregir la inconsistencia del registro seleccionado. -- 2° Proceso Insertar --";
                }
            } else {
                $arreglo["error"] = true;
                $arreglo["titulo"] = "¡ Error al corregir !";
                $arreglo["msj"] = "No se pudo corregir la inconsistencia del registro seleccionado. -- 1° Proceso Eliminar --";
            }
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ Error de consulta  !";
            $arreglo["msj"] = "Error al obtner los datos de la cuenta seleccionada para corregir";
        }
        return $arreglo;
    }
    public function corregirInconsitencia() {
        $this -> verificarPermiso(PERMISO_MNTINCONSISTENCIA);

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
        $this -> pk_Account["id_backup"] = Form::getValue("idBack");
        $arreglo = array();
        $query = $this -> a -> mostrar("id_backup = " . $this -> pk_Account["id_backup"], "max(id_account) as max");
        if ($query) {
            $newId_Account = $query[0] -> max + 1;
            $arreglo["newId_account"] = $newId_Account;
            $arreglo["error"] = false;
            $arreglo["titulo"] = "¡ Id Account calculado !";
            $arreglo["msj"] = "Se calculo correctamente el id_account de la nueva cuenta a ingresar";
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ Id Account no calculado !";
            $arreglo["msj"] = "No se calculo correctamente el id_account de la nueva cuenta a ingresar";
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
                $arreglo["titulo"] = "¡ Registro existente !";
                $arreglo["msj"] = "NO se puede " . (($isUpdate) ? "actualizar la" : "registrar la nueva") . " cuenta, puesto que ya existe un registro en la BD con el mismo ID_ACCOUNT del mismo backup. Porfavor verifique el id e intente cambiarlo";
                return $arreglo;
            }
        }

        $arreglo["sqlVerfiyIndexUnique"] = $this -> conditionVerifyExistsUniqueIndex($newAccount, $this -> a -> columnsTableIndexUnique);
        $result = $this -> a -> mostrar($arreglo["sqlVerfiyIndexUnique"]);
        if ($result) {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ Registro existente !";
            $arreglo["msj"] = "NO se puede " . (($isUpdate) ? "actualizar la" : "registrar la nueva") . " cuenta, puesto que ya existe un registro en la BD con el mismo NOMBRE del mismo backup. Porfavor cambie el nombre y vuleva a intentarlo";
        }
        return $arreglo;
    }
    public function agregarAccount() {
        $this -> verificarPermiso(PERMISO_INSERT);

        $account = json_decode(Form::getValue("account", false, false));
        $arreglo = array();

        $arreglo = $this -> verifyExistsNameAccount($account);
        if ($arreglo["error"]) return $arreglo;

        $insert = $this -> a -> agregar($account);
        if ($insert) {
            $this -> pk_Account["id_backup"] = $account -> id_backup;
            $this -> pk_Account["id_account"] = $account -> id_account;
            $this -> pk_Account["name"] = $account -> name;
            $queryAccountNew = $this -> buscarAccountsBackup(false);
            $arreglo["account"]["error"] =  $queryAccountNew["error"];
            $arreglo["account"]["titulo"] = $queryAccountNew["titulo"];
            $arreglo["account"]["msj"] = $queryAccountNew["msj"];
            if (!$arreglo["account"]["error"])
                $arreglo["account"]["new"] = $queryAccountNew["accounts"][0];

            $arreglo["error"] = false;
            $arreglo["titulo"] = "¡ Account agregado !";
            $arreglo["msj"] = "Se agrego correctamente la cuenta con " . $this -> keyValueArray($this -> pk_Account);
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ Account no agregado !";
            $arreglo["msj"] = "Ocurrio un error al intentar agregar la cuenta con " . $this -> keyValueArray($account);
        }
        return $arreglo;
    }
    public function actualizarAccount() {
        $this -> verificarPermiso(PERMISO_UPDATE);

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
            $arreglo["titulo"] = "¡ Account actualizado !";
            $arreglo["msj"] = "La cuenta con " . $this -> keyValueArray($indexUnique) . " se ha actualizado correctamente";

            $this -> pk_Account["id_backup"] = $account -> id_backup;
            $this -> pk_Account["id_account"] = $account -> id_account;
            $this -> pk_Account["name"] = $account -> name;

            $queryAccountUpdate = $this -> buscarAccountsBackup(false);
            $arreglo["account"]["error"] =  $queryAccountUpdate["error"];
            $arreglo["account"]["titulo"] = $queryAccountUpdate["titulo"];
            $arreglo["account"]["msj"] = $queryAccountUpdate["msj"];
            if (!$arreglo["account"]["error"])
                $arreglo["account"]["update"] = $queryAccountUpdate["accounts"][0];
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ Account no actualizado !";
            $arreglo["msj"] = "Ocurrio un error al intentar actualizar la cuenta con " . $this -> keyValueArray($indexUnique);
        }
        return $arreglo;
    }
    public function eliminarAccount() {
        $this -> verificarPermiso(PERMISO_DELETE);

        $indexUnique = json_decode(Form::getValue("indexUnique", false, false));
        $arreglo = array();
        $delete = $this -> a -> eliminar($indexUnique);
        if ($delete) {
            $arreglo["error"] = false;
            $arreglo["titulo"] = "¡ Account eliminada !";
            $arreglo["msj"] = "La cuenta con " . $this -> keyValueArray($indexUnique) . " ha sido eliminado correctamente";
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ Account no eliminada !";
            $arreglo["msj"] = "Ocurrio un error al intentar eliminar la cuenta con " . $this -> keyValueArray($indexUnique);
        }
        return $arreglo;
    }

}