<?php
/**
 * @file class.DirectoryField.php
 * @brief Contiene la definizione ed implementazione della classe Gino.DirectoryField
 *
 * @copyright 2005-2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

/**
 * @brief Campo di tipo DIRECTORY
 *
 * @copyright 2005-2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class DirectoryField extends Field {

	/**
	 * Proprietà dei campi specifiche del tipo di campo
	 */
	protected $_path, $_prefix, $_default_name;
	
    /**
     * @brief Costruttore
     *
     * @see Gino.Field::__construct()
     * @param array $options array associativo di opzioni del campo del database
     *   - opzioni generali definite come proprietà nella classe Field()
     *   - opzioni specifiche del tipo di campo
     *     - @b path (string): percorso assoluto della directory superiore
     *     - @b prefix (string): prefisso da aggiungere al nome della directory
     *     - @b default_name (array): valori per il nome di default
     *       - @a field (string): nome dell'input dal quale ricavare il nome della directory (default id)
     *       - @a maxlentgh (integer): numero di caratteri da considerare nel nome dell'input (default 15)
     *       - @a value_type (string): tipo di valore (default string)
     */
    function __construct($options) {

    	$this->_default_widget = 'text';
    	parent::__construct($options);

        $this->_value_type = 'string';
        
        $this->_path = isset($options['path']) ? $options['path'] : '';
        if(!$this->_path) {
        	throw new \Exception(_('Parametro path inesistente'));
        }
        if(substr($this->_path, -1) !== OS) {
        	$this->_path .= OS;
        }
        $this->_prefix = isset($options['prefix']) ? $options['prefix'] : '';
        $this->_default_name = isset($options['default_name']) ? $options['default_name'] : array();
    }
    
    /**
     * @see Gino.Field::getProperties()
     */
	public function getProperties() {

		$prop = parent::getProperties();

		$prop['path'] = $this->_path;
		$prop['prefix'] = $this->_prefix;
		$prop['default_name'] = $this->_default_name;

		return $prop;
	}

    /**
     * @see Gino.Field::getFormatValue()
     * @return null or string
     */
    public function getFormatValue($value) {
    	 
    	if(is_null($value)) {
    		return null;
    	}
    	elseif(is_string($value)) {
    		return $value;
    	}
    	else throw new \Exception(_("Valore non valido"));
    }
}
