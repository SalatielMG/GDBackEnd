<?php
/**
 * Created by PhpStorm.
 * User: pc-01
 * Date: 21/08/2019
 * Time: 14:04
 */

class Category extends DB
{
    public $nameTable = "backup_categories";
    public $columnsTable = [
         ['name' => 'id_backup', 'type' => Form::typeInt],
         ['name' => 'id_category', 'type' => Form::typeSmallint],
         ['name' => 'id_account', 'type' => Form::typeSmallint],
         ['name' => 'name', 'type' => Form::typeVarchar],
         ['name' => 'sign', 'type' => Form::typeChar],
         ['name' => 'icon_name', 'type' => Form::typeVarchar],
         ['name' => 'number', 'type' => Form::typeSmallint],
    ];
    public $columnsTableIndexUnique = [];

    public function __construct()
    {
        parent::__construct();
        foreach ($this -> columnsTable as $key => $value) {
            if (($value["name"] == "id_backup")
                || ($value["name"] == "id_account")
                || ($value["name"] == "name")
                || ($value["name"] == "sign")) {
                array_push($this -> columnsTableIndexUnique, $value);
            }
        }
    }
    public function mostrar($where = "1", $select = "*", $tabla = "backup_categories"){
        return $this -> getDatos($tabla, $select, $where);
    }
    public function agregar($dataCategory) {
        $category = [
            "id_backup" => $dataCategory -> id_backup,
            "id_category" => $dataCategory -> id_category,
            "id_account" => $dataCategory -> id_account,
            "name" => "'$dataCategory->name'",
            "sign" => "'" . $this -> signValue($dataCategory -> sign) . "'",
            "icon_name" => "'$dataCategory->icon_name'",
            "number" => $dataCategory -> number,
        ];
        return $this -> insert($this -> nameTable, $category);
    }
    public function actualizar($dataCategory, $indexUnique) {
        $category = [
            "id_category" => $dataCategory -> id_category,
            "id_account" => $dataCategory -> id_account,
            "name" => "'$dataCategory->name'",
            "sign" => "'" . $this -> signValue($dataCategory -> sign) . "'",
            "icon_name" => "'$dataCategory->icon_name'",
            "number" => $dataCategory -> number,
        ];
        return $this -> update($this -> nameTable, $category, "id_backup = $indexUnique->id_backup and id_account = $indexUnique->id_account AND id_category = $indexUnique->id_category");
    }
    public function eliminar($indexUnique) {
        return $this -> delete($this -> nameTable, "id_backup = $indexUnique->id_backup and id_account = $indexUnique->id_account AND id_category = $indexUnique->id_category");
    }
}