<?php if(!empty($result)): ?>
		<div class="alert alert-success">
		<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
		<?= $result ?></div>
		<div id="returnVal" style="display:none;">true</div>
<?php endif; ?>


	<form class="common-form index-menu" id="rolesadmin" name="rolesadmin" method="post">
	<input type="hidden" name="csrf-token" value="<?= $_SESSION['csrf-token'] ?>">
	<a href="/roles"><span class="glyphicon glyphicon-arrow-left"> </span> <?= L_BACK_TO_PERMISSIONS ?></a>
		 <h2 class="form-signup-heading"><?= $isEdit ? $editData['name'] :  L_ADD_ROLE ?></h2>
		<?= $isEdit ? '<input type="hidden" name="rid" id="rid" value="' . $editData['id'] . '">' : ''; ?>
		
        <input name="name" id="name" type="text" class="form-control" value="<?= $isEdit ? $editData['name']: '' ?>" placeholder="<?= L_NAME ?> *" autofocus>
        
		<br>
		<?php if($isEdit): ?>
			<input name="del" id="del" type="checkbox"  value="1"> <label> <?= L_DELETE ?></label>
		<br><br>
		<?php endif; ?>
		
        <button name="Submit" id="submit" class="btn btn-lg btn-primary btn-block" type="submit"><?= $isEdit ? L_SAVE :  L_ADD ?></button>

      </form>
<input id="require-text" type="hidden" value="<?= L_REQUIRE ?>">

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script type="text/javascript" src="/js/bootstrap.js"></script>


    <script src="/js/jquery.validate.min.js"></script>
	<script src="/js/additional-methods.min.js"></script>
<script>
var validateObj = {
  rules: {
	 name: {
		maxlength: 32,
		required: true
    }
  },  
  messages: {
		name: {
			maxlength: "<?= L_MAX_LENGTH ?> 32 <?= L_MAX_CHAR ?>"
		}
  }

};


var validForm = $("#rolesadmin").validate(validateObj);

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
		$("#rolesadmin").validate(validateObj);
	}
});


</script>



