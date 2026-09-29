<?php 

	$usersService = new UsersService();
		
		if(empty($usersService->hasPermission())) {
			echo NO_PERMISSION;
			exit;
		}
		
			
		$roles = $usersService->getRoles();
		$groups = $usersService->getGroups();
		$users = $usersService->getUsers();
		
?>

	