<?php
/**
 * Created by PhpStorm.
 * User: pc-01
 * Date: 21/08/2019
 * Time: 14:39
 */
require_once (APP_PATH."model/Extra.php");

class ControlExtra extends Valida
{
    private $e;
    private $pagina = 0;
    public function __construct()
    {
        $this -> e = new Extra();
    }
    public function buscarExtrasBackup() {
        $idBackup = Form::getValue('idBack');
        $select = $this -> e -> mostrar("id_backup = $idBackup");
        $arreglo = array();
        if ($select) {
            $arreglo["error"] = false;
            $arreglo["extras"] = $select;
            $arreglo["titulo"] = "¡ EXTRAS ENCONTRADOS !";
            $arreglo["msj"] = "Se encontraron extras del respaldo solicitado.";
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ EXTRAS NO ENCONTRADOS !";
            $arreglo["msj"] = "No se encontraron extras del respaldo solicitado.";
        }
        return $arreglo;
    }
    public function inconsistenciaExtra(){
        $email = Form::getValue('email');
        $this -> pagina = Form::getValue('pagina');
        $arreglo = array();
        if ($email != "Generales") {
            $form = new Form();
            $form -> validarDatos($email, 'Correo electronico', 'email');
            if (count($form -> errores) > 0) {
                $arreglo["error"] = true;
                $arreglo["titulo"] = "¡ ERROR DE VALIDACIÓN !";
                $arreglo["msj"] = $form -> errores;
                return $arreglo;
            }
        }
        $this -> pagina = $this -> pagina * $this -> limit_Inconsistencia;
        $select = "be.*, COUNT(be.id_backup) cantidadRepetida";
        $table = "backup_extras be, users u, backups b";
        $where = "b.id_user = u.id_user AND b.id_backup = be.id_backup ". $this -> condicionarConsulta("'$email'", "u.email", "'Generales'") ." GROUP BY ". $this -> namesColumns($this -> e -> nameColumns, "be.") ." HAVING COUNT( * ) >= $this->having_Count limit $this->pagina, $this->limit_Inconsistencia";
        $arreglo["consultaSQL"] = $this -> consultaSQL($select, $table, $where);
        $consulta = $this -> e -> mostrar($where, $select, $table);
        if ($consulta) {
            $arreglo["error"] = false;
            $arreglo["extras"] = $consulta;
            $arreglo["titulo"] = "¡ INCONSISTENCIAS ENCONTRADOS !";
            $arreglo["msj"] = "Se encontraron duplicidades de registros en la tabla Extra ". (($email != "Generales") ? "del usuario: $email" : "");
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ INCONSISTENCIAS NO ENCONTRADOS !";
            $arreglo["msj"] = "No se encontraron duplicidades de registros en la tabla Extra ". (($email != "Generales") ? "del usuario: $email" : "");
        }
        return $arreglo;
    }
    public function corregirInconsitencia() {
        $sql = $this -> senetenciaInconsistenicaSQL($this -> e -> nameTable, ['id_backup', 'id_extra'], "id_backup");
        $operacion = $this -> e -> ejecutarMultSentMySQLi($sql);
        $arreglo = array(
            "SenteciasSQL" => $sql,
            "Result" => $operacion
        );
        return $arreglo;
    }
}