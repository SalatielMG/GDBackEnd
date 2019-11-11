<?php
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Methods: *');
    header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
	header("Content-type: application/json; charset=utf-8");
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");

	define("APP_PATH", "app/");
	define("APP_UTIL","util/");

	require_once APP_UTIL."inicio.php";

	//creando App
	$app = new App;