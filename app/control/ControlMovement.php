<?php
/**
 * Created by PhpStorm.
 * User: pc-01
 * Date: 21/08/2019
 * Time: 14:43
 */
require_once (APP_PATH."model/Movement.php");

class ControlMovement extends Valida
{
    private $m;
    private $pagina = 0;
    public function __construct()
    {
        $this -> m = new Movement();
    }
    public function buscarMovementsBackup() {
        $idBackup = Form::getValue('idBack');
        $select = $this -> m -> mostrar("1 ORDER BY CC.date_record",
            "CC.*",
            "(SELECT bm.*, bc.symbol, bac.name as account, bcat.name as category FROM backup_movements bm, backup_currencies bc, backup_accounts bac, backup_categories bcat WHERE bm.id_backup = bc.id_backup AND bm.id_backup = bac.id_backup AND bm.id_account = bac.id_account AND bm.id_backup = bcat.id_backup AND (bm.id_category = bcat.id_category) AND bm.id_backup = $idBackup
                    UNION
                   SELECT bm.*, bc.symbol, bac.name as account, '' AS category FROM backup_movements bm, backup_currencies bc, backup_accounts bac WHERE bm.id_backup = bc.id_backup AND bm.id_backup = bac.id_backup AND bm.id_account = bac.id_account AND bm.id_backup = $idBackup AND bm.id_category >= 10000) AS CC");

        /*$select = $this -> m -> mostrar("bm.id_backup = bc.id_backup AND bm.id_backup = $idBackup", "bm.*, bc.symbol", "backup_movements bm, backup_currencies bc");
        $select = $this -> m -> mostrar("bm.id_backup = bc.id_backup AND bm.id_backup = bac.id_backup AND bm.id_account = bac.id_account AND bm.id_backup = bcat.id_backup AND (bm.id_category = bcat.id_category OR bm.id_category >= 10000) AND bm.id_backup = $idBackup",
            "bm.*, bc.symbol, bac.name as account, bcat.name as category",
            "backup_movements bm, backup_currencies bc, backup_accounts bac, backup_categories bcat");*/

        $arreglo = array();
        if ($select) {
            $arreglo["error"] = false;
            $arreglo["movements"] = $select;
            $arreglo["titulo"] = "¡ MOVEMENTS ENCONTRADOS !";
            $arreglo["msj"] = "Se encontraron movements del respaldo solicitado.";
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ MOVEMENTS NO ENCONTRADOS !";
            $arreglo["msj"] = "No se encontraron movements del respaldo solicitado.";
        }
        return $arreglo;
    }
    public function inconsistenciaMovement(){
        $data = json_decode(Form::getValue('dataUser', false, false));
        $this -> pagina = Form::getValue('pagina');
        $backups = json_decode(Form::getValue('backups', false, false));
        $arreglo = array();

        $this -> pagina = $this -> pagina * $this -> limit_Inconsistencia;
        $select = "bm.*, COUNT(bm.id_backup) cantidadRepetida";
        $table = "backup_movements bm, backups b";
        $where = "b.id_backup = bm.id_backup " . $this -> condicionarConsulta($data -> id, "b.id_user", 0) . $this -> inBackups($backups, "bm.id_backup") . " GROUP BY ". $this -> namesColumns($this -> m -> nameColumnsIndexUnique, "bm.") ." HAVING COUNT( * ) >= $this->having_Count limit $this->pagina, $this->limit_Inconsistencia";
        $arreglo["consultaSQL"] = $this -> consultaSQL($select, $table, $where);
        $consulta = $this -> m -> mostrar($where, $select, $table);
        if ($consulta) {
            $arreglo["error"] = false;
            $arreglo["movements"] = $consulta;
            $arreglo["titulo"] = "¡ INCONSISTENCIAS ENCONTRADOS !";
            $arreglo["msj"] = "Se encontraron duplicidades de registros en la tabla Movement ". (($data -> email != "Generales") ? "del usuario: $data->email" : "");
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ INCONSISTENCIAS NO ENCONTRADOS !";
            $arreglo["msj"] = "No se encontraron duplicidades de registros en la tabla Movement ". (($data -> email != "Generales") ? "del usuario: $data->email" : "");
        }
        return $arreglo;
    }
    public function corregirInconsitencia() {
        $indices = $this -> m -> ejecutarCodigoSQL("SHOW INDEX from " . $this -> m -> nameTable);
        $arreglo = array();
        $arreglo["indice"] = false;
        foreach ($indices as $key => $value) {
            if ($value -> Key_name == "indiceUnico") { //Ya existe el indice unico... Entonces la tabla ya se encuentra corregida
                $arreglo["indice"] = true;
                $arreglo["msj"] = "Ya existe el campo unico en la tabla Movements, por lo tanto ya se ha realizado la corrección de datos inconsistentes anteriormente.";
                $arreglo["titulo"] = "¡ TABLA CORREGIDA ANTERIORMENTE !";
                return $arreglo;
            }
        }
        $sql = $this -> sentenciaInconsistenicaSQL($this -> m -> nameTabl, $this -> m -> nameColumnsIndexUnique,"id_backup");
        $operacion = $this -> m -> ejecutarMultSentMySQLi($sql);
        $arreglo["SenteciasSQL"] = $sql;
        $arreglo["Result"] = $operacion;
        return $arreglo;
    }
}