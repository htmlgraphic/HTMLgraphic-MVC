<?php
include_once(dirname(__FILE__) . '/Config.class.inc.php');

class Loader
{

	private static $extensions = array(
	    'utility' => '.class.inc.php',
	    'model' => '.class.inc.php',
	    'mutator' => '.class.inc.php',
	    'view' => '.tpl.php',
	    'controller' => '.class.inc.php',
	    'doc' => '.class.inc.php',
	    'vendor' => '.class.inc.php',
	    'cron' => '.class.inc.php',
	    'module' => '.class.inc.php'
	);

	/*
	 * Load common classes here.
	 */
	static function loadCommon()
	{
		self::load('utility', 'Debugger');
	}

	/*
	 * Loader::load will include_once (by default) files in the following dirs:
	 * utility, model, view, controller, vendor, and root.
	 * View and controller dirs are automatically relative to the site.
	 * 
	 * Files passed in can be array or string. Extentions will automatically be appended
	 * if left off.
	 * 
	 * Note: if including a preceding '/' in the path, the path is relative to root
	 * instead of the specific type (utility, conroller, etc.)
	 * 
	 * Note: Passing in only a file is the same as using a 'root' type
	 * 
	 * Examples:
	 * 
	 * // include_once(Config::get('DocRoot').'/controller/Controller.class.inc.php');
	 * Loader::load('root', 'controller/Controller');
	 * 
	 * // include_once(Config::get('UtilityRoot').'URL.class.inc.php');
	 * // include_once(Config::get('UtilityRoot').'Debugger.class.inc.php');
	 * Loader::load('utility', array('url/URL', 'Debugger'));
	 * 
	 * // require_once(Config::get('VendorRoot').'/pqp/classes/PhpQuickProfiler.php');
	 * Loader::load('vendor', 'pqp/classes/PhpQuickProfiler.php', 'require_once');
	 * 
	 * // include_once(Config::get('DocRoot').'/controller/RedirectController');
	 * // new RedirectController('http://google.com/');
	 * Loader::loadNew('controller', '/controller/RedirectController', 'http://google.com/');
	 * 
	 * Loader::load('controller/model/...');
	 * 
	 */
	static function load($type, $files = null, $fn = 'include_once')
	{
		if (!isset($files))
		{
			$files = $type;
			$type = 'doc';
		}

		foreach ((array) $files as $file)
		{
			$file = self::getPath($type, $file);

			if (file_exists($file))
			{
				switch ($fn)
				{
					case 'include':
						include($file);
						break;
					case 'include_once':
						include_once($file);
						break;
					case 'require':
						require($file);
						break;
					case 'require_once':
						require_once($file);
						break;
				}
			}
			else
			{
				$backtrace = debug_backtrace();
				$backtrace = $backtrace[0];
				$error = "Loader error. Failed include on {$file}. Called in {$backtrace['file']} on line {$backtrace['line']}.";
				error_log($error);
				Debugger::error(new Exception($error));
			}
		}
	}

	/*
	 * Loader::loadNew will load the file and return a new instance of the class.
	 * 
	 * To pass arguments to the constructor, pass in the optional $arg parameter.
	 * If passing multiple arguments, store them in an array.
	 * 
	 * If the filename is different than the class that you want to instantiate,
	 * pass in the optional $class parameter with the correct name
	 * 
	 */
	static function loadNew($type, $file, $args = array(), $class = null)
	{
		self::load($type, $file);

		$obj = null;

		if (!isset($class))
		{
			$class = substr(strrchr($file, '/'), 1);
			if ($class == '')
			{
				$class = $file;
			}
		}

		if (class_exists($class))
		{
			$reflectionObj = new ReflectionClass($class);
			if ($reflectionObj->hasMethod('__construct'))
			{
				if (is_object($args))
					$args = array($args);
				$obj = $reflectionObj->newInstanceArgs((array) $args);
			}
			else
			{
				//error_log("create new instance");
				$obj = $reflectionObj->newInstance();
			}
		}
		else
		{
			Debugger::error(new Exception("Class '$class' not found!"));
		}

		return $obj;
	}

	/*
	 * Loader::getPath will get the full path of a file if you need to check for
	 * its existence before you load it.
	 */
	static function getPath($type, $file)
	{
		if ($type == 'media')
			return realpath(Config::get('DocRoot') . '/../media-repository/' . $file);
		if ($type == 'virtual')
			return realpath(Config::get('DocRoot') . '/../virtual/' . $file);

		if ($type == 'root')
			$type = 'doc';

		$root = Config::get(ucfirst($type) . 'Root');

		if (strpos($file, '.') === false && isset(self::$extensions[$type]))
			$file .= self::$extensions[$type];

		if (substr($file, 0, 1) == '/')
			$file = Config::get('DocRoot') . $file;
		else
			$file = $root . '/' . $file;

		return $file;
	}

	/*
	 * Loader::loadAssets should be used in template files in <head> to print
	 * css, js, and rss files.
	 * 
	 * Example:
	 * 
	 * Loader::loadAssets(array(
	 * 		'js' => array(
	 * 			'/file.js',
	 * 			'var inline_js = true;',
	 * 			array(
	 * 				'path' => 'conditional.js'
	 * 				'conditional' => 'lt IE 8'
	 * 			)
	 * 		),
	 * 		'css' => array(
	 * 			'style.css',
	 * 			array(
	 * 				'inline' => '#el {color:red}',
	 * 				'media' => 'print'
	 * 			)
	 * 		),
	 * 		'rss' => array(
	 * 			'title' => 'path.xml'
	 * 		)
	 * ));
	 * 
	 */
	static function loadAssets($assets)
	{
		if (isset($assets['combine']) && $assets['combine'])
		{
			$url = (isset($assets['combined_url'])) ? $assets['combined_url'] : '/min/file/';
			if (isset($assets['css']))
				$assets['css'] = self::combine_type($assets['css'], 'css', $url);

			if (isset($assets['js']))
				$assets['js'] = self::combine_type($assets['js'], 'js', $url);
		}
		if (isset($assets['css']))
			array_walk($assets['css'], array('self', 'loadCss'));

		if (isset($assets['js']))
			array_walk($assets['js'], array('self', 'loadJs'));

		if (isset($assets['rss']))
			array_walk($assets['rss'], array('self', 'loadRss'));
	}

	static private function combine_type($files, $extension, $url)
	{
		$combined = array();
		$files_copy = $files;

		foreach ($files as $index => $file)
		{
			$filename = null;
			if (!is_array($file))
			{
				if (self::fileHasExtension($file, $extension))
				{
					$filename = $file;
				}
			}
			else
			{
				if (count($file) == 1 && isset($file['path']))
				{
					$filename = $file['path'];
				}
			}

			if (isset($filename) && substr($filename, 0, 7) != 'http://')
			{
				$clean = ltrim($file, '/');
				$clean = explode('?', $clean);
				$combined[] = $clean[0];
				unset($files[$index], $filename);
			}
		}

		if (!empty($combined))
		{
			Loader::load('module', 'file/CombineAssetsModule');

			$root = Config::get('VirtualRoot');
			foreach ($combined as &$file)
				$file = "$root/$file";

			$combiner = new CombineAssetsModule($combined, $extension);
			$combined_file = $combiner->activate();

			if (!$combined_file)
				$files = $files_copy;
			else
				array_unshift($files, $url . $combined_file);
		}

		return $files;
	}

	static private function loadCss($css)
	{
		if (!is_array($css))
		{

			if (self::fileHasExtension($css, 'css'))
			{
				$css = array('path' => $css);
			}
			else
			{
				$css = array('inline' => $css);
			}
		}

		if (!isset($css['media']))
		{
			$css['media'] = 'screen';
		}

		$output = '';

		if (isset($css['conditional']))
		{
			$output .= "<!--[if $css[conditional]]>\n";
		}

		if (isset($css['inline']))
		{
			$output .= "<style type=\"text/css\" media=\"$css[media]\">\n$css[inline]\n</style>\n";
		}
		else
		{
			$output .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"$css[path]\" media=\"$css[media]\">\n";
		}

		if (isset($css['conditional']))
		{
			$output .= "<![endif]-->\n";
		}

		echo $output;
	}

	static private function loadJs($js)
	{
		if (!is_array($js))
		{
			if (self::fileHasExtension($js, 'js'))
			{
				$js = array('path' => $js);
			}
			else
			{
				$js = array('inline' => $js);
			}
		}

		$output = '';

		if (isset($js['conditional']))
		{
			$output .= "<!--[if $js[conditional]]>\n";
		}

		if (isset($js['inline']))
		{
			$output .= "<script type=\"text/javascript\">\n$js[inline]\n</script>\n";
		}
		else
		{
			$output .= "<script type=\"text/javascript\" src=\"$js[path]\"></script>\n";
		}

		if (isset($js['conditional']))
		{
			$output .= "<![endif]-->\n";
		}

		echo $output;
	}

	static private function loadRss($rss)
	{
		$title = key($rss);
		$path = $rss[$title];
		echo "<link rel=\"alternate\" title=\"$title\" href=\"$path\" type=\"application/rss+xml\">";
	}

	static private function fileHasExtension($file, $ext)
	{
		$index = strrpos($file, '?');
		if ($index !== false)
		{
			if (substr($file, $index - strlen($ext) - 1, strlen($ext) + 1) == ".$ext")
				return true;
		}

		if (substr($file, -(strlen($ext) + 1)) == ".$ext")
			return true;

		return false;
	}

	/*
	 * $actions = array(
	 * 		'_setDomainName' => 'example.com',
	 * 		'_setCustomVar' => array(1, 'Section', 'Life & Style', 3),
	 * 		'_trackPageview'
	 * 		// other ga actions
	 * )
	 * 
	 */
	static function loadGoogleAnalytics($actions)
	{
		$script = "<script type=\"text/javascript\">

var _gaq = _gaq || [];
_gaq.push(['_setAccount', '%__UA__%']);
_gaq.push(%__ACTIONS__%);

(function() {
	var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
	ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
	var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
})();

</script>";

		if (isset($actions['_setAccount']))
		{
			$script = str_replace('%__UA__%', $actions['_setAccount'], $script);
			unset($actions['_setAccount']);

			$ga_options = array();
			foreach ((array) $actions as $index => $action)
			{
				if (is_int($index))
				{
					if (is_array($action))
						$ga_options[] = json_encode($action);
					else
						$ga_options[] = "['$action']";
				}
				else
				{
					if (is_int($action))
						$ga_options[] = "['$index'," . ((string) $action) . "]";
					elseif (is_bool($action))
					{
						$ga_options[] = "['$index'," . (($action === true) ? 'true' : 'false') . "]";
					}
					elseif (is_array($action))
					{
						$ga_options[] = "['$index'," . join(',', $action) . "]";
					}
					else
						$ga_options[] = "['$index','$action']";
				}
			}

			echo str_replace('%__ACTIONS__%', join(',', $ga_options), $script);
		}
		else
		{
			Debugger::error(new Exception("Didn't load google analytics. No UA set."));
		}
	}

}
Loader::loadCommon();
?>
