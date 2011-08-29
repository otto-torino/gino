<?php

/**
 * Struttura HTML
 *
 * Esempio:
 * 
10/2/2010, Informatica	-> preHeaderLabel
IL LINGUAGGIO PHP		-> headerLabel
Le basi del php			-> subHeaderLabel

Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.	-> content

[paginazione] da 10 a 20	-> footer

- a seguito di 'headerLabel'	-> headerLinks
- indicare un tag per la label	-> preHeaderTag, headerTag, subHeaderTag
 */

class htmlSection {

	private $_p = array(
			'class'=>null,
			'id'=>null,
			'preHeaderTag' =>'h4',
			'preHeaderLabel' =>null,
			'headerTag' =>'header',
			'headerLabel' =>null,
			'subHeaderTag' =>'h3',
			'subHeaderLabel' =>null,
			'headerLinks' =>null,
			'content'=>null,
			'footer'=>null
		);


	function __construct($data = array()) {

		foreach($data as $k=>$v) {
			if(array_key_exists($k, $this->_p)) $this->_p[$k] = $v;
		}

	}

	public function __get($pName) {
	
		if(!array_key_exists($pName, $this->_p)) exit(error::syserrorMessage("htmlSection", "__get", _("Nome proprietà non valido")." ($pName)", __LINE__));
		if(method_exists($this, 'get'.$pName)) return $this->{'get'.$pName}();
		else return $this->_p[$pName];
	}
	
	public function __set($pName, $value) {

		if(!array_key_exists($pName, $this->_p)) exit(error::syserrorMessage("htmlSection", "__set", _("Nome proprietà non valido"), __LINE__));
		if(method_exists($this, 'set'.$pName)) return $this->{'set'.$pName}($value);
		else $this->_p[$pName] = $value;
	}

	public function render() {

		$buffer = "<section class=\"$this->class\" ".(($this->id)? "id=\"$this->id\"":"").">\n";
		if($this->headerLabel || $this->headerLinks || $this->preHeaderLabel) $buffer .= $this->renderHeader();
		if($this->content) $buffer .= $this->renderContent();
		if($this->footer) $buffer .= $this->renderFooter();
		$buffer .= "</section>";

		return $buffer;

	}

	private function renderHeader() {

		$buffer = '';
		if($this->preHeaderLabel || $this->subHeaderLabel) $buffer .= "<hgroup>";
		if($this->preHeaderLabel) {
			$buffer .= "<".$this->preHeaderTag.">";
			$buffer .= $this->preHeaderLabel;
			$buffer .= "</".$this->preHeaderTag.">";
		}
		$buffer .= "<".$this->headerTag.">";
		if($this->headerLinks) {
			$buffer .= "<div class=\"headerInside left\">".$this->headerLabel."</div>";
			$buffer .= "<div class=\"headerInside right\">".((is_array($this->headerLinks))?implode(" ", $this->headerLinks):$this->headerLinks)."</div>";
			$buffer .= "<div class=\"null\"></div>\n";
		}
		else $buffer .= $this->headerLabel;
		$buffer .= "</".$this->headerTag.">";
		if($this->subHeaderLabel) {
			$buffer .= "<".$this->subHeaderTag.">";
			$buffer .= $this->subHeaderLabel;
			$buffer .= "</".$this->subHeaderTag.">";
		}

		if($this->preHeaderLabel || $this->subHeaderLabel) $buffer .= "</hgroup>";

		return $buffer;
	}
	
	private function renderContent() {

		$buffer = "<div class=\"section_body\">".$this->content."</div>";

		return $buffer;
	}
	
	private function renderFooter() {

		$buffer = "<footer>$this->footer</footer>";

		return $buffer;
	}



}

?>
