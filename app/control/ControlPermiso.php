<?php
/**
 * Created by PhpStorm.
 * User: pc-01
 * Date: 01/11/2019
 * Time: 11:35 AM
 */
require_once(APP_PATH.'model/Permiso.php');

class ControlPermiso extends Valida
{
    private $p;
    private $select = "";
    private $table = "";
    private $where = "";

    public function __construct()
    {
        $this -> p = new Permiso();
    }

    public function obtPermisosGral() {
        $arreglo = array();
        $permisos = $this -> p -> mostrar();
        if ($permisos) {
            $arreglo["permisos"] = $permisos;
            $arreglo["error"] = false;
            $arreglo["titulo"] = "¡ PERMISOS ENCONTRADOS !";
            $arreglo["msj"] = "Se econtraron permisos registrados en la base de datos.";
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ PERMISOS NO ENCONTRADOS !";
            $arreglo["msj"] = "No se econtraron permisos registrados en la base de datos.";
        }
        return $arreglo;
    }
    public function obtPermisosUsuario($id_usuario) {
        $arreglo = array();
        $this -> where = "up.usuario = $id_usuario AND up.permiso = p.permiso";
        $this -> table = "usuarios_permisos up, permisos p";
        $this -> select = "p.*";
        $permisos = $this -> p -> mostrar($this -> where, $this -> select, $this -> table);

        if ($permisos) {
            $arreglo["permisos"] = $permisos;
            $arreglo["error"] = false;
            $arreglo["titulo"] = "¡ PERMISOS ASIGNADOS !";
            $arreglo["msj"] = "Se econtraron permisos asignados.";
        } else {
            $arreglo["permisos"] = [];
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ PERMISOS NO ASIGNADOS !";
            $arreglo["msj"] = "NO se econtraron permisos asignados.";
        }
        return $arreglo;
    }
    public function verifyExistsPermiso ($permiso, $isUpdate = false) {
        $arreglo = array();
        $arreglo["error"] = false;
        $arreglo["sqlVerfiyIndexUnique"] = "UPPER(permiso) = UPPER('$permiso')";
        $result = $this -> p -> mostrar( $arreglo["sqlVerfiyIndexUnique"]);
        if ($result) {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ PERMISO EXISTENTE !";
            $arreglo["msj"] = "NO se puede " . (($isUpdate) ? "actualizar el " : "registrar el nuevo ") . "Permiso, porque ya existe un registro en la BD con el mismo nombre. Porfavor verifique y vuelva a intentarlo";
        }
        return $arreglo;
    }
    public function agregarPermiso () {
        $arreglo = array();
        $permiso = json_decode(Form::getValue("permiso", false, false));
        $userSelected = json_decode(Form::getValue("userSelected", false, false));

        return $arreglo;
    }
    public function actualizarPermiso () {
        $arreglo = array();
        $permiso = json_decode(Form::getValue("permiso", false, false));
        $permisoSelected = json_decode(Form::getValue("permisoSelected", false, false));
        $userSelected = json_decode(Form::getValue("userSelected", false, false));

        if (strtoupper($permiso -> permiso) != strtoupper($permisoSelected -> permiso)) {
            $arreglo = $this -> verifyExistsPermiso($permiso -> permiso, true);
            if ($arreglo["error"]) return $arreglo;
        }
        $update = $this -> p -> actualizar($permiso, $permisoSelected);
        if ($update) {
            $arreglo["error"] = false;
            $arreglo["titulo"] = "¡ PERMISOS ACTUALIZADO !";
            $arreglo["msj"] = "El Permiso: " . $permiso -> permiso . " se ha actualizado correctamente";
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ PERMISOS NO ACTUALIZADO !";
            $arreglo["msj"] = "Ocurrio un errror al intentar actualizar el Permiso: " . $permiso -> permiso ;
        }
        return $arreglo;
    }
    public function eliminarPermiso () {
        $arreglo = array();
        $permiso = json_decode(Form::getValue("permiso", false, false));
        $deleteUsuarios_Permiso = $this -> p -> eliminarUsuario_Permiso($permiso -> permiso);
        if ($deleteUsuarios_Permiso) {
            $delete = $this -> p -> eliminar($permiso -> permiso);
            if ($delete) {
                $arreglo["error"] = false;
                $arreglo["titulo"] = "¡ PERMISOS ELIMINADO !";
                $arreglo["msj"] = "El Permiso: " . $permiso -> permiso . " se ha eliminado correctamente";
            } else {
                $arreglo["error"] = true;
                $arreglo["titulo"] = "¡ PERMISOS NO ELIMINADO !";
                $arreglo["msj"] = "Ocurrio un error al intentar eliminar el Permiso: " . $permiso -> permiso;
            }
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ ERROR DE DEPENDENCIAS !";
            $arreglo["msj"] = "Ocurrio un error al intentar eliminar los usuarios asociados al Permiso: " . $permiso -> permiso;
        }
        return $arreglo;
    }
}