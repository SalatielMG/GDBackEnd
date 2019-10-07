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

}