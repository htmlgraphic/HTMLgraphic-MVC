<?php
/*
 * Created on Jan 21, 2009
 *
 *
 */

include_once("Submit_Field.class.inc");

class Keywords extends Submit_Field
{
	protected $messages = array(
		"blank" => "Please enter some keywords in the keyword field that are related to your article. See our Suggested Keywords for optimal choices.<br>",
		"html" => "HTML Detected.  Please remove any HTML in the contents.<br>",
		"javascript" => "Java Script Detected in Keywords of article.  Please remove any Scripting from the Keywords of the article.<br>",
		"php" => "PHP Script Detected in Keywords field.  Please remove any Scripting from the Keywords field of the article.<br>",
		"restricted words" => "Sorry, but it appears as though your Keywords contains a restricted word or phrase of RESTRICTED_WORD.  Please re-word your keywords to not contain this word or phrase.<br>");

	protected $issue_checks = array("blank","html","javascript","php","restricted words");
	
	//ignore spelling mistakes, they could still be useful keywords.
	protected $autocorrections = array(
	//"common branding errors",
	//"common acronym errors"
			"trim spaces"
		);
	/*
	protected $suggestion_checks = array("common branding errors","common acronym errors");
	*/


	public function name()
    {
    	return get_class();
    }
}

?>
