<?php
/**
 * Created by PhpStorm.
 * User: pc-hp
 * Date: 16/09/2019
 * Time: 01:35 PM
 */
Ruta::post("agregarCategoria","ControlCategory@agregarCategoria");
Ruta::post("actualizarCategoria","ControlCategory@actualizarCategoria");
Ruta::delete("eliminarCategoria","ControlCategory@eliminarCategoria");
Ruta::get("obtNewId_Category","ControlCategory@obtNewId_Category");
Ruta::get("obtCategoriesAccountBackup","ControlCategory@obtCategoriesAccountBackup");
Ruta::get("buscarCategoriesBackup","ControlCategory@buscarCategoriesBackup");
Ruta::get("buscarInconsistenciaDatosCategories","ControlCategory@inconsistenciaCategory");
Ruta::get("obtSizeTablecategories","ControlCategory@obtSizeTable");
Ruta::get("corregirInconsistenciaDatoscategories","ControlCategory@corregirInconsitencia");