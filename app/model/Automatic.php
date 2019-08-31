<?php
/**
 * Created by PhpStorm.
 * User: pc-01
 * Date: 21/08/2019
 * Time: 12:23
 */

class Automatic extends DB
{
    public function mostrar($where = "1", $select = "*", $tabla = "backup_automatics"){
        return $this -> getDatos($tabla, $select, $where);
    }
}