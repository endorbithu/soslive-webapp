
      
	  <form class="form-signin" name="form1" method="post">
	  	<h2 class="form-signin-heading"><?= L_LOGIN ?></h2>
		<input type="hidden" name="csrf-token" id="csrf-token" value="<?= $csrf  ?>">
        <input name="myusername" id="myusername" type="text" class="form-control" value="sosliveadmin" disabled="disabled" placeholder="<?= L_USERNAME ?>" autofocus>
        <input name="mypassword" id="mypassword" type="password" class="form-control" value="test123" disabled="disabled" placeholder="<?= L_PASSWORD ?>">
        <button name="Submit" id="submit" class="btn btn-lg btn-primary btn-block" type="submit"><?= L_LOGIN ?></button>
        <div id="message"></div>
      </form>
 
    <script src="/js/login.js"></script>