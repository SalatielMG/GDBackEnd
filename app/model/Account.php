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
    public $nameColumnsIndexUnique = ['id_backup', 'id_account'];
    public $nameColumns = ['id_backup', 'id_account', 'name', 'detail', 'sign', 'income', 'expense', 'initial_balance', 'final_balance', 'month', 'year', 'positive_limit', 'negative_limit', 'positive_max', 'negative_max', 'iso_code', 'selected', 'value_type', 'include_total', 'rate', 'icon_name'];
    public function mostrar($where = "1", $select = "*", $tabla = "backup_accounts"){
        return $this -> getDatos($tabla, $select, $where);
    }
    public function agregar($data) {
        $account = [
            'id_backup' => $data -> id_backup,
            'id_account' => $data -> id_account,
            'name' => "'$data->name'",
            'detail' => "'$data->detail'",
            'sign' => "'" . $this -> signValue($data -> sign) . "'",
            'income' => $data->income,
            'expense' => $data->expense,
            'initial_balance' => $data->initial_balance,
            'final_balance' => $data->final_balance,
            'month' => $data->month,
            'year' => $data->year,
            'positive_limit' => $data->positive_limit,
            'negative_limit' => $data->negative_limit,
            'positive_max' => $data->positive_max,
            'negative_max' => $data->negative_max,
            'iso_code' => "'$data->iso_code'",
            'selected' => $data->selected,
            'value_type' => $data->value_type,
            'include_total' => $data->include_total,
            'rate' => $data->rate,
            'icon_name' => "'$data->icon_name'"
        ];
        return $this -> insert($this -> nameTable, $account);
    }
    public function actualizar ($data, $indexUnique) {
        $account = [
            'name' => "'$data->name'",
            'detail' => "'$data->detail'",
            'sign' => "'" . $this -> signValue($data -> sign) . "'",
            'income' => $data->income,
            'expense' => $data->expense,
            'initial_balance' => $data->initial_balance,
            'final_balance' => $data->final_balance,
            'month' => $data->month,
            'year' => $data->year,
            'positive_limit' => $data->positive_limit,
            'negative_limit' => $data->negative_limit,
            'positive_max' => $data->positive_max,
            'negative_max' => $data->negative_max,
            'iso_code' => "'$data->iso_code'",
            'selected' => $data->selected,
            'value_type' => $data->value_type,
            'include_total' => $data->include_total,
            'rate' => $data->rate,
            'icon_name' => "'$data->icon_name'"
        ];
        return $this -> update($this-> nameTable, $account, "id_backup = $indexUnique->id_backup and id_account = $indexUnique->id_account");
    }
    public function eliminar($indexUnique) {
        return $this -> delete($this -> nameTable, "id_backup = $indexUnique->id_backup and id_account = $indexUnique->id_account");
    }
}