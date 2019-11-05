<?php
/**
 * Created by PhpStorm.
 * User: pc-01
 * Date: 01/11/2019
 * Time: 11:50 AM
 */
Ruta::get("obtPermisosGral", "ControlPermiso@getPermisosGral");
Ruta::post("agregarPermiso", "ControlPermiso@agregarPermiso");
Ruta::post("actualizarPermiso", "ControlPermiso@actualizarPermiso");
Ruta::post("actualizarUsuarios_Permiso", "ControlPermiso@actualizarUsuarios_Permiso");
Ruta::delete("eliminarPermiso", "ControlPermiso@eliminarPermiso");