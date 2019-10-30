<?php
/**
 * Created by PhpStorm.
 * User: pc-hp
 * Date: 29/10/2019
 * Time: 11:01 PM
 */

require APP_UTIL.'librerias/PHPOffice/vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

new Reporte();

class Reporte
{
    public function __construct()
    {
        $this -> exportXLS();
    }

    private function exportXLS(){
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'iso_code');
        $sheet->setCellValue('B1', 'symbol');
        $sheet->setCellValue('C1', 'icon');
        $sheet->setCellValue('D1', 'selected');
        /*foreach ($currencies as $key => $value) {
            $sheet->setCellValue('A' . ($key + 2), $value["iso_code"]);
            $sheet->setCellValue('B' . ($key + 2), $value["symbol"]);
            $sheet->setCellValue('C' . ($key + 2), $value["icon"]);
            $sheet->setCellValue('D' . ($key + 2), $value["selected"]);
        }*/
        $filename = 'sample-'.time().'.xlsx';
// Redirect output to a client's web browser (Xlsx)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');
// If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

// If you're serving to IE over SSL, then the following may be needed
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
    }

}