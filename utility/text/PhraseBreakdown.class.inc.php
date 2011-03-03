<?php
Loader::load("utility", "text/Tokens");
Loader::load("utility", "text/PorterStemmer");

class PhraseBreakdown
{
	public $words;
	public $phrases;
	public $word_profile;
	private $text;
	private $tokens;
	//settings
	private $use_stem = true;
	private $debug = false;

	//tokens will be autoloaded if nothing is passed
	function __construct($text, $tokens=null)
	{
		$this->text = $text;
		$this->tokens = $tokens;
		if (!isset($this->tokens))
		{
			$this->tokens = Tokens::pull();
		}
	}

	public function get_phrases()
	{
		$this->article_body_word_build(); //populates words and word_profile
		//print_r($this->words);
		//print_r($this->word_profile);
		if (!is_array($this->words))
		{
			return;
		}
		$this->phraseDensities();   //populates phrases
		//print_r($this->phrases);
		$this->getRateThreshold();
		//print_r($this->rated_threshold);
		//return array('phrases' => $this->phrases, 'words' => $this->words, 'word_profile' => $this->word_profile);
		return;
	}

	private function article_body_word_build()
	{
		if (!isset($this->words) && !isset($this->word_profile))
		{

			$text_string = $this->text;

			//condition string
			$text_string = preg_replace('/<[^<]+(?<!=)>/', ' ', $text_string); ## much better than strip_tags
			$text_string = html_entity_decode($text_string);

			$match = array(
			    "/[\n\r\t\d\s�()-,]+/",
			    "/\.{2,}/",
			    "/(\W|_)/"
			);

			$pieces = explode(" ", preg_replace($match, " ", $text_string));

			$words = "";
			$words_minus_tokens = "";

			foreach ($pieces as $key => $word)
			{
				//condition words
				if (preg_match("/[A-Za-z�-��-��-�]/i", $word))
				{
					$word = preg_replace("/(\W|_)/", '', $word);
					if (strlen($word))
					{
						$word = strtolower($word);
						if ($this->use_stem)
						{
							$word = PorterStemmer :: stem($word); //$this->stem($word);
						}

						if (!in_array($word, $this->tokens))
						{
							$words_minus_tokens[] = $word; ### word map
						}
						$words[] = $word;
					}
				}
			}

			if (is_array($words_minus_tokens))
				$this->word_profile = array_count_values($words_minus_tokens);
			else
				$this->word_profile = array();

			$this->words = $words;
		}

		return $this->words; //words in original order
	}

	private function phraseDensities($size = 2, $limit = 1000) // # Phrase Segmentation & Intersect Match
	{
		$phrases = array();
		$my_phrases = array();
		$more_phrases = array();
		$build = array();
		$builds = array();

		foreach ($this->words as $key => $item)
		{
			//gather words between stop words
			$profile_word = !(in_array($item, $this->tokens)); //array_key_exists($item, $this->getWordProfile());

			if (!$profile_word)
			{
				//we've hit a stop word...
				//group the total phrase into pieces of the given phrase length.
				//Example: "peperoni pizza rules" => array("peperoni pizza","pizza rules");
				while (count($builds) > 1)
				{
					$phrase = array(
					    array_shift($builds)
					);

					$i = 0;
					while (count($phrase) < $size && isset($builds[$i]))
					{
						$phrase[] = $builds[$i];
						$i++;
					}
					$my_phrases[] = implode(" ", $phrase);
				}
				$builds = array();
			}
			else
			{
				$builds[] = $item;
			}
		}
		$profile = $this->word_profile;

		//phrases are weighted by the total number of times each of the words appear in the body.
		$my_final = array();
		foreach ($my_phrases as $phrase)
		{
			$words = explode(" ", $phrase);
			$weight = 0;
			foreach ($words as $word)
			{
				$word = trim($word);

				if (strlen($word) && isset($profile[$word]))
				{
					$weight += $profile[$word];
				}
			}

			if ($weight > 1)
			{
				$my_final[$phrase] = $weight;
			}
		}
		$final = $my_final;

		if ($this->debug)
		{
			echo ("\n\nphrase scrores</br>\n");
			print_r($final);
			echo ("\n\n");
		}

		if ($limit == 0 || count($final) < $limit)//1000)
		{
			//pass all for mode to get all phrases else return only those that repeat
			$this->phrases = $final;
			//return $final;
		}
		else
		{
			$inc = 0;
			//while (list ($ff, $score) = each($final))
			foreach ($final as $ff => $score)
			{

				//echo "-- $ff $score \n";
				//if($debug){echo("Final Trim:$ff:$score\n")};
				if ($score > 2)
				{
					$finalphrases[$ff] = $score;
					$inc++;
				}
				else
				{
					//break;
				}
			}
			//return ($finalphrases);
			$this->phrases = $finalphrases;
		}
		return;
	}

	public $rated_threshold;

	private function getRateThreshold()
	{
		if (!isset($this->rated_threshold))
		{
			$word_count = count($this->words);
			$sequence_count = count($this->phrases);
			if ($word_count <= 400)//$siz1 <= 400)
			{
				$threshold = $sequence_count / 1.6; //sizeof($phrases1) / 1.6;
			}
			else if ($word_count <= 650)//$siz1 <= 650)
			{ // #lower phrase match threshold for smaller articles
				$threshold = $sequence_count / 2.3;
			}
			else
			{
				$threshold = $sequence_count / 3;
			}
			$this->rated_threshold = $threshold;
		}
		return $this->rated_threshold;
	}

}
?>
