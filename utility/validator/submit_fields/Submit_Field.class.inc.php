<?php

include_once("/var/www/script-repository/lib/classes/article/problem_detection/IssueDetector.class.inc");
include_once("/var/www/script-repository/lib/classes/article/problem_detection/SuggestionDetector.class.inc");
include_once("/var/www/script-repository/lib/classes/article/problem_detection/Autocorrector.class.inc");

abstract class Submit_Field
{
	private $common_messages = array(
	/*
		"common branding errors" => "Some common branding errors were found in the FIELD of your article. These will be fixed by the Editor.  <i>If these are intentional please use a bold or italize tag and they will be left as-is .</i>",
		"common acronym errors" =>  "Some common acronym errors were found in the FIELD of your article. These will be fixed by the Editor.  <i>If these are intentional please use bold or italize tag and they will be left as-is.</i>",
		"common spelling errors" => "Some common spelling errors were found in the FIELD of your article. These will be fixed by the Editor.  <i>If these are intentional please use bold or italize tag and they will be left as-is.</i>",
	*/);

	protected static $min_word_count = 0;
	protected static $max_word_count = 0;

	private $issues;

	protected $messages;

	protected $article;

/*	protected $content;
	protected $author;
	protected $member;
	protected $coauthor;
	*/
	protected $issue_checks = array("blank");
	protected $suggestion_checks = array();// array("common spelling errors","common branding errors","common acronym errors");
	protected $autocorrections = array();//array("common spelling errors","common branding errors","common acronym errors");

    public function __construct(Article $article)//$content)
	{
		$this->article = $article;
		//$this->content = $content;

		//$this->member = $member;
		//$this->author = $author;
		//$this->coauthor = $coauthor;
	}

    abstract function name();

    public function get_authors()
    {

  	  	return $this->article->get_member()->get_authors();


    }

    function getArticle()
    {
    	return $this->article;
    }

    function get_article_id()
    {
    	return $this->article->id;
    }

    function get_value()
    {
    	if($this->article&&!isset($this->content))
		{
			$name = strtolower($this->name());
			if($name == "resource")
				$name = "sig";
			$this->content = $this->article->$name;
    	}
		return $this->content;
    }

    public function get_author_name()
    {

    	return $this->article->get_author()->author;
    }

    public function get_coauthor_name()
	{
		if($this->article->get_coauthor())
			return $this->article->get_coauthor()->author;
		return null;
	}


    function get_message($issue,$replacements=null)
   	{
   		$issue = str_replace("_"," ",$issue);
   		//echo "getmessage: $issue";
		if(in_array($issue,array_keys($this->messages)))
		{
	   		$message = $this->messages[$issue];

		}
	   	else
	   		$message = str_replace("FIELD",$this->name(),$this->common_messages[$issue]);
   		foreach((array)$replacements as $key => $value)
   		{

   			$message = str_replace($key,$value,$message);

   		}

   		return $message;// $this->messages[$issue];
   	}


     /*
     * Issues with the field that prevent it from being submitted.
     */
   	function get_issues()
    {
    	$this->issues = array();
    	//echo "get issues....$this " . count($this->issue_checks) ;
    	foreach($this->issue_checks as $issue)
		{
			//echo "Looking for issue: $issue<br>";
			$issue = str_replace(" ","_",$issue);
			$result = call_user_func(array('IssueDetector',$issue),$this);

			if($result)
			{
				if(is_array($result))
				{
					foreach($result as $row)
					{
						$this->issues[] = $row;
					}
					//$this->issues[] = array_merge($this->issues,$result);
				}
				else
					$this->issues[] = $result;
			}
		}

		return $this->issues;

    }

    /*
     * Issues with the field that we suggestion be fixed to speed up approval but could have false positives
     *
     */
    function get_suggestions()
    {
    	$this->suggestion = array();
    	foreach($this->suggestion_checks as $suggestion)
		{

			$suggestion = str_replace(" ","_",$suggestion);
			//echo "suggestion: $suggestion<br>";
			$result = call_user_func(array('SuggestionDetector',$suggestion),$this);
			if($result)
			{
				if(is_array($result))
				{
					foreach($result as $row)
					{
						if(!is_array($row))
						{

							$this->suggestions[] = array("message" => $row);
						}
						else
							$this->suggestions[] = $row;
			//			$this->issues[] = $row;
					}
					//$this->issues[] = array_merge($this->issues,$result);
				}
				else
					$this->suggestions[] = array("message" => $result);




			}
		}

		return $this->suggestions;


    }




    /*
     * Issues that we fix to speed up approval.
     *
     */
    function autocorrect()
    {
    	$this->get_value();
    	//$this->autocorrections = array();
    	foreach($this->autocorrections as $autocorrection)
		{

			$autocorrection = str_replace(" ","_",$autocorrection);
			//echo "autocorrect: $autocorrection<br>";
			$this->content =call_user_func(array('Autocorrector',$autocorrection),$this);

		}
		return $this->content;
    }

    public function get_restricted_words($id=FALSE) //need to be able to pass member id to the restricted words class
    {
    	include_once("/var/www/script-repository/lib/classes/article/problem_detection/Restricted_Words.php");

    	return Restricted_Words::words($id);
    }

    public function word_count()
    {
    	include_once('/var/www/script-repository/wordcount.php');

  		return wordcount($this->get_value());
    }

    function get_maximum_words()
    {
    	return self::$max_word_count;
    }
	function get_minimum_words()
	{
		return self::$min_word_count;
	}


    //copied from (original) submit.php
	public static function link_count($str,$autolink=true)
	{
		if($autolink)
			$str = autolink($str);
		$links = array();
		$count = preg_match_all("/<a[^>]+href=([\"']?)([^\\s\"'>]+)\\1/is", $str, $matches, PREG_SET_ORDER);
		return count($matches);


	}

	public function anchorWordCheck($anchorLimit) //Anchor Limit is when it becomes an error (beyond the max)
	{
		$result = IssueDetector::excessive_anchor_text($this,$anchorLimit);
		$issues = array();
		if($result) {
			if(is_array($result)) {
				foreach($result as $row) {
					$issues[] = $row;
				}
			} else
				$issues[] = $result;
		}
		return $issues;
	} 


}
?>
