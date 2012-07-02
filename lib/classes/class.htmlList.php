<?php
/**
 * @file class.htmlList.php
 * @brief Contiene la classe htmlList
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

/**
 * @brief Fornisce gli elementi per la rappresentazione in formato lista di un insieme di elementi
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class htmlList {

	private $_p = array(
		'class'=>'admin',
		'id'=>null,
		'separator'=>false,
		'numItems' => 0
	);
	private $_count;

	/**
	 * Costruttore
	 * 
	 * @param array $data elementi della pagina
	 *   - @b class (string): classe del contenitore lista (UL)
	 *   - @b id (string): id del contenitore lista (UL)
	 *   - @b separator (boolean): separatore lista
	 *   - @b numItems (integer): numero totale di elementi
	 * @return void
	 */
	function __construct($data = array()) {

		foreach($data as $k=>$v) {
			if(array_key_exists($k, $this->_p)) $this->_p[$k] = $v;
		}

		$this->_count = 0;
	}

	/**
	 * Ritorna il valore della proprietà
	 * @param string $pName
	 * @return mixed
	 */
	public function __get($pName) {
	
		if(!array_key_exists($pName, $this->_p)) exit(error::syserrorMessage("htmlList", "__get", _("Nome proprietà non valido")." ($pName)", __LINE__));
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

		if(!array_key_exists($pName, $this->_p)) exit(error::syserrorMessage("htmlList", "__set", _("Nome proprietà non valido"), __LINE__));
		if(method_exists($this, 'set'.$pName)) return $this->{'set'.$pName}($value);
		else $this->_p[$pName] = $value;
	}

	/**
	 * Inizializza la lista
	 * @return string
	 */
	public function start() {

		$buffer = "<ul class=\"".$this->class."\" ".($this->id?"id=\"$this->id\"":"").">\n";
		return $buffer;
	}

	/**
	 * Elemento della lista
	 * 
	 * @param string $text testo principale (titolo)
	 * @param mixed $links collegamenti (se sono più di uno creare un array)
	 * @param boolean $selected indica se l'elemento è selezionato
	 * @param boolean $autoSeparate indica se inserire un separatore (metodo listLine())
	 * @param string $itemContent testo aggiuntivo
	 * @param string $id id del tag LI
	 * @param string $class classe del tag LI
	 * @param string $other altro nel tag LI
	 * @return string
	 */
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
	
	/**
	 * Elemento di separazione
	 * @return string
	 */
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

	/**
	 * Chiude la lista
	 * @return string
	 */
	public function end() {
		
		$buffer = "</ul>\n";

		$this->_count = 0;

		return $buffer;
	}
}
?>
