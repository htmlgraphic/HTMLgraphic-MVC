<?php

class SanitizeText
{

  static function sanitize($text, $field = null, $gedit = true)
  {
    $text = self::wysi_convert($text);
    $text = self::CleanWord($text, 1, 1);

    $text = self::remove_p_tags($text);

    if ($field == "body" && $gedit)
    {
      $text = str_replace("<br />", "\n", $text);
      $text = htmlentities($text, ENT_QUOTES, 'UTF-8');
      $text = self::decode_entities($text);
    }
    else
    {

      $text = htmlentities($text, ENT_QUOTES, 'UTF-8');
      $text = self::chars_encode($text);
      $text = self::chars_decode($text);
      $text = self::decode_entities($text);
    }

    $text = self::wysi_convert($text);

    if ($field == "summary")
    {
      $text = str_replace("\r\n", " ", $text);
      $text = str_replace("\n", " ", $text);
      $text = str_replace("\r", " ", $text);
    }


    $text = self::wysi_convert($text);

    return trim($text);
  }

  static function CleanWord($html, $bIgnoreFont, $bRemoveStyles)
  {
    $CleanWordKeepsStructure = 1;
    // fix up ol/ul alternatives
    $html = preg_replace('/(<ol)\s*.*?(>)/i', '\\1\\2', $html);
    $html = preg_replace('/(<ul)\s*.*?(>)/i', '\\1\\2', $html);

    //whatever the hell these things are
    $html = preg_replace('/<o:p>\s*<\/o:p>/', '', $html);
    $html = preg_replace('/<o:p>.*?<\/o:p>/', '&nbsp;', $html);

    // Remove mso-xxx styles.
    $html = preg_replace('/\s*mso-[^:]+:[^;"]+;?/i', '', $html);

    // Remove margin styles.
    $html = preg_replace('/\s*MARGIN: 0cm 0cm 0pt\s*;/i', '', $html);
    $html = preg_replace('/\s*MARGIN: 0cm 0cm 0pt\s*"/i', "\"", $html);

    $html = preg_replace('/\s*TEXT-INDENT: 0cm\s*;/i', '', $html);
    $html = preg_replace('/\s*TEXT-INDENT: 0cm\s*"/i', "\"", $html);

    $html = preg_replace('/\s*TEXT-ALIGN: [^\s;]+;?"/i', "\"", $html);

    $html = preg_replace('/\s*PAGE-BREAK-BEFORE: [^\s;]+;?"/i', "\"", $html);

    $html = preg_replace('/\s*FONT-VARIANT: [^\s;]+;?"/i', "\"", $html);

    $html = preg_replace('/\s*tab-stops:[^;"]*;?/i', '', $html);
    $html = preg_replace('/\s*tab-stops:[^"]*/i', '', $html);

    // Remove FONT face attributes.

    if ($bIgnoreFont)
    {
      $html = preg_replace('/\s*face="[^"]*"/i', '', $html);
      $html = preg_replace('/\s*face=[^ >]*/i', '', $html);

      $html = preg_replace('/\s*style="[^"]*"/i', '', $html);
      $html = preg_replace('/\s*style=[^ >]*/i', '', $html);

      $html = preg_replace('/\s*color="[^"]*"/i', '', $html);
      $html = preg_replace('/\s*color=[^ >]*/i', '', $html);

      $html = preg_replace('/\s*size="[^"]*"/i', '', $html);
      $html = preg_replace('/\s*size=[^ >]*/i', '', $html);

      $html = preg_replace('/\s*FONT-FAMILY:[^;"]*;?/i', '', $html);
    }

    // Remove Class attributes
    $html = preg_replace('/<(\w[^>]*) class=([^ |>]*)([^>]*)/i', "<\\1\\3", $html);

    // Remove styles.

    if ($bRemoveStyles)
      $html = preg_replace('/<(\w[^>]*) style="([^\"]*)"([^>]*)/i', "<\\1\\3", $html);

    // Remove empty styles.
    $html = preg_replace('/\s*style="\s*"/i', '', $html);

    $html = preg_replace('/<SPAN\s*[^>]*>\s*&nbsp;\s*<\/SPAN>/i', '&nbsp;', $html);

    $html = preg_replace('/<SPAN\s*[^>]*><\/SPAN>/i', '', $html);

    // Remove Lang attributes
    $html = preg_replace('/<(\w[^>]*) lang=([^ |>]*)([^>]*)/i', "<\\1\\3", $html);

    $html = preg_replace('/<SPAN\s*>(.*?)<\/SPAN>/i', "\\1", $html);

    $html = preg_replace('/<FONT\s*>(.*?)<\/FONT>/i', "\\1", $html);

    //<meta((\n|.)*?)</meta>$l
    //$html = ereg_replace('<meta((\n|.)*?)<\/meta>$', "\\1", $html);
    $html = preg_replace('/<meta(.*?)\/>/i', "", $html);
    $html = preg_replace("/<link href=\"file(.*?)\/>/i", "", $html);

    $html = preg_replace("/<style (.*?)>/i", "", $html);


    // Remove XML elements and declarations
    $html = preg_replace('/<\\?\?xml[^>]*>/i', '', $html);

    // Remove Tags with XML namespace declarations: <o:p><\/o:p>
    $html = preg_replace('/<\/?\w+:[^>]*>/i', '', $html);

    // Remove comments [SF BUG-1481861].
    //$html = preg_replace('/<\!--.*?-->/', '', $html);
    // modified to catch comments across several lines...
    $html = preg_replace('/<\!--(\n|.)*?-->/', '', $html);

    $html = preg_replace('/<(U|I|STRIKE)>&nbsp;<\/\1>/', '&nbsp;', $html);

    $html = preg_replace('/<H\d>(.*?)<\/H\d>/i', "\\1", $html);

    // Remove "display:none" tags.
    $html = preg_replace('/<(\w+)[^>]*\sstyle="[^"]*DISPLAY\s?:\s?none(.*?)<\/\1>/i', '', $html);

    // Remove language tags
    $html = preg_replace('/<(\w[^>]*) language=([^ |>]*)([^>]*)/i', "<\\1\\3", $html);

    // Remove onmouseover and onmouseout events (from MS Word comments effect)
    $html = preg_replace('/<(\w[^>]*) onmouseover="([^\"]*)"([^>]*)/i', "<\\1\\3", $html);
    $html = preg_replace('/<(\w[^>]*) onmouseout="([^\"]*)"([^>]*)/i', "<\\1\\3", $html);

    $html = preg_replace('/<FONT\s*>(.*?)<\/FONT>/i', "\\1", $html); ### repeat 2nd time to make sure

    if ($CleanWordKeepsStructure)
    {
      // The original <Hn> tag send from Word is something like this: <Hn style="margin-top:0px;margin-bottom:0px">
      $html = preg_replace('/<H(\d)([^>]*)>/i', '<h$1>', $html);

      // Word likes to insert extra <font> tags, when using MSIE. (Wierd).
      $html = preg_replace('/<(H\d)><FONT[^>]*>(.*?)<\/FONT><\/\1>/i', "<\\1>\\2<\/\\1>", $html);
      $html = preg_replace('/<(H\d)><EM>(.*?)<\/EM><\/\1>/i', "<\\1>\\2<\/\\1>", $html);

      $html = preg_replace('/<([^\s>]+)(\s[^>]*)?>\s*<\/\1>/', '', $html);
    }
    else
    {
      $html = preg_replace('/<H1([^>]*)>/i', '<div$1><b><font size="6">', $html);
      $html = preg_replace('/<H2([^>]*)>/i', '<div$1><b><font size="5">', $html);
      $html = preg_replace('/<H3([^>]*)>/i', '<div$1><b><font size="4">', $html);
      $html = preg_replace('/<H4([^>]*)>/i', '<div$1><b><font size="3">', $html);
      $html = preg_replace('/<H5([^>]*)>/i', '<div$1><b><font size="2">', $html);
      $html = preg_replace('/<H6([^>]*)>/i', '<div$1><b><font size="1">', $html);

      $html = preg_replace('/<\/H\d>/i', '<\/font><\/b><\/div>', $html);

      // Transform <P> to <DIV>
      $re = ('/(<P)([^>]*>.*?)(<\/P>)/i'); // Different because of a IE 5.0 error
      $html = preg_replace($re, '<div$2<\/div>', $html);

      // Remove empty tags (three times, just to be sure).
      // This also removes any empty anchor
      $html = preg_replace('/<([^\s>]+)(\s[^>]*)?>\s*<\/\1>/', '', $html);
      $html = preg_replace('/<([^\s>]+)(\s[^>]*)?>\s*<\/\1>/', '', $html);
      $html = preg_replace('/<([^\s>]+)(\s[^>]*)?>\s*<\/\1>/', '', $html);
    }

    return $html;
  }

  static function remove_p_tags($str)
  {
    //print "\n\n<strong>String before remove_p_tags()</strong> : <pre>$str</pre><br><br>\n\n";
    $patterns[0] = '/<[\s]?p[\s]?>/i';
    $patterns[1] = '/<[\s]?\/p[\s]?>/i';
    $replacements[0] = '';
    $replacements[1] = "\n"; //"\n\n";
    $str = preg_replace($patterns, $replacements, $str);
    #########
    $patterns[0] = '/<[\s]?span[\s]?>/i';
    $patterns[1] = '/<[\s]?\/span[\s]?>/i';
    $replacements[0] = '';
    $replacements[1] = "";
    $str = preg_replace($patterns, $replacements, $str);
    #########
    /*
      $patterns[0] = '/<[\s]?div[\s]?>/i';// */
    /*
      $patterns[0] = '/<[\s]?div[^>]*[\s]?>/i';// */
    ///*
    $patterns[0] = '/<div[^>]*>/i';//*/
    $patterns[1] = '/<[\s]?\/div[\s]?>/i';
    $replacements[0] = '';
    $replacements[1] = "";
    $str = preg_replace($patterns, $replacements, $str);
    //print "<strong>String After remove_p_tags()</strong> : <pre>$str</pre><br><br>\n\n";
    //<(DIV|div)[^>]*>

    return $str;
  }

//end remove_p_tags()

  static function chars_encode($string, $encodeAll = false)
  {
    // declare variables
    $chars = array(
    );
    $ent = null;

    // split string into array
    $chars = preg_split("//", $string, -1, PREG_SPLIT_NO_EMPTY);

    // encode each character
    for ($i = 0; $i < count($chars); $i++)
    {
      if (preg_match('/^(\w| )$/', $chars[$i]) && $encodeAll == false)
      {
        $ent[$i] = $chars[$i];
      }
      else
      {
        $ent[$i] = "&#" . ord($chars[$i]) . ";";
      }
    }

    if (sizeof($ent) < 1)
      return "";

    $assemble = implode("", $ent);
    #print "encode:$assemble<br>";#### translate from windows codes to ascii
    $assemble = str_replace("8216", "39", $assemble); # '
    $assemble = str_replace("8217", "39", $assemble); # '
    $assemble = str_replace("8218", "39", $assemble); # '
    $assemble = str_replace("8220", "34", $assemble); # "
    $assemble = str_replace("8221", "34", $assemble); # "
    $assemble = str_replace("8222", "34", $assemble); # "
    $assemble = str_replace("8211", "45", $assemble); # -
    $assemble = str_replace("8212", "45", $assemble); # -
    $assemble = str_replace("8249", "60", $assemble); # <
    $assemble = str_replace("8250", "62", $assemble); # >
    $assemble = str_replace("8230", "46;&#46;&#46", $assemble);

    ### replace &bull; or &#8226;  with <ul><li></li></ul>

    return $assemble;
  }

  static function chars_decode($string)
  {
    // declare variables
    $tok = 0;
    $cur = 0;
    $chars = null;
    #print "Decode:$string<br>";
    // move through the string until the end is reached
    while ($cur < strlen($string))
    {
      // find the next token
      $tok = strpos($string, "&#", $cur);

      // if no more tokens exist, move pointer to end of string
      if ($tok === false)
        $tok = strlen($string);

      // if the current char is alpha-numeric or a space
      if (preg_match("/^(\w| )$/", substr($string, $cur, 1)))
      {
        $chars .= substr($string, $cur, $tok - $cur);
      }
      // the current char must be the start of a token
      else
      {
        $cur += 2;
        $tok = strpos($string, ';', $cur);
        $chars .= chr(substr($string, $cur, $tok - $cur));
        $tok++;
      }

      // move the current pointer to the next token
      $cur = $tok;
    }

    return $chars;
  }

  static function wysi_convert($str)
  {
    $str = str_ireplace("&ldquo;", '"', $str);
    $str = str_ireplace("&rdquo;", '"', $str);
    $str = str_ireplace("&bdquo;", '"', $str);

    $str = str_ireplace("&lsquo;", "'", $str);
    $str = str_ireplace("&rsquo;", "'", $str);
    $str = str_ireplace("&sbquo;", "'", $str);

    $str = str_ireplace("&ndash;", "-", $str);
    $str = str_ireplace("&mdash;", "-", $str);

    $str = str_ireplace("&lsaquo;", "<", $str);
    $str = str_ireplace("&rsaquo;", ">", $str);

    $str = str_ireplace("&hellip;", "...", $str);
    return $str;
  }

  static function decode_entities($text, $encoding = "ISO-8859-1")
  {
    $text = html_entity_decode($text, ENT_QUOTES, $encoding);
    $text = preg_replace('/&#(\d+);/me', "chr(\\1)", $text); #decimal notation
    $text = preg_replace('/&#x([a-f0-9]+);/mei', "chr(0x\\1)", $text); #hex notation
    return $text;
  }

  static function convert_smart_quotes($string)
  {
    //utf8
    $text = str_replace(
            array("\xe2\x80\x98", "\xe2\x80\x99", "\xe2\x80\x9c", "\xe2\x80\x9d", "\xe2\x80\x93", "\xe2\x80\x94", "\xe2\x80\xa6"), array("'", "'", '"', '"', '-', '--', '...'), $string);


    return str_replace(
            array(chr(145), chr(146), chr(147), chr(148), chr(150), chr(151), chr(133)), array("'", "'", '"', '"', '-', '-', '...'), $text);
  }

  static function closeTags($text)
  {
    $patt_open = "%((?<!</)(?<=<)[\s]*[^/!>\s-]+(?=>|[\s]+[^>]*[^/]>)(?!/>))%";
    $patt_close = "%((?<=</)([^>]+)(?=>))%";
    if (preg_match_all($patt_open, $text, $matches))
    {
      $m_open = $matches[1];
      if (!empty($m_open))
      {
        preg_match_all($patt_close, $text, $matches2);
        $m_close = $matches2[1];
        if (count($m_open) > count($m_close))
        {
          $m_open = array_reverse($m_open);
          foreach ($m_close as $tag)
            $c_tags[$tag]++;
          foreach ($m_open as $k => $tag)
            if ($c_tags[$tag]-- <= 0)
              $text.='</' . $tag . '>';
        }
      }
    }
    return $text;
  }

}

?>
