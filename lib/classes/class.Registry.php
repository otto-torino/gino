<?php
/**
 * @file class.Registry.php
 * @brief Contiene la definizione ed implementazione della classe Gino.Registry
 *
 * @copyright 2005-2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

/**
 * @brief Registro di gino
 *
 * Il registro è un Gino.Singleton che può conservare dati accessibili e modificabili da tutte le classi di gino.
 * Le variabili sono chiavi di un array privato della classe e sono accessibili direttamente grazie all'utilizzo dei metodi __get e __set.
 *
 * In particolare conserva alcune variabili che vengono utilizzate nella creazione dei template:
 *   - @b title (valore del tag meta con name 'title')
 *   - @b description (valore del tag meta con name 'description')
 *   - @b keywords (valore del tag meta con name 'keywords')
 *   - @b favicon (percorso relativo della favicon)
 *   - @b css (array di path di file css)
 *   - @b js (array di path di file javascript)
 *   - @b meta (tag meta aggiuntivi)
 *   - @b head_links (link)
 *
 * @copyright 2005-2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class Registry extends singleton {

    /*
     * @brief Array associativo che contiene le chiavi del registro
     * @access private
     */
    private $vars = array();

    /**
     * @brief Imposta il valore di una variabile
     *
     * Esempio
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
     * @brief Ritorna il valore di una variabile
     *
     * Esempio
     * @code
     * echo $instance->foo;
     * @endcode
     *
     * @param string $index nome della variabile
     * @return valore variabile o null se non definita
     */
    public function __get($index) {
        return isset($this->vars[$index]) ? $this->vars[$index] : null;
    }

    /**
     * @brief Controlla se è stata definita una proprietà del registry
     *
     * @param $prop nome della proprietà
     * @return TRUE se è definita, FALSE altrimenti
     */
    public function propertyExists($prop)
    {
        return (bool) isset($this->vars[$prop]);
    }

    /**
     * @brief Aggiunge un file css
     *
     * Esempio
     * @code
     * $instance->addCss(CSS_WWW."/file.css");
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
     * Esempio
     * @code
     * $instance->addCss(SITE_JS."/file.js");
     * @endcode
     *
     * @param string $js percorso relativo al file javascript
     * @return void
     */
    public function addJs($js) {
        $this->vars['js'][] = $js;
    }

    /**
     * @brief Aggiunge un META TAG
     *
     * Esempio
     * @code
     * $instance->addMeta(array('name'=>'bar', 'property'=>'foo'));
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
     * $instance->addHeadLink(array('rel'=>'external', 'title'=>'foo', 'href'=>''));
     * @endcode
     *
     * @param array $link elementi di un tag link (rel, type, title, href)
     * @return void
     */
    public function addHeadLink($link) {
        $this->vars['head_links'][] = $link;
    }

    /**
     * @brief Codice HTML da inserire nell'HEAD del documento della variabile fornita (CSS, JS, META, ...)
     *
     * @description Si possono fornire le seguenti chiavi:
     * - title
     * - description
     * - keywords
     * - favicon
     * - css
     * - js
     * - meta
     * - head_links
     *
     * I Css ed i Js sono trattati dalla classe Gino.Compressor che permette la minificazione e merge dei file quando la
     * costante DEBUG definita in @ref configuration.php è settata a FALSE.
     * @see Gino.Compressor
     * @param string $var nome della chiave che deve essere recuperata dal registro
     * @return codice html
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
        elseif($var == 'js')
        {
            $buffer = '';
            if(sizeof($this->vars[$var]) > 0)
            {
                if(DEBUG) {
                    $buffer = '';
                    foreach(array_unique($this->vars[$var]) as $link)
                        $buffer .= "<script type=\"text/javascript\" src=\"$link\"></script>\n";
                }
                else {
                    $compressor = Loader::load('Compressor', array());
                    $compressor->addJs(array_unique($this->vars[$var]));
                    $buffer = "<script type=\"text/javascript\" src=\"".$compressor->mergeJs()."\"></script>";
                }
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
