<?php

// require_once(APP_PATH."model/Usuario.php");
// require_once(APP_PATH."model/Permiso.php");
class Valida {
    protected $limit = 50;
    protected $having_Count = 2;

    public function namesColumns($arreglo, $aliasTable = "") {
        $name = "";
        $length = count($arreglo) - 1;
        foreach ($arreglo as $key => $value) {
            if ($key == $length)
                $name = $name . $aliasTable. $value;
            else
                $name = $name . $aliasTable. $value . ", ";
        }
        return $name;
    }
    public function condicionarLimit($pagina, $condicion = -10) {
        return ($pagina == $condicion) ? "" : "limit $pagina,$this->limit";
    }
    public function condicionarConsulta($dato, $columna, $condicion = "0") {
        return ($dato == $condicion) ? "" : "AND $columna = $dato";
    }
    public function consultaSQL($select = "", $table = "", $where = "") {
        return "SELECT $select FROM $table WHERE $where";
    }
    public function senetenciaInconsistenicaSQL($nameTable, $namesColumns, $colOrderBy) {
        $sql = "CREATE TABLE duplicado_$nameTable LIKE $nameTable;";
        $sql.= "ALTER TABLE duplicado_$nameTable ADD UNIQUE(". $this -> namesColumns($namesColumns) .");";
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