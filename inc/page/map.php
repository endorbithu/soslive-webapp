<div class="row">
           <br>
          
           <div id="map-desc" class="col-xs-12 col-lg-12" style="margin-bottom:10px"><?= MAP_DESC ?></div>
           
                <input type="hidden" id="new-filter-uri" value ="<?= $newUrl ?>">
                
    	        <div id="filter-event" class="col-xs-12 col-lg-12 form-inline" style="margin-bottom: 5px">
    	         
    	         <?php if(!($_SESSION['only_sos'] === '1')): ?>
    	         
        	         <select data-column="2" id="event_type" class="filter-column form-control" style="display: inline">
            	            <option  value=""><?= EVENT_TYPE . ': ' . ALL ?></option>
            	            <option <?= (isset($get['event_type']) && $get['event_type'] == 'SOSLIVE' ? 'selected="selected"' : '')  ?> value="SOSLIVE"><?= SOSLIVE ?></option>
        	                <option <?= (isset($get['event_type']) && $get['event_type'] == 'LIVE' ? 'selected="selected"' : '')  ?> value="LIVE"><?= LIVE ?></option>
        	                <option <?= (isset($get['event_type']) && $get['event_type'] == 'PHOTO' ? 'selected="selected"' : '')  ?> value="PHOTO"><?= PHOTO ?></option>
            	            
        	         </select>
    	         <?php else: ?>
    	         
    	            <input type="hidden" id="event_type" value="SOSLIVE">
    	         
    	         <?php endif; ?>
    	         
        	      <select data-column="2" class="filter-column form-control" id="groupid"  style="display: block-inline">
        	            <option value=""><?= L_GROUP . ': ' . ALL ?></option>
        	             <?php
        		           foreach($groups as  $group) {
        		               echo '<option value="'. $group['id'] . '" ' . (isset($get['groupid']) && $get['groupid'] == $group['id'] ? 'selected="selected"' : '')   .'>' . $group['name'] . '</option>';
        		            }
		                ?>
    	         </select>
	             <select data-column="2" class="filter-column form-control" id="userid" style="display:inline">
    	             <option value=""><?= L_USER . ': ' . ALL ?></option>
    	             <?php
    		           foreach($allUser  as $user) {
    		               echo '<option value="'. $user['id'] . '" ' . (isset($get['userid']) && $get['userid'] == $user['id'] ? 'selected="selected"' : '')   .'>' . $user['fullname'] . '</option>';
    		            }
	                ?>
    	         </select>
        	</div>
       
       </div>
<input id="pac-input" class="controls" style="width:180px" type="text" placeholder="<?= SETTLEMENT ?>">
<div id="map" style="width: 100%; height: 450px"></div>
<br><br>
<div id="table-container" style="display: none">
	<table class="datatable table table-striped " id="video-table">
			<thead>
				<tr>
					<th><?= CREATED ?></th>
					<th><?= ID ?></th>
					<th><?= L_GROUP ?></th>
					<th><?= SUBMITTER ?></th>
					<th><?= FIRST_COMMENT ?></th>
				</tr>
			</thead>
			<tbody>
				<?= $tableRow ?>
			</tbody>
	</table>
</div>

<script>
    var userCoor = [];

	<?= ((isset($get['n'])) ? $userCoor : '') ?>        
    
	$('.main-nav').height($('#coordtext').width()); 
	
        var n = 0;
        var e = 0;
        var s = 0;
        var w = 0;
        var centerLat;
        var centerLng;
        var minZoomLevel = 5;
        var z = getQueryVariable("zoom") ? parseInt(getQueryVariable("zoom")) : 4;
        
        if(z < 5) {
            z = 5;
        }
    
          function initMap() {
             var map = new google.maps.Map(document.getElementById('map'), {
              streetViewControl: false,
              scrollwheel: false,
              scaleControl: true,
              zoom:  z
            });
            
           
           
            var infowindow = new google.maps.InfoWindow();
            
            var marker, i;
             var markers = [];
             
            for (i = 0; i < userCoor.length; i++) {  
                marker = new google.maps.Marker({
                position: new google.maps.LatLng(userCoor[i][1], userCoor[i][2]),
                map: map,
                optimized: false,
                minZoom: 5,
                icon: '/style/blinking_dot_' + userCoor[i][3] + '.gif'
              });
            
             
              //var bounds = new google.maps.LatLngBounds();
              //bounds.extend(marker.getPosition());
            
                markers.push(marker);
            
            if(userCoor[i][6] == "") {
                userCoor[i][6] = '/style/1px.png';
            }
            

              google.maps.event.addListener(marker, 'click', (function(marker, i) {
                return function() {
                  infowindow.setContent('<a target="_blank" href="' + userCoor[i][7] + '"><span class="bold">' + userCoor[i][0] + '</span> <img src="style/blinking_dot_' + userCoor[i][3] + '.gif"><br>' + userCoor[i][5] +'<br>'+ userCoor[i][6] +'<br>' + userCoor[i][4] + '</a>');
                  infowindow.open(map, marker);
                }
              })(marker, i));
              
              
            
            }
            
             google.maps.event.addListener(infowindow,'closeclick',function(){
                window.location.reload();
            });
            
  
             
              var bounds = new google.maps.LatLngBounds();
              var pne = "";
              var psw = "";
              
            if(getQueryVariable("centerlat") !== false) {
				
                 map.setCenter({ lat: parseFloat(getQueryVariable("centerlat")) , lng: parseFloat(getQueryVariable("centerlng"))});
                
            } else {


             // Try HTML5 geolocation.
                if (navigator.geolocation) {
                  
                  navigator.geolocation.getCurrentPosition(function(position) {
                    var pos = {
                      lat: position.coords.latitude,
                      lng: position.coords.longitude
                    };
                    

                    map.setCenter(pos);
                    map.setZoom(9);
                    
                  }, function() {
                      map.setCenter({ lat: 47.50207614871161 , lng: 19.052920532226608});
                      map.setZoom(12);

                      
                    //handleLocationError(true, infoWindow, map.getCenter());
                    
                  });
                } 
            }
            

            var nowLoadedPage = true;
            
           google.maps.event.addListener(map, 'drag', function() {
                nowLoadedPage = false;
            });
            
            google.maps.event.addListener(map, 'zoom_changed', function() {
                 nowLoadedPage = false;
            });
            
            google.maps.event.addListener(map, 'click', function() {
                nowLoadedPage = false;
            });
            
            google.maps.event.addListener(map, 'dblclick', function() {
                 nowLoadedPage = false;
            });
            
             // Limit the zoom level
             google.maps.event.addListener(map, 'zoom_changed', function () {
                 if (map.getZoom() < minZoomLevel) {
                     map.setZoom(minZoomLevel);
                     nowLoadedPage = true;
                     
                 }
             });
            
            
            //https://jsfiddle.net/upsidown/1svw299r/
            
            
                // Create the search box and link it to the UI element.
                var input = /** @type {HTMLInputElement} */
                (
                document.getElementById('pac-input'));
                map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);
            
                var searchBox = new google.maps.places.SearchBox(
                /** @type {HTMLInputElement} */
                (input));
                
                
                  // [START region_getplaces]
                // Listen for the event fired when the user selects an item from the
                // pick list. Retrieve the matching places for that item.
                google.maps.event.addListener(searchBox, 'places_changed', function () {
                    var places = searchBox.getPlaces();
            
                    if (places.length == 0) {
                        return;
                    }
                    for (var i = 0, marker; marker = markers[i]; i++) {
                        marker.setMap(null);
                    }
            
                    // For each place, get the icon, place name, and location.
                    markers = [];
                    var bounds = new google.maps.LatLngBounds();
                    for (var i = 0, place; place = places[i]; i++) {
                        var image = {
                            url: place.icon,
                            size: new google.maps.Size(71, 71),
                            origin: new google.maps.Point(0, 0),
                            anchor: new google.maps.Point(17, 34),
                            scaledSize: new google.maps.Size(25, 25)
                        };
            
                        // Create a marker for each place.
                        var marker = new google.maps.Marker({
                            map: map,
                            icon: image,
                            title: place.name,
                            position: place.geometry.location
                        });
            
                        markers.push(marker);
            
                        bounds.extend(place.geometry.location);
                    }
                   
                    map.fitBounds(bounds);
                    map.setZoom(12);
                });
                // [END region_getplaces]
                
                
                  // Bias the SearchBox results towards places that are within the bounds of the
                    // current map's viewport.
                    google.maps.event.addListener(map, 'bounds_changed', function () {
                        var bounds = map.getBounds();
                        searchBox.setBounds(bounds);
                    });
            
            
            
            google.maps.event.addListener(map, 'idle', function() {
                
                if(nowLoadedPage && getQueryVariable("n") !== false) { 
                    return;
                }
                
                var bounds = map.getBounds();
       
                window.centerLat = bounds.getCenter().lat();
                window.centerLng = bounds.getCenter().lng();
                window.n = bounds.getNorthEast().lat();
                window.e = bounds.getNorthEast().lng();
                window.s = bounds.getSouthWest().lat();
                window.w = bounds.getSouthWest().lng();
                
                //console.log("ÉK: " + n + ", " + e + "\nDNY:" + s + ", " + w + "\nCenter:" + centerLat +", " +  centerLng +"\nzoom:" + map.getZoom());
                
                var z = map.getZoom() < 5 ? '5' :  map.getZoom();
                
                if(getQueryVariable("n") !== n || getQueryVariable("e") !== e || getQueryVariable("s") !== s || getQueryVariable("w") !== w || map.getZoom() !== getQueryVariable("zoom")) {
                    window.location.replace("/map?n=" + n +"&e=" + e + "&s=" + s +"&w=" + w + "&centerlat=" + centerLat + "&centerlng=" + centerLng+ "&zoom=" + z +"&userid=" 
                    + (getQueryVariable("userid") === false ? "" : getQueryVariable("userid")) + "&groupid=" + (getQueryVariable("groupid") === false ? "" : getQueryVariable("groupid"))
                    + "&event_type=" + (getQueryVariable("event_type") === false ? "" : getQueryVariable("event_type")) );
                }
                
            });
            
            $('.avideo').on('click', function() {
                google.maps.event.trigger(markers[$(this).data('nr')], 'click');
            });   
              
          }
          
                 
            
        function isThereNewVideo() {
            //van e 10mp-nél nem régebbi új videó a térképszelvényben  
            $.get( "/ajax.php?act=check_new_video_on_map&n="+ getQueryVariable("n") +"&e="+ getQueryVariable("e") +"&s="+ getQueryVariable("s") +"&w="+ getQueryVariable("w")+"&event_type="+ getQueryVariable("event_type")+"&groupid="+ getQueryVariable("groupid")+"&userid="+ getQueryVariable("userid"), function( data ) {
           
                if(data == '1') {
                    
                    var au = $("#beep")[0];
                    au.play();
                    setTimeout(function(){
                         window.location.reload();
                    }, 2500);
                }
                
                
                });
        }
        

            setInterval(function(){  
                isThereNewVideo();
            }, 10000);
       
          
        
        $(function() {
            $(".horizontal-scroll").mousewheel(function(event, delta) {
                this.scrollLeft -= (delta * 30);
                event.preventDefault();
            });

        
        
            $(document).on('click','.toggleAudio',function(){
                $(this).children().toggleClass("glyphicon glyphicon-volume-off glyphicon glyphicon-volume-up");
              });
       
             window.onresize = function(event) {
                //window.location.reload();
            };
        });

        
</script>

    <audio id="beep"<?php if(!empty($playSound)) echo 'autoplay';  ?>>
      <source src="/style/beep.mp3" type="audio/mpeg">
      <source src="/style/beep.ogg" type="audio/ogg">
		Your browser does not support the audio element.
    </audio>


<script src='https://maps.googleapis.com/maps/api/js?key=AIzaSyCN_wvLZh60kpbykFXqiKHNlrm4gl5M6ew&libraries=places&callback=initMap' async defer></script>

         
<script>
   $(document).ready(function() {
    
        $("#speaker").on("click", function(){
            var au = $("#beep")[0];
            au.play();
        });
        
        $("#table-container").show();
        
        $("#event_type").on('change', function() {
            window.location.replace($("#new-filter-uri").val() + "&" + $("#groupid").attr('id') + "=" + $("#groupid").val() + "&" + $("#userid").attr('id') + "=" + $("#userid").val() + "&" + $("#event_type").attr('id') + "=" +$("#event_type").val());
        });
        
        $("#userid").on('change', function() {
            window.location.replace($("#new-filter-uri").val() + "&" + $("#groupid").attr('id') + "=&" + $("#userid").attr('id') + "=" + $("#userid").val() + "&" + $("#event_type").attr('id') + "=" +$("#event_type").val());
        });
        
        $("#groupid").on('change', function() {
            window.location.replace($("#new-filter-uri").val() + "&" + $("#groupid").attr('id') + "=" + $("#groupid").val() + "&" + $("#userid").attr('id') + "=&" + $("#event_type").attr('id') + "=" +$("#event_type").val());
        });
        
    
    });
</script>

