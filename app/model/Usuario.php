<?php
/**
 * Created by PhpStorm.
 * Users: pc-01
 * Date: 15/08/2019
 * Time: 11:25
 */

class Usuario extends DB
{
    public $nameTable = "usuarios";
    public $nameTableMM = "usuarios_permisos";

    public function mostrar($where = "1", $select = "*", $table = "usuarios"){
        return $this -> getDatos($table, $select, $where);
    }
}