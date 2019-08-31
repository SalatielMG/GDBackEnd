<?php
/**
 * Created by PhpStorm.
 * User: pc-01
 * Date: 21/08/2019
 * Time: 12:39
 */

class CardView extends DB
{
    public function mostrar($where = "1", $select = "*", $tabla = "backup_cardviews"){
        return $this -> getDatos($tabla, $select, $where);
    }
}