<?php
/**
 * Created by PhpStorm.
 * User: pc-01
 * Date: 21/08/2019
 * Time: 14:39
 */

class Extra extends DB
{
    public function mostrar($where = "1", $select = "*", $tabla = "backup_extras"){
        return $this -> getDatos($tabla, $select, $where);
    }
}