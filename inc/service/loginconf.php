<?php
class LoginConf
{
    public $conf;
    public static $attempts;
	public static $apis = ["SOSlive" => "***REMOVED***"];
	public static $dayToDelete = 180;

    public function __construct()
    {
        $this->ip_address = $_SERVER['REMOTE_ADDR'];
        $this->login_timeout = 900;
        $this->timeout_minutes = round(($this->login_timeout / 60), 1);
        $this->base_url = ( empty($_SERVER['HTTPS']) ? 'http://' : 'https://') . $_SERVER['SERVER_NAME'];
        $this->signin_url = substr($this->base_url . $_SERVER['PHP_SELF'], 0, -(6 + strlen(basename($_SERVER['PHP_SELF']))));
        $this->max_attempts = 10;
    }

    public function addAttempt()
    {
        $attempts++;
    }
    public function resetAttempts()
    {
        $attempts = 0;
    }
}
