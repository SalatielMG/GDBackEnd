<?php
/**
 * Created by PhpStorm.
 * User: pc-hp
 * Date: 16/09/2019
 * Time: 01:34 PM
 */
Ruta::get("buscarAccountsBackup","ControlAccount@buscarAccountsBackup");
Ruta::get("buscarInconsistenciaDatosAccounts","ControlAccount@inconsistenciaAccounts");
Ruta::get("corregirInconsistenciaDatosaccounts","ControlAccount@corregirInconsitencia");