<?php

/****************************************************************************************
 * this class is the new autolinker - JPE 10.26.2009
 * give it a string and it'll clean it up, give links the correct attrs, etc
 * will output changes, by change or full article
****************************************************************************************/

error_log('AutoLinker is deprecated. Use module -> URLFormatter instead.');

class AutoLinker
{

	private $record = array();
	private $attrs = array(
		array(
			'name' => 'target',
			'value' => '_new'
		)
	);
	
	//add attributes for each link
	function addAttr($name,$value)
	{
		$this->attrs[] = array(
			'name' => $name,
			'value' => $value
		);
	}
	
	//remove an attribute
	function removeAttr($name)
	{
		if($this->attrs)
		{
			foreach($this->attrs as $k => $v)
			{
				if($v['name']==$name)
				{
					unset($this->attrs[$k]);
					return true;
				}
			}
		}
		return false;
	}
	
	//adds attributes and returns a formatted string - make true for rel="nofollow" attribute
	function autoLink($string)
	{
		$string = $this->fix_linked_links($string);
		$string = $this->fix_unlinked_links($string);
		return $string;
	}
	
	//looks through string and finds linked links and relinks them with new attrs
	private function fix_linked_links($string)
	{
		$pattern = '@(<(\s*)a([^>]+)>)@i';//matches opening a tag
		preg_match_all($pattern,$string,$matches);
		$matches = array_shift($matches);
		if(is_array($matches))
		{
			$pattern = '@href\s*=\s*[\'|"]*([^\s\'">]+)[\'|"]*@i';//matches link inside of the href attribute
			foreach($matches as $match)
			{
				if(preg_match($pattern,$match,$pieces))
				{
					$href = array_pop($pieces);
					if(stristr($href,'mailto') == $href)
						$replace = '<a href="'.$href.'">';//in case it's an email address, doesn't add any attributes
					else
						$replace = '<a'.$this->get_attrs().' href="'.$href.'">';
					$string = str_replace($match,$replace,$string);
					$this->record[] = array(
						'old' => $match,
						'new' => $replace
					);
				}
			}
		}
		return $string;
	}
	
	//looks through string and finds unlinked links (that start with http:// or www.) and links them with attrs
	private function fix_unlinked_links($string)
	{
		/* These steps require some explanation
		 * AutoLinker does not concern itself with the dom structure of the string passed in
		 * AutoLinker also does not care if there are linked links in your string - it strips out the tag and relinks it
		 * So, if you have a string like <a href="url">text url</a>, you will have a link inside your-anchor text
		 * In order to avoid complicating each of the existing preg match, we remove anchor text, do autolink, then put it back in
		 */
		
		preg_match_all('@(<(\s*)a([^>]+)>)(.*)(</a>)@i', $string, $matches, PREG_OFFSET_CAPTURE);
		$anchor_text_array = array();
		
		$replacement_offset = 0;
		foreach($matches[4] as $key => $value)
		{
			list($anchor, $offset) = $value;
			$hash_replacement = md5($anchor . $key . rand(0,500));
			$string = substr_replace($string, $hash_replacement, $offset + $replacement_offset, strlen($anchor));
			$anchor_text_array[$hash_replacement] = $anchor;
			$replacement_offset += (strlen($hash_replacement) - strlen($anchor));
		}
		
		$link = '<a'.$this->get_attrs().' href="$1">$1</a>';//sets up link with attributes
		$patterns = array(
			'@(?<![((ht|f)tps?://)|\[|=|>])((\bwww\.)[\w?=&~\.%+/#:;!_-]++)(?!\s\[)@i' => 'http://$1',//adds an http in front of all www links that aren't deadlinks
			'@(?<!<a href=")(?<!b>)(?<!")(?<!">)(?<![\[=])(((ht|f)tps?)://[\w?=&~\.%+/#:;!_-]++)(?!\s\[)@i' => $link,//adds a tag around all http links that aren't deadlinks
			'/((?<!<a href=\"mailto:)(?<!\">)(?<=(>|\s))[\w_-]+@[\w_.-]+[\w]+)/i' => '<a href="mailto:$1">$1</a>',//adds a tag around all mail links
			'@\.">@' => '">',//fixes the 'period in href attr' problem
			'@\.</a>@' => '</a>.'//fixes the 'period in link' problem
		);
		foreach($patterns as $pattern => $replace)
		{
			preg_match_all($pattern,$string,$matches);
			$string = preg_replace($pattern,$replace,$string);
			$matches = array_shift($matches);
			foreach($matches as $match)
			{
				$new = preg_replace($pattern,$replace,$match);
				$this->record[] = array(
					'old' => $match,
					'new' => $new
				);
			}
		}
		
		foreach($anchor_text_array as $hash_key => $anchor)
		{
			$string = str_replace($hash_key, $anchor, $string);
		}
		
		return $string;
	}
	
	//turns attribute array into url-friendly string
	private function get_attrs()
	{
		$attr = '';
		if($this->attrs)
		{
			foreach($this->attrs as $attr_array)
			{
				$attr .= ' '.$attr_array['name'].'="'.$attr_array['value'].'"';
			}
		}
		return $attr;
	}
	
	//returns an array with a record of each change
	function pullRecord()
	{
		if($this->record)
			return $this->record;
		else
			return false;
	}

}

?>