<?php
/**
 * Created by PhpStorm.
 * User: pc-hp
 * Date: 16/09/2019
 * Time: 01:34 PM
 */
Ruta::post("agregarBudget","ControlBudget@agregarBudget");
Ruta::post("actualizarBudget","ControlBudget@actualizarBudget");
Ruta::delete("eliminarBudget","ControlBudget@eliminarBudget");
Ruta::get("buscarBudgetsBackup","ControlBudget@buscarBudgetsBackup");
Ruta::get("buscarInconsistenciaDatosBudgets","ControlBudget@inconsistenciaBudget");
Ruta::get("obtSizeTablebudgets","ControlBudget@obtSizeTable");
Ruta::get("corregirInconsistenciaDatosbudgets","ControlBudget@corregirInconsitencia");
Ruta::get("corregirInconsistenciaRegistroBudget","ControlBudget@corregirInconsistenciaRegistro");