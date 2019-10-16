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
    public function __construct()
    {
        $this -> cv = new CardView();
    }

    public function buscarCardviewsBackup() {
        $idBackup = Form::getValue('idBack');
        $select = $this -> cv -> mostrar("id_backup = $idBackup");
        $arreglo = array();
        if ($select) {
            $arreglo["error"] = false;
            $arreglo["cardviews"] = $select;
            $arreglo["titulo"] = "¡ CARDVIEWS ENCONTRADOS !";
            $arreglo["msj"] = "Se encontraron cardviews del respaldo solicitado.";
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
}