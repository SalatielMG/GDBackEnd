<?php
/**
 * Created by PhpStorm.
 * User: pc-01
 * Date: 21/08/2019
 * Time: 14:46
 */

class Preference extends DB
{
    public $nameTable = "backup_preferences";
    public $nameColumns = ['id_backup', 'key_name', 'value'];
    public function mostrar($where = "1", $select = "*", $tabla = "backup_preferences"){
        return $this -> getDatos($tabla, $select, $where);
    }
}