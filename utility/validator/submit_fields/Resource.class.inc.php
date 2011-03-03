<?php

include_once("Submit_Field.class.inc");

class Resource extends Submit_Field
{
	protected static $max_word_count = 300;
	protected static $anchor_limit = 6; //Anchor Limit is when it becomes an error (beyond the max)

	protected $messages = array(
		"known affilate link" => "Your Body appears to contain a link to a known affiliate site <b>AFFILIATE</b>. Affiliate links are not allowed<br>",	
		"javascript" => "Java Script Detected in Sig of article.  Please remove any Scripting from the Sig of the article.<br>",
		"php" => "PHP Script Detected in Sig/Resource of article.  Please remove any Scripting from the Sig/Resource of the article.<br>",
		"too long" => "Author Bio - SIG - Resource Box cannot be longer than EXPECTED_LENGTH words.<br>",
		"alt author name" => "Your Resource / Sig contains a reference to an Alternate Author that is neither Author nor Co-Author of this article.<br>",
		"restricted words" => "Sorry, but it appears as though your Sig contains a restricted word or phrase of RESTRICTED_WORD.  Please re-word your sig to not contain this word or phrase.<br>",
		"excessive anchor text" => "It appears that your anchor text link length is more than maximum of ANCHOR_LIMIT words allowed:<br> <b>ANCHOR</b><br>",
		"meta" => "It appears you are attempting to use a Meta Tag within your article. Meta Tags are not allowed.<br>",
		"url blacklisted" => "Sorry, the URL you have selected (URL_BLACKLISTED) cannot be used in your article because it's on one or more RBL's (Real-time BlackLists). Please remove this URL from your article."
	);

	protected $issue_checks = array(
		"known affilate link",
		"meta",
		"javascript",
		"php",
		"too long",
		"restricted words",
		"excessive anchor text",
		"url blacklisted"
	);
   
	protected $suggestion_checks = array(
		//"common spelling errors",
		//"common branding errors",
		//"common acronym errors",
		);
   
	protected $autocorrections = array(
		//"common spelling errors",
		//"common branding errors",
		//"common acronym errors"
		"link spacing",
		"trim spaces"
		);
	
	public function name()
    {	
    	return get_class();
    }	
    
    function get_maximum_words()
    {
    	return self::$max_word_count;
    }
    
    function get_anchor_limit()
    {
    	return self::$anchor_limit;
    }
    
	function get_known_affiliate_links()
	{
		return array("hop.clickbank.net");
	}
}
?>