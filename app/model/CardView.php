<?php
/**
 * Created by PhpStorm.
 * User: pc-01
 * Date: 21/08/2019
 * Time: 12:39
 */

class CardView extends DB
{
    public $nameTable = "backup_cardviews";
    public $columnsTable = [
        ['name' => 'id_backup', 'type' => Form::typeInt],
        ['name' => 'id_card', 'type' => Form::typeInt],
        ['name' => 'name', 'type' => Form::typeVarchar],
        ['name' => 'period', 'type' => Form::typeVarchar],
        ['name' => 'sign', 'type' => Form::typeChar],
        ['name' => 'show_card', 'type' => Form::typeTinyint],
        ['name' => 'number', 'type' => Form::typeSmallint],
    ];
    public $columnsTableIndexUnique = [];

    public function __construct()
    {
        parent::__construct();
        foreach ($this -> columnsTable as $key => $value) {
            if (($value["name"] == "id_backup")
            || ($value["name"] == "id_card")
            //|| ($value["name"] == "name")
            ) {
                array_push($this -> columnsTableIndexUnique, $value);
            }
        }
    }

    public function mostrar($where = "1", $select = "*", $tabla = "backup_cardviews"){
        return $this -> getDatos($tabla, $select, $where);
    }

    public function agregar ($dataCardview) {
        $cardview = Valida::arrayDataOperation($this -> columnsTable, $dataCardview);
        return $this -> insert($this -> nameTable, $cardview);
    }

    public function actualizar ($dataCardview, $indexUnique) {
        $cardview = Valida::arrayDataOperation($this -> columnsTable, $dataCardview, ["id_backup"]);
        return $this -> update($this -> nameTable, $cardview, Valida::conditionVerifyExistsUniqueIndex($indexUnique, $this -> columnsTableIndexUnique, false));
    }

    public function eliminar ($indexUnique) {
        return $this -> delete($this -> nameTable, Valida::conditionVerifyExistsUniqueIndex($indexUnique, $this -> columnsTableIndexUnique, false));
    }
    
}