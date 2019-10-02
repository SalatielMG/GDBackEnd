<?php
/**
 * Created by PhpStorm.
 * User: pc-hp
 * Date: 17/08/2019
 * Time: 12:36 PM
 */

class Backup extends DB
{
    public function mostrar($where = "1", $select = "*", $tabla = "backups"){
        return $this -> getDatos($tabla, $select, $where);
    }
    public function actualizar($data) {
        $backup = [
            "automatic" => $data -> automatic,
            "date_creation" => "'$data->date_creation'",
            //"date_download" => "'$data->date_download'",
            "created_in" => "'$data->created_in'"
        ];
        if ($data -> date_download != "0000-00-00 00:00:00") $backup["date_download"] = "'$data->date_download'";
        return $this -> update("backups", $backup, "id_backup = $data->id_backup");
    }
    public function eliminar($id) {
        return $this -> delete("backups", "id_backup = $id");
    }
    public function eliminarBackupUser($where) {
        return $this -> delete("backups", $where);

    }
}