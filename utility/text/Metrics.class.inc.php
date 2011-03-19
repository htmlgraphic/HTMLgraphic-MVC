<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

Loader::load('utility', 'text/phrase/Breakdown');

class Metrics
{

  // this appears to be more accurate
  public static function wordCount($string)
  {
    $string = html_entity_decode($string, ENT_QUOTES);
    $string = preg_replace('/<[^<].*?>/', ' ', $string);
    $string = preg_replace("/&[#]?.{0,8}?;/", '', $string);
    return str_word_count($string, 0, '0123456789');
  }

  public static function getWordCount($string)
  {
    return str_word_count(
            preg_replace("/&[#]?.{0,8}?;/", '', preg_replace('/<[^<].*?>/', '', html_entity_decode($string, ENT_QUOTES)))
    );
  }

  public static function getRecognizedWords($string)
  {
    $breakdown = new Breakdown($string);
    $words = $breakdown->getWords();

    foreach ($words as $word)
    {
      if (in_array($word, array("the", "be", "to", "of", "and", "a", "in", "that", "have", "i", "it", "for", "not", "on", "with", "he", "as", "you", "do", "at", "this", "but", "his", "by", "from", "they", "we", "say", "her", "she", "or", "an", "will", "my", "one", "all", "would", "there", "their", "what", "so", "up", "out", "if", "about", "who", "get", "which", "go", "me", "when", "make", "can", "like", "time", "no", "just", "him", "know", "take", "people", "into", "year", "your", "good", "some", "could", "them", "see", "other", "than", "then", "now", "look", "only", "come", "its", "over", "think", "also", "back", "after", "use", "two", "how", "our", "work", "first", "well", "way", "even", "new", "want", "because", "any", "these", "give", "day", "most", "us")))
        $count++;
    }
    return number_format($count / count($words), 2);
  }

}

?>
