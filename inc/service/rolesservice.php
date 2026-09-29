<?php
class RolesService extends DbConn
{
	private $post;
	private $sortedRole;
	
	
	public function __construct() {
		parent::__construct();
	}
	
	public function hasPermission() {
		return (isset($_SESSION['can_system_modify']) && ($_SESSION['can_system_modify'] == 1));
	}
	
	public function setPost($post) {
		$this->post = $post;
	}
	
	//sorbarendezzük weight szerint a beérkezett tömböt
	public function sortByWeight() {
		
		//sorbarendezzük weight szerint a beérkezett tömböt
					
					$sortedRole = [];
					foreach($this->post['role'] as $roleForSorting){
						
						//egyenként megnézzük, hogy az ID-ben kapott role weightje neme nagyobb mint az enyém, ha átírták a hidden inputban az ID-t
						$stmtCheck = $this->conn->prepare("SELECT weight FROM roles WHERE id = :id AND roles.companies_id = :companies_id");
						$stmtCheck->bindParam(':id', $roleForSorting['id']);
						$stmtCheck->bindParam(':companies_id', $_SESSION['companies_id']);
						
						$stmtCheck->execute();
						$result = $stmtCheck->fetch(PDO::FETCH_ASSOC);
						
						if($result['weight'] > $_SESSION['weight']) throw new \Exception('Input error!');
						
						
						if(((int)$roleForSorting['weight']) >= 65535 || ((int)$roleForSorting['weight']) <= 0 ) throw new \Exception('Input error!');
			
						
						$sortedRole[$roleForSorting['weight']] = $roleForSorting;
					} 
					
					//fordított sorrenben rendezem, hogy a következőnél a legkisebb $i amit kivonok a saját weightből 
					//a legnagyobbnak POSTban beállított weight legyen
					 krsort($sortedRole); 
					 
					 $this->sortedRole = $sortedRole;
	}
	
	//azért kell, hogy ne kadjon véletlenül se, meg ne fogyjon ki a szám
	public function setAllToNull() {
		   //!!! MUSZÁLY 0-nak LENNIE A LEGKISEBB ROLENAK: !!! 
					//először az érintett elemek weightjait NULL-ra állítom, hogy a következ lépésben ne legyen véletlenül se ütközés
					$stmt0 = $this->conn->prepare("
					UPDATE roles SET weight = NULL 
					WHERE roles.weight < :ownweight 
					AND roles.weight > 0
					AND roles.companies_id = :companies_id;");
					
					$stmt0->bindParam(':ownweight', $_SESSION['weight']);
					$stmt0->bindParam(':companies_id', $_SESSION['companies_id']);
					
					$stmt0->execute();
					
	}
	
	//miutan NULL-ra tettük az összeset az új weightekkel feltöltjük, (a fölöttem lévő roleokat békénhagyja)
	public function setPermissionInDb() {
	
			$i = 1;
			foreach($this->sortedRole  as $role) {		
				
				foreach($role as $elem) {
					if(!is_numeric($elem) || strlen($elem) > 11) throw new \Exception('Input error!');
				}
					
				$stmt = $this->conn->prepare("
				UPDATE roles SET 
					weight = :weight, 
					can_system_modify = :can_system_modify, 
					can_user_modify = :can_user_modify, 
					can_video_modify = :can_video_modify, 
					has_all_group = :has_all_group, 
					default_video_permission = :default_video_permission,
					mod_user = :mod_user,
					mod_timestamp = CURRENT_TIMESTAMP
					WHERE id = :id
					AND roles.companies_id = :companies_id
					;
				");
				
				$relWeight = (((int)$_SESSION['weight']) - $i); //így a saját weight vagy a főnökeim weightjei nem sérülnek
		
				$stmt->bindParam(':id', $role['id']);	
				$stmt->bindParam(':companies_id', $_SESSION['companies_id']);				
				$stmt->bindParam(':weight', $relWeight);
				$stmt->bindParam(':can_system_modify', $role['can_system_modify']);
				$stmt->bindParam(':can_user_modify', $role['can_user_modify']);
				$stmt->bindParam(':can_video_modify', $role['can_video_modify']);
				$stmt->bindParam(':has_all_group', $role['has_all_group']);
				$stmt->bindParam(':default_video_permission', $role['default_video_permission']);
				$stmt->bindParam(':mod_user', $_SESSION['id']);
				
				$stmt->execute();
				
				$i++;
			}
		
	}
	
	
	public function getAllRoles() {		
	
			$stmt = $this->conn->prepare("SELECT * FROM roles WHERE roles.companies_id = :companies_id ORDER BY weight DESC");
			$stmt->bindParam(':companies_id', $_SESSION['companies_id']);			
			$stmt->execute();
			
			return $stmt->fetchAll();
			
	}

	
	
	
	//csak az alattam lévő roleokat tudom módosítani
	public function getVisisbleRoleForMe($canSeeWeakest = false,  $canSeeSelfRank = false)
	{
		
		$stmt = $this->conn->prepare("SELECT roles.*, members.id as members_id,r2.id as r2_id, members.username, members.mod_timestamp as m_modetime
			FROM roles 
			INNER JOIN members ON members.id = roles.mod_user
			INNER JOIN roles as r2 ON r2.id=roles.default_video_permission 
			WHERE roles.companies_id = :companies_id
			AND roles.weight != (SELECT MAX(roles.weight) FROM roles) "
			
			. ((!$canSeeWeakest) ?  ' AND roles.weight != (SELECT MIN(roles.weight) FROM roles) ' : '') . 
			
			"AND roles.weight " . (($canSeeSelfRank) ?  ' <= ' : ' < ') . " :weight
			ORDER BY roles.weight DESC");
		
		$stmt->bindParam(':weight', $_SESSION['weight']);
		$stmt->bindParam(':companies_id', $_SESSION['companies_id']);			
		$stmt->execute();
		return $stmt->fetchAll();
	}
	
	
	
	
}
