<?php

class HttpStatusChecker
{

	private $url;
	private $parsedHeaders = array();
	private $urlChecked = false;

	public function __construct($url)
	{
		$this->url = $url;
	}

	public function getHttpReturnCode()
	{
		$this->run();

		if (isset($this->parsedHeaders['HTTP']['status_code']))
			return (int) $this->parsedHeaders['HTTP']['status_code'];

		return 0;
	}

	private function run()
	{
		if (!$this->urlChecked)
		{
			$c = curl_init($this->url);
			curl_setopt($c, CURLOPT_HEADER, 1);
			curl_setopt($c, CURLOPT_NOBODY, 1);
			curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);

			$output = curl_exec($c);
			curl_close($c);

			$this->parseHeaderOutput($output);

			$this->urlChecked = true;
		}
	}

	private function parseHeaderOutput($header)
	{
		$fields = explode("\r\n", preg_replace('/\x0D\x0A[\x09\x20]+/', ' ', $header));

		foreach ($fields as $field)
		{
			if (preg_match('/([^:]+): (.+)/m', $field, $match))
			{
				$match[1] = preg_replace('/(?<=^|[\x09\x20\x2D])./e', 'strtoupper("\0")', strtolower(trim($match[1])));
				if (isset($this->parsedHeaders[$match[1]]))
					$this->parsedHeaders[$match[1]] = array($this->parsedHeaders[$match[1]], $match[2]);
				else
					$this->parsedHeaders[$match[1]] = trim($match[2]);
			}
			else if (preg_match('/HTTP\/([0-9\.]+) ([0-9]+) (.*)/m', $field, $match))
			{
				if (isset($match[3]))
				{
					$this->parsedHeaders['HTTP'] = array(
					    'version' => $match[1],
					    'status_code' => $match[2],
					    'status' => $match[3]
					);
				}
			}
			else if ($field)
			{
				$this->parsedHeaders[] = $field;
			}
		}
	}

}
?>