<?php
/**
 * Created by PhpStorm.
 * User: pc-01
 * Date: 21/08/2019
 * Time: 12:32
 */

class Budget extends DB
{
    public $nameTable = "backup_budgets";
    public $nameColumnsIndexUnique = ['id_backup','id_account','id_category','period','amount','budget'];
    public $nameColumns = ['id_backup','id_account','id_category','period','amount','budget','initial_date','final_date','number'];
    public function mostrar($where = "1", $select = "*", $tabla = "backup_budgets"){
        return $this -> getDatos($tabla, $select, $where);
    }

    public function agregar($dataBudget) {
        $budget = [
            'id_backup' => $dataBudget -> id_backup,
            'id_account' => $dataBudget -> id_account,
            'id_category' => $dataBudget -> id_category,
            'period' => $dataBudget -> period,
            'amount' => $dataBudget -> amount,
            'budget' => $dataBudget -> budget,
            'number' => $dataBudget -> number,
        ];
        if ($dataBudget -> initial_date != "0000-00-00") $backup["initial_date"] = "'$dataBudget->initial_date'";
        if ($dataBudget -> final_date != "0000-00-00") $backup["final_date"] = "'$dataBudget->final_date'";

        return $this -> insert($this -> nameTable, $budget);
    }

    public function actualizar($dataBudget, $indexUnique) {
        $budget = [
            'id_account' => $dataBudget -> id_account,
            'id_category' => $dataBudget -> id_category,
            'period' => $dataBudget -> period,
            'amount' => $dataBudget -> amount,
            'budget' => $dataBudget -> budget,
            'initial_date' => "'$dataBudget->initial_date'",
            'final_date' => "'$dataBudget->final_date'",
            'number' => $dataBudget -> number,
        ];
        return $this -> update($this -> nameTable, $budget,"id_backup = $indexUnique->id_backup AND id_account = $indexUnique->id_account AND id_category = $indexUnique->id_category");
    }

    public function eliminar($indexUnique) {
        return $this -> delete($this -> nameTable, "id_backup = $indexUnique->id_backup AND id_account = $indexUnique->id_account AND id_category = $indexUnique->id_category");
    }
}