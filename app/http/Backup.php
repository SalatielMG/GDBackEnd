<?php
/**
 * Created by PhpStorm.
 * User: pc-hp
 * Date: 16/09/2019
 * Time: 01:33 PM
 */
Ruta::get("exportarBackup", "ControlBackup@exportarBackup");
Ruta::post("actualizarBackup","ControlBackup@actualizarBackup");
Ruta::delete("eliminarBackup","ControlBackup@eliminarBackup");
Ruta::get("buscarBackupsUserId","ControlBackup@buscarBackupsUserId");
Ruta::get("buscarBackupsUserEmail","ControlBackup@buscarBackupsUserEmail");
Ruta::get("buscarBackupsUserMnt","ControlBackup@buscarBackupsUserCantidad");
Ruta::get("buscarUsersExportacionBackups","ControlBackup@buscarUsersExportacionBackups");
Ruta::delete("limpiarBackups","ControlBackup@limpiarBackups");
Ruta::delete("limpiarBackupsUsers","ControlBackup@limpiarBackupsUsers");