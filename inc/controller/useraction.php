<?php
		
	$userService = new UserService();
	$userService->setGet($get);
	$result = [];
	$isEdit = false;
	$itsme = (isset($_SESSION['id']) && isset($get['id']) && $get['id'] == $_SESSION['id']);
	$editData = [];
	$hasPermission = $userService->hasPermission();
	$canVerify = false;
		
	//Általános jogosultság	  
	if(!$userService->hasPermission() 
		|| (isset($post['uid']) && !$userService->isAvailableUserForCurrentUser($post['uid']))
		|| (isset($get['id']) && !$userService->isAvailableUserForCurrentUser($get['id']))
	) {
		echo NO_PERMISSION;
		exit;
	}
  
  
  if(!empty($post)) {
	
	
	$id = isset($post['uid']) ?  substr($post['uid'], 0,11) : "" ;
	$username = strtolower(substr($post['newuser'],0,32));
	$fullname = substr($post['fullname'],0,64);
	$pw1 = substr($post['password1'],0,128);
	$pw2 = substr($post['password2'],0,128);
	$newpw = hash('sha512', $username . '|||' . $pw1);
	$role = substr($post['role'],0,11);
	$group = substr($post['group'],0,11);
	$verified = (isset($post['verified']) && $post['verified'] === '0') ? '0' : '1';
	$isDelete = isset($post['del']) ?  substr($post['del'],0,1) : "" ;
	$emails = isset($post['emails']) ? substr($post['emails'],0,512) : '';
    $emailText = isset($post['emailtext']) ? substr($post['emailtext'],0,1024) : '';
	$smstelnumbers = isset($post['smstelnumbers']) ? substr($post['smstelnumbers'],0,256) : '';
	$smstext = isset($post['smstext']) ? substr($post['smstext'],0,160) : '';
	
	
	//** Ha MÓDOSÍTÁS VAN és gyengébb a választott usernél a weight VAGY nem saját magam vagyok, akkor kocc: a hasPermissiont false-ra tesszük
	if(!empty($id)) $hasPermission = $userService->isAvailableUserForCurrentUser($id);

	
	if(!$hasPermission) {
		//ne nincs permission (gyengébb weight vagy nem saját magam) akkor kocc
		echo NO_PERMISSION;
	
	} elseif ($isDelete != '1'  &&  ((empty($id)) xor (!empty($pw1) && !empty($id)) ) && ((($pw1 != $pw2 && empty($id)) || ($pw1 != $pw2 && $_SESSION['id'] == $id)))) {
		//ha üresen hagyták a jelszót új usernál vagy saját módosításánál:
		
		$result = ['danger', PASSWORD_NOT_MATCHED];
		
		
		
	} elseif ($isDelete != '1'  &&  ((empty($id)) xor (!empty($pw1) && !empty($id)) ) && (strlen($pw1) < 6 || !(1 === preg_match('~[0-9]~', $pw1)) || !(1 === preg_match('~[a-zA-Z]~', $pw1))  )) {
		
		$result = ['danger',PASSWORD_LESS ];
		
	} elseif (!ctype_alnum($username)) {
		
		$result = ['danger',"ERROR IN USERNAME (a-z0-9)" ];
	 

	//** Ha túl vagyunk a permission vizsgálat és a jelszó validálásán
	} else {
		
		$isValid = (
			!empty($username) 
			&& !empty($fullname)  
			&& is_numeric($role) 
			&& is_numeric($group) );
			
			
		
		//** DELETE: 
		if($isDelete === '1' && !empty($id)){
			
			$response = $userService->deleteUser($id);
				
				if ($response == 'true') {
					$result = ['success', L_SUCCESS_DELETE];
				} else {
					DbConn::mySqlErrors($response);
				}
		
		//** MÓDOSÍTÁS:
		} elseif($isValid && !empty($id)) {
				
				if(empty($pw1)) $newpw = "";
				
					$response = $userService->updateUser($username, $fullname, $id, $newpw,  $group, $role, $verified,$emails,$emailText,$smstelnumbers,$smstext);
				
				if ($response == 'true') {
					$result = ['success', L_SUCCESS_MODIFY];
				} else {
					DbConn::mySqlErrors($response);
				}
						
		
			
		//** ÚJ USER:
		} elseif($isValid && !empty($pw1)) {					
				
				$response = $userService->createUser($username, $fullname, $id, $newpw, $group, $role, $verified, $emails,$emailText,$smstelnumbers,$smstext);
				
				if ($response == 'true') {
					$result = ['success', L_SUCCESS_ADDED];
				} else {
					DbConn::mySqlErrors($response);
				}
			
		} else {
			echo 'An error occurred on the form... try again';
		}
	}	
	
	
  }
	  
	  
	  //eldöntjük, hogy új, vagy módosítás
	if(isset($get['id']) && is_numeric($get['id'])) {
		$editData = $userService->getEditData();
		$isEdit = !empty($editData);
	}
	
	$roles = $userService->getRoles();
	$groups = $userService->getGroups();
	$canVerify = ($isEdit && ($_SESSION['id'] != $editData['id']) && !empty($_SESSION['can_user_modify']));  
	  
  
		