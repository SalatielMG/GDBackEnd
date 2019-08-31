<?php
/**
 * Created by PhpStorm.
 * User: pc-01
 * Date: 21/08/2019
 * Time: 14:04
 */

class Category extends DB
{
    public function mostrar($where = "1", $select = "*", $tabla = "backup_categories"){
    return $this -> getDatos($tabla, $select, $where);
}

}