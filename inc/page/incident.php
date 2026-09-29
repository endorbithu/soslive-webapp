  <div class="row">
        <div class="col-sm-6" id="videoino">
            
			<h2 id="e-id">#<?= $resultArray['id'] ?></h2>
            
            <script>document.title = $("#e-id").text() + " - " + document.title;</script>
            
			<div style="display: none" id="has-new-video">
				<a style="color: #EF4040;" class="bold" id="new-url" href=""><?= NEW_UPPER ?> 
					<img src="/style/live_fast.gif" alt="newlive"> <?= BEGIN_UPPER ?>
				</a>
			</div>
			
    	       <ul class="list-group">
					<li class="list-group-item">
						<?php if($resultArray['deleted'] == '1'): ?> 
							<span class="glyphicon glyphicon-remove"> </span>
						<?php else: ?>
							<img alt="blinking-icon" src="/style/blinking_dot_<?= ((($resultArray['status'] == 'RUNNING') ? '1' 
							: ( ($resultArray['status'] == 'STOPPED' && $resultArray['event_type'] == 'SOSLIVE') ? '2' 
							: ($resultArray['status'] == 'STOPPED' && $resultArray['event_type'] == 'LIVE' ? '3' : '4')))) ?>.gif">
						<?php endif; ?>
							<?= constant($resultArray['event_type']) ?> 
					</li>
					<li class="list-group-item"><span class="glyphicon glyphicon-user"> </span><span class="bold"> <?= L_NAME ?>:</span>
						<a href="/incidents?user=<?= $resultArray['members_id'] ?>"><?= $resultArray['fullname'] ?>   </a>
					</li>
					<li class="list-group-item"><span class="glyphicon glyphicon-user little-user"> </span><span class="glyphicon glyphicon-user"> </span>
						<span class="bold"> <?= L_GROUP ?>:</span> 
						<a href="/incidents?group=<?= $resultArray['gid'] ?>"><?= $resultArray['gname'] ?></a>
					</li>
					<li class="list-group-item"><span class="glyphicon glyphicon-comment"> </span><span class="bold"> <?= FIRST_COMMENT ?>:</span>
						<?= $resultArray['message'] ?>  
					</li>


					<li class="list-group-item"><span class="glyphicon glyphicon-time"> </span><span class="bold"> <?= CREATED ?>:</span> 
						<?= substr($resultArray['datetime'], 0,16) ?>
					</li>
                     
	                <?php if($resultArray['event_type'] != 'PHOTO'): ?>
						<li class="list-group-item"><span class="glyphicon glyphicon-stop"> </span><span class="bold"> <?= STOPPED_AT ?>:</span>
       	                    <?= substr($resultArray['stopped_datetime'], 0,16) ?>  	
						</li>
                     <?php endif; ?>     
                      
                      <?php if($resultArray['delete_timestamp'] < 2147483647): ?>
       	              <li class="list-group-item"><span class="glyphicon glyphicon-trash"> </span><span class="bold"> <?= DELETE_AT ?>:</span>
       	                    <?= $resultArray['deleted'] == '1' ? '<span id="mod-datetime">' . substr($resultArray['mod_timestamp'], 0,16) 
							. '</span> - <a id="mod-userid" href="/user?id='.$resultArray['m2_id'].'"><span  id="mod-username">' 
							.  $resultArray['m2name'] . '</span></a>' 							
       	                    : date('Y-m-d H:i' , $resultArray['delete_timestamp']) ?>  
                      </li>
                      <?php endif ?>
                      
                      
                      <?php if(isset($_SESSION['can_video_modify']) && $_SESSION['can_video_modify'] == '1' && $resultArray['deleted'] != '1'): ?>
                       <li class="list-group-item">
                           
                           <span class="glyphicon glyphicon-lock"> </span><span class="bold"> <?= L_LEAST_ROLE_CAN_SEE_POST ?>:</span>
       	                   
       	                  
       	                   <div class="form-inline">
       	                   
       	                   <select id="perm-val" class="search-column form-control">
        		               <?php
                		          	foreach($allWeakerRoles as $key => $row){ 
                    					print '<option value="'.$row['id'].'" ' . ($row['id'] == $resultArray['least_role_id'] ? 'selected': '') . '>' . $row['name'].  '</option>';
                    				}
            		           ?>
                		    </select> <input type="submit" id="save-perm" class="btn btn-primary" style="display: none" value="<?= L_SAVE ?>"> 
                		    </div>
                		    

                		    <div><span class="bold"><?= L_LAST_MODIFY ?>:</span>
       	                        <span id="mod-datetime"><?= substr($resultArray['pmod_timestamp'], 0,16) . '</span> - <a id="mod-userid" href="/user?id='.$resultArray['m2_id'].'"><span  id="mod-username">' .  $resultArray['m2name'] . '</span></a>' ?>
       	                    </div>
            		    </li>
                    		     <li class="list-group-item">
                    		         <form method="post">
									 <input type="hidden" name="csrf-token" value="<?= isset($_SESSION['csrf-token']) ? $_SESSION['csrf-token'] : '' ?>">
                        		         <div class="form-inline">
                            		            <span class="glyphicon glyphicon-trash"> </span><span class="bold"> <?= L_DELETE ?>:</span>
                       	                        <input type="checkbox" id="delete-toggle" name="delete" value="1">
                       	                        <span id="delete-btn" style="display:none"><?= L_ARE_U_SURE_DEL ?> <input type="submit"  value="<?= L_DELETE ?>" class="btn btn-xs btn-danger" ></span>
                   	                    </div>
               	                    </form>
                                </li>

                      <?php endif;  ?>
                       
                      
                      
                 </ul>
	    </div>
	    
	    <?php if($resultArray['deleted'] == '1') exit;  ?>
        
	    <div class="col-sm-6" id="iframcolumn" style="text-align: center" >
	
	<?php if($resultArray['event_type'] != 'PHOTO'): ?> 
	
		<?php if($resultArray['status'] == 'RUNNING') { ?> 
	
			<a class="btn btn-xs btn-primary" href="<?=  $resultArray['stream_file_server'] ?>/<?= $videoFileName  ?>.flv" download="<?= substr($resultArray['datetime'], 0,10) . '_' . $resultArray['username'] . '_' . $resultArray['id'] ?>">
				<span class="glyphicon glyphicon-download-alt"> </span> <?= DOWNLOAD ?> (.flv)
			</a>
			<br>
			<object type="application/x-shockwave-flash" id="VideoPlayer" data="/js/JarisFLVPlayer.swf" width="100%" height="500px"><param name="menu" value="false"><param name="scale" value="noScale"><param name="allowFullscreen" value="true"><param name="allowScriptAccess" value="always"><param name="bgcolor" value="#000000"><param name="quality" value="high"><param name="wmode" value="opaque"><param name="flashvars" value="source=<?= $videoFileName ?>&amp;type=video&amp;streamtype=rtmp&amp;controltype=1&amp;duration=0&amp;poster=&amp;aspectratio=&amp;autostart=true&amp;logo=&amp;logoposition=top left&amp;logoalpha=30&amp;logowidth=130&amp;logolink=&amp;hardwarescaling=false&amp;controls=true&amp;darkcolor=000000&amp;brightcolor=4c4c4c&amp;controlcolor=FFFFFF&amp;hovercolor=67A8C1&amp;seekcolor=D3D3D3&amp;jsapi=true&amp;server=<?= $resultArray['stream_server'] ?>/rcilive/"></object>
			<?= '<a target="_blank" href="' . $resultArray['stream_server'] . '/rcilive/'.$videoFileName. '" >' . $resultArray['stream_server'] . '/rcilive/<br>'.$videoFileName.'</a>' ?>
			<?php } else { ?> 
				<a class="btn btn-xs btn-primary" href="<?= $mp4FileName ?>" download="<?= substr($resultArray['datetime'], 0,10) . '_' . $resultArray['username'] . '_' . $resultArray['id'] ?>">
				<span class="glyphicon glyphicon-download-alt"> </span>	<?= DOWNLOAD ?> (.mp4)
				</a>
			<br>
				<video width="100%" height="500" controls>
					<source src="<?= $mp4FileName ?>" type="video/mp4">
					Your browser does not support the video tag.
				</video>
		
		 
		<?php } ?>
		
	 <?php endif; ?>

                    
        </div> 
		
		</div>

        

   

<input type="hidden" id="vstatus" value="<?= $resultArray['status'] ?>">
<script>

    
    $("#delete-toggle").on('click', function(){
        if($("#delete-toggle").is(':checked')) {
            $("#delete-btn").show();
        } else {
            $("#delete-btn").hide();
        }
    });
    
    $("#save-perm").on('click', function(){
        $.get( "/ajax.php?act=video_permission&id=<?= $resultArray['id'] ?>&perm=" + $("#perm-val").val() , function( data ) {
            
            if(data != '') {
                 $("#save-perm").hide();
                 
                 var mod = $.parseJSON(data);
                $("#mod-username").text(mod.username);
                $("#mod-datetime").text(mod.mod_datetime);
                $("#mod-userid").attr('href', '/user?id=' + mod.userid);
                 
                 
            }
            
        });

    });
    
    
     $("#perm-val").on('change', function(){
        $("#save-perm").show();
     });


    $(function() {
        checkNewVideo();
        
        setInterval(function(){  
            checkNewVideo();
        }, 10000);
    } );
    
    
    
    
    
    var needRefresh = false;
    
    function checkNewVideo() {
        $.get( "/ajax.php?act=video_status&id=<?= $resultArray['id'] ?>&time=<?= $date->format('Y-m-d H:i:s') ?>&user_id=<?= $resultArray['members_id'] ?>", function( data ) {
            
            var dataJson =  $.parseJSON(data);
            
            if(needRefresh) { 
                window.location.reload();
            }
            
             if($("#vstatus").prop("value") != "" && $("#vstatus").prop("value") != dataJson.status) {
                 needRefresh = true;
             }
             
             if(dataJson.hasNew != "") {
                 $("#has-new-video").show();
                 $("#new-url").attr('href',dataJson.hasNew );
             }
             
            });
    }
    
    
    
</script>



<iframe src="/incidentiframe?id=<?= $resultArray['id'] ?>" 
    id="screen-and-loc"  width="100%" 
    style="border:none;overflow:hidden; margin-top:0px; min-height:350px" 
    scrolling="no" 
    frameborder="0" >
</iframe>
<!-- onload="resizeIframe(this)" -->


<script>


if(($("body").width() < 750) && ($("#has-loc").attr("value") == "1")) {
    $("#screen-and-loc").css("min-height", "1200px");
} else {
     $("#screen-and-loc").css("min-height", "420px");
} 



$("#screen-and-loc").on("load", function() {
    $(this).height( $(this).contents().find("body").height() );
});


$( window ).resize(function() {
    if(($("body").width() < 750) && ($("#has-loc").attr("value") == "1")) {
        $("#screen-and-loc").css("min-height", "1161px");
    } else {
         $("#screen-and-loc").css("min-height", "420px");
    } 
    
    $("#screen-and-loc").height( $("#screen-and-loc").contents().find("body").height() );
});


</script>
	    
