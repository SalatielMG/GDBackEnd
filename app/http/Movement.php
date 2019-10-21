<?php
/**
 * Created by PhpStorm.
 * User: pc-hp
 * Date: 16/09/2019
 * Time: 01:35 PM
 */
Ruta::post("agregarMovement","ControlMovement@agregarMovement");
Ruta::post("actualizarMovement","ControlMovement@actualizarMovement");
Ruta::delete("eliminarMovement","ControlMovement@eliminarMovement");
Ruta::get("buscarMovementsBackup","ControlMovement@buscarMovementsBackup");
Ruta::get("buscarInconsistenciaDatosMovements","ControlMovement@inconsistenciaMovement");
Ruta::get("corregirInconsistenciaDatosmovements","ControlMovement@corregirInconsitencia");