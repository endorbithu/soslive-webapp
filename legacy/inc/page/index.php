
	
      <div class="index-menu">

		
        <div class="row">
            <div class="col-xs-3"> </div>
            <div class="col-xs-6">
	             <div class="input-group">
		             <div class="input-group-addon">#</div>
		             <input type="text" id="start-id" name="start-id" class="form-control input-lg" >
		        </div>
		    </div>
		    <div class="col-xs-3"> </div>
		</div>
		
		<h2 class="index-h2"><?= $username ?></h2>
			<a href="/map" class="btn btn-lg btn-danger btn-block"><span class="glyphicon glyphicon-globe"></span> <?= L_LISTEN_ON_MAP ?></a>		
			<a href="/incidents" class="btn btn-lg btn-primary btn-block"><span class="glyphicon glyphicon-list-alt"></span> <?= L_POSTS ?></a>		
			<a href="/incidents?user=<?= $uid ?>" class="btn btn-lg btn-default btn-block"><span class="glyphicon glyphicon-list-alt"></span> <?= L_MY_INCIDENTS ?></a>		
		<hr>
		
		<?php if($canUserEdit): ?>
			<a href="/users" class="btn btn-lg btn-primary btn-block"><span class="glyphicon glyphicon-user"></span> <?= L_USERS ?></a>
		<?php endif; ?>	
		

		<?php if($canSystemEdit): ?>
			<a href="/groups" class="btn btn-lg btn-primary btn-block"><span class="glyphicon glyphicon-user little-user"></span><span class="glyphicon glyphicon-user"></span>  <?= L_GROUPS ?></a>
			<a href="/roles" class="btn btn-lg btn-primary btn-block"><span class="glyphicon glyphicon-lock"></span> <?= L_PERMISSIONS ?></a>
		<?php endif; ?>
					

		
		<a href="/user?id=<?= $uid ?>" class="btn btn-lg btn-default btn-block"><span class="glyphicon glyphicon-pencil"></span> <?= L_MY_ACCOUNT ?></a>		
	

		<a href="/logout" class="btn btn-default btn-lg btn-block"><?= L_LOGOUT ?></a>
		<br><br>
			<a href="/contact" id="cotact" class="btn btn-sm btn-default btn-block"><?= ERROR_REPORT ?></a>
      </div>
      
      <script>
          
           //wire up keyup events
            $('#start-id').on('keyup',function(){
                if(event.keyCode==13){
                    window.location.href = "/" + this.value;
                }
            });
      </script>