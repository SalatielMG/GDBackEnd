<?php
/**
 * Created by PhpStorm.
 * User: pc-01
 * Date: 30/10/2019
 * Time: 10:09 AM
 */
require_once (APP_PATH . "control/ControlMovement.php");
require_once (APP_PATH . "control/ControlCurrency.php");
require_once (APP_PATH . "control/ControlCategory.php");
require_once (APP_PATH . "control/ControlBudget.php");
require_once (APP_PATH . "control/ControlAutomatic.php");
require_once (APP_PATH . "control/ControlAccount.php");
require (APP_UTIL . "librerias/PHPOffice/vendor/autoload.php");
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;


class ExportXLSX
{
    private $id_backup;

    public function __construct($id_backup)
    {
        $this -> id_backup = $id_backup;
    }

    public function xlsxExport() {
        $arreglo = array();
        $srcfile = APP_UTIL . 'plantillas/template-report.xlsx';
        $dstfile = 'exports/Reporte.xlsx';
        if (!copy($srcfile, $dstfile)) {
            $arreglo["error"] = true;
            $arreglo["titulo"] = "¡ ERROR AL GENERAR LA PLANTILLA EXCEL !";
            $arreglo["msj"] = "Ocurrio un error al intentar crear la plantilla base del Reporte en Excel desde el Servidor";
            return $arreglo;
        }
        $error = 0;
        $rutaArchivo = $dstfile;
        $documento = IOFactory::load($rutaArchivo);

        // ------------------------------ Insert Accounts ------------------------------ //
        $control = new ControlAccount($this -> id_backup);
        $query = $control ->  buscarAccountsBackup(false, true, "xlsx");
        if (!$query["error"]) {
            $insert = $this -> insertDatainXLSX($query["accounts"], $control -> getAccountModel() -> columnsSheetXLSX, $documento -> getSheetByName($control -> getAccountModel() -> nameSheetXLSX));
            if ($insert["error"]){
                $arreglo["errorInsert"][$error]["error"] = true;
                $arreglo["errorInsert"][$error]["titulo"] = "";
                $arreglo["errorInsert"][$error]["msj"] = "Ocurrio un error al intentar ingresar los registros en la hoja: \"" . $control -> getAccountModel() -> nameSheetXLSX . "\" del archivo Reporte.xlsx\nMessage Error => " . $insert["msj"];
                $error++;
            }
        }
        // ------------------------------ Insert Accounts ------------------------------ //

        // ------------------------------ Insert Automatics ------------------------------ //
        $control = new ControlAutomatic($this -> id_backup);
        $query = $control ->  buscarAutomaticsBackup(false, true, "xlsx");
        if (!$query["error"]) {
            $insert = $this -> insertDatainXLSX($query["automatics"], $control -> getAutomaticModel() -> columnsSheetXLSX,  $documento -> getSheetByName($control -> getAutomaticModel() -> nameSheetXLSX));
            if ($insert["error"]){
                $arreglo["errorInsert"][$error]["error"] = true;
                $arreglo["errorInsert"][$error]["titulo"] = "";
                $arreglo["errorInsert"][$error]["msj"] = "Ocurrio un error al intentar ingresar los registros en la hoja: \"" . $control -> getAutomaticModel() -> nameSheetXLSX . "\" del archivo Reporte.xlsx\nMessage Error => " . $insert["msj"];
                $error++;
            }
        }
        // ------------------------------ Insert Automatics ------------------------------ //

        // ------------------------------ Insert Budgets ------------------------------ //
        $control = new ControlBudget($this -> id_backup);
        $query = $control ->  buscarBudgetsBackup(false, true, "xlsx");
        if (!$query["error"]) {
            $insert = $this -> insertDatainXLSX($query["budgets"], $control -> getBudgetModel() -> columnsSheetXLSX, $documento -> getSheetByName($control -> getBudgetModel() -> nameSheetXLSX));
            if ($insert["error"]){
                $arreglo["errorInsert"][$error]["error"] = true;
                $arreglo["errorInsert"][$error]["titulo"] = "";
                $arreglo["errorInsert"][$error]["msj"] = "Ocurrio un error al intentar ingresar los registros en la hoja: \"" . $control -> getBudgetModel() -> nameSheetXLSX . "\" del archivo Reporte.xlsx\nMessage Error => " . $insert["msj"];
                $error++;
            }
        }
        // ------------------------------ Insert Budgets ------------------------------ //

        // ------------------------------ Insert Categories ------------------------------ //
        $control = new ControlCategory($this -> id_backup);
        $query = $control ->  buscarCategoriesBackup(false, true, "xlsx");
        if (!$query["error"]) {
            $insert = $this -> insertDatainXLSX($query["categories"], $control -> getCategoryModel() -> columnsSheetXLSX, $documento -> getSheetByName($control -> getCategoryModel() -> nameSheetXLSX));
            if ($insert["error"]){
                $arreglo["errorInsert"][$error]["error"] = true;
                $arreglo["errorInsert"][$error]["titulo"] = "";
                $arreglo["errorInsert"][$error]["msj"] = "Ocurrio un error al intentar ingresar los registros en la hoja: \"" . $control -> getCategoryModel() -> nameSheetXLSX . "\" del archivo Reporte.xlsx\nMessage Error => " . $insert["msj"];
                $error++;
            }
        }
        // ------------------------------ Insert Categories ------------------------------ //

        // ------------------------------ Insert Currencies ------------------------------ //
        $control = new ControlCurrency($this -> id_backup);
        $query = $control ->  obtCurrenciesGralBackup(false, true, "xlsx");
        if (!$query["error"]) {
            $insert = $this -> insertDatainXLSX($query["currencies"], $control -> getCurrencyModel() -> columnsSheetXLSX, $documento -> getSheetByName($control -> getCurrencyModel() -> nameSheetXLSX));
            if ($insert["error"]){
                $arreglo["errorInsert"][$error]["error"] = true;
                $arreglo["errorInsert"][$error]["titulo"] = "";
                $arreglo["errorInsert"][$error]["msj"] = "Ocurrio un error al intentar ingresar los registros en la hoja: \"" . $control -> getCurrencyModel() -> nameSheetXLSX . "\" del archivo Reporte.xlsx\nMessage Error => " . $insert["msj"];
                $error++;
            }
        }
        // ------------------------------ Insert Currencies ------------------------------ //

        // ------------------------------ Insert Movements ------------------------------ //
        $control = new ControlMovement($this -> id_backup);
        $query = $control ->  buscarMovementsBackup(false, true, "xlsx");
        if (!$query["error"]) {
            $insert = $this -> insertDatainXLSX($query["movements"], $control -> getMovementModel() -> columnsSheetXLSX, $documento -> getSheetByName($control -> getMovementModel() -> nameSheetXLSX));
            if ($insert["error"]){
                $arreglo["errorInsert"][$error]["error"] = true;
                $arreglo["errorInsert"][$error]["titulo"] = "";
                $arreglo["errorInsert"][$error]["msj"] = "Ocurrio un error al intentar ingresar los registros en la hoja: \"" . $control -> getMovementModel() -> nameSheetXLSX . "\" del archivo Reporte.xlsx\nMessage Error => " . $insert["msj"];
                $error++;
            }
        }
        // ------------------------------ Insert Movements ------------------------------ //

        if ($error == 0) {
            $writer = new Xlsx($documento);
            $writer -> save($rutaArchivo);
            $arreglo["error"] = false;
            $arreglo["titulo"] = "¡ EXPORTACIÓN TERMINADA !";
            $arreglo["msj"] = "Se creo correctamente el archivo XLSX del Respaldo con id_backup: " . $this -> id_backup;
        } else {
            $arreglo["error"] = "warning";
            $arreglo["titulo"] = "¡ EXPORTACIÓN NO TERMINADA !";
            $arreglo["msj"] = "Ocurrieron errores al crear el archivo XLSX del Respaldo con id_backup: " . $this -> id_backup;
        }
        return $arreglo;
    }
    private function insertDatainXLSX($dataInsert, $columnsSheet, $sheetCurrent) {
        $arreglo["error"] = false;
        try {
            foreach ($dataInsert as $key => $value) {
                $value = (array) $value;
                foreach ($columnsSheet as $k => $v) {
                    $index = "";
                    switch ($v["name"]) {
                        case "each":
                            $index = "each_number";
                            break;
                        case "repeat":
                            $index = "repeat_number";
                            break;
                        default:
                            $index = $v["name"];
                            break;
                    }
                    $sheetCurrent -> setCellValue($v["column"] . ($key + 5), $value[$index]);
                }
            }
        } catch (Exception $e) {
            $arreglo["error"] = true;
            $arreglo["msj"] = $e -> getMessage();
            return $arreglo;
        }
        return $arreglo;
    }
}