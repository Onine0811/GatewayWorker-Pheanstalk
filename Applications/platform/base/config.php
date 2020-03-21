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
    die('System configure error.');
    Worker::stopAll();
}

