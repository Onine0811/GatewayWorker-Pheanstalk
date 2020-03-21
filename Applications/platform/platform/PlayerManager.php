<?php

require_once __DIR__ . '/Platform.php';
use \GatewayWorker\Lib\Gateway;
class PlayerManager
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

    //**--------------------------------------------
    //链接映射
    private static $m_clientToUid = array();
    //成员数据
    private static $m_userBaseDataList = array();


    /**--------------------------------------------
    *socket连接成功，游戏基本版本数据
    *
    */
    public function onConnectMessage($client_id){
        $info = PlatformDB::getInstance()->getSysInfo();
        $this->sendMsgByClientId($client_id,PT_GAMEINFO,$info);
    }
    /**--------------------------------------------
    *用户登录游戏
    * @param $userInfo 
    *array('openid','nickname','photo' )
    * @return $result 
    * array('code','desc','usernfo')
    * code: 0/1/2 成功/拉黑/数据异常
    */
    public function onLoginMessage($client_id, $userInfo){
        $result = array();
        if (isset($userInfo['openid'])&&$userInfo['openid']!=""&&$userInfo['openid']!=-1&&strlen($userInfo['openid'])<30) {
            $resCode =  PlatformDB::getInstance()->loginCheck($userInfo);
            if ($resCode == 1) {
                $result['code'] = 1;
                $result['desc'] = "你已被禁止登录，请联系管理员！";
            }else{
                $result['code'] = 0;
                $result['desc'] = "登陆成功";
                $result['userinfo'] = $this->getUserBaseInfoByID($resCode);
                $result['notice'] = $this->getHallNotice();
                $result['gamelist'] = PlatformDB::getInstance()->getgameList();
                // $result['photolist'] = PlatformDB::getInstance()->getPhotoList();
                $result['lastroom'] = PlatformDB::getInstance()->getLastRoom($resCode);
                $result['taskpoint'] = $this->taskpoint($resCode);
                $result['signinpoint'] = $this->signinpoint($resCode);
                //设置全局标志
                self::$m_clientToUid[$client_id] = $resCode;
                
            }
        }else{
            //数据异常,提交了非法数据
            $result['code'] = 3;
            $result['desc'] = "数据异常";
        }
        $this->sendMsgByClientId($client_id,PT_LOGIN,$result);
    }
    public function signinpoint($uid)
    {
        $res = PlatformDB::getInstance()->getSignintimeList($uid);
        foreach ($res as $key => $value) {
            if ($res[$key]['state'] == 2) {
                return true;
            }
        }
        return false;
    }
    public function taskpoint($uid)
    {
        $res = PlatformDB::getInstance()->gettasklist($uid);
        foreach ($res as $key => $value) {
            if ($res[$key]['state'] == 1) {
                return true;
            }
        }
        return false;
    }

    /**--------------------------------------------
    *加载一个玩家数据
    */
    public function addUserBaseInfo($userInfo){
        self::$m_userBaseDataList[$userInfo["id"]] = $userInfo;
    }

    //更新玩家房卡
    public function updateCardsByBindCode($userID,$cards){
        foreach (self::$m_userBaseDataList as $uid => $info) {
            if ($uid == $userID) {
                self::$m_userBaseDataList[$uid]['cards'] = $cards;
            }
        }
    }

    /**
    * 获取玩家数据
    */
    public function getUserBaseInfoByID($userID){
        foreach (self::$m_userBaseDataList as $uid => $info) {
            if ($uid == $userID) {
                return $info;
            }
        }
        return null;
    }

    /**--------------------------------------------
    *玩家断线，清除数据
    */
    public function clearUserByOffLine($client_id){
        if (isset(self::$m_clientToUid[$client_id])) {
            $uid = self::$m_clientToUid[$client_id];
            unset(self::$m_clientToUid[$client_id]);
            unset(self::$m_userBaseDataList[$uid]);
        }
    }

    //获取大厅消息
    public function getHallNotice(){
       return  PlatformDB::getInstance()->getHallNotice();
    }
    //获取玩家的房间列表
    public function getPlayerRoomList($uid){
        return  PlatformDB::getInstance()->getPlayerRoomList($uid);
    }

    /**--------------------------------------------
    *获取玩家链接状态
    */
    public function getUserSession($client_id){
        return isset(self::$m_clientToUid[$client_id])?true:false;
    }

    /*获取玩家uid
    */
    public function getUserID($client_id){
        return self::$m_clientToUid[$client_id];
    }
        /**
    * 获取玩家的钻石
    */
    public function getUserCards($userID){
        foreach (self::$m_userBaseDataList as $uid => $info) {
            if ($uid == $userID) {
                return $info['cards'];
            }
        }
        return 0;
    }
    /**--------------------------------------------
    *获取玩家通信ID
    */
    public function getClientIdByUid($userid){
        foreach (self::$m_clientToUid as $client_id => $uid) {
            if ($uid == $userid) {
                return $client_id;
            }
        }
        return null;
    }

    public function sendMsgByClientId($client_id,$MainID,$data) {
        $msg['mainid'] = $MainID;
        $msg['data'] = $data;
        $restag = Gateway::sendToClient($client_id, json_encode($msg));
    }
    public function sendMsgByUid($uid,$MainID,$data) {
        $client_id = $this->getClientIdByUid($uid);
        if ($client_id != null){
            $this->sendMsgByClientId($client_id,$MainID,$data);
        }
    }


}
