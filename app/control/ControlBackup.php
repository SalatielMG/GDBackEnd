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
    public function __construct()
    {
        $this -> b = new Backup();
    }
    private function contarRegistrosBackupsUser($idUser) {
        $select = $this -> b -> mostrar("id_user = $idUser", "count(*) total");
        return ($select) ? $select[0] -> total: 0;
    }
    private function construirPaginacion($idUser) {
        $paginacion = array();
        $totalBackups = $this -> contarRegistrosBackupsUser($idUser);
        if ($totalBackups != 0) {
            /*
             * Paginas de 10
             *
             * */
            if ($totalBackups >= 10) {
                //totalBackups = 87;
                $paginacion["resultado"] = $totalBackups/10;
                $paginacion["resultadoRedondeado_ceil"] = ceil($paginacion["resultado"]);
            } else {
                $paginacion["resultado"] = $totalBackups;
            }
            $paginacion["error"] = false;

        } else {
            $paginacion["error"] = true;
        }
        return $paginacion;
    }
    public function buscarBackups() {
        $idUser = Form::getValue('idUser');
        $OrdeBy = Form::getValue('orderby');
        $arreglo = array();
        $paginacion = $this -> construirPaginacion($idUser);
        if ($paginacion["error"]){
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ BACKUPS NO ENCONTRADOS !";
            $arreglo["msj"] = "No se encontraron backups del usuario solicitado.";
            return $arreglo;
        }
        $arreglo["paginacion"] = $paginacion;
        $select = $this -> b -> mostrar("id_user = $idUser order by id_backup $OrdeBy");
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
        $email = Form::getValue('email');
        $cantidad = Form::getValue('cantidad');
        $arreglo = array();

        $form = new Form();
        $form -> validarDatos($cantidad, 'Cantidad de backups', 'required|enterosPositivos');

        if ($email != "Generales") {
            $form -> validarDatos($email, 'Correo electronico', 'email');
        }
        if (count($form -> errores) > 0) {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ ERROR DE VALIDACIÓN !";
            $arreglo["msj"] = $form -> errores;
            return $arreglo;
        }
        /*
         * 1.- Buscar al usuario.
         * 2.- Buscar los backups del usuario
         * */
        if ($email != "Generales") {
            $where = "email = '$email'";
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

        $where = "1 ORDER BY tabla.cantRep desc limit 0,50";
        $select = "tabla.*, 0 as collapsed";
        $table = "((SELECT b.id_user, u.email, COUNT(b.id_user) as cantRep FROM backups b, users u WHERE b.id_user = u.id_user ". $this -> condicionarConsulta("'$email'", "u.email", "'Generales'")." GROUP BY b.id_user HAVING COUNT(*) >= $cantidad) AS tabla)";
        $arreglo["consulta"] = $this -> consultaSQL($select, $table, $where);
        //return $arreglo;
        $consulta = $this -> b -> mostrar($where, $select, $table);
        if ($consulta) {
            $arreglo["error"] = false;
            $arreglo["backups"] = $consulta;
            $arreglo["titulo"] = "¡ BACKUPS ENCONTRADOS !";
            $arreglo["msj"] = "Se encontraron mas de $cantidad backups ". (($email == "Generales") ? "de todos los usuarios generales" : "del usuario: $email");
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ BACKUPS NO ENCONTRADOS !";
            $arreglo["msj"] = "No se encontraron mas de $cantidad backups ". (($email == "Generales") ? "de todos los usuarios generales" : "del usuario: $email");
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
            $arreglo["msj"] = "El backup $id has sido eliminado satisfactoriamente";
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ BACKUP NO ELIMINADA !";
            $arreglo["msj"] = "Ocurrio un error al intentar eliminar el backup: $id";
        }
        return $arreglo;
    }
    public function limpiarBackupsUser() {
        $idUSer = Form::getValue("idUser");
        $rango = Form::getValue("rango");
        $arreglo = array();
        $where = "id_backup in (select tabla.id_backup from ((SELECT @rownum:=@rownum+1 AS pos, b.* FROM (SELECT @rownum:=0) r, `backups` b where b.id_user = $idUSer ORDER BY `b`.`id_backup` DESC) as tabla) where tabla.pos > $rango)";
        $delete = $this -> b -> eliminarBackupUser($where);
        if ($delete) {
            $arreglo["error"] = false;
            $arreglo["titulo"] = "¡ BACKUPS ELIMINADOS !";
            $arreglo["msj"] = "Los bakups han sido eliminados satisfactoriamente";
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ BACKUPS NO ELIMINADOS !";
            $arreglo["msj"] = "Ocurrio un error al intentar eliminar algunos o todos los backups";
        }
        return $arreglo;
    }
}