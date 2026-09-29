<?php
	
		$groupsService = new GroupsService();
		
		if(empty($groupsService->hasPermission())) {
			echo NO_PERMISSION;
			exit;
		}
			
		$groups = $groupsService->getGroups();
		
			

		

	
       