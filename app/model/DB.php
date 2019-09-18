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
            $this -> servidor = $config["host"];
            $this -> usuario = $config["us"];
            $this -> contrasena = $config["pass"];
            $this -> puerto = $config["puerto"];
            $this -> baseDatos = $config["db"];
            $this -> conectaPDO();
        }catch (Exception $e) {
            echo $e -> getMessage();
            exit();
        }
    }

    private function conectaPDO(){
        $mysqlConnect = "mysql:host=$this->servidor;dbname=$this->baseDatos";
        $this -> conexion = new PDO($mysqlConnect, $this -> usuario, $this -> contrasena);
        $this -> conexion -> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this -> conexion -> exec("set names utf8");
    }
    private function conectaMYSQLI() {
        @$this -> conexion = new mysqli($this -> servidor,$this -> usuario,$this -> contrasena,"",$this -> puerto);
        @$this -> conexion -> set_charset("utf8");
        if(!empty($this -> conexion -> connect_error))
            throw new Exception(json_encode(["error" => 500,"msj" => "Error de conexion con el servidor de BD"]));
        @$c = $this -> conexion -> query("use " . $this -> baseDatos);
        if(!$c)
            throw new Exception(json_encode(["error" => 500,"msj" => "No existe la BD"]));
    }

    private function solicitud($sql, $conexxinPDO = true){
        try{
            if ($conexxinPDO) {
                $this -> conectaPDO();
                $this -> result = $this -> conexion -> query($sql);
                $this -> conexion = null;
            } else {
                $this -> conectaMYSQLI();
                $this -> result = $this -> conexion -> query($sql);
                $this -> conexion -> close();
            }
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

    public function ejecutarCodigoSQL($sql){
        if($this -> solicitud($sql))
            return $this -> arreglo();
        return [];
    }

    public function ejecutarSentSQL($sql) {
        return $this -> solicitud($sql);
    }

    public function ejecutarMultSentMySQLi($sql) {
        try{
            $this -> conectaMYSQLI();
            $this -> result = $this -> conexion -> multi_query($sql);
            $resultado = array(
              0 => array("error" => true, "msj" => "No se pudo crear una copia exacta de la tabla "),
              1 => array("error" => true, "msj" => "No se pudo agregar el indice unico a la nueva tabla creada "),
              2 => array("error" => true, "msj" => "No se pudo insertar los datos sin duplicidad en la nueva tabla creada "),
              3 => array("error" => true, "msj" => "No se pudieron renombrar la tabla nueva con la tabla original ")
            );
            $indice = 0;
            if ($this -> result) {
                do {
                    $resultado[$indice]["msj"] = ($indice + 1) . "° Sentencia ejecutada correctamente";
                    $resultado[$indice]["error"] = false;
                    $indice++;
                    if (!$this -> conexion -> more_results()) {
                        if ($this -> conexion -> errno) {
                            $resultado[$indice]["msj"] = $resultado[$indice]["msj"] ." [ERROR]:= ". $this -> conexion -> error;
                        }
                        break;
                    }

                } while ($this -> conexion -> next_result());
            } else if ($this -> conexion -> errno) {
                $resultado[$indice]["msj"] = $resultado[$indice]["msj"] . $this -> conexion -> error;
            }
            $this -> conexion -> close();
            $this -> result = null;
            return $resultado;
        }catch (Exception $e){
            echo $e -> getMessage();
            exit();
        }
    }

    //creando metodo de consulta
    public function getDatos($tablas, $select = "*", $where = 1, $limit="", $bnd = true){
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

    //creando metodo de borrado
    public function delete($tabla, $where){
        $sql = "DELETE FROM $tabla WHERE $where";
        return $this -> solicitud($sql);
    }

}