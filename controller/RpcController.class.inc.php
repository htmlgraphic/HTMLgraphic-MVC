<?php

class RpcController extends Controller
{
	function __construct()
	{
		parent::__construct();
		if (!Config::get('RPC')) {
			Config::set('RPC', array(
					  'class' => 2,
					  'method' => 3,
					  'action' => 4,
					  'path' => 'procedure'
				   ));
		}
	}

	function activate()
	{
		$setup = Config::get('RPC');
		$class = URL::getPathPart($setup['class']);
		$method = URL::getPathPart($setup['method']);
		list($action, $type) = explode('.', URL::getPathPart($setup['action']), 2);

		$path = $setup["path"] . "/" . $class . "/" . ucwords($method) . ucwords($action) . "Controller";
		if (file_exists(Loader::getPath("controller", $path))) {
			Debugger::log("CONTROLLER: <span style=\"color: #990000\">" . ucwords($method) . ucwords($action) . "Controller</span>");

			$controller = Loader::loadNew("controller", $path);

			$controller->activate();
			if (is_callable(array($controller, $type)))
				echo $controller->$type();
			return;
		}


		$controller_class = ucwords($class) . ucwords($method) . ucwords($action) . "Controller";

		Debugger::log("CONTROLLER: <span style=\"color: #990000\">$controller_class</span>");

		if (file_exists(Loader::getPath("controller", "$setup[path]/$controller_class"))) {
			$controller = Loader::loadNew("controller", "$setup[path]/$controller_class");
			$controller->activate();
		}
		else {
			Loader::load("utility", "Server");
			$ip = Server::getIP();
			$self = Server::getSelf();
			Debugger::log("Possible RPC Injection: RPC call to non-existent controller at path {$setup["path"]}/$controller_class $ip $self");
			error_log("Possible RPC Injection: RPC call to non-existent controller at path $setup[path]/$controller_class $ip $self");
		}
	}

}
?>