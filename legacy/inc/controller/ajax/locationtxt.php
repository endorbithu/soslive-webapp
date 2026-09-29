<?php

 
$iService = new IncidentsService();
$dbConn = new DbConn();
$videoId = $get['id'];
$hasPerm = $iService->isPostAvailableForCurrentUser($get['id']);  
 
 //validáció mint ami a video elején van!
 if(!$hasPerm || !is_numeric($videoId)) exit;
 

	$stmt = $dbConn->conn->prepare('SELECT coord.*, posts.datetime as pdatetime, posts.id as pid, 
					posts.event_type, members.fullname, members.username, groups.name as gname
                    FROM `coord` 
                    JOIN posts ON posts.id = coord.posts_id 
                    JOIN members ON members.id = posts.members_id
                    JOIN groups ON members.groups_id = groups.id
                    WHERE posts.id = :id ORDER BY coord.datetime;');
	$stmt->bindParam(':id', $videoId);
	$stmt->execute();
		
 
	$locs = $stmt->fetchAll();

	if(empty($locs)) {
		echo NO_LOCATION_DATA_YET;
		exit;
	}
	

	$out = "RCI \r\n";
	$out .=  "#" . $videoId . " - " . $_SERVER['HTTP_HOST'] . "/" . $videoId . "\r\n---------------------------------------------\r\n";
	
	foreach($locs as $c) {
		$isLocation = true;
		$out .=  $c['datetime'] . " - " . $c['coord_lat'] . "," .  $c['coord_lng'] . " - http://maps.google.com/maps?q=" . $c['coord_lat'] . "," .  $c['coord_lng'] . "\r\n";
	}

print $out;


$fileName =  $videoId . '-locations';
	header('Content-Description: File Transfer');
    header('Content-Type: application/force-download');
    header('Content-Disposition: attachment; filename="'.$fileName.'.txt"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize(__FILE__));
    header("Connection: close"); 
    exit; 






