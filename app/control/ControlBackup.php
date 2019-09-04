<?php
/**
 * Created by PhpStorm.
 * User: pc-hp
 * Date: 17/08/2019
 * Time: 12:38 PM
 */
require_once(APP_PATH.'model/Backup.php');

class ControlBackup
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
        $arreglo = array();
        $paginacion = $this -> construirPaginacion($idUser);
        if ($paginacion["error"]){
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ BACKUPS NO ENCONTRADOS !";
            $arreglo["msj"] = "No se encontraron backups del usuario solicitado.";
            return $arreglo;
        }
        $arreglo["paginacion"] = $paginacion;
        $select = $this -> b -> mostrar("id_user = $idUser");
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

        if ($email != "Generales") {
            $form = new Form();
            $form -> validarDatos($email, 'Correo electronico', 'email');
            $form -> validarDatos($cantidad, 'Cantidad de backups', 'required|enterosPositivos');
            if (count($form -> errores) > 0) {
                $arreglo["error"] = true;
                $arreglo["titulo"] = "¡ ERROR DE VALIDACIÓN !";
                $arreglo["msj"] = $form -> errores;
                return $arreglo;
            }
        } else {
            $form = new Form();
            $form -> validarDatos($cantidad, 'Cantidad de backups', 'required|enterosPositivos');
            if (count($form -> errores) > 0) {
                $arreglo["error"] = true;
                $arreglo["titulo"] = "¡ ERROR DE VALIDACIÓN !";
                $arreglo["msj"] = $form -> errores;
                return $arreglo;
            }
        }



        //$consulta = $this -> ;

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
}