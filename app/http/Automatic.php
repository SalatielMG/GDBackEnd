<?php
/**
 * Created by PhpStorm.
 * User: pc-hp
 * Date: 16/09/2019
 * Time: 01:34 PM
 */
Ruta::post("agregarAutomatic","ControlAutomatic@agregarAutomatic");
Ruta::post("actualizarAutomatic","ControlAutomatic@actualizarAutomatic");
Ruta::delete("eliminarAutomatic","ControlAutomatic@eliminarAutomatic");

Ruta::get("obtNewId_OperationAccountsCategories","ControlAutomatic@obtNewId_OperationAccountsCategories");
Ruta::get("buscarAutomaticsBackup","ControlAutomatic@buscarAutomaticsBackup");
Ruta::get("buscarInconsistenciaDatosAutomatics","ControlAutomatic@inconsistenciaAutomatics");
Ruta::get("obtSizeTableautomatics","ControlAutomatic@obtSizeTable");
Ruta::get("corregirInconsistenciaDatosautomatics","ControlAutomatic@corregirInconsitencia");
Ruta::get("corregirInconsistenciaRegistroAutomatic","ControlAutomatic@corregirInconsistenciaRegistro");