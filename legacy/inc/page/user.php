	<?php if(!empty($result)): ?>
			<div class="alert alert-<?= $result[0] ?>">
			<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
			<?= $result[1] ?></div>
			<div id="returnVal" style="display:none;">true</div>
		<?php endif; ?>
		
		
	<form class="common-form index-menu" id="usersignup" name="usersignup" method="post">
	<input type="hidden" name="csrf-token" value="<?= $_SESSION['csrf-token'] ?>">
	<a href="/users"><span class="glyphicon glyphicon-arrow-left"> </span> <?= L_BACK_TO_USERS ?></a>
		 <h2 class="form-signup-heading"><?= $isEdit ? $editData['fullname'] :  L_ADD_USER ?></h2>
		<?= $isEdit ? '<input type="hidden" name="uid" id="uid" value="' . $editData['id'] . '">' : ''; ?>
		
		<input name="newuser" id="newuserhidden" type="hidden" value="<?= $isEdit ? $editData['username']: '' ?>" >
        <input name="newuser" id="newuser" type="text" class="form-control" <?= $isEdit ? ' disabled="disabled" ' : '' ?> value="<?= $isEdit ? $editData['username']: '' ?>" placeholder="<?= L_USERNAME ?> *" autofocus>
        <input name="fullname" id="fullname" type="text" value="<?= $isEdit ? $editData['fullname']: '' ?>" class="form-control" placeholder="<?= L_NAME ?> *">
		<select class="form-control" name="role" id="role" form="usersignup">
		<option value=""><?= L_CHOOSE ?>*</option>
			<?php	
				foreach($roles as $row){ 		
					print '<option value="'.$row['id'].'" ' . ($isEdit && $row['id'] == $editData['roles_id'] ? 'selected': '') . '>' . $row['name'].  '</option>';
				} 	
				
			?>
		</select>
		<select class="form-control" name="group" id="group" form="usersignup">
		<option value=""><?= L_CHOOSE ?>*</option>
			<?php
			
				foreach($groups as $row){ 		
					echo '<option value="'.$row['id'].'" ' . ($isEdit && $row['id'] == $editData['groups_id'] ? 'selected': '') . '>' . $row['name'].  '</option>';
				} 		
						
			?>
		</select>
		<?php if($isEdit && !$itsme): ?>
			<input type="checkbox" name="verified" id="resetpassw" > 	<label><?= L_PASSWORD_RESET ?></label>
		<?php endif; ?>
		
		<div id="reset-cont" style="display: <?= $isEdit && !$itsme ? 'none' : 'block' ?>">

            <input name="password1" id="password1" type="password" class="form-control" placeholder="<?= L_PASSWORD ?> *">
            <input name="password2" id="password2" type="password" class="form-control" placeholder="<?= L_REPEAT_PASSWORD ?> *">
		</div>
		<hr>
		<input name="emails" id="emails" type="text" class="form-control" value="<?= $isEdit ? $editData['alert_emails']: '' ?>" placeholder="<?= L_NOTIFIED_EMAILS ?>" >
          
         <label><?= L_EMAIL_TEXT ?></label>
         <textarea name="emailtext" id="emailtext" class="form-control"  ><?= $isEdit ? $editData['emailtext']: '' ?></textarea>
        <input name="smstelnumbers" id="smstelnumbers" type="text" class="form-control" value="<?= $isEdit ? $editData['smstelnumbers']: '' ?>" placeholder="<?= L_NOTIFIED_TELNUM ?>" >
        <label><?= L_SMS_TEXT ?></label>
        <textarea name="smstext" id="smstext" class="form-control"  ><?= $isEdit ? $editData['smstext']: '' ?></textarea>
        


		
		<?php if($canVerify): ?> 
			<br><input type="hidden" name="verified" id="verified" value="0">
			<input type="checkbox" name="verified" id="verified" value="1" <?= ($editData['verified'] == 1 ? 'checked="checked"' : '' ) ?>> 
			<label><?=  L_VERIFIED ?> </label>
			<br><br>
		
		<?php endif; ?>

		<?php if($isEdit && ($_SESSION['id'] != $editData['id'])): ?>
			<input name="del" id="del" type="checkbox"  value="1" > <label><?= L_DELETE ?></label>
			<br>
		<?php endif; ?>
		
		<br>
        <button name="Submit" id="submit" class="btn btn-lg btn-primary btn-block" type="submit"><?= $isEdit ? L_SAVE :  L_ADD ?></button>

      </form>

   <input id="require-text" type="hidden" value="<?= L_REQUIRE ?>">

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <!-- sss -->
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script type="text/javascript" src="/js/bootstrap.js"></script>

	<!-- ez dolgozza fel a cuccokat és küldi el postban!!!!!!!! -->
    <!--<script src="/js/user.js"></script>-->


    <script src="/js/jquery.validate.min.js"></script>
	<script src="/js/additional-methods.min.js"></script>
<script>



var validateObj = {
  rules: {
  
	 newuser: {
      required: true,
      minlength: 3,
      pattern: /^([0-9a-z]+)$/,
	  maxlength: 32
     
    },	
	 fullname: {
      required: true,
	  maxlength: 64
    },	
	 role: {
      required: true,
	  maxlength: 11
    },
	 group: {
      required: true,
	  maxlength: 11
    },
	
	emails: {
		maxlength: 512,
		pattern: /^([a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}|(([a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}\,)+([a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4})))$/
		
	},
	emailtext: {
		maxlength: 1024
	},
	smstelnumbers: {
		maxlength: 256,
		pattern: /^(\+[0-9]{10,15}|((\+[0-9]{10,15}\,)+(\+[0-9]{10,15})))$/
	},
	smstext: {
		maxlength: 160
	},
  
	password1: {
      minlength: 6,
	  maxlength: 127,
	  pattern: /^(?=.*[a-zA-Z])(?=.*\d).+$/
	},
    password2: {
      equalTo: "#password1",
	  maxlength: 127
    }
	
  },  
  
	messages: {
		newuser: {
		  maxlength: "<?= L_MAX_LENGTH ?> 32 <?= L_MAX_CHAR ?>",
		  pattern: "a-z0-9",
		  minlength: "<?= L_MIN_LENGTH ?> 3 <?= L_MAX_CHAR ?>"
		},
		fullname: {
		  maxlength: "<?= L_MAX_LENGTH ?> 64 <?= L_MAX_CHAR ?>"
		},
		role: {
		  maxlength: "<?= L_MAX_LENGTH ?> 11 <?= L_MAX_CHAR ?>"
		},
		group: {
		  maxlength: "<?= L_MAX_LENGTH ?> 11 <?= L_MAX_CHAR ?>"
		},
		
		emails: {
		  pattern: "aaaaa@aaa.hu,bbbbb@bbb.com,cccccc@ccccc.hu",
		  maxlength: "<?= L_MAX_LENGTH ?> 512 <?= L_MAX_CHAR ?>"
		},
		emailtext: {
		  maxlength: "<?= L_MAX_LENGTH ?> 1024 <?= L_MAX_CHAR ?>"
		},
		smstelnumbers: {
		  pattern: "+36123456789,+33445455444,+334157556564",
		  maxlength: "<?= L_MAX_LENGTH ?> 256 <?= L_MAX_CHAR ?>"
		},
		smstext: {
		  maxlength: "<?= L_MAX_LENGTH ?> 160 <?= L_MAX_CHAR ?>"
		},
		password1: {
          pattern: "<?= L_AT_LEAST_ONE_DIGIT ?>",
		  maxlength: "<?= L_MAX_LENGTH ?> 127 <?= L_MAX_CHAR ?>",
	      minlength: "<?= L_MIN_LENGTH ?> 6 <?= L_MAX_CHAR ?>"
		},
		password2: {
		  maxlength: "<?= L_MAX_LENGTH ?> 127 <?= L_MAX_CHAR ?>", 
		  equalTo: "<?= PASSWORD_NOT_MATCHED ?>"
		}
		
	  }
};

validForm = $("#usersignup").validate(validateObj);


$("#del").prop('checked',false);

$("#del").on('click', function() {
	if(this.checked) {
		$("#submit").removeAttr("class");
		$("#submit").addClass("btn btn-lg btn-danger btn-block");
		$("#submit").text("<?=  L_DELETE  ?>");	
		validForm.destroy();
	} else {
		$("#submit").removeAttr("class");
		$("#submit").addClass("btn btn-lg btn-primary btn-block");
		$("#submit").text("<?= $isEdit ? L_SAVE :  L_ADD ?>");	
		$("#usersignup").validate(validateObj);
	}
});

$("#resetpassw").prop('checked',false);
$("#resetpassw").on('click', function() {
	if(this.checked) {
		$("#reset-cont").show();
	} else {
			$("#reset-cont").hide();
	}
});



</script>




