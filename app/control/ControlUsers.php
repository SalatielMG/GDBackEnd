<?php
/**
 * Created by PhpStorm.
 * Users: pc-hp
 * Date: 16/08/2019
 * Time: 12:37 PM
 */
require_once(APP_PATH.'model/Users.php');

class ControlUsers extends Valida
{
    private $u;
    public function __construct()
    {
        $this -> u = new Users();
    }

    /**********************************************************************************************/
    public function ValoresGraficaBackupsCategoriasGastos() {
        $idUser = Form::getValue('idUser');
        $tipo = Form::getValue('tipo');
            $mov = ($tipo == 'neg') ? "gastos": "entradas";
            $tipo = ($tipo == 'neg') ? "-": "+";
        $idBackup = Form::getValue("idBackup");
        $idAccount = Form::getValue("idCuenta");
        $año = Form::getValue("año");
        $mes = Form::getValue("mes");
        $arreglo = array();

        $backups = $this -> extraerBackupsMovements($idUser); //Todos los id de los Backup del usuario
        if (count($backups) == 0) {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ NO HAY RESPALDOS DE ESTE USUARIO !";
            $arreglo["msj"] = "¡ No se encontraron nigun registro de respaldos del usuario seleccionado para poder realizar la grafica de $mov !";
            return $arreglo;
        }
        $arreglo["backups"] = $backups;
        if ($idBackup == "0") {
            $idBackup = $backups[0] -> id_backup;
        }
        $arreglo["ultimoBackup"] = $idBackup;

        $accounts = $this -> extraerCuentasUser($idUser, $idBackup);
        if (count($accounts) == 0) {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ NO HAY CUENTAS DE ESTE USUARIO !";
            $arreglo["msj"] = "¡ No se encontraron nigun registro de cuentas del usuario seleccionado para poder realizar la grafica de $mov !";
            return $arreglo;
        }
        array_unshift($accounts, array('id_account' => '0', 'name' => 'Todas'));
        $arreglo["accounts"] = $accounts;
        $arreglo["ultimaCuenta"] = $idAccount;

        $años = $this -> extraerAñosMovements($idUser, $idBackup, $idAccount);
        if (count($años) == 0) {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ NO HAY MOVIMIENTOS DEL RESPALDO SELECCIONADO  !";
            $arreglo["msj"] = "¡ No se encontraron nigun registro de movimientos del respaldo selecionado para poder realizar la grafica de $mov !";
            return $arreglo;
        }
        array_unshift($años, array('year' => '0'));
        $arreglo["años"] = $años;
        $arreglo["ultimoAño"] = $año;

        $meses = $this -> extraerMesesMovements($idUser, $idBackup, $idAccount, $año);
        if (count($meses) == 0) {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ NO HAY MOVIMIENTOS DEL RESPALDO SELECCIONADO  !";
            $arreglo["msj"] = "¡ No se encontraron nigun registro de movimientos del respaldo selecionado para poder realizar la grafica de $mov !";
            return $arreglo;
        }
        $arreglo["meses"] = $this -> asignarMeses($meses);
        $arreglo["ultimoMes"] = $mes;

        $where = "b.id_backup = bm.id_backup AND bm.id_backup = $idBackup "  . $this -> condicionarConsulta($idAccount, 'bm.id_account') . " " . $this -> condicionarConsulta($año, 'bm.year') . " " . $this -> condicionarConsulta($mes, 'bm.month') . " AND b.id_user = $idUser AND bm.sign = '$tipo' group BY bm.id_category ORDER BY total DESC";
        // return $where;
        $select = $this -> u -> mostrar($where, "bm.id_category, sum(bm.amount) AS total", "backup_movements bm, backups b");
        $arreglo["where"] = $where;
        if ($select) {
            $arreglo["error"] = false;
            $categoria = $this -> extraerDatos($select);
            // $arreglo["arreglo"] = $select;
            $arreglo["categoria"] = $categoria;
            $arreglo["labels"] = array_keys($categoria);
            $arreglo["values"] = array_values($categoria);
            $arreglo["titulo"] = "¡ MOVIMIENTOS ECONTRADOS !";
            $arreglo["msj"] = "Se encontraron movimientos de tipo $mov del usuario solicitaddo";
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ MOVIMIENTOS NO ECONTRADOS !";
            $arreglo["msj"] = "No se encontro ningun movimiento de tipo $mov del usuario solicitado";
        }
        return $arreglo;
    }
    private function asignarMeses($meses) {
        $arreglo = array();
        foreach ($meses as $key => $value) {
            switch ($value -> month){
                case "1":
                    $arreglo[$key]["monthNumber"] = $value -> month;
                    $arreglo[$key]["monthName"] = "Enero";
                    break;
                case "2":
                    $arreglo[$key]["monthNumber"] = $value -> month;
                    $arreglo[$key]["monthName"] = "Febrero";
                    break;
                case "3":
                    $arreglo[$key]["monthNumber"] = $value -> month;
                    $arreglo[$key]["monthName"] = "Marzo";
                    break;
                case "4":
                    $arreglo[$key]["monthNumber"] = $value -> month;
                    $arreglo[$key]["monthName"] = "Abril";
                    break;
                case "5":
                    $arreglo[$key]["monthNumber"] = $value -> month;
                    $arreglo[$key]["monthName"] = "Mayo";
                    break;
                case "6":
                    $arreglo[$key]["monthNumber"] = $value -> month;
                    $arreglo[$key]["monthName"] = "Junio";
                    break;
                case "7":
                    $arreglo[$key]["monthNumber"] = $value -> month;
                    $arreglo[$key]["monthName"] = "Julio";
                    break;
                case "8":
                    $arreglo[$key]["monthNumber"] = $value -> month;
                    $arreglo[$key]["monthName"] = "Agosto";
                    break;
                case "9":
                    $arreglo[$key]["monthNumber"] = $value -> month;
                    $arreglo[$key]["monthName"] = "Septiembre";
                    break;
                case "10":
                    $arreglo[$key]["monthNumber"] = $value -> month;
                    $arreglo[$key]["monthName"] = "Octubre";
                    break;
                case "11":
                    $arreglo[$key]["monthNumber"] = $value -> month;
                    $arreglo[$key]["monthName"] = "Noviembre";
                    break;
                case "12":
                    $arreglo[$key]["monthNumber"] = $value -> month;
                    $arreglo[$key]["monthName"] = "Diciembre";
                    break;
            }
        }
        array_unshift($arreglo, array('monthNumber' => '0', 'monthName' => 'Todos'));
        return $arreglo;
    }
    private function extraerDatos($arreglo) {
        /*
         * Los primeros 15.
         * */
        $data = array();
        foreach ($arreglo as $key => $value) {
            $data[$value -> id_category] = $value -> total;
        }
        /*if (count($arreglo) > 30) {
            $sumaOtro = 0;
            foreach ($arreglo as $key => $value) {
                if ($key > 29) {
                    $sumaOtro = $sumaOtro + $value -> total;
                } else {
                    $data[$value -> id_category] = $value -> total;
                }
            }
            $data["Otros"] = $sumaOtro;
        } else {
            foreach ($arreglo as $key => $value) {
                $data[$value -> id_category] = $value -> total;
            }
        }*/
        return $data;

    }
    /**********************************************************************************************/

    public function ValoresGrficaGastosvsIngresos() {
        $idUser = Form::getValue("idUser");
        $idBackup = Form::getValue("idBackup");
        $año = Form::getValue("año");
        $arreglo = array();
        $backups = $this -> extraerBackupsMovements($idUser); //Todos los id de los Backup del usuario
        if (count($backups)== 0) {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ NO HAY RESPALDOS DE ESTE USUARIO !";
            $arreglo["msj"] = "¡ No se encontraron nigun registro de respaldos del usuario seleccionado para poder realizar la grafica !";
            return $arreglo;
        }
        $arreglo["backups"] = $backups;
        $arreglo["ultimoBackup"] = $idBackup;
        if ($idBackup == "0") {
            $idBackup = $backups[0] -> id_backup;
            $arreglo["ultimoBackup"] = $backups[0] -> id_backup;
        }


        $años = $this -> extraerAñosMovements($idUser, $idBackup);
        if (count($años) == 0) {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ NO HAY MOVIMIENTOS DEL RESPALDO SELECCIONADO  !";
            $arreglo["msj"] = "¡ No se encontraron nigun registro de movimientos del respaldo selecionado para poder realizar la grafica !";
            return $arreglo;
        }
        array_unshift($años, array('year' => '0'));
        $arreglo["años"] = $años;
        $arreglo["ultimoAño"] = $año;


        /*$años = $this -> extraerAñosMovements($idUser, $idBackup);
        if (count($años) == 0) {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ NO HAY MOVIMIENTOS DEL RESPALDO SELECCIONADO  !";
            $arreglo["msj"] = "¡ No se encontraron nigun registro de movimientos del respaldo selecionado para poder realizar al grafica. !";
            return $arreglo;
        }
        $arreglo["años"] = $años;
        $arreglo["ultimoAño"] = $año;
        if ($año == "0") {
            $año = $años[0] -> year;
            $arreglo["ultimoAño"] = $años[0] -> year;
        }*/

        $gastos = $this -> u -> mostrar("b.id_backup = bm.id_backup AND bm.id_backup = $idBackup " . $this -> condicionarConsulta($año, "bm.year") . " AND bm.sign = '-' and b.id_user = $idUser GROUP BY bm.month ORDER BY bm.month ", "bm.month, SUM(bm.amount) Total", "backup_movements bm, backups b");
        $ingresos = $this -> u -> mostrar("b.id_backup = bm.id_backup AND bm.id_backup = $idBackup " . $this -> condicionarConsulta($año, "bm.year") . "  AND bm.sign = '+' and b.id_user = $idUser GROUP BY bm.month ORDER BY bm.month ", "bm.month, SUM(bm.amount) Total", "backup_movements bm, backups b");
        if ($gastos || $ingresos) {
            $dataGastos = $this -> asignarMontoMeses($gastos);
            $dataIngresos = $this -> asignarMontoMeses($ingresos);

            $arreglo["Gastos"] = array_values($dataGastos);
            $arreglo["Ingresos"] = array_values($dataIngresos);

            $arreglo["TotalGastos"] = $this -> sumaTotales($arreglo["Gastos"]);
            $arreglo["TotalIngresos"] = $this -> sumaTotales($arreglo["Ingresos"]);
            $arreglo["TotalAñoLabel"] = array(0 => $año);


            $arreglo["error"] = false;
            $arreglo["titulo"] = "¡ MOVIMIENTOS ENCONTRADOS !";
            $arreglo["msj"] = "¡ Movimientos encontrados del año seleccionado !";
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ NO HAY NINGUN MOVIMIENTOS !";
            $arreglo["msj"] = "¡ No se encontraron nigun registro de movimientos del año seleccionado para poder realizar al grafica. !";
        }
        return $arreglo;
    }
    private function sumaTotales($arreglo) {
        $suma = 0;
        foreach ($arreglo as $value) {
            $suma = $suma + $value;
        }
        return $data = array(0 => $suma);
    }
    private function asignarMontoMeses($arreglo) {
        $data = array(1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0, 7 => 0, 8 => 0, 9 => 0, 10 => 0, 11 => 0, 12 => 0);
        foreach ($data as $key => $value) {
            foreach ($arreglo as $k => $v) {
                if ($v -> month == $key) {
                    $data[$key] = $v -> Total;
                }
            }
        }
        return $data;
    }
    private function extraerBackupsMovements($idUser) {
        return $this -> u -> mostrar("b.id_user = $idUser order by b.id_backup desc", "b.id_backup", "backups b");
    }
    private function extraerAñosMovements($idUSer, $id_backup, $idAccount = "0") {
        return $select = $this -> u -> mostrar("b.id_backup = bm.id_backup and bm.id_backup = $id_backup " . $this -> condicionarConsulta($idAccount, 'bm.id_account') . " and b.id_user = $idUSer GROUP BY bm.year ORDER BY bm.year desc", "bm.year", "backup_movements bm, backups b");
    }
    private function extraerMesesMovements($idUSer, $id_backup, $idAccount = "0", $año = "0") {
        return $select = $this -> u -> mostrar("b.id_backup = bm.id_backup and bm.id_backup = $id_backup " . $this -> condicionarConsulta($idAccount, 'bm.id_account') . " " . $this -> condicionarConsulta($año, 'bm.year') . " and b.id_user = $idUSer GROUP BY bm.month ORDER BY bm.month desc", "bm.month", "backup_movements bm, backups b");
    }
    private function extraerCuentasMovements($idUSer, $id_backup) {
        return $select = $this -> u -> mostrar("b.id_backup = bm.id_backup and bm.id_backup = $id_backup and b.id_user = $idUSer GROUP BY bm.id_account ORDER BY bm.id_account desc", "bm.id_account", "backup_movements bm, backups b");
    }
    private function  extraerCuentasUser($idUSer, $id_backup) {
        return $select = $this -> u -> mostrar("b.id_backup = ba.id_backup and ba.id_backup = $id_backup and b.id_user = $idUSer GROUP BY ba.id_account ORDER BY ba.id_account desc", "ba.id_account, ba.name", "backup_accounts ba, backups b");
    }
    /**********************************************************************************************/

    public function buscarUser() {
        $email = Form::getValue('email');
        // return compact("email");
        $form = new Form();
        $form -> validarDatos($email, 'Correo electronico', 'required');

        $arreglo = array();

        if (count($form -> errores) > 0) {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ ERROR DE VALIDACIÓN !";
            $arreglo["msj"] = $form -> errores;
            return $arreglo;
        }

        $usuario = $this -> u -> mostrar("email = '$email'", "*" );

        if (count($usuario) > 0) {
            $usuario = $usuario[0];
            $arreglo["error"] = false;
            $arreglo["titulo"] = "¡ USUARIO ENCONTRADO !";
            $arreglo["msj"] = "Usuario localizado en la BD";
            $arreglo["user"] = $usuario;
            /*$backups = $this -> u -> mostrar("b.id_user = u.id_user and b.id_user = $usuario->id_user", "*", "backups as b, users as u");
            if (count($backups) > 0) {
                $arreglo["backups"]["error"] = false;
                $arreglo["backups"]["respaldos"] = $backups;
                $arreglo["backups"]["titulo"] = "¡ BACKUPS ENCONTRADOS !";
                $arreglo["backups"]["msj"] = "Se localizaron Backups realizados por el usuario: $email";
            } else {
                $arreglo["backups"]["error"] = true;
                $arreglo["backups"]["titulo"] = "¡ BACKUPS NO ENCONTRADOS !";
                $arreglo["backups"]["msj"] = "No se localizaron Backups realizados por el usuario: $email";
            }*/
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ USUARIO NO ENCONTRADO !";
            $arreglo["msj"] = "No se encontro ningun usuario registrado con el correo: '$email' proporcionado";
        }
        return $arreglo;
    }
}