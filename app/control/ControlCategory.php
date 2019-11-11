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
    private $pk_Category = array();

    public function __construct($id_backup = 0)
    {
        $this -> c = new Category();
        $this -> pk_Category["id_backup"] = $id_backup;
    }

    public function setId_Account($id_account) {
        $this -> pk_Category["id_account"] = $id_account;
    }

    public function getCategoryModel() {
        return $this -> c;
    }


    public function obtCategoriesAccountBackup($isQuery = true, $signCategories = "both") {
        if ($isQuery) {
            $this -> pk_Category["id_backup"] = Form::getValue("id_backup");
            $this -> pk_Category["id_account"] = Form::getValue("id_account");
            $signCategories = Form::getValue("signCategories");
            if ($signCategories != "both") {
                $signCategories = ($signCategories == 0) ? "'-'": "'+'";
            }
        }
        $arreglo = array();
        $this -> select = "id_category, name, sign";
        $this -> where = "id_backup = " . $this -> pk_Category["id_backup"] . " AND id_account = " . $this -> pk_Category["id_account"] . $this -> condicionarConsulta($signCategories, "sign", "both") . " GROUP BY " . $this -> namesColumns($this -> c -> columnsTableIndexUnique, "") . "  HAVING COUNT( * ) >= 1 ORDER BY id_category";
        $categoriesBackup = $this -> c -> mostrar($this -> where, $this -> select);
        if ($categoriesBackup) {
            $arreglo["categories"] = $categoriesBackup;
            $arreglo["error"] = false;
            $arreglo["titulo"] = "¡ CATEGORIES ENCONTRADOS !";
            $arreglo["msj"] = "Se encontraron categorias con " . $this -> keyValueArray($this -> pk_Category);
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ CATEGORIES NO ENCONTRADOS !";
            $arreglo["msj"] = "No se encontraron categorias con " . $this -> keyValueArray($this -> pk_Category);
        }
        return $arreglo;
    }

    public function buscarCategoriesBackup($isQuery = true, $isExport = false, $typeExport = "sqlite") {
        if ($isQuery) {
            $this -> pk_Category["id_backup"] = Form::getValue("id_backup");
            $this -> pagina = Form::getValue("pagina");
            $this -> pagina = $this -> pagina * $this -> limit;
        }
        $exixstIndexUnique = $this -> c -> verifyIfExistsIndexUnique($this -> c -> nameTable);
        if ($exixstIndexUnique["indice"]) { // Table sin inconsistencia de datos.

        } else { // Table con inconsistencia de datos.
            if ($isExport)
                if ($typeExport == "sqlite")
                    $this -> select = "bc.id_category as _id, (SELECT nameAccount(" . $this -> pk_Category["id_backup"] . ", bc.id_account)) AS account, bc.name as category, bc.sign, bc.icon_name as icon, number, 0 as selected";
                else
                    $this -> select = "(SELECT nameAccount(" . $this -> pk_Category["id_backup"] . ", bc.id_account)) AS account, bc.name as category, bc.sign";
            else
                $this -> select = "bc.*, (SELECT nameAccount(" . $this -> pk_Category["id_backup"] . ", bc.id_account)) AS nameAccount, COUNT(bc.id_backup) as repeated";
            $this -> table = $this -> c -> nameTable . " bc";
            $this -> where = (($isQuery || $isExport) ? "bc.id_backup = " . $this -> pk_Category["id_backup"] : $this -> conditionVerifyExistsUniqueIndex($this -> pk_Category, $this -> c -> columnsTableIndexUnique, false, "bc.") . " AND bc.id_category = " . $this -> pk_Category["id_category"]) . " GROUP BY " . $this -> namesColumns($this -> c -> columnsTableIndexUnique, "bc."). " HAVING COUNT( * ) >= 1 ORDER BY bc.id_category " . (($isQuery) ? "limit $this->pagina,$this->limit" : "");
        }
        $select = $this -> c -> mostrar($this -> where, $this -> select, $this -> table);
        //$select = $this -> c -> mostrar("bc.id_backup = ba.id_backup AND bc.id_account = ba.id_account AND bc.id_backup = " . $this -> pk_Category["id_backup"], "bc.*, ba.name as account", "backup_categories bc, backup_accounts ba");
        $arreglo = array();
        if ($select) {
            $arreglo["error"] = false;
            $arreglo["categories"] = $select;
            $arreglo["titulo"] = ($isQuery) ? "¡ CATEGORIAS ENCONTRADOS !" : "¡ CATEGORIA ENCONTRADO !";
            $arreglo["msj"] = (($isQuery) ? "Se encontraron categorias con " : "Se encontro la categoría con ") . $this -> keyValueArray($this -> pk_Category);
            if ($isQuery && $this -> pagina == 0) {
                $this -> ctrlAccount = new ControlAccount($this -> pk_Category["id_backup"]);
                $arreglo["accountsBackup"] = $this -> ctrlAccount -> obtAccountsBackup(false);
            }
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = ($isQuery) ? "¡ CATEGORIAS NO ENCONTRADOS !" : "¡ CATEGORIA NO ENCONTRADO !";
            $arreglo["msj"] = (($isQuery) ? "No se encontraron categorias con " : "No se encontro la categoría con ") . $this -> keyValueArray($this -> pk_Category);
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
        $where = "b.id_backup = bc.id_backup ". $this -> condicionarConsulta($data -> id, "b.id_user", 0) . $this -> inBackups($backups, "bc.id_backup") . " GROUP BY ". $this -> namesColumns($this -> c -> columnsTableIndexUnique, "bc.") ." HAVING COUNT( * ) >= $this->having_Count limit $this->pagina , $this->limit_Inconsistencia";
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
        $this -> verificarPermiso(PERMISO_MNTINCONSISTENCIA);

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
        $sql = $this -> sentenciaInconsistenicaSQL($this -> c -> nameTable, $this -> c ->columnsTableIndexUnique, "id_backup");
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
    public function verifyExistsIndexUniqueCategory($newCategory, $isUpdate = false, $id_category = 0) {
        $arreglo = array();
        $arreglo["error"] = false;
        $isDifferentId_Category = true;
        if ($isUpdate) $isDifferentId_Category = $newCategory -> id_category != $id_category;
        if ($isDifferentId_Category) {
            $result = $this -> c -> mostrar("id_backup = $newCategory->id_backup AND id_category = $newCategory->id_category");
            if ($result) {
                $arreglo["error"] = true;
                $arreglo["titulo"] = "¡ REGISTRO EXISTENTE !";
                $arreglo["msj"] = "NO se puede " . (($isUpdate) ? "actualizar la" : "registrar la nueva") . " Categoria, puesto que ya existe un registro en la BD con el mismo ID_CATEGORY del mismo backup. Porfavor verifique el id e intente cambiarlo";
                return $arreglo;
            }
        }

        $arreglo["sqlVerfiyIndexUnique"] = $this -> conditionVerifyExistsUniqueIndex($newCategory, $this -> c -> columnsTableIndexUnique);
        $result = $this -> c -> mostrar($arreglo["sqlVerfiyIndexUnique"]);
        if ($result) {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ REGISTRO EXISTENTE !";
            $arreglo["msj"] = "NO se puede " . (($isUpdate) ? "actualizar la" : "registrar la nueva") . " Categoria, puesto que ya existe un registro en la BD con los mismos datos del mismo backup. Porfavor verifique los datos y vuelva a intentarlo";
        }
        return $arreglo;
    }
    public function agregarCategoria() {
        $this -> verificarPermiso(PERMISO_INSERT);

        $category = json_decode(Form::getValue("category", false, false));
        $arreglo = array();

        $arreglo = $this -> verifyExistsIndexUniqueCategory($category);
        if ($arreglo["error"]) return $arreglo;

        $insert = $this -> c -> agregar($category);
        if ($insert) {

            $this -> pk_Category["id_backup"] = $category -> id_backup;
            $this -> pk_Category["id_account"] = $category -> id_account;
            $this -> pk_Category["id_category"] = $category -> id_category;
            $this -> pk_Category["name"] = $category -> name;
            $this -> pk_Category["sign"] = $category -> sign;

            $queryNewCategory = $this -> buscarCategoriesBackup(false);
            $arreglo["category"]["error"] = $queryNewCategory["error"];
            $arreglo["category"]["titulo"] = $queryNewCategory["titulo"];
            $arreglo["category"]["msj"] = $queryNewCategory["msj"];
            if (!$arreglo["category"]["error"]) $arreglo["category"]["new"] = $queryNewCategory["categories"][0];

            $arreglo["error"] = false;
            $arreglo["titulo"] = "¡ CATEGORIA AGREGADO !";
            $arreglo["msj"] = "Se agrego correctamente la categoria con " . $this -> keyValueArray($this -> pk_Category);

        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ CATEGORIA NO AGREGADO !";
            $arreglo["msj"] = "Ocurrio un error al agregar la categoria con " . $this -> keyValueArray($this -> pk_Category);
        }
        return $arreglo;
    }
    public function actualizarCategoria() {
        $this -> verificarPermiso(PERMISO_UPDATE);

        $category = json_decode(Form::getValue("category", false, false));
        $indexUnique = json_decode(Form::getValue("indexUnique", false, false));
        $arreglo = array();

        if (($category -> id_backup != $indexUnique -> id_backup)
            || ($category -> id_account != $indexUnique -> id_account)
            || ($category -> id_category != $indexUnique -> id_category)
            || (strtoupper($category -> name) != strtoupper($indexUnique -> name))
            || ($category -> sign != $indexUnique -> sign)) {
            $arreglo = $this -> verifyExistsIndexUniqueCategory($category, true, $indexUnique -> id_category);
            if ($arreglo["error"]) return $arreglo;
        }

        $update = $this -> c -> actualizar($category, $indexUnique);
        if ($update) {
            $arreglo["error"] = false;
            $arreglo["titulo"] = "¡ CATEGORIA ACTUALIZADO !";
            $arreglo["msj"] = "Se actualizo correctamente la categoria con " . $this -> keyValueArray($indexUnique);

            $this -> pk_Category["id_backup"] = $category -> id_backup;
            $this -> pk_Category["id_account"] = $category -> id_account;
            $this -> pk_Category["id_category"] = $category -> id_category;
            $this -> pk_Category["name"] = $category -> name;
            $this -> pk_Category["sign"] = $category -> sign;

            $queryUpdateCategory = $this -> buscarCategoriesBackup(false);
            $arreglo["category"]["error"] = $queryUpdateCategory["error"];
            $arreglo["category"]["titulo"] = $queryUpdateCategory["titulo"];
            $arreglo["category"]["msj"] = $queryUpdateCategory["msj"];
            if (!$arreglo["category"]["error"]) $arreglo["category"]["update"] = $queryUpdateCategory["categories"][0];
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ CATEGORIA NO ACTUALIZADO !";
            $arreglo["msj"] = "Ocurrio un error al actualizar la categoria con " . $this -> keyValueArray($indexUnique);
        }
        return $arreglo;
    }
    public function eliminarCategoria() {
        $this -> verificarPermiso(PERMISO_DELETE);

        $indexUnique = json_decode(Form::getValue("indexUnique", false, false));
        $arreglo = array();
        $delete = $this -> c -> eliminar($indexUnique);
        if ($delete) {
            $arreglo["error"] = false;
            $arreglo["titulo"] = "¡ CATEGORIA ELIMINADA !";
            $arreglo["msj"] = "La categoria con " . $this -> keyValueArray($indexUnique) . " se ha eliminado correctamente";
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ CATEGORIA NO ELIMINADA !";
            $arreglo["msj"] = "Ocurrio un error al intentar eliminar la categoría con " . $this -> keyValueArray($indexUnique);
        }
        return $arreglo;
    }
}