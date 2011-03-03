<?php

class RedirectController extends Controller
{

	private $url, $type;

	function __construct()
	{
		parent::__construct();
		$this->url = Config::get('Redirect');
		$this->type = Config::get('RedirectType');

		// **to disable redirects "touch disable-redirect" in the root of the your trunk directory
		// **to enable redirects "unlink disable-redirect" in the root of the your trunk directory
		// this only works on devboxes for now, and is a ghetto hack until it can be added as a
		// preference that can be switched on and off from an interface.

		$this->bypass = Config::get('DocRoot') . "/../disable-redirect";

		if (!isset($this->url)) {
			Debugger::error(new Exception("Can't redirect! Config::get('Redirect') contains no url."));
		}

		if (!isset($this->type)) {
			$this->type = '301';
		}
	}

	function activate()
	{
		if (!Config::isLive() && file_exists($this->bypass)) {
			Debugger::log("<span style='color: #939393'>Follow {$this->type} Redirect to:</span> <br><a href='$this->url' style='color: white;'>$this->url</a> ");
			exit;
		}
		else {
			header("Location: {$this->url}", true, $this->type);
			exit;
		}
		return;
	}

}
?>