<?php
require_once('common.php');
require_once('mongodb.php');
set_error_handler(function ($errno, $errstr, $errfile) {
    echo $errno . ' : ' . $errstr . ' : ' . $errfile . "\r\n";
    return true;
});


//配置信息 
$conn_args = array( 
    'host' => 'mq.helputalk.com',  
    'port' => '56729', 
    'heartbeat' => 30,  
    'login' => 'wuheng',  
    'password' => 'wuheng1314,./', 
    'vhost'=>'/task' 
);   
  

//交换机名
$exchange_name  = 'com.helputalk.log3';  

$queue_name_cou = 'log.utalk.log';
$route_key = 'log';

 
//创建消息连接
$conn = new AMQPConnection($conn_args);   
if ( !$conn->connect() ) {   
    die("无法连接到MQ服务器!\n");   
}
//创建消息通道   
$channel = new AMQPChannel($conn);   
 
//创建交换机    
$exchangeResult = new AMQPExchange($channel);   
$exchangeResult->setName($exchange_name); 
$exchangeResult->setType(AMQP_EX_TYPE_TOPIC);    //direct类型 消息直接推到列队里  匹配KEY就能取到消息  
/**
 * AMQP_EX_TYPE_DIRECT、 AMQP_EX_TYPE_FANOUT、 AMQP_EX_TYPE_HEADERS、 AMQP_EX_TYPE_TOPIC
 */
$exchangeResult->setFlags(AMQP_DURABLE);  //持久化 
$exchangeResult->declareExchange();

//创建队列    
$queueResult = new AMQPQueue($channel); 
$queueResult->setName($queue_name_cou);   
$queueResult->setFlags(AMQP_DURABLE); //持久化  
$queueResult->declareQueue();
    //绑定交换机与队列，并指定路由键 取指定交换机下的指定列队
$queueResult->bind($exchange_name, $route_key);

//阻塞模式接收消息  
    $queueResult->consume(function($envelope, $queue){ 
        if (!isset($m)) {
            $m = new mongo();
        }
        $data = $envelope->getBody();
        // $type = 'log';
        // log_write($data,$type);
        $arr = json_decode($data,true);
        $m->insert_log($arr);
        
    },AMQP_AUTOACK);
