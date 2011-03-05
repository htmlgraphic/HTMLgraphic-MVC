<?php
function get_sessionconn()
{
	include (dirname(__FILE__) . "/cfgsession.php");
	$sessionconn = mysqli_connect("$dbhost", "$dbuser", "$dbpass", "$db");
	return $sessionconn;
}

function sopen($s, $n)
{
	if (get_sessionconn ())
	{
		return true;
	}
	else
	{
		return false;
	}
}

function sread($id)
{
	$sessionconn = get_sessionconn();

	if (!$sessionconn)
	{
		return false;
	}

	$sql = "SELECT 1,`value` FROM `sessions` WHERE `sessionid` = '$id'";
	$result = $sessionconn->query($sql);

	if (mysqli_error($sessionconn) || !$result || !mysqli_num_rows($result))
	{
		$save_path = ini_get("session.save_path");
		if (file_exists($save_path . "/sess_$id"))
		{

			//check if file should have expired already
			$session_duration = ini_get('session.gc_maxlifetime'); //how long a session is good for, in seconds
			$use_file_for_session = true;
			if (filemtime($save_path . "/sess_$id") < time() - $session_duration)
			{
				$use_file_for_session = false;
			}


			//allow fail-over without logging out
			if (file_exists("/tmp/session_use"))
			{
				$use = file_get_contents("/tmp/session_use");
			}
			else
			{
				$use = "";
			}

			try
			{
				if (mysqli_error($sessionconn))
					$message .= ' error, ';
				if (!$result)
					$message .= ' no result, ';
				if ($result)
					$message .= ' result is set ';
				if (!mysqli_num_rows($result))
					$message .= ' no rows, ';
				$to_write = " unable to fetch result ";
				if ($result)
				{
					$data = $result->fetch_assoc();
					$to_write = "print_r: " . print_r($data, true);
				}
			} catch (Exception $e)
			{

			}
			$message .= $to_write;

			if ($use_file_for_session)
			{
				file_put_contents("/tmp/session_use", $use . "\n" . time() . " using file for " . $id . " error: '" . mysqli_error($sessionconn) . "' sql: '" . $sql . "' message: " . $message);
			}
			else
			{
				file_put_contents("/tmp/session_use", $use . "\n" . time() . " *NOT* using file for " . $id . " error: '" . mysqli_error($sessionconn) . "' sql: '" . $sql . "' message: " . $message);
			}
			return file_get_contents($save_path . "/sess_$id");
		}
		return false;
	}
	$data = $result->fetch_assoc();
	return stripslashes($data['value']);
}

function swrite($id, $data)
{
	$sessionconn = get_sessionconn();
	if (!$sessionconn)
		return false;
	$sql = "REPLACE INTO `sessions` (`sessionid`,`atime`,`value`) VALUES ('$id',NOW(),'" . addslashes($data) . "')";
	$result = $sessionconn->query($sql);
	$save_path = ini_get("session.save_path");
	file_put_contents($save_path . "/sess_$id", $data);
	return true;
}

function sclose()
{
	return true;
}

function sdestroy($id)
{ // do not modify function parameters
	$sessionconn = get_sessionconn();

	if (!$sessionconn)
	{
		return false;
	}

	$sql = "DELETE FROM `sessions` WHERE `sessionid` = '$id' LIMIT 1";
	$result = $sessionconn->query($sql);
	$save_path = ini_get("session.save_path");
	$filename = "$save_path/sess_" . $id;
	@unlink($filename);
	file_put_contents("/tmp/session_destroy", $use . "\n" . time() . " destroying session " . $id);

	if (mysqli_error($sessionconn) || !$result)
	{
		return false;
	}

	return true;
}

function sgc($expire)
{
	$sessionconn = get_sessionconn();
	if (!$sessionconn)
		return false;
	$session_duration = ini_get('session.gc_maxlifetime'); //how long a session is good for, in seconds
	$sql = "DELETE FROM `sessions` WHERE `atime` < DATE_ADD(NOW(), INTERVAL -$session_duration SECOND)"; //delete from the table where the access time is less than (NOW() minus session_duration seconds)
	file_put_contents("/tmp/sql", $sql);
	$result = $sessionconn->query($sql);
	//if (mysqli_error($sessionconn) || !$result) return false;
	$save_path = ini_get("session.save_path");
	$output = time() . " \n";

	foreach (glob("$save_path/sess_*") as $filename)
	{
		//$output .= $filename.":  ".filemtime($filename)." + $session_duration < ".time();
		if (@filemtime($filename) < time() - $session_duration)
		{
			@unlink($filename);
			//$output .= " DELETE \n";
		}
		else
		{
			//$output .= " keep \n";
		}
	}

	file_put_contents("/tmp/session_gc", $output);

	return $result;
}

//session_set_save_handler ("open", "close", "read", "write", "destroy", "gc");
include_once('/var/www/script-repository/session_handler_include.php');
?>