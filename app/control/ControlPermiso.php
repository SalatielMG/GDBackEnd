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
    private $pk_Permiso = array();

    public function __construct()
    {
        $this -> p = new Permiso();
    }

    public function getPermisosGral($isQuery = true) {
        $arreglo = array();
        $this -> where = ($isQuery) ? "1": "id = " . $this -> pk_Permiso["id"];
        $permisos = $this -> p -> mostrar($this -> where);
        $arreglo["consultaSQL"] = $this -> consultaSQL("*", $this -> p -> nameTable, $this -> where);
        if ($permisos) {
            //$arreglo["Beforepermisos"] = $permisos;

            require_once (APP_PATH . "control/ControlUsuario.php");
            $ctrlUsuario = new ControlUsuario();
            foreach ($permisos as $key => $value) {
                //$UserPermiso = $ctrlUsuario -> obtUsuarios_Permiso($value -> permiso);
                $value -> usuarios = $ctrlUsuario -> obtUsuarios_Permiso($value -> id)["usuarios"];
                //$arreglo[$key]["UserPermiso"] = $UserPermiso;
                //$arreglo[$key]["value"] = $value;
                //$permisos[$key] = $value;
                //$p = $ctrlUsuario -> obtUsuarios_Permiso($value -> permiso)["usuarios"];
            }
            $arreglo["permisos"] = $permisos;
            $arreglo["error"] = false;
            $arreglo["titulo"] = ($isQuery) ? "¡ PERMISOS ENCONTRADOS !" : "¡ PERMISO ENCONTRADO !";
            $arreglo["msj"] = ($isQuery) ? "Se econtraron permisos registrados en la base de datos." : "Se encontro el Permiso: " . $this -> pk_Permiso["id"] . " en la base de datos.";
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = ($isQuery) ? "¡ PERMISOS NO ENCONTRADOS !" : "¡ PERMISO NO ENCONTRADO !";
            $arreglo["msj"] = ($isQuery) ? "No se econtraron permisos registrados en la base de datos." : "No se encontro el Permiso " . $this -> pk_Permiso["id"] . " en la base de datos.";
        }
        return $arreglo;
    }
    public function obtPermisosUsuario($id_usuario) {
        $arreglo = array();
        $this -> where = "up.usuario = $id_usuario AND up.permiso = p.id";
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

    public function obtMaxIdPermiso(){
        $arreglo = array();
        $arreglo["error"] = true;
        $idMax = $this -> p -> mostrar("1", "max(id) as id");
        if ($idMax) {
            $arreglo["error"] = false;
            $arreglo["id"] = $idMax[0] -> id;
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
        $arreglo = $this -> verifyExistsPermiso($permiso -> permiso);
        if ($arreglo["error"]) return $arreglo;
        $insert = $this -> p -> agregar($permiso);
        if ($insert) {
            $arreglo["error"] = false;
            $arreglo["titulo"] = "¡ Permiso agregado !";
            $arreglo["msj"] = "El Permiso: " . $permiso -> permiso . " se ha agregado correctamente";

            $newIdPermiso = $this -> obtMaxIdPermiso();
            if ($newIdPermiso["error"]) {
                $arreglo["error"] = true;
                $arreglo["msj"] = "El Permiso: " . $permiso -> permiso . " se ha agregado correctamente, pero no se pudo recuperar los datos. Porfavor recargue la pagina";
                return $arreglo;
            }
            if (count($userSelected) > 0) {
                $insertUsuarios_Permiso = $this -> p -> agregarUsuarios_Permiso($newIdPermiso["id"], $userSelected);
                $arreglo["msj"] = "El Permiso: " . $permiso -> permiso . " se ha agregado y asignado correctamente los usuarios privilegiados.";
                if (!$insertUsuarios_Permiso) {
                    $arreglo["msj"] = "El Permiso: " . $permiso -> permiso . " se ha agregado correctamente, pero no se han podido agregar satisfactoriamente los usuarios asignados al permiso. Porfavor verifique y vuelva a asignarlos.";
                }
            }

            $this -> pk_Permiso["id"] = $newIdPermiso["id"];
            $permisoNew = $this -> getPermisosGral(false);
            $arreglo["permisoNew"] = $permisoNew;
            $arreglo["permiso"]["error"] = $permisoNew["error"];
            $arreglo["permiso"]["titulo"] = $permisoNew["titulo"];
            $arreglo["permiso"]["msj"] = $permisoNew["msj"];
            if (!$arreglo["permiso"]["error"]) {
                $arreglo["permiso"]["new"] = $permisoNew["permisos"][0];
            }
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ PERMISO NO AGREGADO !";
            $arreglo["msj"] = "Ocurrio un error al intentar agregar el nuevo Permiso: " . $permiso -> permiso;
        }
        return $arreglo;
    }
    public function actualizarPermiso () {
        $arreglo = array();
        $permiso = json_decode(Form::getValue("permiso", false, false));
        $permisoSelected = json_decode(Form::getValue("permisoSelected", false, false));
        $isChangeUsers = json_decode(Form::getValue("isChangeUsers", false, false));

        if (strtoupper($permiso -> permiso) != strtoupper($permisoSelected -> permiso)) {
            $arreglo = $this -> verifyExistsPermiso($permiso -> permiso, true);
            if ($arreglo["error"]) return $arreglo;
        }
        $update = $this -> p -> actualizar($permiso, $permisoSelected);
        if ($update) {
            $arreglo["error"] = false;
            $arreglo["titulo"] = "¡ PERMISO ACTUALIZADO !";
            $arreglo["msj"] = "El Permiso: " . $permisoSelected -> permiso . " se ha actualizado correctamente";

            if ($isChangeUsers -> isChangeUsers) { // Actualizar usuarios
                $arreglo["msj"] = "El Permiso: " . $permisoSelected -> permiso . " se ha actualizado y también asignados los usuarios correctamente.";
                $deleteUsuarios_Permiso = $this -> p -> eliminarUsuario_Permiso($permisoSelected -> id);
                if (!$deleteUsuarios_Permiso) {
                    $arreglo["msj"] = "El Permiso: " . $permisoSelected -> permiso . " se ha actualizado correctamente, pero no se han podido actualizar satisfactoriamente los usuarios asignados al permiso (Error en 1° etapa). Porfavor verifique y vuelva a itentarlo.";
                    return $arreglo;
                }
                if (count($isChangeUsers -> userSelected) > 0) {
                    $updateUsuarios_Permiso = $this -> p -> agregarUsuarios_Permiso($permiso -> id, $isChangeUsers -> userSelected);
                    if (!$updateUsuarios_Permiso) {
                        $arreglo["msj"] = "El Permiso: " . $permisoSelected -> permiso . " se ha actualizado correctamente, pero no se han podido actualizar satisfactoriamente los usuarios asignados al permiso (Error en 1° etapa). Porfavor verifique y vuelva a itentarlo.";
                    }
                }

            }

            $this -> pk_Permiso["id"] = $permiso -> id;
            $permisoUpdate = $this -> getPermisosGral(false);
            $arreglo["permisoUpdate"] = $permisoUpdate;
            $arreglo["permiso"]["error"] = $permisoUpdate["error"];
            $arreglo["permiso"]["titulo"] = $permisoUpdate["titulo"];
            $arreglo["permiso"]["msj"] = $permisoUpdate["msj"];
            if (!$arreglo["permiso"]["error"]) $arreglo["permiso"]["update"] = $permisoUpdate["permisos"][0];
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ PERMISO NO ACTUALIZADO !";
            $arreglo["msj"] = "Ocurrio un errror al intentar actualizar el Permiso: " . $permisoSelected -> permiso ;
        }
        return $arreglo;
    }
    public function eliminarPermiso () {
        $arreglo = array();
        $permiso = json_decode(Form::getValue("permiso", false, false));
        $deleteUsuarios_Permiso = $this -> p -> eliminarUsuario_Permiso($permiso -> id);
        if ($deleteUsuarios_Permiso) {
            $delete = $this -> p -> eliminar($permiso -> id);
            if ($delete) {
                $arreglo["error"] = false;
                $arreglo["titulo"] = "¡ PERMISO ELIMINADO !";
                $arreglo["msj"] = "El Permiso: " . $permiso -> permiso . " se ha eliminado correctamente";
            } else {
                $arreglo["error"] = true;
                $arreglo["titulo"] = "¡ PERMISO NO ELIMINADO !";
                $arreglo["msj"] = "Ocurrio un error al intentar eliminar el Permiso: " . $permiso -> permiso;
            }
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ ERROR DE DEPENDENCIAS !";
            $arreglo["msj"] = "Ocurrio un error al intentar eliminar los usuarios asociados al Permiso: " . $permiso -> permiso;
        }
        return $arreglo;
    }
    public function actualizarUsuarios_Permiso() {
        $arreglo = array();
        $isChangeUsers = json_decode(Form::getValue("isChangeUsers", false, false));
        if ($isChangeUsers -> isChangeUsers) { // Actualizar usuarios
            $deleteUsuarios_Permiso = $this -> p -> eliminarUsuario_Permiso($isChangeUsers -> permisoSelected -> id);
            if (!$deleteUsuarios_Permiso) {
                $arreglo["error"] = true;
                $arreglo["titulo"] = "¡ Usuarios no asignados !";
                $arreglo["msj"] = "No se han podido actualizar satisfactoriamente los usuarios asignados al Permiso: " . $isChangeUsers -> permisoSelected -> permiso . ". (Error en 1° etapa => eliminación). Porfavor verifique y vuelva a itentarlo.";
                return $arreglo;
            }
            if (count($isChangeUsers -> userSelected) > 0) {
                $updateUsuarios_Permiso = $this -> p -> agregarUsuarios_Permiso($isChangeUsers -> permisoSelected -> id, $isChangeUsers -> userSelected);
                if (!$updateUsuarios_Permiso) {
                    $arreglo["error"] = true;
                    $arreglo["titulo"] = "¡ Usuarios no asignados !";
                    $arreglo["msj"] = "No se han podido actualizar satisfactoriamente los usuarios asignados al Permiso: " . $isChangeUsers -> permisoSelected -> permiso . ". (Error en 1° etapa => insercción). Porfavor verifique y vuelva a itentarlo.";
                } else {
                    $arreglo["error"] = false;
                    $arreglo["titulo"] = "¡ Usuarios asignados !";
                    $arreglo["msj"] = "Se actualizo la lista de usuarios asignados al Permiso: " . $isChangeUsers -> permisoSelected -> permiso;
                    require_once (APP_PATH . "control/ControlUsuario.php");
                    $ctrlUsuario = new ControlUsuario();
                    $arreglo["usuarios"] = $ctrlUsuario -> obtUsuarios_Permiso($isChangeUsers -> permisoSelected -> id);
                }
            } else {
                $arreglo["error"] = false;
                $arreglo["titulo"] = "¡ Usuarios designados !";
                $arreglo["msj"] = "Se detecto una lista vacia de usuarios asignados por lo tanto se resetearon los usuarios del Permiso: " . $isChangeUsers -> permisoSelected -> permiso;
                $arreglo["usuarios"] = [
                    "error" => false, "usuarios" => []
                ];
            }

        } else {
            $arreglo["error"] = false;
            $arreglo["titulo"] = "¡ Operación inecesaria !";
            $arreglo["msj"] = "No se detecto nigun cambio en la lista de usuarios seleccionados. No es necesario actualizar";
        }
        return $arreglo;
    }
}