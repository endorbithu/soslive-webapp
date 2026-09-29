<?php

require_once('../../inc/controller/core.php'); 

if(!in_array($_SERVER['REMOTE_ADDR'], STREAM_SERVER_IP)) exit;

$dbConn =  new DbConn(true);
$incidentService = new IncidentsService();
if(empty($post)) $post = $get;

	// *** LIVE VIDEO USER ELLENŐRZÉS -------------------------------------------------------------------------------------------
	//$_SERVER['REMOTE_ADDR'] is legyen ajd egy tömbbe rendezve, és csaka tömbben lévő IP címeket engedje be
	if(($post['call'] === 'publish' )) {
	    
	    $videoFileName = $incidentService->getVideoFileName($post['id']);
 
		if($videoFileName != $post['name']) {
		   
			http_response_code(404);
			exit;
		}

		
		
	    $stmt = $dbConn->conn->prepare('SELECT posts.*, members.username FROM `posts` JOIN members ON members.id = posts.members_id WHERE posts.id = :posts_id  AND username= :username ;');
	    $stmt->bindParam(':posts_id', $post['id']);
	    $stmt->bindParam(':username', $post['username']);
        $stmt->execute();
		 
	    $postElem = $stmt->fetch(PDO::FETCH_ASSOC);    
		
		if(empty($postElem)) {
			http_response_code(404); 
			exit;
		}

	    $stmt = $dbConn->conn->prepare('INSERT INTO video (posts_id, status) VALUES (:posts_id, "RUNNING")');
	    $stmt->bindParam(':posts_id', $postElem['id']);
        $stmt->execute();
		
		
		exit;
	        
	}
	
	
	
	if(($post['call'] === 'publish_done' ))
	{
        
        $videoFileName = $incidentService->getVideoFileName($post['id']);

		if($videoFileName !== $post['name']) {
			http_response_code(404);
			exit;
		}

		
	    $stmt = $dbConn->conn->prepare('SELECT posts.*, members.username FROM `posts` JOIN members ON members.id = posts.members_id WHERE posts.id = :posts_id AND username= :username ;');
	    $stmt->bindParam(':posts_id', $post['id']);
	    $stmt->bindParam(':username', $post['username']);
        $stmt->execute();
		$postElem = $stmt->fetch(PDO::FETCH_ASSOC);   
		
		
		
		if(empty($postElem)) {
			http_response_code(404); 
			exit;
		}
		
		$stmt = $dbConn->conn->prepare('UPDATE  video SET status = "STOPPED", stopped_datetime = CURRENT_TIMESTAMP WHERE posts_id= :posts_id ;');
	    $stmt->bindParam(':posts_id', $postElem['id']);
        $stmt->execute();
		
		
		exit;
	        
	}

	
	
	
	
	
	

	
	
	
	
	
	




