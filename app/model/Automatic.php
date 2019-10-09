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

    public function agregar($dataAutomatic) {
        $automatic = [
          'id_backup' => $dataAutomatic -> id_backup,
          'id_operation' => $dataAutomatic -> id_operation,
          'id_account' => $dataAutomatic -> id_account,
          'id_category' => $dataAutomatic -> id_category,
          'period' => $dataAutomatic -> period,
          'repeat_number' => $dataAutomatic -> repeat_number,
          'each_number' => $dataAutomatic -> each_number,
          'enabled' => $dataAutomatic -> enabled,
          'amount' => $dataAutomatic -> amount,
          'sign' => "'" . $this -> signValue($dataAutomatic -> sign) . "'",
          'detail' => "'$dataAutomatic->detail'",
          'initial_date' => "'$dataAutomatic->initial_date'",
          'next_date' => "'$dataAutomatic->next_date'",
          'operation_code' => "'$dataAutomatic->operation_code'",
          'rate' => $dataAutomatic -> rate,
          'counter' => $dataAutomatic -> counter,
        ];
        return $this -> insert($this -> nameTable, $automatic);
    }

    public function actualizar($dataAutomatic, $indexUnique) {
        $automatic = [
            'id_account' => $dataAutomatic -> id_account,
            'id_category' => $dataAutomatic -> id_category,
            'period' => $dataAutomatic -> period,
            'repeat_number' => $dataAutomatic -> repeat_number,
            'each_number' => $dataAutomatic -> each_number,
            'enabled' => $dataAutomatic -> enabled,
            'amount' => $dataAutomatic -> amount,
            'sign' => "'" . $this -> signValue($dataAutomatic -> sign) . "'",
            'detail' => "'$dataAutomatic->detail'",
            'initial_date' => "'$dataAutomatic->initial_date'",
            'next_date' => "'$dataAutomatic->next_date'",
            'operation_code' => "'$dataAutomatic->operation_code'",
            'rate' => $dataAutomatic -> rate,
            'counter' => $dataAutomatic -> counter,
        ];
        return $this -> update($this -> nameTable, $automatic, "id_backup = $indexUnique->id_backup AND id_operation = $indexUnique->id_operation AND id_account = $indexUnique->id_account AND id_category = $indexUnique->id_category");
    }

    public function eliminar($indexUnique) {
        return $this -> delete($this -> nameTable, "id_backup = $indexUnique->id_backup AND id_operation = $indexUnique->id_operation AND id_account = $indexUnique->id_account AND id_category = $indexUnique->id_category");
    }

}