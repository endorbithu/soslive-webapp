<?php
class GroupsService extends DbConn
{
	private $get;
	
	
	public function __construct() {
		parent::__construct();
	}
	
	
	public function hasPermission() {
		
		return(isset($_SESSION['can_system_modify']) && !empty($_SESSION['can_system_modify']));
	}

	public function getGroups() {
		try {
						
            $stmt = $this->conn->prepare("SELECT * from groups WHERE groups.companies_id = :companies_id order by name ");     
			$stmt->bindParam(':companies_id', $_SESSION['companies_id']);
            $stmt->execute();

			$result = $stmt->fetchAll();
			$groups = [];
			foreach($result as $key => $val) {
				$groups[$key] = $val;
				if(!empty($bUser = $this->getBiggestUser($val['id']))){
					
					$groups[$key]['fullname'] = $bUser['fullname'];
					$groups[$key]['members_id'] = $bUser['members_id'];
					$groups[$key]['roles_name'] = $bUser['name'];
				}
			}
			
			return (empty($groups) ? [] : $groups);
			
		} catch (PDOException $e) {
            print "Error!";
			error_log($e->getMessage());
        } catch (Exception $ex) {
			print "Error!";
			error_log($e->getMessage());
		}

	}
	
    public function getBiggestUser($groupId)
    {
        try {						
	
            // prepare sql and bind parameters
			$stmt = $this->conn->prepare("SELECT members.id as members_id, members.fullname ,roles.name
								FROM members INNER JOIN roles ON members.roles_id=roles.id 
								WHERE groups_id = :gid AND members.companies_id = :companies_id ORDER by weight DESC LIMIT 1"); 
								
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
