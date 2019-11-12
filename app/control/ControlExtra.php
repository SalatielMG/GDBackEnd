<?php
/**
 * Created by PhpStorm.
 * User: pc-01
 * Date: 21/08/2019
 * Time: 14:39
 */
require_once (APP_PATH."model/Extra.php");

class ControlExtra extends Valida
{
    private $e;
    private $pagina = 0;
    private $pk_Extra = array();
    private $where = "";
    private $select = "";
    private $table = "";

    public function __construct()
    {
        $this -> e = new Extra();
    }
    public function buscarExtrasBackup($isQuery = true) {
        $arreglo = array();
        if ($isQuery) {
            $this -> pk_Extra["id_backup"] = Form::getValue('id_backup');
            $this -> pagina = Form::getValue("pagina");
            $this -> pagina = $this -> pagina * $this -> limit;
        }
        /*$exixstIndexUnique = $this -> e -> verifyIfExistsIndexUnique($this -> e -> nameTable);

        if ($exixstIndexUnique["indice"]) {

        } else {*/
        $this -> select = $this -> selectMode_Only_Full_Group_By_Enabled($this -> e -> columnsTable, $this -> e -> columnsTableIndexUnique) . ", COUNT(id_extra) repeated";
        $this -> where = (($isQuery) ? "id_backup = " . $this -> pk_Extra["id_backup"] : $this -> conditionVerifyExistsUniqueIndex($this -> pk_Extra, $this -> e -> columnsTableIndexUnique, false)) . " GROUP BY " . $this -> namesColumns($this -> e -> columnsTableIndexUnique) . " HAVING COUNT( * ) >= 1 " . (($isQuery) ? "limit $this->pagina, $this->limit" : "" );
        //}
        $select = $this -> e -> mostrar($this -> where, $this -> select);
        if ($select) {
            $arreglo["error"] = false;
            $arreglo["extras"] = $select;
            $arreglo["titulo"] = ($isQuery) ? "¡ EXTRAS ENCONTRADOS !" : "¡ EXTRA ENCONTRADO !";
            $arreglo["msj"] = (($isQuery) ? "Se encontraron extras con " : "Se encontro extra con ") . $this -> keyValueArray($this -> pk_Extra);
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = ($isQuery) ? "¡ EXTRAS NO ENCONTRADOS !" : "¡ EXTRA NO ENCONTRADO !";
            $arreglo["msj"] = (($isQuery) ? "No se encontraron extras con " : "No se encontro extra con ") . $this -> keyValueArray($this -> pk_Extra);
        }
        return $arreglo;
    }
    public function inconsistenciaExtra(){
        $data = json_decode(Form::getValue('dataUser', false, false));
        $this -> pagina = Form::getValue('pagina');
        $backups = json_decode(Form::getValue('backups', false, false));
        $arreglo = array();

        $this -> pagina = $this -> pagina * $this -> limit_Inconsistencia;
        $this -> select = $this -> selectMode_Only_Full_Group_By_Enabled($this -> e -> columnsTable, $this -> e -> columnsTableIndexUnique, "be.") . ", COUNT(be.id_extra) repeated";
        $this -> table = "backup_extras be, backups b";
        $this -> where = "b.id_backup = be.id_backup " . $this -> condicionarConsulta($data -> id, "b.id_user", 0) . $this -> inBackups($backups, "be.id_backup") . " GROUP BY ". $this -> namesColumns($this -> e -> columnsTableIndexUnique, "be.") ." HAVING COUNT( * ) >= $this->having_Count limit $this->pagina, $this->limit_Inconsistencia";
        $arreglo["consultaSQL"] = $this -> consultaSQL($this -> select, $this -> table, $this -> where);
        $consulta = $this -> e -> mostrar($this -> where, $this -> select, $this -> table);
        if ($consulta) {
            $arreglo["error"] = false;
            $arreglo["extras"] = $consulta;
            $arreglo["titulo"] = "¡ INCONSISTENCIAS ENCONTRADOS !";
            $arreglo["msj"] = "Se encontraron duplicidades de registros en la tabla Extra ". (($data -> email != "Generales") ? "del usuario: $data->email" : "");
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ INCONSISTENCIAS NO ENCONTRADOS !";
            $arreglo["msj"] = "No se encontraron duplicidades de registros en la tabla Extra ". (($data -> email != "Generales") ? "del usuario: $data->email" : "");
        }
        return $arreglo;
    }
    public function corregirInconsitencia() {
        $this -> verificarPermiso(PERMISO_MNTINCONSISTENCIA);

        $indices = $this -> e -> ejecutarCodigoSQL("SHOW INDEX from " . $this -> e -> nameTable);
        $arreglo = array();
        $arreglo["indice"] = false;
        foreach ($indices as $key => $value) {
            if ($value -> Key_name == "indiceUnico") { //Ya existe el indice unico... Entonces la tabla ya se encuentra corregida
                $arreglo["indice"] = true;
                $arreglo["msj"] = "Ya existe el campo unico en la tabla Extras, por lo tanto ya se ha realizado la corrección de datos inconsistentes anteriormente.";
                $arreglo["titulo"] = "¡ TABLA CORREGIDA ANTERIORMENTE !";
                return $arreglo;
            }
        }
        $sql = $this -> sentenciaInconsistenicaSQL($this -> e -> nameTable, $this -> e -> columnsTableIndexUnique, "id_backup");
        $operacion = $this -> e -> ejecutarMultSentMySQLi($sql);
        $arreglo["SenteciasSQL"] = $sql;
        $arreglo["Result"] = $operacion;
        return $arreglo;
    }
    public function verifyExistsIndexUnique ($newExtra, $isUpdate = false) {
        $arreglo = array();
        $arreglo["error"] = false;
        $arreglo["sqlVerfiyIndexUnique"] = $this -> conditionVerifyExistsUniqueIndex($newExtra, $this -> e -> columnsTableIndexUnique);
        $result = $this -> e -> mostrar( $arreglo["sqlVerfiyIndexUnique"]);
        if ($result) {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ REGISTRO EXISTENTE !";
            $arreglo["msj"] = "NO se puede " . (($isUpdate) ? "actualizar el " : "registrar el nuevo ") . "Extra, porque ya existe un registro en la BD con el mismo id_extra del mismo backup. Porfavor verifique y vuelva a intentarlo";
        }
        return $arreglo;
    }
    public function agregarExtra() {
        $this -> verificarPermiso(PERMISO_INSERT);

        $extra = json_decode(Form::getValue("extra", false, false));
        $arreglo = array();
        $arreglo = $this -> verifyExistsIndexUnique($extra);
        if ($arreglo["error"]) return $arreglo;
        $insert = $this -> e -> agregar($extra);
        if ($insert) {
            $this -> pk_Extra["id_backup"] = $extra -> id_backup;
            $this -> pk_Extra["id_extra"] = $extra -> id_extra;
            $queryExtraNew = $this -> buscarExtrasBackup(false);
            $arreglo["extra"]["error"] = $queryExtraNew["error"];
            $arreglo["extra"]["titulo"] = $queryExtraNew["titulo"];
            $arreglo["extra"]["msj"] = $queryExtraNew["msj"];
            if (!$arreglo["extra"]["error"]) $arreglo["extra"]["new"] = $queryExtraNew["extras"][0];
            $arreglo["error"] = false;
            $arreglo["titulo"] = "¡ EXTRA AGREGADO !";
            $arreglo["msj"] = "Se agrego correctamente el nuevo Extra con " . $this -> keyValueArray($this -> pk_Extra);
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ EXTRA NO AGREGADO !";
            $arreglo["msj"] = "Ocurrio un error al ingresar el nuevo Extra con " . $this -> keyValueArray($this -> pk_Extra);
        }
        return $arreglo;
    }
    public function actualizarExtra() {
        $this -> verificarPermiso(PERMISO_UPDATE);

        $extra = json_decode(Form::getValue("extra", false, false));
        $indexUnique = json_decode(Form::getValue("indexUnique", false, false));
        $arreglo = array();

        if (($extra -> id_backup != $indexUnique -> id_backup)
            || ($extra -> id_extra != $indexUnique -> id_extra))
        {
            $arreglo = $this -> verifyExistsIndexUnique($extra, true);
            if ($arreglo["error"]) return $arreglo;
        }
        $update = $this -> e -> actualizar($extra, $indexUnique);
        if ($update) {
            $this -> pk_Extra["id_backup"] = $extra -> id_backup;
            $this -> pk_Extra["id_extra"] = $extra -> id_extra;
            $queryExtraUpdate = $this -> buscarExtrasBackup(false);
            $arreglo["extra"]["error"] = $queryExtraUpdate["error"];
            $arreglo["extra"]["titulo"] = $queryExtraUpdate["titulo"];
            $arreglo["extra"]["msj"] = $queryExtraUpdate["msj"];
            if (!$arreglo["extra"]["error"]) $arreglo["extra"]["update"] = $queryExtraUpdate["extras"][0];
            $arreglo["error"] = false;
            $arreglo["titulo"] = "¡ EXTRA ACTUALIZADO !";
            $arreglo["msj"] = "Se acualizo correctamente el Extra con " . $this -> keyValueArray($indexUnique);
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ EXTRA NO ACTUALIZADO !";
            $arreglo["msj"] = "Ocurrio un error al intentar actualizar el Extra con " . $this -> keyValueArray($indexUnique);
        }
        return $arreglo;
    }
    public function eliminarExtra() {
        $this -> verificarPermiso(PERMISO_DELETE);

        $indexUnique = json_decode(Form::getValue("indexUnique", false, false));
        $arreglo = array();
        $delete = $this -> e -> eliminar($indexUnique);
        if ($delete) {
            $arreglo["error"] = false;
            $arreglo["titulo"] = "¡ EXTRA ELIMINADA !";
            $arreglo["msj"] = "El Extra con " . $this -> keyValueArray($indexUnique) . " ha sido eliminado correctamente";
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ EXTRA NO ELIMINADA !";
            $arreglo["msj"] = "Ocurrio un error al intentar eliminar el Extra con " . $this -> keyValueArray($indexUnique);
        }
        return $arreglo;
    }
}