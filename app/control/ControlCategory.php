<?php
/**
 * Created by PhpStorm.
 * User: pc-01
 * Date: 21/08/2019
 * Time: 14:05
 */
require_once (APP_PATH."model/Category.php");
class ControlCategory extends Valida
{
    private $c;
    public function __construct()
    {
        $this -> c = new Category();
    }
    public function buscarCategoriesBackup() {
        $idBackup = Form::getValue('idBack');
        $select = $this -> c -> mostrar("bc.id_backup = ba.id_backup AND bc.id_account = ba.id_account AND bc.id_backup = $idBackup", "bc.*, ba.name as account", "backup_categories bc, backup_accounts ba");
        $arreglo = array();
        if ($select) {
            $arreglo["error"] = false;
            $arreglo["categories"] = $select;
            $arreglo["titulo"] = "¡ CARDVIEWS ENCONTRADOS !";
            $arreglo["msj"] = "Se encontraron categories del respaldo solicitado.";
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ CARDVIEWS NO ENCONTRADOS !";
            $arreglo["msj"] = "No se encontraron categories del respaldo solicitado.";
        }
        return $arreglo;
    }

    public function inconsistenciaCategory() {
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
        $table = "backup_categories bc, users u, backups b";
        $where = "b.id_user = u.id_user AND b.id_backup = bc.id_backup ". $this -> condicionarConsulta("'$email'", "u.email", "'Generales'") ." GROUP BY ". $this -> namesColumns($this -> c -> nameColumns, "bc.") ." HAVING COUNT( * ) >= $this->having_Count limit 1, $this->limit";
        $arreglo["consultaSQL"] = $this -> consultaSQL($select, $table, $where);
        $consulta = $this -> c -> mostrar($where, $select, $table);
        if ($consulta) {
            $arreglo["error"] = false;
            $arreglo["categories"] = $consulta;
            $arreglo["titulo"] = "¡ INCONSISTENCIAS ENCONTRADOS !";
            $arreglo["msj"] = "Se encontraron duplicidades de registros en la tabla Category ". (($email != "Generales") ? "del usuario: $email" : "");
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ INCONSISTENCIAS NO ENCONTRADOS !";
            $arreglo["msj"] = "No se encontraron duplicidades de registros en la tabla Category ". (($email != "Generales") ? "del usuario: $email" : "");
        }
        return $arreglo;
    }
}