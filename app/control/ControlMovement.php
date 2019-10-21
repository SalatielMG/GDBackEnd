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

    public function __construct()
    {
        $this -> m = new Movement();
    }
    public function buscarMovementsBackup($isQuery = true) {
        if ($isQuery) {
            $this -> pk_Movement["id_backup"] = Form::getValue('id_backup');
            $this -> pagina = Form::getValue("pagina");
            $this -> pagina = $this -> pagina * $this -> limit;
        }
        $arreglo = array();
        $exixstIndexUnique = $this -> m -> verifyIfExistsIndexUnique($this -> m -> nameTable);

        if ($exixstIndexUnique["indice"]) {

        } else {
            $this -> select = "bm.*, (SELECT symbolCurrency(" . $this -> pk_Movement["id_backup"] . ", '', bm.id_account)) AS symbol, (SELECT nameAccount(" . $this -> pk_Movement["id_backup"] . ", bm.id_account)) AS nameAccount, (SELECT nameCategory(" . $this -> pk_Movement["id_backup"] . ", bm.id_category)) as nameCategory,  COUNT(bm.id_backup) repeated";
            $this -> table = "backup_movements bm";
            $this -> where = (($isQuery) ? "bm.id_backup = " . $this -> pk_Movement["id_backup"] : $this -> conditionVerifyExistsUniqueIndex($this -> pk_Movement, $this -> m -> columnsTableIndexUnique, false, "bm.")) . " GROUP BY " . $this -> namesColumns($this -> m -> columnsTableIndexUnique, "bm.") . " HAVING COUNT( * ) >= 1 ORDER BY bm.date_idx " . (($isQuery) ? "limit $this->pagina,$this->limit" : "");
        }
        /*$select = $this -> m -> mostrar("1 ORDER BY CC.date_record",
            "CC.*",
            "(SELECT bm.*, bc.symbol, bac.name as account, bcat.name as category FROM backup_movements bm, backup_currencies bc, backup_accounts bac, backup_categories bcat WHERE bm.id_backup = bc.id_backup AND bm.id_backup = bac.id_backup AND bm.id_account = bac.id_account AND bm.id_backup = bcat.id_backup AND (bm.id_category = bcat.id_category) AND bm.id_backup = $idBackup
                    UNION
                   SELECT bm.*, bc.symbol, bac.name as account, '' AS category FROM backup_movements bm, backup_currencies bc, backup_accounts bac WHERE bm.id_backup = bc.id_backup AND bm.id_backup = bac.id_backup AND bm.id_account = bac.id_account AND bm.id_backup = $idBackup AND bm.id_category >= 10000) AS CC");

        $select = $this -> m -> mostrar("bm.id_backup = bc.id_backup AND bm.id_backup = $idBackup", "bm.*, bc.symbol", "backup_movements bm, backup_currencies bc");
        $select = $this -> m -> mostrar("bm.id_backup = bc.id_backup AND bm.id_backup = bac.id_backup AND bm.id_account = bac.id_account AND bm.id_backup = bcat.id_backup AND (bm.id_category = bcat.id_category OR bm.id_category >= 10000) AND bm.id_backup = $idBackup",
            "bm.*, bc.symbol, bac.name as account, bcat.name as category",
            "backup_movements bm, backup_currencies bc, backup_accounts bac, backup_categories bcat");*/


        $arreglo["consultaSQL"] = $this -> consultaSQL($this -> select, $this -> table, $this -> where);
        $select = $this -> m -> mostrar($this -> where, $this -> select, $this -> table);

        if ($select) {
            $arreglo["error"] = false;
            $arreglo["movements"] = $select;
            $arreglo["titulo"] = ($isQuery) ? "¡ MOVIMIENTOS ENCONTRADOS !": "¡ MOVIMIENTO ENCONTRADO !";
            $arreglo["msj"] = (($isQuery) ? "Se encontraron movimientos con " : "Se encontro movimiento con ") . $this -> keyValueArray($this -> pk_Movement);
            if ($isQuery && $this -> pagina == 0) {
                $this -> ctrlAccount = new ControlAccount($this -> pk_Movement["id_backup"]);
                $arreglo["accountsBackup"] = $this -> ctrlAccount -> obtAccountsBackup(false);
            }
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = ($isQuery) ? "¡ MOVIMIENTOS NO ENCONTRADOS !": "¡ MOVIMIENTO NO ENCONTRADO !";
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
        $select = "bm.*, COUNT(bm.id_backup) cantidadRepetida";
        $table = "backup_movements bm, backups b";
        $where = "b.id_backup = bm.id_backup " . $this -> condicionarConsulta($data -> id, "b.id_user", 0) . $this -> inBackups($backups, "bm.id_backup") . " GROUP BY ". $this -> namesColumns($this -> m -> columnsTableIndexUnique, "bm.") ." HAVING COUNT( * ) >= $this->having_Count limit $this->pagina, $this->limit_Inconsistencia";
        $arreglo["consultaSQL"] = $this -> consultaSQL($select, $table, $where);
        $consulta = $this -> m -> mostrar($where, $select, $table);
        if ($consulta) {
            $arreglo["error"] = false;
            $arreglo["movements"] = $consulta;
            $arreglo["titulo"] = "¡ INCONSISTENCIAS ENCONTRADOS !";
            $arreglo["msj"] = "Se encontraron duplicidades de registros en la tabla Movement ". (($data -> email != "Generales") ? "del usuario: $data->email" : "");
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ INCONSISTENCIAS NO ENCONTRADOS !";
            $arreglo["msj"] = "No se encontraron duplicidades de registros en la tabla Movement ". (($data -> email != "Generales") ? "del usuario: $data->email" : "");
        }
        return $arreglo;
    }
    public function corregirInconsitencia() {
        $indices = $this -> m -> ejecutarCodigoSQL("SHOW INDEX from " . $this -> m -> nameTable);
        $arreglo = array();
        $arreglo["indice"] = false;
        foreach ($indices as $key => $value) {
            if ($value -> Key_name == "indiceUnico") { //Ya existe el indice unico... Entonces la tabla ya se encuentra corregida
                $arreglo["indice"] = true;
                $arreglo["msj"] = "Ya existe el campo unico en la tabla Movements, por lo tanto ya se ha realizado la corrección de datos inconsistentes anteriormente.";
                $arreglo["titulo"] = "¡ TABLA CORREGIDA ANTERIORMENTE !";
                return $arreglo;
            }
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
            $arreglo["titulo"] = "¡ REGISTRO EXISTENTE !";
            $arreglo["msj"] = "NO se puede " . (($isUpdate) ? "actualizar el" : "registrar el nuevo") . " Movimiento, porque ya existe un registro en la BD con los mismos datos del mismo backup. Porfavor verifica y vuleva a intentarlo";

        }
        return $arreglo;
    }
    public function agregarMovement () {
        $movement = json_decode(Form::getValue("movement", false, false));
        $arreglo = array();
        $arreglo = $this -> verifyExistsIndexUnique($movement);
        if ($arreglo["error"]) return $arreglo;

        $insert = $this -> m -> agregar($movement);
        /*$arreglo["error"]= true;
        $arreglo["insert"]= $insert;
        return $arreglo;*/

        if ($insert) {
            $arreglo["error"] = false;
            $arreglo["titulo"] = "¡ MOVIMIENTO AGREGADO !";
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
            $arreglo["titulo"] = "¡ MOVIMIENTO NO AGREGADO !";
            $arreglo["msj"] = "Ocurrio un error al ingresar el nuevo movimiento.";
        }
        return $arreglo;
    }
    public function actualizarMovement () {
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
        /*$arreglo["error"] = true;
        $arreglo["update"] = $update;
        return $arreglo;*/

        if ($update) {
            $arreglo["error"] = false;
            $arreglo["titulo"] = "¡ MOVIMIENTO ACTUALIZADO !";
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
            $arreglo["titulo"] = "¡ MOVIMIENTO NO ACTUALIZADA !";
            $arreglo["msj"] = "Ocurrio un error al intentar actualizar el movimiento con " . $this -> keyValueArray($indexUnique);
        }
        return $arreglo;
    }
    public function eliminarMovement () {
        $indexUnique = json_decode(Form::getValue("indexUnique", false, false));
        $arreglo = array();
        $delete = $this -> m -> eliminar($indexUnique);
        if ($delete) {
            $arreglo["error"] = false;
            $arreglo["titulo"] = "¡ MOVIMIENTO ELIMINADA !";
            $arreglo["msj"] = "El movimiento con " . $this -> keyValueArray($indexUnique) . " ha sido eliminado correctamente";
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ MOVIMIENTO NO ELIMINADA !";
            $arreglo["msj"] = "Ocurrio un error al intentar eliminar el movimiento con " . $this -> keyValueArray($indexUnique);
        }
        return $arreglo;
    }
}