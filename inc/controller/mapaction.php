<?php 

$uService = new UsersService();
$iService = new IncidentsService();
$iService->setGet($get);

$groups = $uService->getGroups();

	$newUrl = '/map?1=1';
	foreach($get as $key => $val) {
		if($key == 'event_type' || $key == 'groupid' || $key == 'userid' ) continue;
		$newUrl .= '&' . $key . '=' . $val;
	}
	
	
	$allUser = $iService->getUsers();
	$tableRow = '';
	
	   if(isset($get['n'])){
       
            $playSound = false;
            
            $_SESSION['loadedTime'] = time();
			
			$resultCoords = $iService->getMapIncidents();

			$userCoor = 'userCoor = [';
			
	        foreach ($resultCoords as $i => $resultCoordArray) {  

				$t = new \DateTime($resultCoordArray['datetime']); 
				$today = new \DateTime(date('Y-m-d', time()) . ' 00:00:00');
				$isToday = $t->format('Y-m-d') == $today->format('Y-m-d');
				$createdTime = (($isToday) ? $t->format('H:i') : $t->format('Y-m-d H:i'));
            
                $tableRow .=  '<tr>';
                
                    $t = new \DateTime($resultCoordArray['datetime']); 
			        $today = new \DateTime(date('Y-m-d', time()) . ' 00:00:00');
			        $isToday = $t->format('Y-m-d') == $today->format('Y-m-d');
                
                $tableRow .= '<td data-sort="'. $resultCoordArray['datetime'] . '"><a class="avideo" data-nr="'.$i.'" href="#"><img alt="blink" src="/style/blinking_dot_' . (($resultCoordArray['status'] == 'RUNNING') ? '1' : ( ($resultCoordArray['status'] == 'STOPPED' && $resultCoordArray['event_type'] == 'SOSLIVE') ? '2' : ($resultCoordArray['status'] == 'STOPPED' && $resultCoordArray['event_type'] == 'LIVE' ? '3' : '4'))) . '.gif"> '.$createdTime .
                ($resultCoordArray['event_type'] == 'SOSLIVE' ? '<span style="color:red;font-weight:bold"> (SOS)</span>' : '') 
                 . '</a></td>'; 
                
				$tableRow .= '<td><a href="/' . $resultCoordArray['id'] . '">#' .  $resultCoordArray['id'] . '</a></td>';
                $tableRow .= '<td><a href="/users?group='. $resultCoordArray['gid'] .'">' .  $resultCoordArray['gname'] . '</a></td>';
                
                $tableRow .= '<td><span class="glyphicon glyphicon-user" style="font-size: 0.75em"></span> <a href="/user?id='. $resultCoordArray['members_id'].'">' .  $resultCoordArray['fullname'] . ' (' . $resultCoordArray['rname'] . ')' . '</a></td>'; 
    
                $tableRow .= '<td>' .  (empty($resultCoordArray['message']) ?'' : substr($resultCoordArray['message'], 0,64) . '...') .'</td>';
                
        	    $coord = [$resultCoordArray['first_pos_lat'], $resultCoordArray['first_pos_lng']];
        	 
        	    $time = substr($resultCoordArray['datetime'], 11,5);
        	    $timeDir = str_replace('-','', substr($resultCoordArray['datetime'], 0,10));
        	    
        	    $userCoor .= '
        	    [ "' . $createdTime . '",' .  $coord [0] . ', ' . $coord [1] . ', ' . (($resultCoordArray['status'] == 'RUNNING') ? '1' : ( ($resultCoordArray['status'] == 'STOPPED' && $resultCoordArray['event_type'] == 'SOSLIVE') ? '2' : ($resultCoordArray['status'] == 'STOPPED' && $resultCoordArray['event_type'] == 'LIVE' ? '3' : '4'))) .', 
        	    "#' .$resultCoordArray['id'] . '", "' . $resultCoordArray['fullname'] . '", 
        	     "' . $resultCoordArray['gname'] . '","' . $resultCoordArray['id'] . ' "],';
        	    
        	    $tableRow .= '</tr>';
        	    
	        }
	        
	        $userCoor = rtrim($userCoor, ',') . "];  \n";
			
	   }
	
	
		
	
?>


