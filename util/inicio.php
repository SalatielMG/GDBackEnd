<?php
	chdir(dirname(__DIR__));

	//cargando clases
	require_once(APP_UTIL."config.php");
	require_once(APP_UTIL."Form.php");
	require_once(APP_PATH."model/DB.php");
	require_once(APP_PATH."control/Valida.php");
	require_once(APP_UTIL."App.php");
	require_once(APP_UTIL."Ruta.php");
	require_once(APP_PATH."http/rutas.php");
