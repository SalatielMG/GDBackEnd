<?php
/**
 * Created by PhpStorm.
 * User: pc-01
 * Date: 21/08/2019
 * Time: 14:42
 */

class Movement extends DB
{
    public $nameTable = "backup_movements";
    public $columnsTableIndexUnique = [];
    public $columnsTable = [
         ['name' => 'id_backup', 'type' => Form::typeInt],
         ['name' => 'id_account', 'type' => Form::typeSmallint],
         ['name' => 'id_category', 'type' => Form::typeSmallint],
         ['name' => 'amount', 'type' => Form::typeDecimal],
         ['name' => 'sign', 'type' => Form::typeChar],
         ['name' => 'detail', 'type' => Form::typeVarchar],
         ['name' => 'date_record', 'type' => Form::typeDate],
         ['name' => 'time_record', 'type' => Form::typeTime],
         ['name' => 'confirmed', 'type' => Form::typeTinyint],
         ['name' => 'transfer', 'type' => Form::typeTinyint],
         ['name' => 'date_idx', 'type' => Form::typeVarchar],
         ['name' => 'day', 'type' => Form::typeTinyint],
         ['name' => 'week', 'type' => Form::typeTinyint],
         ['name' => 'fortnight', 'type' => Form::typeTinyint],
         ['name' => 'month', 'type' => Form::typeTinyint],
         ['name' => 'year', 'type' => Form::typeSmallint],
         ['name' => 'operation_code', 'type' => Form::typeVarchar],
         ['name' => 'picture', 'type' => Form::typeVarchar],
         ['name' => 'iso_code', 'type' => Form::typeChar],
    ];

    public $nameTableSQLITE = "table_movements";
    public $columnsTableSQLITE = [
        ["name" => "_id", "type" => Form::typeSQLITE_INTEGER],
        ["name" => "account", "type" => Form::typeSQLITE_TEXT],
        ["name" => "category", "type" => Form::typeSQLITE_TEXT],
        ["name" => "amount", "type" => Form::typeSQLITE_REAL],
        ["name" => "sign", "type" => Form::typeSQLITE_TEXT],
        ["name" => "detail", "type" => Form::typeSQLITE_TEXT],
        ["name" => "date", "type" => Form::typeSQLITE_TEXT],
        ["name" => "time", "type" => Form::typeSQLITE_TEXT],
        ["name" => "confirmed", "type" => Form::typeSQLITE_INTEGER],
        ["name" => "transfer", "type" => Form::typeSQLITE_INTEGER],
        ["name" => "date_idx", "type" => Form::typeSQLITE_TEXT],
        ["name" => "day", "type" => Form::typeSQLITE_TEXT],
        ["name" => "week", "type" => Form::typeSQLITE_TEXT],
        ["name" => "fortnight", "type" => Form::typeSQLITE_TEXT],
        ["name" => "month", "type" => Form::typeSQLITE_TEXT],
        ["name" => "year", "type" => Form::typeSQLITE_TEXT],
        ["name" => "code", "type" => Form::typeSQLITE_TEXT],
        ["name" => "picture", "type" => Form::typeSQLITE_TEXT],
        ["name" => "iso_code", "type" => Form::typeSQLITE_TEXT],
        ["name" => "selected", "type" => Form::typeSQLITE_INTEGER],
    ];

    public $nameSheetXLSX = "movements";
    public $columnsSheetXLSX = [
        ["name" => "account", "column" => "A"],
        ["name" => "category", "column" => "B"],
        ["name" => "amount", "column" => "C"],
        ["name" => "sign", "column" => "D"],
        ["name" => "detail", "column" => "E"],
        ["name" => "date", "column" => "F"],
        ["name" => "time", "column" => "G"],
    ];

    public function __construct()
    {
        parent::__construct();
        foreach ($this -> columnsTable as $key => $value) {
            if (($value["name"] == "id_backup")
                || ($value["name"] == "id_account")
                || ($value["name"] == "id_category")
                || ($value["name"] == "amount")
                || ($value["name"] == "detail")
                || ($value["name"] == "date_idx")) {
                array_push($this -> columnsTableIndexUnique, $value);
            }
        }
    }

    public function mostrar($where = "1", $select = "*", $tabla = "backup_movements"){
        return $this -> getDatos($tabla, $select, $where);
    }
    public function agregar ($dataMovement) {
        $movement = Valida::arrayDataOperation($this -> columnsTable, $dataMovement);
        //return $movement;
        return $this -> insert($this -> nameTable, $movement);
    }
    public function actualizar ($dataMovement, $indexUnique) {
        $movement = Valida::arrayDataOperation($this -> columnsTable, $dataMovement, ["id_backup"]);
        //return $movement;
        return $this -> update($this -> nameTable, $movement, Valida::conditionVerifyExistsUniqueIndex($indexUnique, $this -> columnsTableIndexUnique, false));
    }
    public function eliminar ($indexUnique) {
        return $this -> delete($this -> nameTable, Valida::conditionVerifyExistsUniqueIndex($indexUnique, $this -> columnsTableIndexUnique, false));
    }
}