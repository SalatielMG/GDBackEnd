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
    private $pk_Preference = array();
    private $where = "";
    private $select = "";
    private $table = "";
    
    public function __construct($id_backup = 0)
    {
        $this -> p = new Preference();
        $this -> pk_Preference["id_backup"] = $id_backup;
    }
    public function getPreferenceModel() {
        return $this -> p;
    }
    public function buscarPreferencesBackup($isQuery = true, $isExport = false) {
        $arreglo = array();
        if ($isQuery) {
            $this -> pk_Preference["id_backup"] = Form::getValue('id_backup');
            $this -> pagina = Form::getValue("pagina");
            $this -> pagina = $this -> pagina * $this -> limit;
        }
        if ($isExport)
            $this -> select = "key_name, value";
        else
            $this -> select = "*, COUNT(key_name) repeated";
        $this -> where = (($isQuery || $isExport) ? "id_backup = " . $this -> pk_Preference["id_backup"] : $this -> conditionVerifyExistsUniqueIndex($this -> pk_Preference, $this -> p -> columnsTableIndexUnique, false)) . " GROUP BY " . $this -> namesColumns($this -> p -> columnsTableIndexUnique) . " HAVING COUNT( * ) >= 1 " . (($isQuery) ? "limit $this->pagina, $this->limit" : "" );

        $arreglo["consultaSQL"] = $this -> consultaSQL($this -> select, $this -> table, $this -> where);
        //return $arreglo;
        $select = $this -> p -> mostrar($this -> where , $this -> select);

        if ($select) {
            $arreglo["error"] = false;
            $arreglo["preferences"] = $select;
            $arreglo["titulo"] = ($isQuery) ? "¡ Preferences Ecnontrados !" : "¡ Preference encontrado !";
            $arreglo["msj"] = (($isQuery) ? "Se encontraron preferencias con " : "Se encontro preferencia con ") . $this -> keyValueArray($this -> pk_Preference);
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = ($isQuery) ? "¡ Preferences no Ecnontrados !" : "¡ Preference no encontrado !";
            $arreglo["msj"] = (($isQuery) ? "No se encontraron preferencias con " : "No se encontro preferencia con ") . $this -> keyValueArray($this -> pk_Preference);
        }
        return $arreglo;
    }
    public function inconsistenciaPreference(){
        $data = json_decode(Form::getValue('dataUser', false, false));
        $this -> pagina = Form::getValue('pagina');
        $backups = json_decode(Form::getValue('backups', false, false));
        $arreglo = array();

        $this -> pagina = $this -> pagina * $this -> limit_Inconsistencia;
        $this -> select = "bp.*, COUNT(bp.id_backup) repeated";
        $this -> table = $this -> p -> nameTable . " bp, backups b";
        $this -> where = "b.id_backup = bp.id_backup " . $this -> condicionarConsulta($data -> id, "b.id_user", 0) . $this -> inBackups($backups, "bp.id_backup") . " GROUP BY ". $this -> namesColumns($this -> p -> columnsTableIndexUnique, "bp.") ." HAVING COUNT( * ) >= $this->having_Count limit $this->pagina, $this->limit_Inconsistencia";

        $arreglo["consultaSQL"] = $this -> consultaSQL($this -> select, $this -> table, $this -> where);
        //return $arreglo;
        $consulta = $this -> p -> mostrar($this -> where, $this -> select, $this -> table);
        if ($consulta) {
            $arreglo["error"] = false;
            $arreglo["preferences"] = $consulta;
            $arreglo["titulo"] = "¡ Inconsistencias encontrados !";
            $arreglo["msj"] = "Se encontraron inconsistencias de registros en la tabla Preference ". (($data -> email != "Generales") ? "del usuario: $data->email" : "");
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ Inconsistencias no encontrados !";
            $arreglo["msj"] = "No se encontraron inconsistencias de registros en la tabla Preference ". (($data -> email != "Generales") ? "del usuario: $data->email" : "");
        }
        return $arreglo;
    }
    public function corregirInconsitencia() {
        $this -> verificarPermiso(PERMISO_MNTINCONSISTENCIA);

        $arreglo = array();
        $exixstIndexUnique = $this -> p -> verifyIfExistsIndexUnique($this -> p -> nameTable);
        if ($exixstIndexUnique["indice"]) {
            return $arreglo = $exixstIndexUnique;
        }
        $sql = $this -> sentenciaInconsistenicaSQL($this -> p -> nameTable, $this -> p -> columnsTableIndexUnique, "id_backup");
        $operacion = $this -> p -> ejecutarMultSentMySQLi($sql);
        $arreglo["SenteciasSQL"] = $sql;
        $arreglo["Result"] = $operacion;
        return $arreglo;
    }
    public function verifyExistsIndexUnique ($newPreference, $isUpdate = false) {
        $arreglo = array();
        $arreglo["error"] = false;
        $arreglo["sqlVerfiyIndexUnique"] = $this -> conditionVerifyExistsUniqueIndex($newPreference, $this -> p -> columnsTableIndexUnique);
        $result = $this -> p -> mostrar( $arreglo["sqlVerfiyIndexUnique"]);
        if ($result) {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ Registro existente !";
            $arreglo["msj"] = "NO se puede " . (($isUpdate) ? "actualizar la" : "registrar la nueva") . " Preferencia, porque ya existe un registro en la BD con el mismo KEY_NAME del mismo backup. Porfavor verifique y vuelva a intentarlo";
        }
        return $arreglo;
    }
    public function agregarPreference () {
        $this -> verificarPermiso(PERMISO_INSERT);

        $preference = json_decode(Form::getValue("preference", false, false));
        $arreglo = array();
        $arreglo = $this -> verifyExistsIndexUnique($preference);
        if ($arreglo["error"]) return $arreglo;
        $insert = $this -> p -> agregar($preference);

        if ($insert) {
            $this -> pk_Preference["id_backup"] = $preference -> id_backup;
            $this -> pk_Preference["key_name"] = $preference -> key_name;
            $queryPreferenceNew = $this -> buscarPreferencesBackup(false);
            $arreglo["preference"]["error"] = $queryPreferenceNew["error"];
            $arreglo["preference"]["titulo"] = $queryPreferenceNew["titulo"];
            $arreglo["preference"]["msj"] = $queryPreferenceNew["msj"];
            if (!$arreglo["preference"]["error"]) $arreglo["preference"]["new"] = $queryPreferenceNew["preferences"][0];
            $arreglo["error"] = false;
            $arreglo["titulo"] = "¡ Preferencia agregado !";
            $arreglo["msj"] = "Se agrego correctamente la nueva Preferencia con " . $this -> keyValueArray($this -> pk_Preference);
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ Preferencia no agregado !";
            $arreglo["msj"] = "Ocurrio un error al ingresar la nueva Preferencia con " . $this -> keyValueArray($this -> pk_Preference);
        }
        return $arreglo;
    }
    public function actualizarPreference () {
        $this -> verificarPermiso(PERMISO_UPDATE);

        $preference = json_decode(Form::getValue("preference", false, false));
        $indexUnique = json_decode(Form::getValue("indexUnique", false, false));
        $arreglo = array();

        if (($preference -> id_backup != $indexUnique -> id_backup)
            || ($preference -> key_name != $indexUnique -> key_name))
        {
            $arreglo = $this -> verifyExistsIndexUnique($preference, true);
            if ($arreglo["error"]) return $arreglo;
        }
        $update = $this -> p -> actualizar($preference, $indexUnique);
        
        if ($update) {
            $this -> pk_Preference["id_backup"] = $preference -> id_backup;
            $this -> pk_Preference["key_name"] = $preference -> key_name;
            $queryPreferenceUpdate = $this -> buscarPreferencesBackup(false);
            $arreglo["preference"]["error"] = $queryPreferenceUpdate["error"];
            $arreglo["preference"]["titulo"] = $queryPreferenceUpdate["titulo"];
            $arreglo["preference"]["msj"] = $queryPreferenceUpdate["msj"];
            if (!$arreglo["preference"]["error"]) $arreglo["preference"]["update"] = $queryPreferenceUpdate["preferences"][0];
            $arreglo["error"] = false;
            $arreglo["titulo"] = "¡ Prefrencia agregado !";
            $arreglo["msj"] = "Se actualizo correctamente la Preferencia con " . $this -> keyValueArray($indexUnique);
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ Prefrencia no agregado !";
            $arreglo["msj"] = "Ocurrio un error al intentar actualizar la Preferencia con " . $this -> keyValueArray($indexUnique);
        }
        return $arreglo;
    }
    public function eliminarPreference () {
        $this -> verificarPermiso(PERMISO_DELETE);

        $indexUnique = json_decode(Form::getValue("indexUnique", false, false));
        $arreglo = array();
        $delete = $this -> p -> eliminar($indexUnique);
        if ($delete) {
            $arreglo["error"] = false;
            $arreglo["titulo"] = "¡ Preferncia eliminada !";
            $arreglo["msj"] = "La preferencia con " . $this -> keyValueArray($indexUnique) . " ha sido eliminado correctamente";
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ Preferncia no eliminada !";
            $arreglo["msj"] = "Ocurrio un error al intentar eliminar la preferencia con " . $this -> keyValueArray($indexUnique);
        }
        return $arreglo;
    }

}