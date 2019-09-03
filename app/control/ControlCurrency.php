<?php
/**
 * Created by PhpStorm.
 * User: pc-01
 * Date: 21/08/2019
 * Time: 14:32
 */
require_once (APP_PATH."model/Currency.php");
class ControlCurrency extends Valida
{
    private $c;
    public function __construct()
    {
        $this -> c = new Currency();
    }
    public function buscarCurrenciesBackup() {
        $idBackup = Form::getValue('idBack');
        $select = $this -> c -> mostrar("id_backup = $idBackup");
        $arreglo = array();
        if ($select) {
            $arreglo["error"] = false;
            $arreglo["currencies"] = $select;
            $arreglo["titulo"] = "¡ CURRENCIES ENCONTRADOS !";
            $arreglo["msj"] = "Se encontraron currencies del respaldo solicitado.";
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ CURRENCIES NO ENCONTRADOS !";
            $arreglo["msj"] = "No se encontraron currencies del respaldo solicitado.";
        }
        return $arreglo;
    }

    public function inconsistenciaCurrency(){
        $email = Form::getValue('email');
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
        $select = "bc.*, COUNT(bc.id_backup) cantidadRepetida";
        $table = "backup_currencies bc, users u, backups b";
        $where = "b.id_user = u.id_user AND b.id_backup = bc.id_backup ". $this -> condicionarConsulta("'$email'", "u.email", "'Generales'") ." GROUP BY ". $this -> namesColumns($this -> c -> nameColumns, "bc.") ." HAVING COUNT( * ) >= $this->having_Count limit 1, $this->limit";
        $arreglo["consultaSQL"] = $this -> consultaSQL($select, $table, $where);
        $consulta = $this -> c -> mostrar($where, $select, $table);
        if ($consulta) {
            $arreglo["error"] = false;
            $arreglo["currencies"] = $consulta;
            $arreglo["titulo"] = "¡ INCONSISTENCIAS ENCONTRADOS !";
            $arreglo["msj"] = "Se encontraron duplicidades de registros en la tabla Currency ". (($email != "Generales") ? "del usuario: $email" : "");
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ INCONSISTENCIAS NO ENCONTRADOS !";
            $arreglo["msj"] = "No se encontraron duplicidades de registros en la tabla Currency ". (($email != "Generales") ? "del usuario: $email" : "");
        }
        return $arreglo;
    }
    public function corregirInconsitencia() {
        $sql = $this -> senetenciaInconsistenicaSQL($this -> c -> nameTable, $this -> c -> nameColumns, "id_backup");
        $operacion = $this -> c -> ejecutarMultSentMySQLi($sql);
        $arreglo = array(
            "SenteciasSQL" => $sql,
            "Result" => $operacion
        );
        return $arreglo;
    }
}