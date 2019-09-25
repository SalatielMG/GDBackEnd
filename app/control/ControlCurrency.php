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
    public function __construct()
    {
        $this -> c = new Currency();
    }
    public function buscarCurrenciesBackup() {
        $idBackup = Form::getValue('idBack');
        $select = $this -> c -> mostrar("id_backup = $idBackup");
        $arreglo = array();
        if ($select) {
            $arreglo["error"] = false;
            $arreglo["currencies"] = $select;
            $arreglo["titulo"] = "¡ CURRENCIES ENCONTRADOS !";
            $arreglo["msj"] = "Se encontraron currencies del respaldo solicitado.";
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ CURRENCIES NO ENCONTRADOS !";
            $arreglo["msj"] = "No se encontraron currencies del respaldo solicitado.";
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
        $where = "b.id_backup = bc.id_backup " . $this -> condicionarConsulta($data -> id, "b.id_user", 0) . $this -> inBackups($backups, "bc.id_backup") . " GROUP BY ". $this -> namesColumns($this -> c -> nameColumns, "bc.") ." HAVING COUNT( * ) >= $this->having_Count limit $this->pagina, $this->limit_Inconsistencia";
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
        $sql = $this -> sentenciaInconsistenicaSQL($this -> c -> nameTable, ['id_backup','iso_code'], "id_backup");
        $operacion = $this -> c -> ejecutarMultSentMySQLi($sql);
        $arreglo["SenteciasSQL"] = $sql;
        $arreglo["Result"] = $operacion;
        return $arreglo;
    }
}