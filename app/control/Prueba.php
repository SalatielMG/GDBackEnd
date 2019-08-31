<?php
/**
 * Created by PhpStorm.
 * Users: pc-01
 * Date: 15/08/2019
 * Time: 10:50
 */

class Prueba
{
    /**
     * @return array
     */
    public function encriptacion() {
        $password = '1234';
        $hash = password_hash($password, PASSWORD_DEFAULT, ['cost' => 15]);
        return ['pasword' => $hash ];
    }
}

