<?php

$requestData = $get;
if(!isset($requestData['order'])) exit;

//$g =  json_encode($requestData);
//print($g);


$dbLogin = new DbConn();
$incidentsService = new IncidentsService();

//szűrőket kinyerni
$dateInterval = [];
if(!empty($requestData['columns'][0]['search']['value'])) {
	
	$d = substr($requestData['columns'][0]['search']['value'],0,19);
	
	$dateFrom = new DateTime($d);
	$dateTo = new DateTime($d);
	$dateTo->modify('first day of next month');
		
	$dateInterval = [$dateFrom->format("Y-m") . '-01 00:00:00', $dateTo->format("Y-m") . '-01 00:00:00'];
	
	
}

$postsId = '';
if(!empty($requestData['columns'][1]['search']['value']) && is_numeric($requestData['columns'][1]['search']['value'])) {
	$postsId = $requestData['columns'][1]['search']['value'];
}

$groupId = '';
if(!empty($requestData['columns'][2]['search']['value']) && is_numeric($requestData['columns'][2]['search']['value'])) {
	$groupId = $requestData['columns'][2]['search']['value'];
}

$user = '';
if(!empty($requestData['columns'][3]['search']['value'])) {
	if(!is_numeric($requestData['columns'][3]['search']['value'])) {
		$user = '%' . substr($requestData['columns'][3]['search']['value'],0,16) . '%';
	} else {
		$user = $requestData['columns'][3]['search']['value'];
	}
}

$firstComment = '';
if(!empty($requestData['columns'][4]['search']['value'])) {
	$firstComment = '%' . substr($requestData['columns'][4]['search']['value'], 0,16) . '%';
}


//ordert kiszűrni
switch($requestData['order'][0]['column']) {
	case '0':
		$orderColumn = 'posts.datetime';
	break;
	case '1':
		$orderColumn = 'posts.id';
	break;
	case '2':
		$orderColumn = 'groups.name';
	break;
	case '3':
		$orderColumn = 'members.fullname';
	break;
	case '5':
		$orderColumn = 'm2.fullname';
	break;
	default: 
		$orderColumn = 'posts.datetime';
}

$dir = $requestData['order'][0]['dir'];
$orderDir = ($dir == 'desc') ? 'desc' : 'asc';

$length = intval($requestData['length']);
$start = intval($requestData['start']);



//összerakni a queryt
$sqlRetrieve = "
			                        SELECT DISTINCT posts.* , posts.mod_timestamp as pmod_timestamp, 
									members.username, members.fullname, video.status, 
			                        groups.name as gname, groups.id as gid, roles.name as rname, m2.fullname as m2name, m2.id as m2_id
			                                FROM posts 
			                                JOIN members ON posts.members_id=members.id
			                                JOIN groups ON members.groups_id=groups.id
			                                JOIN roles ON members.roles_id=roles.id
			                                JOIN members as m2 ON posts.mod_user = m2.id
			                                JOIN roles as r2 ON posts.least_role_id = r2.id
			                                LEFT JOIN video ON video.posts_id = posts.id 
			                                WHERE members.companies_id = :companies_id
											AND r2.weight <= (SELECT weight FROM roles r3 WHERE r3.id = :roles_id ) "
											
											
											. (($_SESSION['has_all_group'] === '1') ?'':' AND groups.id = :groups_id ')
											
											. ((empty($postsId )) ?'':' AND posts.id = :posts_id ')
											
											. ((empty($groupId)) ?'':' AND groups.id = :group_id ')
											
											. ((!empty($user) && !is_numeric($user)) ? ' AND members.fullname LIKE :user ' : '')
											
											. ((!empty($user) && is_numeric($user)) ? ' AND members.id = :user ': '')
											
											. ((empty($firstComment)) ?'':' AND posts.message LIKE :first_comment ')
											
											. ((empty($dateInterval )) ?'':' AND (posts.datetime >= :dateFrom AND posts.datetime < :dateTo)') . " ";
														
			                      $sqlEnd =   " ORDER BY " . $orderColumn . ($orderDir === 'desc' ? ' DESC' : ' ASC') . " LIMIT " . $length . " OFFSET ". $start ." ;";
		
	    $countFromDb = $dbLogin->conn->prepare(($sqlRetrieve));
	   
  		$countFromDb->bindParam(':roles_id', $_SESSION['roles_id']);
		$countFromDb->bindParam(':companies_id', $_SESSION['companies_id']);
		if(($_SESSION['has_all_group'] != '1')) $countFromDb->bindParam(':groups_id', $_SESSION['groups_id']);		
				
		if((!empty($postsId ))) $countFromDb->bindParam(':posts_id', $postsId);
		
		if((!empty($groupId ))) $countFromDb->bindParam(':group_id', $groupId);
		if((!empty($user))) $countFromDb->bindParam(':user', $user);
		if((!empty($firstComment ))) $countFromDb->bindParam(':first_comment', $firstComment);
		
		if(!empty($dateInterval)) {
			$countFromDb->bindParam(':dateFrom', $dateInterval[0]);
			$countFromDb->bindParam(':dateTo', $dateInterval[1]);
		}
		
		$countFromDb->execute();
		$filteredTotalData = $countFromDb->rowCount();
	    
	    
	    
	    
	    
	    
	    
	    
	
	    $dataFromDb = $dbLogin->conn->prepare(($sqlRetrieve . $sqlEnd));
		$dataFromDb->bindParam(':roles_id', $_SESSION['roles_id']);
		$dataFromDb->bindParam(':companies_id', $_SESSION['companies_id']);
		if(($_SESSION['has_all_group'] != '1')) $dataFromDb->bindParam(':groups_id', $_SESSION['groups_id']);		
				
		if((!empty($postsId ))) $dataFromDb->bindParam(':posts_id', $postsId);
		
		if((!empty($groupId ))) $dataFromDb->bindParam(':group_id', $groupId);
		if((!empty($user))) $dataFromDb->bindParam(':user', $user);
		if((!empty($firstComment ))) $dataFromDb->bindParam(':first_comment', $firstComment);
		
		if(!empty($dateInterval)) {
			$dataFromDb->bindParam(':dateFrom', $dateInterval[0]);
			$dataFromDb->bindParam(':dateTo', $dateInterval[1]);
		}
		
		$dataFromDb->execute();
		$dataFromDb = $dataFromDb->fetchAll();
		

		$data = [];		
		foreach($dataFromDb as $key => $row) {
			
			$t = new \DateTime($row['datetime']); 
			$today = new \DateTime(date('Y-m-d', time()) . ' 00:00:00');
			$isToday = $t->format('Y-m-d') == $today->format('Y-m-d');
			
			
			$data[$key]['0'] = ($row['deleted'] == '1' ? '<span class="glyphicon glyphicon-remove"></span> ' : ($row['status'] == 'RUNNING' ? ' <img src="/style/blinking_dot_1.gif"></a>' : 
					        ($row['event_type'] == 'PHOTO' ? '</a> <img src="/style/blinking_dot_4.gif">' : ($row['event_type'] == 'LIVE' ? '</a> <img src="/style/blinking_dot_3.gif">' : '</a> <img src="/style/blinking_dot_2.gif">')  )));
			
			$data[$key]['0'] .=
			 ' <a href="' . $row['id'] . '">' .(($isToday) ? $t->format('H:i') : $t->format('Y-m-d H:i')) .
					  ($row['event_type'] == 'SOSLIVE' ? '<span style="color:red;font-weight:bold"> (SOS)</span>' : '') . '</a>';
			
			
			
			
			$data[$key]['1'] = '<a href="' . $row['id'] . '">#' . $row['id'] . '</a>';
			
			$data[$key]['2'] = '<a href="/users?group=' . $row['gid'] .'"> ' . $row['gname'] . '</a>';
			$data[$key]['3'] = '<a href="/user?id=' . $row['members_id'] . '">' . $row['fullname'] . '</a> (' . $row['rname'] . ')';
			
									
			$data[$key]['4'] = (empty($row['message']) ?'' : substr($row['message'], 0,64) . '...');
			
			$t= new DateTime($row['pmod_timestamp']); 
			$data[$key]['5'] = '<a href="/user?id=' . $row['m2_id'] .'">' . $row['m2name'] . '</a> - ' . $t->format('Y-m-d H:i') . '';
			
		}
		

$return = [

			"draw"            => intval( $requestData['draw'] ),   // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            "recordsTotal"    => $filteredTotalData, //intval( $totalData ),  // total number of records
            "recordsFiltered" => $filteredTotalData, //intval( $totalData ), // total number of records after searching, if there is no searching then totalFiltered = totalData
            "data"  => $data
];			

echo json_encode($return);

exit;