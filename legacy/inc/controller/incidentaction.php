<?php 

	$date = new DateTime();
	$iService = new IncidentsService();
		
	
	
	$rService = new RolesService();
	$resultArray = $iService->getIncidentContent($get['q']);
	if(isset($post['delete']) && $post['delete'] == '1') {
		$iService->deleteIncident($resultArray);	
		$resultArray = $iService->getIncidentContent($get['q']);
	} 	   
	
	$hasPerm = $iService->isPostAvailableForCurrentUser($get['q']);
	$allWeakerRoles = $rService->getVisisbleRoleForMe(true, true);
	
    $videoFileName = $iService->getVideoFileName($get['q']);
	
	//ha nincs találat akkor exit
	if(empty($resultArray) || empty($hasPerm)) {
	   print "<h2>". NO_RESULT_OR_NO_PERM ."</h2>";
	   exit;
	} 	 
	
	$created = new DateTime($resultArray['datetime']);
	 
	 $mp4path = $resultArray['file_server'] .'/' . substr(str_replace('-', '' , $resultArray['datetime']),0,6) . '/' . $videoFileName . '.mp4';

        $getHeaders = get_headers($mp4path);
        $existsInFtp = (strpos($getHeaders[0], '200') !== false);

    if (!$existsInFtp)  { 
        $mp4FileName = $resultArray['stream_file_server'] . '/' . $videoFileName . '.mp4';
    } else {
        $mp4FileName = $mp4path;
    }
	
	
?>
  
