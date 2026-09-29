<?php
require_once('../inc/controller/core.php'); 

if(!isset($get['act']) || !is_string($get['act']) || empty(preg_match($usernamePm, $get['act']))) exit;

$file = __DIR__ . '/../inc/controller/ajax/' . $get['act'] . '.php';
if(file_exists($file)) {
	require_once($file);
} else {
	print "FILE NEM TALÁLHATÓ!";
}
