<?php

  require_once 'core.php';
  $dbLogin = new DbConn;

  
	if (((!isset($get['q']) && (!isset($_SESSION['id']) || empty($_SESSION['id']))) 
	|| (isset($get['q']) && $get['q'] != 'login' && !is_numeric($get['q']) && (!isset($_SESSION['id']) || empty($_SESSION['id'])) && $get['q'] != 'incidentiframe'))) {
		header("location: /login");
	} elseif(isset($_SESSION['id']) && is_numeric($_SESSION['id'])) {
		
		 $stmt = $dbLogin->conn->prepare("SELECT members.*, companies.only_sos, roles.weight, roles.has_all_group, roles.can_user_modify, 
		roles.can_video_modify, roles.can_system_modify FROM members 
		INNER JOIN roles ON members.roles_id = roles.id 
		INNER JOIN companies ON members.companies_id = companies.id 
		WHERE members.id = :id");
        $stmt->bindParam(':id', $_SESSION['id']);
        $stmt->execute();

        // Gets query result
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
		
		if($result['verified'] !== '1') {
			session_destroy();
			header("location: /login");
		} else {		
			$_SESSION['fullname'] = $result['fullname'];
			$_SESSION['groups_id'] = $result['groups_id'];
			$_SESSION['roles_id'] = $result['roles_id'];
			$_SESSION['weight'] = $result['weight'];
			$_SESSION['can_system_modify'] = $result['can_system_modify'];
			$_SESSION['can_user_modify'] = $result['can_user_modify'];
			$_SESSION['can_video_modify'] = $result['can_video_modify'];
			$_SESSION['has_all_group'] = $result['has_all_group'];
			$_SESSION['companies_id'] = $result['companies_id'];
			$_SESSION['only_sos'] = $result['only_sos'];
		}		
	} 
	

	    $_SESSION['fullname'] = isset($_SESSION['fullname']) ? $_SESSION['fullname'] : '';
		$_SESSION['groups_id']	= isset($_SESSION['groups_id']) ? $_SESSION['groups_id'] : '';
		$_SESSION['roles_id']	= isset($_SESSION['roles_id']) ? $_SESSION['roles_id'] : '';
		$_SESSION['weight']	= isset($_SESSION['weight']) ? $_SESSION['weight'] : '';
		$_SESSION['can_system_modify']	= isset($_SESSION['can_system_modify']) ? $_SESSION['can_system_modify'] : '';
		$_SESSION['can_user_modify']	= isset($_SESSION['can_user_modify']) ? $_SESSION['can_user_modify'] : '';
		$_SESSION['can_video_modify'] = isset($_SESSION['can_video_modify']) ? $_SESSION['can_video_modify'] : '';
		$_SESSION['has_all_group']	= isset($_SESSION['has_all_group']) ? $_SESSION['has_all_group'] : '';
		$_SESSION['companies_id']	= isset($_SESSION['companies_id']) ? $_SESSION['companies_id'] : ''; 
		$_SESSION['only_sos']	= isset($_SESSION['only_sos']) ? $_SESSION['only_sos'] : ''; 

	
	
	
	