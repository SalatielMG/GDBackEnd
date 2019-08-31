<?php
/**
 * Created by PhpStorm.
 * User: pc-hp
 * Date: 17/08/2019
 * Time: 12:36 PM
 */

class Backup extends DB
{
    public function mostrar($where = "1", $select = "*", $tabla = "backups"){
        return $this -> getDatos($tabla, $select, $where);
    }
    public function eliminar($id) {
        return $this -> delete("backups", "id_backup = $id");
    }
}