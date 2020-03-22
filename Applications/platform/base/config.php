<?php
// +----------------------------------------------------------------------
// | Author: Onine
// +----------------------------------------------------------------------
use \Workerman\Worker;

$envFile = __DIR__ .'/../../../env.ini';
if(file_exists($envFile)){
    $envConfigure = parse_ini_file($envFile, TRUE);
    define('ENVFILE', $envFile);
}else{
    echo 'System configure error.';
    Worker::stopAll();
}

define('MYSQL_HOST', $envConfigure['COMMON']['MYSQL_HOST']);
define('MYSQL_PORT', $envConfigure['COMMON']['MYSQL_PORT']);
define('MYSQL_USER', $envConfigure['COMMON']['MYSQL_USER']);
define('MYSQL_PASSWORD', $envConfigure['COMMON']['MYSQL_PASSWORD']);
define('MYSQL_DATABASE', $envConfigure['COMMON']['MYSQL_DATABASE']);

define('TIME_INTERVAL', $envConfigure['ENVIRONMENT']['TIME_INTERVAL']);

