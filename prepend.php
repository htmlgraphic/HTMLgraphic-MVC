<?php
function detect_fatal_error()
{
	$last_error = error_get_last();
	if ($last_error && ($last_error['type'] == E_ERROR || $last_error['type'] == E_PARSE) && class_exists('Loader'))
	{
		try
		{
			Loader::load('utility', 'Template');
			if (file_exists(Loader::getPath('view', 'FatalError')))
			{
				Debugger::log("Type {$last_error["type"]}: {$last_error["message"]} in {$last_error["file"]} on line {$last_error["line"]}");
				ob_clean();
				header('HTTP/1.1 503 Service Temporarily Unavailable');
				header('Status: 503 Service Temporarily Unavailable');
				Template::insert('FatalError');
				error_log("[Error Caught] {$last_error['message']} in {$last_error['file']} on line {$last_error['line']}");
				ob_end_flush();
			}
		} catch (Exception $e)
		{
			//echo "We've encountered an error. Please go back and try again.";
		}
	}
}

function track_excessive_memory()
{
	if (memory_get_peak_usage() > 943718400)
	{
		$host = (isset($_SERVER['HTTP_HOST'])) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
		$file = (isset($_SERVER['REQUEST_URI'])) ? $_SERVER['REQUEST_URI'] : $_SERVER['SCRIPT_FILENAME'];
		error_log("Excessive Memory Use: " . number_format(memory_get_peak_usage()) . " on $host - $file");
	}
}

register_shutdown_function('track_excessive_memory');
register_shutdown_function('detect_fatal_error');

if (!get_cfg_var("development_mode"))
{
	include_once(dirname(__FILE__) . "/session_handler.php");
}

include_once(dirname(__FILE__) . "/utility/Loader.class.inc.php");
?>