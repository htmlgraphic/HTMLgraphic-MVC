<?php

class Browser
{
	static function fckCompat()
	{
		// opera and safari not supported for gui editor
		$browser = $_SERVER['HTTP_USER_AGENT'];
		$br_v = '';
		$skip = false;
		$extra_msg = '';
		$forcePreviewNewWindow = false;
		if (preg_match("/Opera/", $browser)) {
			preg_match("/Opera\/([\d]+)\.([\d]+)/", $browser, $ver);
			$opera_ver = "$ver[1].$ver[2]";
			if ($opera_ver >= '9.5') {

			}
			else {
				$br_v = "Opera";
				$extra_msg = "Please Upgrade to Opera V9.5 or newer";
				$skip = true;
			}
		}
		if (preg_match("/Safari/", $browser)) {
			if (preg_match("/Chrome/", $browser, $match)) {
				## nadda
			}
			else {
				if (strpos($browser, 'Mobile') !== false) {
					$br_v = "Mobile Safari";
					$extra_msg = "Mobile Safari does not support this feature";
					$skip = true;
				}
				else {
					preg_match("/Version\/([\d]+)\.([\d]+)/", $browser, $ver);
					if ($ver[1] >= '3') {

					}
					else {
						$br_v = "Safari";
						$extra_msg = "Please Upgrade to Safari V3.0 or newer";
						$skip = true;
					}
				}
			}
		}
		if (preg_match("/Firefox/", $browser)) {
			preg_match("/Firefox\/([\d]+)\.([\d]+)/", $browser, $ver);
			$ff_ver = "$ver[1].$ver[2]";
			if ($ff_ver >= '1.5') {

			}
			else {
				$br_v = "Firefox";
				$extra_msg = "Please Upgrade to FireFox V1.5 or newer";
				$skip = true;
			}
		}
		if (preg_match("/MSIE/", $browser)) {
			preg_match("/MSIE ([\d]+)\.([\d]+)/", $browser, $ver);
			$ie_ver = "$ver[1].$ver[2]";
			if ($ie_ver >= '5.5') {

			}
			else {
				$br_v = "Internet Explorer";
				$extra_msg = "Please Upgrade to Internet Explorer V5.5 or newer";
				$skip = true;
			}
			if ($ie_ver <= "7.0")
				$forcePreviewNewWindow = true;
		}
		$data["disabled"] = $skip;
		$data["version"] = $br_v;
		$data["message"] = $extra_msg;
		$data['new_preview_window'] = $forcePreviewNewWindow;

		return $data;
	}

}
?>