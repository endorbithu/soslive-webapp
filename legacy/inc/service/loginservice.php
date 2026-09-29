<?php
class LoginService 
{
    public function checkLogin($myusername, $mypassword)
    {
        $conf = new LoginConf;
        $ip_address = $conf->ip_address;
        $login_timeout = $conf->login_timeout;
        $max_attempts = $conf->max_attempts;
        $timeout_minutes = $conf->timeout_minutes;
        $attcheck = $this->checkAttempts($myusername);
        $curr_attempts = $attcheck['attempts'];
		
        $datetimeNow = date("Y-m-d H:i:s");
        $oldTime = strtotime(isset($attcheck['lastlogin']) ? $attcheck['lastlogin'] :  $datetimeNow);
        $newTime = strtotime($datetimeNow);
        $timeDiff = $newTime - $oldTime;

        try {
            $dbLogin = new DbConn(true);
            $tbl_members = $dbLogin->tbl_members;
            $err = '';

        } catch (PDOException $e) {

            $err = "Error: " . $e->getMessage();

        }

        $stmt = $dbLogin->conn->prepare("SELECT members.*, roles.weight, roles.has_all_group, roles.can_user_modify, 
		roles.can_video_modify, roles.can_system_modify FROM ".$tbl_members." 
		INNER JOIN roles ON members.roles_id = roles.id WHERE username = :myusername");
        $stmt->bindParam(':myusername', $myusername);
        $stmt->execute();

        // Gets query result
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($curr_attempts >= $max_attempts && $timeDiff < $login_timeout) {

            //Too many failed attempts
						
            $success = "<div class=\"alert alert-danger alert-dismissable\"><button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-hidden=\"true\">&times;</button>Maximum number of login attempts exceeded... please wait ".$timeout_minutes." minutes before logging in again</div>";

        } else {

             //If max attempts not exceeded, continue
            			
			// Checks password entered against db password 
			if(strlen($mypassword) >= 128) { 
				//js-ben max 127 hosszú lehet a jelszó a user page-nél, tehát ha ez igaz, akkor phone app küldött access tokent
				//itt majd játszunk a JAVA-val, hogy a HEX számokhoz hozzáadunk 1-et, itt meg kivonjuk pl, és így lesz meg a rendes HASH
				$sentPw = $mypassword; 
			} else {
				$sentPw = (hash('sha512', $myusername . '|||' . $mypassword));
			}
			
            if ($sentPw === $result['password'] && $result['verified'] == '1') {
					
					$resetAttempt = '1';
					$now = date("Y-m-d H:i:s");
					$conf = new LoginConf;
					$sql = "UPDATE ".$dbLogin->tbl_attempts." SET attempts = :attempts, lastlogin = :lastlogin where ip = :ip and username = :username";
					$stmt2 = $dbLogin->conn->prepare($sql);
					$stmt2->bindParam(':attempts', $resetAttempt);
					$stmt2->bindParam(':ip', $conf->ip_address);
					$stmt2->bindParam(':lastlogin', $now);
					$stmt2->bindParam(':username', $myusername);
					$stmt2->execute();
					

                //Success! Register $myusername, $mypassword and return "true"
                $success = 'true';
                    
                    $_SESSION['username'] = $myusername;
                    $_SESSION['id'] = $result['id'];
                    $_SESSION['fullname'] = $result['fullname'];
                    $_SESSION['groups_id'] = $result['groups_id'];
                    $_SESSION['roles_id'] = $result['roles_id'];
                    $_SESSION['weight'] = $result['weight'];
                    $_SESSION['can_system_modify'] = $result['can_system_modify'];
                    $_SESSION['can_user_modify'] = $result['can_user_modify'];
                    $_SESSION['can_video_modify'] = $result['can_video_modify'];
                    $_SESSION['has_all_group'] = $result['has_all_group'];
                    $_SESSION['companies_id'] = $result['companies_id'];
					
			
            } elseif ((hash('sha512', $myusername . '|||' . $mypassword) === $result['password']) && $result['verified'] == '0') {

                //Account not yet verified
                $success = "<div class=\"alert alert-danger alert-dismissable\"><button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-hidden=\"true\">&times;</button>
				". L_ACCOUNT_DISABLED ."</div>";

            } else {

                //Wrong username or password
                $success = "<div class=\"alert alert-danger alert-dismissable\"><button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-hidden=\"true\">&times;</button>
				". L_WRONG_USER_PASSW ."</div>";

            }
        }
        return $success;
    }

    public function insertAttempt($username)
    {
        try {
            $dbLogin = new DbConn;
            $conf = new LoginConf;
            $tbl_attempts = $dbLogin->tbl_attempts;
            $ip_address = $conf->ip_address;
            $login_timeout = $conf->login_timeout;
            $max_attempts = $conf->max_attempts;


            $datetimeNow = date("Y-m-d H:i:s");
            $attcheck = $this->checkAttempts($username);
            $curr_attempts =  $attcheck['attempts'] ;

            $stmt = $dbLogin->conn->prepare("INSERT INTO ".$tbl_attempts." (ip, attempts, lastlogin, username) values(:ip, 1, :lastlogin, :username)");
            $stmt->bindParam(':ip', $ip_address);
            $stmt->bindParam(':lastlogin', $datetimeNow);
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            $curr_attempts++;
            $err = '';

        } catch (PDOException $e) {

            $err = "Error: " . $e->getMessage();

        }

        //Determines returned value ('true' or error code)
        $resp = ($err == '') ? 'true' : $err;

        return $resp;

    }

	
	public function checkAttempts($username) {

		try {

			$dbLogin = new DbConn;
			$conf = new LoginConf;
			$tbl_attempts = $dbLogin->tbl_attempts;
			$ip_address = $conf->ip_address;
			$err = '';

			$sql = "SELECT attempts as attempts, lastlogin FROM ".$tbl_attempts." WHERE ip = :ip and username = :username";

			$stmt = $dbLogin->conn->prepare($sql);
			$stmt->bindParam(':ip', $ip_address);
			$stmt->bindParam(':username', $username);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			
			$oldTime = strtotime($result['lastlogin']);
			$newTime = time();
			$timeDiff = $newTime - $oldTime;
			
			if($timeDiff > $conf->login_timeout) $result['attempts'] = 1;
			
			return $result;

		} catch (PDOException $e) {
			
			$err = "Error: " . $e->getMessage();
			error_log( $err);

		}

		//Determines returned value ('true' or error code)
		$resp = ($err == '') ? 'true' : $err;

		return $resp;

	}
	
	
    public function updateAttempts($username)
    {
        try {
            $dbLogin = new DbConn;
            $conf = new LoginConf;
            $tbl_attempts = $dbLogin->tbl_attempts;
            $ip_address = $conf->ip_address;
            $login_timeout = $conf->login_timeout;
            $max_attempts = $conf->max_attempts;
            $timeout_minutes = $conf->timeout_minutes;

            $attcheck = $this->checkAttempts($username);
            $curr_attempts =  $attcheck['attempts'] ;

            $datetimeNow = date("Y-m-d H:i:s");
            $oldTime =  strtotime(isset($attcheck['lastlogin']) ? $attcheck['lastlogin'] : $datetimeNow) ;
            $newTime = strtotime($datetimeNow);
            $timeDiff = $newTime - $oldTime;

            $err = '';
            $sql = '';

			
            if ($curr_attempts >= $max_attempts && $timeDiff < $login_timeout) {

                if ($timeDiff >= $login_timeout) {

                    $sql = "UPDATE ".$tbl_attempts." SET attempts = :attempts, lastlogin = :lastlogin where ip = :ip and username = :username";
                    $curr_attempts = 1;

                }

            } else {

                if ($timeDiff < $login_timeout) {

                    $sql = "UPDATE ".$tbl_attempts." SET attempts = :attempts, lastlogin = :lastlogin where ip = :ip and username = :username";
                    $curr_attempts++;

                } elseif ($timeDiff >= $login_timeout) {

                    $sql = "UPDATE ".$tbl_attempts." SET attempts = :attempts, lastlogin = :lastlogin where ip = :ip and username = :username";
                    $curr_attempts = 1;

                }

                $stmt2 = $dbLogin->conn->prepare($sql);
                $stmt2->bindParam(':attempts', $curr_attempts);
                $stmt2->bindParam(':ip', $ip_address);
                $stmt2->bindParam(':lastlogin', $datetimeNow);
                $stmt2->bindParam(':username', $username);
                $stmt2->execute();

            }

        } catch (PDOException $e) {

            $err = "Error: " . $e->getMessage();

        }

        //Determines returned value ('true' or error code) (ternary)
        $resp = ($err == '') ? 'true' : $err;

        return $resp;

    }

}
