<?php
/**
 * Created by PhpStorm.
 * User: pc-01
 * Date: 01/11/2019
 * Time: 11:36 AM
 */

class Permiso extends DB
{
    public $nameTable = "permisos";
    public $nameTableMM = "usuarios_permisos";

    public function mostrar($where = "1", $select = "*", $table = "permisos") {
        return $this -> getDatos($table, $select, $where);
    }


    public function eliminar($permiso) {
        return $this -> delete($this -> nameTable, "permiso = $permiso");
    }
    public function eliminarUsuario_Permiso($permiso) {
        return $this -> delete($this -> nameTableMM, "permiso = $permiso");
    }
}