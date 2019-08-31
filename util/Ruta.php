<?php
// "home",Control@index
//GET,POST,PUT,DELETE
class Ruta{
	private static $rutas = [];

	private function __construct(){}

	private static function agregar($ruta,$accion,$metodo){
		list($control,$accion) = explode("@",$accion);
		static::$rutas[$ruta] = ["control"=>$control,"accion"=>$accion,"metodo"=>$metodo];
	}

	public static function post($ruta,$accion){
		static::agregar($ruta,$accion,"POST");
	}
	public static function get($ruta,$accion){
		static::agregar($ruta,$accion,"GET");
	}
	public static function put($ruta,$accion){
		static::agregar($ruta,$accion,"PUT");
	}
	public static function delete($ruta,$accion){
		static::agregar($ruta,$accion,"DELETE");
	}

	public static function getAccion($ruta){
		
		if(array_key_exists($ruta, static::$rutas)){
			if(strtoupper($_SERVER["REQUEST_METHOD"]) == static::$rutas[$ruta]["metodo"])
				return static::$rutas[$ruta];
			else
				throw new Exception(json_encode(["error"=>400,"msj"=>"Request method no valido en la ruta  $ruta"]));
				
		}else
			throw new Exception(json_encode(["error"=>400,"msj"=>"la ruta $ruta no es valida"]));
			//home/index
	}
}