<?php
require_once __DIR__ . '/MessageDefine.php';
require_once __DIR__ . '/Platform.php';
use \GatewayWorker\Lib\Gateway;
class MaintainManager
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

    public function onSocketMessage($client_id,$MainID,$msg){
        switch ($MainID){
            case 1:
                $this->checkStatus($client_id,$msg);
                break;
            default:
                break;
        }
    }

    public function checkStatus($client_id,$msg){
        $status = Platform::$pheanstalk->statsTube($msg);
        Gateway::sendToClient($client_id, json_encode($status));
    }
}
