   <h2 class="form-signup-heading"><span class="glyphicon glyphicon-list-alt"></span> <?= L_POSTS ?></h2>
		<table class="datatable table  order-column" id="users">
		<thead>
		   <tr>
        	    <th><?= CREATED ?></th>
    	        <th><?= ID ?></th>
        	     <th><?= L_GROUP ?></th>
        		<th><?= SUBMITTER ?></th>
    			<th><?= FIRST_COMMENT ?></th>
        		<th><?= L_LAST_MODIFY ?></th>
		    </tr>
		    <tr>
        	    <th>
					 <select data-column="0" class="search-column form-control" >
        	            <option value="">...</option>
        	             <?php
        		           foreach($months as $key => $month) {
        		               echo '<option value="'. $key . '">' . $month . '</option>';
        		            }
		                ?>
        	         </select>
				</th>
    	        <th><input type="text" data-column="1" class="search-column form-control" value=""></th>
        	     <th>
        	         <select data-column="2" class="search-column form-control" >
        	            <option value="">...</option>
        	             <?php
        		           foreach($groups as  $group) {
        		               echo '<option value="'. $group['id'] . '" ' . (isset($get['group']) &&  $get['group'] == $group['id'] ? ' selected="selected" ': '') . '>' . $group['name'] . '</option>';
        		            }
		                ?>
        	         </select>
        	     </th>
        		<th><input type="text" data-column="3" class="search-column form-control" value="<?= (isset($get['user']) ? $get['user'] : "") ?>"></th>
    			<th><input type="text" data-column="4" class="search-column form-control" value=""></th>
    			<th></th>
		    </tr>
		</thead>
		<tbody>
		</tbody>
		</table>
		

		<script>
		
		 var dataTable = $('.datatable').DataTable({
             "language": {
                "url": "/js/datatable_<?= $_SESSION['lang'] ?>.json",
            },
            "pageLength": 25,
            "orderCellsTop": true,
            "order": [[ 0, "desc" ]],
            "stateSave": true,
			 "serverSide": true,
			"ajax": {
				"url": "/ajax.php?act=incidents_datatable",
				"type": "GET"
			},
			"columnDefs": [
				{ "data": "0" },
				{ "data": "1" },
				{ "data": "2" },
				{ "data": "3" },
				{ "data": "4", "orderable": false },
				{ "data": "5" }
			]

         });
   
               
         
        // Apply the filter
        $(".search-column").on( 'change keyup', function (event) {    	
            dataTable
                .column( $(this).parent().index()+':visible' )
                .search( this.value )
                .draw();
        } );
         
        $(function(){

           $(".search-column").each(function() {
                dataTable
                    .column( $(this).parent().index()+':visible' )
                    .search( this.value )
                    .draw();
           });
          
        });
  
         
         
		</script>
		