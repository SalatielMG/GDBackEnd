<?php
/**
 * Created by PhpStorm.
 * User: pc-hp
 * Date: 13/08/2019
 * Time: 12:15 AM
 */

$db_host = 'localhost';
$db_name = 'gastos5_app';
$db_user = 'root';
$db_pass = '';
$date = new DateTime();

// $dump = "mysqldump -h$db_host -u$db_user -p$db_pass --opt --where='1 limit 1000' $db_name | gzip -c > MiRespaldo.sql.gz";
$dump = "mysqldump -h$db_host -u$db_user -p$db_pass --opt --where='1 limit 1000' $db_name > MiRespaldo_".$date->getTimestamp().".sql";

system($dump, $output);