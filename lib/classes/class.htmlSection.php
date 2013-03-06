<?php
/**
 * @file class.htmlSection.php
 * @brief Contiene la classe htmlSection
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

/**
 * @brief Fornisce gli elementi per una struttura HTML delle pagine
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 * 
 * Esempio di sruttura
 * @code
 * preHeaderLabel -> 10/2/2010, Informatica
 * headerLabel -> IL LINGUAGGIO PHP
 * subHeaderLabel -> Le basi del php
 * content -> Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.
 * 
 * footer -> ad esempio per la paginazione: da 10 a 20
 * headerLinks -> a seguito di 'headerLabel'
 * preHeaderTag, headerTag, subHeaderTag -> indicare un tag per la label
 * @endcode
 */
class htmlSection {

	private $_p = array(
		'class'=>null,
		'id'=>null,
		'preHeaderTag' =>'h4',
		'preHeaderLabel' =>null,
		'headerTag' =>'header',
		'headerClass' =>null,
		'headerLabel' =>null,
		'subHeaderTag' =>'h3',
		'subHeaderLabel' =>null,
		'headerLinks' =>null,
		'content'=>null,
		'footer'=>null
	);

	/**
	 * Costruttore
	 * 
	 * @param array $data elementi della pagina
	 *   - @b class (string): classe del contenitore
	 *   - @b id (string): id del contenitore
	 *   - @b preHeaderTag (string): TAG del testo da mostrare prima di headerTag -> renderHeader()
	 *   - @b preHeaderLabel (string): testo da mostrare prima di headerTag -> renderHeader()
	 *   - @b headerTag (string): TAG principale -> renderHeader()
	 *   - @b headerClass (string): classe css del TAG principale -> renderHeader()
	 *   - @b headerLabel (string): testo del TAG principale -> renderHeader()
	 *   - @b subHeaderTag (string): TAG del testo seguente headerTag -> renderHeader()
	 *   - @b subHeaderLabel (string): testo del TAG seguente headerTag -> renderHeader()
	 *   - @b headerLinks (mixed): collegamenti nel TAG principale -> renderHeader()
	 *   - @b content (string): contenuto della pagina
	 *   - @b footer (string): footer
	 * @return void
	 */
	function __construct($data = array()) {

		foreach($data as $k=>$v) {
			if(array_key_exists($k, $this->_p)) $this->_p[$k] = $v;
		}
	}

	/**
	 * Ritorna il valore della proprietà
	 * @param string $pName
	 * @return mixed
	 */
	public function __get($pName) {
	
		if(!array_key_exists($pName, $this->_p)) exit(error::syserrorMessage("htmlSection", "__get", _("Nome proprietà non valido")." ($pName)", __LINE__));
		if(method_exists($this, 'get'.$pName)) return $this->{'get'.$pName}();
		else return $this->_p[$pName];
	}
	
	/**
	 * Imposta il valore della proprietà
	 * @param string $pName
	 * @param mixed $value
	 * @return void
	 */
	public function __set($pName, $value) {

		if(!array_key_exists($pName, $this->_p)) exit(error::syserrorMessage("htmlSection", "__set", _("Nome proprietà non valido"), __LINE__));
		if(method_exists($this, 'set'.$pName)) return $this->{'set'.$pName}($value);
		else $this->_p[$pName] = $value;
	}

	/**
	 * Stampa il contenitore
	 * @return string
	 */
	public function render() {

		$buffer = "<section class=\"$this->class\" ".(($this->id)? "id=\"$this->id\"":"").">\n";
		if($this->headerLabel || $this->headerLinks || $this->preHeaderLabel) $buffer .= $this->renderHeader();
		if($this->content) $buffer .= $this->renderContent();
		if($this->footer) $buffer .= $this->renderFooter();
		$buffer .= "</section>";

		return $buffer;
	}

	/**
	 * Stampa l'header della section
	 * @return string
	 */
	private function renderHeader() {

		//@todo remove when all header strings have been changed
		if($this->headerTag == 'header') {
			$this->headerTag = 'h1';
		}

		$buffer = '';
		if($this->preHeaderLabel || $this->headerLinks) $buffer .= "<header>";
		if($this->preHeaderLabel) {
			$buffer .= "<".$this->preHeaderTag.">";
			$buffer .= $this->preHeaderLabel;
			$buffer .= "</".$this->preHeaderTag.">";
		}
		if($this->subHeaderLabel && preg_match("#h\d#", $this->subHeaderLabel) && !$this->headerLinks) {
			$buffer .= "<hgroup>";
		}
		if($this->headerLinks) {
			$buffer .= "<".$this->headerTag." class=\"headerInside left".($this->headerClass ? " ".$this->headerClass : "")."\">".$this->headerLabel."</".$this->headerTag.">";
			$buffer .= "<div class=\"headerInside right\">".((is_array($this->headerLinks))?implode(" ", $this->headerLinks):$this->headerLinks)."</div>";
			$buffer .= "<div class=\"null\"></div>\n";
		}
		else {
			$buffer .= "<".$this->headerTag.($this->headerClass ? " class=\"".$this->headerClass."\"" : "").">".$this->headerLabel."</".$this->headerTag.">";
		}
		if($this->subHeaderLabel) {
			$buffer .= "<".$this->subHeaderTag.">";
			$buffer .= $this->subHeaderLabel;
			$buffer .= "</".$this->subHeaderTag.">";
		}
		if($this->subHeaderLabel && preg_match("#h\d#", $this->subHeaderLabel) && !$this->headerLinks) {
			$buffer .= "</hgroup>";
		}
		if($this->preHeaderLabel || $this->headerLinks) $buffer .= "</header>";

		return $buffer;

	}
	
	/**
	 * Stampa il contenuto della section
	 * @return string
	 */
	private function renderContent() {

		$buffer = "<div class=\"section_body\">".$this->content."</div>";

		return $buffer;
	}
	
	/**
	 * Stampa il footer della section
	 * @return string
	 */
	private function renderFooter() {

		$buffer = "<footer>$this->footer</footer>";

		return $buffer;
	}
}
?>
