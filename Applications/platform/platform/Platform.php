<?php
// 自动加载类
require_once __DIR__ . '/PlatformDB.php';
require_once __DIR__ . '/PlayerManager.php';
use Workerman\Worker;
use Pheanstalk\Pheanstalk;
use Workerman\Lib\Timer;

class Platform
{

    //静态变量保存全局实例
    private static $_instance = null;
    //私有构造函数，防止外界实例化对象
    private function __construct() {
        $this->pheanstalk = new Pheanstalk('127.0.0.1',11300);
        $this->tubeName =  self::$pheanstalkQueue[self::$_workerId];
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
    // 初始化连接队列
    private $pheanstalk;
    // 处理队列名称
    private $tubeName;
    // 静态数组变量保存各进程完成的异步队列
    private static $pheanstalkQueue = array(
        0 => 'user_eamil_message_list',
        1 => 'sql',
        2 => 'php',
        3 => 'http',
    );
    // 当前进程ID
    public static $_workerId;
    // 当前处理管道信息定时器
    public $time_id;

    // 检查管道是否拥有待处理数据
    public function checkQueue(){
        // 获取当前的管道
        $name = $this->pheanstalk->listTubes();
        if (!in_array($this->tubeName,$name)){
            return;
        }
        // 获取管道状态
        $status = $this->pheanstalk->statsTube($this->tubeName);
        // 管道中拥有待处理则进行
        if ($status['current-jobs-ready'] > 0){
            $this->time_id = Timer::add(0.1, function()
            {
                $this->takeoutQueue();
            },[],true);
        }
    }

    /**
     * type => 1:sql;2:http;3:php
     * implement => 可执行内容
     * delay => 延迟时间
     */
    // 取出管道中待处理数据
    public function takeoutQueue(){
        $job = $this->pheanstalk->watch($this->tubeName)->ignore('default')->reserve();
        $data = json_decode($job->getData());
        switch ($data['type']){
            case 1:
                $func = 'sqlQueue';
                break;
            case 2:
                $func = 'httpQueue';
                break;
            case 3:
                $func = 'phpQueue';
                break;
            default:
                return;
        }
        if ($data['delay'] == 0){
            $this->$func($data['implement']);
        }else{
            $this->time_id = Timer::add($data['delay'], function() use ($func,$data)
            {
                $this->$func($data['implement']);
            },[],false);
        }
    }

    // 执行sql
    public function sqlQueue($implement){

    }
    // 执行http curl请求
    public function httpQueue($implement){

    }
    // 执行命令行请求
    public function phpQueue($implement){

    }

}
