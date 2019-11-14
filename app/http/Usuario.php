<?php
/**
 * Created by PhpStorm.
 * User: pc-hp
 * Date: 16/09/2019
 * Time: 01:32 PM
 */
Ruta::post("login","ControlUsuario@login");
Ruta::post("obtUsuario","ControlUsuario@obtUsuario");
Ruta::get("obtUsuariosGral","ControlUsuario@obtUsuariosGral");
Ruta::get("verifyPasswordCurrent","ControlUsuario@verifyPasswordCurrent");
Ruta::get("verifyEmailAndSendCode","ControlUsuario@verifyEmailAndSendCode");
Ruta::get("verifyCodeResetPasword","ControlUsuario@verifyCodeResetPasword");
Ruta::post("agregarUsuario","ControlUsuario@agregarUsuario");
Ruta::post("UpdateProfile","ControlUsuario@updateProfile");
Ruta::post("UpdatePassword","ControlUsuario@updatePassword");
Ruta::post("ResetPassword","ControlUsuario@resetPassword");
Ruta::post("UpdateImage","ControlUsuario@updateImage");
Ruta::post("actualizarUsuario","ControlUsuario@actualizarUsuario");
Ruta::post("actualizarPermisos_Usuario","ControlUsuario@actualizarPermisos_Usuario");
Ruta::delete("eliminarUsuario","ControlUsuario@eliminarUsuario");