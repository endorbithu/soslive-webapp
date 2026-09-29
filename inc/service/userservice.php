<?php
class UserService extends DbConn
{
	private $get;
	
	
	public function __construct() {
		parent::__construct();
	}
	
	
	public function setGet($get) {
		$this->get = $get;
	}
	
	
	public function hasPermission() {
		
		$commonPerm = (!(((!isset($_SESSION['can_user_modify']) || empty($_SESSION['can_user_modify']) ) 
		&& (isset($get['id']) && ($get['id']) != $_SESSION['id']))));
		
		return ($commonPerm);
	}
	
	
	public function isAvailableUserForCurrentUser($id) {
		
		$stmt = $this->conn->prepare('SELECT members.*, roles.weight FROM members inner join roles on members.roles_id = roles.id 
			WHERE members.companies_id = :companies_id AND ((members.id = :id AND weight < :weight ) OR (members.id = :members_id ))
			AND (members.groups_id = :groups_id || 1 = :has_all_group )
			');
			
			
			
		$stmt->bindParam(':id', $id);		
		$stmt->bindParam(':weight', $_SESSION['weight']);	
		$stmt->bindParam(':companies_id', $_SESSION['companies_id']);	
		$stmt->bindParam(':members_id', $_SESSION['id']);	
		$stmt->bindParam(':groups_id', $_SESSION['groups_id']);	
		$stmt->bindParam(':has_all_group', $_SESSION['has_all_group']);	
		
		$stmt->execute();
		
		return (!empty($stmt->rowCount()) && $this->hasPermission());
					
	}
	
	
	public function getEditData()
	{
		
		$stmt = $this->conn->prepare("SELECT members.*, roles.id as roles_id, roles.weight  
		  FROM members INNER JOIN roles ON members.roles_id=roles.id 
		  WHERE members.id = :id AND members.companies_id = :companies_id");
		
		$stmt->bindParam(':id', $this->get['id']);
		$stmt->bindParam(':companies_id', $_SESSION['companies_id']);
		$stmt->execute();				
		return $stmt->fetch(PDO::FETCH_ASSOC);	
	}
	
	public function getRoles() {
		$roles = [];
		
		$stmt = $this->conn->prepare("SELECT roles.* FROM roles WHERE weight <= :weight AND roles.companies_id = :companies_id  ORDER BY weight ASC");
		
		$stmt->bindParam(':weight', $_SESSION['weight']);
		$stmt->bindParam(':companies_id', $_SESSION['companies_id']);
		$stmt->execute();
		
		$result = $stmt->fetchAll();
				
		if(!empty($result)) unset($result[0]);
		
		return $result;
	}
	
	public function getGroups() {
		$roles = [];
		
		$stmt = $this->conn->prepare("SELECT groups.* FROM groups WHERE 1=1 AND groups.companies_id = :companies_id " 
		. ($_SESSION['has_all_group'] === '1' ? "" : " AND groups.id= :gid  ") . " ORDER BY groups.name");
		
		if($_SESSION['has_all_group'] != '1') $stmt->bindParam(':gid', $_SESSION['groups_id']);
		$stmt->bindParam(':companies_id', $_SESSION['companies_id']);
		$stmt->execute();
		
		return $stmt->fetchAll();
		
	}
	
	
	
	public function deleteUser($id) {
		
		if(!$this->isAvailableUserForCurrentUser($id)) return false;
		
		try {
			
			$dbLogin = new DbConn;
	
            // prepare sql and bind parameters
            $stmt = $dbLogin->conn->prepare("DELETE FROM members WHERE id = :id");
			$stmt->bindParam(':id', $id);
            $stmt->execute();

            $err = '';

        } catch (PDOException $e) {
            $err = "Error: " . $e->getMessage();
        } catch (Exception $ex) {
			$err = "Error: " . $ex->getMessage();
		}
		
        //Determines returned value ('true' or error code)
        if ($err == '') {
            $success = 'true';
        } else {
            $success = $err;
        }

        return $success;

	}
	
	
    public function createUser($usr, $fullname, $uid, $pw, $group,$role, $verified, $emails, $emailText, $telNum, $smsText) {
		
        try {
			
			$dbLogin = new DbConn;
						
			foreach($dbLogin->conn->query("SELECT weight FROM roles WHERE id = " . $role ) as $row){ 		
					if(($_SESSION['weight'] < $row['weight'] ))
						throw new \Exception('Nincs jogosultság');
			} 		
	
            $tbl_members = $dbLogin->tbl_members;
            // prepare sql and bind parameters
            $stmt = $dbLogin->conn->prepare("INSERT INTO ".$tbl_members." (username, fullname, password, groups_id, roles_id, verified,mod_user, alert_emails, emailtext, smstelnumbers, smstext, companies_id)
            VALUES (:username, :fullname, :password, :groups_id, :roles_id, :verified, :mod_user, :alert_emails, :emailtext, :smstelnumbers, :smstext, :companies_id)");
            $stmt->bindParam(':username', $usr);
            $stmt->bindParam(':fullname', $fullname);
            $stmt->bindParam(':password', $pw);
			$stmt->bindParam(':groups_id', $group);
			$stmt->bindParam(':roles_id', $role);
			$stmt->bindParam(':verified', $verified);
			$stmt->bindParam(':mod_user', $_SESSION['id']);
			$stmt->bindParam(':alert_emails', $emails);
			$stmt->bindParam(':emailtext', $emailText);
			$stmt->bindParam(':smstelnumbers', $telNum);
			$stmt->bindParam(':smstext', $smsText);
			$stmt->bindParam(':companies_id', $_SESSION['companies_id']);
            $stmt->execute();

            $err = '';

        } catch (PDOException $e) {
            $err = "Error: " . $e->getMessage();
        } catch (Exception $ex) {
			$err = "Error: " . $ex->getMessage();
		}
		
        //Determines returned value ('true' or error code)
        if ($err == '') {
            $success = 'true';
        } else {
            $success = $err;
        };

        return $success;

    }
	
	public function updateUser($usr, $fullname,  $uid, $pw,$group,$role, $verified, $emails, $emailText, $telNum, $smsText)
    {
        try {
			$dbLogin = new DbConn;
		
			if(!$this->isAvailableUserForCurrentUser($uid)) return false;
			          
			
            $tbl_members = $dbLogin->tbl_members;
            // prepare sql and bind parameters
            $stmt = $dbLogin->conn->prepare("UPDATE ".$tbl_members." SET 
			fullname = :fullname, groups_id= :groups_id, roles_id = :roles_id, verified = :verified, 
			mod_user = :mod_user, alert_emails = :alert_emails,  emailtext = :emailtext, smstelnumbers = :smstelnumbers, smstext = :smstext   
			" . (!empty($pw) ? ', password = :password ' : '') . "
			WHERE id = :id;
			");
            $stmt->bindParam(':id', $uid);       
			$stmt->bindParam(':fullname', $fullname);
			$stmt->bindParam(':groups_id', $group);
			$stmt->bindParam(':roles_id', $role);
			$stmt->bindParam(':verified', $verified);
			$stmt->bindParam(':mod_user', $_SESSION['id']);
			$stmt->bindParam(':alert_emails', $emails);
			$stmt->bindParam(':emailtext', $emailText);
			$stmt->bindParam(':smstelnumbers', $telNum);
			$stmt->bindParam(':smstext', $smsText);

			
			if(!empty($pw)) $stmt->bindParam(':password', $pw);
			
			
            $stmt->execute();
            $err = '';

        } catch (PDOException $e) {
            $err = "Error: " . $e->getMessage();
        } catch (Exception $ex) {
			$err = "Error: " . $ex->getMessage();
		}
		
        //Determines returned value ('true' or error code)
        if ($err == '') {			
			if($_SESSION['id'] == $uid) {
				//sessionöket átírni
			}
            $success = 'true';

        } else {
            $success = $err;
        };

        return $success;

    }
}
