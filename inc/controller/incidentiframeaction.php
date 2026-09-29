<?php 
   
	$q = $get['id'];
	$iService = new IncidentsService();
	$hasPerm = $iService->isPostAvailableForCurrentUser($q);  
	
	if(!$hasPerm) {
	   print "<h2>". NO_RESULT_OR_NO_PERM ."</h2>";
	   exit;
	}
	
	//jött comments              
	if(!empty($post) && !empty($post['message'])) $iService->addComment($q, $post['message']);
	
	$resultArray = $iService->getIncidentContent($q);
	$photos = $iService->getSnapshots($q);
	$comments = $iService->getComments($q);
	$locPathJs = $iService->getLocationPathJs($q);
	
   

	
?>
	
        
        