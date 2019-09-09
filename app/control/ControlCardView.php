<?php
/**
 * Created by PhpStorm.
 * User: pc-01
 * Date: 21/08/2019
 * Time: 12:47
 */
require_once (APP_PATH."model/CardView.php");
class ControlCardView extends Valida
{
    private $cv;
    private $pagina = 0;
    public function __construct()
    {
        $this -> cv = new CardView();
    }

    public function buscarCardviewsBackup() {
        $idBackup = Form::getValue('idBack');
        $select = $this -> cv -> mostrar("id_backup = $idBackup");
        $arreglo = array();
        if ($select) {
            $arreglo["error"] = false;
            $arreglo["cardviews"] = $select;
            $arreglo["titulo"] = "¡ CARDVIEWS ENCONTRADOS !";
            $arreglo["msj"] = "Se encontraron cardviews del respaldo solicitado.";
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ CARDVIEWS NO ENCONTRADOS !";
            $arreglo["msj"] = "No se encontraron cardviews del respaldo solicitado.";
        }
        return $arreglo;
    }
    public function inconsistenciaCardView() {
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
        $select = "cv.*, COUNT(cv.id_backup) cantidadRepetida";
        $table = "backup_cardviews cv, users u, backups b";
        $where = "b.id_user = u.id_user AND b.id_backup = cv.id_backup ". $this -> condicionarConsulta("'$email'", "u.email", "'Generales'") ." GROUP BY ". $this -> namesColumns($this -> cv -> nameColumns, "cv.") ." HAVING COUNT( * ) >= $this->having_Count limit $this->pagina, $this->limit_Inconsistencia";
        $arreglo["consultaSQL"] = $this -> consultaSQL($select, $table, $where);
        $consulta = $this -> cv -> mostrar($where, $select, $table);
        if ($consulta) {
            $arreglo["error"] = false;
            $arreglo["cardviews"] = $consulta;
            $arreglo["titulo"] = "¡ INCONSISTENCIAS ENCONTRADOS !";
            $arreglo["msj"] = "Se encontraron duplicidades de registros en la tabla CardView ". (($email != "Generales") ? "del usuario: $email" : "");
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ INCONSISTENCIAS NO ENCONTRADOS !";
            $arreglo["msj"] = "No se encontraron duplicidades de registros en la tabla CardView ". (($email != "Generales") ? "del usuario: $email" : "");
        }
        return $arreglo;
    }
    public function corregirInconsitencia() {
        $sql = $this -> senetenciaInconsistenicaSQL($this -> cv -> nameTable, ['id_backup','id_card'], "id_backup");
        $operacion = $this -> cv -> ejecutarMultSentMySQLi($sql);
        $arreglo = array(
            "SenteciasSQL" => $sql,
            "Result" => $operacion
        );
        return $arreglo;
    }
}