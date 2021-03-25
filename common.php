<?php
	function log_write($content,$basename)
	{
		$filename = getFileName(1,$basename);
        $file = fopen($filename,"a");
        fwrite($file,$content."\r\n");
        fclose($file);
	}
	function getFileName($flag,$basename)
	{
		$tmp = [];
		$dir = "./log/".$basename.'/'.date('Ymd').'/';
		if(!is_dir($dir)){
		    mkdir(iconv("UTF-8", "GBK", $dir),0777,true);
		}    

	    $filename = scandir($dir);

	    foreach ($filename as $key => $value) {
	    	$tmp = explode('_',$value);
	    	if (count($tmp) > 1 && $tmp[1] > $flag) {
			    $flag = $tmp[1];
	    	}
	    }
	    $filename = $dir.$basename.'_'.$flag.'_'.'.log';
	    if (file_exists($filename)) {
	        $filesize = 5000000;
	        // $filesize = 1;
	        if(filesize($filename) > $filesize){
	            $tmp = explode('_',$filename);
	            $tmp[1]++;
	            $filename = implode('_',$tmp);
	        };
	    }

	    return $filename;
	};