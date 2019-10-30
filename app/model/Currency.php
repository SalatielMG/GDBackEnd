<?php
/**
 * Created by PhpStorm.
 * User: pc-01
 * Date: 21/08/2019
 * Time: 14:32
 */

class Currency extends DB
{
    public $nameTable = "backup_currencies";
    public $columnsTable = [
        ['name' => 'id_backup', 'type' => Form::typeInt],
        ['name' => 'iso_code', 'type' => Form::typeChar],
        ['name' => 'symbol', 'type' => Form::typeChar],
        ['name' => 'icon_name', 'type' => Form::typeVarchar],
        ['name' => 'selected', 'type' => Form::typeTinyint],
    ];

    public $nameTableSQLITE = "table_currencies";
    public $columnsTableSQLITE = [
        ["name" => "_id", "type" => Form::typeSQLITE_INTEGER],
        ["name" => "iso_code", "type" => Form::typeSQLITE_TEXT],
        ["name" => "symbol", "type" => Form::typeSQLITE_TEXT],
        ["name" => "icon", "type" => Form::typeSQLITE_TEXT],
        ["name" => "selected", "type" => Form::typeSQLITE_INTEGER],
    ];
    public $columnsTableIndexUnique = [];

    public $nameSheetXLSX = "currencies";
    public $columnsSheetXLSX = [
        ["name" => "iso_code", "column" => "A"],
        ["name" => "symbol", "column" => "B"],
    ];

    public function __construct()
    {
        parent::__construct();
        foreach ($this -> columnsTable as $key => $value) {
            if (($value["name"] == "id_backup")
            || ($value["name"] == "iso_code")
            //|| ($value["name"] == "symbol")
            ) {
                array_push($this -> columnsTableIndexUnique, $value);
            }
        }
    }

    public function mostrar($where = "1", $select = "*", $tabla = "backup_currencies"){
        return $this -> getDatos($tabla, $select, $where);
    }
    public function agregarCurrencies() {
        return $this -> insertMultipleData("table_currencies", $this -> Currencies) ;
    }
    public function agregar ($dataCurrency) {
        $currency = Valida::arrayDataOperation($this -> columnsTable, $dataCurrency);
        return $this -> insert($this -> nameTable, $currency);
    }
    public function actualizar ($dataCurrency, $indexUnique) {
        $currency = Valida::arrayDataOperation($this -> columnsTable, $dataCurrency, ["id_backup"]);
        return $this -> update($this -> nameTable, $currency, Valida::conditionVerifyExistsUniqueIndex($indexUnique, $this -> columnsTableIndexUnique, false));
    }
    public function eliminar ($indexUnique) {
        return $this -> delete($this -> nameTable, Valida::conditionVerifyExistsUniqueIndex($indexUnique, $this -> columnsTableIndexUnique, false));
    }
    public $Currencies = [
        ["_id" => 1,	"iso_code" => "'AED'", "symbol" => "'د.إ '", "icon" => "'flag_aed'", "selected" => 0],
        ["_id" => 2,	"iso_code" => "'AKZ'", "symbol" => "'Kz'", "icon" => "'flag_akz'", "selected" => 0],
        ["_id" => 3,	"iso_code" => "'AFS'", "symbol" => "'Af'", "icon" => "'flag_afs'", "selected" => 0],
        ["_id" => 4,	"iso_code" => "'ARS'", "symbol" => "'$'", "icon" => "'flag_ars'", "selected" => 0],
        ["_id" => 5,	"iso_code" => "'AUD'", "symbol" => "'$'", "icon" => "'flag_aud'", "selected" => 0],
        ["_id" => 6,	"iso_code" => "'AZN'", "symbol" => "'ман.'", "icon" => "'flag_azn'", "selected" => 0],
        ["_id" => 7,	"iso_code" => "'BAM'", "symbol" => "'KM'", "icon" => "'flag_bam'", "selected" => 0],
        ["_id" => 8,	"iso_code" => "'BDT'", "symbol" => "'৳'"	, "icon" => "'flag_bdt'", "selected" => 0],
        ["_id" => 9,	"iso_code" => "'BGN'", "symbol" => "'лв.'", "icon" => "'flag_bgn'", "selected" => 0],
        ["_id" => 10,	"iso_code" => "'BHD'", "symbol" => "'.د.ب'", "icon" => "'flag_bhd'", "selected" => 0],
        ["_id" => 11,	"iso_code" => "'BOB'", "symbol" => "'Bs.'", "icon" => "'flag_bob'", "selected" => 0],
        ["_id" => 12,	"iso_code" => "'BTC'", "symbol" => "'Ƀ'", "icon" => "'flag_btc'", "selected" => 0],
        ["_id" => 13,	"iso_code" => "'BRL'", "symbol" => "'R$'", "icon" => "'flag_brl'", "selected" => 0],
        ["_id" => 14,	"iso_code" => "'CAD'", "symbol" => "'C$'", "icon" => "'flag_cad'", "selected" => 0],
        ["_id" => 15,	"iso_code" => "'CHF'", "symbol" => "'Fr.'", "icon" => "'flag_chf'", "selected" => 0],
        ["_id" => 16,	"iso_code" => "'CLP'", "symbol" => "'$'", "icon" => "'flag_clp'", "selected" => 0],
        ["_id" => 17,	"iso_code" => "'CNY'", "symbol" => "'¥'", "icon" => "'flag_cny'", "selected" => 0],
        ["_id" => 18,	"iso_code" => "'COP'", "symbol" => "'$'", "icon" => "'flag_cop'", "selected" => 0],
        ["_id" => 19,	"iso_code" => "'CRC'", "symbol" => "'₡'"	, "icon" => "'flag_crc'", "selected" => 0],
        ["_id" => 20,	"iso_code" => "'CZK'", "symbol" => "'Kč'", "icon" => "'flag_czk'", "selected" => 0],
        ["_id" => 21,	"iso_code" => "'DKK'", "symbol" => "'kr'", "icon" => "'flag_dkk'", "selected" => 0],
        ["_id" => 22,	"iso_code" => "'DOP'", "symbol" => "'RD$'", "icon" => "'flag_dop'", "selected" => 0],
        ["_id" => 23,	"iso_code" => "'EGP'", "symbol" => "'ج.م'", "icon" => "'flag_egp'", "selected" => 0],
        ["_id" => 24,	"iso_code" => "'EUR'", "symbol" => "'€'", "icon" => "'flag_eur'", "selected" => 0],
        ["_id" => 25,	"iso_code" => "'GBP'", "symbol" => "'£'", "icon" => "'flag_gbp'", "selected" => 0],
        ["_id" => 26,	"iso_code" => "'GTQ'", "symbol" => "'Q'", "icon" => "'flag_gtq'", "selected" => 0],
        ["_id" => 27,	"iso_code" => "'HKD'", "symbol" => "'$'", "icon" => "'flag_hkd'", "selected" => 0],
        ["_id" => 28,	"iso_code" => "'HNL'", "symbol" => "'L'", "icon" => "'flag_huf'", "selected" => 0],
        ["_id" => 29,	"iso_code" => "'HRK'", "symbol" => "'kn'", "icon" => "'flag_hrk'", "selected" => 0],
        ["_id" => 30,	"iso_code" => "'HUF'", "symbol" => "'Ft'", "icon" => "'flag_huf'", "selected" => 0],
        ["_id" => 31,	"iso_code" => "'IDR'", "symbol" => "'Rp'", "icon" => "'flag_idr'", "selected" => 0],
        ["_id" => 32,	"iso_code" => "'ILS'", "symbol" => "'₪'"	, "icon" => "'flag_ils'", "selected" => 0],
        ["_id" => 33,	"iso_code" => "'INR'", "symbol" => "'₹'", "icon" => "'flag_inr'", "selected" => 0],
        ["_id" => 34,	"iso_code" => "'JPY'", "symbol" => "'¥'", "icon" => "'flag_jpy'", "selected" => 0],
        ["_id" => 35,	"iso_code" => "'KES'", "symbol" => "'KSh'", "icon" => "'flag_kes'", "selected" => 0],
        ["_id" => 36,	"iso_code" => "'KRW'", "symbol" => "'₩'"	, "icon" => "'flag_krw'", "selected" => 0],
        ["_id" => 37,	"iso_code" => "'KWD'", "symbol" => "'د.ك'", "icon" => "'flag_kwd'", "selected" => 0],
        ["_id" => 38,	"iso_code" => "'KZT'", "symbol" => "'T'", "icon" => "'flag_kzt'", "selected" => 0],
        ["_id" => 39,	"iso_code" => "'MAD'", "symbol" => "'درهم'", "icon" => "'flag_mad'", "selected" => 0],
        ["_id" => 40,	"iso_code" => "'LBP'", "symbol" => "'ل.ل.'" , "icon" => "'flag_lbp'", "selected" => 0],
        ["_id" => 41,	"iso_code" => "'LKR'", "symbol" => "'Rs'", "icon" => "'flag_lkr'", "selected" => 0],
        ["_id" => 42,	"iso_code" => "'MXN'", "symbol" => "'$'", "icon" => "'flag_mxn'", "selected" => 0],
        ["_id" => 43,	"iso_code" => "'MYR'", "symbol" => "'RM'", "icon" => "'flag_myr'", "selected" => 0],
        ["_id" => 44,	"iso_code" => "'NGN'", "symbol" => "'₦'"	, "icon" => "'flag_ngn'", "selected" => 0],
        ["_id" => 45,	"iso_code" => "'NIO'", "symbol" => "'C$'", "icon" => "'flag_nio'", "selected" => 0],
        ["_id" => 46,	"iso_code" => "'NOK'", "symbol" => "'kr'", "icon" => "'flag_nok'", "selected" => 0],
        ["_id" => 47,	"iso_code" => "'NZD'", "symbol" => "'$'", "icon" => "'flag_nzd'", "selected" => 0],
        ["_id" => 48,	"iso_code" => "'OMR'", "symbol" => "'ر.ع.'"	, "icon" => "'flag_omr'", "selected" => 0],
        ["_id" => 49,	"iso_code" => "'PAB'", "symbol" => "'$'", "icon" => "'flag_pab'", "selected" => 0],
        ["_id" => 50,	"iso_code" => "'PEN'", "symbol" => "'S/.'", "icon" => "'flag_pen'", "selected" => 0],
        ["_id" => 51,	"iso_code" => "'PHP'", "symbol" => "'₱'"	, "icon" => "'flag_php'", "selected" => 0],
        ["_id" => 52,	"iso_code" => "'PKR'", "symbol" => "'Rs'", "icon" => "'flag_pkr'", "selected" => 0],
        ["_id" => 53,	"iso_code" => "'PLN'", "symbol" => "'zł'", "icon" => "'flag_pln'", "selected" => 0],
        ["_id" => 54,	"iso_code" => "'PYG'", "symbol" => "'Gs.'", "icon" => "'flag_pyg'", "selected" => 0],
        ["_id" => 55,	"iso_code" => "'RON'", "symbol" => "'lei'", "icon" => "'flag_ron'", "selected" => 0],
        ["_id" => 56,	"iso_code" => "'RUB'", "symbol" => "'₽'", "icon" => "'flag_rub'", "selected" => 0],
        ["_id" => 57,	"iso_code" => "'RWF'", "symbol" => "'R₣'", "icon" => "'flag_rwf'", "selected" => 0],
        ["_id" => 58,	"iso_code" => "'SAR'", "symbol" => "'ر.س'", "icon" => "'flag_sar'", "selected" => 0],
        ["_id" => 59,	"iso_code" => "'SEK'", "symbol" => "'kr'", "icon" => "'flag_sek'", "selected" => 0],
        ["_id" => 60,	"iso_code" => "'SGD'", "symbol" => "'$'", "icon" => "'flag_sgd'", "selected" => 0],
        ["_id" => 61,	"iso_code" => "'SVC'", "symbol" => "'₡'"	, "icon" => "'flag_svd'", "selected" => 0],
        ["_id" => 62,	"iso_code" => "'THB'", "symbol" => "'฿'"	, "icon" => "'flag_thb'", "selected" => 0],
        ["_id" => 63,	"iso_code" => "'TND'", "symbol" => "'د.ت'", "icon" => "'flag_tnd'", "selected" => 0],
        ["_id" => 64,	"iso_code" => "'TRY'", "symbol" => "'₺'", "icon" => "'flag_try'", "selected" => 0],
        ["_id" => 65,	"iso_code" => "'TWD'", "symbol" => "'$'", "icon" => "'flag_twd'", "selected" => 0],
        ["_id" => 66,	"iso_code" => "'TZS'", "symbol" => "'Tsh'", "icon" => "'flag_tzs'", "selected" => 0],
        ["_id" => 67,	"iso_code" => "'UAH'", "symbol" => "'₴'", "icon" => "'flag_uah'", "selected" => 0],
        ["_id" => 68,	"iso_code" => "'USD'", "symbol" => "'$'", "icon" => "'flag_usd'", "selected" => 0],
        ["_id" => 69,	"iso_code" => "'UYU'", "symbol" => "'" . '$U' . "'", "icon" => "'flag_uyu'", "selected" => 0],
        ["_id" => 70,	"iso_code" => "'VEF'", "symbol" => "'Bs.'", "icon" => "'flag_vef'", "selected" => 0],
        ["_id" => 71,	"iso_code" => "'VND'", "symbol" => "'₫'", "icon" => "'flag_vnd'", "selected" => 0],
        ["_id" => 72,	"iso_code" => "'XOF'", "symbol" => "'CFA'", "icon" => "'flag_xof'", "selected" => 0],
        ["_id" => 73,	"iso_code" => "'ZAR'", "symbol" => "'R'", "icon" => "'flag_zar'", "selected" => 0],
    ];
}