<?php
	class App{

		function __construct(){
			$url = $this->getUrl();
			try{
				
				$accion = Ruta::getAccion($url);
				$control = $accion["control"];
				$metodo = $accion["accion"];
				require_once(APP_PATH."control/$control".".php");
				$c = new $control();
				
				echo json_encode($c->$metodo(),JSON_UNESCAPED_UNICODE);

			}catch(Exception $e){
				echo $e->getMessage();
			}
		}

		function getUrl(){
			if(isset($_GET["url"]))
				return trim($_GET["url"]);
			else
				return "/";
		}
	}