<?php

class FormatText
{

  function __construct()
  {
    
  }

  static function missing_html($text)
  {
    preg_match("/<p>(.*)<\/p>/si", $text, $result);

    return!isset($result[0]);
  }

  static function collectXMPContents($text)
  {

    $i = 1;
    $xmp_replace = array(
    );

    if (preg_match_all("/\<ol\>(.+?)\<\/ol\>?/is", $text, $matches))
    {
      foreach ($matches[0] as $xmp_match)
      {
        $rep_str = '%__xmp_replace_' . $i . '__%';
        $xmp_replace[$rep_str] = "'" . addslashes($xmp_match) . "'";
        ++$i;
      }
    }

    if (preg_match_all("/\<ul\>(.+?)\<\/ul\>?/is", $text, $matches))
    {
      foreach ($matches[0] as $xmp_match)
      {
        $rep_str = '%__xmp_replace_' . $i . '__%';
        $xmp_replace[$rep_str] = "'" . addslashes($xmp_match) . "'";
        ++$i;
      }
    }

    if (preg_match_all("/\<xmp\>(.+?)\<\/xmp\>?/is", $text, $matches))
    {
      foreach ($matches[0] as $xmp_match)
      {
        $rep_str = '%__xmp_replace_' . $i . '__%';
        $xmp_replace[$rep_str] = "'" . addslashes($xmp_match) . "'";
        ++$i;
      }
    }

    return $xmp_replace;
  }

  static function escapeForRegex($string)
  {
    if ($string == '')
    {
      return false;
    }
    $string = str_replace("?", "\\?", $string);
    $string = str_replace("(", "\\(", $string);
    $string = str_replace(")", "\\)", $string);
    $string = str_replace(".", "\\.", $string);
    $string = str_replace("-", "\\-", $string);
    $string = str_replace('$', '\\$', $string);
    $string = str_replace("#", "\\#", $string);
    $string = str_replace("+", "\\+", $string);
    $string = str_replace("*", "\\*", $string);
    $string = str_replace("[", "\\[", $string);
    $string = str_replace("]", "\\]", $string);
    $string = str_replace("|", "\\|", $string);

    return $string;
  }

  static function formatText($text, $addPara = 1, $addBreakLine = 0, $removeHardBreak = 1, $stripTags = 0, $addHardBreak = 0, $allowedTags = '')
  {
    $text = trim($text);
    # add new lines to paragraphs and convert br to newline
    if ($addHardBreak)
    {
      $text = str_replace("<br>", "\n", $text);
      $text = str_replace("<BR>", "\n", $text);
      $text = str_replace("</p>", "</p>\n\n", $text);
      $text = str_replace("</P>", "</P>\n\n", $text);
    }
    # strip all HTML tags if the param $stripTags is set to 1
    # keep the tags that are set in the param $allowedTags
    if ($stripTags)
    {
      if ($allowedTags)
      {
        $text = strip_tags($text, $allowedTags);
      }
      else
      {
        $text = strip_tags($text);
      }
    }
    # add <p> if the param $addPara is set to 1
    if ($addPara)
    {
      $textElements = preg_split("/\n(\s)+/U", $text);

      $newText = "";
      foreach ($textElements as $item)
      {
        #be sure to strip control chars
        $item = trim($item);
        if (strlen($item) != 0)
        {
          if (preg_match("/<(ol|ul|li)>/", $item))
          {### don't wrap list items
            $newText .= "$item";
          }
          else
          {
            $newText .= "<p>$item</p>";
          }
        }
      }
      $text = $newText;
    }
    # add <br> if the param $addBreakLine is set to 1
    if ($addBreakLine)
    {
      $text = str_replace("\n", "<br>", $text);
      #$text       = nl2br($text);
    }
    if ($removeHardBreak)
    { ### remove hardreturns
      $text = str_replace("\n", " ", $text);
      $text = str_replace("\r", "", $text);
      #$table      = null;
      # $table["</p>"]  = "</p>\n\n";
      # $text       = strtr ($text, $table);
    }
    return $text;
  }

  static function stripStyleTags($text)
  {

    $start_tag = "<a ";
    //$start_tag = "href=";
    $end_tag = "</a>";

    $s_tagLen = strlen($start_tag);
    $e_tagLen = strlen($end_tag);
    $start_tag_pos = 0;

    while (($start_tag_pos = strpos(strtolower($text), $start_tag, $start_tag_pos)) !== false)
    {
      $start_tag_pos += $s_tagLen;
      if (($end_tag_pos = strpos(strtolower($text), $end_tag, $start_tag_pos)) !== false)
      {
        $str_to_clean = substr($text, $start_tag_pos, $end_tag_pos - $start_tag_pos);
        if (self::has_correct_open_close_tag($str_to_clean))
        {
          $clean_str = strip_tags($str_to_clean); ### remove all tags
          ///$clean_str = preg_replace("/<[\/]{0,1}(B|b|I|i|em|EM|STRONG|strong)[^><]*>/",'',$str_to_clean);
          $text = str_replace($str_to_clean, $clean_str, $text); ### replace old text with clean text
        }
        $start_tag_pos = $end_tag_pos + $e_tagLen;
      }
      if (strlen($text) < $start_tag_pos)
        break;
    }//while
    $text = str_replace("  ", " ", $text); ### remove double spaces
    return $text;
  }

  static function has_correct_open_close_tag($text)
  {
    return ((substr_count($text, '<') + 1) == substr_count($text, '>')) ? TRUE : FALSE;
  }

  static function myucwords($str)
  {
    /// capitalize every word except ones in $no_cap;
    $no_cap = array(
        ' To ', ' Is ', ' In ', ' On ', ' It ', ' And ', ' At ', ' By ', ' A ', ' An ', ' Of ', ' The '
    );
    $str = ucwords($str);

    foreach ($no_cap as $word)
    {
      $tmp = strtolower($word);
      $str = preg_replace("/(?<!`)(?<![(\.\!\-\?)\s?])$word/", $tmp, $str);
    }
    return $str;
  }

  static function rssfilter($str, $isTitle=false)
  {
    if ($str == "")
    {
      return "";
    }

    //--> STRIP ANY HTML TAGS
    $str = strip_tags($str);

    if ($isTitle)
    {
      $str = self::rssfilter_clean_title($str);
    }

    //REPLACE INTERNATIONAL AND SPECIAL CHARACTERS
    $trans = (isset($trans) && is_array($trans)) ? $trans : get_html_translation_table(HTML_ENTITIES, ENT_QUOTES);
    foreach ($trans as $k => $v)
    {
      $trans[$k] = "&#" . ord($k) . ";";
    }
    $str = strtr($str, $trans);

    //--> REPLACE MS WORD CHARACTERS
    /* MS WORD CHARS: 
      \x80= Euro Sign
      \x82= Single Low-9 Quotation mark
      \x84= Double Low-9 Quotation mark
      \x85= horizontal elipse '...'
      \x91= left single quote
      \x92= right single quote
      \x93= left double quote
      \x94= right double quote
      \x95= bullet - small black circle
      \x96= ndash - dash same width as n char
      \x97= mdash - dash same width as m char (common ms word dash)
     */

    $ms_word_arr = array("\x80", "\x82", "\x84", "\x85", "\x91", "\x92", "\x93", "\x94", "\x95", "\x96", "\x97", "\x99");
    $ms_word_repl_arr = array("&#8364;", ",", "", "...", "'", "'", "\"", "\"", "", "-", "-", "(TM)");
    //$ms_word_repl_arr = array("&#8218;", "&#8222;", "&#8230;", "&#8216;", "&#8217;", "&#8220;", "&#8221;", "&#8226;", "&#8211;", "&#8212;");

    $str = str_replace($ms_word_arr, $ms_word_repl_arr, $str);

    //--> REPLACE DOS ^M RETURN CHARACTER
    $str = str_replace("
	", "\n", $str);

    //rerun title filter again to remove any ms word characters that were converted in the process
    if ($isTitle)
    {
      $str = self::rssfilter_format_title($str);
    }

    return trim($str);
  }

//end rssfilter()

  private static function rssfilter_clean_title($str)
  {
    if ($str == "")
    {
      return "";
    }

    $str = str_replace("-", " ", $str); //remove any dashes and replace with space
    $str = preg_replace("/\s+/", " ", $str); //replace any instances of multiple spaces with only one space

    $str = str_replace("<", "", $str);
    $str = str_replace(">", "", $str);
    $str = str_replace("'", "", $str);
    $str = str_replace("\"", "", $str);
    $str = str_replace("&", "and", $str); //leave in clean function to prevent convertion to &amp;
    //$str = str_replace("?","",$str); //was comments out for some reason.. left commented out in small attempt to keep links consistent
    $str = str_replace("#", "", $str);

    return trim($str);
  }

//end rssfilter_clean_title()

  private static function rssfilter_format_title($str)
  {
    if ($str == "")
    {
      return "";
    }

    $str = self::rssfilter_clean_title($str);
    $str = str_replace(" ", "-", $str);

    return trim($str);
  }

//end rssfilter_format_title()

  public static function convertlinks($str)
  {
    preg_match_all("/(<([\w]+)[^>]*>)(.*)(<\/\\2>)/", $str, $matches, PREG_SET_ORDER);

    foreach ($matches as $val)
    {
      $tmp = $val[0];
      $tmp2 = str_ireplace("</a>", "", $tmp);
      $tmp2 = str_ireplace("<a", "", $tmp2);
      $tmp2 = str_ireplace('target="_new"', "", $tmp2);
      $tmp2 = str_ireplace('target="_blank"', "", $tmp2);
      $tmp2 = str_ireplace('href=', "[", $tmp2);
      $tmp2 = str_replace('>', "]", $tmp2);
      $tmp2 = str_replace('"', '', $tmp2);
      $str = str_replace($tmp, $tmp2, $str);
    }
    return $str;
  }

  public static function dedupelinks($str)
  {
    preg_match_all("/\[(.+?)\]\s?([\w?=&~.:;,'\"\/#-]+)/", $str, $matches);

    $pos = 0;
    foreach ($matches[1] as $value)
    {
      $label = '';
      $label = $matches['2'][$pos];

      $label_tmp = trim($label);
      $value_tmp = trim($value);
      $label_tmp = strtolower($label_tmp);
      $value_tmp = strtolower($value_tmp);

      if ($label_tmp == $value_tmp)
      {
        $str = str_replace("[$value]", '', $str);
      }
      $pos++;
    }

    $str = preg_replace("/\s{2,}(\[?http:)/i", ' \1', $str);

    return $str;
  }

}

?>
