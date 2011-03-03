<?php
Loader::load('utility', array(
		  'response/MessageLogger',
		  'Request'
	   ));

abstract class ProcedureController extends Controller
{

	private $return_data;

	final function setData($data)
	{
		$this->return_data = $data;
	}

	function json()
	{
		if (isset($this->return_data))
			return json_encode($this->return_data);
		else
			echo MessageLogger::asJSON();
	}

	function jpeg()
	{
		if (isset($this->return_data) && isset($this->return_data["image"])) {

			$image = $this->return_data["image"];
			header('Content-Type: image/jpeg');
			Config::set("HideDebugger", true);

			foreach ((array) $this->return_data["headers"] as $header => $value)
			{
				header("$header: $value");
			}

			if (isset($this->return_data["size"])) {
				$jpeg_image = imagecreatefromstring($image);
				$size = $this->return_data["size"];
				$quality = isset($this->return_data["quality"]) ? $this->return_data["quality"] : 100;
				$current_width = imagesx($jpeg_image);
				$current_height = imagesy($jpeg_image);

				if ($current_width > $size["width"]) {
					$newx = intval($size["width"]);
					$newy = intval($current_height * ($size["width"] / $current_width));
				}
				else {
					$newx = intval($size["width"]); //$current_width;
					$newy = intval($current_height * ($size["width"] / $current_width)); //$current_height;
				}
				if (!$destimg = @imagecreatetruecolor($newx, $newy))
					exit;
				if (!@imagecopyresampled($destimg, $jpeg_image, 0, 0, 0, 0, $newx, $newy, $current_width, $current_height))
					exit;

				ob_start();
				imagejpeg($destimg, null, $quality);
				$image = ob_get_clean();
			}

			header('Content-Length: ' . strlen($image));
			echo($image);
		}
	}

	function xml()
	{
		//insert default implementation here
	}

}
?>