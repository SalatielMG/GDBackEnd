<?php
/**
 * Created by PhpStorm.
 * User: pc-01
 * Date: 21/08/2019
 * Time: 14:32
 */
require_once (APP_PATH."model/Currency.php");
class ControlCurrency extends Valida
{
    private $c;
    private $pagina = 0;
    private $isCurrenciesAccount = 0;
    private $select = "";
    private $table = "";
    private $where = "";

    private $pk_Currency = array();

    public function __construct($id_backup = 0, $isCurrencyAccount = 0)
    {
        $this -> c = new Currency();
        $this -> pk_Currency["id_backup"] = $id_backup;
        $this -> isCurrenciesAccount = $isCurrencyAccount;
    }
    public function getCurrencyModel() {
        return $this -> c;
    }
    public function insertCurrencies() {
        $insert = $this -> c -> agregarCurrencies();
        var_dump($insert);
        exit();
        $arreglo = array();
        $result = $this -> c -> mostrar("1", "*", "table_currencies");
        if (!$result) { // No tiene regitros => Hay que ingresarlos
            $insert = false;
            if ($insert) {
                $arreglo["error"] = false;
                $arreglo["titulo"] = "¡ DATOS AGREGADOS !";
                $arreglo["msj"] = "Se agregaron correctamente los registros a la tabla table_currencies";
            } else {
                $arreglo["error"] = true;
                $arreglo["titulo"] = "¡ ERROR INSERT !";
                $arreglo["msj"] = "Ocurrio un error al intentar agregar los registros a la tabla table_currencies";
            }
        } else { // Ya tiene registros
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ TABLA CON REGISTROS !";
            $arreglo["msj"] = "No se puede realizar las insercciones de datos, puesto que ya existen los registros en la tabla: table_currencies";
        }
        return $arreglo;
    }
    private function obtCurrenciesInTableCurrecies($notIn_ISo_Code, $isExport = false, $typeExport = "sqlite") {
        $arreglo = array();
        if ($isExport)
            if ($typeExport == "sqlite")
                $this -> select = "iso_code, symbol, icon, selected";
            else
                $this -> select = "iso_code, symbol";
        else
            $this -> select = "iso_code, symbol, icon as icon_name, selected";
        $this -> table = "table_currencies";
        $this -> where = "iso_code NOT IN $notIn_ISo_Code";
        $select = $this -> c -> mostrar($this -> where, $this -> select, $this -> table);
        if ($select) {
            $arreglo["currencies"] = $select;
            $arreglo["error"] = false;
            $arreglo["titulo"] = "¡ CURRENCIES ENCONTRADOS !";
            $arreglo["msj"] = "Se encontraron currencies en la tabla Table_Currencies";
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ CURRENCIES NO ENCONTRADOS !";
            $arreglo["msj"] = "No se encontraron currencies en la tabla Table_Currencies";
        }
        return $arreglo;
    }
    public function obtCurrenciesGralBackup($isQuery = true, $isExport = false, $typeExport = "sqlite") {
        if ($isQuery) {
            $this -> pk_Currency["id_backup"] = Form::getValue("id_backup");
        }
        $arreglo = array();
        if ($isExport)
            if ($typeExport == "sqlite")
                $this -> select = "iso_code, symbol, icon_name as icon, selected";
            else
                $this -> select = "iso_code, symbol";
        else
            $this -> select = "iso_code, symbol, icon_name, selected";
        $this -> where = "id_backup = " . $this -> pk_Currency["id_backup"] . " GROUP BY " . $this -> namesColumns($this -> c -> columnsTableIndexUnique, "") . " HAVING COUNT( * ) >= 1 ";
        $select = $this -> c -> mostrar($this -> where, $this -> select);
        $this -> where = "('')";
        if (count($select) > 0) {
            $this -> where = "(";
            foreach ($select as $key => $value) {
                $this -> where .= "'$value->iso_code',";
            }
            $this -> where = substr_replace($this -> where, ")", strlen($this -> where) - 1);
        }
        $currencies = $this -> obtCurrenciesInTableCurrecies($this -> where, $isExport, $typeExport);
        if (!$currencies["error"]) {
            $currencies = array_merge($currencies["currencies"], $select);
            sort($currencies);
        } else {
            $currencies = [];
        }
        $arreglo["currencies"] = $currencies;
        if ($select) {
            $arreglo["error"] = false;
            $arreglo["currenciesSelected"] = $select;
            $arreglo["titulo"] = "¡ CURRENCIES ENCONTRADOS !";
            $arreglo["msj"] = "Se encontraron currencies del respaldo con " . $this -> keyValueArray($this -> pk_Currency);
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ CURRENCIES NO ENCONTRADOS !";
            $arreglo["msj"] = "No se encontraron currencies del respaldo con " . $this -> keyValueArray($this -> pk_Currency);
        }
        return $arreglo;
    }
    public function buscarCurrenciesBackup($isQuery = true) {
        $arreglo = array();
        if ($isQuery) {
            $this -> pk_Currency["id_backup"] = Form::getValue("id_backup");
            $this -> pagina = Form::getValue("pagina");
            $this -> pagina = $this -> pagina * $this -> limit;
        }
        $exixstIndexUnique = $this -> c -> verifyIfExistsIndexUnique($this -> c -> nameTable);
        if ($exixstIndexUnique["indice"]) {

        } else {
            $this -> select = "*, count(id_backup) as repeated";
            $this -> where = (($isQuery) ? "id_backup = " . $this -> pk_Currency["id_backup"] : $this -> conditionVerifyExistsUniqueIndex($this -> pk_Currency, $this -> c -> columnsTableIndexUnique, false)) . " GROUP BY " . $this -> namesColumns($this -> c -> columnsTableIndexUnique) . " HAVING COUNT( * ) >= 1 " . (($isQuery) ? "limit $this->pagina,$this->limit": "");
        }
        $arreglo["consultaSQL"] = $this -> consultaSQL($this -> select, $this -> table, $this -> where);
        $select = $this -> c -> mostrar($this -> where, $this -> select);
        if ($select) {
            $arreglo["error"] = false;
            $arreglo["currencies"] = $select;
            $arreglo["titulo"] = "¡ CURRENCIES ENCONTRADOS !";
            $arreglo["msj"] = "Se encontraron currencies del respaldo con id_backup: " . $this -> pk_Currency["id_backup"];
            if ($isQuery && $this -> pagina == 0)
                $arreglo["curreciesGralBackup"] = $this -> obtCurrenciesGralBackup(false);
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ CURRENCIES NO ENCONTRADOS !";
            $arreglo["msj"] = "No se encontraron currencies del respaldo con id_backup: " . $this -> pk_Currency["id_backup"];
        }
        return $arreglo;
    }

    public function inconsistenciaCurrency(){
        $data = json_decode(Form::getValue('dataUser', false, false));
        $this -> pagina = Form::getValue('pagina');
        $backups = json_decode(Form::getValue('backups', false, false));
        $arreglo = array();

        $this -> pagina = $this -> pagina * $this -> limit_Inconsistencia;
        $select = "bc.*, COUNT(bc.id_backup) cantidadRepetida";
        $table = "backup_currencies bc, backups b";
        $where = "b.id_backup = bc.id_backup " . $this -> condicionarConsulta($data -> id, "b.id_user", 0) . $this -> inBackups($backups, "bc.id_backup") . " GROUP BY ". $this -> namesColumns($this -> c -> columnsTableIndexUnique, "bc.") ." HAVING COUNT( * ) >= $this->having_Count limit $this->pagina, $this->limit_Inconsistencia";
        $arreglo["consultaSQL"] = $this -> consultaSQL($select, $table, $where);
        $consulta = $this -> c -> mostrar($where, $select, $table);
        if ($consulta) {
            $arreglo["error"] = false;
            $arreglo["currencies"] = $consulta;
            $arreglo["titulo"] = "¡ INCONSISTENCIAS ENCONTRADOS !";
            $arreglo["msj"] = "Se encontraron duplicidades de registros en la tabla Currency ". (($data -> email != "Generales") ? "del usuario: $data->email" : "");
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ INCONSISTENCIAS NO ENCONTRADOS !";
            $arreglo["msj"] = "No se encontraron duplicidades de registros en la tabla Currency ". (($data -> email != "Generales") ? "del usuario: $data->email" : "");
        }
        return $arreglo;
    }
    public function corregirInconsitencia() {
        $this -> verificarPermiso(PERMISO_MNTINCONSISTENCIA);

        $indices = $this -> c -> ejecutarCodigoSQL("SHOW INDEX from " . $this -> c -> nameTable);
        $arreglo = array();
        $arreglo["indice"] = false;
        foreach ($indices as $key => $value) {
            if ($value -> Key_name == "indiceUnico") { //Ya existe el indice unico... Entonces la tabla ya se encuentra corregida
                $arreglo["indice"] = true;
                $arreglo["msj"] = "Ya existe el campo unico en la tabla Currencies, por lo tanto ya se ha realizado la corrección de datos inconsistentes anteriormente.";
                $arreglo["titulo"] = "¡ TABLA CORREGIDA ANTERIORMENTE !";
                return $arreglo;
            }
        }
        $sql = $this -> sentenciaInconsistenicaSQL($this -> c -> nameTable, $this -> c -> columnsTableIndexUnique, "id_backup");
        $operacion = $this -> c -> ejecutarMultSentMySQLi($sql);
        $arreglo["SenteciasSQL"] = $sql;
        $arreglo["Result"] = $operacion;
        return $arreglo;
    }
    public function verifyExistsIndexUnique ($newCurrency, $isUpdate = false) {
        $arreglo = array();
        $arreglo["error"] = false;
        $arreglo["sqlVerfiyIndexUnique"] = $this -> conditionVerifyExistsUniqueIndex($newCurrency, $this -> c -> columnsTableIndexUnique);
        $result = $this -> c -> mostrar( $arreglo["sqlVerfiyIndexUnique"]);
        if ($result) {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ REGISTRO EXISTENTE !";
            $arreglo["msj"] = "NO se puede " . (($isUpdate) ? "actualizar la" : "registrar la nueva") . " Currency, porque ya existe un registro en la BD con el mismo iso_code del mismo backup. Porfavor verifique y vuelva a intentarlo";
        }
        return $arreglo;
    }
    public function agregarCurrency () {
        $this -> verificarPermiso(PERMISO_INSERT);

        $currency = json_decode(Form::getValue("currency", false, false));
        $arreglo = array();
        $arreglo = $this -> verifyExistsIndexUnique($currency);
        if ($arreglo["error"]) return $arreglo;
        $insert = $this -> c -> agregar($currency);
        if ($insert) {
            $this -> pk_Currency["id_backup"] = $currency -> id_backup;
            $this -> pk_Currency["iso_code"] = $currency -> iso_code;
            $queryCurrencyNew = $this -> buscarCurrenciesBackup(false);
            $arreglo["currency"]["error"] = $queryCurrencyNew["error"];
            $arreglo["currency"]["titulo"] = $queryCurrencyNew["titulo"];
            $arreglo["currency"]["msj"] = $queryCurrencyNew["msj"];
            if (!$arreglo["currency"]["error"]) $arreglo["currency"]["new"] = $queryCurrencyNew["currencies"][0];
            $arreglo["currenciesBackup"] = $this -> obtCurrenciesGralBackup(false);
            $arreglo["error"] = false;
            $arreglo["titulo"] = "¡ CURRENCY AGREGADA !";
            $arreglo["msj"] = "Se agrego correctamente la currency con " . $this -> keyValueArray($this -> pk_Currency);
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ CURRENCY NO AGREGADA !";
            $arreglo["msj"] = "Ocurrio un error al intentar ingresar la currency con " . $this -> keyValueArray($this -> pk_Currency);
        }
        return $arreglo;
    }
    public function actualizarCurrency () {
        $this -> verificarPermiso(PERMISO_UPDATE);

        $currency = json_decode(Form::getValue("currency", false, false));
        $indexUnique = json_decode(Form::getValue("indexUnique", false, false));
        $arreglo = array();
        if (($currency -> id_backup != $indexUnique -> id_backup)
            || ($currency -> iso_code != $indexUnique -> iso_code)) {
            $arreglo = $this -> verifyExistsIndexUnique($currency, true);
            if ($arreglo["error"]) return $arreglo;
        }
        $update = $this -> c -> actualizar($currency, $indexUnique);
        if ($update) {
            $arreglo["error"] = false;
            $arreglo["titulo"] = "¡ CURRENCY ACTUALIZADA !";
            $arreglo["msj"] = "La currency con " . $this -> keyValueArray($indexUnique) . " se ha actualizado correctamente";
            $this -> pk_Currency["id_backup"] = $currency -> id_backup;
            $this -> pk_Currency["iso_code"] = $currency -> iso_code;
            $queryCurrencyUpdate = $this -> buscarCurrenciesBackup(false);
            $arreglo["currency"]["error"] = $queryCurrencyUpdate["error"];
            $arreglo["currency"]["titulo"] = $queryCurrencyUpdate["titulo"];
            $arreglo["currency"]["msj"] = $queryCurrencyUpdate["msj"];
            if (!$arreglo["currency"]["error"]) $arreglo["currency"]["update"] = $queryCurrencyUpdate["currencies"][0];
            $arreglo["currenciesBackup"] = $this -> obtCurrenciesGralBackup(false);
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ CURRENCY NO ACTUALIZADA !";
            $arreglo["msj"] = "Ocurrio un error al intentar actualizar la currency con " . $this -> keyValueArray($indexUnique);
        }
        return $arreglo;
    }
    public function eliminarCurrency () {
        $this -> verificarPermiso(PERMISO_DELETE);

        $indexUnique = json_decode(Form::getValue("indexUnique", false, false));
        $arreglo = array();
        $delete = $this -> c -> eliminar($indexUnique);
        if ($delete) {
            $arreglo["error"] = false;
            $arreglo["titulo"] = "¡ CURRENCY ELIMINADA !";
            $arreglo["msj"] = "La currency con " . $this -> keyValueArray($indexUnique) . " ha sido eliminado correctamente";
            $this -> pk_Currency["id_backup"] = $indexUnique -> id_backup;
            $arreglo["currenciesBackup"] = $this -> obtCurrenciesGralBackup(false);
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ CURRENCY NO ELIMINADA !";
            $arreglo["msj"] = "Ocurrio un error al intentar eliminar la currency con " . $this -> keyValueArray($indexUnique);
        }
        return $arreglo;
    }
}