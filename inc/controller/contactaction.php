<?php

$csrf = $_SESSION['csrf-token'];

if(isset($post["is-contact"]) && $post["is-contact"] == "1") {
   
			$email;$comment;$captcha;$message;

			if(isset($post['name'])){
			  $name=$post['name'];
			}
			
			if(isset($post['yourcontact'])){
			  $email=$post['yourcontact'];
			}
			
			if(isset($post['subject'])){
			  $subject=$post['subject'];
			} 
			
			if(isset($post['message'])){
			  $message=$post['message'];
			} 
	
			
			//Create a new PHPMailer instance
			
			$mail = new PHPMailer();
			//Set who the message is to be sent from
			$mail->setFrom($email, $name);
			//Set who the message is to be sent to
			$mail->addAddress('sosliveinfocontact@gmail.com','SOSlive' );
			//Set the subject line
			$mail->Subject = $subject;
			//Read an HTML message body from an external file, convert referenced images to embedded,
			//convert HTML into a basic plain-text alternative body
			$mail->msgHTML(nl2br($message) . '<br><br><hr><br>Innen kè´‰ldve: <a href="http://soslive.info' . $_SERVER['REQUEST_URI'] . '">http://soslive.info' . $_SERVER['REQUEST_URI'] . '</a><br><br>'  . $name . '<br>' . $email);

			$mail->AltBody = $message;
			//send the message, check for errors
			if ($mail->send()) {
				 echo '<div style="text-align: center"><div class="alert alert-success" role="alert">' . MESSAGE_SENT . '</div></div>';
			} else {
				echo '<div style="text-align: center"><div class="alert alert-danger" role="alert" style="text-align: center">' . MESSAGE_NOT_SENT . '</div></div>';
				error_log($mail->ErrorInfo);
			}

        
}



?>
