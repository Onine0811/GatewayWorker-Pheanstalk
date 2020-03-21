<?php
define("GET_QRCODE_URL", "http://www.huangfeng6.top/chargeadmin.php/Myqrcode/index");    //获取二维码


class HttpManager
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

    public function getQrcode($invitecode)
    {
        $url = GET_QRCODE_URL;

        //post数据
        $data['invitecode'] = $invitecode;

        $res = $this->httpPostWithoutHead($data,$url);

        return $res;
    }


    public function httpPost($data = array(),$url){

        $signature = $this->getSignature($data);
        $headers = $this->getHeader($data['timestamp'],$signature);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        // post数据
        curl_setopt($ch, CURLOPT_POST, 1);
        // post的变量
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }

    public function httpPostWithoutHead($data = array(),$url){

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // post数据
        curl_setopt($ch, CURLOPT_POST, 1);
        // post的变量
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }

}
