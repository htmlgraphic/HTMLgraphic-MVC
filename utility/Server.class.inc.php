<?php
class Server
{

	static function getReferrer()
	{
		return (isset($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : "";
	}

	static function getUserAgent()
	{
		return (isset($_SERVER['HTTP_USER_AGENT'])) ? $_SERVER['HTTP_USER_AGENT'] : "";
	}

	static function getRemoteIP()
	{
		return (isset($_SERVER['REMOTE_ADDR'])) ? $_SERVER['REMOTE_ADDR'] : "";
	}

	static function getServerAddress()
	{
		return (isset($_SERVER['SERVER_ADDR'])) ? $_SERVER['SERVER_ADDR'] : "";
	}

	static function getSelf()
	{
		return (isset($_SERVER['PHP_SELF'])) ? $_SERVER['PHP_SELF'] : "";
	}

	static function getHost()
	{
		return (isset($_SERVER['HTTP_HOST'])) ? $_SERVER['HTTP_HOST'] : "";
	}

	static function getRemoteHost()
	{
		return (isset($_SERVER['REMOTE_HOST'])) ? $_SERVER['REMOTE_HOST'] : "";
	}

	static function getFilename()
	{
		return (isset($_SERVER['SCRIPT_FILENAME'])) ? $_SERVER['SCRIPT_FILENAME'] : "";
	}

	static function getForwardedIP()
	{
		return (isset($_SERVER['HTTP_X_FORWARDED_FROM'])) ? $_SERVER['HTTP_X_FORWARDED_FROM'] : "";
	}

	static function getRequestURI()
	{
		return (isset($_SERVER['REQUEST_URI'])) ? $_SERVER['REQUEST_URI'] : "";
	}

	//use this getIP for more compatibilty, unless you need the specific forwarded ip or remote ip
	static function getIP()
	{
		return self::getRemoteIP();
	}
}
?>