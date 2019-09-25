<?php
/**
 * Created by PhpStorm.
 * User: pc-01
 * Date: 21/08/2019
 * Time: 12:35
 */
require_once (APP_PATH."model/Budget.php");
class ControlBudget extends Valida
{
    private $b;
    private $pagina = 0;
    public function __construct()
    {
        $this -> b = new Budget();
    }

    public function buscarBudgetsBackup() {
        $idBackup = Form::getValue('idBack');

        $select = $this -> b -> mostrar("1",
            "CC.*",
            "(SELECT bd.*, bc.symbol, bac.name as account, bcat.name as category FROM backup_budgets bd, backup_currencies bc, backup_accounts bac, backup_categories bcat WHERE bd.id_backup = bc.id_backup AND bd.id_backup = bac.id_backup AND bd.id_account = bac.id_account AND bd.id_backup = bcat.id_backup AND bd.id_category = bcat.id_category AND bd.id_backup = $idBackup
            UNION
            SELECT bd.*, bc.symbol, 'Cuenta no encontrada' as account, 'Categoria no encontrada' as category FROM backup_budgets bd, backup_currencies bc WHERE bd.id_backup = bc.id_backup AND (bd.id_account >= 10000 OR bd.id_category >= 10000) AND bd.id_backup = $idBackup) as CC");
        /*$select = $this -> b -> mostrar("bd.id_backup = bc.id_backup AND bd.id_backup = bac.id_backup AND bd.id_account = bac.id_account AND bd.id_backup = bcat.id_backup AND bd.id_category = bcat.id_category AND bd.id_backup = $idBackup",
            "bd.*, bc.symbol, bac.name as account, bcat.name as category",
            "backup_budgets bd, backup_currencies bc, backup_accounts bac, backup_categories bcat");*/
        $arreglo = array();
        if ($select) {
            $arreglo["error"] = false;
            $arreglo["budgets"] = $select;
            $arreglo["titulo"] = "¡ BUDGETS ENCONTRADOS !";
            $arreglo["msj"] = "Se encontraron budgets del respaldo solicitado.";
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ BUDGETS NO ENCONTRADOS !";
            $arreglo["msj"] = "No se encontraron budgets del respaldo solicitado.";
        }
        return $arreglo;
    }

    public function inconsistenciaBudget() {
        $data = json_decode(Form::getValue('dataUser', false, false));
        $this -> pagina = Form::getValue('pagina');
        $backups = json_decode(Form::getValue('backups', false, false));
        $arreglo = array();

        $this -> pagina = $this -> pagina * $this -> limit_Inconsistencia;
        $select = "bd.*, COUNT(bd.id_backup) cantidadRepetida";
        $table = "backup_budgets bd, backups b";
        $where = "b.id_backup = bd.id_backup ". $this -> condicionarConsulta($data -> id, "b.id_user", 0) . $this -> inBackups($backups, "bd.id_backup") . " GROUP BY ". $this -> namesColumns($this -> b -> nameColumns, "bd.") ." HAVING COUNT( * ) >= $this->having_Count limit $this->pagina, $this->limit_Inconsistencia";
        $arreglo["consultaSQL"] = $this -> consultaSQL($select, $table, $where);
        $consulta = $this -> b -> mostrar($where, $select, $table);
        if ($consulta) {
            $arreglo["error"] = false;
            $arreglo["budgets"] = $consulta;
            $arreglo["titulo"] = "¡ INCONSISTENCIAS ENCONTRADOS !";
            $arreglo["msj"] = "Se encontraron duplicidades de registros en la tabla Budgets ". (($data -> email != "Generales") ? "del usuario: $data->email" : "");
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ INCONSISTENCIAS NO ENCONTRADOS !";
            $arreglo["msj"] = "No se encontraron duplicidades de registros en la tabla Budgets ". (($data -> email != "Generales") ? "del usuario: $data->email" : "");
        }
        return $arreglo;
    }
    public function corregirInconsitencia() {
        $indices = $this -> b -> ejecutarCodigoSQL("SHOW INDEX from " . $this -> b -> nameTable);
        $arreglo = array();
        $arreglo["indice"] = false;
        foreach ($indices as $key => $value) {
            if ($value -> Key_name == "indiceUnico") { //Ya existe el indice unico... Entonces la tabla ya se encuentra corregida
                $arreglo["indice"] = true;
                $arreglo["msj"] = "Ya existe el campo unico en la tabla Budgets, por lo tanto ya se ha realizado la corrección de datos inconsistentes anteriormente.";
                $arreglo["titulo"] = "¡ TABLA CORREGIDA ANTERIORMENTE !";
                return $arreglo;
            }
        }
        $sql = $this -> sentenciaInconsistenicaSQL($this -> b -> nameTable, ['id_backup','id_account','id_category','period','amount','budget'], "id_backup");
        $operacion = $this -> b -> ejecutarMultSentMySQLi($sql);
        $arreglo["SenteciasSQL"] = $sql;
        $arreglo["Result"] = $operacion;
        return $arreglo;
    }
}