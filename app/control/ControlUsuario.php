<?php
/**
 * Created by PhpStorm.
 * Users: pc-01
 * Date: 15/08/2019
 * Time: 11:09
 */
require_once(APP_PATH.'model/Usuario.php');
class ControlUsuario
{
    private $u;
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
                $arreglo["id"] = base64_encode($usuario -> id);
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


}