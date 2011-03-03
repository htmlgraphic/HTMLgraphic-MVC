<?php
class KeywordDensity
{
	private $text;
	private $type;
	private $line_separator = "\n";

	function __construct($text, $type)
	{
		$this->text = $text;
		$this->type = $type;
	}

	function setLineSeparator($separator)
	{
		$this->line_separator = $separator;
	}

	//global $keyword_density_stop_words_cache;

	function getDensities($returned_results = null)
	{
		$line_separator = $this->line_separator;
		$text = $this->text;

		$returned_results = (int) $returned_results;
		if ($returned_results < 1) //make sure returned results is a positive integer - if not, reset to default
			$returned_results = 0;

		$stops = $this->getStopWords();
		//scrub words list
		//Debugger::log("working with " . count($stops) . " words");
		//should include strip tags around $text - but old version did not do this and my goal is to provide same results so currently not done.
		$words = explode(" ", str_replace('_', ' ', strip_tags($text))); //removes empty values
		for ($i = 0, $imax = count($words); $i < $imax; $i++)
		{
			$words[$i] = trim(strtolower(preg_replace('/\W/', '', $words[$i])));
		}
		$words = array_filter($words); //removes empty values
		//Debugger::log("words: " . count($words));
		$non_stop_words = array_diff($words, $stops); //clear stop words from words array

		$word_counts = array_count_values($non_stop_words); //counts word values
		arsort($word_counts); //sort array by highest count value first

		$total_words = array_sum(array_values($word_counts)); //get a sum of the total number of cleaned words in the article

		$data = array();
		foreach ($word_counts as $word => $count)
		{
			if ($count) //skip count == 0
				$data[$word] = array("count" => $count, "density" => round((($count / $total_words) * 100), 1));
		}

		return $data;
	}

	//by default, load local script repository version of the stopwords.txt file... can include absolute path to reach other stop word file locations
	function getDensity($returned_results = 5, $include_percentage=TRUE)
	{
		$line_separator = $this->line_separator;
		$text = $this->text;

		$returned_results = (int) $returned_results;
		if ($returned_results < 1) //make sure returned results is a positive integer - if not, reset to default
			$returned_results = 5;

		$stops = $this->getStopWords(); //$stop_words_file);
		Debugger::log("working with " . count($stops) . " words");
		//scrub words list
		//should include strip tags around $text - but old version did not do this and my goal is to provide same results so currently not done.
		$words = explode(" ", str_replace('_', ' ', strip_tags($text))); //removes empty values
		for ($i = 0, $imax = count($words); $i < $imax; $i++)
		{
			$words[$i] = trim(strtolower(preg_replace('/\W/', '', $words[$i])));
		}
		$words = array_filter($words); //removes empty values
		Debugger::log("words: " . count($words) . " stops: " . count($stops));
		$non_stop_words = array_diff($words, $stops); //clear stop words from words array
		$word_counts = array_count_values($non_stop_words); //counts word values
		arsort($word_counts); //sort array by highest count value first

		if ($include_percentage) //do not calculate total words if percentage not required
			$total_words = array_sum(array_values($word_counts)); //get a sum of the total number of cleaned words in the article

			$word_counts = array_slice($word_counts, 0, $returned_results, TRUE); //cut down to top 5 terms while preserving key value associations

		$data = array();
		foreach ($word_counts as $word => $count)
		{
			if ($count) //skip count == 0
				$data[].= $word . (($include_percentage) ? " - " . round((($count / $total_words) * 100), 1) . "%" : '');
		}

		return implode($line_separator, $data);
	}

	private static $keyword_density_stop_words_cache = array();

	private function getStopWords()
	{
		$stop_words_file = $this->type;
		if (!isset(self::$keyword_density_stop_words_cache[$stop_words_file]))
		{

			//use stop_words_file variable as key for cache so calls for same file return proper cached results
			self::$keyword_density_stop_words_cache[$stop_words_file] = array(); //set to empty array so it doesn't trying to access the file system each time its called if something fails
			//build file path - check for absolute path first
			$stop_words_full_path = $stop_words_file; //assume full path passed first
			if ($stop_words_file[0] != '/') //if file path does not start with '/', assume local file requested and add script repo path
				$stop_words_full_path = '/var/www/script-repository/' . $stop_words_file;

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

					self::$keyword_density_stop_words_cache[$stop_words_file] = $stops;
				}//end if is_array
			}//end if file_exists
			else
			{
				Debugger::log("$stop_words_full_path not found!");
			}
		}//end if cache var not set

		return self::$keyword_density_stop_words_cache[$stop_words_file];
	}

}
?>