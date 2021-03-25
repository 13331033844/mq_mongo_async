<?php
date_default_timezone_set('Asia/Shanghai');
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

$queue_name_cou = 'err.utalk.log';
$route_key = 'err';
 
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
        $webhook = "https://oapi.dingtalk.com/robot/send?access_token=9236c7123f46452692b8a7b58811b480bdafb7291d115d5b7b8f57b79dc57f2e";


        $msg = json_decode($envelope->getBody(),true);
        $msg["time"] = date('Y-m-d H:i:s',$msg["time"]);

        $message ="监控报警：\n 报错等级：严重错误\n 报错IP：{$msg["clientIp"]}\n 报错代码：{$msg["key"]}\n 时报错间：{$msg["time"]}\n 错误描述：\n {$msg["desc"]}\n [详细信息]\n appId：{$msg["info"]["appId"]}\n 号码：{$msg["info"]["mobile"]}\n 机型：{$msg["info"]["model"]}\n 网络信息：{$msg["info"]["network"]}\n 系统：{$msg["info"]["os"]}\n 设备类型：{$msg["info"]["type"]}\n 用户id：{$msg["info"]["uid"]}\n 版本：{$msg["info"]["version"]}\n ";
        $data = array ('msgtype' => 'text','text' => array ('content' => $message));
        $data_string = json_encode($data);
        $result = httpPost($webhook, $data_string);  
    },AMQP_AUTOACK);



function httpPost($url,$data = null){

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array ('Content-Type: application/json;charset=utf-8'));
        curl_setopt($curl, CURLOPT_POST, 1);
        // $data = http_build_query($data);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
}