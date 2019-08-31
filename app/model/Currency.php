<?php
/**
 * Created by PhpStorm.
 * User: pc-01
 * Date: 21/08/2019
 * Time: 14:32
 */

class Currency extends DB
{
    public function mostrar($where = "1", $select = "*", $tabla = "backup_currencies"){
        return $this -> getDatos($tabla, $select, $where);
    }
}