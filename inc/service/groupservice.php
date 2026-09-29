<?php
class GroupService extends DbConn
{
	private $get;
	
	
	public function __construct() {
		parent::__construct();
	}
	
	
	public function setGet($get) {
		$this->get = $get;
	}
	
	
	public function hasPermission() {
		return(((isset($_SESSION['can_system_modify']) && !empty($_SESSION['can_system_modify']) )));
	}
	
	
	public function isAvailableGroupForCurrentUser($id) {
		$stmt = $this->conn->prepare('SELECT * FROM groups WHERE id = :id AND groups.companies_id = :companies_id');
		$stmt->bindParam(':id', $id);
		$stmt->bindParam(':companies_id', $_SESSION['companies_id']);
		$stmt->execute();

		return !empty($stmt->rowCount());		
	}
	
	
	public function deleteGroup($gid) {
		try {
						
			if(!isset($_SESSION['can_system_modify']) || empty($_SESSION['can_system_modify'])) throw new \Exception('Nincs jogosultság');
	
            $stmt = $this->conn->prepare("DELETE FROM groups WHERE id = :id AND groups.companies_id = :companies_id");         
			$stmt->bindParam(':id', $gid);
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
	
    public function createGroup($name,$emails, $emailText, $telNum, $smsText)
    {
        try {
						
			if(!isset($_SESSION['can_system_modify']) || empty($_SESSION['can_system_modify'])) throw new \Exception('Nincs jogosultság');
	
            // prepare sql and bind parameters
            $stmt = $this->conn->prepare("INSERT INTO groups (name,alert_emails, emailtext, smstelnumbers, smstext,companies_id) 
			VALUES (:name, :alert_emails, :emailtext, :smstelnumbers, :smstext, :companies_id)");
			$stmt->bindParam(':name', $name);
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
	
	public function updateGroup($gid, $name,$emails, $emailText, $telNum, $smsText)
    {
        try {
			
			if(!isset($_SESSION['can_system_modify']) || empty($_SESSION['can_system_modify'])) throw new \Exception('Nincs jogosultság');
			
            // prepare sql and bind parameters
            $stmt = $this->conn->prepare("UPDATE groups SET name = :name, alert_emails = :alert_emails,emailtext = :emailtext, smstelnumbers = :smstelnumbers, smstext = :smstext  
			WHERE id = :id AND groups.companies_id = :companies_id; ");    
			$stmt->bindParam(':id', $gid);
			$stmt->bindParam(':companies_id', $_SESSION['companies_id']);			
			$stmt->bindParam(':name', $name);
			$stmt->bindParam(':alert_emails', $emails);
			$stmt->bindParam(':emailtext', $emailText);
			$stmt->bindParam(':smstelnumbers', $telNum);
			$stmt->bindParam(':smstext', $smsText);
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
	
	public function getEditData() {

		$stmt = $this->conn->prepare('SELECT *  FROM groups WHERE id = :id AND groups.companies_id = :companies_id');
		$stmt->bindParam(':id', $this->get['id']);
		$stmt->bindParam(':companies_id', $_SESSION['companies_id']);
		$stmt->execute();				
		return $stmt->fetch(PDO::FETCH_ASSOC);	
	
	}
	
}
