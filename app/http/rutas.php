<?php

    /*Rutas de prueba*/
	Ruta::get("contraseña","Prueba@encriptacion");

	/*Usuario*/
	Ruta::post("login","ControlUsuario@login");

	/*User*/
	Ruta::post("buscarUser","ControlUsers@buscarUser");
	Ruta::get("valoresGrafica","ControlUsers@ValoresGraficaBackupsCategoriasGastos");
	Ruta::get("valoresGraficaGVSI","ControlUsers@ValoresGrficaGastosvsIngresos");

	/*Backup*/
    Ruta::delete("eliminarBackup","ControlBackup@eliminarBackup");
    Ruta::get("buscarBackups","ControlBackup@buscarBackups");

    /*Accounts*/
    Ruta::get("buscarAccountsBackup","ControlAccount@buscarAccountsBackup");
    Ruta::get("buscarInconsistenciaDatosAccounts","ControlAccount@inconsistenciaAccounts");

    /*Automatics*/
    Ruta::get("buscarAutomaticsBackup","ControlAutomatic@buscarAutomaticsBackup");
    Ruta::get("buscarInconsistenciaDatosAutomatics","ControlAutomatic@inconsistenciaAutomatics");

    /*Budgets*/
    Ruta::get("buscarBudgetsBackup","ControlBudget@buscarBudgetsBackup");
    Ruta::get("buscarInconsistenciaDatosBudgets","ControlBudget@inconsistenciaBudget");

    /*Cardviews*/
    Ruta::get("buscarCardviewsBackup","ControlCardView@buscarCardviewsBackup");
    Ruta::get("buscarInconsistenciaDatosCardviews","ControlCardView@inconsistenciaCardView");

    /*Categories*/
    Ruta::get("buscarCategoriesBackup","ControlCategory@buscarCategoriesBackup");
    Ruta::get("buscarInconsistenciaDatosCategories","ControlCategory@inconsistenciaCategory");

    /*Currencies*/
    Ruta::get("buscarCurrenciesBackup","ControlCurrency@buscarCurrenciesBackup");
    Ruta::get("buscarInconsistenciaDatosCurrencies","ControlCurrency@inconsistenciaCurrency");

    /*Extras*/
    Ruta::get("buscarExtrasBackup","ControlExtra@buscarExtrasBackup");
    Ruta::get("buscarInconsistenciaDatosExtras","ControlExtra@inconsistenciaExtra");

    /*Movements*/
    Ruta::get("buscarMovementsBackup","ControlMovement@buscarMovementsBackup");
    Ruta::get("buscarInconsistenciaDatosMovements","ControlMovement@inconsistenciaMovement");

    /*Preferences*/
    Ruta::get("buscarPreferencesBackup","ControlPreference@buscarPreferencesBackup");
    Ruta::get("buscarInconsistenciaDatosPreferences","ControlPreference@inconsistenciaPreference");
