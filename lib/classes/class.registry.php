<?php

class registry extends singleton {

	/*
	 * @the vars array
	 * @access private
	 */
	private $vars = array();

	/**
	 * @set undefined vars
	 *
	 * @param string $index
	 * @param mixed $value
	 * @return void
	 */
	public function __set($index, $value) {
		$this->vars[$index] = $value;
	}
	
	/**
	 * @get variables
	 *
	 * @param mixed $index
	 * @return mixed
	 */
	public function __get($index) {
		return $this->vars[$index];
	}

	public function addCss($css) {
		$this->vars['css'][] = $css;
	}

	public function addJs($js) {
		$this->vars['js'][] = $js;
	}

	public function addMeta($meta) {
		$this->vars['meta'][] = $meta;
	}
	
	public function addHeadLink($link) {
		$this->vars['head_links'][] = $link;
	}
	
	public function variables($var) {
		
		if($var == 'title' || $var == 'description' || $var == 'keywords' || $var == 'favicon') {
			return $this->vars[$var];
		}
		elseif($var == 'css')
		{
			$buffer = '';
			if(sizeof($this->vars[$var]) > 0)
			{
				foreach(array_unique($this->vars[$var]) as $link)
					$buffer .= "<link rel=\"stylesheet\" href=\"$link\" type=\"text/css\" />\n";
			}
			return $buffer;
		}
		elseif($var == 'js')
		{
			$buffer = '';
			if(sizeof($this->vars[$var]) > 0)
			{
				foreach(array_unique($this->vars[$var]) as $link)
					$buffer .= "<script type=\"text/javascript\" src=\"$link\"></script>\n";
			}
			return $buffer;
		}
		elseif($var == 'meta')
		{
			$buffer = '';
			foreach(array_unique($this->vars[$var]) as $meta)
			{
				$buffer .= "<meta"
				.(isset($meta['name']) ? " name=\"".$meta['name']."\"" : '')
				.(isset($meta['property']) ? " property=\"".$meta['property']."\"" : '')
				." content=\"".$meta['content']."\" />\n";
			}
			return $buffer;
		}
		elseif($var == 'head_links')
		{
			$buffer = '';
			foreach(array_unique($this->vars[$var]) as $hlink) {
				$buffer .= "<link"
				.(isset($hlink['rel']) ? " rel=\"".$hlink['rel']."\"" : '')
				.(isset($hlink['type']) ? " type=\"".$hlink['type']."\"" : '')
				.(isset($hlink['title']) ? " title=\"".$hlink['title']."\"" : '')
				." href=\"".$hlink['href']."\" />\n";
			}
			return $buffer;
		}
	}
}

?>