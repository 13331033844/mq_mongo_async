<?php

$data = $_GET;

if(count($data['data']) < 1){
    die(json_encode(["code"=>403,"msg"=>"参数缺失!","data"=>$data]));
}
$arr = [];
foreach ($data['data'] as $key => $value) {
    $arr[$key] = $value;
    $arr[$key]['info'] = $data['info'];
}


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
$exchangeName = 'com.helputalk.log3'; 
//验证交换机、路由、消息等参数
 
 
try{
    //建立连接
    $conn = new AMQPConnection($conn_args);
    if ( !$conn->connect() ) {   
        die("无法连接到MQ服务器!\n");   
    }
    //创建通道
    $channel = new AMQPChannel($conn);
    //创建交换机
    $exchange = new AMQPExchange($channel);
    $exchange->setName($exchangeName);
    $exchange->setType(AMQP_EX_TYPE_TOPIC);
    $exchange->setFlags(AMQP_DURABLE);
    $exchange->declareExchange();
    
    
    //绑定路由关系发送消息
    if ($arr) {
        foreach ($arr as $key => $value) {
            $routeKey = $value['level'];
            $value['clientIp'] = $_SERVER['REMOTE_ADDR'];

            $exchange->publish(json_encode($value),$routeKey);
        }
    }

}catch (\Exception $exception){
    var_dump($exception);
}
