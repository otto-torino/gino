<?php
/**
 * @file class.registry.php
 * @brief Contiene la classe registry
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

/**
 * @brief Registro di gino
 * 
 * Gli elementi presenti nel registro vengono caricati prima della generazione delle pagina
 * 
 * Le chiavi che possono essere utilizzate per recuperare i valori dal registro sono:
 *   - @b title (valore del tag meta con name 'title')
 *   - @b description (valore del tag meta con name 'description')
 *   - @b keywords (valore del tag meta con name 'keywords')
 *   - @b favicon (percorso relativo della favicon)
 *   - @b css (file css)
 *   - @b js (file javascript)
 *   - @b meta (tag meta aggiuntivi)
 *   - @b head_links (link)
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class registry extends singleton {

	/*
	 * @the vars array
	 * @access private
	 */
	private $vars = array();

	/**
	 * Imposta il valore di una variabile
	 *
	 * @param string $index nome della variabile
	 * @param mixed $value valore della variabile
	 * @return void
	 * 
	 * Esempio
	 * @code
	 * $instance->foo = 'bar';
	 * @endcode
	 */
	public function __set($index, $value) {
		$this->vars[$index] = $value;
	}
	
	/**
	 * Ritorna il valore di una variabile
	 *
	 * @param mixed $index nome della variabile
	 * @return mixed
	 * 
	 * Esempio
	 * @code
	 * echo $instance->foo;
	 * @endcode
	 */
	public function __get($index) {
		return $this->vars[$index];
	}

    /**
     * @brief Controlla se è stata definita una proprietà del registry
     *
     * @param $prop nome della proprietà
     *
     * @return vero se è definita, falso altrimenti
     */
    public function propertyExists($prop)
    {
        return (bool) isset($this->vars[$prop]);
    }

	/**
	 * Carica un file css in un array (chiave @a css)
	 * 
	 * @param string $css percorso relativo al file css
	 * @return void
	 * 
	 * Esempio
	 * @code
	 * $instance->addCss(CSS_WWW."/file.css");
	 * @endcode
	 */
	public function addCss($css) {
		$this->vars['css'][] = $css;
	}

	/**
	 * Carica un file javascript in un array (chiave @a js)
	 * 
	 * @param string $js percorso relativo al file javascript
	 * @return void
	 * 
	 * Esempio
	 * @code
	 * $instance->addCss(SITE_JS."/file.js");
	 * @endcode
	 */
	public function addJs($js) {
		$this->vars['js'][] = $js;
	}

	/**
	 * Carica gli elementi di un tag meta in un array (chiave @a meta)
	 * 
	 * @param array $meta elementi di un tag meta (name, property, content)
	 * @return void
	 * 
	 * Esempio
	 * @code
	 * $instance->addMeta(array('name'=>'bar', 'property'=>'foo'));
	 * @endcode
	 */
	public function addMeta($meta) {
		$this->vars['meta'][] = $meta;
	}
	
	/**
	 * Carica gli elementi di un tag link in un array (chiave @a head_links)
	 * 
	 * @param array $link elementi di un tag link (rel, type, title, href)
	 * @return void
	 * 
	 * Esempio
	 * @code
	 * $instance->addHeadLink(array('rel'=>'external', 'title'=>'foo', 'href'=>''));
	 * @endcode
	 */
	public function addHeadLink($link) {
		$this->vars['head_links'][] = $link;
	}
	
	/**
	 * Stampa le variabili di registro
	 * 
	 * @param mixed $var nome della chiave che deve essere recuperata dal registro
	 * @return string
	 */
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
      $already_inserted = array();
			foreach($this->vars[$var] as $meta)
      {
        if(!in_array($meta, $already_inserted)) {
          $buffer .= "<meta"
          .(isset($meta['name']) ? " name=\"".$meta['name']."\"" : '')
          .(isset($meta['property']) ? " property=\"".$meta['property']."\"" : '')
          ." content=\"".$meta['content']."\" />\n";
          $already_inserted[] = $meta;
        }
			}
			return $buffer;
		}
		elseif($var == 'head_links')
		{
			$buffer = '';
      $already_inserted = array();
			foreach($this->vars[$var] as $hlink) {
        if(!in_array($hlink, $already_inserted)) {
          $buffer .= "<link"
          .(isset($hlink['rel']) ? " rel=\"".$hlink['rel']."\"" : '')
          .(isset($hlink['type']) ? " type=\"".$hlink['type']."\"" : '')
          .(isset($hlink['title']) ? " title=\"".$hlink['title']."\"" : '')
          ." href=\"".$hlink['href']."\" />\n";
          $already_inserted[] = $hlink;
        }
			}
			return $buffer;
		}
	}
}

?>
