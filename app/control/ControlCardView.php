<?php
/**
 * Created by PhpStorm.
 * User: pc-01
 * Date: 21/08/2019
 * Time: 12:47
 */
require_once (APP_PATH."model/CardView.php");
class ControlCardView extends Valida
{
    private $cv;
    private $pagina = 0;
    private $pk_CardView = array();
    private $where = "";
    private $select = "";
    private $table = "";

    public function __construct($id_backup = 0)
    {
        $this -> cv = new CardView();
        $this -> pk_CardView["id_backup"] = $id_backup;
    }
    public function getCardViewModel() {
        return $this -> cv;
    }
    public function obtCardViewsGralBackup($isQuery = true, $isExport = false) {
        if ($isQuery) {
            $this -> pk_CardView["id_backup"] = Form::getValue("id_backup");
        }
        $arreglo = array();
        $this -> select = ($isExport) ? "id_card, name, period, sign, show_card as show_item, number" : "id_card, name";
        $this -> where = "id_backup = " . $this -> pk_CardView["id_backup"] . " GROUP BY " . $this -> namesColumns($this -> cv -> columnsTableIndexUnique, ""). " HAVING COUNT( * ) >= 1 ORDER BY id_card";
        $arreglo["consultaSQL"] = $this -> consultaSQL($this -> select, $this -> cv -> nameTable, $this -> where);
        //return $arreglo;
        $cardviewBackup = $this -> cv -> mostrar($this -> where, $this -> select);
        if ($cardviewBackup) {
            $arreglo["cardviews"] = $cardviewBackup;
            $arreglo["error"] = false;
            $arreglo["titulo"] = "¡ Cardviews encontrados !";
            $arreglo["msj"] = "Se encontraron cardviews con ". $this -> keyValueArray($this -> pk_CardView);
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ Cardviews no encontrados !";
            $arreglo["msj"] = "No se encontraron cardviews con " . $this -> keyValueArray($this -> pk_CardView);
        }
        return $arreglo;
    }
    public function buscarCardviewsBackup($isQuery = true) {
        $arreglo = array();
        if ($isQuery) {
            $this -> pk_CardView["id_backup"] = Form::getValue('id_backup');
            $this -> pagina = Form::getValue("pagina");
            $this -> pagina = $this -> pagina * $this -> limit;
        }

        $this -> select = "bcv.* , COUNT(bcv.id_card) repeated";
        $this -> table = $this -> cv -> nameTable . " bcv";
        $this -> where = (($isQuery) ? "bcv.id_backup = " . $this -> pk_CardView["id_backup"] : $this -> conditionVerifyExistsUniqueIndex($this -> pk_CardView, $this -> cv -> columnsTableIndexUnique, false, "bcv.") ) . " GROUP BY " . $this -> namesColumns($this -> cv -> columnsTableIndexUnique, "bcv.") . " HAVING COUNT( * ) >= 1 ORDER BY bcv.id_card " . (($isQuery) ? "limit $this->pagina,$this->limit" : "");

        $arreglo["consultaSQL"] = $this -> consultaSQL($this -> select, $this -> table, $this -> where);
        $select = $this -> cv -> mostrar($this -> where, $this -> select, $this -> table);
        if ($select) {
            $arreglo["error"] = false;
            $arreglo["cardviews"] = $select;
            $arreglo["titulo"] = ($isQuery) ? "¡ Cardviews encontrados !" : "Cardview encontrado";
            $arreglo["msj"] = (($isQuery) ? "Se econtraron cardviews con " : "Se econtro cardview con " ) . $this -> keyValueArray($this -> pk_CardView);
            if ($isQuery && $this -> pagina == 0) {
                $arreglo["cardviewsBackup"] = $this -> obtCardViewsGralBackup(false);
            }
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ Cardviews no encontrados !";
            $arreglo["msj"] = "No se encontraron cardviews del respaldo solicitado.";
        }
        return $arreglo;
    }
    public function inconsistenciaCardView() {
        $data = json_decode(Form::getValue('dataUser', false, false));
        $this -> pagina = Form::getValue('pagina');
        $backups = json_decode(Form::getValue('backups', false, false));
        $arreglo = array();

        $this -> pagina = $this -> pagina * $this -> limit_Inconsistencia;
        $this -> select = "cv.*, COUNT(cv.id_card) repeated";
        $this -> table = $this -> cv -> nameTable . " cv, backups b";
        $this -> where = "b.id_backup = cv.id_backup ". $this -> condicionarConsulta($data -> id, "b.id_user", 0) . $this -> inBackups($backups, "cv.id_backup") . " GROUP BY ". $this -> namesColumns($this -> cv -> columnsTableIndexUnique, "cv.") ." HAVING COUNT( * ) >= $this->having_Count limit $this->pagina, $this->limit_Inconsistencia";
        $arreglo["consultaSQL"] = $this -> consultaSQL($this -> select, $this -> table, $this -> where);
        $consulta = $this -> cv -> mostrar($this -> where, $this -> select, $this -> table);
        if ($consulta) {
            $arreglo["error"] = false;
            $arreglo["cardviews"] = $consulta;
            $arreglo["titulo"] = "¡ Inconsistencias encontrados !";
            $arreglo["msj"] = "Se encontraron inconsistencias de registros en la tabla CardView ". (($data -> email != "Generales") ? "del usuario: $data->email" : "");
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ Inconsistencias no encontrados !";
            $arreglo["msj"] = "No se encontraron inconsistencias de registros en la tabla CardView ". (($data -> email != "Generales") ? "del usuario: $data->email" : "");
        }
        return $arreglo;
    }
    public function obtSizeTable() {
        $this -> verificarPermiso(PERMISO_MNTINCONSISTENCIA);
        $arreglo = array();
        $exixstIndexUnique = $this -> cv -> verifyIfExistsIndexUnique($this -> cv -> nameTable);
        if ($exixstIndexUnique["indice"]) {
            return $arreglo = $exixstIndexUnique;
        }
        $size = $this -> cv -> sizeTable($this -> cv -> nameTable);
        if ($size) {
            $arreglo["size"] = $size[0];
            $arreglo["error"] = false;
            $arreglo["titulo"] = "¡ Tamaño calculado !";
            $arreglo["msj"] = "Se calculo correctamente el tamaño de la tabla de datos: " . $this -> cv -> nameTable;
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ Tamaño no calculado !";
            $arreglo["msj"] = "No se pudo calcular correctamente el tamaño de la tabla de datos: " . $this -> cv -> nameTable;
        }
        return $arreglo;
    }
    public function corregirInconsistenciaRegistro() {
        $this -> verificarPermiso(PERMISO_MNTINCONSISTENCIA);

        $indexUnique = json_decode(Form::getValue("indexUnique", false, false));
        $arreglo = array();
        $this -> pk_CardView["id_backup"] = $indexUnique -> id_backup;
        $this -> pk_CardView["id_card"] = $indexUnique -> id_card;
        $cardview = $this -> buscarCardviewsBackup(false);
        //var_dump($account); return;
        if ($cardview) {
            $correcion = $this -> cv -> eliminar($indexUnique);
            if ($correcion) {
                $insertCardview = $this -> cv -> agregar($cardview["cardviews"][0]);
                if ($insertCardview) {
                    $arreglo["error"] = false;
                    $arreglo["titulo"] = "¡ Cardview corregida !";
                    $arreglo["msj"] = "Se corrigio correctamente la Cardview con " . $this -> keyValueArray($this -> pk_CardView);
                    $arreglo["cardview"] = $this -> buscarCardviewsBackup(false);
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
            $arreglo["msj"] = "Error al obtner los datos de la Cardview seleccionada para corregir";
        }
        return $arreglo;
    }
    public function corregirInconsitencia() {
        $this -> verificarPermiso(PERMISO_MNTINCONSISTENCIA);

        $arreglo = array();
        $exixstIndexUnique = $this -> cv -> verifyIfExistsIndexUnique($this -> cv -> nameTable);
        if ($exixstIndexUnique["indice"]) {
            return $arreglo = $exixstIndexUnique;
        }
        $sql = $this -> sentenciaInconsistenicaSQL($this -> cv -> nameTable, $this -> cv -> columnsTableIndexUnique, "id_backup");
        $operacion = $this -> cv -> ejecutarMultSentMySQLi($sql);
        $arreglo["SenteciasSQL"] = $sql;
        $arreglo["Result"] = $operacion;
        return $arreglo;
    }
    public function verifyExistsIndexUnique ($newCardview, $isUpdate = false) {
        $arreglo = array();
        $arreglo["error"] = false;
        $arreglo["sqlVerfiyIndexUnique"] = $this -> conditionVerifyExistsUniqueIndex($newCardview, $this -> cv -> columnsTableIndexUnique);
        $result = $this -> cv -> mostrar( $arreglo["sqlVerfiyIndexUnique"]);
        if ($result) {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ Registro Existente !";
            $arreglo["msj"] = "NO se puede " . (($isUpdate) ? "actualizar la" : "registrar la nueva") . " CardView, porque ya existe un registro en la BD con el mismo ID_CARD del mismo backup. Porfavor verifique y vuelva a intentarlo";
        }
        return $arreglo;
    }
    public function agregarCardview() {
        $this -> verificarPermiso(PERMISO_INSERT);

        $cardview = json_decode(Form::getValue("cardview", false, false));
        $arreglo = array();
        $arreglo = $this -> verifyExistsIndexUnique($cardview);
        if ($arreglo["error"]) return $arreglo;
        $insert = $this -> cv -> agregar($cardview);

        if ($insert) {
            $this -> pk_CardView["id_backup"] = $cardview -> id_backup;
            $this -> pk_CardView["id_card"] = $cardview -> id_card;
            $queryCardviewNew = $this -> buscarCardviewsBackup(false);
            $arreglo["cardview"]["error"] = $queryCardviewNew["error"];
            $arreglo["cardview"]["titulo"] = $queryCardviewNew["titulo"];
            $arreglo["cardview"]["msj"] = $queryCardviewNew["msj"];
            if (!$arreglo["cardview"]["error"]) $arreglo["cardview"]["new"] = $queryCardviewNew["cardviews"][0];
            $arreglo["error"] = false;
            $arreglo["titulo"] = "¡ Cardview agregado !";
            $arreglo["msj"] = "Se agrego correctamente el nuevo cardview con " . $this -> keyValueArray($this -> pk_CardView);
            $arreglo["cardviewsBackup"] = $this -> obtCardViewsGralBackup(false);
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ Cardview no agregado !";
            $arreglo["msj"] = "Ocurrio un error al ingresar el nuevo cardview con " . $this -> keyValueArray($this -> pk_CardView);
        }
        return $arreglo;
    }
    public function actualizarCardview() {
        $this -> verificarPermiso(PERMISO_UPDATE);

        $cardview = json_decode(Form::getValue("cardview", false, false));
        $indexUnique = json_decode(Form::getValue("indexUnique", false, false));
        $arreglo = array();
        if (($cardview -> id_backup != $indexUnique -> id_backup)
            || ($cardview -> id_card != $indexUnique -> id_card))
        {
            $arreglo = $this -> verifyExistsIndexUnique($cardview, true);
            if ($arreglo["error"]) return $arreglo;
        }
        $update = $this -> cv -> actualizar($cardview, $indexUnique);
        if ($update) {
            $arreglo["error"] = false;
            $arreglo["titulo"] = "¡ Cardview actualizada !";
            $arreglo["msj"] = "La cardview con " . $this -> keyValueArray($indexUnique) . " se ha actualizado correctamente";
            $this -> pk_CardView["id_backup"] = $cardview -> id_backup;
            $this -> pk_CardView["id_card"] = $cardview -> id_card;
            $queryCardviewUpdate = $this -> buscarCardviewsBackup(false);
            $arreglo["cardview"]["error"] = $queryCardviewUpdate["error"];
            $arreglo["cardview"]["titulo"] = $queryCardviewUpdate["titulo"];
            $arreglo["cardview"]["msj"] = $queryCardviewUpdate["msj"];
            if (!$arreglo["cardview"]["error"]) $arreglo["cardview"]["update"] = $queryCardviewUpdate["cardviews"][0];
            $arreglo["cardviewsBackup"] = $this -> obtCardViewsGralBackup(false);
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ Cardview no actualizada !";
            $arreglo["msj"] = "Ocurrio un error al intentar actualizar la cardview con " . $this -> keyValueArray($indexUnique);
        }
        return $arreglo;
    }
    public function eliminarCardview() {
        $this -> verificarPermiso(PERMISO_DELETE);

        $indexUnique = json_decode(Form::getValue("indexUnique", false, false));
        $arreglo = array();
        $delete = $this -> cv -> eliminar($indexUnique);
        if ($delete) {
            $arreglo["error"] = false;
            $arreglo["titulo"] = "¡ Cardview eliminada !";
            $arreglo["msj"] = "La cardview con " . $this -> keyValueArray($indexUnique) . " ha sido eliminado correctamente";
            $this -> pk_CardView["id_backup"] = $indexUnique -> id_backup;
            $arreglo["cardviewsBackup"] = $this -> obtCardViewsGralBackup(false);
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ Cardview no eliminada !";
            $arreglo["msj"] = "Ocurrio un error al intentar eliminar la cardview con " . $this -> keyValueArray($indexUnique);
        }
        return $arreglo;
    }
}