<?php
// 自动加载类
use Workerman\Worker;
use Pheanstalk\Pheanstalk;
use Workerman\Lib\Timer;

class Platform
{

    //静态变量保存全局实例
    private static $_instance = null;
    //私有构造函数，防止外界实例化对象
    private function __construct() {
        self::$pheanstalk = new Pheanstalk('127.0.0.1',11300);
        self::$tubeName =  self::$pheanstalkQueue[self::$_workerId];
        self::$db = new \Workerman\MySQL\Connection(MYSQL_HOST, MYSQL_PORT, MYSQL_USER, MYSQL_PASSWORD, MYSQL_DATABASE);
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
    public static $pheanstalk;
    // 处理队列名称
    public static $tubeName;
    // 数据库连接
    private static $db = null;
    // 静态数组变量保存各进程完成的异步队列
    private static $pheanstalkQueue = array(
        0 => 'user_eamil_message_list',
        1 => 'sql',
        2 => 'php',
        3 => 'http',
    );
    // 当前进程ID
    public static $_workerId;
    // 当前检查管道信息定时器
    private $statusTime_id;
    // 当前处理管道信息定时器
    private $time_id;

    // 检查管道状态
    public function checkQueueStatus(){
        $this->statusTime_id = Timer::add(TIME_INTERVAL, function()
        {
            $this->checkQueue();
        });
    }

    // 检查管道是否拥有待处理数据
    public function checkQueue(){
        // 获取当前的管道
        $name = self::$pheanstalk->listTubes();
        if (!in_array(self::$tubeName,$name)){
            return;
        }
        // 获取管道状态
        $status = self::$pheanstalk->statsTube(self::$tubeName);
        // 管道中拥有待处理则进行
        if ($status['current-jobs-ready'] > 0){
            // 删除检查管道定时器
            Timer::del($this->statusTime_id);
            $this->time_id = Timer::add(0.1, function() use ($status)
            {
                $status = self::$pheanstalk->statsTube(self::$tubeName);
                $this->takeoutQueue();
                if ($status['current-jobs-ready'] <= 0){
                    $status = self::$pheanstalk->statsTube(self::$tubeName);
                    if ($status['current-jobs-ready'] == 0){
                        // 目前队列处理完毕 处理定时器关闭 检查定时器打开
                        Timer::del($this->time_id);
                        $this->checkQueueStatus();
                    }
                }
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
        $job = self::$pheanstalk->watch(self::$tubeName)->ignore('default')->reserve();
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
            $this->$func($job,$data['implement']);
        }else{
            $this->time_id = Timer::add($data['delay'], function() use ($func,$data,$job)
            {
                $this->$func($job,$data['implement']);
            },[],false);
        }
    }

    // 执行sql
    public function sqlQueue($job,$implement){
        self::$db->row($implement);
    }
    // 执行http curl请求
    public function httpQueue($job,$implement){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $implement);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        $output = curl_exec($ch);
        curl_close($ch);
    }
    // 执行命令行请求
    public function phpQueue($job,$implement){
        popen($implement,'r');
    }
    // 完成异步任务清理
    public function cleanQueue($job){
        self::$pheanstalk->delete($job);
        echo '[ ' . date("Y-m-d H:i:s") . '] job Finish'.PHP_EOL;
    }

}
