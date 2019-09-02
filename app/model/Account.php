<?php
/**
 * Created by PhpStorm.
 * User: pc-hp
 * Date: 20/08/2019
 * Time: 11:35 PM
 */

class Account extends DB
{
    public $nameTable = "backup_accounts";
    public $nameColumns = ['id_backup', 'id_account', 'name', 'detail', 'sign', 'income', 'expense', 'initial_balance', 'final_balance', 'month', 'year', 'positive_limit', 'negative_limit', 'positive_max', 'negative_max', 'iso_code', 'selected', 'value_type', 'include_total', 'rate', 'icon_name'];
    public function mostrar($where = "1", $select = "*", $tabla = "backup_accounts"){
        return $this -> getDatos($tabla, $select, $where);
    }
}