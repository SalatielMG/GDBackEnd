<?php
/**
 * Created by PhpStorm.
 * User: pc-hp
 * Date: 17/08/2019
 * Time: 12:38 PM
 */
require_once(APP_PATH.'model/Backup.php');

class ControlBackup extends Valida
{
    private $b;
    private $idUser;
    private $rango;
    private $cantidad;
    private $email;
    private $pagina = 0;

    public function __construct()
    {
        $this -> b = new Backup();
    }
    public function buscarBackups() {
        $idUser = Form::getValue('idUser');
        $OrdeBy = Form::getValue('orderby');
        $this -> pagina = Form::getValue('pagina');
        $arreglo = array();
        $this -> pagina = $this -> pagina * $this -> limit;
        $select = $this -> b -> mostrar("id_user = $idUser order by id_backup $OrdeBy " . $this -> condicionarLimit($this -> pagina, "-$this->limit"));
        if ($select) {
            $arreglo["error"] = false;
            $arreglo["backups"] = $select;
            $arreglo["titulo"] = "¡ BACKUPS ENCONTRADOS !";
            $arreglo["msj"] = "Se encontraron backups del usuario solicitado.";
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ BACKUPS NO ENCONTRADOS !";
            $arreglo["msj"] = "No se encontraron backups del usuario solicitado.";
        }
        return $arreglo;
    }
    public function buscarBackupsUserCantidad() {
        $this -> email = Form::getValue('email');
        $this -> rango = Form::getValue('cantidad');
        $this -> pagina = Form::getValue('pagina');
        $arreglo = array();

        $form = new Form();
        $form -> validarDatos($this -> rango, 'Cantidad mínima de backups', 'required|enterosPositivos');

        if ($this -> email != "Generales") {
            $form -> validarDatos($this -> email, 'Correo electronico', 'email');
        }
        if (count($form -> errores) > 0) {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ ERROR DE VALIDACIÓN !";
            $arreglo["msj"] = $form -> errores;
            return $arreglo;
        }
        $arreglo = $this -> buscarBackupsUC();
        return $arreglo;
    }
    public function buscarBackupsUC() {
        $arreglo = array();
        if ($this -> email != "Generales") {
            $where = "email = " . "'".$this -> email."'";
            $select = "id_user, email";
            $table = "users";
            $consultaUser = $this -> b -> mostrar($where, $select, $table);
            if (!$consultaUser) {
                $arreglo["error"] = true;
                $arreglo["titulo"] = "¡ USUARIO NO ENCONTRADO !";
                $arreglo["msj"] = " El usuario solicitado no se encuentra registrado la base de datos ";
                return $arreglo;
            }
        }
        $this -> pagina = $this -> pagina * $this -> limit;
        $where = "1 ORDER BY tabla.cantRep desc limit $this->pagina, $this->limit";
        $select = "tabla.*, 0 as collapsed";
        $table = "((SELECT b.id_user, u.email, COUNT(b.id_user) as cantRep FROM backups b, users u WHERE b.id_user = u.id_user ". $this -> condicionarConsulta("'".$this -> email."'", "u.email", "'Generales'")." GROUP BY b.id_user HAVING COUNT(*) > $this->rango) AS tabla)";
        $arreglo["consulta"] = $this -> consultaSQL($select, $table, $where);
        //return $arreglo;
        $consulta = $this -> b -> mostrar($where, $select, $table);
        if ($consulta) {
            $arreglo["error"] = false;
            $arreglo["backups"] = $consulta;
            $arreglo["titulo"] = "¡ BACKUPS ENCONTRADOS !";
            $arreglo["msj"] = "Se encontraron mas de $this->rango backups ". (($this -> email == "Generales") ? "de todos los usuarios generales" : "del usuario: $this->email");
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ BACKUPS NO ENCONTRADOS !";
            $arreglo["msj"] = "No se encontraron mas de $this->rango backups ". (($this -> email == "Generales") ? "de todos los usuarios generales" : "del usuario: $this->email");
        }
        return $arreglo;
    }
    public function eliminarBackup() {
        $id = Form::getValue('id');

        $arreglo = array();

        $delete = $this -> b -> eliminar($id);
        if ($delete) {
            $arreglo["error"] = false;
            $arreglo["titulo"] = "¡ BACKUP ELIMINADA !";
            $arreglo["msj"] = "El backup $id ha sido eliminado satisfactoriamente";
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ BACKUP NO ELIMINADA !";
            $arreglo["msj"] = "Ocurrio un error al intentar eliminar el backup: $id";
        }
        return $arreglo;
    }

    public function limpiarBackups() {
        $this -> idUser = Form::getValue("idUser");
        $this -> rango = Form::getValue("rango");
        $arreglo = array();
        $form = new Form();
        $form -> validarDatos($this -> idUser,"Usuario","required|enterosPositivos");
        if ($this -> idUser == 0) { // Limpieza general
            if (count($form -> errores) > 0) {
                $arreglo["error"] = true;
                $arreglo["titulo"] = "¡ ERROR DE VALIDACIÓN !";
                $arreglo["msj"] = $form -> errores;
                return $arreglo;
            }
            $this -> email = "Generales";
            $UsuariosGralBack = $this -> buscarBackupsUC();
            $arreglo["UsuariosGralBack "] = $UsuariosGralBack;
            if (!$UsuariosGralBack["error"]) {
                $error = 0;
                foreach ($UsuariosGralBack["backups"] as $key => $value) {
                    $this -> idUser = $value -> id_user;
                    $this -> cantidad = $value -> cantRep;
                    $this -> cantidad = $this -> cantidad - $this -> rango;
                    $this -> email = $value -> email;
                    $limpiarBackupsUser = $this -> limpiarBackupsUser();
                    if ($limpiarBackupsUser["error"]) {
                        $error++;
                        $arreglo["error"] = true;
                        $arreglo["titulo"] = ($error == 1) ? "¡ ERROR DE AJUSTE DE 1 USUARIO !" : "¡ ERROR DE AJUTE DE $error USUARIOS !";
                        $arreglo["msj"] = "No se ajustaron algunos o todos lo backups de $error usuario" . (($error == 1) ? "": "s");
                        $arreglo["errorUser"][$this -> idUser] =  $limpiarBackupsUser;
                    }
                }
                if ($error == 0){
                    $arreglo["error"] = false;
                    $arreglo["titulo"] = "¡ BACKUPS AJUSTADOS !";
                    $arreglo["msj"] = "Se ajustaron correctamente todos los backups de los usuarios que tenian mas de $this->rango Respaldos.";
                }
            } else {
                $arreglo = $UsuariosGralBack;
            }
        } else { // Limpieza de un solo usuario
            $this -> email = Form::getValue("email");
            $this -> cantidad = Form::getValue("cantidad");
            $this -> cantidad = $this -> cantidad - $this -> rango;
            if (count($form -> errores) > 0) {
                $arreglo["error"] = true;
                $arreglo["titulo"] = "¡ ERROR DE VALIDACIÓN !";
                $arreglo["msj"] = $form -> errores;
                return $arreglo;
            }
            $arreglo = $this -> limpiarBackupsUser();
        }
        return $arreglo;
    }
    private function limpiarBackupsUser() {
        $arreglo = array();
        $where = "id_backup in (select tabla.id_backup from ((SELECT @rownum:=@rownum+1 AS pos, b.id_backup FROM (SELECT @rownum:=0) r, `backups` b where b.id_user = $this->idUser ORDER BY `b`.`id_backup` DESC) as tabla) where tabla.pos > $this->rango)";
        $delete = $this -> b -> eliminarBackupUser($where);
        if ($delete) {
            $arreglo["error"] = false;
            $arreglo["titulo"] = ($this -> cantidad == 1) ? "¡ BACKUP AJUSTADO !" : "¡ BACKUPS AJUSTADOS !";
            $arreglo["msj"] = (($this -> cantidad == 1) ? "El backup ha sido eliminado satisfactoriamente" : "Los bakups han sido eliminados satisfactoriamente") . " del usuario : " . $this -> email;
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = ($this -> cantidad == 1) ? "¡ BACKUP NO AJUSTADO !" : "¡ BACKUPS NO AJUSTADOS !";
            $arreglo["msj"] = "Ocurrio un error al intentar eliminar " . (($this -> cantidad == 1) ? "el backup" : " algunos o todos los backups") . " del usuario : " . $this -> email;
        }
        return $arreglo;
    }
}