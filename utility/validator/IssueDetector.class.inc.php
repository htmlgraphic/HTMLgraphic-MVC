<?php
/*
 * This is an abstract class for common functionality for detectors.
 *
 */

include_once("/var/www/script-repository/lib/classes/article/problem_detection/Common_Word_Errors.class.inc");
include_once("/var/www/script-repository/lib/classes/db/DatabaseFactory.class.inc");

class IssueDetector
{
	public static function blank(Submit_Field $field)
	{

		if (strlen($field->get_value()) == 0)
			return $field->get_message(__FUNCTION__);
	}

	public static function body_to_resource_ratio(Submit_Field $field)
	{
		$ratio = $field->get_metric_manager()->getBodyRatio();
		if ($ratio > 40)
			$found[] = $field->get_message(__FUNCTION__, array("PHRASE" => $key, "DENSITY" => $phrase['density']));
		return $found;
	}

	public static function pipe(Submit_Field $field)
	{
		preg_match("/[\|]/", $field->get_value(), $result);
		if (count($result))
		{
			return $field->get_message(__FUNCTION__);
		}
	}

	public static function image(Submit_Field $field)
	{
		if (!preg_match("/<xmp>/i", $field->get_value()))
		{
			if (preg_match("/<img/i", $field->get_value()))
				return $field->get_message(__FUNCTION__);
		}
	}

	public static function meta(Submit_Field $field)
	{
		if (!preg_match("/<xmp>/i", $field->get_value()))
		{
			if (preg_match("/<meta/i", $field->get_value()))
				return $field->get_message(__FUNCTION__);
		}
	}

	public static function author_name(Submit_Field $field)
	{

		$name = $field->get_author_name();
		//echo "***************checking for name $name\n";
		if (strlen($name) && preg_match("/$name/i", $field->get_value(), $matches))
			return $field->get_message(__FUNCTION__);
		//else
		//	echo "{$field->name()} does not contain {$field->get_value()} author name************************\n\n";
	}

	//checks if the field contains an author associated with the account that isn't author or coauthor
	public static function alt_author_name(Submit_Field $field)
	{
		$alts = $field->get_authors();

		foreach ($alts as $author)
		{
			$name = $author->author;
			//echo "looking for $name\n";
			if ($name != $field->get_author_name() && $name != $field->get_coauthor_name() && preg_match("/$name/i", $field->get_value(), $matches))
			{
				//	echo "matched it\n";
				return $field->get_message(__FUNCTION__);
			}
			//if($name == $field->get_author_name())
			//	echo "This is allowed silly\n";
			//if($name == $field->get_coauthor_name())
			//	echo "This is the coauthor...{$field->get_coauthor_name()}\n";
		}
	}

	public static function html(Submit_Field $field)
	{
		preg_match("/<(.*)>(.*)<(.*)>/", $field->get_value(), $result);

		if (count($result))
			return $field->get_message(__FUNCTION__);
	}

	public static function javascript(Submit_Field $field)
	{

		preg_match("/(?<!<xmp>[\r\n])<script.*?>/si", $field->get_value(), $result);
		if (count($result))
			return $field->get_message(__FUNCTION__);
	}

	public static function php(Submit_Field $field)
	{
		preg_match("/(?<!<xmp>[\r\n])<\?php.*\?>/si", $field->get_value(), $result);
		if (count($result))
			return $field->get_message(__FUNCTION__);
	}

	public static function requires_unique(Submit_Field $field)
	{
		$words = split(" ", $field->get_value());
		$final = array();
		foreach ($words as $word)
		{
			if (strlen(trim($word)))
				$final[] = $word;
			//else
			//	echo "too short..." . trim($word)
		}
		$final = array_unique($final);

		//echo count($final) . " found ... unique: " . count(array_unique($words)) . "...". $field->get_minimum_words() . "<br>";
		if (!(count($final) >= $field->get_minimum_words()))
			return $field->get_message(__FUNCTION__);
	}

	public static function too_short(Submit_Field $field)
	{

		$min = $field->get_minimum_words();

		$count = $field->word_count();
		if ($count < $min)
		{
			$over = $count - $min;
			return $field->get_message(__FUNCTION__, array("REQUIRED_LENGTH" => $min, "LENGTH" => $count));
		}
	}

	public static function too_long(Submit_Field $field)
	{
		$max = $field->get_maximum_words();
		if ($max == 0)
			return;
		$count = $field->word_count();
		if ($count > $max)
		{
			$over = $count - $max;
			return $field->get_message(__FUNCTION__, array("EXPECTED_LENGTH" => $max, "OVER_LIMIT" => $over, "LENGTH" => $count));
		}
	}

	public static function excessive_puncuation(Submit_Field $field)
	{

		if (strpos($field->get_value(), "??") !== false || strpos($field->get_value(), "!!") !== false)
			return $found[] = $field->get_message(__FUNCTION__);
	}

	public static function restricted_phrase(Submit_Field $field)
	{
		$restricted = $field->get_restricted_phrases();
		$found = null;
		foreach ($restricted as $word)
		{
			$escaped_word = IssueDetector::preg_quote_fixed($word);

			if ($word != '' && preg_match("/$escaped_word/i", $field->get_value()))
				$found[] = $field->get_message(__FUNCTION__, array("RESTRICTED_PHRASE" => $word));
		}
		return $found;
	}

	public static function restricted_words(Submit_Field $field)
	{
		//include_once("/var/www/script-repository/lib/classes/article/problem_detection/Restricted_Words.php");
		$restricted = $field->get_restricted_words($field->getArticle()->get_member()->get_member_id());
		$found = null;
		foreach ($restricted as $stopword)
		{
			$escaped_word = IssueDetector::preg_quote_fixed($stopword);

			if ($stopword != '' && preg_match("/\b$escaped_word\b/i", $field->get_value()))
				$found[] = $field->get_message(__FUNCTION__, array("RESTRICTED_WORD" => $stopword));
		}
		return $found;
	}

	public static function above_the_fold(Submit_Field $field)
	{
		include_once("/var/www/script-repository/autoLink.php");


		$str = $field->get_value();
		$str = autolink($str);

		preg_match_all("/<a[^>]+href=([\"']?)([^\\s\"'>]+)\\1/is", $str, $matches, PREG_SET_ORDER);

		$fold = (strlen($str) / 2.5);

		foreach ($matches as $match)
		{
			$urls[] = $match[2];
			$position = strpos($field->get_value(), $match[2]);

			if ($position !== false && $position < $fold)
			{
				return $field->get_message(__FUNCTION__, array("URL" => $match[2]));
			}
		}
	}

	public static function known_affilate_link(Submit_Field $field)
	{

		include_once("/var/www/script-repository/autoLink.php");

		$affiliates = $field->get_known_affiliate_links();

		$str = $field->get_value();
		$str = autolink($str);

		foreach ($affiliates as $affiliate)
		{

			$position = stripos($field->get_value(), $affiliate);

			if ($position !== false)
			{

				return $field->get_message(__FUNCTION__, array("AFFILIATE" => $affiliate));
			}
		}
	}

	public static function disallowed_html(Submit_Field $field)
	{

		$tidy = new tidy();

		if ($tidy->parseString("<html><body>" . $field->get_value() . "</body></html>"))
		{
			//echo "String was parsed<br>";
		}

		//$tidy->parseString(tidy_repair_string($field->get_value()));

		$tidy->cleanRepair();

		//echo "repaired: " . $tidy->body() . "<br>";
		//echo $tidy->xpath('/body/img');


		return;

		$body = $tidy->body();
		echo "body: $body\n";
		$img_nodes = $body->get_nodes(TIDY_TAG_IMG);

		// begin printing the IMG nodes
		print("<br>\n --- Begin img nodes for $uri --- <br>\n");
		// using foreach, loop through each IMG node
		foreach ($img_nodes as $img_node)
		{
			// print the current node, using htmlspecialchars() to convert
			// HTML characters to web printable format
			print("img_node=" . htmlspecialchars($img_node) . "<br>\n");
		}
		/*

		  $allowed = $field->get_allowed_tags();
		  $count = preg_match_all("/<(.|\n)*?>/i",$field->get_value(),$matches, PREG_SET_ORDER);
		  $found = null;
		  $unique = array();
		  foreach($matches as $match)
		  {
		  $matched = false;
		  foreach($allowed as $tag)
		  {
		  $open = "<$tag>";
		  $close = "</$tag>";

		  if($match[0] == $open || $close == $match[0] || substr($match[0],0,(strlen($tag)+2)) == "<$tag " )
		  {
		  // if(substr($match[0],0,(strlen($tag)+1)) == "<$tag" )
		  // 	echo "MATCHING: $match[0].....$tag.....";
		  $matched = true;
		  break;
		  }


		  }
		  if(!$matched)
		  {


		  $unique[] = $match[0];
		  $count = count($unique);
		  if($count != count(array_unique($unique)))
		  {
		  $found[] = $field->get_message(__FUNCTION__,array("TAG" => (str_replace(">","&gt;",(str_replace("<","&lt;",$match[0]))))));
		  }
		  }
		  }

		  return $found;
		 */
	}

	function dead_links(Submit_Field $field)
	{
		include_once("/var/www/script-repository/dead_link_find.php");

		$checker = new DeadLinks($field->get_value());

		$dead = $checker->detect();
		//echo "dead...." . count($dead);
		foreach ($dead as $url)
		{
			$found[] = $field->get_message(__FUNCTION__, array("URL" => $url));
		}

		return $found;
	}

	public static function phrase_density(Submit_Field $field)
	{
		foreach ($field->get_metric_manager()->getPhraseDensity(2, 2, "", true) as $key => $twoWord)
			$phrases[$key] = $twoWord;

		foreach ($field->get_metric_manager()->getPhraseDensity(2, 3, "", true) as $key => $threeWord)
			$phrases[$key] = $threeWord;

		foreach ($phrases as $key => $phrase)
		{
			if ($phrase['density'] >= 2)
			{
				$found[] = $field->get_message(__FUNCTION__, array("PHRASE" => $key, "DENSITY" => $phrase['density']));
			}
		}
		return $found;
	}

	public static function word_density(Submit_Field $field)
	{
		foreach ($field->get_metric_manager()->getKeywordDensity(6, true) as $word => $word_data)
		{
			$found[] = $field->get_message(__FUNCTION__, array("PHRASE" => $word, "DENSITY" => $word_data['density']));
		}
		return $found;
	}

	public static function one_paragraph_check(Submit_Field $field)
	{
		$paragraphs = $field->get_metric_manager()->getParagraphs();

		if (!$paragraphs || count($paragraphs) == 0)
		{
			$found[] = $field->get_message(__FUNCTION__);
		}

		return $found;
	}

	/**
	 * Checks for blacklisted urls
	 */
	public static function url_blacklisted(Submit_Field $field)
	{
		Loader::load("model", "/articles/article/formatting/ArticleLink");
		Loader::load("utility", "url/URLBlacklist");

		if ($field->name() == "Body")
			$string = html_entity_decode($field->get_value());
		else if ($field->name() == "Resource")
			$string = stripslashes($field->get_value());

		$dom = ArticleLink::getLinksInString($string);
		//error_log ( "\n" . $field->name() );

		if (count($dom))
		{
			foreach ($dom as $no => $url)
			{
				if (URLBlacklist::is_blacklisted($url))
				{
					$found[] = $field->get_message(__FUNCTION__, array("URL_BLACKLISTED" => $url));
				}
			}
		}
		return $found;
	}

	/**
	 * 	Checks for excessive anchor text within tags
	 */
	public static function excessive_anchor_text(Submit_Field $field, $outsideAnchorLimit = false)
	{
		//include_once("/var/www/script-repository/lib/classes/utility/TextManipulation.class.inc.php");

		$commonWords = array("to", "is", "in", "on", "it", "and", "at", "by", "a", "an", "of", "for", "or");

		if ($field->name() == "Body")
			$string = html_entity_decode($field->get_value());
		else if ($field->name() == "Resource")
			$string = stripslashes($field->get_value());

		if (preg_match_all("/<[\s]?a[\s]+[^>]*?href[\s]?=[\s\"']?+(.*?)[\"']?+.*?>([^<]+|.*?)?<\/a>/i", $string, $matches))
		{
			foreach ($matches[2] as $match)
			{
				$wordArray = explode(" ", $match);

				$commonWordsUsed = array();
				foreach ($commonWords as $commonWord)
				{
					$commonWordsUsed[$commonWord] = false;
				}

				foreach ($wordArray as $word)
				{
					if ($word == "")
						continue;

					//this line was modified when someone snuck seven words using these common words
					//if( !in_array(strtolower($word), array("to","is","in","on","it","and","at","by","a","an")) && count($word) )
					//	$array[] = $word;
					if (in_array(strtolower($word), $commonWords) && (!$commonWordsUsed[strtolower($word)]))
					{
						$commonWordsUsed[strtolower($word)] = true;
						continue;
					}
					if (count($word))
						$array[] = $word;
				}

				if ($outsideAnchorLimit)
					$anchor_limit = $outsideAnchorLimit;
				else
					$anchor_limit = $field->get_anchor_limit();
				if (count($array) >= $anchor_limit)
				{
					$found[] = $field->get_message(__FUNCTION__, array("ANCHOR_LIMIT" => $anchor_limit - 1, "ANCHOR" => $match));
				}

				unset($array);
			}
		}

		return $found;
	}

	public static function url_in_title(Submit_Field $field)
	{
		if (preg_match("/(([\w]+:)?\/\/)?(([\d\w]|%[a-fA-f\d]{2,2})+(:([\d\w]|%[a-fA-f\d]{2,2})+)?@)?([\d\w][-\d\w]{0,253}[\d\w]\.)+[\w]{2,4}(:[\d]+)?(\/([-+_~.\d\w]|%[a-fA-f\d]{2,2})*)*(\?(&?([-+_~.\d\w]|%[a-fA-f\d]{2,2})=?)*)?(#([-+_~.\d\w]|%[a-fA-f\d]{2,2})*)?/", $field->get_value()))
		{
			$found[] = $field->get_message(__FUNCTION__);
		}

		return $found;
	}

	/*
	 * This function is for restricted phrases, as preg_quote doesn't work on PHP 5.1
	 */
	public static function preg_quote_fixed($word)
	{
		return str_replace(
			   array(".", "+", "*", "?", "[", "^", "]", "(", ")", "{", "}", "=", "!", "<", ">", "|", ":"),
			   array("\.", "\+", "\*", "\?", "\[", "\^", "\]", "\(", "\)", "\{", "\}", "\=", "\!", "\<", "\>", "\|", "\:"), $word);
	}

	/*
	  public static function article_not_in_english(Submit_Field $field)
	  {

	  }
	 */
}
?>
