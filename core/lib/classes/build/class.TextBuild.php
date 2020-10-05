<?php
/**
 * @file class.TextBuild.php
 * @brief Contiene la definizione ed implementazione della classe Gino.TextBuild
 *
 * @copyright 2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

namespace Gino;

use \Gino\Http\Request;

Loader::import('class/build', '\Gino\Build');

/**
 * @brief Campo di tipo TEXT
 *
 * @copyright 2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class TextBuild extends Build {

    /**
     * Proprietà dei campi specifiche del tipo di campo
     */
    protected $_trnsl;

    /**
     * @brief Costruttore
     *
     * @see Gino.Build::__construct()
     * @param array $options array associativo di opzioni del campo del database
     *   - opzioni generali definite come proprietà nella classe Build()
     */
    function __construct($options) {

        parent::__construct($options);

        $this->_trnsl = $options['trnsl'];
    }

    /**
     * @brief Getter della proprietà trnsl (indica se il campo necessita di traduzione o no)
     * @return proprietà trnsl
     */
    public function getTrnsl() {

        return $this->_trnsl;
    }

    /**
     * @brief Setter della proprietà trnsl
     * @param bool $value
     * @return void
     */
    public function setTrnsl($value) {

        if(is_bool($value)) $this->_trnsl = $value;
    }

    /**
     * @see Gino.Build::canBeOrdered()
     * @return FALSE
     */
    public function canBeOrdered() {

        return FALSE;
    }

    /**
     * @see Gino.Build::filterWhereClause()
     */
    public function filterWhereClause($value, $options=array()) {

        $value = str_replace("'", "''", $value);

        if(preg_match("#^\"([^\"]*)\"$#", $value, $matches)) {
            $condition = "='".$matches[1]."'";
        }
        elseif(preg_match("#^\"([^\"]*)$#", $value, $matches)) {
            $condition = " LIKE '".$matches[1]."%'";
        }
        else {
            $condition = " LIKE '%".$value."%'";
        }

        return $this->_table.".".$this->_name.$condition;
    }

    /**
     * @see Gino.Build::formElement()
     */
    public function formElement($mform, $options=array()) {

        if(!isset($options['trnsl'])) $options['trnsl'] = $this->_trnsl;
        if(!isset($options['field'])) $options['field'] = $this->_name;

        if(isset($options['is_filter']) and $options['is_filter']) {
            $options['widget'] = 'textarea';
        }

        return parent::formElement($mform, $options);
    }

    /**
     * @see Gino.Build::clean()
     * 
     * @param array $options array associativo di opzioni
     *   - opzioni delle funzioni Gino.clean_text(), Gino.clean_html()
     *   - @b widget (string): widget (editor!textarea)
     *   - @b typeoftext (string): tipo di dato da ripulire; accetta i valori @a text (default) e @a html
     * @return string or null
     */
    public function clean($request_value, $options=null) {
    	
    	$widget = gOpt('widget', $options, null);
    	$typeoftext = gOpt('typeoftext', $options, 'text');
    	
    	if($widget == 'editor') {
    		return clean_html($request_value, $options);
    	}
    	else {
    		if($typeoftext == 'text') {
    			return clean_text($request_value, $options);
    		} elseif($typeoftext == 'html') {
    			return clean_html($request_value, $options);
    		} else {
    			return null;
    		}
    	}
    }
}
