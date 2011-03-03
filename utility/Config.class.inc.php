<?php
/*
  Class: Config

  Static class to store config settings retrieved like so
  Config::get('DocRoot');

  You can also set things dynamically with
  Config::set('NewSetting', 'new value');

  This is to keep the namespace clean, not sure if it's neccessary, feel free
  to simplify.

  If a setting needs to act like a constant, set it here as a class variable
  and it can't be overwritten.

  If you need a dynamic constant, declare the class variable here and once set
  with Config::set('DeclaredVariable', 'value'), it can't be overwritten.
 */

class Config
{
	private static
	$DocRoot,
	$ModelRoot,
	$MutatorRoot,
	$ViewRoot,
	$ControllerRoot,
	$UtilityRoot,
	$ModuleRoot,
	$VendorRoot,
	$CronRoot,
	$Domain,
	$DevMode,
	$config = array();

	public static function init()
	{
		self::set('StartTime', microtime(true));
		$docroot = realpath(dirname(__FILE__) . '/../');
		self::set('DocRoot', $docroot);
		self::set('ModelRoot', "$docroot/model");
		self::set('MutatorRoot', "$docroot/mutator");
		self::set('VendorRoot', "$docroot/vendor");
		self::set('CronRoot', "$docroot/cron");
		self::set('UtilityRoot', "$docroot/utility");
		self::set('ModuleRoot', "$docroot/module");
		self::set('DevMode', get_cfg_var('development_mode'));
		self::set('DataCenter', get_cfg_var('datacenter'));
	}

	/*
	  Method: get

	  Returns val if found, otherwise null
	 */

	public static function get($name)
	{
		$value = null;
		if (isset(self::$$name))
			$value = self::$$name;
		if (isset(self::$config[$name]))
			$value = self::$config[$name];

		return $value;
	}

	/*
	  Method: set

	  Returns true on success, false if attempting to overwrite previously set
	  class variable
	 */

	public static function set($name, $value)
	{

		if (isset(self::$$name))
			return false;

		if (property_exists('Config', $name)) {
			self::$$name = $value;
		}
		else {
			self::$config[$name] = $value;
		}

		return true;
	}

	public static function isMyDevBox()
	{
		return (self::get('DevMode') == '1');
	}

	public static function isTheDevBox()
	{
		return (self::get('DevMode') == '2');
	}

	public static function isStaging()
	{
		return (self::get('DevMode') == '3');
	}

	public static function isLive()
	{
		$devmode = self::get('DevMode');
		return (empty($devmode));
	}

	public static function isSoftLayer()
	{
		return (self::get('DataCenter') == 'softlayer');
	}
}
Config::init();
?>