<?php
class UsersService extends DbConn
{
	private $get;
	
	
	public function __construct() {
		parent::__construct();
	}
	
	
	public function hasPermission() {
		
		return(isset($_SESSION['can_user_modify']) && !empty($_SESSION['can_user_modify']));
	}

	public function getUsers() {
		try {
			
            $stmt = $this->conn->prepare("SELECT members.id as members_id, members.username, members.fullname, 
			members.verified, members.mod_timestamp, members.alert_emails, roles.name as roles_name, groups.name as groups_name, 
			roles.weight, roles.can_system_modify, roles.can_user_modify,roles.has_all_group, roles.id as roles_id,
			groups.id as groups_id, m2.id as m2_id, m2.username as m2_username 
			FROM members 
			INNER JOIN roles on members.roles_id = roles.id 
			INNER JOIN groups ON members.groups_id=groups.id 
			INNER JOIN members as m2 ON members.mod_user = m2.id 
			WHERE roles.weight <= :weight 
			AND (members.groups_id = :gid OR :hasallgroup = 1  )
			AND members.companies_id = :companies_id
			ORDER BY verified DESC, members.fullname ASC");         
				
			$stmt->bindParam(':weight', $_SESSION['weight']);
			$stmt->bindParam(':gid', $_SESSION['groups_id']);
			$stmt->bindParam(':companies_id', $_SESSION['companies_id']);
			$stmt->bindParam(':hasallgroup', $_SESSION['has_all_group']);
			$stmt->execute();
			
			return $stmt->fetchAll();
			
			
		} catch (PDOException $e) {
            print "Error!";
			error_log($e->getMessage());
        } catch (Exception $ex) {
			print "Error!";
			error_log($e->getMessage());
		}

	}
	
	
	public function getRoles() {
		$roles = [];
		
		$stmt = $this->conn->prepare("SELECT roles.* FROM roles WHERE weight <= :weight AND roles.companies_id = :companies_id  
										ORDER BY weight ASC");
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
	
	
	
    public function getBiggestUser($groupId) {
		
        try {						
	
            // prepare sql and bind parameters
			$stmt = $this->conn->prepare("SELECT members.id as members_id, members.fullname ,roles.name
								FROM members INNER JOIN roles ON members.roles_id=roles.id 
								WHERE members.companies_id = :companies_id
								AND groups_id = :gid order by weight DESC LIMIT 1"); 
								
			$stmt->bindParam(':gid', $groupId);
			$stmt->bindParam(':companies_id', $_SESSION['companies_id']);
            $stmt->execute();

			if(!empty($ret = $stmt->fetchAll())) {
				return $ret[0];
			}
			return [];
			
			

        } catch (PDOException $e) {
            print "Error!";
			error_log($e->getMessage());
        } catch (Exception $ex) {
			print "Error!";
			error_log($e->getMessage());
		}
		
		
		
    }
	
	
}
