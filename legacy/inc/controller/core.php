<?php

//------------------------- MINDEN mozznatnál lefuttatandó dolgok START ---------------------------------------

$_SERVER = filterRecursive($_SERVER); //a session filterezése lentebb van
$get = filterRecursive($_GET);
$post = filterRecursive($_POST);
$_POST = [];
$_GET = [];
$_COOKIE = filterRecursive($_COOKIE);

require_once(__DIR__ . '/../config/config.php');

date_default_timezone_set(TIMEZONE);

$urlPm = '/^[A-Za-z0-9\.\:\/]+$/i';
$usernamePm = '/^[A-Za-z0-9\.\_]+$/i';
$coordPm = '/^[0-9\.\,]+$/i';

	
//ha nem cronjob hanem request (tehát vami browserből jön) akkor mehet a SESSION amúgy emiatt elszáll
if(isset($_SERVER['REQUEST_METHOD'])){
    
    session_start();
    session_regenerate_id();
	$_SESSION = filterRecursive($_SESSION);
	
	//CSRF ha még nincs a sessionben, ha van akkor ne csináljunk újat, elég
	if(!isset($_SESSION['csrf-token']) || empty($_SESSION['csrf-token'])) {
		 $_SESSION['csrf-token'] = hash('sha512' , 'sdfsdf' . rand(0,99999999) . time() . 'soedhsdfbhdsbfsdff');
	}
		
    if(!array_key_exists('lang', $_SESSION)) {
        if((isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) && !(strpos($_SERVER['HTTP_ACCEPT_LANGUAGE'],'hu' ) === false)) {
            $_SESSION['lang'] = 'hu';
        } else {
            $_SESSION['lang'] = 'en';
        }
        
    }

    if(array_key_exists('lang', $get)) {
        
        if(!is_string($get['lang'])) $get['lang'] = 'en';
        $lang = substr($get['lang'], 0, 2);
        
        if(file_exists(__DIR__ . '/../lang/' . $lang . '.php')) {
            $_SESSION['lang'] = $lang;
        }
    }
    
    require_once(__DIR__ . '/../lang/' . $_SESSION['lang'] . '.php');
    
}

//CSRF ellenőrzés, az összes post hívásnál van hidden element csrf-token néven még a logon ajax hívásánál is, 
//tehát ha bármi post jön kell lennie mert elszáll
//tcurl => a nginx rtmp szerver post üzenetében van benne, így ki tudjuk venni az ellenőrzés alól
if(!empty($post) && !isset($post['tcurl']) && !isset($post['app_token']) && (!isset($post['app-token']) || !in_array($post["app_token"], APIS)) &&
	(!isset($post['csrf-token'])
	|| empty($post['csrf-token'])
	|| (($post['csrf-token']) != $_SESSION['csrf-token']))) {
		print($_SESSION['csrf-token'] . "\n" . $post['csrf-token']);
		echo ' - ERROR: CSRF TOKEN HAS NOT MATCHED!';
		exit;
	}
	
	
//------------------------- MINDEN mozznatnál lefuttatandó dolgok START ---------------------------------------


	
	
// ---------------------  ÁLTALÁNOS FÜGGVÉNYEK   START -------------------------------------------

//Class Autoloader
spl_autoload_register(function ($className) {

    $className = strtolower($className);
    $path = __DIR__ . "/../service/{$className}.php";

    if (file_exists($path)) {
        require_once($path);
    } else {
        die("The file {$className}.php could not be found.");
    }
});

function filterRecursive($array)
{
    
	if (!is_array($array)) return [];
	$helper = [];
	foreach ($array as $key => $value) {
		$key = strip_tags(substr(hardFilter($key), 0, 32 ));
		$helper[$key] = is_array($value)
			? filterRecursive($value)
			: (is_string($value) ? strip_tags(substr(hardFilter($value), 0, 512 )) : ((is_numeric($value) || is_bool($value)) ? $value : ""));
	}
	return $helper;
}


function hardFilter($string) {
    if(!is_string($string) && !is_numeric($string)) return 'NEM-SZTRING VAGY SZÁM';
    
    $patterns = [];
    $replacements = [];
    $patterns[0] = '/\-\-/';
    $replacements[0] = '- ';
    $patterns[1] = '/\;/';
    $replacements[1] = ',';
    $patterns[2] = '/\$/';
    $replacements[2] = 'Dollar';
    $patterns[3] = '/\%/';
    $replacements[3] = '÷';
	$patterns[4] = '/\'/';
    $replacements[4] = '˝';
	$patterns[5] = '/\"/';
    $replacements[5] = '˝';  
	
    $string = preg_replace($patterns, $replacements, $string);
    $string = preg_replace('/[^a-zA-Z0-9_.\,\öÖüÜóÓőŐúÚéÉáÁűŰíÍÀÂÃÄÅÆÈÊËÌÎÏÒÔÕÙÛàâãäåèêëìîïòôõùûß\-\@\+\˝\_\€\÷\§\?\:\!\(\)]/', ' ', $string);
    
    return $string;
    	
}

 
// ---------------------  ÁLTALÁNOS FÜGGVÉNYEK   END -------------------------------------------

	

