<?php 

	require_once(__DIR__ . '/../inc/controller/core.php'); 
	
	//azért kell külön, mert ha cronjob van akkor bekavara a SESSION
	require_once(__DIR__ . '/../inc/controller/header_check.php'); 
	
    $header = true;
    $logo = 1;
    $footer = true;
    
    
	//Főoldal landing page
	if((empty($get)) || (!isset($get['q']) && isset($get['lang']))) { 
		$logo = 2;
		require_once('../inc/header.php');
		require_once(__DIR__ . '/../inc/controller/indexaction.php'); 
		require_once('../inc/page/index.php'); 
		require_once('../inc/footer.php'); 
		
	}


	//oldalkérések
    switch($get['q']) {
    
        case 'login':
			if(isset($_SESSION['id'])) header('Location: /');
            require_once('../inc/header.php');
			require_once(__DIR__ . '/../inc/controller/loginaction.php'); 
            require_once('../inc/page/login.php'); 
            require_once('../inc/footer.php'); 
        break;
        case 'logout':
            session_destroy();
			header('Location: /');
        break; 
        
		case 'incidentiframe':
            $header = false;
			require_once('../inc/header.php');
			
			require_once(__DIR__ . '/../inc/controller/incidentiframeaction.php'); 
			require_once(__DIR__ . '/../inc/page/incidentiframe.php'); 
			
			$footer = false;
			require_once('../inc/footer.php');
		   
        break; 
		
		default:
		require_once('../inc/header.php');			
			
			if(is_numeric($get['q'])) {
				
				require_once(__DIR__ . '/../inc/controller/incidentaction.php');
				require_once(__DIR__ . '/../inc/page/incident.php'); 		
			
			} else {
				
				if(empty(preg_match($usernamePm, $get['q']))) exit;
					$file = __DIR__ . '/../inc/page/'. $get['q'] .'.php';
					if(file_exists($file))	 {	
						require_once(__DIR__ . '/../inc/controller/'. $get['q'] .'action.php'); 
						require_once($file); 
				} else {
					print "<h2>Nem található ilyen oldal!</h2>";
				}				
											
			}
			
			require_once('../inc/footer.php'); 
			
        break;
                
    }
    
    
  



   
	


 ?>


	