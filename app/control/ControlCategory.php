<?php
/**
 * Created by PhpStorm.
 * User: pc-01
 * Date: 21/08/2019
 * Time: 14:05
 */
require_once (APP_PATH."model/Category.php");
require_once ("ControlAccount.php");

class ControlCategory extends Valida
{
    private $c;
    private $ctrlAccount;
    private $pagina = 0;
    private $where = "";
    private $select = "";
    private $table = "";
    private $id_backup = 0;
    private $id_account = 0;
    private $pk_Category = array();

    public function __construct($id_backup = 0)
    {
        $this -> c = new Category();
        $this -> id_backup = $id_backup;
    }

    public function setId_Backup($id_backup) {
        $this -> id_backup = $id_backup;
    }

    public function getId_Backup() {
        return $this -> id_backup;
    }

    public function setId_Account($id_account) {
        $this -> id_account = $id_account;
    }

    public function getId_Account() {
        return $this -> id_account;
    }

    private function condition_pk_Category($isQuery, $alias){
        return (!$isQuery) ? " AND $alias.id_account = " . $this -> pk_Category["id_account"] . " AND $alias.id_category = " . $this -> pk_Category["id_category"] : "";
    }

    public function obtCategoriesAccountBackup($isQuery = true) {
        if ($isQuery) {
            $this -> id_backup = Form::getValue("id_backup");
            $this -> id_account = Form::getValue("id_account");
        }
        $arreglo = array();
        $this -> select = "id_category, name";
        $this -> where = "id_backup = $this->id_backup AND id_account = $this->id_account GROUP BY " . $this -> namesColumns($this -> c -> nameColumnsIndexUnique, "") . "  HAVING COUNT( * ) >= 1";
        $categoriesBackup = $this -> c -> mostrar($this -> where, $this -> select);
        if ($categoriesBackup) {
            $arreglo["categories"] = $categoriesBackup;
            $arreglo["error"] = false;
            $arreglo["titulo"] = "¡ CATEGORIES ENCONTRADOS !";
            $arreglo["msj"] = "Se encontraron categories del Respaldo con id_backup: $this->id_backup con el id_account: $this->id_account.";
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ CATEGORIES NO ENCONTRADOS !";
            $arreglo["msj"] = "No se encontraron categories del Respaldo con id_backup: $this->id_backup con el id_account: $this->id_account.";
        }
        return $arreglo;
    }

    public function buscarCategoriesBackup($isQuery = true) {
        if ($isQuery) {
            $this -> pk_Category["id_backup"] = Form::getValue("id_backup");
            $this -> pagina = Form::getValue("pagina");
            $this -> pagina = $this -> pagina * $this -> limit;
        }
        $exixstIndexUnique = $this -> c -> verifyIfExistsIndexUnique($this -> c -> nameTable);
        if ($exixstIndexUnique["indice"]) { // Table si inconsistencia de datos.

        } else { // Table con inconsistencia de datos.
            $this -> select = "bc.*, (SELECT nameAccount(" . $this -> pk_Category["id_backup"] . ", bc.id_account)) AS nameAccount, COUNT(bc.id_backup) as repeated";
            $this -> table = $this -> c -> nameTable . " bc";
            $this -> where = "bc.id_backup = " . $this -> pk_Category["id_backup"] . $this -> condition_pk_Category($isQuery, "bc") . " GROUP BY " . $this -> namesColumns($this -> c -> nameColumnsIndexUnique, "bc."). " HAVING COUNT( * ) >= 1 " . (($isQuery) ? "limit $this->pagina,$this->limit" : "");
        }
        $select = $this -> c -> mostrar($this -> where, $this -> select, $this -> table);
        //$select = $this -> c -> mostrar("bc.id_backup = ba.id_backup AND bc.id_account = ba.id_account AND bc.id_backup = " . $this -> pk_Category["id_backup"], "bc.*, ba.name as account", "backup_categories bc, backup_accounts ba");
        $arreglo = array();
        if ($select) {
            $arreglo["error"] = false;
            $arreglo["categories"] = $select;
            $arreglo["titulo"] = ($isQuery) ? "¡ CATEGORIAS ENCONTRADOS !" : "¡ CATEGORIA ENCONTRADO !";
            $arreglo["msj"] = ($isQuery) ? "Se encontraron categorias del respaldo con id_backup: " . $this -> pk_Category["id_backup"] : "Se encontro la categoria con id_category: " . $this -> pk_Category["id_category"] . " de la cuenta con id_account: " . $this -> pk_Category["id_account"] . " del respaldo con id_backup: " . $this -> pk_Category["id_backup"];
            if ($isQuery && $this -> pagina == 0) {
                $this -> ctrlAccount = new ControlAccount($this -> pk_Category["id_backup"]);
                $arreglo["accountsBackup"] = $this -> ctrlAccount -> obtAccountsBackup(false);
            }
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = ($isQuery) ? "¡ CATEGORIAS NO ENCONTRADOS !" : "¡ CATEGORIA NO ENCONTRADO !";
            $arreglo["msj"] = ($isQuery) ? "No se encontraron categorias del respaldo con id_backup: " . $this -> pk_Category["id_backup"] : "No se encontro la categoria con id_category: " . $this -> pk_Category["id_category"] . " de la cuenta con id_account: " . $this -> pk_Category["id_account"] . " del respaldo con id_backup: " . $this -> pk_Category["id_backup"];
        }
        return $arreglo;
    }

    public function inconsistenciaCategory() {
        $data = json_decode(Form::getValue('dataUser', false, false));
        $this -> pagina = Form::getValue('pagina');
        $backups = json_decode(Form::getValue('backups', false, false));
        $arreglo = array();

        $this -> pagina = $this -> pagina * $this -> limit_Inconsistencia;
        $select = "bc.*, COUNT(bc.id_backup) cantidadRepetida";
        $table = "backup_categories bc, backups b";
        $where = "b.id_backup = bc.id_backup ". $this -> condicionarConsulta($data -> id, "b.id_user", 0) . $this -> inBackups($backups, "bc.id_backup") . " GROUP BY ". $this -> namesColumns($this -> c -> nameColumnsIndexUnique, "bc.") ." HAVING COUNT( * ) >= $this->having_Count limit $this->pagina , $this->limit_Inconsistencia";
        $arreglo["consultaSQL"] = $this -> consultaSQL($select, $table, $where);
        $consulta = $this -> c -> mostrar($where, $select, $table);
        if ($consulta) {
            $arreglo["error"] = false;
            $arreglo["categories"] = $consulta;
            $arreglo["titulo"] = "¡ INCONSISTENCIAS ENCONTRADOS !";
            $arreglo["msj"] = "Se encontraron duplicidades de registros en la tabla Category ". (($data -> email != "Generales") ? "del usuario: $data->email" : "");
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ INCONSISTENCIAS NO ENCONTRADOS !";
            $arreglo["msj"] = "No se encontraron duplicidades de registros en la tabla Category ". (($data -> email != "Generales") ? "del usuario: $data->email" : "");
        }
        return $arreglo;
    }
    public function corregirInconsitencia() {
        $indices = $this -> c -> ejecutarCodigoSQL("SHOW INDEX from " . $this -> c -> nameTable);
        $arreglo = array();
        $arreglo["indice"] = false;
        foreach ($indices as $key => $value) {
            if ($value -> Key_name == "indiceUnico") { //Ya existe el indice unico... Entonces la tabla ya se encuentra corregida
                $arreglo["indice"] = true;
                $arreglo["msj"] = "Ya existe el campo unico en la tabla Categories, por lo tanto ya se ha realizado la corrección de datos inconsistentes anteriormente.";
                $arreglo["titulo"] = "¡ TABLA CORREGIDA ANTERIORMENTE !";
                return $arreglo;
            }
        }
        $sql = $this -> sentenciaInconsistenicaSQL($this -> c -> nameTable, $this -> c ->nameColumnsIndexUnique, "id_backup");
        $operacion = $this -> c -> ejecutarMultSentMySQLi($sql);
        $arreglo["SenteciasSQL"] = $sql;
        $arreglo["Result"] = $operacion;
        return $arreglo;
    }
    public function obtNewId_Category() {
        $arreglo = array();
        $this -> pk_Category["id_backup"] = Form::getValue("id_backup");
        // $this -> pk_Category["id_account"] = Form::getValue("id_account");
        $this -> where = "id_backup = " . $this -> pk_Category["id_backup"];
        $this -> select = "max(id_category)as max";
        $result = $this -> c -> mostrar($this -> where, $this -> select);
        $arreglo["consultaSQL"] = $this -> consultaSQL($this -> select, $this -> table, $this -> where);
        if ($result) {
            $newId_Category = $result[0] -> max + 1;
            $arreglo["newId_Category"] = $newId_Category;
            $arreglo["error"] = false;
            $arreglo["titulo"] = "¡ ID CATEGORY CALCULADO !";
            $arreglo["msj"] = "Se calculo correctamente la nueva id_category del respaldo con id_backup: " . $this -> pk_Category["id_backup"];
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ ID CATEGORY NO CALCULADO !";
            $arreglo["msj"] = "NO se pudo calcular correctamente la nueva id_category del respaldo con id_backup: " . $this -> pk_Category["id_backup"];
        }
        return $arreglo;
    }
    public function verifyExistsIndexUniqueCategory($indexUnique) {
        $arreglo["isExists"] = false;
        $this -> where = "id_backup = $indexUnique->id_backup AND id_account = $indexUnique->id_account AND UPPER(name) = UPPER('$indexUnique->name')";
        $result = $this -> c -> mostrar($this -> where);
        $arreglo["result"] =  $result;
        $arreglo["consultaSQL"] = $this -> consultaSQL("*", $this -> c -> nameTable, $this -> where);
        if ($result)
            $array["isExists"] = true;
        return $arreglo;
    }
    public function agregarCategoria() {
        $category = json_decode(Form::getValue("category", false, false));
        $arreglo = array();
        $arreglo["verifyExistsIndexUniqueCategory"] = $this -> verifyExistsIndexUniqueCategory($category);
        if (!$arreglo["verifyExistsIndexUniqueCategory"]["isExists"]) {
            $insert = $this -> c -> agregar($category);
            if ($insert) {
                $arreglo["error"] = false;
                $arreglo["titulo"] = "¡ CATEGORIA AGREGADO !";
                $arreglo["msj"] = "Se agrego correctamenmte la categoria con id_category: $category->id_category a la cuenta con id_account: $category->id_account del respaldo con id_backup: $category->id_backup";

                $this -> pk_Category["id_backup"] = $category -> id_backup;
                $this -> pk_Category["id_account"] = $category -> id_account;
                $this -> pk_Category["id_category"] = $category -> id_category;
                $queryNewCategory = $this -> buscarCategoriesBackup(false);
                $arreglo["category"]["error"] = $queryNewCategory["error"];
                $arreglo["category"]["titulo"] = $queryNewCategory["titulo"];
                $arreglo["category"]["msj"] = $queryNewCategory["msj"];
                if (!$arreglo["category"]["error"]) $arreglo["category"]["new"] = $queryNewCategory["categories"][0];
            } else {
                $arreglo["error"] = true;
                $arreglo["titulo"] = "¡ CATEGORIA NO AGREGADO !";
                $arreglo["msj"] = "Ocurrio un error al agregar la categoria con id_category: $category->id_category a la cuenta con id_account: $category->id_account del respaldo con id_backup: $category->id_backup";
            }
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ REGISTRO EXISTENTE !";
            $arreglo["msj"] = "NO se puede agregar la nueva categoria, puesto que ya existe un registro en la Base de Datos con el mismo nombre de la misma cuenta del backup solicitado. Porfavor cambie el nombre y vuelva a intentarlo.";
        }
        return $arreglo;
    }
    public function actualizarCategoria() {
        $category = json_decode(Form::getValue("category", false, false));
        $indexUnique = json_decode(Form::getValue("indexUnique", false, false));
        $arreglo = array();

        $isExistsIndexUnique = false;
        if (($category -> id_account != $indexUnique -> id_account) || strtoupper(($category -> name)) != strtoupper($indexUnique -> name)) {
            $arreglo["verifyExistsIndexUniqueCategory"] = $this -> verifyExistsIndexUniqueCategory($category);
            $isExistsIndexUnique = $arreglo["verifyExistsIndexUniqueCategory"]["isExists"];
            // return $isExistsIndexUnique;
        }
        if ($isExistsIndexUnique) {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ REGISTRO EXISTENTE !";
            $arreglo["msj"] = "NO se puede actualizar la categoria, puesto que ya existe un registro en la Base de Datos con el mismo nombre de la misma cuenta del backup solicitado. Porfavor cambie el nombre y vuelva a intentarlo.";
            return $arreglo;
        }
        $update = $this -> c -> actualizar($category, $indexUnique);
        if ($update) {
            $arreglo["error"] = false;
            $arreglo["titulo"] = "¡ CATEGORIA ACTUALIZADO !";
            $arreglo["msj"] = "Se actualizo correctamente la categoria con id_category: $indexUnique->id_category de la cuenta con id_account: $indexUnique->id_account del respaldo con id_backup: $indexUnique->id_backup";

            $this -> pk_Category["id_backup"] = $category -> id_backup;
            $this -> pk_Category["id_account"] = $category -> id_account;
            $this -> pk_Category["id_category"] = $category -> id_category;
            $queryUpdateCategory = $this -> buscarCategoriesBackup(false);
            $arreglo["category"]["error"] = $queryUpdateCategory["error"];
            $arreglo["category"]["titulo"] = $queryUpdateCategory["titulo"];
            $arreglo["category"]["msj"] = $queryUpdateCategory["msj"];
            if (!$arreglo["category"]["error"]) $arreglo["category"]["update"] = $queryUpdateCategory["categories"][0];
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ CATEGORIA NO ACTUALIZADO !";
            $arreglo["msj"] = "Ocurrio un error al actualizar la categoria con id_category: $indexUnique->id_category de la cuenta con id_account: $indexUnique->id_account del respaldo con id_backup: $indexUnique->id_backup";
        }
        return $arreglo;
    }
    public function eliminarCategoria() {
        $indexUnique = json_decode(Form::getValue("indexUnique", false, false));
        $areglo = array();
        $delete = $this -> c -> eliminar($indexUnique);
        if ($delete) {
            $arreglo["error"] = false;
            $arreglo["titulo"] = "¡ CATEGORIA ELIMINADA !";
            $arreglo["msj"] = "La categoria con id_category: $indexUnique->id_category de la cuenta con id: $indexUnique->id_account del respaldo con id_backup: $indexUnique->id_backup se ha eliminado correctamente";
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ CATEGORIA NO ELIMINADA !";
            $arreglo["msj"] = "La categoria con id_category: $indexUnique->id_category de la cuenta con id: $indexUnique->id_account del respaldo con id_backup: $indexUnique->idbackup node ha eliminado correctamente";
        }
        return $areglo;
    }
}