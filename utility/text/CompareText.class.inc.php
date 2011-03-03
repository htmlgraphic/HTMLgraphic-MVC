<?
//compair text to see how similar they are

class CompareText
{
	private $text1;
	private $text2;
	public $results;
	
	function __construct(PhraseBreakdown $text1, PhraseBreakdown $text2)
	{
		$this->text1 = $text1;
		$this->text2 = $text2;
	}
	
	public function processResults() 
	{
		$this->word_diff();
		$this->phrase_diff();
		//print_r($results);
		return;
	}
	
	private function word_diff() 
	{
		$matched_word_count = count($this->text2->words);
		$word_count = count($this->text1->words);
		
		//words that exist in the current article and not in the second.
		$word_difference = array_diff($this->text1->words,$this->text2->words);
		//words that exist in the second article but not the current.
		$matched_word_difference = array_diff($this->text2->words, $this->text1->words);
		
		//all of the words that only exist in one of the articles.
		$difference = array_merge($word_difference,$matched_word_difference);
		$difference = array_unique($difference);

		if ($matched_word_count > $word_count)//$siz1 > $siz2)
		{
			if ($matched_word_count > ($word_count * 3.9)) //$siz1 > ($siz2 * 3.9))
			{ // # if content ratio is severly mismatched
				$verification_failed = true;//$skip = 1;
			}
			$longer_articles_count = $matched_word_count;//$siz1;
		}
		else
		{
			if ($word_count > ($matched_word_count * 3.9)) //$siz2 > ($siz1 * 3.9))
			{ // # if content ratio is severly mismatched
				$verification_failed = true;//$skip = 1;
			}
			$longer_articles_count = $word_count;//$siz2;
		}
		
		$tmpdiff = count($difference) / $longer_articles_count;//$diffsize / $maxsiz;
		$percentdiff = $tmpdiff * 100;
		$percentdiff = intval($percentdiff);
		
		$this->results['percentdiff'] = $percentdiff;
		
		return;
	}
	
	private function phrase_diff()
	{
		//determine rate based on phrase difference
		$sequence_difference = array_intersect(array_keys($this->text2->phrases),array_keys($this->text1->phrases));
		$rate = count($sequence_difference);
			
		//use the rate_threshold of the smaller article
		$rate_threshold = ($this->text2->rated_threshold > $this->text1->rated_threshold) ? 
				$this->text1->rated_threshold : $this->text2->rated_threshold;

		$rate_threshold = intval($rate_threshold);
		if ($rate < $rate_threshold)//$rate < $rate_thres)
		{
			$rate_failed = true;//$skip = 1;
		} else { 
			$rate_failed = false; 
		}
		
		$this->results['rate'] = $rate;
		$this->results['rate_threshold'] = $rate_threshold;
		$this->results['rate_fail'] = $rate_failed;

		return;
	}
}
?>