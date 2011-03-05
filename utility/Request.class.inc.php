<?php
/*
 * Use this to get request variables ($_POST, $_GET)
 */

class Request
{

	private static $data;

	static function get($name = null, $type = 'REQUEST')
	{
		if (!isset(self::$data)) {
			// I've experienced empty $_GET var for some reason so I'm populating
			// it here. -c4
			$GET = substr(strstr($_SERVER['REQUEST_URI'], '?'), 1);
			parse_str($GET, $_GET);

			self::$data = array(
			    'REQUEST' => array_merge($_GET, $_POST, $_COOKIE),
			    'GET' => $_GET,
			    'POST' => $_POST,
			    'COOKIE' => $_COOKIE,
			    'FILES' => $_FILES
			);

			foreach (self::$data as $key => & $val)
			{
				foreach ($val as $k => & $v)
				{
					if (($key == "REQUEST" || $key == "GET") && is_string($v) && (substr_count($v, "script") > 0 && substr_count($v, "/script") > 0)) {
						$clean = self::strip_only($v, array("script"), true);

						if (strlen($v) <> strlen($clean))
							$v = $clean;
					}
				}
			}
		}

		if (!isset($name)) {
			return self::$data[$type];
		}
		else {
			if (isset(self::$data[$type][$name])) {
				return self::$data[$type][$name];
			}
			else {
				return null;
			}
		}
	}

	static function getPost($name = null)
	{
		$vals = self::get($name, 'POST');
		if (is_array($vals)) {
			foreach ($vals as $key => $val)
				$new[$key] = addslashes($val);
		}
		else
			$new = addslashes($vals);
		return isset($new) ? $new : null;
	}

	static function getRequest($name = null)
	{
		$vals = self::get($name, 'REQUEST');
		if (is_array($vals)) {
			foreach ($vals as $key => $val)
				$new[$key] = addslashes($val);
		}
		else
			$new = addslashes($vals);
		return $new;
	}

	static function getGet($name = null)
	{
		$vals = self::get($name, 'GET');
		if (is_array($vals)) {
			foreach ($vals as $key => $val)
				$new[$key] = addslashes($val);
		}
		else
			$new = addslashes($vals);
		return $new;
	}

	static function getCookie($name = null)
	{
		$vals = self::get($name, 'COOKIE');
		if (is_array($vals)) {
			foreach ($vals as $key => $val)
				$new[$key] = addslashes($val);
		}
		else
			$new = addslashes($vals);
		return $new;
	}

	//not sure if we can addslashes to files
	static function getFile($name = null)
	{
		return self::_FILES($name);
	}

	static function clean($name, $type = 'REQUEST')
	{
		return self::get($name, $type);
	}

	static function _GET($name = null)
	{
		return self::get($name, 'GET');
	}

	static function _REQUEST($name = null)
	{
		return self::get($name, 'REQUEST');
	}

	static function _POST($name = null)
	{
		Debugger::log(__METHOD__ . " is deprecated, use getPost() instead.");
		return self::get($name, 'POST');
	}

	static function _COOKIE($name = null)
	{
		return self::get($name, 'COOKIE');
	}

	static function _FILES($name = null)
	{
		return self::get($name, 'FILES');
	}

	function strip_only($str, $tags, $stripContent = false)
	{
		$content = '';
		if (!is_array($tags)) {
			$tags = (strpos($str, '>') !== false ? explode('>', str_replace('<', '', $tags)) : array($tags));
			if (end($tags) == '')
				array_pop($tags);
		}
		foreach ($tags as $tag)
		{
			if ($stripContent)
				$content = '(.+</' . $tag . '[^>]*>|)';
			$str = preg_replace('#</?' . $tag . '[^>]*>' . $content . '#is', '', $str);
		}
		return $str;
	}

}
?>