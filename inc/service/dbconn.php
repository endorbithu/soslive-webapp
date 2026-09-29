<?php
// Extend this class to re-use db connection
class DbConn
{
    public $conn;
    public function __construct($ifForceUpdateInReadOnlyMode = false)
    {
		
	
        

		$host = DB_HOST; // Host name 
		if($ifForceUpdateInReadOnlyMode) {
			$username = DB_USERNAME_FOR_UPDATE; // Mysql username
			$password = DB_PW_FOR_UPDATE; // Mysql password
		} else {
			$username = DB_USERNAME; // Mysql username
			$password = DB_PW; // Mysql password			
		}
		
		$db_name = DB_NAME; // Database name
		$tbl_prefix = DB_PREFIX; //***PLANNED FEATURE, LEAVE VALUE BLANK FOR NOW*** Prefix for all database tables
		$tbl_members = DB_MEMBERS;
		$tbl_attempts = DB_ATTEMPTS;

		
		
        $this->host = $host; // Host name
        $this->username = $username; // Mysql username
        $this->password = $password; // Mysql password
        $this->db_name = $db_name; // Database name
        $this->tbl_prefix = $tbl_prefix; // Prefix for all database tables
        $this->tbl_members = $tbl_members;
        $this->tbl_attempts = $tbl_attempts;

        try {
			// Connect to server and select database.
			$this->conn = new PDO('mysql:host=' . $host . ';dbname=' . $db_name . ';charset=utf8', $username, $password);
			$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} catch (\Exception $e) {

			die('Database connection error' . $e->getMessage());
		}
    }
	

		
	public static function mySqlErrors($response)
	{
		//Returns custom error messages instead of MySQL errors
		switch (substr($response, 0, 22)) {

			case 'Error: SQLSTATE[23000]':
				echo "<div class=\"alert alert-danger alert-dismissable\"><button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-hidden=\"true\">&times;</button>
					". L_DUPLICATED_OR_FOREIGN_KEY_C."</div>";
				break;

			default:
				echo "<div class=\"alert alert-danger alert-dismissable\"><button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-hidden=\"true\">&times;</button>
				". L_ERROR_OCCURED ."</div>";
		}
	}
}
