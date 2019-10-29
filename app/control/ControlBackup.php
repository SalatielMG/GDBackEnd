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
    private $idUser;
    private $rango;
    private $cantidad;
    private $email;
    private $pagina = 0;
    private $where = "";
    private $select = "";
    private $table = "";

    public function __construct()
    {
        $this -> b = new Backup();
    }
    public function buscarBackupsUserEmail() {
        $this -> email = Form::getValue('email');
        $OrdeBy = Form::getValue('orderby');
        $this -> pagina = Form::getValue('pagina');

        $this -> pagina = $this -> pagina * $this -> limit;

        $arreglo = array();
        $arreglo["id_user"] = 0;
        $this -> where = "b.id_user = u.id_user ORDER BY id_backup $OrdeBy limit $this->pagina,$this->limit";
        $this -> select = "@rownum:=@rownum+1 AS pos, b.*, u.email";
        $this -> table = "backups b, users u, (SELECT @rownum:=$this->pagina) r";
        if ($this -> email != "Generales") {
            $form = new Form();
            $form -> validarDatos($this -> email, 'Correo electronico', 'email');
            if (count($form -> errores) > 0) {
                $arreglo["error"] = true;
                $arreglo["titulo"] = "¡ ERROR DE VALIDACIÓN !";
                $arreglo["msj"] = $form -> errores;
                return $arreglo;
            }
            $this -> where = "email = " . "'".$this -> email."'";
            $this -> select = "id_user, email";
            $this -> table = "users";
            $consultaUser = $this -> b -> mostrar($this -> where, $this -> select, $this -> table);
            if (!$consultaUser) {
                $arreglo["error"] = true;
                $arreglo["titulo"] = "¡ USUARIO NO ENCONTRADO !";
                $arreglo["msj"] = " El usuario solicitado no se encuentra registrado la base de datos ";
                return $arreglo;
            }
            $arreglo["id_user"] = $consultaUser[0] -> id_user;
            $this -> where = "b.id_user = " . $arreglo["id_user"] . "  ORDER BY id_backup $OrdeBy limit $this->pagina,$this->limit      ";
            $this -> select = "@rownum:=@rownum+1 AS pos, b.*";
            $this -> table = "backups b, (SELECT @rownum:=$this->pagina) r";
        }
        $arreglo["consultaBackups"] = $this -> consultaSQL($this -> select, $this -> table, $this -> where);
        $backups = $this -> b -> mostrar($this -> where, $this -> select, $this -> table);
        if ($backups) {
            $arreglo["error"] = false;
            if ($this -> pagina == 0) array_unshift($backups, array('id_backup' => '0'));

            $arreglo["backups"] = $backups;
            $arreglo["titulo"] = "¡ BACKUPS ENCONTRADOS !";
            $arreglo["msj"] = "Se encontraron backups ". (($this -> email == "Generales") ? "de todos los usuarios generales" : "del usuario: $this->email");
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ BACKUPS NO ENCONTRADOS !";
            $arreglo["msj"] = "No se encontraron backups ". (($this -> email == "Generales") ? "de todos los usuarios generales" : "del usuario: $this->email");
        }
        return $arreglo;
    }
    public function buscarBackupsUserId() {
        $idUser = Form::getValue('idUser');
        $OrdeBy = Form::getValue('orderby');
        $this -> pagina = Form::getValue('pagina');
        $arreglo = array();
        $this -> pagina = $this -> pagina * $this -> limit;
        $newLimit = "-$this->limit";
        $this -> where = "b.id_user = $idUser order by b.id_backup $OrdeBy " . $this -> condicionarLimit($this -> pagina, $newLimit);
        $this -> select = "@rownum:=@rownum+1 AS pos, b.*";
        $this -> table = "backups b, (SELECT @rownum:=" . (($this -> pagina == $newLimit) ?  0:  $this -> pagina) . ") r";
        $select = $this -> b -> mostrar($this -> where, $this -> select, $this -> table);
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
    public function buscarUsersExportacionBackups() {
        $this -> email = Form::getValue("email");
        $this -> pagina = Form::getValue('pagina');
        $arreglo = array();

        $form = new Form();
        if ($this -> email != "Generales") {
            $form -> validarDatos($this -> email, "Correo electronico", "email");
            if (count($form -> errores) > 0) {
                $arreglo["error"] = true;
                $arreglo["titulo"] = "¡ ERROR DE VALIDACIÓN !";
                $arreglo["msj"] = $form -> errores;
                return $arreglo;
            }
        }
        $arreglo = $this -> buscarUsersEB();
        return $arreglo;
    }
    private function buscarUsersEB() {
        $arreglo = array();
        if ($this -> email != "Generales") {
            $arreglo = $this -> verifyExistsUser();
            if ($arreglo["error"]) return $arreglo;
        }
        $this -> pagina = $this -> pagina * $this -> limit;
        $this -> select = "u.*, COUNT(b.id_user) as cantRep";
        $this -> table = "backups b, users u";
        $this -> where = "b.id_user = u.id_user ". $this -> condicionarConsulta("'".$this -> email."'", "u.email", "'Generales'")." GROUP BY b.id_user ORDER BY u.email limit $this->pagina,$this->limit";

        $arreglo["consulta"] = $this -> consultaSQL($this -> select, $this -> table, $this -> where);
        //return $arreglo;
        $consulta = $this -> b -> mostrar($this -> where, $this -> select, $this -> table);
        if ($consulta) {
            $arreglo["error"] = false;
            $arreglo["users"] = $consulta;
            $arreglo["titulo"] = ($this -> email == "Generales") ? "¡ USUARIOS ENCONTRADOS !" : "¡ USUARIO ENCONTRADO !";
            $arreglo["msj"] = "Se encontraron Usuario". (($this -> email == "Generales") ? "s: " : ": ") . $this->email;
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = ($this->email == "Generales") ? "¡ USUARIOS NO ENCONTRADOS !" : "¡ USUARIO NO ENCONTRADO !";
            $arreglo["msj"] = "No se encontraron Usuario". (($this -> email == "Generales") ? "s: " : ": ") . $this->email;
        }
        return $arreglo;
    }
    public function buscarBackupsUserCantidad() {
        $this -> email = Form::getValue('email');
        $this -> rango = Form::getValue('cantidad');
        $this -> pagina = Form::getValue('pagina');
        $arreglo = array();

        $form = new Form();
        $form -> validarDatos($this -> rango, 'Cantidad mínima de backups', 'required|enterosPositivos');

        if ($this -> email != "Generales") {
            $form -> validarDatos($this -> email, 'Correo electronico', 'email');
        }
        if (count($form -> errores) > 0) {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ ERROR DE VALIDACIÓN !";
            $arreglo["msj"] = $form -> errores;
            return $arreglo;
        }
        $arreglo = $this -> buscarBackupsUC();
        return $arreglo;
    }
    private function verifyExistsUser() {
        $arreglo = array();
        $where = "email = " . "'".$this -> email."'";
        $select = "id_user, email";
        $table = "users";
        $consultaUser = $this -> b -> mostrar($where, $select, $table);
        if (!$consultaUser) {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ USUARIO NO ENCONTRADO !";
            $arreglo["msj"] = " El usuario con email: $this->email no se encuentra registrado en la base de datos ";
            return $arreglo;
        } else {
            $arreglo["error"] = false;
        }
        return $arreglo;
    }
    public function buscarBackupsUC() {
        $arreglo = array();
        if ($this -> email != "Generales") {
            $arreglo = $this -> verifyExistsUser();
            if ($arreglo["error"]) return $arreglo;
        }
        //$filtrosSearch = "(SELECT JSON_PRETTY({\"id_backup\":{\"value\" : \"\", \"valueAnt\" : \"\", \"isFilter\" : false}, \"automatic\":{\"value\" : \"-1\", \"valueAnt\" : \"\", \"isFilter\" : false}, \"date_creation\":{\"value\" : \"\",\"valueAnt\" : \"\",\"isFilter\" : false},\"date_download\":{\"value\" : \"\",\"valueAnt\" : \"\",\"isFilter\" : false},\"created_in\":{\"value\" : \"\",\"valueAnt\" : \"\",\"isFilter\" : false}})) as filtrosSearch";
        //$filtrosSearch = "{\"id_backup\":{value : '', valueAnt : '', isFilter : false}, automatic:{value : '-1', valueAnt : '', isFilter : false}, date_creation:{value : '',valueAnt : '',isFilter : false},date_creation:{value : '',valueAnt : '',isFilter : false},date_download:{value : '',valueAnt : '',isFilter : false},created_in:{value : '',valueAnt : '',isFilter : false}} as filtrosSearch";
        $this -> pagina = $this -> pagina * $this -> limit;
        $where = "1 ORDER BY tabla.cantRep desc limit $this->pagina, $this->limit";
        $select = "tabla.*";
        $table = "((SELECT u.*, COUNT(b.id_user) as cantRep FROM backups b, users u WHERE b.id_user = u.id_user ". $this -> condicionarConsulta("'".$this -> email."'", "u.email", "'Generales'")." GROUP BY b.id_user HAVING COUNT(*) > $this->rango) AS tabla)";
        $arreglo["consulta"] = $this -> consultaSQL($select, $table, $where);
        //return $arreglo;
        $consulta = $this -> b -> mostrar($where, $select, $table);
        if ($consulta) {
            $arreglo["error"] = false;
            $arreglo["backups"] = $consulta;
            $arreglo["titulo"] = "¡ BACKUPS ENCONTRADOS !";
            $arreglo["msj"] = "Se encontraron mas de $this->rango backups ". (($this -> email == "Generales") ? "de todos los usuarios generales" : "del usuario: $this->email");
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ BACKUPS NO ENCONTRADOS !";
            $arreglo["msj"] = "No se encontraron mas de $this->rango backups ". (($this -> email == "Generales") ? "de todos los usuarios generales" : "del usuario: $this->email");
        }
        return $arreglo;
    }
    public function eliminarBackup() {
        $id = Form::getValue('id_backup');

        $arreglo = array();

        $delete = $this -> b -> eliminar($id);
        if ($delete) {
            $arreglo["error"] = false;
            $arreglo["titulo"] = "¡ BACKUP ELIMINADA !";
            $arreglo["msj"] = "El backup $id ha sido eliminado satisfactoriamente";
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ BACKUP NO ELIMINADA !";
            $arreglo["msj"] = "Ocurrio un error al intentar eliminar el backup: $id";
        }
        return $arreglo;
    }

    public function limpiarBackups() {
        $this -> idUser = Form::getValue("idUser");
        $this -> rango = Form::getValue("rango");
        $arreglo = array();
        $form = new Form();
        $form -> validarDatos($this -> idUser,"Usuario","required|enterosPositivos");
        if ($this -> idUser == 0) { // Limpieza general
            if (count($form -> errores) > 0) {
                $arreglo["error"] = true;
                $arreglo["titulo"] = "¡ ERROR DE VALIDACIÓN !";
                $arreglo["msj"] = $form -> errores;
                return $arreglo;
            }
            $this -> email = "Generales";
            $UsuariosGralBack = $this -> buscarBackupsUC();
            $arreglo["UsuariosGralBack "] = $UsuariosGralBack;
            if (!$UsuariosGralBack["error"]) {
                $error = 0;
                foreach ($UsuariosGralBack["backups"] as $key => $value) {
                    $this -> idUser = $value -> id_user;
                    $this -> cantidad = $value -> cantRep;
                    $this -> cantidad = $this -> cantidad - $this -> rango;
                    $this -> email = $value -> email;
                    $limpiarBackupsUser = $this -> limpiarBackupsUser();
                    if ($limpiarBackupsUser["error"]) {
                        $error++;
                        $arreglo["error"] = true;
                        $arreglo["titulo"] = ($error == 1) ? "¡ ERROR DE AJUSTE DE 1 USUARIO !" : "¡ ERROR DE AJUTE DE $error USUARIOS !";
                        $arreglo["msj"] = "No se ajustaron algunos o todos lo backups de $error usuario" . (($error == 1) ? "": "s");
                        $arreglo["errorUser"][$this -> idUser] =  $limpiarBackupsUser;
                    }
                }
                if ($error == 0){
                    $arreglo["error"] = false;
                    $arreglo["titulo"] = "¡ BACKUPS AJUSTADOS !";
                    $arreglo["msj"] = "Se ajustaron correctamente todos los backups de los usuarios que tenian mas de $this->rango Respaldos.";
                }
            } else {
                $arreglo = $UsuariosGralBack;
            }
        } else { // Limpieza de un solo usuario
            $this -> email = Form::getValue("email");
            $this -> cantidad = Form::getValue("cantidad");
            $this -> cantidad = $this -> cantidad - $this -> rango;
            if (count($form -> errores) > 0) {
                $arreglo["error"] = true;
                $arreglo["titulo"] = "¡ ERROR DE VALIDACIÓN !";
                $arreglo["msj"] = $form -> errores;
                return $arreglo;
            }
            $arreglo = $this -> limpiarBackupsUser();
        }
        return $arreglo;
    }
    private function limpiarBackupsUser() {
        $arreglo = array();
        $where = "id_backup in (select tabla.id_backup from ((SELECT @rownum:=@rownum+1 AS pos, b.id_backup FROM (SELECT @rownum:=0) r, `backups` b where b.id_user = $this->idUser ORDER BY `b`.`id_backup` DESC) as tabla) where tabla.pos > $this->rango)";
        $delete = $this -> b -> eliminarBackupUser($where);
        $arreglo["id_user"] = $this -> idUser;
        if ($delete) {
            $arreglo["error"] = "success";
            $arreglo["titulo"] = ($this -> cantidad == 1) ? "¡ BACKUP AJUSTADO !" : "¡ BACKUPS AJUSTADOS !";
            $arreglo["msj"] = (($this -> cantidad == 1) ? "El backup ha sido eliminado satisfactoriamente" : "Los bakups han sido eliminados satisfactoriamente") . " del usuario : " . $this -> email;
        } else {
            $arreglo["error"] = "warning";
            $arreglo["titulo"] = ($this -> cantidad == 1) ? "¡ BACKUP NO AJUSTADO !" : "¡ BACKUPS NO AJUSTADOS !";
            $arreglo["msj"] = "Ocurrio un error al intentar eliminar " . (($this -> cantidad == 1) ? "el backup" : " algunos o todos los backups") . " del usuario : " . $this -> email;
        }
        return $arreglo;
    }
    // ------------------------ limpiarBackupsUsers ----------------------------
    public function limpiarBackupsUsers() {
        $users = json_decode(Form::getValue("users", false, false));
        $this -> rango = Form::getValue("rangoBackups");

        $arreglo = array();

        $warning = 0;
        $errorUserCleanBackup = array();
        $resultCleanBackupsUser = array();
        foreach ($users as $key => $value) {
            $this -> idUser = $value -> id;
            $this -> cantidad = $value -> cantidadBackups;
            $this -> cantidad = $this -> cantidad - $this -> rango;
            $this -> email = $value -> email;
            $limpiarBackupsUser = $this -> limpiarBackupsUser();
            array_push($resultCleanBackupsUser, $limpiarBackupsUser);
            if ($limpiarBackupsUser["error"] == "warning") {
                $warning++;
                array_push($errorUserCleanBackup, $value);
            }
        }
        $arreglo["warning"] = $warning;
        $arreglo["resultCleanBackupsUser"] = $resultCleanBackupsUser;
        if ($warning == 0){
            $arreglo["error"] = false;
            $arreglo["titulo"] = "¡ BACKUPS AJUSTADOS !";
            $arreglo["msj"] = "Se ajustaron correctamente todos los backups de los usuarios que tenian mas de $this->rango Respaldos.";
        } else {
            $arreglo["usuariosError"] = $errorUserCleanBackup;
            $arreglo["error"] = true;
            $arreglo["titulo"] = ($warning == 1) ? "¡ ERROR DE AJUSTE DE 1 USUARIO !" : "¡ ERROR DE AJUTE DE $warning USUARIOS !";
            $arreglo["msj"] = "No se ajustaron algunos o todos lo backups de $warning usuario" . (($warning == 1) ? "": "s");
        }
        return $arreglo;
    }
    // ------------------------ limpiarBackupsUsers ----------------------------
    public function actualizarBackup() {
        /*$id_backup = Form::getValue("id_backup");
        $automatic = Form::getValue("automatic");
        $date_creation = Form::getValue("date_creation");
        $date_download = Form::getValue("date_download");
        $created_in = Form::getValue("created_in");*/
        $backup = json_decode(Form::getValue("backup", false, false));
        $update = $this -> b -> actualizar($backup);
        $arreglo = array();
        //return $update;
        if ($update) {
            $arreglo["error"] = false;
            $arreglo["titulo"] = "¡ BACKUP ACTUALIZADO !";
            $arreglo["msj"] = "El backup con id $backup->id_backup se actualizo correctamente";
            $this -> id_backup = $backup->id_backup;
            $queryBackupUpdate = $this -> consultarBackup(false);
            if (!$queryBackupUpdate["error"]) {
                $arreglo["backup"]["error"] = false;
                $arreglo["backup"]["update"] = $queryBackupUpdate["backup"][0];
            } else {
                $arreglo["backup"]["error"] = true;
                $arreglo["backup"]["titulo"] = "";
                $arreglo["backup"]["msj"] = "No se pudo cargar lo datos del backup actualizado. Porfavor recargue esta página";
            }
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ BACKUP NO ACTUALIZADO !";
            $arreglo["msj"] = "El backup con id $backup->id_backup no se actualizo correctamente";
        }
        return $arreglo;
    }
    private $id_backup = 0;
    public function consultarBackup($isQuery = true) {
        if ($isQuery) {
            $this -> id_backup = Form::getValue("id_backup");
        }
        $arreglo = array();
        $query = $this -> b -> mostrar("id_backup = $this->id_backup");
        if ($query) {
            $arreglo["backup"] = $query;
            $arreglo["error"] = false;
            $arreglo["titulo"] = "¡ BACKUP LOCALIZADO !";
            $arreglo["msj"] = "El backup con id $this->id_backup se encuentra en la base de daos";
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ BACKUP NO LOCALIZADO !";
            $arreglo["msj"] = "El backup con id $this->id_backup no se encuentra en la base de datos";
        }
        return $arreglo;
    }
    public function exportarBackup() {
        $id_backup = Form::getValue("id_backup");
        $typeExport = Form::getValue("typeExport");
        return ($typeExport == "sqlite") ? $this -> sqliteExport($id_backup) : $this -> xlsExport($id_backup);
    }
    private function sqliteExport($id_backup) {
        require_once (APP_PATH . "model/DBSQLITE.php");
        require_once (APP_PATH . "control/ControlMovement.php");
        require_once (APP_PATH . "control/ControlCurrency.php");
        require_once (APP_PATH . "control/ControlCardView.php");
        require_once (APP_PATH . "control/ControlCategory.php");
        require_once (APP_PATH . "control/ControlBudget.php");
        require_once (APP_PATH . "control/ControlAutomatic.php");
        require_once (APP_PATH . "control/ControlAccount.php");
        require_once (APP_PATH . "control/ControlPreference.php");
        $arreglo = array();
        $dbSqlite = new DBSQLITE();
        $dbSqlite -> generateSchema();
        $error = 0;

        // ------------------------------ Insert Movements ------------------------------ //
        $control = new ControlMovement();
        $control -> setPk_Movement($id_backup);
        $query = $control -> buscarMovementsBackup(false, true);
        if (!$query["error"]) {
            $insert = $dbSqlite -> insertMultipleData("table_movements", $query["movements"], $control -> getMovementModel() -> columnsTableSQLITE);
            $arreglo["insertMovements"] = $insert;
            if (!$insert) {
                $error++;
                $arreglo["errorInsert"][$error]["error"] = true;
                $arreglo["errorInsert"][$error]["titulo"] = "";
                $arreglo["errorInsert"][$error]["msj"] = "Ocurrio un error al intentar ingresar los registros en la tabla \"table_movements\" sobre el fichero database.sqlite";
            }
        }
        // ------------------------------ Insert Movements ------------------------------ //

        // ------------------------------ Insert Currencies ------------------------------ //
        $control = new ControlCurrency();
        $control -> setPk_Currency($id_backup);
        $query = $control -> obtCurrenciesGralBackup(false, true);
        $insert = $dbSqlite -> insertMultipleData("table_currencies", $query["currencies"], $control -> getCurrencyModel() -> columnsTableSQLITE);
        $arreglo["insertCurrencies"] = $insert;
        if (!$insert) {
            $error++;
            $arreglo["errorInsert"][$error]["error"] = true;
            $arreglo["errorInsert"][$error]["titulo"] = "";
            $arreglo["errorInsert"][$error]["msj"] = "Ocurrio un error al intentar ingresar los registros en la tabla \"table_currencies\" sobre el fichero database.sqlite";
        }
        // ------------------------------ Insert Currencies ------------------------------ //

        // ------------------------------ Insert Cardviews ------------------------------ //
        $control = new ControlCardView();
        $control -> setPk_CardView($id_backup);
        $query = $control -> obtCardViewsGralBackup(false, true);
        //$arreglo["queryCardView"] = $query;
        if (!$query["error"]) {
            $insert = $dbSqlite -> insertMultipleData("table_cardviews", $query["cardviews"], $control -> getCardViewModel() -> columnsTableSQLITE);
            $arreglo["insertCardViews"] = $insert;
            if (!$insert) {
                $error++;
                $arreglo["errorInsert"][$error]["error"] = true;
                $arreglo["errorInsert"][$error]["titulo"] = "";
                $arreglo["errorInsert"][$error]["msj"] = "Ocurrio un error al intentar ingresar los registros en la tabla \"table_cardviews\" sobre el fichero database.sqlite";
            }
        }
        // ------------------------------ Insert Cardviews ------------------------------ //

        // ------------------------------ Insert Categories ------------------------------ //
        $control = new ControlCategory($id_backup);
        $query = $control ->  buscarCategoriesBackup(false, true);
        if (!$query["error"]) {
            $insert = $dbSqlite -> insertMultipleData($control -> getCategoryModel() -> nameTableSQLITE, $query["categories"], $control -> getCategoryModel() -> columnsTableSQLITE);
            $arreglo["insertCategories"] = $insert;
            if (!$insert) {
                $error++;
                $arreglo["errorInsert"][$error]["error"] = true;
                $arreglo["errorInsert"][$error]["titulo"] = "";
                $arreglo["errorInsert"][$error]["msj"] = "Ocurrio un error al intentar ingresar los registros en la tabla \"" . $control -> getCategoryModel() -> nameTableSQLITE . "\" sobre el fichero database.sqlite";
            }
        }
        // ------------------------------ Insert Categories ------------------------------ //

        // ------------------------------ Insert Budgets ------------------------------ //
        $control = new ControlBudget($id_backup);
        $query = $control ->  buscarBudgetsBackup(false, true);
        if (!$query["error"]) {
            $insert = $dbSqlite -> insertMultipleData($control -> getBudgetModel() -> nameTableSQLITE, $query["budgets"], $control -> getBudgetModel() -> columnsTableSQLITE);
            $arreglo["insertBudgets"] = $insert;
            if (!$insert) {
                $error++;
                $arreglo["errorInsert"][$error]["error"] = true;
                $arreglo["errorInsert"][$error]["titulo"] = "";
                $arreglo["errorInsert"][$error]["msj"] = "Ocurrio un error al intentar ingresar los registros en la tabla \"" . $control -> getBudgetModel() -> nameTableSQLITE . "\" sobre el fichero database.sqlite";
            }
        }
        // ------------------------------ Insert Budgets ------------------------------ //

        // ------------------------------ Insert Automatics ------------------------------ //
        $control = new ControlAutomatic($id_backup);
        $query = $control ->  buscarAutomaticsBackup(false, true);
        if (!$query["error"]) {
            $insert = $dbSqlite -> insertMultipleData($control -> getAutomaticModel() -> nameTableSQLITE, $query["automatics"], $control -> getAutomaticModel() -> columnsTableSQLITE);
            $arreglo["insertAutomatics"] = $insert;
            if (!$insert) {
                $error++;
                $arreglo["errorInsert"][$error]["error"] = true;
                $arreglo["errorInsert"][$error]["titulo"] = "";
                $arreglo["errorInsert"][$error]["msj"] = "Ocurrio un error al intentar ingresar los registros en la tabla \"" . $control -> getAutomaticModel() -> nameTableSQLITE . "\" sobre el fichero database.sqlite";
            }
        }
        // ------------------------------ Insert Automatics ------------------------------ //

        // ------------------------------ Insert Accounts ------------------------------ //
        $control = new ControlAccount($id_backup);
        $query = $control ->  buscarAccountsBackup(false, true);
        if (!$query["error"]) {
            $insert = $dbSqlite -> insertMultipleData($control -> getAccountModel() -> nameTableSQLITE, $query["accounts"], $control -> getAccountModel() -> columnsTableSQLITE);
            $arreglo["insertAccounts"] = $insert;
            if (!$insert) {
                $error++;
                $arreglo["errorInsert"][$error]["error"] = true;
                $arreglo["errorInsert"][$error]["titulo"] = "";
                $arreglo["errorInsert"][$error]["msj"] = "Ocurrio un error al intentar ingresar los registros en la tabla \"" . $control -> getAccountModel() -> nameTableSQLITE . "\" sobre el fichero database.sqlite";
            }
        }
        // ------------------------------ Insert Accounts ------------------------------ //

        // ------------------------------ Insert Preferences ------------------------------ //
        $control = new ControlPreference($id_backup);
        $query = $control ->  buscarPreferencesBackup(false, true);
        if (!$query["error"]) {
            $insert = $dbSqlite -> insertMultipleData($control -> getPreferenceModel() -> nameTableSQLITE, $query["preferences"], $control -> getPreferenceModel() -> columnsTableSQLITE);
            $arreglo["insertPreferences"] = $insert;
            if (!$insert) {
                $error++;
                $arreglo["errorInsert"][$error]["error"] = true;
                $arreglo["errorInsert"][$error]["titulo"] = "";
                $arreglo["errorInsert"][$error]["msj"] = "Ocurrio un error al intentar ingresar los registros en la tabla \"" . $control -> getPreferenceModel() -> nameTableSQLITE . "\" sobre el fichero database.sqlite";
            }
        }
        // ------------------------------ Insert Preferences ------------------------------ //

        if ($error == 0) {
            /*header("Content-Type: application/force-download");
            header("Content-disposition: attachment; filename=ExportBackupSQlite/database.sqlite");
            header("Content-type: application/sqlite");
            readfile("ExportBackupSQlite/database.sqlite");*/
            $arreglo["error"] = false;
            $arreglo["titulo"] = "¡ EXPORTACIÓN TERMINADA !";
            $arreglo["msj"] = "Se creo correctamente el fichero SQLITE del Respaldo con id_backup: " . $id_backup;
        } else {
            $arreglo["error"] = "warning";
            $arreglo["titulo"] = "¡ EXPORTACIÓN NO TERMINADA !";
            $arreglo["msj"] = "Ocurrieron errores al crear el fichero SQLITE del Respaldo con id_backup: " . $id_backup;
        }
        return $arreglo;
    }
    private function xlsExport($id_backup) {
        $arreglo["id_backup"] = $id_backup;
        return $arreglo;
    }
}