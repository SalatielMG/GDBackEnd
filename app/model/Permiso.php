<?php
/**
 * Created by PhpStorm.
 * User: pc-01
 * Date: 01/11/2019
 * Time: 11:36 AM
 */

class Permiso extends DB
{
    public $nameTable = "permisos";
    public $nameTableMM = "usuarios_permisos";

    public function mostrar($where = "1", $select = "*", $table = "permisos") {
        return $this -> getDatos($table, $select, $where);
    }
    public function agregar($permisoNuevo) {
        $permiso = [
            "id" => "null",
            "permiso" => "'" . $permisoNuevo -> permiso . "'",
            "descripcion" => "'" . $permisoNuevo -> descripcion . "'",
        ];
        return $this -> insert($this -> nameTable, $permiso);
    }
    public function agregarUsuarios_Permiso($id_permiso, $userSelected) {
        $data = array();
        foreach ($userSelected as $key => $value){
            $data[$key] = [
                "usuario" => $value,
                "permiso" => $id_permiso,
            ];
        }
        return $this -> insertMultipleData($this -> nameTableMM, $data);
    }
    public function actualizar($permisoNuevo, $permisoSeleccionado) {
        $permiso = [
            "permiso" => "'" . $permisoNuevo -> permiso . "'",
            "descripcion" => "'" . $permisoNuevo -> descripcion . "'",
        ];
        return $this -> update($this -> nameTable, $permiso, "id = $permisoSeleccionado->id");
    }
    public function eliminar($id_permiso) {
        return $this -> delete($this -> nameTable, "id = $id_permiso");
    }
    public function eliminarUsuario_Permiso($id_permiso) {
        return $this -> delete($this -> nameTableMM, "permiso = $id_permiso");
    }
}