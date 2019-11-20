<?php
/**
 * Created by PhpStorm.
 * User: pc-01
 * Date: 21/08/2019
 * Time: 14:43
 */
require_once (APP_PATH."model/Movement.php");
require_once ("ControlAccount.php");

class ControlMovement extends Valida
{
    private $m;
    private $ctrlAccount;
    private $pagina = 0;
    private $pk_Movement = array();

    private $where = "";
    private $select = "";
    private $table = "";

    public function __construct($id_backup = 0)
    {
        $this -> m = new Movement();
        $this -> pk_Movement["id_backup"] = $id_backup;
    }
    public function setPk_Movement($id_backup) {
        $this -> pk_Movement["id_backup"] = $id_backup;
    }
    public function getMovementModel() {
        return $this -> m;
    }
    public function buscarMovementsBackup($isQuery = true, $export = false, $typeExport = "sqlite") {
        if ($isQuery) {
            $this -> pk_Movement["id_backup"] = Form::getValue('id_backup');
            $this -> pagina = Form::getValue("pagina");
            $this -> pagina = $this -> pagina * $this -> limit;
        }
        $arreglo = array();
        if ($export)
            if ($typeExport == "sqlite")
                $this -> select = "(SELECT nameAccount(" . $this -> pk_Movement["id_backup"] . ", bm.id_account)) AS account, (SELECT nameCategory(" . $this -> pk_Movement["id_backup"] . ", bm.id_category)) as category, bm.amount, bm.sign, bm.detail, DATE_FORMAT(bm.date_record, '%d/%m/%Y') as date, DATE_FORMAT(bm.time_record, '%h:%i %p') as time, bm.confirmed, bm.transfer, bm.date_idx, bm.day, bm.week, bm.fortnight, bm.month, bm.year, bm.operation_code as code, bm.picture, bm.iso_code, 0 as selected";
            else
                $this -> select = "(SELECT nameAccount(" . $this -> pk_Movement["id_backup"] . ", bm.id_account)) AS account, (SELECT nameCategory(" . $this -> pk_Movement["id_backup"] . ", bm.id_category)) as category, bm.amount, bm.sign, bm.detail, DATE_FORMAT(bm.date_record, '%d/%m/%Y') as date, DATE_FORMAT(bm.time_record, '%h:%i %p') as time, (SELECT symbolCurrency(" . $this -> pk_Movement["id_backup"] . ", '', bm.id_account)) AS symbol";
        else
            $this -> select = "bm.*, (SELECT symbolCurrency(" . $this -> pk_Movement["id_backup"] . ", '', bm.id_account)) AS symbol, (SELECT nameAccount(" . $this -> pk_Movement["id_backup"] . ", bm.id_account)) AS nameAccount, (SELECT nameCategory(" . $this -> pk_Movement["id_backup"] . ", bm.id_category)) as nameCategory,  COUNT(bm.id_backup) repeated";
        $this -> table = $this -> m -> nameTable . " bm";
        $this -> where = (($isQuery || $export) ? "bm.id_backup = " . $this -> pk_Movement["id_backup"] : $this -> conditionVerifyExistsUniqueIndex($this -> pk_Movement, $this -> m -> columnsTableIndexUnique, false, "bm.")) . " GROUP BY " . $this -> namesColumns($this -> m -> columnsTableIndexUnique, "bm.") . " HAVING COUNT( * ) >= 1 ORDER BY bm.date_idx " . (($isQuery) ? "limit $this->pagina,$this->limit" : "");

        $arreglo["consultaSQL"] = $this -> consultaSQL($this -> select, $this -> table, $this -> where);
        //return $arreglo;
        $select = $this -> m -> mostrar($this -> where, $this -> select, $this -> table);

        if ($select) {
            $arreglo["error"] = false;
            $arreglo["movements"] = $select;
            $arreglo["titulo"] = ($isQuery) ? "¡ Movimientos encontrados !": "¡ Movimiento encontrado !";
            $arreglo["msj"] = (($isQuery) ? "Se encontraron movimientos con " : "Se encontro movimiento con ") . $this -> keyValueArray($this -> pk_Movement);
            if ($isQuery && $this -> pagina == 0) {
                $this -> ctrlAccount = new ControlAccount($this -> pk_Movement["id_backup"]);
                $arreglo["accountsBackup"] = $this -> ctrlAccount -> obtAccountsBackup(false);
            }
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = ($isQuery) ? "¡ Movimientos no encontrados !": "¡ Movimiento no encontrado !";
            $arreglo["msj"] = (($isQuery) ? "NO se encontraron movimientos con " : "NO se encontro movimiento con ") . $this -> keyValueArray($this -> pk_Movement);
        }
        return $arreglo;
    }
    public function inconsistenciaMovement(){
        $data = json_decode(Form::getValue('dataUser', false, false));
        $this -> pagina = Form::getValue('pagina');
        $backups = json_decode(Form::getValue('backups', false, false));
        $arreglo = array();

        $this -> pagina = $this -> pagina * $this -> limit_Inconsistencia;
        $this -> select = "bm.*, (SELECT symbolCurrency(bm.id_backup, '', bm.id_account)) AS symbol, (SELECT nameAccount(bm.id_backup, bm.id_account)) AS nameAccount, (SELECT nameCategory(bm.id_backup, bm.id_category)) as nameCategory,  COUNT(bm.id_backup) repeated";
        $this -> table = $this -> m -> nameTable . " bm, backups b";
        $this -> where = "b.id_backup = bm.id_backup " . $this -> condicionarConsulta($data -> id, "b.id_user", 0) . $this -> inBackups($backups, "bm.id_backup") . " GROUP BY ". $this -> namesColumns($this -> m -> columnsTableIndexUnique, "bm.") ." HAVING COUNT( * ) >= $this->having_Count limit $this->pagina, $this->limit_Inconsistencia";
        $arreglo["consultaSQL"] = $this -> consultaSQL($this -> select, $this -> table, $this -> where);
        $consulta = $this -> m -> mostrar($this -> where, $this -> select, $this -> table);
        if ($consulta) {
            $arreglo["error"] = false;
            $arreglo["movements"] = $consulta;
            $arreglo["titulo"] = "¡ Inconsistencias encontradas !";
            $arreglo["msj"] = "Se encontraron incosistencias de registros en la tabla Movement ". (($data -> email != "Generales") ? "del usuario: $data->email" : "");
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ Inconsistencias no encontradas !";
            $arreglo["msj"] = "No se encontraron incosistencias de registros en la tabla Movement ". (($data -> email != "Generales") ? "del usuario: $data->email" : "");
        }
        return $arreglo;
    }
    public function obtSizeTable() {
        $this -> verificarPermiso(PERMISO_MNTINCONSISTENCIA);
        $arreglo = array();
        $exixstIndexUnique = $this -> m -> verifyIfExistsIndexUnique($this -> m -> nameTable);
        if ($exixstIndexUnique["indice"]) {
            return $arreglo = $exixstIndexUnique;
        }
        $size = $this -> m -> sizeTable($this -> m -> nameTable);
        if ($size) {
            $arreglo["size"] = $size[0];
            $arreglo["error"] = false;
            $arreglo["titulo"] = "¡ Tamaño calculado !";
            $arreglo["msj"] = "Se calculo correctamente el tamaño de la tabla de datos: " . $this -> m -> nameTable;
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ Tamaño no calculado !";
            $arreglo["msj"] = "No se pudo calcular correctamente el tamaño de la tabla de datos: " . $this -> m -> nameTable;
        }
        return $arreglo;
    }
    public function corregirInconsistenciaRegistro() {
        $this -> verificarPermiso(PERMISO_MNTINCONSISTENCIA);

        $indexUnique = json_decode(Form::getValue("indexUnique", false, false));
        $arreglo = array();
        $this -> pk_Movement["id_backup"] = $indexUnique -> id_backup;
        $this -> pk_Movement["id_account"] = $indexUnique -> id_account;
        $this -> pk_Movement["id_category"] = $indexUnique -> id_category;
        $this -> pk_Movement["amount"] = $indexUnique -> amount;
        $this -> pk_Movement["detail"] = $indexUnique -> detail;
        $this -> pk_Movement["date_idx"] = $indexUnique -> date_idx;
        $movement = $this -> buscarMovementsBackup(false);
        //var_dump($account); return;
        if ($movement) {
            $correcion = $this -> m -> eliminar($indexUnique);
            if ($correcion) {
                $insertMovement = $this -> m -> agregar($movement["movements"][0]);
                if ($insertMovement) {
                    $arreglo["error"] = false;
                    $arreglo["titulo"] = "¡ Movimiento corregida !";
                    $arreglo["msj"] = "Se corrigio correctamente el Movimiento con " . $this -> keyValueArray($this -> pk_Movement);
                    $arreglo["movement"] = $this -> buscarMovementsBackup(false);
                } else {
                    $arreglo["error"] = true;
                    $arreglo["titulo"] = "¡ Error al corregir !";
                    $arreglo["msj"] = "No se pudo corregir la inconsistencia del registro seleccionado. -- 2° Proceso Insertar --";
                }
            } else {
                $arreglo["error"] = true;
                $arreglo["titulo"] = "¡ Error al corregir !";
                $arreglo["msj"] = "No se pudo corregir la inconsistencia del registro seleccionado. -- 1° Proceso Eliminar --";
            }
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ Error de consulta  !";
            $arreglo["msj"] = "Error al obtener los datos del Movimiento seleccionado para corregir";
        }
        return $arreglo;
    }
    public function corregirInconsitencia() {
        $this -> verificarPermiso(PERMISO_MNTINCONSISTENCIA);

        $arreglo = array();
        $exixstIndexUnique = $this -> m -> verifyIfExistsIndexUnique($this -> m -> nameTable);
        if ($exixstIndexUnique["indice"]) {
            return $arreglo = $exixstIndexUnique;
        }
        $sql = $this -> sentenciaInconsistenicaSQL($this -> m -> nameTable, $this -> m -> columnsTableIndexUnique,"id_backup");
        $operacion = $this -> m -> ejecutarMultSentMySQLi($sql);
        $arreglo["SenteciasSQL"] = $sql;
        $arreglo["Result"] = $operacion;
        return $arreglo;
    }
    public function verifyExistsIndexUnique ($newMovement, $isUpdate = false) {
        $arreglo = array();
        $arreglo["error"] = false;
        $arreglo["sqlVerfiyIndexUnique"] = $this -> conditionVerifyExistsUniqueIndex($newMovement, $this -> m -> columnsTableIndexUnique);
        $result = $this -> m -> mostrar( $arreglo["sqlVerfiyIndexUnique"]);
        if ($result) {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ Registro existente !";
            $arreglo["msj"] = "NO se puede " . (($isUpdate) ? "actualizar el" : "registrar el nuevo") . " Movimiento, porque ya existe un registro en la BD con los mismos datos del mismo backup. Porfavor verifica y vuleva a intentarlo";

        }
        return $arreglo;
    }
    public function agregarMovement () {
        $this -> verificarPermiso(PERMISO_INSERT);

        $movement = json_decode(Form::getValue("movement", false, false));
        $arreglo = array();
        $arreglo = $this -> verifyExistsIndexUnique($movement);
        if ($arreglo["error"]) return $arreglo;

        $insert = $this -> m -> agregar($movement);
        if ($insert) {
            $arreglo["error"] = false;
            $arreglo["titulo"] = "¡ Movimiento agregado !";
            $arreglo["msj"] = "Se agrego correctamente el nuevo movimiento.";

            $this -> pk_Movement["id_backup"] = $movement -> id_backup;
            $this -> pk_Movement["id_account"] = $movement -> id_account;
            $this -> pk_Movement["id_category"] = $movement -> id_category;
            $this -> pk_Movement["amount"] = $movement -> amount;
            $this -> pk_Movement["detail"] = $movement -> detail;
            $this -> pk_Movement["date_idx"] = $movement -> date_idx;
            $queryMovementNew = $this -> buscarMovementsBackup(false);
            $arreglo["movement"]["error"]  = $queryMovementNew["error"];
            $arreglo["movement"]["titulo"] = $queryMovementNew["titulo"];
            $arreglo["movement"]["msj"]    = $queryMovementNew["msj"];
            if (!$arreglo["movement"]["error"]) $arreglo["movement"]["new"] = $queryMovementNew["movements"][0];
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ Movimiento no agregado !";
            $arreglo["msj"] = "Ocurrio un error al ingresar el nuevo movimiento.";
        }
        return $arreglo;
    }
    public function actualizarMovement () {
        $this -> verificarPermiso(PERMISO_UPDATE);

        $movement = json_decode(Form::getValue("movement", false, false));
        $indexUnique = json_decode(Form::getValue("indexUnique", false, false));
        $arreglo = array();

        if (($movement -> id_backup != $indexUnique -> id_backup)
            || ($movement -> id_account != $indexUnique -> id_account)
            || ($movement -> id_category != $indexUnique -> id_category)
            || ($movement -> amount != $indexUnique -> amount)
            || ($movement -> detail != $indexUnique -> detail)
            || ($movement -> date_idx != $indexUnique -> date_idx)) {
            $arreglo = $this -> verifyExistsIndexUnique($movement, true);
            if ($arreglo["error"]) return $arreglo;
        }

        $update = $this -> m -> actualizar($movement, $indexUnique);
        if ($update) {
            $arreglo["error"] = false;
            $arreglo["titulo"] = "¡ Movimiento actualizada !";
            $arreglo["msj"] = "El movimiento con " . $this -> keyValueArray($indexUnique) . " se ha actualizado correctamente";
            $this -> pk_Movement["id_backup"] = $movement -> id_backup;
            $this -> pk_Movement["id_account"] = $movement -> id_account;
            $this -> pk_Movement["id_category"] = $movement -> id_category;
            $this -> pk_Movement["amount"] = $movement -> amount;
            $this -> pk_Movement["detail"] = $movement -> detail;
            $this -> pk_Movement["date_idx"] = $movement -> date_idx;
            $queryMovementUpdate = $this -> buscarMovementsBackup(false);
            $arreglo["movement"]["error"] = $queryMovementUpdate["error"];
            $arreglo["movement"]["titulo"] = $queryMovementUpdate["titulo"];
            $arreglo["movement"]["msj"] = $queryMovementUpdate["msj"];
            if (!$arreglo["movement"]["error"]) $arreglo["movement"]["update"] = $queryMovementUpdate["movements"][0];
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ Movimiento no actualizada !";
            $arreglo["msj"] = "Ocurrio un error al intentar actualizar el movimiento con " . $this -> keyValueArray($indexUnique);
        }
        return $arreglo;
    }
    public function eliminarMovement () {
        $this -> verificarPermiso(PERMISO_DELETE);

        $indexUnique = json_decode(Form::getValue("indexUnique", false, false));
        $arreglo = array();
        $delete = $this -> m -> eliminar($indexUnique);
        if ($delete) {
            $arreglo["error"] = false;
            $arreglo["titulo"] = "¡ Movimiento eliminada !";
            $arreglo["msj"] = "El movimiento con " . $this -> keyValueArray($indexUnique) . " ha sido eliminado correctamente";
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ Movimiento no eliminada !";
            $arreglo["msj"] = "Ocurrio un error al intentar eliminar el movimiento con " . $this -> keyValueArray($indexUnique);
        }
        return $arreglo;
    }
}