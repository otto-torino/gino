<?php

class htmlList {

	private $_p = array(
			'class'=>'admin',
			'id'=>null,
			'separator'=>false,
			'numItems' => 0
		);
	private $_count;


	function __construct($data = array()) {

		foreach($data as $k=>$v) {
			if(array_key_exists($k, $this->_p)) $this->_p[$k] = $v;
		}

		$this->_count = 0;

	}

	public function __get($pName) {
	
		if(!array_key_exists($pName, $this->_p)) exit(error::syserrorMessage("htmlList", "__get", _("Nome proprietà non valido")." ($pName)", __LINE__));
		if(method_exists($this, 'get'.$pName)) return $this->{'get'.$pName}();
		else return $this->_p[$pName];
	}
	
	public function __set($pName, $value) {

		if(!array_key_exists($pName, $this->_p)) exit(error::syserrorMessage("htmlList", "__set", _("Nome proprietà non valido"), __LINE__));
		if(method_exists($this, 'set'.$pName)) return $this->{'set'.$pName}($value);
		else $this->_p[$pName] = $value;
	}

	public function start() {

		$buffer = "<ul class=\"".$this->class."\" ".($this->id?"id=\"$this->id\"":"").">\n";

		return $buffer;
		
	}

	public function item($text, $links, $selected, $autoSeparate=true, $itemContent=null, $id=null, $class='', $other=null) {
		
		$buffer = "<li class=\"".(($selected)?"selected ":"").$class."\" ".($id?"id=\"$id\"":"")." ".($other?$other:"").">";
		$buffer .= "<div class=\"liTitle left\">$text</div>\n";
		$buffer .= "<div class=\"right\">".((is_array($links))?implode(" ", $links):$links)."</div>\n";
		$buffer .= "<div class=\"null\"></div>\n";
		if($itemContent) $buffer .= $itemContent;
		$buffer .= "</li>\n";

		$this->_count++;

		if($this->separator && $autoSeparate) $buffer .= $this->listLine();

		return $buffer;

	}
	
	public function listLine() {

		if($this->_count>=$this->numItems) return '';
		$buffer = "<li class=\"listLine\">";
		$buffer .= "<div class=\"liLineLeft\"></div>";
		$buffer .= "<div class=\"liLineCenter\"></div>";
		$buffer .= "<div class=\"liLineRight\"></div>";
		$buffer .= "<div class=\"null\"></div>";
		$buffer .= "</li>\n";

		return $buffer;
	}

	public function end() {
		
		$buffer = "</ul>\n";

		$this->_count = 0;

		return $buffer;

	}
	
}


?>
