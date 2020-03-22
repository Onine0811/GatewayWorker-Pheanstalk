<?php
/**
 * This file is part of workerman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link http://www.workerman.net/
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * 用于检测业务代码死循环或者长时间阻塞等问题
 * 如果发现业务卡死，可以将下面declare打开（去掉//注释），并执行php start.php reload
 * 然后观察一段时间workerman.log看是否有process_timeout异常
 */
//declare(ticks=1);

// 自动加载类
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/base/config.php';
// 加载业务大厅类
require_once __DIR__ . '/platform/Platform.php';
require_once __DIR__ . '/platform/MaintainManager.php';

use \GatewayWorker\Lib\Gateway;
use Workerman\Lib\Timer;

/**
 * 主逻辑
 * 主要是处理 onConnect onMessage onClose 三个方法
 * onConnect 和 onClose 如果不需要可以不用实现并删除
 */
class Events
{
    public static function onWorkerStart($businessWorker)
    {
        // 初始化程序
        date_default_timezone_set('Asia/Shanghai');
        Platform::$_workerId = $businessWorker->id;
        // 每秒检查是否拥有待处理的异步队列
        Platform::getInstance()->checkQueueStatus();
    }

    public static function onWorkerStop($businessWorker)
    {

    }

    /**
     * 当客户端连接时触发
     * 如果业务不需此回调可以删除onConnect
     * 
     * @param int $client_id 连接id
     */
    public static function onConnect($client_id)
    {

    }
    
   /**
    * 当客户端发来消息时触发
    * @param int $client_id 连接id
    * @param mixed $message 具体消息
    */
   public static function onMessage($client_id, $message)
   {
        //first parse Data
        $data = json_decode($message, true);
        if (!is_null($data)){
          $MainID = isset($data['mainid'])?$data['mainid']:"";
          $msg =  isset($data['msg'])?$data['msg']:"";
          MaintainManager::getInstance()->onSocketMessage($client_id,$MainID,$msg);
        }else{
          Gateway::closeClient($client_id);
        }
   }

   /**
    * 当用户断开连接时触发
    * @param int $client_id 连接id
    */
   public static function onClose($client_id)
   {

   }
}
