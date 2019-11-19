<?php
/**
 * Created by PhpStorm.
 * User: pc-hp
 * Date: 16/09/2019
 * Time: 01:35 PM
 */
Ruta::post("agregarExtra","ControlExtra@agregarExtra");
Ruta::post("actualizarExtra","ControlExtra@actualizarExtra");
Ruta::delete("eliminarExtra","ControlExtra@eliminarExtra");
Ruta::get("buscarExtrasBackup","ControlExtra@buscarExtrasBackup");
Ruta::get("buscarInconsistenciaDatosExtras","ControlExtra@inconsistenciaExtra");
Ruta::get("obtSizeTableextras","ControlExtra@obtSizeTable");
Ruta::get("corregirInconsistenciaDatosextras","ControlExtra@corregirInconsitencia");
Ruta::get("corregirInconsistenciaRegistroExtra","ControlExtra@corregirInconsistenciaRegistro");