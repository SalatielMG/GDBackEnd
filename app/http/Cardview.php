<?php
/**
 * Created by PhpStorm.
 * User: pc-hp
 * Date: 16/09/2019
 * Time: 01:35 PM
 */
Ruta::post("agregarCardview","ControlCardView@agregarCardview");
Ruta::post("actualizarCardview","ControlCardView@actualizarCardview");
Ruta::delete("eliminarCardview","ControlCardView@eliminarCardview");
Ruta::get("obtCardViewsGralBackup","ControlCardView@obtCardViewsGralBackup");
Ruta::get("buscarCardviewsBackup","ControlCardView@buscarCardviewsBackup");
Ruta::get("buscarInconsistenciaDatosCardviews","ControlCardView@inconsistenciaCardView");
Ruta::get("obtSizeTablecardviews","ControlCardView@obtSizeTable");
Ruta::get("corregirInconsistenciaDatoscardviews","ControlCardView@corregirInconsitencia");
Ruta::get("corregirInconsistenciaRegistroCardview","ControlCardView@corregirInconsistenciaRegistro");