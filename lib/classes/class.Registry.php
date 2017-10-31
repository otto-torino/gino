<?php
/**
 * @file class.Registry.php
 * @brief Contiene la definizione ed implementazione della classe Gino.Registry
 *
 * @copyright 2005-2017 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

/**
 * @brief Registro di gino
 *
 * @copyright 2005-2017 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 * 
 * Il registro è un Gino.Singleton che può conservare dati accessibili e modificabili da tutte le classi di gino.
 * Le variabili sono chiavi di un array privato della classe e sono accessibili direttamente grazie all'utilizzo dei metodi __get e __set.
 * 
 * Le chiavi del registro nelle quali vengono caricate le informazioni e i file da richiamare sono le seguenti: \n
 *   - @a title (valore del tag meta con name 'title')
 *   - @a description (valore del tag meta con name 'description')
 *   - @a keywords (valore del tag meta con name 'keywords')
 *   - @a favicon (percorso relativo della favicon)
 *   - @a css (array di path di file css)
 *   - @a core_js (array), file javascript caricati come core di gino
 *   - @a handle_core_js (array), file javascript caricati come core di gino ma manipolati (opzioni compress/minify)
 *   - @a js (array di path di file javascript)
 *   - @a custom_js (array di path di file javascript con eventuali opzioni)
 *   - @a meta (tag meta aggiuntivi)
 *   - @a head_links (link)
 * 
 * Poi è il metodo variables() che permette di caricare codice HTML direttamente nel tag HEAD del documento (ad esempio nel template). \n
 * Le chiavi valide per caricare i meta, css, js sohno le seguenti: \n
 *   - @b title
 *   - @b description
 *   - @b keywords
 *   - @b favicon
 *   - @b css
 *   - @b core_js (per le chiavi del registro core_js, handle_core_js)
 *   - @b js (per le chiavi del registro js, custom_js)
 *   - @b meta
 *   - @b head_links
 * 
 * ##Gino.Compressor
 * I file Css e Js sono trattati dalla classe Gino.Compressor che permette la minificazione e merge dei file quando la
 * costante DEBUG definita in @ref configuration.php è settata a FALSE.
 * 
 * ##Caricamento Javascript
 * I file Js vengono trattati in diversi modi a seconda del valore della costante DEBUG e del tipo di caricamento nel registro, 
 * con Registry::addCoreJS(), Registry::addJS() e Registry::addCustomJS(). \n
 * I file caricati con Registry::addCustomJS() vengono accodati a quelli caricati con Registry::addJS().
 * 
 * ####Registry::addCoreJs()
 * File javascript del core di gino. Vengono caricati prima degli altri file javascript.
 * 
 * ####Registry::addJS()
 * Con Debug True i file vengono caricati uno per uno nella versione originale, mentre con Debug False i file vengono minificati e compressi in unico file (Compressor::mergeJs()). \n
 * Il file che viene generato viene salvato nella directory cache/js, e viene soltanto recuperato se risulta già presente.
 * 
 * ####Registry::addCustomJS()
 * Con Debug True i file vengono caricati uno per uno nella versione originale, mentre con Debug False si devono verificare i valori delle opzioni @a compress e @a minify di ogni file. \n
 * Se il valore di @a compress è True il file viene inglobato nel file generale che comprende i file caricati con Registry::addJS(). 
 * Naturalmente il file compresso viene anche minificato. \n
 * Se il valore di @a compress è False e il valore di @a minify è True il file viene minificato, altrimenti viene caricata la sua versione originale.
 */
class Registry extends Singleton {

    /**
     * @brief Array associativo che contiene le chiavi del registro
     * @access private
     */
    private $vars = array();

    /**
     * @brief Imposta il valore di una variabile
     *
     * @example
     * @code
     * $instance->foo = 'bar';
     * @endcode
     *
     * @param string $index nome della variabile
     * @param mixed $value valore della variabile
     * @return void
     *
     */
    public function __set($index, $value) {
        $this->vars[$index] = $value;
    }

    /**
     * @brief Ritorna il valore di una variabile o null se la variabile non è definita
     *
     * @example
     * @code
     * echo $instance->foo;
     * @endcode
     *
     * @param string $index nome della variabile
     * @return mixed|null
     */
    public function __get($index) {
        return isset($this->vars[$index]) ? $this->vars[$index] : null;
    }

    /**
     * @brief Controlla se è stata definita una proprietà del registry
     *
     * @param string $prop nome della proprietà
     * @return TRUE se è definita, FALSE altrimenti
     */
    public function propertyExists($prop)
    {
        return (bool) isset($this->vars[$prop]);
    }

    /**
     * @brief Aggiunge un file css
     *
     * @example
     * @code
     * Registry->addCss(CSS_WWW."/file.css");
     * @endcode
     *
     * @param string $css percorso relativo al file css
     * @return void
     */
    public function addCss($css) {
        $this->vars['css'][] = $css;
    }

    /**
     * @brief Aggiunge un file javascript
     *
     * @example
     * @code
     * Registry->addCss(SITE_JS."/file.js");
     * @endcode
     *
     * @param string $js percorso relativo al file javascript
     * @return void
     */
    public function addJs($js) {
    	$this->vars['js'][] = $js;
    }
    
    /**
     * @brief Aggiunge un file javascript con eventuali opzioni
     * @description Popola l'array $this->vars['custom_js']
     *
     * @example
     * @code
     * Registry->addCustomCss(SITE_JS."/file.js", array(compress => bool, minify => bool));
     * @endcode
     *
     * @param string $js percorso relativo al file javascript
     * @param array $options
     *   array associativo di opzioni
     *   - @b compress (boolean): comprime il file inglobandolo nel file generale che comprende i file caricati con Gino.Compressor->addJs; default true
     *   - @b minify (boolean): per effettuare la minificazione del file (default true)
     * @return void
     */
    public function addCustomJs($js, $options=array()) {
        
    	$compress = gOpt('compress', $options, true);
    	$minify = gOpt('minify', $options, true);
    	
    	$link = base64_encode($js);
    	$this->vars['custom_js'][$link] = array('compress' => $compress, 'minify' => $minify);
    }
    
    /**
     * @brief Aggiunge un file javascript nel core di gino
     * @description Popola gli array $this->vars['core_js'] e $this->vars['handle_core_js']
     * 
     * @example
     * @code
     * Registry->addCoreJs(SITE_JS."/file.js", array(compress => bool, minify => bool, handle => bool));
     * @endcode
     * 
     * @param string $js percorso relativo al file javascript
     * @param array $options
     *   array associativo di opzioni
     *   - @b compress (boolean): comprime il file inglobandolo nel file generale che comprende i file caricati con Gino.Compressor->addJs; default false
     *   - @b minify (boolean): per effettuare la minificazione del file (default false)
     *   - @b handle (boolean): indica se il file deve essere manipolato (default false)
     * @return void
     */
    public function addCoreJs($js, $options=array()) {
    	
    	$compress = gOpt('compress', $options, false);
    	$minify = gOpt('minify', $options, false);
    	$handle = gOpt('handle', $options, false);
    	
    	if($handle) {
    		$link = base64_encode($js);
    		$this->vars['handle_core_js'][$link] = array('compress' => $compress, 'minify' => $minify);
    	}
    	else {
    		$this->vars['core_js'][] = $js;
    	}
    }

    /**
     * @brief Aggiunge un META TAG
     *
     * Esempio
     * @code
     * Registry->addMeta(array('name'=>'bar', 'property'=>'foo'));
     * @endcode
     *
     * @param array $meta elementi di un tag meta (name, property, content)
     * @return void
     */
    public function addMeta($meta) {
        $this->vars['meta'][] = $meta;
    }

    /**
     * @brief Aggiunge un LINK TAG
     *
     * Esempio
     * @code
     * Registry->addHeadLink(array('rel'=>'external', 'title'=>'foo', 'href'=>''));
     * @endcode
     *
     * @param array $link elementi di un tag link (rel, type, title, href)
     * @return void
     */
    public function addHeadLink($link) {
        $this->vars['head_links'][] = $link;
    }

    /**
     * @brief Gestisce il codice HTML da inserire nel tag HEAD del documento (meta, css, js)
     * 
     * @see Gino.Compressor
     * @param string $var nome del tipo di informazioni da caricare nel tag HEAD; i valori validi sono:
     *   - @a title
     *   - @a description
     *   - @a keywords
     *   - @a favicon
     *   - @a css
     *   - @a core_js (chiavi del registro core_js, handle_core_js)
     *   - @a js (chiavi del registro js, custom_js)
     *   - @a meta
     *   - @a head_links
     * @return string
     */
    public function variables($var) {

        if($var == 'title' or $var == 'description' or $var == 'keywords' or $var == 'favicon') {
            return $this->vars[$var];
        }
        elseif($var == 'css')
        {
            $buffer = '';
            if(sizeof($this->vars[$var]) > 0)
            {
                if(DEBUG) {
                    $buffer = '';
                    foreach(array_unique($this->vars[$var]) as $link)
                        $buffer .= "<link rel=\"stylesheet\" href=\"$link\" type=\"text/css\" />\n";
                }
                else {
                    $compressor = Loader::load('Compressor', array());
                    $compressor->addCss(array_unique($this->vars[$var]));
                    $buffer = "<link rel=\"stylesheet\" href=\"".$compressor->mergeCss()."\" type=\"text/css\" />";
                }
            }
            return $buffer;
        }
        elseif($var == 'core_js')
        {
        	$custom_key = 'handle_core_js';
        	
        	$buffer = '';
            if(isset($this->vars[$var]) && sizeof($this->vars[$var]) > 0)
            {
                if(DEBUG) {
                    foreach(array_unique($this->vars[$var]) as $link)
                    {
                    	$buffer .= "<script type=\"text/javascript\" src=\"$link\"></script>\n";
                    }
                    
                    if(isset($this->vars[$custom_key]) && sizeof($this->vars[$custom_key]) > 0)
                    {
                    	foreach($this->vars[$custom_key] as $key=>$array)
                    	{
                    		$link = base64_decode($key);
                    		$buffer .= "<script type=\"text/javascript\" src=\"".$link."\"></script>\n";
                    	}
                    }
                }
                else {
                    $compressor = Loader::load('Compressor', array());
                	$compressor->addJs(array_unique($this->vars[$var]));
                	
                	$buffer_custom = '';
                	if(isset($this->vars[$custom_key]) && sizeof($this->vars[$custom_key]) > 0)
                	{
                		foreach($this->vars[$custom_key] as $key=>$array)
                		{
                			$link = base64_decode($key);
                			if(array_key_exists('compress', $array) && $array['compress'])
                			{
                				$compressor->addJs($link);
                			}
                			else
                			{
                				if(array_key_exists('minify', $array) && $array['minify'])
                				{
                					$compressor_min = Loader::load('Compressor', array());
                					$compressor_min->addJs($link);
                					
                					$link = $compressor_min->mergeJs(array('minify' => true));
                				}
                				$buffer_custom .= "<script type=\"text/javascript\" src=\"".$link."\"></script>\n";
                			}
                		}
                	}
                	
                	$buffer .= "<script type=\"text/javascript\" src=\"".$compressor->mergeJs()."\"></script>";
                	$buffer .= $buffer_custom;
                }
            }
           
            return $buffer;
        }
        elseif($var == 'js')
        {
        	$custom_key = 'custom_js';
        	$compression = false;
        	$compression_custom = false;
        	
        	$compressor = Loader::load('Compressor', array());
        	
        	$buffer = "";
        	$buffer_custom = "";
        	
        	if(isset($this->vars[$var]) && sizeof($this->vars[$var]) > 0)
        	{
        		if(DEBUG) {
        			foreach(array_unique($this->vars[$var]) as $link)
        			{
        				$buffer .= "<script type=\"text/javascript\" src=\"$link\"></script>\n";
        			}
        		}
        		else {
        			$compressor->addJs(array_unique($this->vars[$var]));
        			$compression = true;
        		}
        	}
        	
        	if(isset($this->vars[$custom_key]) && sizeof($this->vars[$custom_key]) > 0)
        	{
        		if(DEBUG) {
        			foreach($this->vars[$custom_key] as $key=>$array)
        			{
        				$link = base64_decode($key);
        				$buffer .= "<script type=\"text/javascript\" src=\"".$link."\"></script>\n";
        			}
        		}
        		else {
        			foreach($this->vars[$custom_key] as $key=>$array)
        			{
        				$link = base64_decode($key);
        				if(array_key_exists('compress', $array) && $array['compress'])
        				{
        					$compressor->addJs($link);
        					$compression_custom = true;
        				}
        				else
        				{
        					if(array_key_exists('minify', $array) && $array['minify'])
        					{
        						$compressor_min = Loader::load('Compressor', array());
        						$compressor_min->addJs($link);
        						
        						$link = $compressor_min->mergeJs(array('minify' => true));
        					}
        					$buffer_custom .= "<script type=\"text/javascript\" src=\"".$link."\"></script>\n";
        				}
        			}
        		}
        	}
        	
        	if($compression or $compression_custom) {
        		$buffer .= "<script type=\"text/javascript\" src=\"".$compressor->mergeJs()."\"></script>";
        	}
        	if($buffer_custom) {
        		$buffer .= $buffer_custom;
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
