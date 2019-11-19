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
            $arreglo["titulo"] = "¡ Categories encontrados !";
            $arreglo["msj"] = "Se encontraron categorias con " . $this -> keyValueArray($this -> pk_Category);
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ Categories no encontrados !";
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
        if ($isExport)
            if ($typeExport == "sqlite")
                $this -> select = "bc.id_category as _id, (SELECT nameAccount(" . $this -> pk_Category["id_backup"] . ", bc.id_account)) AS account, bc.name as category, bc.sign, bc.icon_name as icon, number, 0 as selected";
            else
                $this -> select = "(SELECT nameAccount(" . $this -> pk_Category["id_backup"] . ", bc.id_account)) AS account, bc.name as category, bc.sign";
        else
            $this -> select = "bc.*, (SELECT nameAccount(" . $this -> pk_Category["id_backup"] . ", bc.id_account)) AS nameAccount, COUNT(bc.id_backup) as repeated";

        $this -> table = $this -> c -> nameTable . " bc";
        $this -> where = (($isQuery || $isExport) ? "bc.id_backup = " . $this -> pk_Category["id_backup"] : $this -> conditionVerifyExistsUniqueIndex($this -> pk_Category, $this -> c -> columnsTableIndexUnique, false, "bc.") . " AND bc.id_category = " . $this -> pk_Category["id_category"]) . " GROUP BY " . $this -> namesColumns($this -> c -> columnsTableIndexUnique, "bc."). " HAVING COUNT( * ) >= 1 ORDER BY bc.id_category " . (($isQuery) ? "limit $this->pagina,$this->limit" : "");

        $arreglo["consultaSQL"] = $this -> consultaSQL($this -> select , $this -> table, $this -> where);
        //return $arreglo;
        $select = $this -> c -> mostrar($this -> where, $this -> select, $this -> table);
        $arreglo = array();
        if ($select) {
            $arreglo["error"] = false;
            $arreglo["categories"] = $select;
            $arreglo["titulo"] = ($isQuery) ? "¡ Categories encontradas !" : "¡ Category encontrado !";
            $arreglo["msj"] = (($isQuery) ? "Se encontraron categorias con " : "Se encontro la categoría con ") . $this -> keyValueArray($this -> pk_Category);
            if ($isQuery && $this -> pagina == 0) {
                $this -> ctrlAccount = new ControlAccount($this -> pk_Category["id_backup"]);
                $arreglo["accountsBackup"] = $this -> ctrlAccount -> obtAccountsBackup(false);
            }
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = ($isQuery) ? "¡ Categories no encontradas !" : "¡ Category no encontrado !";
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
        $this -> select = "bc.*, (SELECT nameAccount(bc.id_backup, bc.id_account)) AS nameAccount, COUNT(bc.id_backup) as repeated";
         $this -> table = $this -> c -> nameTable . " bc, backups b";
        $this -> where = "b.id_backup = bc.id_backup ". $this -> condicionarConsulta($data -> id, "b.id_user", 0) . $this -> inBackups($backups, "bc.id_backup") . " GROUP BY ". $this -> namesColumns($this -> c -> columnsTableIndexUnique, "bc.") ." HAVING COUNT( * ) >= $this->having_Count limit $this->pagina , $this->limit_Inconsistencia";
        $arreglo["consultaSQL"] = $this -> consultaSQL($this -> select, $this -> table, $this -> where);
        $consulta = $this -> c -> mostrar($this -> where, $this -> select, $this -> table);
        if ($consulta) {
            $arreglo["error"] = false;
            $arreglo["categories"] = $consulta;
            $arreglo["titulo"] = "¡ Inconsitencias encontrados !";
            $arreglo["msj"] = "Se encontraron inconsistencias de registros en la tabla Category ". (($data -> email != "Generales") ? "del usuario: $data->email" : "");
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ Inconsitencias no encontrados !";
            $arreglo["msj"] = "No se encontraron inconsistencias de registros en la tabla Category ". (($data -> email != "Generales") ? "del usuario: $data->email" : "");
        }
        return $arreglo;
    }
    public function obtSizeTable() {
        $this -> verificarPermiso(PERMISO_MNTINCONSISTENCIA);
        $arreglo = array();
        $exixstIndexUnique = $this -> c -> verifyIfExistsIndexUnique($this -> c -> nameTable);
        if ($exixstIndexUnique["indice"]) {
            return $arreglo = $exixstIndexUnique;
        }
        $size = $this -> c -> sizeTable($this -> c -> nameTable);
        if ($size) {
            $arreglo["size"] = $size[0];
            $arreglo["error"] = false;
            $arreglo["titulo"] = "¡ Tamaño calculado !";
            $arreglo["msj"] = "Se calculo correctamente el tamaño de la tabla de datos: " . $this -> c -> nameTable;
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ Tamaño no calculado !";
            $arreglo["msj"] = "No se pudo calcular correctamente el tamaño de la tabla de datos: " . $this -> c -> nameTable;
        }
        return $arreglo;
    }
    public function corregirInconsistenciaRegistro() {
        $this -> verificarPermiso(PERMISO_MNTINCONSISTENCIA);

        $indexUnique = json_decode(Form::getValue("indexUnique", false, false));
        $arreglo = array();
        $this -> pk_Category["id_backup"] = $indexUnique -> id_backup;
        $this -> pk_Category["id_category"] = $indexUnique -> id_category;
        $this -> pk_Category["id_account"] = $indexUnique -> id_account;
        $this -> pk_Category["name"] = $indexUnique -> name;
        $this -> pk_Category["sign"] = $indexUnique -> sign;
        $category = $this -> buscarCategoriesBackup(false);
        if ($category) {
            $correcion = $this -> c -> eliminar($indexUnique);
            if ($correcion) {
                $insertCategory = $this -> c -> agregar($category["categories"][0]);
                if ($insertCategory) {
                    $arreglo["error"] = false;
                    $arreglo["titulo"] = "¡ Categoria corregida !";
                    $arreglo["msj"] = "Se corrigio correctamente la Categoria con " . $this -> keyValueArray($this -> pk_Category);
                    $arreglo["category"] = $this -> buscarCategoriesBackup(false);
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
            $arreglo["msj"] = "Error al obtner los datos de la Categoria seleccionada para corregir";
        }
        return $arreglo;
    }
    public function corregirInconsitencia() {
        $this -> verificarPermiso(PERMISO_MNTINCONSISTENCIA);

        $arreglo = array();
        $exixstIndexUnique = $this -> c -> verifyIfExistsIndexUnique($this -> c -> nameTable);
        if ($exixstIndexUnique["indice"]) {
            return $arreglo = $exixstIndexUnique;
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
            $arreglo["titulo"] = "¡ Id Category calculado !";
            $arreglo["msj"] = "Se calculo correctamente la nueva id_category del respaldo con id_backup: " . $this -> pk_Category["id_backup"];
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ Id Category no calculado !";
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
                $arreglo["titulo"] = "¡ Registro existente !";
                $arreglo["msj"] = "NO se puede " . (($isUpdate) ? "actualizar la" : "registrar la nueva") . " Categoria, puesto que ya existe un registro en la BD con el mismo ID_CATEGORY del mismo backup. Porfavor verifique el id e intente cambiarlo";
                return $arreglo;
            }
        }

        $arreglo["sqlVerfiyIndexUnique"] = $this -> conditionVerifyExistsUniqueIndex($newCategory, $this -> c -> columnsTableIndexUnique);
        $result = $this -> c -> mostrar($arreglo["sqlVerfiyIndexUnique"]);
        if ($result) {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ Registro existente !";
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
            $arreglo["titulo"] = "¡ Categoria agregado !";
            $arreglo["msj"] = "Se agrego correctamente la categoria con " . $this -> keyValueArray($this -> pk_Category);

        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ Categoria no agregado !";
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
            $arreglo["titulo"] = "¡ Categoria actualizada !";
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
            $arreglo["titulo"] = "¡ Categoria no actualizada !";
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
            $arreglo["titulo"] = "¡ Categoria eliminada !";
            $arreglo["msj"] = "La categoria con " . $this -> keyValueArray($indexUnique) . " se ha eliminado correctamente";
        } else {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ Categoria no eliminada !";
            $arreglo["msj"] = "Ocurrio un error al intentar eliminar la categoría con " . $this -> keyValueArray($indexUnique);
        }
        return $arreglo;
    }
}