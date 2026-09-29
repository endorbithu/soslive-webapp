<?php

require_once('../../inc/controller/core.php'); 

$dbConn = new DbConn(true);
	
//if(empty($_SERVER['HTTPS'])) exit; nem SSL a feltöltés az androidban de nem is baj


if(!isset($post['app_token']) || !in_array($post["app_token"], APIS) )	 
{
    http_response_code(404);
	print "-1";
	exit;
}


	 
	// *** BINÁRIS KÉPADAT FOGADÁSA -------------------------------------------------------------------------------------------
	

	if(!empty($_FILES) && hash('sha512', $post['username'] . '||' . ACCESS_TOKEN_SALT . '||' . $post['id']) === $post['access_token']) {

		
	    $stmt = $dbConn->conn->prepare('SELECT `datetime` FROM `posts` WHERE `id` = :posts_id;');
	    $stmt->bindParam(':posts_id', $post['id']);
        $stmt->execute();
		$aPost = $stmt->fetch(PDO::FETCH_ASSOC);   
		
		
		if(!isset($aPost['datetime'])) exit;
		
		$t = new DateTime($aPost['datetime']);
		
		$path = __DIR__ . '/../photos/' . $t->format('Ymd') . '/' . $post['id'] . '_' . substr($post['access_token'],0,48);
		$file = date('YmdHis', time());
		$pathAndFile =  $path . '/' . $file;
		
		if (!file_exists($path)) {
			mkdir($path , 0777, true);
		}
		
		if(move_uploaded_file($_FILES['uploaded_file']['tmp_name'], $pathAndFile)) {
		    
		      $exif= exif_read_data($pathAndFile);
			
			  $img = imagecreatefromjpeg($pathAndFile);
			  
			    if(!empty($exif['Orientation'])) {
                    switch($exif['Orientation']) {
                        case 8:
                            $img = imagerotate($img,90,0);
                            break;
                        case 3:
                            $img = imagerotate($img,180,0);
                            break;
                        case 6:
                            $img = imagerotate($img,-90,0);
                            break;
                    }
                }
			   
			   
			 //if(imagejpeg($img, $pathAndFile ,100)) {
			  
			  $width = imagesx( $img );
			  $height = imagesy( $img );
			   
			   
			  // calculate thumbnail size
			  $new_height = 250;
			  $new_width = floor( $width * ( $new_height / $height ) );

			  // create a new temporary image
			  $tmp_img = imagecreatetruecolor( $new_width, $new_height );


			  // copy and resize old image into new image 
			  imagecopyresized( $tmp_img, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height );
			  
			  
			  // save thumbnail into a file
			  print(imagejpeg($tmp_img, $path . '/thumb_' . $file ));
		} else{
	        http_response_code(404);
			echo "0";
		}	
	
	
		
		exit;
	        
	}
	
	
	
	http_response_code(404);
	
	
	
	
	
	

	
	
	
	
	
	




