<?php
class DB {
    private $conexion;
    private $baseDatos;
    private $servidor;
    private $usuario;
    private $contrasena;
    private $puerto;
    protected $result;  
    
    function __construct() {
        $config = $GLOBALS['config']["mysql"];
        
        try { 
            $this->servidor = $config["host"];
            $this->usuario = $config["us"];
            $this->contrasena = $config["pass"];
            $this->puerto = $config["puerto"];
            $this->baseDatos = $config["db"];
            $this->conecta();
        }catch (Exception $e) {
            echo $e->getMessage();
            exit();
        }
    }

    public function conecta(){
        $mysqlConnect = "mysql:host=$this->servidor;dbname=$this->baseDatos";
        $this -> conexion = new PDO($mysqlConnect, $this -> usuario, $this -> contrasena);
        $this -> conexion -> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this -> conexion -> exec("set names utf8");
    }

    public function solicitud($sql){
        try{
            $this -> conecta();
            $this -> result = $this -> conexion -> query($sql);
            //return $sql;
            $this -> conexion = null;
            if($this -> result)
                return true;
            else{
                $this -> result = null;
                return false;
            }
        }catch (Exception $e){
            echo $e -> getMessage();
            exit();
        }
    }

	/*public function getResultado(){
		return $this -> result;
	}

    public function funcionAlm($nomFunAlm){
        return $this -> solicitud($nomFunAlm);
    }*/

    public function getDatos($tablas, $select = "*", $where = 1, $limit="", $bnd=true){
        $sql = "SELECT $select FROM $tablas WHERE $where $limit";
        if($bnd)
            $sql = "SELECT $select FROM $tablas WHERE $where";
        if($this -> solicitud($sql))
            return $this -> arreglo();
        return [];
    }

    public function arreglo(){
        $arreglo = array();
        if($this -> result -> rowCount() > 0){
            $arreglo = $this -> result -> fetchAll(PDO::FETCH_OBJ);
        }
        return $arreglo;
    }

    //creando metodo de inserccion
    public function insert($tabla, $datos){
        $campos = array_keys($datos);
        $values = array_values($datos);
        $sql = "INSERT INTO ".$tabla." (".implode(", " , $campos).") VALUES (".implode(", ", $values).");";
        return $this -> solicitud($sql);
    }

    //creando metodo de actualizacion
    public function update($tabla, $datos, $where){
        $sql = "UPDATE ".$tabla." SET ";
        $u = array();
        foreach ($datos as $k => $v)
            $u[] = $k."=".$v;
        $sql .= implode(", ", $u)." WHERE $where";
        
        return $this -> solicitud($sql);
    }

    public function delete($tabla, $where){
        $sql = "DELETE FROM $tabla WHERE $where";
        return $this -> solicitud($sql);
    }

}