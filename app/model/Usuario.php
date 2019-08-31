<?php
/**
 * Created by PhpStorm.
 * Users: pc-01
 * Date: 15/08/2019
 * Time: 11:25
 */

class Usuario extends DB
{
    public function mostrar($where = "1", $select = "*"){
        return $this -> getDatos('usuarios', $select, $where);
    }
}