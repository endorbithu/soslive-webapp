<?php

require_once('../../inc/controller/core.php'); 

if(empty($_SERVER['HTTPS'])) exit;


$post = $get;

if(!isset($post['app_token']) || !in_array($post["app_token"], APIS)) {
	print "-1";
	exit;
}

$dbConn = new DbConn(true);

//------------------- loginpostcucc --------------------------------
$username = $post['username'];
$password  = $post['access_token'];

$loginCtl = new LoginService;
$conf = new LoginConf;
$lastAttempt = $loginCtl->checkAttempts($username);
$max_attempts = $conf->max_attempts;


//First Attempt
if (!isset($lastAttempt['lastlogin']) || $lastAttempt['lastlogin'] == '') {
    $loginCtl->insertAttempt($username);
    $response = $loginCtl->checkLogin($username, $password);

} elseif ( $lastAttempt['attempts'] >= $max_attempts) {
    //Exceeded max attempts
	
	
	
    $loginCtl->updateAttempts($username);
    $response = $loginCtl->checkLogin($username, $password);
	print '-3';
	exit;

} else {
    $response = $loginCtl->checkLogin($username, $password);
}


if ($lastAttempt['attempts'] < $max_attempts && $response != 'true') $loginCtl->updateAttempts($username);

//--------------------------- loginpost vége ----------------------------------	



if(!isset($post['action']) 
    || empty(preg_match($usernamePm, $post['username']))
	|| !($response  == 'true')) {
	print '0';
	exit;
} 


try {
    $stmt = $dbConn->conn->prepare('SELECT  members.*, companies.stream_server, groups.smstext as gsmstext, 
								    groups.smstelnumbers as gsmstelnumbers, groups.emailtext as gemailtext, groups.alert_emails as gemails
                                    FROM members
                                    JOIN groups ON groups.id = members.groups_id
									JOIN companies ON companies.id = members.companies_id
                                    WHERE username = :username AND verified=1;');
    $stmt->bindParam(':username', $post['username']);
    $stmt->execute();
    $memberDb = $stmt->fetch(PDO::FETCH_ASSOC);    
}
catch(Exception $e) {
	print '-2';
    exit;
}

//____________________  ACTIONÖK ____________________________________________////////////////////////

	// *** BECSEKKOLÁS---------------------------------------------------------------------
	if($post['action'] === 'checkin') {
	    
	    $smstelnumbers = (isset($memberDb['smstelnumbers']) && !empty($memberDb['smstelnumbers'])) ? $memberDb['smstelnumbers'] : ( (isset($memberDb['gsmstelnumbers']) && empty($memberDb['gsmstelnumbers'])) ? $memberDb['smstelnumbers'] : "");
	    $smstext = (isset($memberDb['smstext']) && !empty($memberDb['smstext'])) ? $memberDb['smstext'] : ( (isset($memberDb['gsmstext']) && empty($memberDb['gsmstext'])) ? $memberDb['gsmstext'] : "");
	    
	    echo '{' 
    	        . '"smstelnumbers":"'.  $smstelnumbers 
    	        . '","smstext":"' . $smstext 
    	        . '","rtmpurl":"' . $memberDb['stream_server']
    	        . '","companies_id":"' . $memberDb['companies_id'] . '"'
            . '}';
		
		exit;		
	}


		
		
	// *** ÚJ EVENT id-jét előállítjuk és visszaadjuk---------------------------------------------------------------------------------------
	if($post['action'] === 'new_event') {
		
		$post['coord'] = (isset($post['coord']) && !empty(preg_match($coordPm, $post['coord']))) ? $post['coord'] : '';
		$post['coord'] =  '47.' . rand(1000000,9999999) . ',19.' . rand(1000000,9999999);
		$postlat = explode(",",$post["coord"])[0];
		$postlng = explode(",",$post["coord"])[1];
		$deletetime = (time() + (60 * 60 * 24 * DAY_TO_DELETE));
		

	    $stmt = $dbConn->conn->prepare('
		            INSERT INTO posts (
		                members_id, 
		                event_type, 
		                ' . (empty($post['coord']) ? '': 'first_pos_lat, first_pos_lng,' ) . ' 
		                ip_address, 
		                delete_timestamp, 
		                mod_user, 
		                least_role_id ) 
					VALUES (
					    :member_id,
					    :event_type, 
					    ' . (empty($post['coord']) ? '' : ':postlat, :postlng , ' ). '
					    :remote_address, 
						:deletetime, 
						:moduser, 
						(SELECT default_video_permission FROM roles WHERE id = :roles_id ));
					');
        
        
        
        $stmt->bindParam(':member_id', $memberDb['id']);
        $stmt->bindParam(':event_type', $post['event_type']);
        $stmt->bindParam(':postlat', $postlat);
        $stmt->bindParam(':postlng', $postlng);
        $stmt->bindParam(':remote_address', $_SERVER['REMOTE_ADDR']);
        $stmt->bindParam(':deletetime', $deletetime);
        $stmt->bindParam(':moduser', $memberDb['id'] );
        $stmt->bindParam(':roles_id', $memberDb['roles_id'] );
        
        $stmt->execute();
		
		if(!($stmt->rowCount() > 0)) {
			exit;
		}
		
	
		
		//TODO: erre van PDO utolsó módosított sor lekérdezése is
		
	    $stmt = $dbConn->conn->prepare('SELECT posts.id as id, posts.datetime, members.companies_id, members.username 
	                    FROM posts join members ON members.id = posts.members_id 
	                    WHERE members_id = :members_id ORDER BY posts.id DESC LIMIT 1;');
	    $stmt->bindParam(':members_id', $memberDb['id']);
        $stmt->execute();
        
	    $postDb = $stmt->fetch(PDO::FETCH_ASSOC);    
	    
	    
	    	//email küldés
		
	    $emailAdresses = (isset($memberDb['alert_emails']) && !empty($memberDb['alert_emails'])) ? $memberDb['alert_emails'] : ( (isset($memberDb['gemails']) && empty($memberDb['gemails'])) ? $memberDb['gemails'] : "");
	    $emailText = (isset($memberDb['emailtext']) && !empty($memberDb['emailtext'])) ? $memberDb['emailtext'] : ( (isset($memberDb['gemailtext']) && empty($memberDb['gemailtext'])) ? $memberDb['gemailtext'] : "");

		
			//Create a new PHPMailer instance
			
			$emailAddrArr = explode(',',$emailAdresses);
			
			if(is_array($emailAddrArr) && !empty($emailAddrArr)) {
			
    			foreach($emailAddrArr as $addr) {
    			    
        			$mail = new PHPMailer();
        			//Set who the message is to be sent from
        			$mail->setFrom('sosliveinfocontact@gmail.com','SOSlive' );
        			//Set who the message is to be sent to
        			$mail->addAddress($addr,'');
        			//Set the subject line
        			$mail->Subject = 'SOS live video';
        			//Read an HTML message body from an external file, convert referenced images to embedded,
        			//convert HTML into a basic plain-text alternative body
        			$mail->msgHTML(nl2br($emailText) . '<br><br>Info: ' . SYSHOST . '/' . $postDb['id']);
        
        			$mail->AltBody = $emailText;
        			//send the message, check for errors
        			if ($mail->send()) {
        			    
        			} else {
        				error_log($mail->ErrorInfo);
        			}
    			}
			}
		
		
		
		//email küldés vége
	    
		 

		if(!empty($post['coord']) && !empty(preg_match($coordPm, $post["coord"]))) {
		    
		    $stmt = $dbConn->conn->prepare('INSERT INTO coord (posts_id, coord_lat, coord_lng) VALUES (:post_id, :postlat, :postlng);');
		    
		    $stmt->bindParam(':post_id', $postDb['id']);
		    $stmt->bindParam(':postlat', $postlat);
		    $stmt->bindParam(':postlng', $postlng);
            $stmt->execute();
		    
		}
		
		$dateTime = new DateTime($postDb['datetime']);
		

	    echo '{' 
    	        . '"id":"'.  (isset($postDb['id']) ? $postDb['id'] : '0') 
    	        . '","datetime":"' . $dateTime->format('Ymd') 
    	        . '","username":"' . $postDb['username']
    	        . '","company":"' . $postDb['companies_id']
    	        . '"'
            . '}';
		
		//ne csak az id-t adjuk át hanem datetime-ot és companyt is 
		//print (isset($postDb['id']) ? $postDb['id'] : '0');

		exit;
	}
	
	
	

	
	// *** HELYADAT FRISSÍTÉS FOGADÁSA-------------------------------------------------------------------------------------------
	if($post['action'] === 'updatelocation' && isset($post['id']) && !empty(preg_match($coordPm, $post['coord']))) {

		$coord = explode(",",  $post['coord']);
		$coord = ['47.' . rand(1000000,9999999) , '19.' . rand(1000000,9999999)];
		
		
		$stmt = $dbConn->conn->prepare('SELECT id, event_type, first_pos_lat FROM posts WHERE posts.id = :posts_id; ');
	    $stmt->bindParam(':posts_id', $post['id']);
        $stmt->execute();
		$postDb = $stmt->fetch(PDO::FETCH_ASSOC);    
	
       
    		if(empty($postDb['first_pos_lat'])) {
    		    
    		    	$stmt = $dbConn->conn->prepare('UPDATE posts SET first_pos_lat= :first_pos_lat , first_pos_lng = :first_pos_lng  WHERE posts.id = :posts_id; ');
	                $stmt->bindParam(':first_pos_lat', $coord[0]);
	                $stmt->bindParam(':first_pos_lng', $coord[1]);
	                $stmt->bindParam(':posts_id', $postDb['id']);
	                $stmt->execute();
    		    
    		}
    		
    		
		if(empty($postDb['first_pos_lat']) || $postDb['event_type'] != 'PHOTO') {
		    
	    	$stmt = $dbConn->conn->prepare('
	    	            INSERT INTO `coord` (`id`, `posts_id`, `datetime`, `coord_lat`, `coord_lng`) 
    	                VALUES (NULL, :posts_id , CURRENT_TIMESTAMP, (:coord_lat), (:coord_lng));');
            $stmt->bindParam(':posts_id', $post['id']);
            $stmt->bindParam(':coord_lat', $coord[0]);
            $stmt->bindParam(':coord_lng', $coord[1]);
 
            $stmt->execute();
           
            print ($stmt->rowCount() > 0);
		    
    	
		}
			
		exit;
	        
	}
	

 
	
	
	// *** COMMENT HOZZÁADÁSA -------------------------------------------------------------------------------------------
	if($post['action'] === 'addcomment' && isset($post['id']) && isset($post['message']) && !empty($post['message'])) {
		

		$stmt = $dbConn->conn->prepare('SELECT `id` FROM `members` WHERE `username` = :username ;');
	    $stmt->bindParam(':username', $post['username']);
        $stmt->execute();
		$member = $stmt->fetch(PDO::FETCH_ASSOC);    
		
		$incService = new IncidentsService();
	    $inci = $incService->getIncidentContent($post['id']);
	    
	    if(empty($inci['message']) && $inci['members_id'] == $member['id']) {
        	$stmt = $dbConn->conn->prepare("UPDATE posts SET message = :message WHERE id=:id;");
    		$stmt->bindParam(':id', $post['id']);
    		$stmt->bindParam(':message', $post['message']);
    		$stmt->execute();
	    }
		
		$stmt = $dbConn->conn->prepare('INSERT INTO `comments` (`id`, `posts_id`, `datetime`, `members_id`, `message`) 
			VALUES (NULL, :posts_id , CURRENT_TIMESTAMP, :member_id, :message );');
			
	    $stmt->bindParam(':posts_id',  $post['id']);
        $stmt->bindParam(':member_id',  $member['id']);
        $stmt->bindParam(':message',  $post['message']);
        $stmt->execute();
        
        print ($stmt->rowCount() > 0);

		exit;
		
	
	        
	}
	
	
	
	// *** COMMENTEK APPRA KÜLDÉSE
	if($post['action'] === 'getcomments' && isset($post['id']) && !empty($post['id'])) {

    	$stmt = $dbConn->conn->prepare('SELECT comments.*, members.fullname, DATE_FORMAT(comments.datetime, "%Y/%m/%d %H:%i") as datetime  
		FROM `comments` JOIN members on comments.members_id = members.id
		WHERE `posts_id` = :posts_id
		ORDER BY datetime ASC;');
		
	    $stmt->bindParam(':posts_id', $post['id']);
        $stmt->execute();
		$comments = $stmt->fetchAll();    
    	$comments["total_count"] =  $stmt->rowCount();

		echo json_encode($comments);
			
		exit;
	        
	}
	
	
	

	
	
	
	
	

	
	
	
	
	
	




