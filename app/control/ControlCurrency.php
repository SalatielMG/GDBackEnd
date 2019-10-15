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
    public function insertCurrencies() {
        $arreglo = array();
        $result = $this -> c -> mostrar("1", "*", "table_currencies");
        if (!$result) { // No tiene regitros => Hay que ingresarlos
            $insert = $this -> c -> agregarCurrencies();
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
    public function buscarCurrenciesBackup($isQuery = true) {
        if ($isQuery) {
            $this -> pk_Currency["id_backup"] = Form::getValue("id_backup");
            $this -> isCurrenciesAccount = Form::getValue("isCurrenciesAccount");
            if ($this -> isCurrenciesAccount == 0) {
                $this -> pagina = Form::getValue("pagina");
                $this -> pagina = $this -> pagina * $this -> limit;
            }
        }
        $this -> select = ($this -> isCurrenciesAccount == 1) ? "iso_code, symbol" : "*, count(id_backup) as repeated";
        $this -> where = "id_backup = " . $this -> pk_Currency["id_backup"] . " GROUP BY " . $this -> namesColumns($this -> c -> nameColumnsIndexUnique, "") . " HAVING COUNT( * ) >= 1 " . (($isQuery && $this -> isCurrenciesAccount == 0) ? "limit $this->pagina,$this->limit": "");

        $select = $this -> c -> mostrar($this -> where, $this -> select);
        $arreglo = array();
        if ($select) {
            $arreglo["error"] = false;
            $arreglo["currenciesSelected"] = $select;
            $arreglo["titulo"] = "¡ CURRENCIES ENCONTRADOS !";
            $arreglo["msj"] = "Se encontraron currencies del respaldo con id_backup: " . $this -> pk_Currency["id_backup"];

            if ($this -> isCurrenciesAccount == 1) {
                $where = "(";
                foreach ($select as $key => $value) {
                    $where .= "'$value->iso_code',";
                }
                $where = substr_replace($where, ")", strlen($where) - 1);
                $this -> where = "iso_code NOT IN $where";
                $currencies = $this -> c -> mostrar($this -> where, $this -> select, "table_currencies");
                $currencies = array_merge($currencies, $select);
                sort($currencies);
                $arreglo["currencies"] = $currencies;
            }
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
        $where = "b.id_backup = bc.id_backup " . $this -> condicionarConsulta($data -> id, "b.id_user", 0) . $this -> inBackups($backups, "bc.id_backup") . " GROUP BY ". $this -> namesColumns($this -> c -> nameColumnsIndexUnique, "bc.") ." HAVING COUNT( * ) >= $this->having_Count limit $this->pagina, $this->limit_Inconsistencia";
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
        $sql = $this -> sentenciaInconsistenicaSQL($this -> c -> nameTable, $this -> c -> nameColumnsIndexUnique, "id_backup");
        $operacion = $this -> c -> ejecutarMultSentMySQLi($sql);
        $arreglo["SenteciasSQL"] = $sql;
        $arreglo["Result"] = $operacion;
        return $arreglo;
    }
}