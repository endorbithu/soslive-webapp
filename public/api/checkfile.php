<?php

require_once('../../inc/controller/core.php'); 


if(!in_array($_SERVER['REMOTE_ADDR'], STREAM_SERVER_IP)) exit;


if((($get['act'] === 'checkfile' ) && is_numeric($get['company']))) {
	   
	    $dbConn = new DbConn(true);
	    
    	$stmt = $dbConn->conn->prepare("SELECT * FROM companies WHERE companies.id = :id");
    	$stmt->bindParam(':id', $get['company']);
		$stmt->execute();
		
		$comp =   $stmt->fetch(PDO::FETCH_ASSOC);
		
	    
	    $serverAndDir = $comp['file_server'] .'/';
	    $month = $get['month'] . '/';
	    $fileName = $get['filename'];
	    
	    $file = $serverAndDir . $month . $fileName;
 
        $ch = curl_init($file);
    
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, TRUE);
        curl_setopt($ch, CURLOPT_NOBODY, TRUE);
        
        $data = curl_exec($ch);
        $size = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
        
        curl_close($ch);
        echo $size;
		
	    exit;
	        
	}
	
	
	

	
	
	
	
	

	
	
	
	
	
	




