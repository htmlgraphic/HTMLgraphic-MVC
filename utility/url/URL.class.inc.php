<?php
class URL
{

	private static
	$url,
	$url_parts,
	$rules = array(),
	$controllers = array(),
	$aliases = array(),
	$path_map;
	//root source http://www.iana.org/domains/root/db/
	// generic tlds (source: http://en.wikipedia.org/wiki/Generic_top-level_domain)
	private $generic_tld = array(
	    'biz', 'com', 'edu', 'gov', 'info', 'int', 'mil', 'name', 'net', 'org',
	    'aero', 'asia', 'cat', 'coop', 'jobs', 'mobi', 'museum', 'pro', 'tel', 'travel',
	    'arpa', 'root',
	    'berlin', 'bzh', 'cym', 'gal', 'geo', 'kid', 'kids', 'lat', 'mail', 'nyc', 'post', 'sco', 'web', 'xxx',
	    'nato',
	    'example', 'invalid', 'localhost', 'test',
	    'bitnet', 'csnet', 'ip', 'local', 'onion', 'uucp',
	    'co' // note: not technically, but used in things like co.uk
	);
	// country tlds (source: http://en.wikipedia.org/wiki/Country_code_top-level_domain)
	private $common_tld = array(
	    'ac', 'ad', 'ae', 'af', 'ag', 'ai', 'al', 'am', 'an', 'ao', 'aq', 'ar', 'as', 'at', 'au', 'aw', 'ax', 'az',
	    'ba', 'bb', 'bd', 'be', 'bf', 'bg', 'bh', 'bi', 'bj', 'bm', 'bn', 'bo', 'br', 'bs', 'bt', 'bw', 'by', 'bz',
	    'ca', 'cc', 'cd', 'cf', 'cg', 'ch', 'ci', 'ck', 'cl', 'cm', 'cn', 'co', 'cr', 'cu', 'cv', 'cx', 'cy', 'cz',
	    'de', 'dj', 'dk', 'dm', 'do', 'dz', 'ec', 'ee', 'eg', 'er', 'es', 'et', 'eu', 'fi', 'fj', 'fk', 'fm', 'fo',
	    'fr', 'ga', 'gd', 'ge', 'gf', 'gg', 'gh', 'gi', 'gl', 'gm', 'gn', 'gp', 'gq', 'gr', 'gs', 'gt', 'gu', 'gw',
	    'gy', 'hk', 'hm', 'hn', 'hr', 'ht', 'hu', 'id', 'ie', 'il', 'im', 'in', 'io', 'iq', 'ir', 'is', 'it', 'je',
	    'jm', 'jo', 'jp', 'ke', 'kg', 'kh', 'ki', 'km', 'kn', 'kr', 'kw', 'ky', 'kz', 'la', 'lb', 'lc', 'li', 'lk',
	    'lr', 'ls', 'lt', 'lu', 'lv', 'ly', 'ma', 'mc', 'md', 'mg', 'mh', 'mk', 'ml', 'mm', 'mn', 'mo', 'mp', 'mq',
	    'mr', 'ms', 'mt', 'mu', 'mv', 'mw', 'mx', 'my', 'mz', 'na', 'nc', 'ne', 'nf', 'ng', 'ni', 'nl', 'no', 'np',
	    'nr', 'nu', 'nz', 'om', 'pa', 'pe', 'pf', 'pg', 'ph', 'pk', 'pl', 'pn', 'pr', 'ps', 'pt', 'pw', 'py', 'qa',
	    're', 'ro', 'ru', 'rw', 'sa', 'sb', 'sc', 'sd', 'se', 'sg', 'sh', 'si', 'sk', 'sl', 'sm', 'sn', 'sr', 'st',
	    'sv', 'sy', 'sz', 'tc', 'td', 'tf', 'tg', 'th', 'tj', 'tk', 'tl', 'tm', 'tn', 'to', 'tr', 'tt', 'tv', 'tw',
	    'tz', 'ua', 'ug', 'uk', 'us', 'uy', 'uz', 'va', 'vc', 've', 'vg', 'vi', 'vn', 'vu', 'wf', 'ws', 'ye', 'yu',
	    'za', 'zm', 'zw',
	    'eh', 'kp', 'me', 'rs', 'um', 'bv', 'gb', 'pm', 'sj', 'so', 'yt', 'su', 'tp', 'bu', 'cs', 'dd', 'zr'
	);

	/*
	 * Like getCurrent, except with optional $url parameter to use your own url
	 * (defaults to current url), which is how it should have been in the first place
	 */

	static function addParams($params, $url = null)
	{
		if (!isset($url))
			$url = self::getCurrent();

		$new_params = $params;
		if (!is_array($params))
			parse_str($params, $new_params);

		$parts = explode('?', $url, 2);

		if (count($parts) > 1)
		{
			parse_str($parts[1], $existing_params);
			$new_query = array_merge($existing_params, $new_params);
		}
		else
		{
			$new_query = $new_params;
		}

		$new_url = $parts[0] . '?' . http_build_query($new_query);

		return $new_url;
	}

	/*
	 * Remove params from url
	 */
	static function clearParams($url = null)
	{
		if (!isset($url))
			$url = self::getCurrent();

		return reset(explode('?', $url, 2));
	}

	/*
	 * Add url params, removing any existing ones
	 * 
	 */
	static function setParams($params, $url = null)
	{
		if (!isset($url))
			$url = self::getCurrent();

		return self::addParams(self::clearParams($url), $params);
	}

	/*
	 * Set path parts, like /author/[id], accepts same params as addParams
	 * 
	 * not functional yet...
	 */
	static function addParts($parts, $url = null)
	{
		if (!isset($url))
			$url = self::getCurrent();

		if (!is_array($parts))
			parse_str($parts, $new_parts);

		$url_arr = @parse_url($url);

		$current = explode('/', $url_arr['path']);

		foreach ($current as $index => $val)
		{
			if (isset($parts[$val]))
			{
				$current[$index + 1] = $parts[$val];
			}
		}

		$url_arr['path'] = implode('/', $current);

		//return http_build_url($url_arr);
	}

	static function parse($url = null)
	{
		if (!isset($url))
			$url = self::getCurrent();

		$arr = @parse_url($url);
		$obj = new StdClass;

		foreach ($arr as $key => $val)
		{
			$obj->$key = $val;
		}

		return $obj;
	}

	/*
	 * Method: getCurrent
	 *
	 * Parameters:
	 * 	$params - query string or assoc array to be appended to url, array will be
	 * 			  converted to query string
	 *
	 * Returns:
	 * 	current page's url
	 */
	static function getCurrent($params = null, $overwrite = false)
	{
		if (!isset(self::$url))
		{
			$url = 'http';
			if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on")
			{
				$url .= "s";
			}
			$url .= "://";
			if ($_SERVER["SERVER_PORT"] != "80")
			{
				$url .= $_SERVER["HTTP_HOST"] . ":" . $_SERVER["SERVER_PORT"];
			}
			else
			{
				if (isset($_SERVER["HTTP_HOST"]))
					$url .= $_SERVER["HTTP_HOST"];
			}

			self::$url = $url;
		}
		else
		{
			$url = self::$url;
		}

		if (isset($params))
		{
			$new_params = $params;
			if (!is_array($params))
			{
				parse_str($params, $new_params);
			}

			$parts = explode('?', $_SERVER["REQUEST_URI"], 2);

			if (count($parts) > 1)
			{
				if ($overwrite)
				{
					$new_query = $new_params;
				}
				else
				{
					parse_str($parts[1], $existing_params);
					$new_query = array_merge($existing_params, $new_params);
				}
			}
			else
			{
				$new_query = $new_params;
			}

			if ($params == '' && $overwrite)
			{
				$url .= $parts[0];
			}
			else
			{
				$url .= $parts[0] . '?' . http_build_query($new_query);
			}
		}
		else
		{
			$url .= $_SERVER["REQUEST_URI"];
		}

		return $url;
	}

	static function getCurrentSSL()
	{
		return str_replace(array("http://", ":443"), array("https://", ""), self::getCurrent());
	}

	static function getCurrentWithoutSSL()
	{
		return str_replace(array("https://", ":443"), array("http://", ""), self::getCurrent());
	}

	static function getCurrentWithTrailingSlash()
	{
		return rtrim(self::getCurrent(), '/') . '/';
	}

	static function getParams()
	{
		return self::getCurrentArr('params');
	}

	static function getPath()
	{
		return self::getCurrentArr('path');
	}

	static function getPathPart($index)
	{
		$parts = explode('/', self::getPath());
		unset($parts[0]);
		if (isset($parts[$index]))
			return $parts[$index];
		else
			return null;
	}

	static function getPathParts()
	{
		$parts = explode('/', self::getPath());
		unset($parts[0]);
		if (isset($parts))
			return $parts;
		else
			return null;
	}

	static function getPathMap($keys)
	{
		$parts = explode('/', self::getPath());
		$arr = array();
		foreach ($keys as $key)
		{
			$index = array_search($key, $parts);
			if ($index !== false && isset($parts[$index + 1]) && !in_array($parts[$index + 1], $keys))
			{
				$arr[$key] = $parts[$index + 1];
			}
			else
			{
				$arr[$key] = null;
			}
		}

		return $arr;
	}

	static function getCurrentArr($index=null)
	{
		if (!isset(self::$url_parts))
		{
			self::$url_parts = self::parsePath(self::getCurrent());
		}
		if (isset($index))
		{
			if (isset(self::$url_parts[$index]))
			{
				return self::$url_parts[$index];
			}
			else
				return;
		}
		return self::$url_parts;
	}

	static function getEnvironmentRoot($url, $https = false)
	{
		if (!isset($https))
			$https = (isset($_SERVER['HTTPS']));

		$root = ($https ? 'https' : 'http') . "://$url";
		$parts = explode(".", URL::getCurrentArr('host'));
		if (Config::isMyDevBox())
			$root .= '.' . implode(".", array_slice($parts, -4));
		elseif (Config::isStaging())
			$root .= '.' . implode(".", array_slice($parts, -3));

		return $root;
	}

	/*
	 * Method: add
	 *
	 * Adds a url to check against. Url can check against parameters, using
	 * format: '/url?param1=*&param2=val'.
	 *
	 * Root urls can also be checked against using: '/url/*'.
	 *
	 * Parameters:
	 * 	$u - url or array of url => controller pairs
	 *  $c - controller if $u is string
	 *
	 * Examples:
	 * 	URL::add('/', 'HomeController');
	 *  URL::add(array(
	 *  	'/benefits/', 'static/BenefitsController',
	 *  	'/?id', 'article/ArticleController'
	 *  ));
	 */
	static function add($u, $c = null)
	{
		if (!is_array($u))
			$u = array($u => $c);

		foreach ($u as $url => $controller)
		{
			$url = self::parsePath($url);

			if (isset($url['params']))
			{
				self::addParamUrl($url, $controller);
			}
			elseif (isset($url['dynamic']))
			{
				self::addDynamicUrl($url, $controller);
			}
			else
			{
				self::addStaticUrl($url, $controller);
			}
		}
	}

	/*
	 * Method: addFlip
	 *
	 * Extends the add function to allow easier (multiple urls -> controller)
	 * pairs
	 *
	 * Root urls can also be checked against using: '/url/*'.
	 *
	 * Parameters:
	 * 	$c - controller or array of controller => url array pairs
	 *  $u - url array if $c is a string
	 *
	 * Examples:
	 * 	URL::add('HomeController', array(
	 * 		'/',
	 * 		'/index.php'
	 * 	));
	 *  URL::add(array(
	 *  	'BenefitsController' => array(
	 * 			'/benefits/',
	 * 			'/benefits.html'
	 * 		),
	 *  	'article/ArticleController' => array(
	 * 			'?id',
	 * 			'?id*'
	 * 		)
	 * 	));
	 */
	static function addFlip($c, $u = null)
	{
		if (is_array($u))
			$c = array($c => $u);

		foreach ($c as $controller => $url_array)
		{
			if (is_array($url_array))
			{
				foreach ($url_array as $url)
				{
					$array[$url] = $controller;
				}
			}
		}

		if (is_array($array))
			self::add($array);
	}

	/*
	 * Method: alias
	 *
	 * Creates an alternate url that will automatically 301 redirect to primary url.
	 *
	 * Parameters:
	 * 	$primary - primary url to redirect to
	 * 	$aliases - string or array of urls to redirect from
	 *
	 */
	static function alias($primary, $aliases)
	{
		$redirect = "redirect=>$primary";
		foreach ((array) $aliases as $alias)
		{
			$url = self::parsePath($alias);
			if (isset($url['params']))
			{
				self::addParamUrl($url, $redirect);
			}
			elseif (isset($url['dynamic']))
			{
				self::addDynamicUrl($url, $redirect);
			}
			else
			{
				self::addStaticUrl($url, $redirect);
			}
		}
	}

	/*
	 * Method: getUrlFromController
	 */
	static function getUrlFromController($controller)
	{
		return self::$controllers[$controller];
	}

	/*
	 * Method: getControllerFromUrl
	 *
	 * Parameters:
	 * 	$url - url associated with the controller, defaults to current page url
	 */
	static function getControllerFromUrl($url = null)
	{
		if (!isset($url))
		{
			$url = self::getCurrent();
		}

		$url = self::parsePath($url);

		$controller = self::findDynamicPath($url);

		if (!$controller)
		{
			$controller = self::findParamPath($url);
		}

		if (!$controller)
		{
			$controller = self::findStaticPath($url);
		}

		if (!$controller)
		{
			$controller = '/controller/Error404Controller';

			if (file_exists(Loader::getPath('controller', 'Error404Controller')))
			{
				$controller = 'Error404Controller';
			}
		}

		if (substr($controller, 0, 10) == 'redirect=>')
		{
			$controller = self::parseRedirect($url, $controller);
		}

		return $controller;
	}

	static function logRules()
	{
		Debugger::log(self::$rules);
	}

	static function getControllerURL($controller=null)
	{
		if (isset($controller) && strlen($controller))
		{
			$controller .= "Controller.class.inc.php";
		}
		else
		{
			$backtrace = debug_backtrace();
			$file_location = $backtrace[0]['file'];
			$file_breakdown = explode("/", $file_location);
			$controller = $file_breakdown[count($file_breakdown) - 1];
		}

		if (substr_count($controller, "Controller"))
			$controller = str_replace(".class.inc.php", "", $controller);

		foreach (self::$rules as $type => $rule_set)
			foreach ($rule_set as $link => $name)
				if (substr_count($name, $controller))
					$matches[] = $link;

		if (count($matches) > 0)
		{
			$match = array_pop($matches);
			return $match;
		}
		else
		{
			Debugger::log("Unable to find requested Controller", $controller);
		}
	}

	public static function getLastPathPart()
	{
		$parts = self::getPathParts();

		foreach ($parts as $part)
			if (strlen($part) <> 0)
				$new[] = $part;

		return array_pop($new);
	}

	private function addDynamicUrl($url, $controller)
	{
		// special check for '/*'
		if (empty($url['dynamic']))
		{
			$url['dynamic'] = '/*';
		}
		self::$rules['dynamic'][$url['dynamic']] = $controller;
	}

	private function addParamUrl($url, $controller)
	{
		$store = & self::$rules['param'][$url['path']][$controller];
		foreach ($url['params'] as $name => $value)
		{
			$store[$name] = $value;
		}
	}

	private function addStaticUrl($url, $controller)
	{
		self::$rules['static'][$url['path']] = $controller;
	}

	private function parsePath($path)
	{
		$parts = @parse_url($path);

		if (isset($parts['path']))
		{
			if (substr($parts['path'], -2) == '/*')
			{
				$parts['dynamic'] = substr($parts['path'], 0, -2);
			}
		}

		if (isset($parts['query']))
		{
			parse_str($parts['query'], $params);
			ksort($params);
			$parts['params'] = $params;
		}

		return $parts;
	}

	private function findDynamicPath($url)
	{
		if (!isset($url['path']))
			return false;
		$parts = explode('/', $url['path']);
		array_shift($parts);
		$string = '';
		$controller = false;
		// special check for '/*'
		if (isset(self::$rules['dynamic']['/*']) && !empty($parts[0]) && !self::findStaticPath($url))
		{
			$controller = self::$rules['dynamic']['/*'];
		}
		// check others
		foreach ($parts as $index => $part)
		{
			$string .= '/' . $part;
			if (isset(self::$rules['dynamic'][$string]) && !empty($parts[$index + 1]))
			{
				$controller = self::$rules['dynamic'][$string];
			}
		}

		return $controller;
	}

	private function findParamPath($url)
	{
		$c = false;
		$matches = array();
		if (isset($url['params']) && isset(self::$rules['param'][$url['path']]))
		{
			foreach (self::$rules['param'][$url['path']] as $controller => $conditions)
			{
				$pass = true;
				foreach ($conditions as $name => $value)
				{
					if (!isset($url['params'][$name]))
					{
						$pass = false;
					}
					else
					{
						if ($value != '*' && $value != $url['params'][$name])
						{
							$pass = false;
						}
					}
				}
				if ($pass)
				{
					$matches[count($conditions)] = $controller;
					//break;
				}
			}
		}

		if (count($matches) === 0)
		{
			return false;
		}
		else
		{
			ksort($matches);
			return end($matches);
		}
	}

	private function findStaticPath($url)
	{
		if (isset($url['path']) && isset(self::$rules['static'][$url['path']]))
		{
			return self::$rules['static'][$url['path']];
		}
		else
			return false;
	}

	private function parseRedirect($url, $redirect)
	{
		if (isset(self::$rules['dynamic']))
			$dynamic = array_search($redirect, self::$rules['dynamic']);
		$redirect = substr($redirect, 10);
		$redirect = self::parsePath($redirect);

		$to = $redirect['path'];

		if (isset($redirect['scheme']) && isset($redirect['host']))
			$to = "{$redirect['scheme']}://{$redirect['host']}{$to}";

		//fix dynamic url
		if (isset($redirect['dynamic']))
		{
			$to = str_replace($dynamic, $redirect['dynamic'], $to);
		}

		$to_query = array();

		// replace any param tokens
		if (isset($redirect['params']))
		{
			foreach ($redirect['params'] as $name => $val)
			{
				if (preg_match('/^\[(.*)\]$/', $val, $match) === 1)
				{
					$to_query[$name] = $url['params'][$match[1]];
					unset($url['params'][$match[1]]);
				}
			}
		}

		// replace any other tokens
		if (isset($url['params']))
		{
			foreach ($url['params'] as $name => $val)
			{
				$to = str_replace("[$name]", $val, $to, $count);
				if ($count == 1)
				{
					unset($url['params'][$name]);
				}
			}
		}

		if (isset($url['params']))
		{
			$to_query = array_merge($to_query, $url['params']);
		}

		if (count($to_query) > 0)
		{
			$to .= '?' . http_build_query($to_query);
		}

		Debugger::log("Redirecting to: $to");

		Config::set('Redirect', $to);
		return '/controller/RedirectController';
	}

	public function getDomain($url)
	{
		//make sure url is cleaned before parsing
		$url = str_replace(" ", "", $url);
		$url = ltrim($url, "/");
		if (substr($url, 0, 5) != "http:")
		{
			$url = "http://" . $url;
		}

		$url_parts = $this->parsePath($url);
		$host_breakdown = $this->parseHost($url_parts['host']);
		return $host_breakdown['domain'] . "." . implode(".", $host_breakdown['tld']);
	}

	private function parseHost($host)
	{
		$host_parts = explode('.', $host);
		$host_parts = array_reverse($host_parts);

		if (in_array($host_parts[0], $this->common_tld) && in_array($host_parts[1], $this->generic_tld) && $host_parts[2] != 'www')
		{
			$host_breakdown['tld'] = array($host_parts[1], $host_parts[0]);
			$host_breakdown['domain'] = $host_parts[2];
			$host_breakdown['subdomain'] = implode(".", array_slice($host_parts, 3));
		}
		else
		{
			$host_breakdown['tld'] = array($host_parts[0]);
			$host_breakdown['domain'] = $host_parts[1];
			$host_breakdown['subdomain'] = implode(".", array_slice($host_parts, 2));
		}

		return $host_breakdown;
	}

	public static function getParsedObject($url)
	{
		$object = new stdClass;
		foreach (@parse_url($url) as $key => $val)
		{
			$object->{$key} = $val;
		}
		return $object;
	}

	public static function parseCurrentURL()
	{
		return (object) @parse_url(URL::getCurrent());
	}

}
?>