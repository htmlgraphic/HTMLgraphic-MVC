<?php
/*
 * 
 * This is an abstract class for common functionality among the text field type detectors.
 * 
 */



abstract class TextDetector
{

	
	protected $issues;

	protected static $urls_allowed;
	protected static $min_words = 0;
	protected static $max_words = 0;
	protected $value;
	
	private static $white_list = array('83051'=>'for dummies','126598'=>'for dummies','5953'=>'4dummies','176998'=>'leading under pressure','53643'=>'hometips','66263'=>'hometips','54349'=>'hometips');
	private static $black_list = null;
	
    public function __construct($value,$restricted) 
    {
    	
    	$this->value = $value;
    	$this->restricted = $restricted;
    	
    	
    	$this->issues = array();
    }
    
    abstract protected function className();
    
    public function issues()
    {
    	
    	$interfaces = class_implements($this->className());
    	
    	$this->issues = array();
    
    	if(count($interfaces) != 0 && in_array('CannotBeBlank', $interfaces))
    	{
    			
    		$this->is_empty();
    	}
    	
    	if(count($interfaces) != 0 && in_array('GeneralProblems', $interfaces))
    	{
    		$this->has_restricted($this->value,$this->restricted);
  		
  			$this->contains_javascript();
    	
   		 	$this->contains_php();
    	}
    		
    	if(count($interfaces) != 0 && in_array('LengthCheck', $interfaces))
    	{
    		$this->too_short();
    		$this->too_long();
    	}
    	
    	if(count($interfaces) != 0 && in_array('HTMLNotAllowed',$interfaces))
    	{
    		//echo "<br>check for html allowed: " . get_class($this);
    		$this->contains_html();
    	}
    	//else
    	//	echo "<br>dont then..." . get_class($this);
    	
   
    	$this->additional_checks();
    
    	return $this->issues;	
    }
    
    
    
    //over-ride to add checks that apply to only one field type.
    public function additional_checks()
    {
    }
    

    
    public function meets_link_requirement()
    {
    	return true;
    }
 
    
    protected function is_empty()
    {
    	if($this->value == null || strlen($this->value) == 0)
    		$this->issues[] = $this->empty_value_message();
    	
    }
    
    protected function contains_html()
    {
    	
    	preg_match("/<(.*)>(.*)<(.*)>/",$this->value,$result);
		if(count($result))
		{
			$this->issues[] = $this->html_detected_message();
		}
		
    }
    
    protected function contains_javascript()
    {
		#### check for java script content in body
		preg_match("/(?<!<xmp>[\r\n])<script.*?>/si",$this->value,$result);
		if(count($result))  
			$this->issues[] = $this->javascript_detected_message();
		
    }
    
    protected function contains_php()
    {
    	
    	##### check for php script content in body
		preg_match("/(?<!<xmp>[\r\n])<\?php.*\?>/si",$this->value,$result);
		if(count($result)) 
			$this->issues[] = $this->php_detected_message();
		
			
	
    }
    
    private function word_count()
    {
    	include_once('/var/www/script-repository/wordcount.php');
    	
  		return wordcount($this->value);  	
    }
    
    protected function too_long()
    {
    	$max = $this->maximum_words_allowed();
    	$count = $this->word_count();
    	if($count > $max)
    	{
    		$over = $count -$max;
    		$this->issues[] = $this->max_word_count_exceeded_message($count,$max);
    		
    	}
    }
    
    protected function too_short()
    {
    	
    	$min = $this->minimum_words_allowed();
    	$count = $this->word_count();
    	if($count < $min)
    	{
    		$over = $count - $min;
    		$this->issues[] = $this->min_word_count_not_met_message($count,$min);
    		
    	}
    }
    
    //copied from submit.php
    private static function autoLink($str)
    {
		// link all urls and email
		$str2 = preg_replace("/(?<!http:\/\/)(?<!https:\/\/)(?<!www\.)((\bwww)[\w?=&~.%+\/\#:;!_-]+)/","http://\\1",$str);
		$str2 = preg_replace("/(?<!<a href=\")(?<!b>)(?<!\")(?<!\">)((http|https|ftp):\/\/[\w?=&~.%+\/\#:;!_-]+)/","<a target=\"_new\" href=\"\\1\">\\1</a>",$str2);
		$str2 = preg_replace("/((?<!<a href=\"mailto:)(?<!\">)(?<=(>|\s))[\w_-]+@[\w_.-]+[\w]+)/","<a href=\"mailto:\\1\">\\1</a>",$str2);   
		
		return $str2;
	}
	//copied from submit.php
	public static function link_count($str,$autolink=true) 
	{
		if($autolink)
			$str = TextDetector::autolink($str);
		$links = array();
		$count = preg_match_all("/<a[^>]+href=([\"']?)([^\\s\"'>]+)\\1/is", $str, $matches, PREG_SET_ORDER);
		return count($matches);
		
		
	}
    
    public static function url_count($value)
    {
    	
		$link_data = Text_Verifier::autoLink($value);
		return Text_Verifier::link_count($link_data);	
    	
    }   
   
    private function has_restricted($value,$stops)
    {	
		foreach($stops as $stopword)
		{
		
    		if($stopword != '' && preg_match("/$stopword/i",$value))
 					$this->issues[] = $this->contains_restricted_word_message($stopword);
 					
		}
		
	}
    
   
}
?>