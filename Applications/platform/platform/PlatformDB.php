<?php

require_once __DIR__ . '/../../../vendor/mysql-master/src/Connection.php';
require_once __DIR__ . '/PlayerManager.php';
require_once __DIR__ . '/HttpManager.php';

class PlatformDB
{

    //静态变量保存全局实例
    private static $_instance = null;
    //私有构造函数，防止外界实例化对象
    private function __construct() {
    }
    //私有克隆函数，防止外办克隆对象
    private function __clone() {
    }
    //静态方法，单例统一访问入口
    static public function getInstance() {
        if (is_null ( self::$_instance ) || isset ( self::$_instance )) {
            self::$_instance = new self ();
        }
        return self::$_instance;
    }


    private static $db = null;


    public function initDB(){
        if(self::$db == null){
            echo "[Platform DB success]\n";
            self::$db = new \Workerman\MySQL\Connection('127.0.0.1', '3306', 'root', 'q%G2@QK4', 'test');
        }
    }

}
