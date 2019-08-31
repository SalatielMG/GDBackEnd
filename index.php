<?php
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Methods: *');
    header("Access-Control-Allow-Headers: X-Requested-With");
	header("Content-type: application/json; charset=utf-8");



	define("APP_PATH", "app/");
	define("APP_UTIL","util/");

	require_once APP_UTIL."inicio.php";
	



	//creando App
	$app = new App;