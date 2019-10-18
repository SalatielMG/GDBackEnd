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
        $password = '12345';
        $hash = password_hash($password, PASSWORD_DEFAULT);
        return ['pasword' => $hash ];
    }

    public function backupBD(){
        $db_host = 'localhost';
        $db_name = 'gastos5_app';
        $db_user = 'root';
        $db_pass = 'w$cp@HCJ';
// $date = new DateTime();

// $dump = "mysqldump -h$db_host -u$db_user -p$db_pass --opt $db_name | gzip -c > MiRespaldo`date +%Y%m%d_%H%M%S`.sql.gz";
        $dump = "mysqldump -h$db_host -u$db_user -p$db_pass --opt --where='1 limit 1000' $db_name > MiRespaldo.sql";

        system($dump, $output);
    }
}

