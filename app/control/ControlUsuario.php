<?php
/**
 * Created by PhpStorm.
 * Users: pc-01
 * Date: 15/08/2019
 * Time: 11:09
 */
require_once(APP_PATH.'model/Usuario.php');

class ControlUsuario extends Valida
{
    private $u;
    private $select = "";
    private $table = "";
    private $where = "";
    private $pk_Usuario = array();

    public function __construct() {
        $this -> u = new Usuario();
    }

    public function login() {
        $data = json_decode(Form::getValue("data",false,false));
        $form = new Form();
        $form -> validarDatos($data -> email, 'Correo electronico', 'required');
        $form -> validarDatos($data -> pass, 'Contraseña', 'required');
        $arreglo = array();
        $errores = count($form -> errores);
        if ($errores > 0) {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ ERROR DE VALIDACIÓN !";
            $arreglo["msj"] = $form -> errores;
            return $arreglo;
        }
        $email = $data -> email;
        $usuario = $this -> u -> mostrar("email = '$email'", "id, email, password" );
        if (count($usuario) > 0) {
            $usuario = $usuario[0];
            $pass = $data -> pass;
            if (password_verify($pass, $usuario -> password)) {
                $arreglo["error"] = false;
                $arreglo["titulo"] = "¡ LOGIN EXITOSO !";
                $arreglo["idEncode"] = base64_encode($usuario -> id);
                $arreglo["msj"] = "Bienvenido usuario $email";
            } else {
                $arreglo["error"] = true;
                $arreglo["titulo"] = "¡ CONTRASEÑA INCORRECTA !";
                $arreglo["msj"] = "La contraseña proporcionada no es correcta";
            }
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ EMAIL NO ENCONTRADO !";
            $arreglo["msj"] = "No existe ningun usuario registrado con el correo: $email";
        }
        return $arreglo;
    }

    public function obtUsuariosGral($isQuery = true) {
        if ($isQuery) {
            $id_usuario = Form::getValue("id_usuario", false);
            $show_permiso = Form::getValue("show_permiso");
            //var_dump($id_usuario, $show_permiso);
        } else {
            $id_usuario = "0";
            $show_permiso = 1;
        }

        if ($id_usuario === "0") { // Busca todos los usuarios
            $this -> where = ($isQuery) ? "1" : "id = " . $this -> pk_Usuario["id"];
        } else { // Busca todos los usuarios esxcepto con el id recibido
            if(!empty($id_usuario)) {
                $id_usuario = base64_decode($id_usuario);
                $this -> where = "id != $id_usuario";
            } else {
                $arreglo["error"] = true;
                $arreglo["titulo"] = "¡ USUARIO NO RECIBIDO !";
                $arreglo["msj"] = "NO se recibio ningun dato del usuario solicitado en el servidor";
                return $arreglo;
            }
        }
        $arreglo = array();
        $arreglo["consultaSQL"] = $this -> consultaSQL("*", $this -> u -> nameTable, $this -> where);
        $usuarios = $this -> u -> mostrar($this -> where);
        if ($usuarios) {
            /*for ($i = 0; $i < 35 ; $i++)
            {
                foreach ($usuarios as $key => $user) {
                    $usuarios[$i] = $user;
                }
            }*/
            if ($show_permiso == 1) { //Buscar los permisos de cada usuario
                require_once (APP_PATH . "control/ControlPermiso.php");
                $ctrlPermiso = new ControlPermiso();
                foreach ($usuarios as $key => $value) {
                    $value -> permisos = $ctrlPermiso -> obtPermisosUsuario($value -> id)["permisos"];
                    //$usuarios[$key]["permisos"] = $ctrlPermiso -> obtPermisosUsuario($value -> id)["permisos"];
                }
            }
            $arreglo["usuarios"] = $usuarios;
            $arreglo["error"] = false;
            $arreglo["titulo"] = ($isQuery) ? "¡ Usuarios encontrados !" : "¡ Usuario encontrado !";
            $arreglo["msj"] = ($isQuery) ? "Se encontraron usuarios regsitrados en la base de datos" : "Se encontro el Usuario: " . $this -> pk_Usuario["id"]. " en la base de datos.";

        } else {
            $arreglo["usuarios"] = [];
            $arreglo["error"] = true;
            $arreglo["titulo"] = ($isQuery) ? "¡ Usuarios no econtrados !" : "¡ Usuario no encontrado !";
            $arreglo["msj"] = ($isQuery) ? "No se encontraron usuarios regsitrados en la base de datos" : "No se encontro el Usuario: " . $this -> pk_Usuario["id"]. " en la base de datos.";
        }
        return $arreglo;
    }

    public function obtUsuarios_Permiso($id) {
        $arreglo = array();
        $this -> select = "u.*";
        $this -> table = "usuarios_permisos up, usuarios u";
        $this -> where = "up.permiso = $id AND u.id = up.usuario";
        $usuarios = $this -> u -> mostrar($this -> where , $this -> select, $this -> table);
        $arreglo["consultaSQL"] = $this -> consultaSQL($this -> select, $this -> table, $this -> where);
        if ($usuarios){
            $arreglo["usuarios"] = $usuarios;
            $arreglo["error"] = false;
            $arreglo["titulo"] = "¡ USUARIOS ASIGNADOS !";
            $arreglo["msj"] = "Se econtraron usuarios asignados.";
        } else {
            $arreglo["usuarios"] = [];
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ USUARIOS NO ASIGNADOS !";
            $arreglo["msj"] = "NO se econtraron usuarios asignados.";
        }
        return $arreglo;
    }
    public function obtMaxIdUsuario() {
        $arreglo = array();
        $arreglo["error"] = true;
        $idMax = $this -> u -> mostrar("1", "max(id) as id");
        if ($idMax) {
            $arreglo["error"] = false;
            $arreglo["id"] = $idMax[0] -> id;
        }
        return $arreglo;
    }
    public function verifyExistsUsuario ($email, $isUpdate = false) {
        $arreglo = array();
        $arreglo["error"] = false;
        $arreglo["sqlVerfiyIndexUnique"] = "UPPER(email) = UPPER('$email')";
        $result = $this -> u -> mostrar( $arreglo["sqlVerfiyIndexUnique"]);
        if ($result) {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ Email existente !";
            $arreglo["msj"] = "NO se puede " . (($isUpdate) ? "actualizar el " : "registrar el nuevo ") . "Usuario, porque ya existe un registro en la BD con el mismo email. Porfavor verifique y vuelva a intentarlo";
        }
        return $arreglo;
    }
    public function agregarUsuario() {
        $arreglo = array();
        $usuario = json_decode(Form::getValue("usuario", false, false));
        $permisosSelected = json_decode(Form::getValue("permisosSelected", false, false));
        $arreglo = $this -> verifyExistsUsuario($usuario -> email);
        if ($arreglo["error"]) return $arreglo;
        $insert = $this -> u -> agregar($usuario);
        if ($insert) {
            $arreglo["error"] = false;
            $arreglo["titulo"] = "¡ Usuario agregado !";
            $arreglo["msj"] = "El Usuario: " . $usuario -> email . " se ha agregado correctamente";

            $newIdUsuario = $this -> obtMaxIdUsuario();
            if ($newIdUsuario["error"]) {
                $arreglo["error"] = true;
                $arreglo["msj"] = "El Usuario: " . $usuario -> email . " se ha agregado correctamente, pero no se pudo recuperar los datos. Porfavor recargue la pagina";
                return $arreglo;
            }
            if (count($permisosSelected) > 0) {
                $insertPermisos_Usuario = $this -> u -> agregarPermisos_Usuario($newIdUsuario["id"], $permisosSelected);
                $arreglo["msj"] = "El Usuario: " . $usuario -> email . " se ha agregado y asignado correctamente los permisos privilegiados.";
                if (!$insertPermisos_Usuario) {
                    $arreglo["msj"] = "El Usuario: " . $usuario -> email . " se ha agregado correctamente, pero no se han podido agregar satisfactoriamente los permisos asignados al usuario. Porfavor verifique y vuelva a asignarlos.";
                }
            }

            $this -> pk_Usuario["id"] = $newIdUsuario["id"];
            $usuarioNew = $this -> obtUsuariosGral(false);
            $arreglo["usuario"]["error"] = $usuarioNew["error"];
            $arreglo["usuario"]["titulo"] = $usuarioNew["titulo"];
            $arreglo["usuario"]["msj"] = $usuarioNew["msj"];
            if (!$arreglo["usuario"]["error"]) {
                $arreglo["usuario"]["new"] = $usuarioNew["usuarios"][0];
            }
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ Usuario no agregado !";
            $arreglo["msj"] = "Ocurrio un error al intentar agregar el nuevo Usuario: " . $usuario -> email;
        }
        return $arreglo;
    }
    public function actualizarUsuario() {
        $arreglo = array();
        $usuario = json_decode(Form::getValue("usuario", false, false));
        $usuarioSelected = json_decode(Form::getValue("usuarioSelected", false, false));
        $isChangePermisos = json_decode(Form::getValue("isChangePermisos", false, false));

        if (strtoupper($usuario -> email) != strtoupper($usuarioSelected -> email)) {
            $arreglo = $this -> verifyExistsUsuario($usuario -> email, true);
            if ($arreglo["error"]) return $arreglo;
        }
        $update = $this -> u -> actualizar($usuario, $usuarioSelected);
        if ($update) {
            $arreglo["error"] = false;
            $arreglo["titulo"] = "¡ Usuario actualizado !";
            $arreglo["msj"] = "El Usuario: " . $usuarioSelected -> email . " se ha actualizado correctamente";

            if ($isChangePermisos -> isChangePermisos) {
                $arreglo["msj"] = "El Usuario: " . $usuarioSelected -> email . " se ha actualizado y asignados los permisos correctamente.";
                $deletePermisos_Usuario = $this -> u -> eliminarPermisos_Usuario($usuarioSelected -> id);
                if (!$deletePermisos_Usuario) {
                    $arreglo["msj"] = "El Usuario: " . $usuarioSelected -> email . " se ha actualizado correctamente, pero no se han podido actualizar satisfactoriamente los permisos asignados al usuario (Error en 1° etapa). Porfavor verifique y vuelva a itentarlo.";
                    return $arreglo;
                }
                if (count($isChangePermisos -> permisosSelected) > 0) {
                    $updatePermisos_Usuario = $this -> u -> agregarPermisos_Usuario($usuario -> id, $isChangePermisos -> permisosSelected);
                    if (!$updatePermisos_Usuario) {
                        $arreglo["msj"] = "El Usuario: " . $usuarioSelected -> email . " se ha actualizado correctamente, pero no se han podido actualizar satisfactoriamente los permisos asignados al usuario (Error en 2° etapa). Porfavor verifique y vuelva a itentarlo.";
                    }
                }
            }
            $this -> pk_Usuario["id"] = $usuario -> id;
            $usuarioUpdate = $this -> obtUsuariosGral(false);
            $arreglo["usuario"]["error"] = $usuarioUpdate["error"];
            $arreglo["usuario"]["titulo"] = $usuarioUpdate["titulo"];
            $arreglo["usuario"]["msj"] = $usuarioUpdate["msj"];
            if (!$arreglo["usuario"]["error"]) $arreglo["usuario"]["update"] = $usuarioUpdate["usuarios"][0];
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ Usuario no actualizado !";
            $arreglo["msj"] = "Ocurrio un errror al intentar actualizar el Usuario: " . $usuarioSelected -> email ;
        }
        return $arreglo;
    }
    public function eliminarUsuario() {
        $arreglo = array();
        $usuarioSelected = json_decode(Form::getValue("usuarioSelected", false, false));
        $deletePermisos_Usuario = $this -> u -> eliminarPermisos_Usuario($usuarioSelected -> id);
        if ($deletePermisos_Usuario) {
            $delete = $this -> u -> eliminar($usuarioSelected -> id);
            if ($delete) {
                $arreglo["error"] = false;
                $arreglo["titulo"] = "¡ Usuario eliminado !";
                $arreglo["msj"] = "El usuario: " . $usuarioSelected -> email. " se ha eliminado correctamente";
            } else {
                $arreglo["error"] = true;
                $arreglo["titulo"] = "¡ Usuario no eliminado !";
                $arreglo["msj"] = "Ocurrio un error al intentar eliminar el usuario: " . $usuarioSelected -> email;
            }
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ ERROR DE DEPENDENCIAS !";
            $arreglo["msj"] = "Ocurrio un error al intentar eliminar los permisos asociados al Usuario: " . $usuarioSelected -> email;
        }
        return $arreglo;
    }
    public function actualizarPermisos_Usuario() {
        $arreglo = array();
        $isChangePermisos = json_decode(Form::getValue("isChangePermisos", false, false));
        if ($isChangePermisos -> isChangePermisos) {
            $deletePermisos_Usuario = $this -> u -> eliminarPermisos_Usuario($isChangePermisos -> usuarioSelected -> id);
            if (!$deletePermisos_Usuario) {
                $arreglo["error"] = true;
                $arreglo["titulo"] = "¡ Permisos no asignados !";
                $arreglo["msj"] = "No se han podido actualizar satisfactoriamente los permisos asignados al Usuario: " . $isChangePermisos -> usuarioSelected -> email . ". (Error en 1° etapa => eliminación). Porfavor verifique y vuelva a itentarlo.";
                return $arreglo;
            }
            if (count($isChangePermisos -> permisosSelected) > 0) {
                $updatePermisos_Usuario = $this -> u -> agregarPermisos_Usuario($isChangePermisos -> usuarioSelected -> id, $isChangePermisos -> permisosSelected);
                if (!$updatePermisos_Usuario) {
                    $arreglo["error"] = true;
                    $arreglo["titulo"] = "¡ Permisos no asignados !";
                    $arreglo["msj"] = "No se han podido actualizar satisfactoriamente los permisos asignados al Usuario: " . $isChangePermisos -> usuarioSelected -> email . ". (Error en 1° etapa => insercción). Porfavor verifique y vuelva a itentarlo.";
                } else {
                    $arreglo["error"] = false;
                    $arreglo["titulo"] = "¡ Permisos asignados !";
                    $arreglo["msj"] = "Se actualizo la lista de permisos asignados al Usuario: " . $isChangePermisos -> usuarioSelected -> email;
                    require_once (APP_PATH . "control/ControlPermiso.php");
                    $ctrlPermiso = new ControlPermiso();
                    $arreglo["permisos"] = $ctrlPermiso -> obtPermisosUsuario($isChangePermisos -> usuarioSelected -> id);
                }
            } else {
                $arreglo["error"] = false;
                $arreglo["titulo"] = "¡ Permisos designados !";
                $arreglo["msj"] = "Se detecto una lista vacia de permisos asignados por lo tanto se resetearon los permisos del Usuario: " . $isChangePermisos -> usuarioSelected -> email;
                $arreglo["permisos"] = [
                    "error" => false, "permisos" => []
                ];
            }
        } else {
            $arreglo["error"] = false;
            $arreglo["titulo"] = "¡ Operación inecesaria !";
            $arreglo["msj"] = "No se detecto nigun cambio en la lista de permisos seleccionados. No fue necesario actualizar";
        }
        return $arreglo;
    }
}