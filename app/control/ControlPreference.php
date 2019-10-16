<?php
/**
 * Created by PhpStorm.
 * User: pc-01
 * Date: 21/08/2019
 * Time: 14:47
 */
require_once (APP_PATH."model/Preference.php");

class ControlPreference extends Valida
{
    private $p;
    private $pagina = 0;
    public function __construct()
    {
        $this -> p = new Preference();
    }
    public function buscarPreferencesBackup() {
        $idBackup = Form::getValue('idBack');
        $select = $this -> p -> mostrar("id_backup = $idBackup");
        $arreglo = array();
        if ($select) {
            $arreglo["error"] = false;
            $arreglo["preferences"] = $select;
            $arreglo["titulo"] = "¡ PREFERNCES ENCONTRADOS !";
            $arreglo["msj"] = "Se encontraron preferences del respaldo solicitado.";
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ PREFERNCES NO ENCONTRADOS !";
            $arreglo["msj"] = "No se encontraron preferences del respaldo solicitado.";
        }
        return $arreglo;
    }
    public function inconsistenciaPreference(){
        $data = json_decode(Form::getValue('dataUser', false, false));
        $this -> pagina = Form::getValue('pagina');
        $backups = json_decode(Form::getValue('backups', false, false));
        $arreglo = array();

        $this -> pagina = $this -> pagina * $this -> limit_Inconsistencia;
        $select = "bp.*, COUNT(bp.id_backup) cantidadRepetida";
        $table = "backup_preferences bp, backups b";
        $where = "b.id_backup = bp.id_backup " . $this -> condicionarConsulta($data -> id, "b.id_user", 0) . $this -> inBackups($backups, "bp.id_backup") . " GROUP BY ". $this -> namesColumns($this -> p -> columnsTableIndexUnique, "bp.") ." HAVING COUNT( * ) >= $this->having_Count limit $this->pagina, $this->limit_Inconsistencia";
        $arreglo["consultaSQL"] = $this -> consultaSQL($select, $table, $where);
        $consulta = $this -> p -> mostrar($where, $select, $table);
        if ($consulta) {
            $arreglo["error"] = false;
            $arreglo["preferences"] = $consulta;
            $arreglo["titulo"] = "¡ INCONSISTENCIAS ENCONTRADOS !";
            $arreglo["msj"] = "Se encontraron duplicidades de registros en la tabla Preference ". (($data -> email != "Generales") ? "del usuario: $data->email" : "");
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ INCONSISTENCIAS NO ENCONTRADOS !";
            $arreglo["msj"] = "No se encontraron duplicidades de registros en la tabla Preference ". (($data -> email != "Generales") ? "del usuario: $data->email" : "");
        }
        return $arreglo;
    }
    public function corregirInconsitencia() {
        $indices = $this -> p -> ejecutarCodigoSQL("SHOW INDEX from " . $this -> p -> nameTable);
        $arreglo = array();
        $arreglo["indice"] = false;
        foreach ($indices as $key => $value) {
            if ($value -> Key_name == "indiceUnico") { //Ya existe el indice unico... Entonces la tabla ya se encuentra corregida
                $arreglo["indice"] = true;
                $arreglo["msj"] = "Ya existe el campo unico en la tabla Preference, por lo tanto ya se ha realizado la corrección de datos inconsistentes anteriormente.";
                $arreglo["titulo"] = "¡ TABLA CORREGIDA ANTERIORMENTE !";
                return $arreglo;
            }
        }
        $sql = $this -> sentenciaInconsistenicaSQL($this -> p -> nameTable, $this -> p -> columnsTableIndexUnique, "id_backup");
        $operacion = $this -> p -> ejecutarMultSentMySQLi($sql);
        $arreglo["SenteciasSQL"] = $sql;
        $arreglo["Result"] = $operacion;
        return $arreglo;
    }
}