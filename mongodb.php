<?php

/**
 * 
 */
class Mongo{
	public function insert_log($arr)
	{
		$manager = new MongoDB\Driver\Manager("mongodb://log_service:bp1599897e8966941323@dds-bp1599897e8966941323-pub.mongodb.rds.aliyuncs.com:3717/om_center"); 
		$bulk = new MongoDB\Driver\BulkWrite;
		$bulk->insert($arr);
		$manager->executeBulkWrite('om_center.log_app', $bulk);
	}


	public function insert_count($arr)
	{
		$servername = "utalk-test.mysql.polardb.rds.aliyuncs.com";
		$username = "utalk";
		$password = "utalk2016!#*";
		$dbname = "utalk2016_production";
		 
		try {
		    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
		    file_put_contents('c.txt',$conn);
		    
		    // 设置 PDO 错误模式，用于抛出异常
		    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		    foreach ($arr as $key => $value) {
		    	if ($key == 'info') {
		    		foreach ($value as $k => $v) {
		    			$column[] = $k;
		    			$val[] = $v;
		    		}
		    	}else if($key == 'data') {
		    		$column[] = $key;
		    		$val[] = json_encode($value);
		    	}else{
		    		$column[] = $key;
		    		$val[] = $value;
		    	}
		    }
// echo '<pre>';
// 		    print_r($arr);
// 		    print_r($column);
// 		    exit;
		    $column = implode('`,`',$column);

		    $val = implode("','",$val);
		    
		    $sql = "INSERT INTO log_count (`".$column."`)
		    VALUES ('".$val."')";

		    // 使用 exec() ，没有结果返回 
		    $conn->exec($sql);
		}
		catch(PDOException $e)
		{
		    file_put_contents('b.txt',$e);

		}
		 
		$conn = null;
	}

	public function insert_warn($arr)
	{
		$manager = new MongoDB\Driver\Manager("mongodb://log_service:bp1599897e8966941323@dds-bp1599897e8966941323-pub.mongodb.rds.aliyuncs.com:3717/om_center"); 
		$bulk = new MongoDB\Driver\BulkWrite;
		$bulk->insert($arr);
		$manager->executeBulkWrite('om_center.log_app', $bulk);
	}

}
