GatewayWorker-Pheanstalk
==========

[![Build Status](https://github.com/Onine0811/GatewayWorker-Pheanstalk)](https://github.com/Onine0811/GatewayWorker-Pheanstalk)

 将GatewayWorker与Pheanstalk结合起来，通过多进程处理异步队列
 
  DEMO 
  
  异步SQL语句
     
     $jobData=array(
         'type' => 'sql',
         'implement' => [
             'sql' => 'SELECT * FROM test WHERE id = ?'
             'param' => ['test']
         ],
         'delay' => 0,
     );
     
     $jobData=array(
          'type' => 'sql',
          'implement' => [
              'sql' => 'SELECT * FROM test WHERE id = :id'
              'param' => ['id' => 'test']
          ],
          'delay' => 0,
     );
     
     $jobData=array(
          'type' => 'sql',
          'implement' => [
              'sql' => 'SELECT * FROM test WHERE id = "test"'
          ],
          'delay' => 0,
     );
          
     $pheanstalk ->useTube('sql') ->put( json_encode( $jobData));
     
  异步http请求
  
     $jobData=array(
          'type' => 'http',
          'implement' => [
              'action' => 'POST',
              'url' => 'www.baidu.com',
              'param' => ['test'=>'test']
          ],
          'delay' => 0,
     );
     $jobData=array(
          'type' => 'http',
          'implement' => [
              'action' => 'GET',
              'url' => 'www.baidu.com?test=test',
          ],
          'delay' => 0,
     );
     $pheanstalk ->useTube('http') ->put( json_encode( $jobData));
     
  异步命令行执行
  
     $jobData=array(
          'type' => 'php',
          'implement' => [
              'cmd' => 'php index.php/control/method'
          ],
          'delay' => 0,
     );
     $pheanstalk ->useTube('php') ->put( json_encode( $jobData));
       
 2020-04-12 V 1.0.1
 
 完成部分上版本需求
    
    1.允许sql的使用命名占位符和问号占位符的预处理语句
    2.允许http的post请求
 
 2020-03-22 V 1.0.0
   
  初版本demo实现
  
     1.允许异步http请求、命令行执行、sql执行，实现延迟
     2.通过websocket实现维护者维护
  
  下一版本
    
    1.异常日志输出
    2.允许异步http的post请求
    3.增加维护者请求接口

License
-------

© Onine
