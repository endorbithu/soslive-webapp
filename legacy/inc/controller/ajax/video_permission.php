<?php

if(empty($get)) exit;
    
$iService = new IncidentsService();
$dbConn = new DbConn();
$hasPerm = $iService->isPostAvailableForCurrentUser($get['id']);  
if(!$hasPerm || $_SESSION['can_video_modify'] != '1' ) exit; 

$stmt = $dbConn->conn->prepare('SELECT * FROM roles WHERE weight <= :weight AND id = :perm ;');
$stmt->bindParam(':weight', $_SESSION['weight']);
$stmt->bindParam(':perm', $get['perm']);
$stmt->execute();
$selectedPerm = $stmt->fetch(PDO::FETCH_ASSOC);

if(empty($selectedPerm)) exit;


$stmt = $dbConn->conn->prepare("UPDATE posts SET least_role_id =  :least_role_id, mod_user = :mod_user WHERE id = :id ;");
$stmt->bindParam(':id', $get['id']);
$stmt->bindParam(':least_role_id', $get['perm']);
$stmt->bindParam(':mod_user', $_SESSION['id']);
$stmt->execute();


$stmt = $dbConn->conn->prepare("SELECT posts.*, members.id as userid, members.username FROM posts 
								JOIN members ON members.id=posts.mod_user WHERE posts.id = :id ;");
$stmt->bindParam(':id', $get['id']);
$stmt->execute();
$setPost = $stmt->fetch(PDO::FETCH_ASSOC);

	   
if(!empty($setPost)) {
	
	$returnObj['userid'] = $setPost['userid'];
	$returnObj['username'] = $setPost['username'];
	$returnObj['mod_datetime'] = $setPost['mod_timestamp'];
	
	print json_encode($returnObj);
	exit;
} 
		
	
  











