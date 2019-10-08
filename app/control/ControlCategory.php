<?php
/**
 * Created by PhpStorm.
 * User: pc-01
 * Date: 21/08/2019
 * Time: 14:05
 */
require_once (APP_PATH."model/Category.php");
class ControlCategory extends Valida
{
    private $c;
    private $pagina = 0;
    private $where = "";
    private $select = "";
    private $table = "";
    private $id_backup = 0;

    public function __construct($id_backup = 0)
    {
        $this -> c = new Category();
        $this -> id_backup = $id_backup;
    }

    public function setId_Backup($id_backup) {
        $this -> id_backup = $id_backup;
    }

    public function getId_Backup() {
        return $this -> id_backup;
    }

    public function obtCategoriesBackup() {
        $arreglo = array();
        $this -> select = "*";
        $this -> where = "id_backup = $this->id_backup GROUP BY " . $this -> namesColumns($this -> c -> nameColumnsIndexUnique, "") . "  HAVING COUNT( * ) >= 1";
        $categoriesBackup = $this -> c -> mostrar($this -> where, $this -> select);
        if ($categoriesBackup) {
            $arreglo["categories"] = $categoriesBackup;
            $arreglo["error"] = false;
            $arreglo["titulo"] = "¡ CATEGORIES ENCONTRADOS !";
            $arreglo["msj"] = "Se encontraron categories del Respaldo con id_backup: $this->id_backup.";
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ CATEGORIES NO ENCONTRADOS !";
            $arreglo["msj"] = "No se encontraron categories del Respaldo con id_backup: $this->id_backup.";
        }
        return $arreglo;
    }

    public function buscarCategoriesBackup() {
        $idBackup = Form::getValue('idBack');
        $select = $this -> c -> mostrar("bc.id_backup = ba.id_backup AND bc.id_account = ba.id_account AND bc.id_backup = $idBackup", "bc.*, ba.name as account", "backup_categories bc, backup_accounts ba");
        $arreglo = array();
        if ($select) {
            $arreglo["error"] = false;
            $arreglo["categories"] = $select;
            $arreglo["titulo"] = "¡ CARDVIEWS ENCONTRADOS !";
            $arreglo["msj"] = "Se encontraron categories del respaldo solicitado.";
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ CARDVIEWS NO ENCONTRADOS !";
            $arreglo["msj"] = "No se encontraron categories del respaldo solicitado.";
        }
        return $arreglo;
    }

    public function inconsistenciaCategory() {
        $data = json_decode(Form::getValue('dataUser', false, false));
        $this -> pagina = Form::getValue('pagina');
        $backups = json_decode(Form::getValue('backups', false, false));
        $arreglo = array();

        $this -> pagina = $this -> pagina * $this -> limit_Inconsistencia;
        $select = "bc.*, COUNT(bc.id_backup) cantidadRepetida";
        $table = "backup_categories bc, backups b";
        $where = "b.id_backup = bc.id_backup ". $this -> condicionarConsulta($data -> id, "b.id_user", 0) . $this -> inBackups($backups, "bc.id_backup") . " GROUP BY ". $this -> namesColumns($this -> c -> nameColumns, "bc.") ." HAVING COUNT( * ) >= $this->having_Count limit $this->pagina , $this->limit_Inconsistencia";
        $arreglo["consultaSQL"] = $this -> consultaSQL($select, $table, $where);
        $consulta = $this -> c -> mostrar($where, $select, $table);
        if ($consulta) {
            $arreglo["error"] = false;
            $arreglo["categories"] = $consulta;
            $arreglo["titulo"] = "¡ INCONSISTENCIAS ENCONTRADOS !";
            $arreglo["msj"] = "Se encontraron duplicidades de registros en la tabla Category ". (($data -> email != "Generales") ? "del usuario: $data->email" : "");
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ INCONSISTENCIAS NO ENCONTRADOS !";
            $arreglo["msj"] = "No se encontraron duplicidades de registros en la tabla Category ". (($data -> email != "Generales") ? "del usuario: $data->email" : "");
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
                $arreglo["msj"] = "Ya existe el campo unico en la tabla Categories, por lo tanto ya se ha realizado la corrección de datos inconsistentes anteriormente.";
                $arreglo["titulo"] = "¡ TABLA CORREGIDA ANTERIORMENTE !";
                return $arreglo;
            }
        }
        $sql = $this -> sentenciaInconsistenicaSQL($this -> c -> nameTable, $this -> c ->nameColumnsIndexUnique, "id_backup");
        $operacion = $this -> c -> ejecutarMultSentMySQLi($sql);
        $arreglo["SenteciasSQL"] = $sql;
        $arreglo["Result"] = $operacion;
        return $arreglo;
    }
}