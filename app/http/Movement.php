<?php
/**
 * Created by PhpStorm.
 * User: pc-hp
 * Date: 16/09/2019
 * Time: 01:35 PM
 */
Ruta::get("buscarMovementsBackup","ControlMovement@buscarMovementsBackup");
Ruta::get("buscarInconsistenciaDatosMovements","ControlMovement@inconsistenciaMovement");
Ruta::get("corregirInconsistenciaDatosmovements","ControlMovement@corregirInconsitencia");