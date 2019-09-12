<?php

    /*Rutas de prueba*/
	Ruta::get("contraseña","Prueba@encriptacion");
	Ruta::get("backupBD","Prueba@backupBD");

	/*Usuario*/
	Ruta::post("login","ControlUsuario@login");

	/*User*/
	Ruta::post("buscarUser","ControlUsers@buscarUser");
	Ruta::get("valoresGrafica","ControlUsers@ValoresGraficaBackupsCategoriasGastos");
	Ruta::get("valoresGraficaGVSI","ControlUsers@ValoresGrficaGastosvsIngresos");

	/*Backup*/
    Ruta::delete("eliminarBackup","ControlBackup@eliminarBackup");
    Ruta::get("buscarBackupsUserId","ControlBackup@buscarBackupsUserId");
    Ruta::get("buscarBackupsUserEmail","ControlBackup@buscarBackupsUserEmail");
    Ruta::get("buscarBackupsUserMnt","ControlBackup@buscarBackupsUserCantidad");
    Ruta::delete("limpiarBackups","ControlBackup@limpiarBackups");

    /*Accounts*/
    Ruta::get("buscarAccountsBackup","ControlAccount@buscarAccountsBackup");
    Ruta::get("buscarInconsistenciaDatosAccounts","ControlAccount@inconsistenciaAccounts");
    Ruta::get("corregirInconsistenciaDatosaccounts","ControlAccount@corregirInconsitencia");

    /*Automatics*/
    Ruta::get("buscarAutomaticsBackup","ControlAutomatic@buscarAutomaticsBackup");
    Ruta::get("buscarInconsistenciaDatosAutomatics","ControlAutomatic@inconsistenciaAutomatics");
    Ruta::get("corregirInconsistenciaDatosautomatics","ControlAutomatic@corregirInconsitencia");

    /*Budgets*/
    Ruta::get("buscarBudgetsBackup","ControlBudget@buscarBudgetsBackup");
    Ruta::get("buscarInconsistenciaDatosBudgets","ControlBudget@inconsistenciaBudget");
    Ruta::get("corregirInconsistenciaDatosbudgets","ControlBudget@corregirInconsitencia");

    /*Cardviews*/
    Ruta::get("buscarCardviewsBackup","ControlCardView@buscarCardviewsBackup");
    Ruta::get("buscarInconsistenciaDatosCardviews","ControlCardView@inconsistenciaCardView");
    Ruta::get("corregirInconsistenciaDatoscardviews","ControlCardView@corregirInconsitencia");

    /*Categories*/
    Ruta::get("buscarCategoriesBackup","ControlCategory@buscarCategoriesBackup");
    Ruta::get("buscarInconsistenciaDatosCategories","ControlCategory@inconsistenciaCategory");
    Ruta::get("corregirInconsistenciaDatoscategories","ControlCategory@corregirInconsitencia");

    /*Currencies*/
    Ruta::get("buscarCurrenciesBackup","ControlCurrency@buscarCurrenciesBackup");
    Ruta::get("buscarInconsistenciaDatosCurrencies","ControlCurrency@inconsistenciaCurrency");
    Ruta::get("corregirInconsistenciaDatoscurrencies","ControlCurrency@corregirInconsitencia");

    /*Extras*/
    Ruta::get("buscarExtrasBackup","ControlExtra@buscarExtrasBackup");
    Ruta::get("buscarInconsistenciaDatosExtras","ControlExtra@inconsistenciaExtra");
    Ruta::get("corregirInconsistenciaDatosextras","ControlExtra@corregirInconsitencia");

    /*Movements*/
    Ruta::get("buscarMovementsBackup","ControlMovement@buscarMovementsBackup");
    Ruta::get("buscarInconsistenciaDatosMovements","ControlMovement@inconsistenciaMovement");
    Ruta::get("corregirInconsistenciaDatosmovements","ControlMovement@corregirInconsitencia");

    /*Preferences*/
    Ruta::get("buscarPreferencesBackup","ControlPreference@buscarPreferencesBackup");
    Ruta::get("buscarInconsistenciaDatosPreferences","ControlPreference@inconsistenciaPreference");
    Ruta::get("corregirInconsistenciaDatospreferences","ControlPreference@corregirInconsitencia");
