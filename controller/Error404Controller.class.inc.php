<?php

class Error404Controller extends Controller
{
	function activate()
	{
		header('HTTP/1.0 404 Not Found');
		echo '<h1>Error 404</h1><p>Page not found.</p>';
		exit();
	}

}
?>