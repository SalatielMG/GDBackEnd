<?php
/**
 * Created by PhpStorm.
 * User: pc-01
 * Date: 21/08/2019
 * Time: 12:25
 */
require_once (APP_PATH."model/Automatic.php");
class ControlAutomatic extends Valida
{
    private $a;
    public function __construct()
    {
        $this -> a = new Automatic();
    }

    public function buscarAutomaticsBackup() {
        $idBackup = Form::getValue('idBack');
        $select = $this -> a -> mostrar("ba.id_backup = bc.id_backup AND ba.id_backup = $idBackup",
            "ba.*, bc.symbol, (SELECT nombreCuenta($idBackup, ba.id_account)) AS ACCOUNT, (SELECT nombreCategoria($idBackup, ba.id_category)) as category ",
            "backup_automatics ba, backup_currencies bc");
        /*$select = $this -> a -> mostrar("1 ORDER BY CC.initial_date",
            "CC.*",
            "(SELECT ba.*, bc.symbol, bac.name as account, bcat.name as category FROM backup_automatics ba, backup_currencies bc, backup_accounts bac, backup_categories bcat WHERE ba.id_backup = bc.id_backup AND ba.id_backup = bac.id_backup AND ba.id_account = bac.id_account AND ba.id_backup = bcat.id_backup AND ba.id_category = bcat.id_category AND ba.id_backup = $idBackup
            UNION
            SELECT ba.*, bc.symbol, bac.name as account, '' as category FROM backup_automatics ba, backup_currencies bc, backup_accounts bac WHERE ba.id_backup = bc.id_backup AND ba.id_backup = bac.id_backup AND ba.id_account = bac.id_account AND ba.id_category >= 10000 AND ba.id_backup = $idBackup) as CC");
        */
        // $selectMiddle = "SELECT ba.*, bc.symbol, bac.name as account, bcat.name as category FROM backup_automatics ba, backup_currencies bc, backup_accounts bac, backup_categories bcat WHERE ba.id_backup = bc.id_backup AND ba.id_backup = bac.id_backup AND ba.id_account = bac.id_account AND ba.id_backup = bcat.id_backup AND ba.id_category = bcat.id_category AND ba.id_backup = $idBackup";
        // $selectFull = "SELECT ba.*, bc.symbol, bac.name as account, '' as category FROM backup_automatics ba, backup_currencies bc, backup_accounts bac WHERE ba.id_backup = bc.id_backup AND ba.id_backup = bac.id_backup AND ba.id_account = bac.id_account AND ba.id_category >= 10000 AND ba.id_backup = $idBackup";
        // $sqlDone = "SELECT ba.*, bc.symbol, (SELECT nombreCuenta(179, ba.id_account)) AS ACCOUNT, (SELECT nombreCategoria(179, ba.id_category)) as category FROM backup_automatics ba, backup_currencies bc WHERE ba.id_backup = bc.id_backup AND ba.id_backup = 179"
        $arreglo = array();
        if ($select) {
            $arreglo["error"] = false;
            $arreglo["automatics"] = $select;
            $arreglo["titulo"] = "¡ AUTOMATICS ENCONTRADOS !";
            $arreglo["msj"] = "Se encontraron automatics del respaldo solicitado.";
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ AUTOMATICS NO ENCONTRADOS !";
            $arreglo["msj"] = "No se encontraron automatics del respaldo solicitado.";
        }
        return $arreglo;
    }

    public function inconsistenciaAutomatics() {
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
        $select = "ba.*, COUNT(ba.id_backup) cantidadRepetida";
        $table = "backup_automatics ba, users u, backups b";
        $where = "b.id_user = u.id_user AND b.id_backup = ba.id_backup ". $this -> condicionarConsulta("'$email'", "u.email", "'Generales'") ." GROUP BY ". $this -> namesColumns($this -> a -> nameColumns, "ba.") ." HAVING COUNT( * ) >= $this->having_Count limit 1, $this->limit";
        $arreglo["consultaSQL"] = $this -> consultaSQL($select, $table, $where);
        $consulta = $this -> a -> mostrar($where, $select, $table);
        if ($consulta) {
            $arreglo["error"] = false;
            $arreglo["automatics"] = $consulta;
            $arreglo["titulo"] = "¡ INCONSISTENCIAS ENCONTRADOS !";
            $arreglo["msj"] = "Se encontraron duplicidades de registros en la tabla Automatics ". (($email != "Generales") ? "del usuario: $email" : "");
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ INCONSISTENCIAS NO ENCONTRADOS !";
            $arreglo["msj"] = "No se encontraron duplicidades de registros en la tabla Automatics ". (($email != "Generales") ? "del usuario: $email" : "");
        }
        return $arreglo;
    }

}