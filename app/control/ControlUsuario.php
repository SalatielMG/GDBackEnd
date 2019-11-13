<?php
/**
 * Created by PhpStorm.
 * Users: pc-01
 * Date: 15/08/2019
 * Time: 11:09
 */
require_once(APP_PATH.'model/Usuario.php');

class

ControlUsuario extends Valida
{
    private $u;
    private $select = "*";
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
            $arreglo["titulo"] = "¡ Error de validación !";
            $arreglo["msj"] = $form -> errores;
            return $arreglo;
        }
        $email = $data -> email;
        $usuario = $this -> u -> mostrar("email = '$email'", "id, email, password, tipo" );
        if (count($usuario) > 0) {
            $usuario = $usuario[0];
            $pass = $data -> pass;
            if (password_verify($pass, $usuario -> password)) {
                $arreglo["error"] = false;
                $arreglo["titulo"] = "¡ Login exitoso !";
                $arreglo["msj"] = "Bienvenido " . $usuario -> tipo . "  $email";

                $this -> pk_Usuario["id"] = $usuario -> id;
                $usuarioLogin = $this -> obtUsuariosGral(false);
                if (!$usuarioLogin["error"]){
                    $usuarioLogin["usuarios"][0] -> id = base64_encode($usuario -> id);
                    $usuarioLogin["usuarios"][0] -> password = "";
                }
                $arreglo["usuario"] = $usuarioLogin;
            } else {
                $arreglo["error"] = true;
                $arreglo["titulo"] = "¡ Contraseña incorrecta !";
                $arreglo["msj"] = "La contraseña proporcionada no es correcta";
            }
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ Emal no encontrado !";
            $arreglo["msj"] = "No existe ningun usuario registrado con el correo: $email";
        }
        return $arreglo;
    }
    public function obtUsuario() {
        $arreglo = array();
        $id_usuario = Form::getValue("id_usuario", false);
        if(!empty($id_usuario)) {
            $id_usuario = base64_decode($id_usuario);
            $this -> pk_Usuario["id"] = $id_usuario;
            $arreglo = $this -> obtUsuariosGral(false);
            if (!$arreglo["error"]){
                $arreglo["usuarios"][0] -> id = base64_encode($arreglo["usuarios"][0] -> id);
                $arreglo["usuarios"][0] -> password = "";
            }
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ Datos no recibidos !";
            $arreglo["msj"] = "No se recibio ningun dato del usuario solicitado en el servidor";
            return $arreglo;
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

        if ($isQuery) { // Conultar todos los usuarios de acuerdo al privilegio del uusario recibido
            if(!empty($id_usuario)) {
                $tipos = "";
                $id_usuario = base64_decode($id_usuario);
                $tipoUsuario = $this -> u -> mostrar("id = $id_usuario", "tipo");
                if ($tipoUsuario) {
                    $tipoUsuario = $tipoUsuario[0] -> tipo;
                    switch ($tipoUsuario) {
                        case "superAdmin":
                            $tipos = "('admin', 'aux')";
                            break;
                        case "admin":
                            $tipos = "('aux')";
                            break;
                        default:
                            $arreglo["error"] = true;
                            $arreglo["titulo"] = "¡ Error de privilegios !";
                            $arreglo["msj"] = "No tienes privilegios para poder ver la información de los usuarios de la aplicacion web.";
                            return $arreglo;
                            break;
                    }
                } else {
                    $arreglo["error"] = true;
                    $arreglo["titulo"] = "¡ Error Interno !";
                    $arreglo["msj"] = "Ocurrio un error al intentar listar los usuarios, de acuerdo a su privilegio.";
                    return $arreglo;
                }
                $this -> where = "tipo IN $tipos";
            } else {
                $arreglo["error"] = true;
                $arreglo["titulo"] = "¡ Datos no recibidos !";
                $arreglo["msj"] = "NO se recibio ningun dato del usuario solicitado en el servidor";
                return $arreglo;
            }
        } else { //Consultar los datos de un usuario en especifico.
            $this -> where = "id = " . $this -> pk_Usuario["id"];
        }

        $arreglo = array();
        $arreglo["consultaSQL"] = $this -> consultaSQL($this -> select, $this -> u -> nameTable, $this -> where);
        $usuarios = $this -> u -> mostrar($this -> where, $this -> select);
        if ($usuarios) {
            if ($show_permiso == 1 && $this -> select == "*") { //Buscar los permisos de cada usuario
                require_once (APP_PATH . "control/ControlPermiso.php");
                $ctrlPermiso = new ControlPermiso();
                foreach ($usuarios as $key => $value) {
                    $value -> permisos = $ctrlPermiso -> obtPermisosUsuario($value -> id)["permisos"];
                }
            }
            $arreglo["usuarios"] = $usuarios;
            $arreglo["error"] = false;
            $arreglo["titulo"] = ($isQuery) ? "¡ Usuarios encontrados !" : "¡ Usuario encontrado !";
            $arreglo["msj"] = ($isQuery) ? "Se encontraron usuarios regsitrados en la base de datos" : "Se encontro el Usuario en la base de datos.";

        } else {
            $arreglo["usuarios"] = [];
            $arreglo["error"] = true;
            $arreglo["titulo"] = ($isQuery) ? "¡ Usuarios no econtrados !" : "¡ Usuario no encontrado !";
            $arreglo["msj"] = ($isQuery) ? "No se encontraron usuarios regsitrados en la base de datos" : "No se encontro el Usuario en la base de datos.";
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
            $arreglo["titulo"] = "¡ Usuarios asignados !";
            $arreglo["msj"] = "Se econtraron usuarios asignados.";
        } else {
            $arreglo["usuarios"] = [];
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ Usuarios no asignados !";
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
    private function uploadImage($isChange, $nameImg) {
        $arreglo = array();
        $arreglo["error"] = false;
        $arreglo["titulo"] = "¡ Operación inecesaria !";
        $arreglo["msj"] = "No hay imagen para subir";
        if ($isChange == "true") {
            $path = APP_UTIL . "avatar/";

            if (isset($_FILES['imagen'])) {
                $imagen = $_FILES['imagen'];
                if (!is_writable($path)) {
                    $arreglo["error"] = true;
                    $arreglo["titulo"] = "¡ Directorio sin permisos !";
                    $arreglo["msj"] = "El directorio de destino no tiene permisos de escritura";
                    return $arreglo;
                }
                $ext = '.' . pathinfo($imagen['name'], PATHINFO_EXTENSION);
                $generatedName = $nameImg . $ext;
                $filePath = $path . $generatedName;
                $arreglo["error"] = true;
                $arreglo["titulo"] = "¡ Error de subida !";
                $arreglo["msj"] = "No se pudo subir la imagen.";
                if (move_uploaded_file($imagen['tmp_name'], $filePath)) {
                    $arreglo["error"] = false;
                    $arreglo["titulo"] = "¡ Imagen subido !";
                    $arreglo["msj"] = "Se subio la imagen correctamente.";
                    $arreglo["generatedName"] = $generatedName;
                    $this -> u -> update($this -> u -> nameTable, ["imagen" => "'" . $generatedName . "'"], "id = " . $nameImg);
                }
            } else {
                $arreglo["error"] = true;
                $arreglo["titulo"] = "¡ Error de solicitud !";
                $arreglo["msj"] = "No se recibio ningun imagen para subir.";
            }
        }
        return $arreglo;
    }
    private function validarOperacionUsuario($id_usuario, $usuario, $operacion, $isOperacionPermiso = false) {
        $arreglo = array();
        $arreglo["error"] = false;
        if(!empty($id_usuario)) {
            $id_usuario = base64_decode($id_usuario);
            $tipoUsuario = $this -> u -> mostrar("id = $id_usuario", "tipo");
            if ($tipoUsuario) {
                $tipoUsuario = $tipoUsuario[0] -> tipo;
                switch ($tipoUsuario) {
                    case "superAdmin":
                        if ($usuario -> tipo != ADMIN && $usuario -> tipo != AUX) {
                            $arreglo["error"] = true;
                            $arreglo["titulo"] = "¡ Error de privilegios !";
                            $arreglo["msj"] = "No puedes $operacion " . (($isOperacionPermiso) ? "del": "el") . " usuario $usuario->email con el tipo: " . $usuario -> tipo . ". Solo puedes $operacion " . (($isOperacionPermiso) ? "de los": "") . " usuarios de tipo: Administrador y Auxiliar";
                            return $arreglo;
                        }
                        break;
                    case "admin":
                        if ($usuario -> tipo != AUX) {
                            $arreglo["error"] = true;
                            $arreglo["titulo"] = "¡ Error de privilegios !";
                            $arreglo["msj"] = "No puedes $operacion " . (($isOperacionPermiso) ? "del": "el") . " usuario $usuario->email con el tipo: " . $usuario -> tipo . ". Solo puedes $operacion " . (($isOperacionPermiso) ? "de los": "") . " usuarios de tipo: Auxiliar";
                            return $arreglo;
                        }
                        break;
                    default:
                        $arreglo["error"] = true;
                        $arreglo["titulo"] = "¡ Error de privilegios !";
                        $arreglo["msj"] = "No tienes privilegios para poder $operacion " . (($isOperacionPermiso) ? "del": "el") . " usuario $usuario->email de la aplicacion web.";
                        return $arreglo;
                        break;
                }
            } else {
                $arreglo["error"] = true;
                $arreglo["titulo"] = "¡ Error Interno !";
                $arreglo["msj"] = "Ocurrio un error al intentar corroboar sus permisos.";
                return $arreglo;
            }
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ Datos no recibidos !";
            $arreglo["msj"] = "NO se recibio ningun dato del usuario solicitado en el servidor";
            return $arreglo;
        }
        return $arreglo;
    }
    public function agregarUsuario() {
        $arreglo = array();
        $id_usuario = Form::getValue("id_usuario", false);
        $usuario = json_decode(Form::getValue("usuario", false, false));
        $arreglo = $this -> validarOperacionUsuario($id_usuario, $usuario, OPERACION_AGREGAR);
        if ($arreglo["error"]) return $arreglo;

        $isChange = Form::getValue("isChange");
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
            $isUploadIMG = $this -> uploadImage($isChange, $newIdUsuario["id"]);
            if ($isUploadIMG["error"]) {
                $arreglo["msj"] .= ". Pero " . $isUploadIMG["msj"];
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
        $id_usuario = Form::getValue("id_usuario", false);
        $usuario = json_decode(Form::getValue("usuario", false, false));
        $arreglo = $this -> validarOperacionUsuario($id_usuario, $usuario, OPERACION_ACTUALIZAR);
        if ($arreglo["error"]) return $arreglo;

        $isChange = Form::getValue("isChange");
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

            $arreglo["uploadImg"] = $this -> uploadImage($isChange, $usuario -> id);
            if ($arreglo["uploadImg"]["error"]) {
                $arreglo["msj"] .= ". Pero " . $arreglo["uploadImg"]["msj"];
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
        $id_usuario = Form::getValue("id_usuario", false);
        $usuarioSelected = json_decode(Form::getValue("usuarioSelected", false, false));
        $arreglo = $this -> validarOperacionUsuario($id_usuario, $usuarioSelected, OPERACION_ELIMINAR);
        if ($arreglo["error"]) return $arreglo;

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
            $arreglo["titulo"] = "¡ Error de dependencias !";
            $arreglo["msj"] = "Ocurrio un error al intentar eliminar los permisos asociados al Usuario: " . $usuarioSelected -> email;
        }
        return $arreglo;
    }
    public function actualizarPermisos_Usuario() {
        $arreglo = array();
        $isChangePermisos = json_decode(Form::getValue("isChangePermisos", false, false));
        if ($isChangePermisos -> isChangePermisos) {
            $arreglo = $this -> validarOperacionUsuario($isChangePermisos -> id_usuario, $isChangePermisos -> usuarioSelected, OPERACION_ACTUALIZAR_PERMISOSUSUARIOS, true);
            if ($arreglo["error"]) return $arreglo;

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
    public function updateProfile() {
        $arreglo = array();
        $usuarioProfile = json_decode(Form::getValue("usuarioProfile", false, false));
        $usuarioCurrent = json_decode(Form::getValue("usuarioCurrent", false, false));
        if (strtoupper($usuarioProfile -> email) != strtoupper($usuarioCurrent -> email)) {
            $arreglo = $this -> verifyExistsUsuario($usuarioProfile -> email, true);
            if ($arreglo["error"]) return $arreglo;
        }
        if(!empty($usuarioCurrent -> id)) {
            $usuarioCurrent -> id = base64_decode($usuarioCurrent -> id);
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ Usuario no recibido !";
            $arreglo["msj"] = "NO se recibio ningun dato referente al usuario solicitado en el servidor";
            return $arreglo;
        }
        $updateProfile = $this -> u -> actualizar($usuarioProfile, $usuarioCurrent, true);
        if ($updateProfile) {
            $arreglo["error"] = false;
            $arreglo["titulo"] = "¡ Perfil actualizado !";
            $arreglo["msj"] = "Se actualizaron correctamente los datos de su perfil";

            $this -> pk_Usuario["id"] = $usuarioCurrent -> id;
            $this -> select = "email, cargo";
            $arreglo["usuario"] = $this -> obtUsuariosGral(false);
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ Perfil no actualizado !";
            $arreglo["msj"] = "Ocurrio un error al intentar actuaizar los datos del servidor";
        }
        return $arreglo;
    }
    public function updateImage() {
        $arreglo = array();
        $id_usuario = Form::getValue("id_usuario", false);
        $isChange = Form::getValue("isChange");

        if(!empty($id_usuario)) {
            $id_usuario = base64_decode($id_usuario);
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ Usuario no recibido !";
            $arreglo["msj"] = "NO se recibio ningun dato referente al usuario solicitado en el servidor";
            return $arreglo;
        }
        $arreglo = $this -> uploadImage($isChange, $id_usuario);
        if (!$arreglo["error"] && $arreglo["msj"] == "No hay imagen para subir") {
            $arreglo["error"] = true;
        }
        return $arreglo;
    }
    public function verifyPasswordCurrent() {
        $arreglo = array();
        $password = Form::getValue("password");
        $id_usuario = Form::getValue("id_usuario", false);
        if (!empty($id_usuario)) {
            $id_usuario = base64_decode($id_usuario);
            $this -> where = "id != $id_usuario";
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ Usuario no recibido !";
            $arreglo["msj"] = "NO se recibio ningun dato del usuario solicitado en el servidor";
            return $arreglo;
        }
        $usuario = $this -> u -> mostrar("id = $id_usuario", "password");
        if ($usuario) {
            $usuario = $usuario[0];
            if (password_verify($password, $usuario -> password)) {
                $arreglo["error"] = false;
                $arreglo["titulo"] = "¡ Contraseña verificada !";
                $arreglo["msj"] = "Contraseña correcta";
            } else {
                $arreglo["error"] = true;
                $arreglo["titulo"] = "¡ Contraseña incorrecta !";
                $arreglo["msj"] = "La contraseña proporcionada no es correcta";
            }
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ Usuario no encontrado !";
            $arreglo["msj"] = "No se encontro el usuario solicitadp";
        }
        return $arreglo;
    }
    public function updatePassword() {
        $arreglo = array();
        $newPassword = Form::getValue("newPassword");
        $id_usuario = Form::getValue("id_usuario", false);
        if (!empty($id_usuario)) {
            $id_usuario = base64_decode($id_usuario);
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ Usuario no recibido !";
            $arreglo["msj"] = "NO se recibio ningun dato del usuario solicitado en el servidor";
            return $arreglo;
        }
        $updatePassword = $this -> u -> actualizarPassword($newPassword, $id_usuario);
        if ($updatePassword) {
            $arreglo["error"] = false;
            $arreglo["titulo"] = "¡ Password actualizado !";
            $arreglo["msj"] = "Se actualizo correctamente su contraseña";
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ Password no actualizado !";
            $arreglo["msj"] = "Ocurrio un error al intentar actualizar su contraseña";
        }
        return $arreglo;
    }
}