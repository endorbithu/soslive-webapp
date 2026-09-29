<?php
class IncidentsService extends DbConn
{
	private $get;
	
	
	public function __construct() {
		parent::__construct();
	}
	
	public function setGet($get) {
		$this->get = $get;
	}
	
	public function getMonths() {
		
		$stmt = $this->conn->prepare("SELECT MIN(datetime) FROM posts JOIN members ON members.id = posts.members_id 
									WHERE members.companies_id = :companies_id"); 
		$stmt->bindParam(':companies_id', $_SESSION['companies_id']);
		$stmt->execute();
		$result = $stmt->fetchAll();		
		
		if(empty($result[0][0])) return [(date('Y-m-d H:i:s', time())) => (date('Y-m', time()))];	
		
		$minDate = new DateTime(substr($result[0][0],0,8) . '00 00:00:00');
				
		
		$date = new DateTime();
		
		$allMonth = [];
		
		while($date >= $minDate) {
			$allMonth[$date->format('Y-m-d H:i:s')] = $date->format('Y-m');
			$date->sub(new DateInterval('P1M'));
		}
		
		return($allMonth);
		
	}
	
	
	public function getUsers() {
		$users = $this->conn->prepare("SELECT members.* FROM members WHERE 1 = 1
		   " . ($_SESSION['has_all_group'] === '1' ? '' : ' AND members.groups_id = :gid ')  .  
		   " AND members.companies_id = :companies_id ORDER BY fullname");
		   
		if($_SESSION['has_all_group'] != '1') $users->bindParam(':gid', $_SESSION['groups_id']);
		$users->bindParam(':companies_id', $_SESSION['companies_id']);
		$users->execute();

		return($users->fetchAll());
		   
	}
	
	public function getMapIncidents($count = false) {
		
			$get = $this->get;
		
			$get['n'] = isset($get['n']) ?  substr($get['n'],0,32) : 0;
            $get['s'] = isset($get['s']) ?  substr($get['s'],0,32) : 0; 
	        $get['e'] = isset($get['e']) ?  substr($get['e'],0,32) : 0;
	        $get['w'] = isset($get['w']) ?  substr($get['w'],0,32) : 0;
	                                
	        
	       $mapIncidents = $this->conn->prepare('
	        SELECT posts.* , members.username, members.fullname, video.status, 
			groups.name as gname, groups.id as gid, roles.name as rname, m2.fullname as m2name,
			m2.id as m2_id, posts.message
					FROM posts 
					JOIN members ON posts.members_id=members.id
					JOIN groups ON members.groups_id=groups.id
					JOIN roles ON members.roles_id=roles.id
					JOIN roles as r2 ON posts.least_role_id = r2.id
					JOIN members as m2 ON posts.mod_user = m2.id
					LEFT JOIN video ON video.posts_id = posts.id 
					WHERE members.companies_id = :companies_id 
					AND r2.weight <= (SELECT weight FROM roles r3 WHERE r3.id = :roles_id) 
					
				AND ((`first_pos_lat` IS NOT NULL) AND (`first_pos_lat` NOT LIKE "") AND `first_pos_lat` < :n 
				AND  `first_pos_lat` > :s AND  `first_pos_lng` < :e  AND  `first_pos_lng` > :w ) 
				
            ' . (($_SESSION['has_all_group'] == '1') ? '':' AND groups.id = :gid ' ) . '
            '. (!isset($get['event_type']) || empty($get['event_type']) ? '' : ' AND posts.event_type = :event_type ')  .'
            '. (!isset($get['groupid']) || !is_numeric($get['groupid']) ? '' : ' AND members.groups_id = :group_id ')  .'
            '. (!isset($get['userid']) || !is_numeric($get['userid']) ? '' : ' AND members.id = :userid ')  .'
			' . ((!$count) ? '': 'AND  posts.datetime > :loaded_time ' )         
			
			. ' ORDER BY `datetime` DESC LIMIT ' . ($count ? '1' : '5000') . ' ;');
	        
			
			$mapIncidents->bindParam(':roles_id', $_SESSION['roles_id']);
			
			$mapIncidents->bindParam(':n', $get['n']);
			$mapIncidents->bindParam(':s', $get['s']);
			$mapIncidents->bindParam(':e', $get['e']);
			$mapIncidents->bindParam(':w', $get['w']);
			$mapIncidents->bindParam(':companies_id', $_SESSION['companies_id']);
			
			$loadedTime = (date('Y-m-d H:i:s', $_SESSION['loadedTime']));
			
			if($_SESSION['has_all_group'] != '1') $mapIncidents->bindParam(':gid', $_SESSION['groups_id']);
			if(isset($get['event_type']) && !empty($get['event_type'])) $mapIncidents->bindParam(':event_type', $get['event_type']);
			if(isset($get['groupid']) && is_numeric($get['groupid'])) $mapIncidents->bindParam(':group_id', $get['groupid']);
			if(isset($get['userid']) && is_numeric($get['userid'])) $mapIncidents->bindParam(':userid', $get['userid']);
			if($count) $mapIncidents->bindParam(':loaded_time', $loadedTime);
			
			$mapIncidents->execute();
 
			if(!$count) {
				$resultCoord = $mapIncidents->fetchAll();
				return $resultCoord;
			} else {
				return $mapIncidents->rowCount();
			}
	}

	public function getIncident($id) {
		$stmt = $this->conn->prepare("SELECT posts.mod_timestamp, members.* , 
		FROM posts JOIN members on posts.mod_user = members.id WHERE posts.id = = :id AND members.companies_id = :companies_id;");
		$stmt->bindParam(':id', $id);
		$stmt->bindParam(':companies_id', $_SESSION['companies_id']);
		$stmt->execute();
		
		return $stmt->fetchAll();
	}
	
	public function getVideoFileName($id) {
	    
    	$stmt = $this->conn->prepare("SELECT posts.*, members.username, members.companies_id
		FROM posts JOIN members on posts.members_id = members.id 
		WHERE posts.id = :id");
		
		$stmt->bindParam(':id', $id);
		$stmt->execute();
		
		$postDetails =   $stmt->fetch(PDO::FETCH_ASSOC);
		
		
    	$shortHash = substr((hash('sha512', $postDetails['username'] . '||' . ACCESS_TOKEN_SALT . '||' .$postDetails['id'] )), 33,48);
    	$postDate = new DateTime($postDetails["datetime"]);
    	
    	return ($postDate->format('Ymd') . "_"  . $postDetails['companies_id'] . "_" . $postDetails['username'] . "_" . $postDetails['id'] . "_" . $shortHash);
	    
	}
	
	public function isPostAvailableForCurrentUser($id) {
        if(!is_numeric($id)) return false;
        
            if($this->isPostPublic($id)) return true;

    		$resultSql = $this->conn->prepare('SELECT DISTINCT posts.* , members.username,  members.fullname, video.status, video.stopped_datetime, 
			                        groups.name as gname, groups.id as gid, roles.name as rname, m2.fullname as m2name,
			                        m2.id as m2_id
			                                FROM posts 
			                                JOIN members ON posts.members_id=members.id
			                                JOIN groups ON members.groups_id=groups.id
			                                JOIN roles ON members.roles_id=roles.id
			                                JOIN roles as r2 ON posts.least_role_id = r2.id
			                                JOIN members as m2 ON posts.mod_user = m2.id
			                                LEFT JOIN video ON video.posts_id = posts.id 
			                                WHERE `posts`.`id` = :id 
											AND members.companies_id = :companies_id
			                                 AND r2.weight <= (SELECT weight FROM roles r3 WHERE r3.id = :roles_id ) 
			                                ' . ((isset($_SESSION['has_all_group']) && $_SESSION['has_all_group'] === '1') ?'':' AND groups.id = :groups_id ' )) ;
			
		$compId = isset($_SESSION['companies_id']) ? $_SESSION['companies_id'] : '0';
		$roleId = isset($_SESSION['roles_id']) ? $_SESSION['roles_id'] : '0';
		$hasAllGroup = isset($_SESSION['has_all_group']) ? $_SESSION['has_all_group'] : '0';
		
		$resultSql->bindParam(':id', $id);
		$resultSql->bindParam(':companies_id', $compId);
		$resultSql->bindParam(':roles_id', $roleId);
		
		if($hasAllGroup != '1') $resultSql->bindParam(':groups_id', $_SESSION['groups_id']);
		$resultSql->execute();		

		return (0 < $resultSql->rowCount());

	}
	
	public function isPostPublic($id) {
    	$resultSql = $this->conn->prepare('SELECT r2.weight
			                                FROM posts
			                                JOIN roles as r2 ON posts.least_role_id = r2.id
			                                WHERE `posts`.`id` = :id');
			
		$resultSql->bindParam(':id', $id);
		$resultSql->execute();		
	    $weight =  $resultSql->fetch(PDO::FETCH_ASSOC);
	    
	    
	    return ($weight['weight'] == 0);
	    
		
	    
	}
	
	public function getIncidentContent($id) {
		
		$stmt = $this->conn->prepare('SELECT DISTINCT posts.* , posts.mod_timestamp as pmod_timestamp , members.username,  
										members.fullname, members.companies_id, video.status, video.stopped_datetime, 
										groups.name as gname, groups.id as gid, roles.name as rname, m2.fullname as m2name,
										m2.id as m2_id, posts.message, companies.file_server, companies.stream_server, companies.stream_file_server
												FROM posts 
												JOIN members ON posts.members_id=members.id
												JOIN groups ON members.groups_id=groups.id
												JOIN roles ON members.roles_id=roles.id
												JOIN members as m2 ON posts.mod_user = m2.id
												JOIN companies ON members.companies_id = companies.id
												LEFT JOIN video ON video.posts_id = posts.id 
												WHERE `posts`.`id` = :id
												' ) ;
		
		$stmt->bindParam(':id', $id);
		$stmt->execute();
		
		return  $stmt->fetch(PDO::FETCH_ASSOC);
		
	}
	
	public function deleteIncident($resultArray) {
		if($_SESSION['can_video_modify'] != '1' || empty($this->isPostAvailableForCurrentUser($resultArray['id']))) return false;
		  
		  
	

	
	if(DELETE_FILE_WITH_POST) {
	    
	    //A KÉPEK / VIDEÓK TÖRLÉSE ha töröltés a postot
	    $companiesId = $resultArray['companies_id'];
	    $dirName = substr(str_replace('-', '', $resultArray['datetime']),0,6);
	    $fileName = $this->getVideoFileName($resultArray['id']) . '.mp4';
	    
	    $ftp = FTP;
	    $compFtp = $ftp[$companiesId];
		$ftp_server = $compFtp['host'];
		
		try {
            $ftp_conn = ftp_connect($ftp_server);
            
            $login = ftp_login($ftp_conn, $compFtp['username'], $compFtp['password']);
            
            $file = '/' .$dirName . "/" . $fileName;
    
            // try to delete file
            if (!ftp_delete($ftp_conn, $file))
              {
                error_log("nem sikerült ftp-vel törölni");
              }
            
            // close connection
            ftp_close($ftp_conn);
            
		} catch(Exception $e) {
		    error_log($e->getMessage());
		}
	}
	
		$stmt = $this->conn->prepare('DELETE FROM posts WHERE id = :id');
		$stmt->bindParam(':id', $resultArray['id']);
		$stmt->execute();
        
			
		$stmt = $this->conn->prepare('INSERT INTO posts (id, members_id, event_type, mod_user, least_role_id, deleted)
									VALUES ( :id, :members_id, :event_type, :uid,:least_role_id ,1);');
		$stmt->bindParam(':id', $resultArray['id']);
		$stmt->bindParam(':members_id', $resultArray['members_id']);
		$stmt->bindParam(':event_type', $resultArray['event_type']);
		$stmt->bindParam(':uid', $_SESSION['id']);
		$stmt->bindParam(':least_role_id', $resultArray['least_role_id']);
		$stmt->execute();

		
		
	}
	
	public function addComment($id, $message) {
		if(empty($this->isPostAvailableForCurrentUser($id))) return false;
		  
	    $inci = $this->getIncidentContent($id);
	    
	    if(empty($inci['message']) && $inci['members_id'] == $_SESSION['id']) {
        	$stmt = $this->conn->prepare("UPDATE posts SET message = :message WHERE id=:id;");
    		$stmt->bindParam(':id', $id);
    		$stmt->bindParam(':message', $message);
    		$stmt->execute();
	    }
	    
		$stmt = $this->conn->prepare("INSERT comments (posts_id, members_id, message) VALUES (:id, :uid, :message) ;");
		$stmt->bindParam(':id', $id);
		$stmt->bindParam(':uid', $_SESSION['id']);
		$stmt->bindParam(':message', $message);
		$stmt->execute();
		
		header('Location: /incidentiframe&id=' . $id);
		
	}
	
	
	public function getSnapshots($id) {
		if(empty($this->isPostAvailableForCurrentUser($id))) return false;
		
		$stmt = $this->conn->prepare('SELECT DISTINCT posts.* , members.username 
										FROM posts JOIN members ON posts.members_id=members.id
										WHERE `posts`.`id` = :id AND members.companies_id = :companies_id' ) ;
			
		$stmt->bindParam(':companies_id', $_SESSION['companies_id']);
		$stmt->bindParam(':id', $id);
		$stmt->execute();
		
		
		
		$resultArray = $stmt->fetch(PDO::FETCH_ASSOC);
		
		            
		            
		$dirName = $resultArray['id'] . '_' . substr(hash('sha512', $resultArray['username'] . '||' . ACCESS_TOKEN_SALT . '||' . $resultArray['id']),0,48);
		
		$Ymd = str_replace('-', '', (substr($resultArray['datetime'], 0,10)));
                  
		$output = '';
		$folder = 'photos/' . $Ymd .'/' . $dirName .'/' ;
		if(file_exists($folder)) {
			$files = scandir($folder);
			$files = array_diff($files, array('.', '..'));
			rsort($files);
			foreach($files as $file) {
				if(strpos($file, 'thumb') === false) continue;
				$bigFile = str_replace('thumb_', '', $file); 
				$output .= '<a style="display: inline-block;" target="_blank" href="/'. $folder . $bigFile . '"><img alt="pic" height="360" src="/'. $folder . $file . '"></a> ';
			}
		} 
		
		return $output;
		
	}
	
	
	public function getComments($id) {		
		if(empty($this->isPostAvailableForCurrentUser($id))) return false;
		
		$stmt = $this->conn->prepare('select comments.*, members.fullname
						FROM `comments`
						LEFT JOIN members ON members.id = comments.members_id
						WHERE `comments`.`posts_id` = :id ORDER by datetime DESC ;');
		
		$stmt->bindParam(':id', $id);
		$stmt->execute();
		
		return  $stmt->fetchAll();
			
		
	}
	
	public function getLocationPathJs($id) {
		if(empty($this->isPostAvailableForCurrentUser($id))) return false;
		
			$i = 0;
	        $flight = 'var flightPlanCoordinates = [';
	        $userCoor = 'var userCoor = [';
	        $prepend = '';
	       
	   $stmt = $this->conn->prepare('select * FROM `coord`  WHERE `posts_id` = :id ORDER BY `datetime` ASC;');
		
		$stmt->bindParam(':id', $id);
		$stmt->execute();
		$resultCoordDb = $stmt->fetchAll(); 	       
		   
	        
	        foreach($resultCoordDb as $resultCoordArray) {	        	        
        	    if((isset($resultArray['first_pos_lat']) && !empty($resultArray['first_pos_lat'])) || ((isset($resultCoordArray['coord_lat']) && !empty($resultCoordArray['coord_lng'])) )) {
        	        
            	    $coord = isset($resultCoordArray['coord_lat']) ?   [$resultCoordArray['coord_lat'] , $resultCoordArray['coord_lng']] : [$resultArray['first_pos_lat'] , $resultArray['first_pos_lng']];
            	    $time = isset($resultCoordArray['coord_lat']) ? substr($resultCoordArray['datetime'], 11,8)  : substr($resultArray['datetime'], 11,8)  ;
            	    $flight .= '{lat: '. $coord [0] . ', lng: '. $coord [1] . '},';
            	    $userCoor .= '[ "' . $time . '",' .  $coord [0] . ', ' . $coord [1] . '],';
            	    
            	    $prepend = '<li class="l-location"><span href="#mapcolumn" class="a-location" data-nr="' . $i . '">' 
            	        .  substr($coord[0],0,10) . ', '. substr($coord[1],0,10) . ' - <span class="bold">' . $time  . '</span></span></li> ' . $prepend;
            	    
                    $i++;
    	        } 
	        }
	        
	        
	        
	        $flight = rtrim($flight, ',') . ']; ';
	        $userCoor = rtrim($userCoor, ',') . ']; ';
	        
	        $out =  $flight . $userCoor . "$('#navcoord').prepend('" . $prepend . "');  $('.main-nav').height($('#coordtext').width());";
		
			return $out;
		
	}
	
	
	
	
	
}
