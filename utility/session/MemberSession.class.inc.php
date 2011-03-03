<?php

class MemberSession
{

	private static
	$OLD_KEY = 'id',
	$NEW_KEY = 'ea_login_user_id',
	$instance;

	static function instance()
	{
		if (!isset(self::$instance))
		{
			self::$instance = new MemberSession();
		}

		return self::$instance;
	}

	private function __construct()
	{
		if (!isset($_SESSION))
		{
			ini_set("session.cookie_path", "/");
			if (get_cfg_var("development_mode"))
			{
				ini_set("session.cookie_domain", "passinggreen.com"); //session will work on development domain and all subdomains
			}
			else
			{
				ini_set("session.cookie_domain", "passinggreen.com"); //session will work public and all subdomains
			}
			ini_set("session.use_only_cookies", "1"); //forces users to accept cookies, but more secure
			ini_set("session.use_trans_sid", "0"); //Do not enable this! Session IDs in the URL are bad, m'kay?
			session_start();
		}
	}

	function isLoggedIn()
	{
		return (
		( isset($_SESSION[self::$OLD_KEY]) && !empty($_SESSION[self::$OLD_KEY]) ) ||
		( isset($_SESSION[self::$NEW_KEY]) && !empty($_SESSION[self::$NEW_KEY]) )
		);
	}

	function getMemberId()
	{
		if (isset($_SESSION[self::$OLD_KEY]) && !empty($_SESSION[self::$OLD_KEY]))
			return $_SESSION[self::$OLD_KEY];

		if (isset($_SESSION[self::$NEW_KEY]) && !empty($_SESSION[self::$NEW_KEY]))
			return $_SESSION[self::$NEW_KEY];
	}

	function getMemberPass()
	{
		if (isset($_SESSION['pass']))
			return $_SESSION['pass'];
	}

}
?>