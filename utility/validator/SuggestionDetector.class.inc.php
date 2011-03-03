<?php

class SuggestionDetector
{
	
	
	public static function duplicate_title(Submit_Field $field)
    {
		    	
    	$title=trim($field->get_value());

		if(strlen($title) < 3)
		{ 
			return; 
		}

		$title=strtolower($title);
		$title=md5($title);
		
		$db = DatabaseFactory::ea_casm_db();
		$query = "SELECT `id` FROM `casm_title_index` WHERE `phrase_key`='$title' LIMIT 1";
		
		$result = $db->query($query);
		
		if($result)
		{
			$row=mysqli_fetch_object($result);
			if($row)
			{
				//unless it's the same article, it's a duplicate.
				
			  
				if($row->id != $field->get_article_id())
					return $field->get_message(__FUNCTION__);
			}
		}
		
		
    	
    }
    
    static function common_word_errors(Submit_Field $field,$type,$function)
    {
    	if(strlen($field->get_value()) > 10)
    	{
	    	$results = Common_Word_Errors::has_common($field->get_value(),$type);
	    	if(count($results))
	    	{
	    		//echo "results(before): " . count($results) . "<br>";
	    		//$results = array_unique($results);
	    		//echo "results(after): " . count($results) . "<br>";
	    		$found = array();
	    		$fixes = array();
	    		foreach($results as $result)
	    		{
	    			//echo "looping through: " . $result->error . "<br>";	
	    			$found[] = $result->error;
	    			$fixes[] = $result->correction;
	    		}
	    		
	    		
	    		return array(array("message" => $field->get_message($function), "replace" => $found, "fix" => $fixes));
	      	}
    	}
    }
    
    static function common_spelling_errors(Submit_Field $field)
    {
    	return self::common_word_errors($field,Common_Word_Errors::SPELLING,__FUNCTION__);
    	
    }
    
    static function common_branding_errors(Submit_Field $field)
    {
    	return self::common_word_errors($field,Common_Word_Errors::BRANDS,__FUNCTION__);
    
    }
    
    static function common_acronym_errors(Submit_Field $field)
    {
    	return self::common_word_errors($field,Common_Word_Errors::ACRONYMS,__FUNCTION__);
    	
    }
    
    static function body_fill(Submit_Field $field)
    {
		preg_match('/^(.*?[\.?!])(.*?[\.?!])/s', $field->get_value(), $matches); ### grab 2 sentences of summary if possible
		
		if (!$matches[2])
		{
			//only display if there is something in the body to use.
			if(strlen($field->get_body()))
				return $field->get_message(__FUNCTION__);
		}
    }

	public static function thin_content_keywords(Submit_Field $field)
	{
		$thin_content = array( "penis", "enlargement", "acai", "forex", "get back your ex", "dating");

		foreach($thin_content as $word)
		{
			if(preg_match("/\b$word\b/i",$field->get_value()))
    			$found[] = $field->get_message(__FUNCTION__,array("KEYWORD" => $word));
		}

		return $found;
	}

	public static function suspected_affilate_link(Submit_Field $field)
    {
    	
        	
    	include_once("/var/www/script-repository/autoLink.php");
    		
    	$affiliates = $field->get_suspected_affiliate_links();
    
   
    	$str = $field->get_value();
		$str = autolink($str);
		$found = null;
	
		$str = $field->get_value();
		$str = autolink($str);
		
		preg_match_all("/<a[^>]+href=([\"']?)([^\\s\"'>]+)\\1/is", $str, $matches, PREG_SET_ORDER);
		
		foreach($matches as $match)
		{
			$url = $match[2];
			//$position = strpos($field->get_value(),$match[2]);
			//echo "checking...$url...<br>";
			foreach($affiliates as $affiliate)
			{
				$position = stripos($url,$affiliate);//"wealth-to-all.com");//$affiliate,$url);
			
				if($position !== false)
				{
					//echo "matched: $affiliate<br>";	
					$found[] = $field->get_message(__FUNCTION__,array("AFFILIATE" => $affiliate));
					break;
				}
			}
			
			
			
		}
	/*
		foreach($affiliates as $affiliate)
		{
				
			$position = stripos($field->get_value(),$affiliate);
			
			if($position !== false)
			{
				
				$found[] = $field->get_message(__FUNCTION__,array("AFFILIATE" => $affiliate));
					
			}
		}
		*/
		return $found;
    }
	
	
}

?>
