<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="utf-8">
    <title>SOSlive</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <script src="/js/jquery.min.js"></script>
    <script src="/js/jquery-ui.js"></script>
    <script src="/js/bootstrap.min.js" ></script>
    <!--<script src="https://www.google.com/recaptcha/api.js" async defer></script>-->
	
    <script type="text/javascript" src="/js/datatables.min.js"></script>
    <script type="text/javascript" src="/js/dataTables.bootstrap.min.js"></script>

	
    <script src='/js/jquery.mousewheel.min.js'></script>
	

	<!--<link href="style/bootstrap.css" rel="stylesheet" media="screen">-->
    <link href="style/main.css" rel="stylesheet" media="screen">    
	<link rel="stylesheet" href="/style/bootstrap.min.css" >
    
    <link rel="stylesheet" href="/style/jquery-ui.css">
	<link rel="stylesheet" href="/style/jquery.dataTables.min.css">
	
	<link rel="stylesheet" href="/style/style.css">
    <link rel="shortcut icon" type="image/png" href="/style/sos_icon.png"/>


    <script>
    function resizeIframe(obj) {
        obj.style.height = (obj.contentWindow.document.body.scrollHeight - 30) + 'px';
      }
      
      function getQueryVariable(variable) {
            var query = window.location.search.substring(1);
            var vars = query.split('&');
            for (var i = 0; i < vars.length; i++) {
                var pair = vars[i].split('=');
                if (decodeURIComponent(pair[0]) == variable) {
                    return decodeURIComponent(pair[1]);
                }
            }
            return false;
            //console.log('Query variable %s not found', variable);
        }
        
    </script>

</head>
<body>
<?php if($header !== false): ?>
<div class="container">
	<div>

        <?php if($logo === 1) echo '<div class="logo-1"><a href="/"><h1><img alt="logo" src="/style/logo_' . ($_SESSION['only_sos'] === '1' ? 'sos' : ($_SESSION['only_sos'] === '0' ? 'rci' : 'empty')) . '.png"></h1></a></div>'; ?>
        
        <div class="lang-1"></div>        
        
        <div class="lang-2"><?= LANG_FLAG ?></div>
            
        <?php if($logo === 2) echo '<div class="logo-2"><a href="/"><h1><img alt="logo" src="/style/logo_' . ($_SESSION['only_sos'] === '1' ? 'sos' : ($_SESSION['only_sos'] === '0' ? 'rci' : 'empty')) . '.png"></h1></a> </div>'; ?>

		<div style="clear: both"></div>
       
    </div>
    <div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
				ÍRÁSVÉDETT MÓD / READ ONLY MODE!
	</div>
	
 <?php endif; ?>

    
    