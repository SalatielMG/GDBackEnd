<?php
/**
 * Created by PhpStorm.
 * User: pc-01
 * Date: 21/08/2019
 * Time: 12:23
 */

class Automatic extends DB
{
    public $nameTable = "backup_automatics";
    public $columnsTable = [
        ["name" => "id_backup", "type" => Form::typeInt],
        ["name" => "id_operation", "type" => Form::typeInt],
        ["name" => "id_account", "type" => Form::typeSmallint],
        ["name" => "id_category", "type" => Form::typeSmallint],
        ["name" => "period", "type" => Form::typeTinyint],
        ["name" => "repeat_number", "type" => Form::typeTinyint],
        ["name" => "each_number", "type" => Form::typeTinyint],
        ["name" => "enabled", "type" => Form::typeTinyint],
        ["name" => "amount", "type" => Form::typeDecimal],
        ["name" => "sign", "type" => Form::typeChar],
        ["name" => "detail", "type" => Form::typeVarchar],
        ["name" => "initial_date", "type" => Form::typeDate],
        ["name" => "next_date", "type" => Form::typeDate],
        ["name" => "operation_code", "type" => Form::typeVarchar],
        ["name" => "rate", "type" => Form::typeDecimal],
        ["name" => "counter", "type" => Form::typeSmallint],
    ];
    public $columnsTableIndexUnique = [];

    public $nameTableSQLITE = "table_automatics";
    public $columnsTableSQLITE = [
        ["name" => "_id", "type" => Form::typeSQLITE_INTEGER],
        ["name" => "account", "type" => Form::typeSQLITE_TEXT],
        ["name" => "title", "type" => Form::typeSQLITE_TEXT],
        ["name" => "period", "type" => Form::typeSQLITE_INTEGER],
        ["name" => "each", "type" => Form::typeSQLITE_INTEGER],
        ["name" => "repeat", "type" => Form::typeSQLITE_INTEGER],
        ["name" => "counter", "type" => Form::typeSQLITE_INTEGER],
        ["name" => "initial_date", "type" => Form::typeSQLITE_TEXT],
        ["name" => "next_date", "type" => Form::typeSQLITE_TEXT],
        ["name" => "code", "type" => Form::typeSQLITE_TEXT],
        ["name" => "category", "type" => Form::typeSQLITE_TEXT],
        ["name" => "amount", "type" => Form::typeSQLITE_REAL],
        ["name" => "sign", "type" => Form::typeSQLITE_TEXT],
        ["name" => "detail", "type" => Form::typeSQLITE_TEXT],
        ["name" => "enabled", "type" => Form::typeSQLITE_INTEGER],
        ["name" => "selected", "type" => Form::typeSQLITE_INTEGER],
    ];

    public $nameSheetXLSX = "automatics";
    public $columnsSheetXLSX = [
        ["name" => "title", "column" => "A"],
        ["name" => "period", "column" => "B"],
        ["name" => "each", "column" => "C"],
        ["name" => "repeat", "column" => "D"],
        ["name" => "counter", "column" => "E"],
        ["name" => "initial_date", "column" => "F"],
        ["name" => "next_date", "column" => "G"],
        ["name" => "enabled", "column" => "H"],
        ["name" => "account", "column" => "I"],
        ["name" => "category", "column" => "J"],
        ["name" => "amount", "column" => "K"],
        ["name" => "sign", "column" => "L"],
    ];

    public function __construct()
    {
        parent::__construct();
        foreach ($this -> columnsTable as $key => $value) {
            if (($value["name"] == "id_backup")
                || ($value["name"] == "id_operation")
                || ($value["name"] == "id_account")
                || ($value["name"] == "id_category")
                || ($value["name"] == "period")
                || ($value["name"] == "repeat_number")
                || ($value["name"] == "each_number")
                || ($value["name"] == "amount")
                || ($value["name"] == "sign")
                || ($value["name"] == "detail")
                || ($value["name"] == "initial_date")) {
                array_push($this -> columnsTableIndexUnique, $value);
            }
        }
    }

    public function mostrar($where = "1", $select = "*", $tabla = "backup_automatics"){
        return $this -> getDatos($tabla, $select, $where);
    }

    public function agregar($dataAutomatic) {
        $automatic = [
          'id_backup' => $dataAutomatic -> id_backup,
          'id_operation' => $dataAutomatic -> id_operation,
          'id_account' => $dataAutomatic -> id_account,
          'id_category' => $dataAutomatic -> id_category,
          'period' => $dataAutomatic -> period,
          'repeat_number' => $dataAutomatic -> repeat_number,
          'each_number' => $dataAutomatic -> each_number,
          'enabled' => $dataAutomatic -> enabled,
          'amount' => $dataAutomatic -> amount,
          'sign' => "'" . $this -> signValue($dataAutomatic -> sign) . "'",
          'detail' => "'$dataAutomatic->detail'",
          'initial_date' => "'$dataAutomatic->initial_date'",
          'next_date' => "'$dataAutomatic->next_date'",
          'operation_code' => "'$dataAutomatic->operation_code'",
          'rate' => $dataAutomatic -> rate,
          'counter' => $dataAutomatic -> counter,
        ];
        return $this -> insert($this -> nameTable, $automatic);
    }

    public function actualizar($dataAutomatic, $indexUnique) {
        $automatic = [
            'id_account' => $dataAutomatic -> id_account,
            'id_category' => $dataAutomatic -> id_category,
            'period' => $dataAutomatic -> period,
            'repeat_number' => $dataAutomatic -> repeat_number,
            'each_number' => $dataAutomatic -> each_number,
            'enabled' => $dataAutomatic -> enabled,
            'amount' => $dataAutomatic -> amount,
            'sign' => "'" . $this -> signValue($dataAutomatic -> sign) . "'",
            'detail' => "'$dataAutomatic->detail'",
            'initial_date' => "'$dataAutomatic->initial_date'",
            'next_date' => "'$dataAutomatic->next_date'",
            'operation_code' => "'$dataAutomatic->operation_code'",
            'rate' => $dataAutomatic -> rate,
            'counter' => $dataAutomatic -> counter,
        ];
        return $this -> update($this -> nameTable, $automatic, Valida::conditionVerifyExistsUniqueIndex($indexUnique, $this -> columnsTableIndexUnique, false));
    }

    public function eliminar($indexUnique) {
        return $this -> delete($this -> nameTable, Valida::conditionVerifyExistsUniqueIndex($indexUnique, $this -> columnsTableIndexUnique, false));
    }

}