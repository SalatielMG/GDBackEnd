<?php
/*
require_once(APP_PATH."model/Usuario.php");
require_once(APP_PATH."model/Permiso.php");
class Valida{
	protected $token;
	protected $id;
	protected $mu;
	public function __construct(){
		$this->token = Form::getValue("token",false);
		$this->id = Form::getValue("id",false);
		if(!empty($this->id))
			$this->id = base64_decode($this->id);
		$this->mu = new Usuario;
		
	}

	public function validaToken(){
		$token = $this->mu->getToken($this->id);
		if($token != $this->token){
		    echo json_encode([
				"permiso"=>0,
				"error"=>1,
				"msj"=>"Token no correspondiente"
			]);
			exit();
		}
	}

	public function permiso($permiso){
		$mp = new Permiso;
		if(count($mp->verificarPermiso($this->id,$permiso)) == 0 ){
			$p = $mp->mostrar("clvP = '$permiso'");
			$np = "El permiso no existe";
			if(count($p) > 0)
				$np = "No tiene el permiso.-".$p[0]->nombreP.".- pongase en contacto con el admin";
			echo json_encode([
				"permiso"=>0,
				"error"=>-2,
				"msj"=>$np
			]);
			exit();
		}
	}
}*/