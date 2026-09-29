<?php

//autoloader: include/roleprocess.php fájl 
	$roleService = new RoleService();
	$roleService->setGet($get);
	$result = '';
	$isEdit = false;
	$editData = [];
	
  
  	//Általános jogosultság	  
	if(!$roleService->hasPermission() 
		|| (isset($post['rid']) && !$roleService->isAvailableRoleForCurrentUser($post['rid']))
		|| (isset($get['id']) && !$roleService->isAvailableRoleForCurrentUser($get['id']))
	) {
		echo NO_PERMISSION;
		exit;
	}

  

  if(!empty($post)) {
	$rid = (isset($post['rid']) ?  $post['rid'] : "" );
	$isDelete = isset($post['del']) ?  $post['del'] : "" ;
	$name = substr($post['name'],0,32);

		
		//** DELETE
		if($isDelete === '1' && !empty($rid)){
			$response = $roleService->deleteRole($rid);
				
				if ($response == 'true') {
					$result =  L_SUCCESS_DELETE;
				} else {
					DbConn::mySqlErrors($response);
				}
		}
		//** MÓDOSÍTÁS
		elseif(!empty($rid)) {
				$response = $roleService->updateRole($rid,$name);
				//Success
				if ($response == 'true') {

					$result = L_SUCCESS_MODIFY;

				} else {					
					//Failure
					DbConn::mySqlErrors($response);

				}
		//** ÚJ CSOPORT		
		} else {
		
				$response = $roleService->createRole($name);

				if ($response == 'true') {
					$result = L_SUCCESS_ADDED;
				} else {
					DbConn::mySqlErrors($response);
				}
			}
  }
	  
  
	
	if(isset($get['id']) && is_numeric($get['id'])) {
		$editData = $roleService->getEditData();
		$isEdit = !empty($editData);
	}
		
		
	

