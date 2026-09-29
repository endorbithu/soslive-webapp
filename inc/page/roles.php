<h2 class="form-signup-heading"><span class="glyphicon glyphicon-lock"></span> <?= L_PERMISSIONS ?></h2>
		
		<a href="/role"  class="btn btn-xs btn-primary"><span class="glyphicon glyphicon-plus"></span> <?= L_ADD_ROLE ?></a><br><br>

		<form id="roles_modify" action="/roles?save=1" method="POST">
		<input type="hidden" name="csrf-token" value="<?= $csrf ?>">
		<table class="datatable table" id="users">
		<thead>
			<tr>
				<th><?= L_MOVE ?></th>
				<th><?= L_NAME ?></th><th><?= L_SYSTEM_MODIFY_PERM ?></th>
				<th><?= L_USER_MODIFY_PERM ?></th>
				<th><?= L_POST_MODIFY_PERM ?></th>
				<th><?= L_ALL_GROUP_PERM ?></th>
				<th><?= L_LEAST_ROLE_CAN_SEE ?></th>
			</tr>
		</thead>
		<tbody class="sortable">
			<?php  foreach($roles as $row):	?>
					<tr>
						<td style="text-align:center">
							<span class="glyphicon glyphicon-move move-row"></span>
							<input type="hidden" class="role-weight" name="role[<?= $row['id']; ?>][weight]" value="<?= $row['weight'] ?>">
						</td>
						<td>
						<input name="role[<?= $row['id']; ?>][id]" type="hidden" value="<?= $row['id'] ?>">
						<span style="white-space: nowrap;"><a href="/users?role=<?= $row['id'] ?>"><?= $row['name']; ?></a> | <a href="/role?id=<?= $row['id']; ?>"><span class="glyphicon glyphicon-pencil"></span></a></span>
						</td>
						
						<td style="text-align:center">
							<input name="role[<?= $row['id']; ?>][can_system_modify]" type="hidden" value="0">
							<input type="checkbox" name="role[<?= $row['id']; ?>][can_system_modify]" value="1" <?= ($row['can_system_modify'] == 1 ? 'checked="checked"' : ''); ?>> 
						</td>
						<td style="text-align:center">
							<input name="role[<?= $row['id']; ?>][can_user_modify]" type="hidden" value="0">
							<input type="checkbox" name="role[<?= $row['id']; ?>][can_user_modify]" value="1" <?= ($row['can_user_modify'] == 1 ? 'checked="checked"' : ''); ?>> 						
						</td>						
						<td style="text-align:center">
							<input name="role[<?= $row['id']; ?>][can_video_modify]" type="hidden" value="0">
							<input type="checkbox" name="role[<?= $row['id']; ?>][can_video_modify]" value="1" <?= ($row['can_video_modify'] == 1 ? 'checked="checked"' : ''); ?>> 						
						</td>
						<td style="text-align:center">
							<input name="role[<?= $row['id']; ?>][has_all_group]" type="hidden" value="0">
							<input type="checkbox" name="role[<?= $row['id']; ?>][has_all_group]" value="1" <?= ($row['has_all_group'] == 1 ? 'checked="checked"' : ''); ?>> 						
						</td>
						<td>
						
						<select class="form-control" name="role[<?= $row['id']; ?>][default_video_permission]">
							
						<?php 
							foreach($allRoles as $aRole) { 
								print '<option ' . (($aRole['id'] == $row['r2_id']) ? 'selected="selected"' : '' ) . ' value="' . $aRole['id'] . '">'. $aRole['name'] .'</option>';				
							}
						?>
						</select>
												
						</td>
					</tr>
					
						
			<?php endforeach; ?>
       </tbody>
		</table>
		
		<?= L_LAST_MODIFY_WITH_ROLES ?>: <?= $row['mod_timestamp'].' - <a href="/user?id='. $row['members_id'] .'"> ' .  $row['username'] . ' </a>' ?><br><br>
		<button name="Submit" id="submit" class="btn btn-lg btn-primary" type="submit"><?= L_SAVE ?></button>

		</form>
		
		<script>
	
			//$(".sortable").sortable()
			$('.sortable').sortable({
				stop: function () {
					var inputs = $('input.role-weight');
					var nbElems = inputs.length;
					$('input.role-weight').each(function(idx) {
						$(this).val(nbElems - idx);
					});
				}
			}).disableSelection();
	
	
		$('.datatable').DataTable({
             "language": {
                "url": "/js/datatable_<?= $_SESSION['lang'] ?>.json",
            },
            "pageLength": 65536,
            "ordering": false,
			 "paging": false,
			 "searching":false,
			  "bInfo" : false
            
         });
		
		</script>
	

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <!-- sss -->
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script type="text/javascript" src="/js/bootstrap.js"></script>