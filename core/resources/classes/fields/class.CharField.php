<?php
/**
 * @file class.CharField.php
 * @brief Contiene la definizione ed implementazione della classe Gino.CharField
 *
 * @copyright 2005-2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

namespace Gino;

loader::import('class/fields', '\Gino\Field');

/**
 * @brief Campi di tipo stringa (CHAR, VARCHAR)
 *
 * Tipologie di input associabili: testo, campo nascosto, textarea
 *
 * @copyright 2005-2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class CharField extends Field {

	/**
	 * Proprietà dei campi specifiche del tipo di campo
	 */
	protected $_trnsl;
	
    /**
     * @brief Costruttore
     *
     * @see Gino.Field::__construct()
     * @param array $options array associativo di opzioni del campo del database
     *   - opzioni generali definite come proprietà nella classe Field()
     *   - opzioni specifiche del tipo di campo
     *     - @b trnsl (boolean): campo con traduzioni
     * @return istanza di Gino.CharField
     */
    function __construct($options) {

        $this->_default_widget = 'text';
        parent::__construct($options);
        
        $this->_trnsl = isset($options['trnsl']) ? $options['trnsl'] : TRUE;
    }
    
    /**
     * @see Gino.Field::getProperties()
     */
    public function getProperties() {
    	 
    	$prop = parent::getProperties();
    	 
    	$prop['trnsl'] = $this->_trnsl;
    	 
    	return $prop;
    }
}
