<?php

// require_once(APP_PATH."model/Usuario.php");
// require_once(APP_PATH."model/Permiso.php");
class Valida {
    protected $limit = 50;
    protected $limit_Inconsistencia = 10;
    protected $having_Count = 2;

    public function keyValueArray($arreglo){
        $string = "";
        $dataObject = (array) ($arreglo);

        foreach ($dataObject as $key => $value) {
            $string .= "$key: " . (($key == "sign") ? DB::signValue($value) : $value) . ", ";
        }
        $string = substr_replace($string, "", strlen($string) - 2);
        return $string;
    }

    public static function conditionVerifyExistsUniqueIndex($dataObjec, $columnsTableIndexUnique) {
        $sql = "";
        $dataObject = (array) ($dataObjec);
        foreach ($columnsTableIndexUnique as $key => $value) {
            if ($value["type"] == Form::typeTinyint
                || $value["type"] == Form::typeInt
                || $value["type"] == Form::typeTinyint
                || $value["type"] == Form::typeSmallint
                || $value["type"] == Form::typeDecimal) {
                $sql .=  $value["name"] . " = " . $dataObject[$value["name"]] . " AND ";
            } else {
                if ($value["type"] == Form::typeDate || $value["type"] == Form::typeTime || $value["type"] == Form::typeDatetime) { //NO UPPERCASE
                    $sql .= $value["name"] . " = '" . $dataObject[$value["name"]] . "' AND ";
                } else if ($value["name"] == "sign" && $value["type"] == Form::typeChar){ //NO UPPERCASE
                    $sql .= $value["name"] . " = '" . DB::signValue($dataObject[$value["name"]]) . "' AND ";
                } else if ($value["name"] == "period" && $value["type"] == Form::typeVarchar) { //NOUPPERCASE
                    $sql .= $value["name"] . " = '" . $dataObject[$value["name"]] . "' AND ";
                } else if ($value["name"] == "operation_code" && $value["type"] == Form::typeVarchar) { //NOUPPERCASE
                    $sql .= $value["name"] . " = '" . $dataObject[$value["name"]] . "' AND ";
                } else if ($value["name"] == "date_idx" && $value["type"] == Form::typeVarchar) { //NOUPPERCASE
                    $sql .= $value["name"] . " = '" . $dataObject[$value["name"]] . "' AND ";
                } else if ($value["name"] == "picture" && $value["type"] == Form::typeVarchar) { //NOUPPERCASE
                    $sql .= $value["name"] . " = '" . $dataObject[$value["name"]] . "' AND ";
                } else if ($value["name"] == "symbol" && $value["type"] == Form::typeChar) { //NOUPPERCASE
                    $sql .= $value["name"] . " = '" . $dataObject[$value["name"]] . "' AND ";
                } else if ($value["name"] == "icon_name" && $value["type"] == Form::typeChar) { //NOUPPERCASE
                    $sql .= $value["name"] . " = '" . $dataObject[$value["name"]] . "' AND ";
                } else { //UPPERCASE
                    $sql .= "UPPER(" . $value["name"] . ") = UPPER('" . $dataObject[$value["name"]] . "') AND ";
                }
            }
        }
        $sql = substr_replace($sql,"", strlen($sql) - 4);
        return $sql;
    }

    public function namesColumns($arreglo, $aliasTable = "") {
        $name = "";
        foreach ($arreglo as $key => $value) {
            $name .= "$aliasTable" . $value["name"] . ", ";
            /*if ($key == $length)
                $name = $name . $aliasTable . $value;
            else
                $name = $name . $aliasTable . $value . ", ";*/
        }
        $name = substr_replace($name, "", strlen($name) - 2);
        return $name;
    }
    public function inBackups($arreglo, $variable = "ba.id_backup") {
        $condicion = "";
        if ($arreglo[0] != "0") {
            $condicion = " AND $variable in (";
            foreach ($arreglo as $key => $value) {
                $condicion.= $value . ",";
            }
            $condicion = substr_replace($condicion, ")", strlen($condicion) - 1);
        }
        return $condicion;
    }
    public function condicionarLimit($pagina, $condicion) {
        return ($pagina == $condicion) ? "" : "limit $pagina,$this->limit";
    }
    public function condicionarConsulta($dato, $columna, $condicion = "0") {
        return ($dato == $condicion) ? "" : "AND $columna = $dato";
    }
    public function consultaSQL($select = "", $table = "", $where = "") {
        return "SELECT $select FROM $table WHERE $where";
    }
    public function sentenciaInconsistenicaSQL($nameTable, $namesColumns, $colOrderBy) {
        $sql = "CREATE TABLE duplicado_$nameTable LIKE $nameTable;";
        $sql.= "ALTER TABLE duplicado_$nameTable ADD UNIQUE indiceUnico (". $this -> namesColumns($namesColumns) .");";
        $sql.= "INSERT IGNORE INTO duplicado_$nameTable SELECT * FROM $nameTable ORDER BY $colOrderBy;";
        $sql.= "RENAME TABLE $nameTable TO duplicate_$nameTable, duplicado_$nameTable TO $nameTable;";

        return $sql;
    }





	/*protected $token;
	protected $id;
	protected $mu;
	public function __construct(){
		$this->token = Form::getValue("token",false);
		$this->id = Form::getValue("id",false);
		if(!empty($this->id))
			$this->id = base64_decode($this->id);
		$this->mu = new Usuario;
		
	}

	public function validaToken(){
		$token = $this->mu->getToken($this->id);
		if($token != $this->token){
		    echo json_encode([
				"permiso"=>0,
				"error"=>1,
				"msj"=>"Token no correspondiente"
			]);
			exit();
		}
	}

	public function permiso($permiso){
		$mp = new Permiso;
		if(count($mp->verificarPermiso($this->id,$permiso)) == 0 ){
			$p = $mp->mostrar("clvP = '$permiso'");
			$np = "El permiso no existe";
			if(count($p) > 0)
				$np = "No tiene el permiso.-".$p[0]->nombreP.".- pongase en contacto con el admin";
			echo json_encode([
				"permiso"=>0,
				"error"=>-2,
				"msj"=>$np
			]);
			exit();
		}
	}*/
}