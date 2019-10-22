<?php
/**
 * Created by PhpStorm.
 * User: pc-01
 * Date: 21/08/2019
 * Time: 14:39
 */

class Extra extends DB
{

    public $nameTable = "backup_extras";
    public $columnsTable = [
        ['name' => 'id_backup', 'type' => Form::typeInt],
        ['name' => 'id_extra', 'type' => Form::typeSmallint],
        ['name' => 'account', 'type' => Form::typeVarchar],
        ['name' => 'category', 'type' => Form::typeVarchar],
    ];
    public $columnsTableIndexUnique = [];

    public function __construct()
    {
        parent::__construct();
        foreach ($this -> columnsTable as $key => $value) {
            if (($value["name"] == "id_backup")
            || ($value["name"] == "id_extra")) {
                array_push($this -> columnsTableIndexUnique, $value);
            }
        }
    }

    public function mostrar($where = "1", $select = "*", $tabla = "backup_extras"){
        return $this -> getDatos($tabla, $select, $where);
    }

    public function agregar ($dataExtra) {
        $extra = Valida::arrayDataOperation($this -> columnsTable, $dataExtra);
        return $this -> insert($this -> nameTable, $extra);
    }

    public function actualizar ($dataExtra, $indexUnique) {
        $extra = Valida::arrayDataOperation($this -> columnsTable, $dataExtra, ["id_backup"]);
        return $this -> update($this -> nameTable, $extra, Valida::conditionVerifyExistsUniqueIndex($indexUnique, $this -> columnsTableIndexUnique, false));
    }

    public function eliminar ($indexUnique) {
        return $this -> delete($this -> nameTable, Valida::conditionVerifyExistsUniqueIndex($indexUnique, $this -> columnsTableIndexUnique, false));
    }

}