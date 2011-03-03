<?php
Loader::load("utility", "text/phrase/Tokenizer");
Loader::load("utility", "text/PorterStemmer");

class Breakdown
{

	private $text;
	private $tokenizer;

	public function __construct($text)
	{
		$this->text = $text;

		$this->tokenizer = new Tokenizer();
	}

	private static $breakdown_cache;

	public function fromCache(Article $article)
	{
		if (!isset(self::$breakdown_cache[$article->getID()]))
		{
			self::$breakdown_cache[$article->getID()] = new Breakdown($article->getBody());
		}

		return self::$breakdown_cache[$article->getID()];
	}

	function setTokenizer(Tokenizer $tokenizer)
	{
		$this->tokenizer = $tokenizer;
	}

	function phraseContainsToken($phrase)
	{
		if (is_string($phrase))
			$phrase = explode(" ", $phrase);
		foreach ($phrase as $word)
			if ($this->getTokenizer()->isToken($word))
				return true;

		return false;
	}

	function getTokenizer()
	{
		return $this->tokenizer;
	}

	private $stemmer;

	function useStemmer()
	{
		$this->stemmer = true;
	}

	function getOriginalText()
	{
		return $this->text;
	}

	private function matchRegularExpressions()
	{
		return array(
		    "/[\n\r\t\d\s�()-,\.]+/",
		    "/\.{2,}/",
		//"/(\W|_)/"
		);
	}

	public function stripTags()
	{
		$text = preg_replace('/<[^<]+(?<!=)>/', ' ', $this->text); ## much better than strip_tags
		$text = html_entity_decode($text);

		return $text;
	}

	private $stem_to_original;
	private $words;

	//private function getWords()
	public function getWords()
	{
		if (!isset($this->words))
		{
			$text = $this->stripTags();
			$this->words = array();
			$pieces = explode(" ", preg_replace($this->matchRegularExpressions(), " ", $text));
			foreach ($pieces as $key => $word)
			{
				if (preg_match("/[A-Za-z�-��-��-�]/i", $word))
				{
					if (isset($this->stemmer))
					{
						$stem = PorterStemmer::stem($word);
						$this->stem_to_original[$stem] = $word;
						$word = $stem;
					}

					$word = preg_replace("/\W/", "", $word);

					if (strlen($word))
					{
						$this->words[] = strtolower($word);
					}
				}
			}
		}

		return $this->words;
	}

	public function getOriginalWord($stem)
	{
		return (isset($this->stem_to_original[$stem])) ? $this->stem_to_original[$stem] : $stem;
	}

	private $sentences;

	function getSentences()
	{

		$split = "/[\.\!\?][ \n\r]/";

		$strip = array(
		    "/[\n\r\t\d\s�()-,]+/",
			   //		"/\.{2,}/",
			   //"/(\W|_)/"
		);

		if (!isset($this->sentences))
		{
			$text = $this->stripTags();
			$this->sentences = array();

			preg_replace($strip, " ", $text);

			preg_replace("/ {2,}/", " ", $text);

			preg_match_all($split, $text, $matches, PREG_OFFSET_CAPTURE);

			$start = 0;
			foreach ($matches[0] as $info)
			{

				$sentence = substr($text, $start, $info[1]);
				$this->sentences[] = $sentence;
				$start = $info[1] + 1;
			}
		}

		return $this->sentences;
	}

	private $phrases = array();

	function getPhrases($length = 0)
	{
		if ($length == 0)
			return null;
		if (!isset($this->phrases[$length]))
		{
			$this->phrases[$length] = array();
			$words = $this->getWords();

			for ($i = 0; $i < count($words); $i++)
			{
				$word = $words[$i];

				//phrases cannot start with a token.
				if (!$this->getTokenizer()->isToken($word))
				{

					$last_word = null;
					$phrase = array_slice($words, $i, $length);
					if (count($phrase) == $length)
					{
						if (!$this->getTokenizer()->isToken($phrase[$length - 1]))
							$this->phrases[$length][] = $phrase;
					}
				}
			}
		}

		return $this->phrases[$length];
	}

	function phraseToString($phrase)
	{
		return implode(" ", $phrase);
	}

	function getDensityOfPhrase($phrase)
	{
		$phrases = $this->getPhraseses();
	}

	/**
	 * Color should be done with a nearest neighbor algorithm, 
	 * 	such that no two colors touch in a phrase breakdown display.
	 * 
	 */
	private static $colors = array('#ffdede', '#ffd999', '#ffff99', '#ccccff', '#aaccff');
	private $index_of_last_color;

	public function nextColor()
	{
		if (isset($this->index_of_last_color))
		{
			$this->index_of_last_color++;
			if ($this->index_of_last_color >= count(self::$colors))
				$this->index_of_last_color = 0;
			//else
			//	$this->index_of_last_color++;
		}
		else
			$this->index_of_last_color = 0;

		return self::$colors[$this->index_of_last_color];
	}

	function getOccurrencesOfPhrase($phrase)//,$words)//,$phrases)
	{
		$words = $this->getWords();

		$occurrences = 0;
		for ($i = 0; $i < count($words); $i++)
		{

			if ($words[$i] == $phrase[0])
			{
				$length = 0;
				$valid = true;
				for ($j = 0; $j < count($phrase) && $valid && ($i + $j) < count($words); $j++)
				{
					$valid = $words[$i + $j] == $phrase[$j];
					$length++;
				}
				if ($valid && $length == count($phrase))//($i+$j) < count($words))
				{
					$occurrences++;
				}
			}
		}
		return $occurrences;
	}

	private $phrase_breakdown;

	function phraseBreakdown($size, $minimum_occurences = 2)
	{
		if (!isset($this->phrase_breakdown[$size]))
		{
			$breakdown = array();
			foreach ((array) $this->getPhrases($size) as $phrase)
			{
				$phrase_string = implode(" ", $phrase);
				if (!isset($breakdown[$phrase_string]))
				{
					$occurrences = $this->getOccurrencesOfPhrase($phrase);
					if ($occurrences >= $minimum_occurences)
						$breakdown[$phrase_string] = $occurrences;
				}
			}
			$this->phrase_breakdown[$size] = $breakdown;
		}
		return $this->phrase_breakdown[$size];
	}

	function getSize()
	{
		return count($this->getWords());
	}

	function getPhraseBreakdown($max_length = 4, $minimum_densities = array(0 => .02), $minimum_occurences = null)
	{
		$this->breakdown = array();
		$length = 1;
		//Debugger::log("*************");
		//error_log("length $length");

		$words = $this->getWords();
		$this->breakdown["size"] = count($words);
		while (count($this->getPhrases($length)) && $length <= $max_length)
		{
			if (isset($minimum_densities[$length]))
				$minimum_density = $minimum_densities[$length];
			else if (isset($minimum_densities[0]))
				$minimum_density = $minimum_densities[0];
			else
				$minimum_density = .04;

			$phrases = $this->getPhrases($length);

			foreach ($phrases as $phrase)
			{
				$occurrences = $this->getOccurrencesOfPhrase($phrase, $words);
				//Debugger::log(implode(" ",$phrase) . " occurs: " . $occurrences);
				$density = ($occurrences / ( count($words) / count($phrase)));


				if (
					   $density >= $minimum_density ||
					   ( isset($minimum_occurences[$length]) &&
					   $occurrences >= $minimum_occurences[$length] ))
				{
					$this->breakdown[$length][$this->phraseToString($phrase)] = array(
					    "density" => $density,
					    "occurrences" => $occurrences,
						   //"color" => $this->nextColor()
					);
				}
			}


			$length++;
		}
		//Debugger::log("length: $length count: " . count($this->getPhrases($length)));
		//Debugger::log("*************");

		return $this->breakdown;
	}

}
?>