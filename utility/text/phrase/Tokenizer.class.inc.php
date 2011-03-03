<?php

class Tokenizer
{

	private $type;

	public function __construct($type = self::NORMAL)
	{
		$this->type = $type;
	}

	private $tokens;

	public function getTokens()
	{
		if (!isset($this->tokens))
		{
			$this->tokens = array();
			//build file path - check for absolute path first

			$stop_words_full_path = "/var/www/script-repository/{$this->type}";

			if (file_exists($stop_words_full_path))
			{
				$stops = @file($stop_words_full_path);
				if (is_array($stops))
				{
					//scrub stops list
					for ($i = 0, $imax = count($stops); $i < $imax; $i++)
					{
						$stops[$i] = trim(strtolower(preg_replace('/\W/', '', $stops[$i])));
					}

					$this->tokens = $stops;
				}//end if is_array
			}//end if file_exists
			else
			{
				Debugger::log("$stop_words_full_path not found!");
			}
		}//end if cache var not set

		return $this->tokens;
	}

	public function isToken($word)
	{
		return in_array($word, $this->getTokens());
	}

}
?>
