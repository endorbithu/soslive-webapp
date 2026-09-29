<?php if(!empty($result)): ?>
		<div class="alert alert-success">
		<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
		<?= $result ?></div>
		<div id="returnVal" style="display:none;">true</div>
		<a href="/groups"><span class="glyphicon glyphicon-arrow-left"> </span> <?= L_BACK_TO_GROUPS ?></a>
<?php endif; ?>

	<form class="common-form index-menu" id="groupadmin" name="groupadmin" method="post">
	<input type="hidden" name="csrf-token" value="<?= $_SESSION['csrf-token'] ?>">
	<a href="/groups"><span class="glyphicon glyphicon-arrow-left"></span> <?= L_BACK_TO_GROUPS ?></a>
		 <h2 class="form-signup-heading"><?= $isEdit ? $editData['name'] :  L_ADD_GROUP ?></h2>
		 
		<?= $isEdit ? '<input type="hidden" name="gid" id="gid" value="' . $editData['id'] . '">' : ''; ?>
		
        <input name="name" id="name" type="text" class="form-control" value="<?= $isEdit ? $editData['name']: '' ?>" placeholder="<?= L_NAME ?> *" autofocus>
        <input name="emails" id="emails" type="text" class="form-control" value="<?= $isEdit ? $editData['alert_emails']: '' ?>" placeholder="<?= L_NOTIFIED_EMAILS ?>" >
       
         <label><?= L_EMAIL_TEXT ?></label>
         <textarea name="emailtext" id="emailtext" class="form-control"  ><?= $isEdit ? $editData['emailtext']: '' ?></textarea>
        <input name="smstelnumbers" id="smstelnumbers" type="text" class="form-control" value="<?= $isEdit ? $editData['smstelnumbers']: '' ?>" placeholder="<?= L_NOTIFIED_TELNUM ?>" >
        <label><?= L_SMS_TEXT ?></label>
        <textarea name="smstext" id="smstext" class="form-control"  ><?= $isEdit ? $editData['smstext']: '' ?></textarea>
        
		<br>
		<?php if($isEdit): ?>
			<input name="del" id="del" type="checkbox"  value="1"> <label> <?= L_DELETE ?></label>
		<br><br>
		<?php endif; ?>
		
        <button name="Submit" id="submit" class="btn btn-lg btn-primary btn-block" type="submit"><?= $isEdit ? L_SAVE :  L_ADD ?></button>

      </form>


    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script type="text/javascript" src="/js/bootstrap.js"></script>

	<input id="require-text" type="hidden" value="<?= L_REQUIRE ?>">

    <script src="/js/jquery.validate.min.js"></script>
	<script src="/js/additional-methods.min.js"></script>
<script>


var validateObj = {
  rules: {
	 name: {
	  maxlength: 32,
      required: true
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
	}

  },
  messages: {
		name: {
			maxlength: "<?= L_MAX_LENGTH ?> 32 <?= L_MAX_CHAR ?>"
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
		}
  }
};

validForm = $("#groupadmin").validate(validateObj);

$("#del").prop('checked',false);
$("#del").on('click', function() {
	if(this.checked) {
		$("#submit").removeAttr("class");
		$("#submit").addClass("btn btn-lg btn-danger btn-block");
		$("#submit").text("<?=  L_DELETE  ?>");	
		$("#groupadmin").rules();
		validForm.destroy();
	} else {
		$("#submit").removeAttr("class");
		$("#submit").addClass("btn btn-lg btn-primary btn-block");
		$("#submit").text("<?= $isEdit ? L_SAVE :  L_ADD ?>");	
		$("#groupadmin").validate(validateObj);
	}
});



</script>

