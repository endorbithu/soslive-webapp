	<div class="row">
		<?php if (isset($resultArray['status']) && $resultArray['status'] == 'RUNNING'): ?>
			<div class="col-xs-12"">
				<?= REFRESH ?>:  <span id="time-to-update" class="light-blue" style="font-weight: bold;"></span> 
				<?= STOP_IT ?>: <input type="checkbox" id="stop-timer">
			</div>
		<?php endif; ?>    	    
		<?php  if($resultArray['event_type'] === 'PHOTO'): ?>
			<div class="col-sm-12 col-xs-12 coli" id="thumbnails">
				<button type="button" class="btn btn-sm refresh-btn">
					<span class="glyphicon glyphicon-refresh"> </span>
				</button>
				<div class="loc-info" style="margin-bottom: 3px; margin-top: 4px"> 
					<span class='glyphicon glyphicon-picture'></span> <span class="bold"><?= PHOTO ?>: </span>
						<a class="btn btn-xs btn-primary" href="/ajax.php?act=zip&id=<?= $q ?>&username=<?= $resultArray['username'] ?>">
							<span class="glyphicon glyphicon-download-alt"> </span>  (.zip) 
						</a>
				</div>
				<div class="horizontal-scroll" style="overflow: auto;">
					<div style="display: inline-block;white-space:nowrap">
						<?= $photos  ?>
					</div>
				</div>
			</div>
		<?php endif; ?>
	</div>
	<div class="row">
		<div class="col-lg-12" id="refresh-row">
			<button type="button" class="btn btn-sm refresh-btn"><span class="glyphicon glyphicon-refresh"> </span></button>
		</div>
	</div>
	<div class="row"> 
		<div class="col-sm-6 col-xs-12 coli" id="comments">
			<div class="comment-info" style="margin-bottom: 3px; margin-top: 4px"> 
				<span class="bold"><span class='glyphicon glyphicon-comment'></span> <?= COMMENTS ?>:</span>
				<a class="btn btn-xs btn-primary" href="/ajax.php?act=downloadcomments&id=<?= $q ?>">
					<span class="glyphicon glyphicon-download-alt"> </span>  (.txt)
				</a>
			</div>           
            <?= $header ?>
            <?php if(isset($_SESSION['id']) && $_SESSION['id'] > 0): ?>
			<form method="post" action="/incidentiframe?id=<?= $q ?>">
				<div class="form-group">
					<textarea id="form_message" name="message" class="form-control" placeholder="" rows="2" required="required"></textarea>
					<input type="submit" class="btn btn-xs btn-primary btn-send" value="Küldés">   
					<input type="hidden" name="csrf-token" value="<?= $_SESSION['csrf-token'] ?>">
				 </div>
			</form>
			<?php endif; ?>
            <div id="comment-content" style="height: 376px; overflow-y: scroll">

				<?php foreach($comments as $comm): ?>
							
					<div>
					<span class="bold"><a target="_blank" href="/user?id=<?= $comm['members_id'] ?>"><?=  $comm['fullname'] ?></a></span> - <?= $comm['datetime'] ?><br><?=  $comm['message'] ?>
					</div>
								
				<?php endforeach; ?>
					   
    	    </div>
		</div>
    	    
    	    
       <div id="mapcolumn" class="col-md-6 col-sm-12 col-xs-12 coli">
       
                <div class="loc-info" style="margin-bottom: 3px; margin-top: 4px"><span class='glyphicon glyphicon-globe'></span> 
                <span class="bold">
                    <?= LOCATION_DATA_BY_PHONE ?>:
                </span>
                    <a class="btn btn-xs btn-primary" href="/ajax.php?act=locationtxt&id=<?= $q ?>">
                        <span class="glyphicon glyphicon-download-alt"> </span> (.txt)
                    </a>
                </div>
        	    <div id='map' style="width: 100%; height: 450px"></div>
	    </div>

		
		<?php if($resultArray['event_type'] != 'PHOTO'  ): ?>
       
			<div class="col-xs-12 col-md-6 coli" id="coordtext"> 
				<div class="loc-info" style="font-weight: bold; margin-bottom: 3px; margin-top: 4px">
				<span class='glyphicon glyphicon-globe'></span> <?= CLICK_TO_LOC ?>: </div>
				<div id="coords" >
					<div class="main-nav" >
						<ul id="navcoord" class="navcoord">

						
						</ul>
					</div>
				</div>
			</div> 
        
        <?php endif; ?>
	
        
	<script> 
	      
	  $( window ).resize(function() {
			$('.main-nav').height($('#coordtext').width());
			$('#coords').height($('#mapcolumn').height());
	  }); 
					
	<?= $locPathJs ?>
	
	 //visszaszámláló
        var timer = {
            interval: null,
            seconds: 15,
        
            start: function () {
            
                var self = this,
                el = document.getElementById('time-to-update');
        
                if(el == null) return;
        
                el.innerText = this.seconds;
        
                this.interval = setInterval(function () {
                    self.seconds--;
                    
                    if (self.seconds == 0) 
                        window.location.reload();
        
                    el.innerText = self.seconds;
                }, 1000);
            },
        
            stop: function () {
                window.clearInterval(this.interval)
            },
            
            restart: function () {
                this.stop();
                this.seconds = 15;
                this.start();
            }
        }
        
        timer.start();
        
        $("#stop-timer").on('change', function() {
            if(this.checked) {
                timer.stop();
            } else {
                 timer.restart();
            }
        });
        
        
        $(".refresh-btn").on('click', function() {
           window.location.reload();
        });
        
        
        $("#form_message").on('focus', function() {
            timer.stop();
        });
        
        $("#form_message").on('blur', function() {
            timer.restart();
        });
        
        
    
	  function initMap() {
          

            if(userCoor.length == 0) {
                $("#mapcolumn").html('<div class="loc-info" style="font-weight: bold; margin-bottom: 3px; margin-top: 4px"><span class="glyphicon glyphicon-globe"></span> <?= NO_LOCATION_DATA ?></div>');
                $("#coordtext").hide();
                
                return;
            }
                
             var map = new google.maps.Map(document.getElementById('map'), {
              zoom: 14,
              streetViewControl: false,
              scrollwheel: false,
              scaleControl: true,
            });
            
            
            var flightPath = new google.maps.Polyline({
              path: flightPlanCoordinates,
              geodesic: true,
              strokeColor: '#FF0000',
              strokeOpacity: 1.0,
              strokeWeight: 2
            });
    
            flightPath.setMap(map);
            
            
            var bounds = new google.maps.LatLngBounds();
            var infowindow = new google.maps.InfoWindow();
            
            var marker, i;
             var markers = [];
             
            var le = userCoor.length;
            var last = le -1 ;
            for (i = 0; i < userCoor.length; i++) {  
                marker = new google.maps.Marker({
                position: new google.maps.LatLng(userCoor[i][1], userCoor[i][2]),
                map: map,
                icon: ((i != last && (i != 0)) ? '/style/1px.png' : ((i == 0) ? '/style/go.png' : '/style/stop.png' ))
              });
            
              bounds.extend(marker.getPosition());
            
                markers.push(marker);
            
              google.maps.event.addListener(marker, 'click', (function(marker, i) {
                return function() {
                  infowindow.setContent('<?= AT_TIME ?> ' + userCoor[i][0]);
                  infowindow.open(map, marker);
                }
              })(marker, i));
            
            }
            
           
            
            infowindow.setContent('<?= LAST_LOCATION_AND_TIME ?> ' + userCoor[last][0] + '');
            infowindow.open(map,marker);
            
            //map.fitBounds(bounds);
            //center: {lat: userCoor[(userCoor.length - 1)][1], lng: userCoor[(userCoor.length - 1)][2]},
             
               
           $('.l-location').on('click', function() {
                google.maps.event.trigger(markers[$(this).find('.a-location').data('nr')], 'click');
                document.querySelector('#mapcolumn').scrollIntoView();
                timer.restart();
            });
            


            $('.a-location').on('click', function(event) {
                timer.restart();
               event.preventDefault();
              // event.stopPropagation();
            });
            
             google.maps.event.addListener(map, 'idle', function() {
                timer.restart();
             });
             
             map.setCenter({lat: userCoor[(userCoor.length - 1)][1], lng: userCoor[(userCoor.length - 1)][2]});

          }
          
          
    

        
        
    </script>
	<script src='https://maps.googleapis.com/maps/api/js?key=AIzaSyCN_wvLZh60kpbykFXqiKHNlrm4gl5M6ew&callback=initMap' async defer></script>

 

   