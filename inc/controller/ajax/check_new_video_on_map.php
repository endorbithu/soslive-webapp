<?php

if(empty($get['n'])) exit;

$iService = new IncidentsService();
$iService->setGet($get);

if(!isset($_SESSION['loadedTime'])) $_SESSION['loadedTime'] = time();

print (($iService->getMapIncidents(true)) > 0);

$_SESSION['loadedTime'] = time();