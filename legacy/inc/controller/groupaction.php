<?php

	$groupService = new GroupService();
	$groupService->setGet($get);
	$result = '';
	$isEdit = false;
	$editData = [];

	
	//Általános jogosultság	  
	if(!$groupService->hasPermission() 
		|| (isset($post['gid']) && !$groupService->isAvailableGroupForCurrentUser($post['gid']))
		|| (isset($get['id']) && !$groupService->isAvailableGroupForCurrentUser($get['id']))
	) {
		echo NO_PERMISSION;
		exit;
	}
	


if(!empty($post)) {

	$gid = (isset($post['gid']) ?  $post['gid'] : "" );
	$isDelete = isset($post['del']) ?  $post['del'] : "" ;
	$name = substr($post['name'],0,32);
	$emails = isset($post['emails']) ? substr($post['emails'],0,512) : '';
	$emailText = isset($post['emailtext']) ? substr($post['emailtext'],0,1024) : '';
	$smstelnumbers = isset($post['smstelnumbers']) ? substr($post['smstelnumbers'],0,256) : '';
	$smstext = isset($post['smstext']) ? substr($post['smstext'],0,160) : '';
					
		
		//** DELETE
		if($isDelete === '1' && !empty($gid)){
			$response = $groupService->deleteGroup($gid);
				
				if ($response == 'true') {
					$result = L_SUCCESS_DELETE;
				} else {
					DbConn::mySqlErrors($response);
				}
		}
		//** MÓDOSÍTÁS
		elseif(!empty($gid)) {
						
				$response = $groupService->updateGroup($gid,$name,$emails,$emailText,$smstelnumbers,$smstext);
				
				//Success
				if ($response == 'true') {

					$result = L_SUCCESS_MODIFY;

				} else {
					
					//Failure
					DbConn::mySqlErrors($response);

				}
		//** ÚJ CSOPORT		
		} else {
		
				$response = $groupService->createGroup($name,$emails,$emailText,$smstelnumbers,$smstext);
				if ($response == 'true') {
					$result = L_SUCCESS_ADDED;
				} else {
					DbConn::mySqlErrors($response);
				}
			}
}

	//eldöntjük, hogy új, vagy módosítás
	if(isset($get['id']) && is_numeric($get['id'])) {
		$editData = $groupService->getEditData();
		$isEdit = !empty($editData);
	}
	
	
