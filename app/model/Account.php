<?php
/**
 * Created by PhpStorm.
 * User: pc-hp
 * Date: 20/08/2019
 * Time: 11:35 PM
 */

class Account extends DB
{
    public function mostrar($where = "1", $select = "*", $tabla = "backup_accounts"){
        return $this -> getDatos($tabla, $select, $where);
    }
}