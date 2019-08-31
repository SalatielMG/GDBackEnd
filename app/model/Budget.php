<?php
/**
 * Created by PhpStorm.
 * User: pc-01
 * Date: 21/08/2019
 * Time: 12:32
 */

class Budget extends DB
{
    public function mostrar($where = "1", $select = "*", $tabla = "backup_budgets"){
        return $this -> getDatos($tabla, $select, $where);
    }
}