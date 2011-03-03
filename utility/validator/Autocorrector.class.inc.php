<?php

class Autocorrector
{
	function common_spelling_errors(Submit_Field $field)
	{
		if (strlen($field->get_value()) > 10)
		{
			return Common_Word_Errors::repair_common($field->get_value(), Common_Word_Errors::SPELLING);
		}
		return $field->get_value();
	}

	function common_branding_errors(Submit_Field $field)
	{
		//echo "reair common branding errors<br>";
		if (strlen($field->get_value()) > 10)
		{

			return Common_Word_Errors::repair_common($field->get_value(), Common_Word_Errors::BRANDS);
		}
		return $field->get_value();
	}

	function common_acronym_errors(Submit_Field $field)
	{
		//echo "repair common acronyms<br>";
		if (strlen($field->get_value()) > 10)
		{
			return Common_Word_Errors::repair_common($field->get_value(), Common_Word_Errors::ACRONYMS);
		}
		return $field->get_value();
	}

	function title_uppercase(Submit_Field $field)
	{

		$title = $field->get_value();

		$pieces = explode("-", $title);
		foreach ($pieces as &$piece)
		{
			$temp = strtolower($piece);
			$piece = myucwords($temp);
		}
		$title = implode("-", $pieces);

		return $title;
	}

	function replace_colons_with_dashes(Submit_Field $field)
	{
		return preg_replace("/(?<![0-9])(\s?[:;]\s?)(?![0-9])/i", ' - ', $field->get_value());
	}

	private static function first_three_lines_from_content($body)
	{
		$upperwl = 100;
		preg_match('/(.*?[\.?!]){3}/ms', $body, $matches2);
		$newsum = $matches2[0];
		$newsum = preg_replace('/(\n|\r)/', ' ', $newsum);
		$words = explode(' ', $newsum);
		if (count($words) >= $upperwl)
		{
			$newsum = '';
			$inc = 0;
			foreach ($words as $word)
			{
				$newsum .= "$word ";
				$inc++;
				if ($inc >= $upperwl)
				{
					break;
				}
			}
			$newsum .= "...";
		}
		//$newsum=CleanWord($newsum,1,1);
		$newsum = sanitize($newsum);
		$newsum = preg_replace('/<[^<]+>/', '', $newsum); ## summary cannot have any tags in it.
		return $newsum;
	}

	public function body_fill(Submit_Field $field)
	{
		########## check for valid summary, if only 1 sentence, grab first 3 sentences of body
		$upperwl = 100;

		preg_match('/^(.*?[\.?!])(.*?[\.?!])/s', $field->get_value(), $matches); ### grab 2 sentences of summary if possible
		#	$matches[1]=trim($matches[1]);

		if (!$matches[2])// || $force)
		{ ## ## is the second one there?
			return str_replace("&quot;", "\"", self::first_three_lines_from_content($field->get_body()));
		}
		return $field->get_value();
	}

	/*
	 * This has potential to do great harm
	 */
	public function purify(Submit_Field $field)
	{
		return $field->get_value();
		echo "let's purify this quick<br>";
		echo $field->get_value() . "<br>";
		require_once("/var/www/script-repository/lib/classes/htmlpurifier-3.2.0/library/HTMLPurifier.auto.php");



		$config = HTMLPurifier_Config::createDefault();

		$config->set('HTML', 'AllowedElements', 'b,i,em,u,br,pre,blockquote,xmp,ol,ul,li,a'); //'[href|target]');
		$config->set('HTML', 'AllowedAttributes', 'a.href,a.target');
		$config->set('Attr', 'AllowedFrameTargets', '_new');

		$config->set("AutoFormat", "RemoveEmpty", true);
		$config->set("URI", "AllowedSchemes", "http,https,mailto");
		$config->set("Core", "EscapeNonASCIICharacters", true);
		$config->set('HTML', 'DefinitionID', 'Link validation');

		/*
		  $def = &$config->getHTMLDefinition(true);
		  $a =&$def->addBlankElement('a');
		  // $a->attr['rel'] = 'Enum#nofollow';
		  $a->attr['target'] = 'Enum#_new';


		  $a->attr_transform_post[] = new HTMLPurifier_AttrTransform_AValidator();
		 */


		$config->set('AutoFormat', 'AutoParagraph', true);
		$purifier = new HTMLPurifier($config);

		$pure = $purifier->purify($field->get_value());
		//echo "given<br>{$field->get_value()}<br>";
		//echo "<br>$pure<br>";

		return $pure;
	}

	public function trim_spaces(Submit_Field $field)
	{
		$terminal_punctuation = array(",", ".", "!", "?", ":", ";");
		$temp = str_replace("&nbsp;", ' ', $field->get_value());
		//Spaces consolidation
		while (strstr($temp, "  "))
		{
			$temp = str_replace("  ", " ", $temp);
		}
		foreach ($terminal_punctuation as $punct)
			$temp = str_replace(" " . $punct, $punct, $temp);
		return trim($temp);
	}

	public function link_spacing(Submit_Field $field)
	{
		$str = $field->get_value();
		$str = preg_replace("/(?<![\s])<a/", " <a", $str); //"<a" becomes " <a"
		$str = preg_replace("/((\s?)(<\/a>))+/", "</a> ", $str); //"</a></a>" becomes "</a>"
		return $str;
	}

	public function dash_spacing(Submit_Field $field)
	{
		$str = $field->get_value();
		$str = preg_replace("/ ?- ?/", " - ", $str); //Ensures " - "
		return $str;
	}

}
?>