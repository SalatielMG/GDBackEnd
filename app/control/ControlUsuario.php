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

    public function obtUsersGral() {
        $arreglo = array();
        $users = $this -> u -> mostrar();
        if ($users) {
            for ($i = 0; $i < 35 ; $i++)
            {
                foreach ($users as $key => $user) {
                    $users[$i] = $user;
                }
            }
            $arreglo["usuarios"] = $users;
            $arreglo["error"] = false;
            $arreglo["titulo"] = "¡ Usuarios encontrados !";
            $arreglo["msj"] = "Se econtraron usuarios registrados en la base de datos.";
        } else {
            $arreglo["usuarios"] = [];
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ Usuarios no encontrados !";
            $arreglo["msj"] = "No se econtraro usuarios registrad en la base de datos.";
        }
        return $arreglo;
    }

    public function obtUsuariosGral() {
        $id_usuario = Form::getValue("id_usuario", false);
        $show_permiso = Form::getValue("show_permiso");
        if ($id_usuario == 0) { // Busca todos los usuarios
            $this -> where = "1";
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
        $usuarios = $this -> u -> mostrar();
        if ($usuarios) {
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
            $arreglo["titulo"] = "¡ USUARIOS ENCONTRADOS !";
            $arreglo["msj"] = "Se encontraron usuarios regsitrados en la base de datos";

        } else {
            $arreglo["usuarios"] = [];
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ USUARIOS NO ENCONTRADOS !";
            $arreglo["msj"] = "NO se encontraron usuarios regsitrados en la base de datos";
        }
        return $arreglo;
    }

    public function obtUsuarios_Permiso($permiso) {
        $arreglo = array();
        $this -> select = "u.*";
        $this -> table = "usuarios_permisos up, usuarios u";
        $this -> where = "up.permiso = '$permiso' AND u.id = up.usuario";
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

}