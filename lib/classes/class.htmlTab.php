<?php

class htmlTab {

	private $_browser;
	private $_p = array(
			'id'=>null,
			'title'=>null,
			'navigationLinks'=>null,
			'htmlContent'=>null,
			'linkPosition'=>null,
			'selectedLink'=>null
		);

	function __construct($data = array()) {

		foreach($data as $k=>$v) {
			if(array_key_exists($k, $this->_p)) $this->_p[$k] = $v;
		}

		$pub = new pub;
		$this->_browser = $pub->detectBrowser('Parent');

	}

	public function __get($pName) {
	
		if(!array_key_exists($pName, $this->_p)) exit(error::syserrorMessage("htmlTab", "__get", _("Nome proprietà non valido")." ($pName)", __LINE__));
		if(method_exists($this, 'get'.$pName)) return $this->{'get'.$pName}();
		else return $this->_p[$pName];
	}
	
	public function __set($pName, $value) {

		if(!array_key_exists($pName, $this->_p)) exit(error::syserrorMessage("htmlTab", "__set", _("Nome proprietà non valido"), __LINE__));
		if(method_exists($this, 'set'.$pName)) return $this->{'set'.$pName}($value);
		else $this->_p[$pName] = $value;
	}

	public function render() {

		$buffer = "<div class=\"tabContainer\" ".(($this->id)? "id=\"box_$this->id\"":"").">\n";
		$buffer .= $this->renderTop();
		$buffer .= $this->renderContent();
		$buffer .= "</div>";

		return $buffer;

	}

	private function renderTop() {
		
		$left = ($this->linkPosition=='left')?$this->renderNavigationLinks():$this->renderTitle();
		$right = ($this->linkPosition=='left')?$this->renderTitle():$this->renderNavigationLinks();
		$style = preg_match("#IE 6#", $this->_browser)? "padding:1px" : (preg_match("#IE 7#", $this->_browser)? "padding-top:1px" : "");
		$buffer = "<div class=\"tabTop\" style=\"$style\">\n";
		$buffer .= "<div class=\"left\">$left</div>\n";
		$buffer .= "<div class=\"right\">$right</div>\n";
		$buffer .= "<div class=\"null\"></div>";
		$buffer .= "</div>";

		return $buffer;

	}

	private function renderNavigationLinks() {

		$buffer = '';
		if(is_array($this->navigationLinks)) {
			foreach($this->navigationLinks as $link) $buffer .= $this->tabNavigation($link);
		}
		elseif(is_string($this->navigationLinks)) $buffer .= $this->tabNavigation($this->navigationLinks);

		return $buffer;

	}

	private function renderTitle() {
		
		$buffer = "<div class=\"tabTitle ".($this->linkPosition=='left'?"tabImgRight":"tabImgLeft")."\">".$this->title."</div>\n";

		return $buffer;

	}

	private function renderContent() {
		$buffer = "<div class=\"tabContent\">\n";
		$buffer .= $this->htmlContent;
		$buffer .= "</div>";

		return $buffer;

	}

	private function tabNavigation($link) {

		$class_ext = "tabExt ".($this->linkPosition=='left'?' left':' right').($this->selectedLink==$link? " extSelected": ""); 
		$class_int = "tabInt left".($this->selectedLink==$link? " intSelected": "");
		$buffer = "<div class=\"$class_ext\">";
		$buffer .= "<div class=\"$class_int\">".$link."</div>";
		$buffer .= "</div>";

		return $buffer;
	}

}

?>
