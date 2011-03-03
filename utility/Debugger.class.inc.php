<?php
/*
 * Methods for debugging. Pretty much just wraps PQP and ErrorHandler
 */
Loader::load('vendor', array(
		  'pqp/classes/PhpQuickProfiler.php',
		  'pqp/classes/Console.php'
	   ));

Loader::load('utility', "ErrorHandler");

class Debugger
{

	private static $profiler, $time = array();
	public static $queries = array();
	private static $console = false;

	/*
	 * initialize the debugger
	 */
	public static function init()
	{
		$developer = false;

		if (isset($_SERVER['REMOTE_ADDR']) && (substr($_SERVER['REMOTE_ADDR'], 0, 5) == "10." || substr($_SERVER['REMOTE_ADDR'], 0, 5) == "192.168."))
		{
			if (!isset($_SESSION))
			{
				Loader::load("utility", "session/Session");
				Session::instance();
			}
			if ((isset($_COOKIE['hg_show_debugger']) && $_COOKIE['hg_show_debugger']) || (isset($_SESSION['hg_show_debugger']) && $_SESSION['hg_show_debugger']))
			{
				$developer = true;
			}
		}

		// don't initialize if the page is live, if it's from command line, or if it's phpmyadmin
		// do init it for developers, though
		if ((!Config::isLive() && isset($_SERVER['HTTP_HOST'])) && $developer)
		{
			ini_set('display_errors', 1);
			error_reporting(E_ALL);
			set_error_handler(array("Debugger", "error_handling_dev"), E_ALL);
			register_shutdown_function(array('ErrorHandler', 'shutdownCheck'));
			register_shutdown_function(array('Debugger', 'display'));
			self::$profiler = new PhpQuickProfiler(Config::get('StartTime'));
		}
		elseif (Config::get('MaxRunTime'))
		{
			register_shutdown_function(array('Debugger', 'checkRunTime'));
		}
		elseif (!isset($_SERVER['HTTP_HOST']) && !Config::isLive())
		{
			self::$console = true;
		}
		else
		{
			set_error_handler(array("Debugger", "error_handling_live"), E_USER_ERROR | E_USER_WARNING | E_USER_NOTICE);
			register_shutdown_function(array('ErrorHandler', 'shutdownCheck'));
		}
	}

	public static function growl($message)
	{
		if (Config::isMyDevBox())
		{
			Loader::load("utility", "growl/GrowlNotify");
			GrowlNotify::instance($_SERVER['REMOTE_ADDR'])->send("Debugger Message", $message);
		}
	}

	public static function error_handling_dev($err_no, $err_str, $err_file, $err_line, $err_context)
	{
		$exception = new ErrorException($err_str, 0, $err_no, $err_file, $err_line);
		Debugger::error($exception);
		$ErrorHandler = new ErrorHandler($exception);
		$ErrorHandler->ProcessError();
		return;
	}

	public static function error_handling_live($err_no, $err_str, $err_file, $err_line, $err_context)
	{
		$exception = new ErrorException($err_str, 0, $err_no, $err_file, $err_line);
		$ErrorHandler = new ErrorHandler($exception);
		$ErrorHandler->ProcessError();
		return;
	}

	/*
	 * Display the output. If you ever need to hide it, use Config::set('HideDebugger', true);
	 */
	public static function display()
	{
		$ajax = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest');
		$pentest = (substr_count($_SERVER['HTTP_USER_AGENT'], "Netsparker")) ? true : false;
		// don't show output if Config::get('HideDebugger') == true or if it's an ajax request
		if (!Config::get('HideDebugger') && !$ajax && !$pentest)
		{
			if (isset(self::$profiler))
			{
				self::$profiler->display();
			}
		}
	}

	/*
	 * if Config::get('MaxRunTime') is set will check runtime and send email if over
	 */
	public static function checkRunTime()
	{
		$max = Config::get('MaxRunTime');
		$start = Config::get('StartTime');
		$end = microtime(true);

		$total = $end - $start;

		if ($total > $max)
		{
			Loader::load('utility', 'email/Email');
			$email = new Email();
			$email->setFromAddress('Excessive Page Load', 'tom@htmlgraphic.com');
			$email->addToAddress('Debugging', 'tom@htmlgraphic.com');

			$page = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

			$email->setBody("$page took $total second(s) to load, exceeding the set $max second(s) threshold.");
			$email->setSubject($page);

			$email->send();
		}
	}

	/*
	 * Debugger::log($data) will display $data in the console, can accept any amount
	 * of parameters
	 */
	public static function log($data)
	{
		if (isset(self::$profiler) || self::$console)
		{
			if (func_num_args() > 1)
			{
				$data = '';
				foreach (func_get_args () as $arg)
				{
					if (!is_string($arg))
						$data .= ' ' . print_r($arg, true);
					else
						$data .= ' ' . $arg;
				}
				$data = trim($data);
			}

			if (isset(self::$profiler) && !self::is_ajax())
				Console::log($data);
			if (isset(self::$profiler) && self::is_ajax() && class_exists('MessageLogger'))
			{
				MessageLogger::logInternal($data, 'log');
			}
			else if (self::$console)
				error_log("Debugger: " . print_r($data, true));
		}
	}

	/*
	 * Debugger::consoleLog($data) specifically log to console if not on live site, used when Debugger::log will not be sufficient
	 * because it will disappear before the error/log is seen (for instance redirects)
	 */
	public static function consoleLog($data)
	{
		if (!Config::isLive())
			error_log("Debugger::consoleLog: " . self::format_data($data));
	}

	/*
	 * Debugger::error($exception) takes a new Exception and displays it in the console
	 */
	public static function error($exception)
	{
		if (isset(self::$profiler))
		{
			Console::logError($exception);
		}
	}

	private static function format_data($data)
	{
		if (func_num_args() > 1)
		{
			$data = '';
			foreach (func_get_args () as $arg)
			{
				if (!is_string($arg))
					$data .= ' ' . print_r($arg, true);
				else
					$data .= ' ' . $arg;
			}
			$data = trim($data);
		}
		return $data;
	}

	/*
	 * Debugger::speed($name) will log how long it takes to get to that point 
	 */
	public static function speed($name)
	{
		if (isset(self::$profiler))
		{
			Console::logSpeed($name);
		}
	}

	/*
	 * Debugger::time($name) will log how long it take to get to the n+1 times it is called again
	 */
	public static function time($name)
	{
		if (isset(self::$profiler))
		{
			if (isset(self::$time[$name]))
			{
				$logItem = array(
				    "data" => PhpQuickProfiler::getMicroTime(),
				    "type" => 'speed',
				    "name" => $name,
				    "time" => self::$time[$name]
				);
				Console::addToConsoleAndIncrement('speedCount', $logItem);
			}
			else
			{
				self::$time[$name] = PhpQuickProfiler::getMicroTime();
			}
		}
	}

	/*
	 * Debugger::memory($object, $name) will display the size of a php object/variable
	 */
	public static function memory($object = null, $name = 'PHP')
	{
		if (isset(self::$profiler))
		{
			Console::logMemory($object, $name);
		}
	}

	/*
	 * Debugger::query($sql, $time) will log a query and the time it takes to the console 
	 */
	public static function query($sql, $time, $database = 'Unknown')
	{
		if (isset(self::$profiler))
		{
			if (!self::is_ajax())
			{
				$time *= 1000;
				self::$queries[] = array('sql' => str_replace(array('/*', '*/'), array('<span style="color:#aaa;font-style:italic;float:right">', '</span>'), $sql), 'time' => $time, 'database' => $database);
			}
			elseif (class_exists('MessageLogger'))
			{
				MessageLogger::logInternal(str_replace(array('/*', '*/'), array('<span style="color:#aaa;font-style:italic;float:right">', '</span>'), $sql), 'query');
			}
		}
		//else
		//error_log($sql);
	}

	private static function is_ajax()
	{
		return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest');
	}

}
Debugger::init();
?>