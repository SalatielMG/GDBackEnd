<?php
/**
 * Created by PhpStorm.
 * User: pc-01
 * Date: 01/11/2019
 * Time: 11:35 AM
 */
require_once(APP_PATH.'model/Permiso.php');

class ControlPermiso
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
}