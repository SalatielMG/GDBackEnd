<?php
/**
 * Created by PhpStorm.
 * Users: pc-01
 * Date: 15/08/2019
 * Time: 11:25
 */

class Usuario extends DB
{
    public $nameTable = "usuarios";
    public $nameTableMM = "usuarios_permisos";

    public function mostrar($where = "1", $select = "*", $table = "usuarios"){
        return $this -> getDatos($table, $select, $where);
    }
    public function agregar($usuarioNuevo) {
        $usuario = [
            "id" => "null",
            "email" => "'$usuarioNuevo->email'",
            "password" => "'" . $this -> generatePassEncrypted($usuarioNuevo -> password) . "'",
            "tipo" => "'$usuarioNuevo->tipo'",
            "cargo" => "'$usuarioNuevo->cargo'",
        ];
        return $this -> insert($this -> nameTable, $usuario);
    }
    public function agregarPermisos_Usuario($id_usuario, $permisosSelected) {
        $data = array();
        foreach ($permisosSelected as $key => $value) {
            $data[$key] = [
                "usuario" => $id_usuario,
                "permiso" => $value,
            ];
        }
        return $this -> insertMultipleData($this -> nameTableMM, $data);
    }
    public function actualizarCodigo($codigo, $email, $isConfirm) {
        $usuario =  [
            "codigo" => ($isConfirm) ? "null": "'$codigo'",
        ];
        return $this -> update($this -> nameTable, $usuario, "email = '$email'");
    }
    public function actualizar($usuarioNuevo, $usuarioSelected, $isUpdateProfile = false) {
        $usuario = (!$isUpdateProfile) ? [
            "email" => "'$usuarioNuevo->email'",
            "tipo" => "'$usuarioNuevo->tipo'",
            "cargo" => "'$usuarioNuevo->cargo'",
        ] : [
            "email" => "'$usuarioNuevo->email'",
            "cargo" => "'$usuarioNuevo->cargo'",
        ];
        return $this -> update($this -> nameTable, $usuario, "id = $usuarioSelected->id");
    }
    public function actualizarPassword($newPassword, $condition, $isReset = false){
        $usuario = [
            "password" => "'" . $this -> generatePassEncrypted($newPassword) . "'",
        ];
        $where = ($isReset) ? "email = '$condition'" : "id = $condition" ;
        return $this -> update($this -> nameTable, $usuario, $where);
    }
    public function eliminar($id_usuario) {
        return $this -> delete($this -> nameTable, "id = $id_usuario");
    }
    public function eliminarPermisos_Usuario($id_usuario) {
        return $this -> delete($this -> nameTableMM, "usuario = $id_usuario");
    }
    private function generatePassEncrypted($pass) {
        return password_hash($pass, PASSWORD_DEFAULT);
    }
}