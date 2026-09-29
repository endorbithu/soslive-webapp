<?php
class RoleService extends DbConn
{
	private $get;
	private $sortedRole;
	
	
	public function __construct() {
		parent::__construct();
	}
	
	
	public function setGet($get) {
		$this->get = $get;
	}
	
	
	public function isAvailableRoleForCurrentUser($id) {
		$stmt = $this->conn->prepare('SELECT weight FROM roles WHERE id = :id AND roles.companies_id = :companies_id');
		$stmt->bindParam(':id', $this->get['id']);
		$stmt->bindParam(':companies_id', $_SESSION['companies_id']);
		$stmt->execute();
		return !empty($stmt->rowCount());		
	}
	
	
	public function hasPermission()
	{
		//olyan rolet tudjon módosítani, aminek a weightje kiseb mint a sajátja
		if(isset($this->get['id']) && is_numeric($this->get['id'])) {
			
			$stmt = $this->conn->prepare('SELECT weight FROM roles WHERE id = :id AND roles.companies_id = :companies_id');
			$stmt->bindParam(':id', $this->get['id']);
			$stmt->bindParam(':companies_id', $_SESSION['companies_id']);
			$stmt->execute();				
			$row = $stmt->fetch(PDO::FETCH_ASSOC);				
			
			
			if(!(((int)$row['weight']) < ((int)$_SESSION['weight']))) return false;
		}
		
		return((isset($_SESSION['can_system_modify']) && ($_SESSION['can_system_modify'] == 1)));
	}
	
	
	public function deleteRole($rid) {
		try {
			
			$dbLogin = new DbConn;
			
			if(!isset($_SESSION['can_system_modify']) || empty($_SESSION['can_system_modify'])) throw new \Exception('Nincs jogosultság');
	
		
	
            $stmt = $dbLogin->conn->prepare("
				DELETE FROM roles 
				WHERE roles.id = :id AND roles.companies_id = :companies_id
				AND roles.weight NOT IN (SELECT r21.weight FROM (SELECT MAX(r2.weight) as weight FROM roles as r2) as r21) 
				AND roles.weight NOT IN (SELECT r22.weight FROM (SELECT MIN(r2.weight) as weight FROM roles as r2) as r22); 	
				
			");         
			$stmt->bindParam(':id', $rid);
			$stmt->bindParam(':companies_id', $_SESSION['companies_id']);
            $stmt->execute();
			
						
			$stmt2 = $dbLogin->conn->prepare("UPDATE roles set mod_user = :mod_user, mod_timestamp = CURRENT_TIMESTAMP;");
			$stmt2->bindParam(':mod_user', $_SESSION['id']);
            $stmt2->execute();

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
	
    public function createRole($name)
    {
        try {
			
			$dbLogin = new DbConn;
			
			
			
			
			if(!isset($_SESSION['can_system_modify']) || empty($_SESSION['can_system_modify'])) throw new \Exception('Nincs jogosultság');
	
            // prepare sql and bind parameters
            $stmt = $dbLogin->conn->prepare("
			INSERT INTO roles (name,mod_user,default_video_permission,weight,companies_id) 
				VALUES (:name, :mod_user,
				(SELECT MAX(r2.id) FROM roles as r2 
                WHERE r2.weight != (SELECT MAX(r3.weight) FROM roles as r3)
                AND r2.weight != (SELECT MIN(r4.weight) FROM roles as r4))
			,((
				SELECT MIN(r2.weight) FROM roles as r2 
                WHERE r2.weight != (SELECT MAX(r3.weight) FROM roles as r3)
                AND r2.weight != (SELECT MIN(r4.weight) FROM roles as r4))
             - 1), :companies_id);		 
			 
			 
			 ");
			$stmt->bindParam(':name', $name);
			$stmt->bindParam(':mod_user', $_SESSION['id']);
			$stmt->bindParam(':companies_id', $_SESSION['companies_id']);
            $stmt->execute();

						
			$stmt2 = $dbLogin->conn->prepare("UPDATE roles set mod_user = :mod_user, mod_timestamp = CURRENT_TIMESTAMP;");
			$stmt2->bindParam(':mod_user', $_SESSION['id']);
            $stmt2->execute();
			
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
	
	public function updateRole($rid, $name)
    {
        try {
			$dbLogin = new DbConn;
			
			if(!isset($_SESSION['can_system_modify']) || empty($_SESSION['can_system_modify'])) throw new \Exception('Nincs jogosultság');
			
            // prepare sql and bind parameters
            $stmt = $dbLogin->conn->prepare("UPDATE roles SET name = :name WHERE id = :id AND companies_id = :companies_id;");    
			$stmt->bindParam(':id', $rid);
			$stmt->bindParam(':companies_id', $_SESSION['companies_id']);
			$stmt->bindParam(':name', $name);
            $stmt->execute();		
						
						
			$stmt2 = $dbLogin->conn->prepare("UPDATE roles set mod_user = :mod_user, mod_timestamp = CURRENT_TIMESTAMP;");
			$stmt2->bindParam(':mod_user', $_SESSION['id']);
            $stmt2->execute();

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

		$stmt = $this->conn->prepare('SELECT *  FROM roles 
		WHERE roles.companies_id = :companies_id AND roles.weight != (SELECT MAX(roles.weight) FROM roles) 
		AND roles.weight != (SELECT MIN(roles.weight) FROM roles) AND id = :id');
		$stmt->bindParam(':id', $this->get['id']);
		$stmt->bindParam(':companies_id', $_SESSION['companies_id']);
		$stmt->execute();				
		return $stmt->fetch(PDO::FETCH_ASSOC);	
	
	}
	
	
	
}





