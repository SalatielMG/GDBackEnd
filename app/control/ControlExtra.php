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

        $this -> select = "*, COUNT(id_extra) repeated";
        $this -> where = (($isQuery) ? "id_backup = " . $this -> pk_Extra["id_backup"] : $this -> conditionVerifyExistsUniqueIndex($this -> pk_Extra, $this -> e -> columnsTableIndexUnique, false)) . " GROUP BY " . $this -> namesColumns($this -> e -> columnsTableIndexUnique) . " HAVING COUNT( * ) >= 1 " . (($isQuery) ? "limit $this->pagina, $this->limit" : "" );

        $select = $this -> e -> mostrar($this -> where, $this -> select);
        if ($select) {
            $arreglo["error"] = false;
            $arreglo["extras"] = $select;
            $arreglo["titulo"] = ($isQuery) ? "¡ Extras encontrados !" : "¡ Extra encontrado !";
            $arreglo["msj"] = (($isQuery) ? "Se encontraron extras con " : "Se encontro extra con ") . $this -> keyValueArray($this -> pk_Extra);
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = ($isQuery) ? "¡ Extras no encontrados !" : "¡ Extra no encontrado !";
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
        $this -> select = "be.*, COUNT(be.id_extra) repeated";
        $this -> table = $this -> e -> nameTable . " be, backups b";
        $this -> where = "b.id_backup = be.id_backup " . $this -> condicionarConsulta($data -> id, "b.id_user", 0) . $this -> inBackups($backups, "be.id_backup") . " GROUP BY ". $this -> namesColumns($this -> e -> columnsTableIndexUnique, "be.") ." HAVING COUNT( * ) >= $this->having_Count limit $this->pagina, $this->limit_Inconsistencia";
        $arreglo["consultaSQL"] = $this -> consultaSQL($this -> select, $this -> table, $this -> where);
        $consulta = $this -> e -> mostrar($this -> where, $this -> select, $this -> table);
        if ($consulta) {
            $arreglo["error"] = false;
            $arreglo["extras"] = $consulta;
            $arreglo["titulo"] = "¡ Inconsistencias encontrados !";
            $arreglo["msj"] = "Se encontraron inconsistencias de registros en la tabla Extra ". (($data -> email != "Generales") ? "del usuario: $data->email" : "");
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ Inconsistencias no encontrados !";
            $arreglo["msj"] = "No se encontraron inconsistencias de registros en la tabla Extra ". (($data -> email != "Generales") ? "del usuario: $data->email" : "");
        }
        return $arreglo;
    }
    public function obtSizeTable() {
        $this -> verificarPermiso(PERMISO_MNTINCONSISTENCIA);
        $arreglo = array();
        $exixstIndexUnique = $this -> e -> verifyIfExistsIndexUnique($this -> e -> nameTable);
        if ($exixstIndexUnique["indice"]) {
            return $arreglo = $exixstIndexUnique;
        }
        $size = $this -> e -> sizeTable($this -> e -> nameTable);
        if ($size) {
            $arreglo["size"] = $size[0];
            $arreglo["error"] = false;
            $arreglo["titulo"] = "¡ Tamaño calculado !";
            $arreglo["msj"] = "Se calculo correctamente el tamaño de la tabla de datos: " . $this -> e -> nameTable;
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ Tamaño no calculado !";
            $arreglo["msj"] = "No se pudo calcular correctamente el tamaño de la tabla de datos: " . $this -> e -> nameTable;
        }
        return $arreglo;
    }
    public function corregirInconsistenciaRegistro() {
        $this -> verificarPermiso(PERMISO_MNTINCONSISTENCIA);

        $indexUnique = json_decode(Form::getValue("indexUnique", false, false));
        $arreglo = array();
        $this -> pk_Extra["id_backup"] = $indexUnique -> id_backup;
        $this -> pk_Extra["id_extra"] = $indexUnique -> id_extra;
        $extra = $this -> buscarExtrasBackup(false);
        //var_dump($account); return;
        if ($extra) {
            $correcion = $this -> e -> eliminar($indexUnique);
            if ($correcion) {
                $insertExtra = $this -> e -> agregar($extra["extras"][0]);
                if ($insertExtra) {
                    $arreglo["error"] = false;
                    $arreglo["titulo"] = "¡ Extra corregida !";
                    $arreglo["msj"] = "Se corrigio correctamente el registro Extra con " . $this -> keyValueArray($this -> pk_Extra);
                    $arreglo["extra"] = $this -> buscarExtrasBackup(false);
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
            $arreglo["msj"] = "Error al obtener los datos del registro Extra seleccionada para corregir";
        }
        return $arreglo;
    }
    public function corregirInconsitencia() {
        $this -> verificarPermiso(PERMISO_MNTINCONSISTENCIA);

        $arreglo = array();
        $exixstIndexUnique = $this -> e -> verifyIfExistsIndexUnique($this -> e -> nameTable);
        if ($exixstIndexUnique["indice"]) {
            return $arreglo = $exixstIndexUnique;
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
            $arreglo["titulo"] = "¡ Registro existente !";
            $arreglo["msj"] = "NO se puede " . (($isUpdate) ? "actualizar el " : "registrar el nuevo ") . "Extra, porque ya existe un registro en la BD con el mismo ID_EXTRA del mismo backup. Porfavor verifique y vuelva a intentarlo";
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
            $arreglo["titulo"] = "¡ Extra agregado !";
            $arreglo["msj"] = "Se agrego correctamente el nuevo registro Extra con " . $this -> keyValueArray($this -> pk_Extra);
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ Extra no agregado !";
            $arreglo["msj"] = "Ocurrio un error al ingresar el nuevo registro Extra con " . $this -> keyValueArray($this -> pk_Extra);
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
            $arreglo["titulo"] = "¡ Extra actualizado !";
            $arreglo["msj"] = "Se acualizo correctamente el registro Extra con " . $this -> keyValueArray($indexUnique);
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ Extra no actualizado !";
            $arreglo["msj"] = "Ocurrio un error al intentar actualizar el registro Extra con " . $this -> keyValueArray($indexUnique);
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
            $arreglo["titulo"] = "¡ Extra eliminada !";
            $arreglo["msj"] = "El registro Extra con " . $this -> keyValueArray($indexUnique) . " ha sido eliminado correctamente";
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ Extra no eliminada !";
            $arreglo["msj"] = "Ocurrio un error al intentar eliminar el registro Extra con " . $this -> keyValueArray($indexUnique);
        }
        return $arreglo;
    }
}