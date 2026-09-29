<?php 

	$csrf = $_SESSION['csrf-token'];
	$rolesService = new RolesService();

	//jogosultságellenőrzés
	if(!$rolesService->hasPermission()) {
		echo NO_PERMISSION;
		exit;
	}		
		
		
	if(!empty($post)) {
	
		try {
			
			$rolesService->setPost($post);
			$rolesService->sortByWeight();
			$rolesService->setAllToNull();
			$rolesService->setPermissionInDb();
			
				
		} catch (PDOException $e) {
			echo 'ERROR';
			error_log("Error: " . $e->getMessage());
			exit;
		} catch (Exception $e) {
			echo 'ERROR';
			error_log("Error: " . $e->getMessage());
			exit;
		}
		
			echo '<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
			'.L_SUCCESS_MODIFY.'</div>';
			
	}
	
	$allRoles = $rolesService->getAllRoles();
	$roles = $rolesService->getVisisbleRoleForMe();
			
