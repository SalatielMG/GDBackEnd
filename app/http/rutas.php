<?php
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

    /*Automatics*/
    Ruta::get("buscarAutomaticsBackup","ControlAutomatic@buscarAutomaticsBackup");

    /*Budgets*/
    Ruta::get("buscarBudgetsBackup","ControlBudget@buscarBudgetsBackup");

    /*Cardviews*/
    Ruta::get("buscarCardviewsBackup","ControlCardView@buscarCardviewsBackup");

    /*Categories*/
    Ruta::get("buscarCategoriesBackup","ControlCategory@buscarCategoriesBackup");

    /*Currencies*/
    Ruta::get("buscarCurrenciesBackup","ControlCurrency@buscarCurrenciesBackup");

    /*Extras*/
    Ruta::get("buscarExtrasBackup","ControlExtra@buscarExtrasBackup");

    /*Movements*/
    Ruta::get("buscarMovementsBackup","ControlMovement@buscarMovementsBackup");

    /*Preferences*/
    Ruta::get("buscarPreferencesBackup","ControlPreference@buscarPreferencesBackup");
