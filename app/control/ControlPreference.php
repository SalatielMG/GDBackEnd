<?php
/**
 * Created by PhpStorm.
 * User: pc-01
 * Date: 21/08/2019
 * Time: 14:47
 */
require_once (APP_PATH."model/Preference.php");

class ControlPreference extends Valida
{
    private $p;
    private $pagina = 0;
    public function __construct()
    {
        $this -> p = new Preference();
    }
    public function buscarPreferencesBackup() {
        $idBackup = Form::getValue('idBack');
        $select = $this -> p -> mostrar("id_backup = $idBackup");
        $arreglo = array();
        if ($select) {
            $arreglo["error"] = false;
            $arreglo["preferences"] = $select;
            $arreglo["titulo"] = "¡ PREFERNCES ENCONTRADOS !";
            $arreglo["msj"] = "Se encontraron preferences del respaldo solicitado.";
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ PREFERNCES NO ENCONTRADOS !";
            $arreglo["msj"] = "No se encontraron preferences del respaldo solicitado.";
        }
        return $arreglo;
    }
    public function inconsistenciaPreference(){
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
        $select = "bp.*, COUNT(bp.id_backup) cantidadRepetida";
        $table = "backup_preferences bp, users u, backups b";
        $where = "b.id_user = u.id_user AND b.id_backup = bp.id_backup ". $this -> condicionarConsulta("'$email'", "u.email", "'Generales'") ." GROUP BY ". $this -> namesColumns($this -> p -> nameColumns, "bp.") ." HAVING COUNT( * ) >= $this->having_Count limit $this->pagina, $this->limit_Inconsistencia";
        $arreglo["consultaSQL"] = $this -> consultaSQL($select, $table, $where);
        $consulta = $this -> p -> mostrar($where, $select, $table);
        if ($consulta) {
            $arreglo["error"] = false;
            $arreglo["preferences"] = $consulta;
            $arreglo["titulo"] = "¡ INCONSISTENCIAS ENCONTRADOS !";
            $arreglo["msj"] = "Se encontraron duplicidades de registros en la tabla Preference ". (($email != "Generales") ? "del usuario: $email" : "");
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ INCONSISTENCIAS NO ENCONTRADOS !";
            $arreglo["msj"] = "No se encontraron duplicidades de registros en la tabla Preference ". (($email != "Generales") ? "del usuario: $email" : "");
        }
        return $arreglo;
    }
    public function corregirInconsitencia() {
        $sql = $this -> sentenciaInconsistenicaSQL($this -> p -> nameTable, ['id_backup', 'key_name'], "id_backup");
        $operacion = $this -> p -> ejecutarMultSentMySQLi($sql);
        $arreglo = array(
            "SenteciasSQL" => $sql,
            "Result" => $operacion
        );
        return $arreglo;
    }
}