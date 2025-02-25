<?php

require_once(APP_PATH . "model/Permiso.php");
class Valida {

    protected $id_usuario;
    protected $limit = 50;
    protected $limit_Inconsistencia = 10;
    protected $having_Count = 2;

    public function verificarPermiso($permiso) {
        $this -> id_usuario = Form::getValue("id_usuario",false);
        if(!empty($this -> id_usuario)) {
            $this -> id_usuario = base64_decode($this -> id_usuario);
            $p = new Permiso();
            if (count($p -> verificarPermiso($this -> id_usuario, $permiso)) == 0) {
                $permiso = $p -> mostrar("id = $permiso");
                $msj = "El permiso no existe";
                if (count($permiso) > 0)
                    $msj = "No tiene el permiso de " . $permiso[0] -> permiso . " pongase en contacto con algun Administrador o Super Administrador del sistema";
                echo json_encode([
                    "error" => true,
                    "titulo" => "¡ Error de permisos !",
                    "msj" => $msj,
                ]);
                exit();
            }
        } else {
            echo json_encode([
                "error" => true,
                "titulo" => "¡ Datos no recibidos !",
                "msj" => "NO se recibio ningun dato del usuario solicitado en el servidor"
            ]);
            exit();
        }
    }
    public function selectMode_Only_Full_Group_By_Enabled($nameColumnsTable, $columnsTableIndexUnique, $alias = "") {
        $select = "";

        foreach ($nameColumnsTable as $key => $value) {
            $bnd = false;
            foreach ($columnsTableIndexUnique as $k => $v) {
                if ($value["name"] ==$v["name"]) {
                    $bnd = true;
                    break;
                }
            }
            if ($bnd) {
                $select .= $alias . $value["name"] . ", ";
            } else {
                $select .= "(ANY_VALUE($alias" . $value["name"] . ")) " . $value["name"] . ", ";
            }
        }
        $select = substr_replace($select, "", strlen($select) - 2);
        return $select;
    }
    public function keyValueArray($arreglo){
        $string = "";
        $dataObject = (array) ($arreglo);

        foreach ($dataObject as $key => $value) {
            $string .= "$key: " . (($key == "sign") ? DB::signValue($value) : $value) . ", ";
        }
        $string = substr_replace($string, "", strlen($string) - 2);
        return $string;
    }
    public static function arrayDataOperation ($columnsTable, $data, $except = array()) {
        $arrayData = array();
        $data = (array) ($data);
        foreach ($columnsTable as $key => $value) {
            $isExcept = false;
            if (count($except) > 0) {
                foreach ($except as $k => $v) {
                    if ($value["name"] == $v) {
                        $isExcept = true;
                        break;
                    }
                }
            }
            if (!$isExcept) {
                if ($value["type"] == Form::typeTinyint
                    || $value["type"] == Form::typeInt
                    || $value["type"] == Form::typeTinyint
                    || $value["type"] == Form::typeSmallint
                    || $value["type"] == Form::typeDecimal) {
                    $arrayData[$value["name"]] = $data[$value["name"]];
                } else {
                    $arrayData[$value["name"]] = (($value["name"] == "sign") ? "'" . DB::signValue($data[$value["name"]]) . "'": "'" . $data[$value["name"]] . "'");
                }
            }
        }
        return $arrayData;
    }
    public static function conditionVerifyExistsUniqueIndex($dataObjec, $columnsTableIndexUnique, $isUPPERCASE = true, $alias = "") {
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
                    $sql .= $alias . $value["name"] . " = '" . $dataObject[$value["name"]] . "' AND ";
                } else if ($value["name"] == "sign" && $value["type"] == Form::typeChar){ //NO UPPERCASE
                    $sql .= $alias . $value["name"] . " = '" . DB::signValue($dataObject[$value["name"]]) . "' AND ";
                } else if ($value["name"] == "period" && $value["type"] == Form::typeVarchar) { //NOUPPERCASE
                    $sql .= $alias . $value["name"] . " = '" . $dataObject[$value["name"]] . "' AND ";
                } else if ($value["name"] == "operation_code" && $value["type"] == Form::typeVarchar) { //NOUPPERCASE
                    $sql .= $alias . $value["name"] . " = '" . $dataObject[$value["name"]] . "' AND ";
                } else if ($value["name"] == "date_idx" && $value["type"] == Form::typeVarchar) { //NOUPPERCASE
                    $sql .= $alias . $value["name"] . " = '" . $dataObject[$value["name"]] . "' AND ";
                } else if ($value["name"] == "picture" && $value["type"] == Form::typeVarchar) { //NOUPPERCASE
                    $sql .= $alias . $value["name"] . " = '" . $dataObject[$value["name"]] . "' AND ";
                } else if ($value["name"] == "symbol" && $value["type"] == Form::typeChar) { //NOUPPERCASE
                    $sql .= $alias . $value["name"] . " = '" . $dataObject[$value["name"]] . "' AND ";
                } else if ($value["name"] == "icon_name" && $value["type"] == Form::typeChar) { //NOUPPERCASE
                    $sql .= $alias . $value["name"] . " = '" . $dataObject[$value["name"]] . "' AND ";
                } else if ($isUPPERCASE){ //UPPERCASE
                    $sql .= "UPPER(" . $alias . $value["name"] . ") = UPPER('" . $dataObject[$value["name"]] . "') AND ";
                } else {
                    $sql .= $alias . $value["name"] . " = '" . $dataObject[$value["name"]] . "' AND ";
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
        return ($dato == $condicion) ? "" : " AND $columna = $dato";
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
}