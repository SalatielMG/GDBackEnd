<?php

    require_once ("Account.php");
    require_once ("Automatic.php");
    require_once ("Backup.php");
    require_once ("Budget.php");
    require_once ("Cardview.php");
    require_once ("Category.php");
    require_once ("Currency.php");
    require_once ("Extra.php");
    require_once ("Movement.php");
    require_once ("Preference.php");
    require_once ("User.php");
    require_once ("Usuario.php");
    /*Rutas de prueba*/
	Ruta::get("contraseña","Prueba@encriptacion");
	Ruta::get("backupBD","Prueba@backupBD");

