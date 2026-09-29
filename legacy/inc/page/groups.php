 <h2 class="form-signup-heading"><span class="glyphicon glyphicon-user little-user"></span><span class="glyphicon glyphicon-user"></span> <?= L_GROUPS ?></h2>
		<a href="/group" class="btn btn-xs btn-primary"><span class="glyphicon glyphicon-plus"></span> <?= L_ADD_GROUP ?></a><br><br>
		<table class="datatable table  order-column" id="users">
		<thead>
			<tr>
				<th><?= L_NAME ?></th><th><?= L_HIGHTEST_PERSON ?></th>
			</tr>
		</thead>
		<tbody>
		
			<?php 
			
			foreach($groups as $row):	?>
					<tr>
						<td><a href="/users?group=<?= $row['id'] ?>"><?= $row['name']; ?></a> | <a href="/group?id=<?= $row['id']; ?>"><span class="glyphicon glyphicon-pencil"></span></a></td>
						<td>						
							<?= (isset($row['members_id']) ? '<a href="/user?id=' . $row['members_id'] . '">'. $row['fullname'] .' </a> (' . $row['roles_name'] .')' : '') ?>
						</td>
					</tr>
					
						
			<?php endforeach; ?>
       </tbody>
		</table>
	<script>
		$('.datatable').DataTable({
             "language": {
                "url": "/js/datatable_<?= $_SESSION['lang'] ?>.json",
            },
            "pageLength": 25,
            "order": [[ 0, "asc" ]]
            
         });
		</script>
