<?php
/**
 * Created by PhpStorm.
 * Users: pc-hp
 * Date: 16/08/2019
 * Time: 12:37 PM
 */

class Users extends DB
{
    public function mostrar($where = "1", $select = "*", $tabla = "users"){
        return $this -> getDatos($tabla, $select, $where);
    }
}