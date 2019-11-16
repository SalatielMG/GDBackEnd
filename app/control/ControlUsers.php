<?php
/**
 * Created by PhpStorm.
 * Users: pc-hp
 * Date: 16/08/2019
 * Time: 12:37 PM
 */
require_once(APP_PATH.'model/Users.php');
require_once(APP_PATH.'model/Movement.php');
require_once(APP_PATH.'model/Account.php');

class ControlUsers extends Valida
{
    private $u;
    private $m;
    private $a;

    public function __construct()
    {
        $this -> m = new Movement();
        $this -> u = new Users();
        $this -> a = new Account();
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
            $arreglo["titulo"] = "¡ No existen respaldos de este usuario !";
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
            $arreglo["titulo"] = "¡ No existen cuentas de este usuario!";
            $arreglo["msj"] = "¡ No se encontraron nigun registro de cuentas del usuario seleccionado para poder realizar la grafica de $mov !";
            return $arreglo;
        }
        array_unshift($accounts, array('id_account' => '0', 'name' => 'Todas'));
        $arreglo["accounts"] = $accounts;
        $arreglo["ultimaCuenta"] = $idAccount;

        $años = $this -> extraerAñosMovements($idUser, $idBackup, $idAccount);
        if (count($años) == 0) {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ No hay movimientos del respaldo seleccionado !";
            $arreglo["msj"] = "¡ No se encontraron nigun registro de movimientos del respaldo selecionado para poder realizar la grafica de $mov !";
            return $arreglo;
        }
        array_unshift($años, array('year' => '0'));
        $arreglo["años"] = $años;
        $arreglo["ultimoAño"] = $año;

        $meses = $this -> extraerMesesMovements($idUser, $idBackup, $idAccount, $año);
        if (count($meses) == 0) {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ No hay movimientos del respaldo seleccionado !";
            $arreglo["msj"] = "¡ No se encontraron nigun registro de movimientos del respaldo selecionado para poder realizar la grafica de $mov !";
            return $arreglo;
        }
        $arreglo["meses"] = $this -> asignarMeses($meses);
        $arreglo["ultimoMes"] = $mes;

        $where = "b.id_backup = bm.id_backup AND bm.id_backup = $idBackup "  . $this -> condicionarConsulta($idAccount, 'bm.id_account') . " " . $this -> condicionarConsulta($año, 'bm.year') . " " . $this -> condicionarConsulta($mes, 'bm.month') . " AND b.id_user = $idUser AND bm.sign = '$tipo' GROUP BY " . $this -> namesColumns($this -> m -> columnsTableIndexUnique, "bm.");

        $select = "cat.id_category, (SELECT nameCategory($idBackup, cat.id_category)) as nameCategory, (SELECT symbolCurrency(cat.id_backup, '', cat.id_account)) as symbol, sum(cat.amount) AS total";
        $table = "(SELECT bm.id_backup, bm.id_account, bm.id_category, bm.amount FROM backup_movements bm, backups b WHERE $where) as cat";
        $where = "1 GROUP BY cat.id_category ORDER BY total DESC";
        $arreglo["consultaSQL"] = $this -> consultaSQL($select, $table, $where);
        $select = $this -> u -> mostrar($where, $select, $table);
        $arreglo["select"] = $select;
        if ($select) {
            $arreglo["error"] = false;
            $categoria = $this -> extraerDatos($select);
            // $arreglo["arreglo"] = $select;
            $arreglo["categoria"] = $categoria;
            $arreglo["labels"] = $categoria["namesCategories"];
            $arreglo["values"] = $categoria["total"];
            $arreglo["symbols"] = $categoria["symbol"];
            $arreglo["totales"] = $categoria["totales"];
            $arreglo["titulo"] = "¡ Movimientos encontrados !";
            $arreglo["msj"] = "Se encontraron movimientos de tipo $mov del usuario solicitaddo";
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ Movimientos no encontrados !";
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
        $data = array();
        $total = array();
        foreach ($arreglo as $key => $value) {
            $data["namesCategories"][$key] = $value -> nameCategory;
            $data["total"][$key] = $value -> total;
            $data["symbol"][$key] = $value -> symbol;
            if (count($total) > 0) {
                foreach ($total as $k  => $v) {
                    //var_dump($v);
                    if ($v["symbol"] == $value -> symbol) {
                        $total[$k]["total"] += $value -> total;
                    } else {
                        array_push($total, array(
                            "symbol" => $value -> symbol,
                            "total" => $value -> total));
                    }
                }
            } else {
                array_push($total, array(
                    "symbol" => $value -> symbol,
                    "total" => $value -> total));
            }

        }
        $data["totales"] = $total;
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
            $arreglo["titulo"] = "¡ No existen respaldos de este uusario !";
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
            $arreglo["titulo"] = "¡ No hay movimientos del respaldo seleccionado !";
            $arreglo["msj"] = "¡ No se encontraron nigun registro de movimientos del respaldo selecionado para poder realizar la grafica !";
            return $arreglo;
        }
        array_unshift($años, array('year' => '0'));
        $arreglo["años"] = $años;
        $arreglo["ultimoAño"] = $año;


        $where = "b.id_backup = bm.id_backup AND bm.id_backup = $idBackup " . $this -> condicionarConsulta($año, "bm.year") . " AND bm.sign = '-' and b.id_user = $idUser GROUP BY " . $this -> namesColumns($this -> m -> columnsTableIndexUnique, "bm.");
        $select = "tempTable.month, SUM(tempTable.amount) Total, (SELECT symbolCurrency(tempTable.id_backup, '', tempTable.id_account)) as symbol";
        $table = "(SELECT bm.id_backup, bm.id_account, bm.month, bm.amount FROM backup_movements bm, backups b WHERE $where) as tempTable";
        $where = "1 GROUP BY tempTable.month ORDER BY tempTable.month";
        $gastos = $this -> u -> mostrar($where, $select, $table);
        $arrreglo["consultaSQLGastos"] = $this -> consultaSQL($select, $table, $where);

        $where = "b.id_backup = bm.id_backup AND bm.id_backup = $idBackup " . $this -> condicionarConsulta($año, "bm.year") . " AND bm.sign = '+' and b.id_user = $idUser GROUP BY " . $this -> namesColumns($this -> m -> columnsTableIndexUnique, "bm.");
        $select = "tempTable.month, SUM(tempTable.amount) Total, (SELECT symbolCurrency(tempTable.id_backup, '', tempTable.id_account)) as symbol";
        $table = "(SELECT bm.id_backup, bm.id_account, bm.month, bm.amount FROM backup_movements bm, backups b WHERE $where) as tempTable";
        $where = "1 GROUP BY tempTable.month ORDER BY tempTable.month";
        $ingresos = $this -> u -> mostrar($where, $select, $table);
        $arrreglo["consultaSQLIngresos"] = $this -> consultaSQL($select, $table, $where);


        if ($gastos || $ingresos) {
            $dataGastos = $this -> asignarMontoMeses($gastos);
            $dataIngresos = $this -> asignarMontoMeses($ingresos);

            $arreglo["Gastos"] = array_values($dataGastos);
            $arreglo["Ingresos"] = array_values($dataIngresos);



            $arreglo["TotalGastos"] = $this -> sumaTotales($arreglo["Gastos"]);
            $arreglo["TotalIngresos"] = $this -> sumaTotales($arreglo["Ingresos"]);
            $arreglo["TotalAnhoLabel"] = array(0 => (($año == 0) ? 'Todos' : $año));
            //var_dump($gastos, $ingresos);
            $arreglo["SymbolsTotales"] =
                [
                    "gastos" => ((count($gastos) > 0) ? $gastos[0] -> symbol : ""),
                    "ingresos" => ((count($ingresos) > 0) ? $ingresos[0] -> symbol : ""),
                    "diffe" => ((count($gastos) > 0) ? $gastos[0] -> symbol : ""),
                ];
            $arreglo["error"] = false;
            $arreglo["titulo"] = "¡ Movimientos encontrados !";
            $arreglo["msj"] = "¡ Movimientos encontrados del año seleccionado !";
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ No hay ningun movimientos !";
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
            $arreglo["titulo"] = "¡ Error de validación !";
            $arreglo["msj"] = $form -> errores;
            return $arreglo;
        }

        $usuario = $this -> u -> mostrar("email = '$email'", "*" );

        if (count($usuario) > 0) {
            $usuario = $usuario[0];
            $arreglo["error"] = false;
            $arreglo["titulo"] = "¡ Usuario encontrado !";
            $arreglo["msj"] = "Usuario localizado en la BD";
            $arreglo["user"] = $usuario;
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ Usuario no encontrado !";
            $arreglo["msj"] = "No se encontro ningun usuario registrado con el correo: '$email'";
        }
        return $arreglo;
    }
}