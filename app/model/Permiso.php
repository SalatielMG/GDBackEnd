<?php
/**
 * Created by PhpStorm.
 * User: pc-01
 * Date: 01/11/2019
 * Time: 11:36 AM
 */

class Permiso extends DB
{
    public function mostrar($where = "1", $select = "*", $table = "permisos") {
        return $this -> getDatos($table, $select, $where);
    }
}