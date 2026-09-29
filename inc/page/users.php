
        <h2 class="form-signup-heading"><span class="glyphicon glyphicon-user"></span> <?= L_USERS ?></h2>
		<a href="/user" class="btn btn-xs btn-primary"><span class="glyphicon glyphicon-plus little-user"></span> <?= L_ADD_USER ?></a><br><br>
		<table class="datatable table  order-column" id="users">
		<thead>
		    <tr>
		<th><?= L_USERNAME ?></th>
		<th><?= L_NAME ?></th>
		<th><?= L_ROLE ?></th>
		<th><?= L_GROUP ?></th>
		<th><?= L_VERIFIED ?></th>
		<th><?= L_LAST_MODIFY ?></th>
		</tr>
		<tr>
		    <th><input type="text" class="form-control search-column" value="<?= (isset($get['username']) ? $get['username'] : "") ?>" ></th>
		    <th><input type="text" class="form-control search-column" value="<?= (isset($get['fullname']) ? $get['fullname'] : "") ?>" ></th>
		    <th>
              <select class="search-column form-control">
		        <option value="">...</option>    
				   <?php
					   foreach( $roles as $role) {
						   echo '<option value="'. $role['name'] . '" ' . (isset($get['role']) &&  $get['role'] == $role['id'] ? ' selected="selected" ': '') . '>' . $role['name'] . '</option>';
						}
		           ?>
		    </select> 
		    </th>
		    <th>
		 
		    
		     <select class="search-column form-control">
		        <option value="">...</option>    
		        <?php
		           foreach($groups as  $group) {
		               echo '<option value="'. $group['name'] . '" ' . (isset($get['group']) &&  $get['group'] == $group['id'] ? ' selected="selected" ': '') . '>' . $group['name'] . '</option>';
		            }
		           ?>
		    </select> 
		    </th>
		    <th>
		        <select class="search-column form-control">
		        <option value="">...</option>  
		        <option value="<?= YES ?>"><?= YES ?></option>  
		        <option value="<?= NO ?>"><?= NO ?></option>  
		        
		    </select> 
		    </th>
		    <th></th>
		</tr>
		</thead>
		<tbody>
		
			<?php	

			foreach($users as $row):	?>
					<tr>
						<td><a href="/user?id=<?= $row['members_id']; ?>"><?= $row['username']; ?></a></td>
						<td><?= $row['fullname']; ?></td>
						<td><?= $row['roles_name']; ?></td>
						<td><?= $row['groups_name']; ?></td>
						<td><?= empty($row['verified']) ? NO : YES; ?></td>
						<td><?= $row['mod_timestamp'] ?> - <a href="/user?id=<?= $row['m2_id']; ?>"><?= $row['m2_username'] ?></a></td>
					</tr>
					
						
			<?php endforeach; ?>
       </tbody>
		</table>
		<script>
		
		   $(function(){

	
			 var table = $('.datatable').DataTable({
				"processing": true,
				 "language": {
					"url": "/js/datatable_<?= $_SESSION['lang'] ?>.json",
				},
				"pageLength": 25,
				"orderCellsTop": true,
				"order": [[ 0, "asc" ]]

			 });
				 
			 // Apply the filter
			$(".search-column").on( 'change keyup', function (event) {    	
				table
					.column( $(this).parent().index()+':visible' )
					.search( this.value )
					.draw();
			} );
         
         
  
		   $(".search-column").each(function() {
				table
					.column( $(this).parent().index()+':visible' )
					.search( this.value )
					.draw();
		   });     

			
          
        });
         
         
         
         
         
		</script>
		
    