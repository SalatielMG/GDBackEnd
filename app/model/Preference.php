<?php
/**
 * Created by PhpStorm.
 * User: pc-01
 * Date: 21/08/2019
 * Time: 14:46
 */

class Preference extends DB
{
    public $nameTable = "backup_preferences";
    public $columnsTable = [
         ['name' => 'id_backup', 'type' => Form::typeInt],
         ['name' => 'key_name', 'type' => Form::typeVarchar],
         ['name' => 'value', 'type' => Form::typeVarchar],
    ];

    public $columnsTableIndexUnique = [];
    public $nameTableSQLITE = "table_preferences";
    public $columnsTableSQLITE = [
        ["name" => "_id", "type" => Form::typeSQLITE_INTEGER],
        ["name" => "key", "type" => Form::typeSQLITE_TEXT],
        ["name" => "value", "type" => Form::typeSQLITE_TEXT],
    ];

    public function __construct()
    {
        parent::__construct();
        foreach ($this -> columnsTable as $key => $value) {
            if (($value["name"] == "id_backup")
            || ($value["name"] == "key_name")) {
                array_push($this -> columnsTableIndexUnique, $value);
            }
        }
    }

    public function mostrar($where = "1", $select = "*", $tabla = "backup_preferences") {
        return $this -> getDatos($tabla, $select, $where);
    }

    public function agregar ($dataPreference) {
        $preference = Valida::arrayDataOperation($this -> columnsTable, $dataPreference);
        return $this -> insert($this -> nameTable, $preference);
    }

    public function actualizar ($dataPreference, $indexUnique) {
        $preference = Valida::arrayDataOperation($this -> columnsTable, $dataPreference, ["id_backup"]);
        return $this -> update($this -> nameTable, $preference, Valida::conditionVerifyExistsUniqueIndex($indexUnique, $this -> columnsTableIndexUnique, false));
    }

    public function eliminar ($indexUnique) {
        return $this -> delete($this -> nameTable, Valida::conditionVerifyExistsUniqueIndex($indexUnique, $this -> columnsTableIndexUnique, false));
    }

}