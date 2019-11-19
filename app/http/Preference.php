<?php
/**
 * Created by PhpStorm.
 * User: pc-hp
 * Date: 16/09/2019
 * Time: 01:36 PM
 */
Ruta::post("agregarPreference","ControlPreference@agregarPreference");
Ruta::post("actualizarPreference","ControlPreference@actualizarPreference");
Ruta::delete("eliminarPreference","ControlPreference@eliminarPreference");
Ruta::get("buscarPreferencesBackup","ControlPreference@buscarPreferencesBackup");
Ruta::get("buscarInconsistenciaDatosPreferences","ControlPreference@inconsistenciaPreference");
Ruta::get("obtSizeTablepreferences","ControlPreference@obtSizeTable");
Ruta::get("corregirInconsistenciaDatospreferences","ControlPreference@corregirInconsitencia");