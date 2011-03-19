<?php

class Link
{

  private static $HTMLDOCTYPE = "<!DOCTYPE html PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\" \"http://www.w3.org/TR/REC-html40/loose.dtd\">\n";

  static function addOutLinks($str)
  {
    $dom = new DOMDocument();
    //libxml_use_internal_errors(true);
    $dom->loadHTML("<html><body>$str</body></html>");
    $dom->normalizeDocument();

    $xpath = new DOMXPath($dom);
    $hrefs = $xpath->query("/html/body//a");

    if ($hrefs->length)
    {
      for ($i = 0; $i < $hrefs->length; $i++) //foreach will not work with xpath
      {
        $content = str_replace(array('\n', '\r'), '', $hrefs->item($i)->textContent);
        $normalized_content = self::normalize($content);
        $url = $hrefs->item($i)->getAttribute('href');
        $normalized_url = self::normalize($url);

        if ($normalized_content == $normalized_url)
          $text = new DOMText("[$normalized_url]");
        else
          $text = new DOMText("$content [$normalized_url]");

        $hrefs->item($i)->parentNode->replaceChild($text, $hrefs->item($i));
      }
    }

    return rtrim(str_replace(array(self::$HTMLDOCTYPE, "<html><body>", "</body></html>"), '', $dom->saveHTML()));
  }

  private function normalize($link)
  {
    return strtolower(rtrim(trim($link), '/'));
  }

}

?>