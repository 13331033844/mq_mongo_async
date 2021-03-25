<?php
error_reporting(E_ALL);

// if($_POST){
//     $param = json_encode($_POST);
// }else{
    $param = file_get_contents('php://input');
// }


if(!$param){
    die(json_encode(["code"=>403,"msg"=>"无参数!"]));
}

$param = unicodeDecode($param);


$param = json_decode($param,true);


$url = $_SERVER['HTTP_HOST'].'/producer_after.php';
$url = explode('/',$url);
$query = isset($param) ? http_build_query($param) : '';
$fp = fsockopen($url[0], 80, $errno, $errstr, 30);


if (!$fp) {

    die(json_encode(["code"=>401,"msg"=>"socket通道建立失败!"]));

} else {
    try{
        $out = "GET /".$url[1]."?".$query." HTTP/1.1\r\n";

        $out .= "Host: ".$url[0]."\r\n";

        $out .= "Connection: Close\r\n\r\n";

        stream_set_blocking($fp,0); //非阻塞
        stream_set_timeout($fp, 1);//响应超时时间（S）
        fwrite($fp, $out);

        while (!feof($fp)) {

            fgets($fp, 128);

        }

        fclose($fp);
        die(json_encode(["code"=>200,"msg"=>"收到信息，异步处理!"]));

    }catch(\Exception $e) {
        die(json_encode(["code"=>402,"msg"=>"抛出异常!"]));
    }
    

}

// json处理中文unicode码
function unicodeDecode($unicode_str){

        // $unicode_str = dotran($unicode_str);
        $unicode_str = str_replace("\U", "\u", $unicode_str);
        $unicode_str = str_replace('\\', '\\\\', $unicode_str);
        $unicode_str = str_replace('"', '\"', $unicode_str);
        $unicode_str = str_replace("'", "\'", $unicode_str);
       
        $json = '{"str":"'.$unicode_str.'"}';

        $arr = json_decode($json,true);




        if(empty($arr)){
            return '';
        }
 
        return $arr['str'];
    }

function dotran($str) {
        $str = str_replace("<","&lt;",$str);
        $str = str_replace(">","&gt;",$str);
        $str = str_replace(" ","&nbsp;",$str);
        $str = str_replace("\n"," ",$str);
        $str = str_replace("&","&amp;",$str);
        return $str;
    }
?>