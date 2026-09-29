<?php

 
$iService = new IncidentsService();
$dbConn = new DbConn();
$hasPerm = $iService->isPostAvailableForCurrentUser($get['id']);  
 
 //validáció mint ami a video elején van!
 if(!$hasPerm) exit;
 
$videoId = $get['id'];

if(!is_numeric($videoId)) exit;


	$stmt = $dbConn->conn->prepare('SELECT comments.*, posts.datetime as pdatetime, 
						posts.id as pid, posts.event_type, members.fullname, members.username, groups.name as gname
                    FROM `comments` 
                    JOIN posts ON posts.id = comments.posts_id 
                    JOIN members ON members.id = comments.members_id
                    JOIN groups ON members.groups_id = groups.id
                    WHERE posts.id = :id ORDER BY comments.datetime;');
	$stmt->bindParam(':id', $videoId);
	$stmt->execute();
		
	$comments = $stmt->fetchAll();

	echo "RCI \r\n";
    echo "#" . $videoId . " - " . $_SERVER['HTTP_HOST'] . "/" . $videoId . "\r\n---------------------------------------------";

        foreach($comments as $comm)
        {
            echo "\r\n";
            echo    $comm['fullname'] . ' (' . $comm['gname']  . ') - '. $comm['datetime'] . "\r\n" .  $comm['message'];
			echo "\r\n---------------------------------------------";
        }
    
    
    
    $fileName =  $videoId . '-comments';


    header('Content-Description: File Transfer');
    header('Content-Type: application/force-download');
    header('Content-Disposition: attachment; filename="'.$fileName.'.txt"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize(__FILE__));
    header("Connection: close"); 
    exit; 	        
    	    













?>