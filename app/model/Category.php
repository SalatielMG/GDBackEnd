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
    public $nameColumnsIndexUnique = ['id_backup', 'id_category', 'id_account'];
    public $nameColumns = ['id_backup', 'id_category', 'id_account', 'name', 'sign', 'icon_name', 'number'];
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