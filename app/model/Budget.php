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
    public $columnsTable = [
        ['name' => 'id_backup', 'type' => Form::typeInt],
        ['name' => 'id_account', 'type' => Form::typeSmallint],
        ['name' => 'id_category', 'type' => Form::typeSmallint],
        ['name' => 'period', 'type' => Form::typeTinyint],
        ['name' => 'amount', 'type' => Form::typeDecimal],
        ['name' => 'budget', 'type' => Form::typeDecimal],
        ['name' => 'initial_date', 'type' => Form::typeDate],
        ['name' => 'final_date', 'type' => Form::typeDate],
        ['name' => 'number', 'type' => Form::typeSmallint],
    ];
    public $columnsTableIndexUnique = [];

    public $nameTableSQLITE = "table_budgets";
    public $columnsTableSQLITE = [
        ["name" => "_id", "type" => Form::typeSQLITE_INTEGER],
        ["name" => "account", "type" => Form::typeSQLITE_TEXT],
        ["name" => "category", "type" => Form::typeSQLITE_TEXT],
        ["name" => "period", "type" => Form::typeSQLITE_INTEGER],
        ["name" => "amount", "type" => Form::typeSQLITE_REAL],
        ["name" => "budget", "type" => Form::typeSQLITE_REAL],
        ["name" => "initial_date", "type" => Form::typeSQLITE_TEXT],
        ["name" => "final_date", "type" => Form::typeSQLITE_TEXT],
        ["name" => "show", "type" => Form::typeSQLITE_TEXT],
        ["name" => "number", "type" => Form::typeSQLITE_INTEGER],
        ["name" => "selected", "type" => Form::typeSQLITE_INTEGER],
    ];

    public $nameSheetXLSX = "budgets";
    public $columnsSheetXLSX = [
        ["name" => "account", "column" => "A"],
        ["name" => "category", "column" => "B"],
        ["name" => "period", "column" => "C"],
        ["name" => "amount", "column" => "D"],
        ["name" => "budget", "column" => "E"],
    ];

    public function __construct()
    {
        parent::__construct();
        foreach ($this -> columnsTable as $key => $value) {
            if (($value["name"] == "id_backup")
                || ($value["name"] == "id_account")
                || ($value["name"] == "id_category")
                || ($value["name"] == "period")
                || ($value["name"] == "amount")
                || ($value["name"] == "budget")) {
                array_push($this -> columnsTableIndexUnique, $value);
            }
        }

    }

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
        if ($dataBudget -> initial_date != "0000-00-00") $budget["initial_date"] = "'$dataBudget->initial_date'";
        if ($dataBudget -> final_date != "0000-00-00") $budget["final_date"] = "'$dataBudget->final_date'";

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
        return $this -> update($this -> nameTable, $budget,Valida::conditionVerifyExistsUniqueIndex($indexUnique, $this -> columnsTableIndexUnique, false));
    }

    public function eliminar($indexUnique) {
        return $this -> delete($this -> nameTable, Valida::conditionVerifyExistsUniqueIndex($indexUnique, $this -> columnsTableIndexUnique, false));
    }
}