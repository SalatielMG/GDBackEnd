<?php
/**
 * Created by PhpStorm.
 * User: pc-hp
 * Date: 16/09/2019
 * Time: 01:35 PM
 */
Ruta::get("insertCurrencies","ControlCurrency@insertCurrencies");
Ruta::get("buscarCurrenciesBackup","ControlCurrency@buscarCurrenciesBackup");
Ruta::get("buscarInconsistenciaDatosCurrencies","ControlCurrency@inconsistenciaCurrency");
Ruta::get("corregirInconsistenciaDatoscurrencies","ControlCurrency@corregirInconsitencia");