<?php
/**
 * Created by PhpStorm.
 * User: pc-01
 * Date: 28/10/2019
 * Time: 11:16 AM
 */

class DBSQLITE
{
    private $db = null;
    private $result;
    public function __construct()
    {
        if (empty($this -> db)) {
            try {
                $this -> db = new PDO('sqlite:' . APP_PATH . 'exports/database.sqlite');
                $this -> db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this -> db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
            } catch (Exception $e) {
                die($e -> getMessage());
                // exit();
            }
        }
    }
    public  function getInstance() {
        return $this -> db;
    }
    public  function generateSchema() {
        $command = '
                DROP TABLE IF EXISTS "table_movements";
                CREATE TABLE IF NOT EXISTS "table_movements" (
                    "_id"	INTEGER PRIMARY KEY AUTOINCREMENT,
                    "account"	TEXT,
                    "category"	TEXT,
                    "amount"	REAL,
                    "sign"	TEXT,
                    "detail"	TEXT,
                    "date"	TEXT,
                    "time"	TEXT,
                    "confirmed"	INTEGER,
                    "transfer"	INTEGER,
                    "date_idx"	TEXT,
                    "day"	TEXT,
                    "week"	TEXT,
                    "fortnight"	TEXT,
                    "month"	TEXT,
                    "year"	TEXT,
                    "code"	TEXT,
                    "picture"	TEXT,
                    "iso_code"	TEXT,
                    "selected"	INTEGER
                );
                DROP TABLE IF EXISTS "table_currencies";
                CREATE TABLE IF NOT EXISTS "table_currencies" (
                    "_id"	INTEGER PRIMARY KEY AUTOINCREMENT,
                    "iso_code"	TEXT,
                    "symbol"	TEXT,
                    "icon"	TEXT,
                    "selected"	INTEGER
                );
                DROP TABLE IF EXISTS "table_cardviews";
                CREATE TABLE IF NOT EXISTS "table_cardviews" (
                    "_id"	INTEGER PRIMARY KEY AUTOINCREMENT,
                    "id_card"	INTEGER,
                    "name"	TEXT,
                    "period"	TEXT,
                    "sign"	TEXT,
                    "show"	TEXT,
                    "number"	INTEGER
                );
                DROP TABLE IF EXISTS "table_categories";
                CREATE TABLE IF NOT EXISTS "table_categories" (
                    "_id"	INTEGER PRIMARY KEY AUTOINCREMENT,
                    "account"	TEXT,
                    "category"	TEXT,
                    "sign"	TEXT,
                    "icon"	TEXT,
                    "number"	INTEGER,
                    "selected"	INTEGER
                );
                DROP TABLE IF EXISTS "table_budgets";
                CREATE TABLE IF NOT EXISTS "table_budgets" (
                    "_id"	INTEGER PRIMARY KEY AUTOINCREMENT,
                    "account"	TEXT,
                    "category"	TEXT,
                    "period"	INTEGER,
                    "amount"	REAL,
                    "budget"	REAL,
                    "initial_date"	TEXT,
                    "final_date"	TEXT,
                    "show"	TEXT,
                    "number"	INTEGER,
                    "selected"	INTEGER
                );
                DROP TABLE IF EXISTS "table_automatics";
                CREATE TABLE IF NOT EXISTS "table_automatics" (
                    "_id"	INTEGER PRIMARY KEY AUTOINCREMENT,
                    "account"	TEXT,
                    "title"	TEXT,
                    "period"	INTEGER,
                    "each"	INTEGER,
                    "repeat"	INTEGER,
                    "counter"	INTEGER,
                    "initial_date"	TEXT,
                    "next_date"	TEXT,
                    "code"	TEXT,
                    "category"	TEXT,
                    "amount"	REAL,
                    "sign"	TEXT,
                    "detail"	TEXT,
                    "enabled"	INTEGER,
                    "selected"	INTEGER
                );
                DROP TABLE IF EXISTS "table_accounts";
                CREATE TABLE IF NOT EXISTS "table_accounts" (
                    "_id"	INTEGER PRIMARY KEY AUTOINCREMENT,
                    "account"	TEXT,
                    "detail"	TEXT,
                    "initial_balance"	REAL,
                    "sign"	TEXT,
                    "icon"	TEXT,
                    "income"	REAL,
                    "expense"	REAL,
                    "balance"	REAL,
                    "month"	TEXT,
                    "year"	TEXT,
                    "negative_max"	TEXT,
                    "positive_max"	TEXT,
                    "iso_code"	TEXT,
                    "rate"	REAL,
                    "include_total"	INTEGER,
                    "value_type"	INTEGER,
                    "selected"	INTEGER
                );
                DROP TABLE IF EXISTS "table_preferences";
                CREATE TABLE IF NOT EXISTS "table_preferences" (
                    "_id"	INTEGER PRIMARY KEY AUTOINCREMENT,
                    "key"	TEXT,
                    "value"	TEXT
                );';
        try {
            $this -> db -> exec($command);
        } catch (PDOException $e) {
            die($e -> getMessage());
            exit();
        }
    }
    private function solicitud($sql){
        try{
            $this -> result = $this -> db -> query($sql);
            //$this -> db = null;
            if($this -> result)
                return true;
            else{
                $this -> result = null;
                return false;
            }
        }catch (Exception $e){
            echo $e;
            exit();
        }
    }
    public function insertMultipleData($table, $arreglo, $columnsTable) {
        $sql = "INSERT INTO $table ";
        foreach ($arreglo as $key => $value) {
            $value = (array) $value;
            if ($key == 0) $sql .= $this -> validateKeyNameDataInSQLITE($columnsTable) . " VALUES ";
            $sql .= $this -> validateKeyValueDataInSQLITE($value, $columnsTable, $table);
        }
        $sql = substr_replace($sql, ";", strlen($sql) - 1);
        //return $sql;
        return $this -> solicitud($sql);
    }
    private function validateKeyNameDataInSQLITE($columnsTable) {
        $columns = "(";
        foreach ($columnsTable as $key => $value){
            $columns .= $value["name"] . ",";
        }
        $columns = substr_replace($columns, ")", strlen($columns) - 1);
        return $columns;
    }
    private function validateKeyValueDataInSQLITE($Value, $columsTable, $table) {
        $sql = "";
        $Value = (array) $Value;
        $sql .= "(";
        foreach ($columsTable as $key => $value) {
            if ($value["type"] == Form::typeSQLITE_INTEGER
            || $value["type"] == Form::typeSQLITE_REAL) {
                switch ($value["name"]) {
                    case "_id":
                        $sql .= (($table == "table_categories" || $table == "table_automatics" || $table == "table_accounts") ? $Value[$value["name"]]: "null") . ",";
                        break;
                    default:
                        $sql .= ((empty($Value[$value["name"]])) ? 0 : $Value[$value["name"]]) . ",";
                        break;
                }
            } else {
                $key = "";
                switch ($value["name"]) {
                    case "show":
                        $key = "show_item";
                        break;
                    case "each":
                        $key = "each_number";
                        break;
                    case "repeat":
                        $key = "repeat_number";
                        break;
                    case "key":
                        $key = "key_name";
                        break;
                    default:
                        $key = $value["name"];
                        break;
                }
                $sql .= "'" . $Value[$key] . "',";
            }
        }
        $sql = substr_replace($sql, "", strlen($sql) - 1);
        $sql .= "),";
        return $sql;
    }
}