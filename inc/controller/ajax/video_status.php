<?php

if(empty($get)) exit;
    
$iService = new IncidentsService();
$dbConn = new DbConn();
$hasPerm = $iService->isPostAvailableForCurrentUser($get['id']);  
if(!$hasPerm) exit; 
$id = $get['id'];
	
$resp = [];   

	//Változott-e a video státusza?
	$stmt = $dbConn->conn->prepare('select `status` FROM `video`  WHERE `posts_id` = :id LIMIT 1 ;');
	$stmt->bindParam(':id', $id);
	$stmt->execute();
	$resultArr = $stmt->fetch(PDO::FETCH_ASSOC);
    $resp['status'] = isset($resultArr['status']) ? $resultArr['status'] : '';


	//van e új futó live?
	$stmt = $dbConn->conn->prepare('select posts.id, status 
    FROM `posts` LEFT JOIN video ON posts.id = video.posts_id  
    WHERE `datetime` > :time AND `members_id` = :members_id AND `video`.`status` = "RUNNING"  LIMIT 1 ;');
	$stmt->bindParam(':time', $get['time']);
	$stmt->bindParam(':members_id', $get['user_id']);
	$stmt->execute();
	$resultNewArr = $stmt->fetch(PDO::FETCH_ASSOC);
	
	$resp['hasNew'] = (!empty($resultNewArr['id'])) ? '/' . $resultNewArr['id'] : '';
      
    echo json_encode($resp);
      
         
    	      

