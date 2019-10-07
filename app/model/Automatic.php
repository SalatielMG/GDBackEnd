<?php
/**
 * Created by PhpStorm.
 * User: pc-01
 * Date: 21/08/2019
 * Time: 12:23
 */

class Automatic extends DB
{
    public $nameTable = "backup_automatics";
    public $nameColumnsIndexUnique = ['id_backup', 'id_operation', 'id_account','id_category', 'period', 'amount', 'initial_date'];
    public $nameColumns = ['id_backup', 'id_operation', 'id_account', 'id_category', 'period', 'repeat_number', 'each_number', 'enabled', 'amount', 'sign', 'detail', 'initial_date', 'next_date', 'operation_code', 'rate', 'counter'];
    public function mostrar($where = "1", $select = "*", $tabla = "backup_automatics"){
        return $this -> getDatos($tabla, $select, $where);
    }
}