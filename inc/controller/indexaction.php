<?php

$username = isset($_SESSION['username']) ? $_SESSION['username'] :'';
$canUserEdit = (isset($_SESSION['can_user_modify']) && $_SESSION['can_user_modify'] == '1');
$canSystemEdit = (isset($_SESSION['can_system_modify']) && $_SESSION['can_system_modify'] == '1');
$uid = isset($_SESSION['id']) ? $_SESSION['id'] :'';


?>




