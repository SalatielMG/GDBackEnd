<?php
/**
 * Created by PhpStorm.
 * User: pc-hp
 * Date: 20/08/2019
 * Time: 11:35 PM
 */

class Account extends DB
{
    public $nameTable = "backup_accounts";
    public $columnsTable = [
        ['name' => 'id_backup', 'type' => Form::typeInt],
        ['name' => 'id_account', 'type' => Form::typeSmallint],
        ['name' => 'name', 'type' => Form::typeVarchar],
        ['name' => 'detail', 'type' => Form::typeVarchar],
        ['name' => 'sign', 'type' => Form::typeChar],
        ['name' => 'income', 'type' => Form::typeDecimal],
        ['name' => 'expense', 'type' => Form::typeDecimal],
        ['name' => 'initial_balance', 'type' => Form::typeDecimal],
        ['name' => 'final_balance', 'type' => Form::typeDecimal],
        ['name' => 'month', 'type' => Form::typeTinyint],
        ['name' => 'year', 'type' => Form::typeSmallint],
        ['name' => 'positive_limit', 'type' => Form::typeTinyint],
        ['name' => 'negative_limit', 'type' => Form::typeTinyint],
        ['name' => 'positive_max', 'type' => Form::typeDecimal],
        ['name' => 'negative_max', 'type' => Form::typeDecimal],
        ['name' => 'iso_code', 'type' => Form::typeChar],
        ['name' => 'selected', 'type' => Form::typeTinyint],
        ['name' => 'value_type', 'type' => Form::typeTinyint],
        ['name' => 'include_total', 'type' => Form::typeTinyint],
        ['name' => 'rate', 'type' => Form::typeDecimal],
        ['name' => 'icon_name', 'type' => Form::typeVarchar],
    ];
    public $columnsTableIndexUnique = [];

    public $nameTableSQLITE = "table_accounts";
    public $columnsTableSQLITE = [
        ["name" => "_id", "type" => Form::typeSQLITE_INTEGER],
        ["name" => "account", "type" => Form::typeSQLITE_TEXT],
        ["name" => "detail", "type" => Form::typeSQLITE_TEXT],
        ["name" => "initial_balance", "type" => Form::typeSQLITE_REAL],
        ["name" => "sign", "type" => Form::typeSQLITE_TEXT],
        ["name" => "icon", "type" => Form::typeSQLITE_TEXT],
        ["name" => "income", "type" => Form::typeSQLITE_REAL],
        ["name" => "expense", "type" => Form::typeSQLITE_REAL],
        ["name" => "balance", "type" => Form::typeSQLITE_REAL],
        ["name" => "month", "type" => Form::typeSQLITE_TEXT],
        ["name" => "year", "type" => Form::typeSQLITE_TEXT],
        ["name" => "negative_max", "type" => Form::typeSQLITE_TEXT],
        ["name" => "positive_max", "type" => Form::typeSQLITE_TEXT],
        ["name" => "iso_code", "type" => Form::typeSQLITE_TEXT],
        ["name" => "rate", "type" => Form::typeSQLITE_REAL],
        ["name" => "include_total", "type" => Form::typeSQLITE_INTEGER],
        ["name" => "value_type", "type" => Form::typeSQLITE_INTEGER],
        ["name" => "selected", "type" => Form::typeSQLITE_INTEGER],
    ];
    public $nameSheetXLSX = "accounts";
    public $columnsSheetXLSX = [
        ["name" => "account", "column" => "A"],
        ["name" => "detail", "column" => "B"],
        ["name" => "initial_balance", "column" => "C"],
        ["name" => "sign", "column" => "D"],
        ["name" => "income", "column" => "E"],
        ["name" => "expense", "column" => "F"],
        ["name" => "balance", "column" => "G"],
        ["name" => "iso_code", "column" => "H"],
    ];

    public function __construct()
    {
        parent::__construct();
        foreach ($this -> columnsTable as $key => $value) {
            if (($value["name"] == "id_backup")
                || ($value["name"] == "id_account")
                || ($value["name"] == "name")) {
                array_push($this -> columnsTableIndexUnique, $value);
            }
        }
    }

    public function mostrar($where = "1", $select = "*", $tabla = "backup_accounts"){
        return $this -> getDatos($tabla, $select, $where);
    }
    public function agregar($data) {
        $account = [
            'id_backup' => $data -> id_backup,
            'id_account' => $data -> id_account,
            'name' => "'$data->name'",
            'detail' => "'$data->detail'",
            'sign' => "'" . $this -> signValue($data -> sign) . "'",
            'income' => $data->income,
            'expense' => $data->expense,
            'initial_balance' => $data->initial_balance,
            'final_balance' => $data->final_balance,
            'month' => $data->month,
            'year' => $data->year,
            'positive_limit' => $data->positive_limit,
            'negative_limit' => $data->negative_limit,
            'positive_max' => $data->positive_max,
            'negative_max' => $data->negative_max,
            'iso_code' => "'$data->iso_code'",
            'selected' => $data->selected,
            'value_type' => $data->value_type,
            'include_total' => $data->include_total,
            'rate' => $data->rate,
            'icon_name' => "'$data->icon_name'"
        ];
        return $this -> insert($this -> nameTable, $account);
    }
    public function actualizar ($data, $indexUnique) {
        $account = [
            'name' => "'$data->name'",
            'detail' => "'$data->detail'",
            'sign' => "'" . $this -> signValue($data -> sign) . "'",
            'income' => $data->income,
            'expense' => $data->expense,
            'initial_balance' => $data->initial_balance,
            'final_balance' => $data->final_balance,
            'month' => $data->month,
            'year' => $data->year,
            'positive_limit' => $data->positive_limit,
            'negative_limit' => $data->negative_limit,
            'positive_max' => $data->positive_max,
            'negative_max' => $data->negative_max,
            'iso_code' => "'$data->iso_code'",
            'selected' => $data->selected,
            'value_type' => $data->value_type,
            'include_total' => $data->include_total,
            'rate' => $data->rate,
            'icon_name' => "'$data->icon_name'"
        ];
        return $this -> update($this-> nameTable, $account, Valida::conditionVerifyExistsUniqueIndex($indexUnique, $this -> columnsTableIndexUnique, false) . " AND id_account = $indexUnique->id_account");
    }
    public function eliminar($indexUnique) {
        return $this -> delete($this -> nameTable, Valida::conditionVerifyExistsUniqueIndex($indexUnique, $this -> columnsTableIndexUnique, false) . " AND id_account = $indexUnique->id_account");
    }
}