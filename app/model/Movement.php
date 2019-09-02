<?php
/**
 * Created by PhpStorm.
 * User: pc-01
 * Date: 21/08/2019
 * Time: 14:42
 */

class Movement extends DB
{
    public $nameTable = "backup_movements";
    public $nameColumns = ['id_backup', 'id_account', 'id_category', 'amount', 'sign', 'detail', 'date_record', 'time_record', 'confirmed', 'transfer', 'date_idx', 'day', 'week', 'fortnight', 'month', 'year', 'operation_code', 'picture', 'iso_code'];
    public function mostrar($where = "1", $select = "*", $tabla = "backup_movements"){
        return $this -> getDatos($tabla, $select, $where);
    }
}