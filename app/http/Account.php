<?php
/**
 * Created by PhpStorm.
 * User: pc-hp
 * Date: 16/09/2019
 * Time: 01:34 PM
 */
Ruta::post("agregarAccount","ControlAccount@agregarAccount");
Ruta::post("actualizarAccount","ControlAccount@actualizarAccount");
Ruta::delete("eliminarAccount","ControlAccount@eliminarAccount");
Ruta::get("obtNewId_account","ControlAccount@obtNewId_account");
Ruta::get("obtAccountsBackup","ControlAccount@obtAccountsBackup");
Ruta::get("buscarAccountsBackup","ControlAccount@buscarAccountsBackup");
Ruta::get("buscarInconsistenciaDatosAccounts","ControlAccount@inconsistenciaAccounts");
Ruta::get("obtSizeTableaccounts","ControlAccount@obtSizeTable");
Ruta::get("corregirInconsistenciaDatosaccounts","ControlAccount@corregirInconsitencia");
Ruta::get("corregirInconsistenciaRegistroAccount","ControlAccount@corregirInconsistenciaRegistro");