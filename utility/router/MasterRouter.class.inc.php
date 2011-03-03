<?php

class MasterRouter
{

	private $router;

	function __construct($find_router = true)
	{
		Loader::load('utility', 'router/SiteRouter');
		Loader::load('controller', '/controller/Controller');

		if ($find_router)
			$this->findRouter();
	}

	function route()
	{
		if (isset($this->router))
		{
			$router = get_class($this->router);
			Config::set('Router', $router);
			Debugger::log("ROUTER: <span style=\"color:#72b618;\">$router</span>");
			$this->router->route();
		}
		else
		{
			Debugger::error(new Exception('Cannot route! No router set.'));
		}
	}

	function loadRouter($router, $args = array(), $class = null)
	{
		$this->router = Loader::loadNew('utility', "router/sites/$router", $args, $class);
	}

	private function findRouter()
	{
		$host = $_SERVER["HTTP_HOST"];
		$parts = explode('.', $host);

		if (Config::isMyDevBox())
		{
			if ($parts[count($parts) - 4] == 'dev')
			{
				$parts = array_slice($parts, 0, count($parts) - 4);
			}
			else
			{
				$parts = array_slice($parts, 0, count($parts) - 3);
			}
		}

		// dbcron.[xxx.]xxx.xxx
		if ($parts[0] == 'dbcron')
		{
			array_shift($parts);
		}

		// dev.xxx.xxx
		if ($parts[0] == 'dev')
		{
			array_shift($parts);
		}

		// www.xxx.xxx
		if ($parts[0] == 'www')
		{
			array_shift($parts);
		}

		// [xxx.]xxx.xxx
		$name = ucfirst($parts[0]);

		// xxx.xxx.xxx
		if (count($parts) > 2)
		{
			$name .= ucfirst($parts[1]);
		}

		$name .= 'Router';

		//strip non-alphanumeric characters
		$name = preg_replace("/[^a-zA-Z0-9\s]/", "", $name);

		$this->loadRouter($name);

		if (!isset($this->router))
		{
			Debugger::error(new Exception("Failed to find site router for $_SERVER[HTTP_HOST]."));
		}
	}

}
?>