<?php 

$iService = new IncidentsService();
$uService = new UsersService();

$groups = $uService->getGroups();
$months = $iService->getMonths();


	
     
    