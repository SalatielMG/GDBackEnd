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

    public function __construct()
    {
        $this -> cv = new CardView();
    }
    public function setPk_CardView($id_backup) {
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
        $this -> select = ($isExport) ? "id_card, name, period, sign, show_card as show, number" : "id_card, name";
        $this -> where = "id_backup = " . $this -> pk_CardView["id_backup"] . " GROUP BY " . $this -> namesColumns($this -> cv -> columnsTableIndexUnique, ""). " HAVING COUNT( * ) >= 1 ORDER BY id_card";
        $cardviewBackup = $this -> cv -> mostrar($this -> where, $this -> select);
        if ($cardviewBackup) {
            $arreglo["cardviews"] = $cardviewBackup;
            $arreglo["error"] = false;
            $arreglo["titulo"] = "¡ CARDVIEWS ENCONTRADOS !";
            $arreglo["msj"] = "Se encontraron cardviews con ". $this -> keyValueArray($this -> pk_CardView);
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ CARDVIEWS NO ENCONTRADOS !";
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
        $exixstIndexUnique = $this -> cv -> verifyIfExistsIndexUnique($this -> cv -> nameTable);

        if ($exixstIndexUnique["indice"]) {

        } else {
            $this -> select = "bcv.*, COUNT(bcv.id_card) repeated";
            $this -> table = $this -> cv -> nameTable . " bcv";
            $this -> where = (($isQuery) ? "bcv.id_backup = " . $this -> pk_CardView["id_backup"] : $this -> conditionVerifyExistsUniqueIndex($this -> pk_CardView, $this -> cv -> columnsTableIndexUnique, false, "bcv.") ) . " GROUP BY " . $this -> namesColumns($this -> cv -> columnsTableIndexUnique, "bcv.") . " HAVING COUNT( * ) >= 1 ORDER BY bcv.id_card " . (($isQuery) ? "limit $this->pagina,$this->limit" : "");
        }
        $arreglo["consultaSQL"] = $this -> consultaSQL($this -> select, $this -> table, $this -> where);
        $select = $this -> cv -> mostrar($this -> where, $this -> select, $this -> table);
        if ($select) {
            $arreglo["error"] = false;
            $arreglo["cardviews"] = $select;
            $arreglo["titulo"] = ($isQuery) ? "¡ CARDVIEWS ENCONTRADOS !" : "CARDVIEW ENCONTRADO";
            $arreglo["msj"] = (($isQuery) ? "Se econtraron cardviews con " : "Se econtro cardview con " ) . $this -> keyValueArray($this -> pk_CardView);
            if ($isQuery && $this -> pagina == 0) {
                $arreglo["cardviewsBackup"] = $this -> obtCardViewsGralBackup(false);
            }
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ CARDVIEWS NO ENCONTRADOS !";
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
        $select = "cv.*, COUNT(cv.id_backup) cantidadRepetida";
        $table = "backup_cardviews cv, backups b";
        $where = "b.id_backup = cv.id_backup ". $this -> condicionarConsulta($data -> id, "b.id_user", 0) . $this -> inBackups($backups, "cv.id_backup") . " GROUP BY ". $this -> namesColumns($this -> cv -> columnsTableIndexUnique, "cv.") ." HAVING COUNT( * ) >= $this->having_Count limit $this->pagina, $this->limit_Inconsistencia";
        $arreglo["consultaSQL"] = $this -> consultaSQL($select, $table, $where);
        $consulta = $this -> cv -> mostrar($where, $select, $table);
        if ($consulta) {
            $arreglo["error"] = false;
            $arreglo["cardviews"] = $consulta;
            $arreglo["titulo"] = "¡ INCONSISTENCIAS ENCONTRADOS !";
            $arreglo["msj"] = "Se encontraron duplicidades de registros en la tabla CardView ". (($data -> email != "Generales") ? "del usuario: $data->email" : "");
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ INCONSISTENCIAS NO ENCONTRADOS !";
            $arreglo["msj"] = "No se encontraron duplicidades de registros en la tabla CardView ". (($data -> email != "Generales") ? "del usuario: $data->email" : "");
        }
        return $arreglo;
    }
    public function corregirInconsitencia() {
        $indices = $this -> cv -> ejecutarCodigoSQL("SHOW INDEX from " . $this -> cv -> nameTable);
        $arreglo = array();
        $arreglo["indice"] = false;
        foreach ($indices as $key => $value) {
            if ($value -> Key_name == "indiceUnico") { //Ya existe el indice unico... Entonces la tabla ya se encuentra corregida
                $arreglo["indice"] = true;
                $arreglo["msj"] = "Ya existe el campo unico en la tabla CardViews, por lo tanto ya se ha realizado la corrección de datos inconsistentes anteriormente.";
                $arreglo["titulo"] = "¡ TABLA CORREGIDA ANTERIORMENTE !";
                return $arreglo;
            }
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
            $arreglo["titulo"] = "¡ REGISTRO EXISTENTE !";
            $arreglo["msj"] = "NO se puede " . (($isUpdate) ? "actualizar la" : "registrar la nueva") . " CardView, porque ya existe un registro en la BD con el mismo id_card del mismo backup. Porfavor verifique y vuelva a intentarlo";
        }
        return $arreglo;
    }
    public function agregarCardview() {
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
            $arreglo["titulo"] = "¡ CARDVIEW AGREGADO !";
            $arreglo["msj"] = "Se agrego correctamente el nuevo cardview con " . $this -> keyValueArray($this -> pk_CardView);
            $arreglo["cardviewsBackup"] = $this -> obtCardViewsGralBackup(false);
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ CARDVIEW NO AGREGADO !";
            $arreglo["msj"] = "Ocurrio un error al ingresar el nuevo cardview con " . $this -> keyValueArray($this -> pk_CardView);
        }
        return $arreglo;
    }
    public function actualizarCardview() {
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
            $arreglo["titulo"] = "¡ CARDVIEW ACTUALIZADA !";
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
            $arreglo["titulo"] = "¡ CARDVIEW NO ACTUALIZADA !";
            $arreglo["msj"] = "Ocurrio un error al intentar actualizar la cardview con " . $this -> keyValueArray($indexUnique);
        }
        return $arreglo;
    }
    public function eliminarCardview() {
        $indexUnique = json_decode(Form::getValue("indexUnique", false, false));
        $arreglo = array();
        $delete = $this -> cv -> eliminar($indexUnique);
        if ($delete) {
            $arreglo["error"] = false;
            $arreglo["titulo"] = "¡ CARDVIEW ELIMINADA !";
            $arreglo["msj"] = "La cardview con " . $this -> keyValueArray($indexUnique) . " ha sido eliminado correctamente";
            $this -> pk_CardView["id_backup"] = $indexUnique -> id_backup;
            $arreglo["cardviewsBackup"] = $this -> obtCardViewsGralBackup(false);
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ CARDVIEW NO ELIMINADA !";
            $arreglo["msj"] = "Ocurrio un error al intentar eliminar la cardview con " . $this -> keyValueArray($indexUnique);
        }
        return $arreglo;
    }
}