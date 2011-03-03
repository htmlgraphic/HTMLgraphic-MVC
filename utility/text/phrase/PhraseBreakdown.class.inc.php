<?php
Loader::load("utility", "text/phrase/Tokenizer");
Loader::load("utility", "text/PorterStemmer");

class PhraseBreakdown
{

	private $text;
	private $stem;
	private $tokenizer;

	public function __construct($text, $stem = false, $tokenizer = null)
	{
		Debugger::speed(__CLASS__);
		$this->text = $text;
		$this->stem = $stem;

		//fixing issue where hyphens would be ignored when
		//calculating the phrase denisty for article
		$this->text = str_replace("-", " ", $this->text);

		if (!isset($tokenizer))
			$this->tokenizer = new Tokenizer();
		else
			$this->tokenizer = $tokenizer;
	}

	public function __destruct()
	{
		Debugger::speed(__CLASS__);
	}

	private function isToken($word)
	{
		return $this->tokenizer->isToken($word);
	}

	private static $REMOVAL_CHARS = array("'", "?", ",", ";");

	private function stem($word)
	{


		if (is_array($word))
		{
			foreach ($word as $w)
			{
				if (function_exists("stemmer"))
					$phrase[] = stemmer(str_replace(self::$REMOVAL_CHARS, "", $w));
				else
					$phrase[] = PorterStemmer::stem(str_replace(self::$REMOVAL_CHARS, "", $w));
			}
			return $phrase;
		}

		if (strlen($word) <= 2)
			return $word;
		else
		{

			if (function_exists("stemmer"))
				return stemmer(str_replace(self::$REMOVAL_CHARS, "", $word));
			else
				return PorterStemmer::stem(str_replace(self::$REMOVAL_CHARS, "", $word));
		}
	}

	function isStemming()
	{
		return $this->stem;
	}

	public function getText()
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

	private $stem_to_original;
	private $words_stemmed;
	private $stemmed;
	private $words;

	private function getWords()
	{
		if (!isset($this->words))
		{
			$text = strtolower(strip_tags(html_entity_decode($this->text)));

			$this->words = array();
			$this->words_stemmed = array();
			$pieces = explode(" ", preg_replace($this->matchRegularExpressions(), " ", $text));
			foreach ($pieces as $key => $word)
			{
				if (preg_match("/[a-z�-��-��-�]/i", $word))
				{
					if ($this->isStemming())
					{
						$stem = $this->stem($word);
						$this->stem_to_original[$stem] = $word;
						$this->stemmed[$word] = $stem;
						$this->words_stemmed[] = $stem;
					}

					$word = preg_replace("/\W/", "", $word);

					if (strlen($word))
					{
						$this->words[] = $word;
					}
				}
			}
			if ($this->isStemming())
				$this->string = " " . implode('  ', $this->words_stemmed) . " ";
			else
				$this->string = " " . implode('  ', $this->words) . " ";
		}

		return $this->words;
	}

	private function getOriginalWord($stem)
	{
		return (isset($this->stem_to_original[$stem])) ? $this->stem_to_original[$stem] : $stem;
	}

	private $phrases = array();

	private function getPhrases($length = 0)
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
				if (!$this->isToken($word))
				{

					$last_word = null;
					$phrase = array_slice($words, $i, $length);
					if (count($phrase) == $length)
					{
						if (!$this->isToken($phrase[$length - 1]))
						{
							$this->phrases[$length][] = $phrase;
						}
					}
				}
			}
		}

		return $this->phrases[$length];
	}

	private function phraseToString($phrase)
	{
		if ($this->isStemming())
			return implode("  ", $this->stem($phrase));
		else
			return implode("  ", $phrase);
	}

	private function getOccurrencesOfPhrase($phrase)
	{
		$this->getWords();
		return substr_count($this->string, " " . $this->phraseToString($phrase) . " ");
	}

	private $phrase_breakdown;

	private function phraseBreakdown($size, $minimum_occurences = 2)
	{
		if (!isset($this->phrase_breakdown[$size]))
		{
			$breakdown = array();
			foreach ((array) $this->getPhrases($size) as $phrase)
			{
				$phrase_string = $this->phraseToString($phrase);

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
		$words = $this->getWords();
		Debugger::log("words: " . count($words));
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
			$iteration = 0;
			$ignore = array();
			foreach ($phrases as $phrase)
			{
				$iteration++;
				$original_phrase_string = implode(" ", $phrase);
				$phrase_string = $this->phraseToString($phrase);
				if (!in_array($original_phrase_string, $ignore))
				{
					if (!isset($this->breakdown[$length][$phrase_string]))
					{

						$occurrences = $this->getOccurrencesOfPhrase($phrase);
						$density = ($occurrences / ( count($words) / count($phrase)));



						$appended = false;
						//when stemming check if this is a variant.
						if (isset($this->breakdown[$length]) && $this->isStemming())
						{
							foreach ($this->breakdown[$length] as $existing_phrase => $info)
							{
								$existing_words = explode(" ", $existing_phrase);
								$matches = true;
								for ($i = 0; $i < $length && $matches; $i++)
								{
									$matches = $this->stem($existing_words[$i]) == $this->stem($phrase[$i]);
								}
								//if it matches something we've already found, we add it as a variant.
								if ($matches)
								{
									$appended = true;

									if (!isset($this->breakdown[$length][$existing_phrase]["variants"]) || !in_array($original_phrase_string, (array) $this->breakdown[$length][$existing_phrase]["variants"]))
										$this->breakdown[$length][$existing_phrase]["variants"][] = $original_phrase_string;
								}
							}
						}
						//we do not add variants.
						if (!$appended)
						{
							if ($density >= $minimum_density ||
								   ( isset($minimum_occurences[$length]) &&
								   $occurrences >= $minimum_occurences[$length] ))
							{


								$original_phrase = "";
								foreach ($phrase as $key => $word)
								{
									$original_phrase .= $this->getOriginalWord($word) . " ";
								}

								$this->breakdown[$length][$original_phrase_string] = array(
								    "density" => $density,
								    "occurrences" => $occurrences
								);
							}
						}
					}
					$ignore[] = $original_phrase_string;
				}
			}

			$length++;
		}

		return $this->breakdown;
	}

}
?>