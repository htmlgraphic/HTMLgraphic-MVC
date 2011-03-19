<?php

class KeywordGenerator
{

  private
  $stopwords = array(
      'a',
      'able',
      'about',
      'above',
      'accordingly',
      'after',
      'again',
      'against',
      'ago',
      'all',
      'also',
      'although',
      'always',
      'am',
      'among',
      'amongst',
      'an',
      'and',
      'another',
      'any',
      'anymore',
      'anyone',
      'are',
      'as',
      'at',
      'away',
      'awhile',
      'be',
      'because',
      'been',
      'begin',
      'begins',
      'before',
      'being',
      'below',
      'both',
      'bring',
      'but',
      'by',
      'can',
      'come',
      'comes',
      'could',
      'did',
      'do',
      'does',
      'done',
      'don\'t',
      'dont',
      'during',
      'each',
      'either',
      'else',
      'entire',
      'even',
      'ever',
      'every',
      'fairly',
      'far',
      'feeling',
      'for',
      'from',
      'get',
      'go',
      'got',
      'had',
      'has',
      'have',
      'he',
      'her',
      'here',
      'hers',
      'herself',
      'him',
      'himself',
      'his',
      'how',
      'however',
      'i',
      'if',
      'in',
      'into',
      'is',
      'it',
      'its',
      'itself',
      'just',
      'lastly',
      'less',
      'like',
      'makes',
      'many',
      'may',
      'me',
      'might',
      'more',
      'most',
      'much',
      'must',
      'my',
      'myself',
      'near',
      'never',
      'new',
      'no',
      'not',
      'now',
      'often',
      'of',
      'often',
      'on',
      'one',
      'only',
      'or',
      'other',
      'our',
      'outselves',
      'out',
      'over',
      'own',
      'perhaps',
      'put',
      'puts',
      'quite',
      're',
      'really',
      'said',
      'saw',
      'say',
      'says',
      'see',
      'seen',
      'several',
      'she',
      'should',
      'since',
      'so',
      'some',
      'soon',
      'such',
      'sure',
      'than',
      'that',
      'the',
      'their',
      'them',
      'themselves',
      'then',
      'there',
      'therefore',
      'these',
      'they',
      'this',
      'those',
      'though',
      'through',
      'throughout',
      'to',
      'too',
      'toward',
      'under',
      'unless',
      'until',
      'up',
      'upon',
      'us',
      'use',
      'using',
      'usually',
      'very',
      'want',
      'wants',
      'was',
      'we',
      'were',
      'what',
      'whatever',
      'when',
      'where',
      'whether',
      'which',
      'while',
      'who',
      'whom',
      'whose',
      'why',
      'will',
      'with',
      'within',
      'without',
      'would',
      'yes',
      'you',
      'your',
      'yours',
      'yourself',
      'yourselves',
      '[0-9]+'
          ),
  $stopwords_pattern,
  $internal_punctuation_pattern = '/[^a-zA-Z0-9-\s]/',
  $keywords,
  $keyphrases,
  $texts = array(),
  $weigths;

  function __construct($texts, $weights)
  {
    $this->texts = $texts;
    $this->weights = $weights;
    $this->stopwords_pattern = '/\b(' . join('|', $this->stopwords) . ')\b/i';
  }

  function getKeywords()
  {
    if (!isset($this->keywords))
    {
      $keyphrases = array();
      $keywords = array();
      foreach ((array)$this->texts as $index => $text)
      {
        $text = preg_replace('/(<([^>]+)>)/i', '', $text); // remove html tags
        $text = preg_replace('/[\n\r|\n|\r]/', ' ', $text); // remove newlines
        $text = preg_replace('/\s-\s/', ' ', $text); // remove non-inernal "-"
        $text = preg_replace('/\s+/', ' ', $text); // remove duplicate whitespace

        $weight = $this->weights[$index];

        $words = explode(' ', $text);

        foreach ($words as $i => $word)
        {

          if (strlen($word) > 3)
          {
            if (!$this->hasStopWord($word))
            {
              $word = strtolower(preg_replace('/[^a-zA-Z0-9-\s]/', '', $word));
              $keywords[$word] = (isset($keywords[$word])) ? $keywords[$word] + $weight : $weight;
            }

            if ($i > 0)
            {
              $phrase = $words[$i - 1] . ' ' . $word;
              if (!$this->hasStopWord($phrase) && !$this->hasInternalPunctuation($phrase))
              {
                $phrase = strtolower(preg_replace('/[^a-zA-Z0-9-\s]/', '', $phrase));
                $keyphrases[$phrase] = (isset($keyphrases[$phrase])) ? $keyphrases[$phrase] + $weight : $weight;
              }
            }

            if ($i > 1)
            {
              $phrase = $words[$i - 2] . ' ' . $words[$i - 1] . ' ' . $word;
              if (!$this->hasStopWord($phrase) && !$this->hasInternalPunctuation($phrase))
              {
                $phrase = strtolower(preg_replace('/[^a-zA-Z0-9-\s]/', '', $phrase));
                $keyphrases[$phrase] = (isset($keyphrases[$phrase])) ? $keyphrases[$phrase] + $weight : $weight;
              }
            }
          }
        }
      }

      $keywords = array_filter($keywords, create_function('$val', 'return ($val > 1);'));
      arsort($keywords);
      $keywords = array_keys($keywords);
      $this->keywords = $keywords;

      $top_keyword_pattern = "/\b(" . join('|', array_slice($keywords, 0, 3)) . ")\b/";

      foreach ($keyphrases as $keyphrase => $count)
      {
        if (preg_match_all($top_keyword_pattern, $keyphrase, $matches))
        {
          $keyphrases[$keyphrase] += count($matches[1]);
        }
      }

      $keyphrases = array_filter($keyphrases, create_function('$val', 'return ($val > 1);'));
      arsort($keyphrases);
      $keyphrases = array_keys($keyphrases);
      $this->keyphrases = $keyphrases;
    }

    return array('keyphrases' => $this->keyphrases, 'keywords' => $this->keywords);
  }

  function getKeywordsJSON()
  {
    $arr = $this->getKeywords();
    echo '{';
    echo '"keyphrases":[';
    foreach ($arr['keyphrases'] as $index => $keyphrase)
    {
      if ($index !== 0)
      {
        echo ',';
      }
      echo "\"$keyphrase\"";
    }
    echo '],';
    echo '"keywords":[';
    foreach ($arr['keywords'] as $index => $keyword)
    {
      if ($index !== 0)
      {
        echo ',';
      }
      echo "\"$keyword\"";
    }
    echo ']';
    echo '}';
  }

  private function hasStopWord($text)
  {
    return preg_match($this->stopwords_pattern, $text);
  }

  private function hasInternalPunctuation($text)
  {
    return preg_match($this->internal_punctuation_pattern, $text);
  }

}

?>