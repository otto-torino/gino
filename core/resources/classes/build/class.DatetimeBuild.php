<?php
/**
 * @file class.DatetimeBuild.php
 * @brief Contiene la definizione ed implementazione della classe Gino.DatetimeBuild
 *
 * @copyright 2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

Loader::import('class/build', '\Gino\Build');

/**
 * @brief Gestisce i campi di tipo DATETIME
 *
 * @description Impostando opportunamente le proprietà @a $_auto_now_add e @a $_auto_now è possibile gestire il campo datetime 
 * in modo che venga impostato soltanto quando viene creato l'oggetto oppure ogni volta che l'oggetto viene salvato.
 *
 * @copyright 2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class DatetimeBuild extends Build {

    /**
	 * Proprietà dei campi specifiche del tipo di campo
	 */
	protected $_auto_now, $_auto_now_add, $_view_input;
	
	/**
     * Costruttore
     *
     * @see Gino.Build::__construct()
     * @param array $options array associativo di opzioni del campo del database
     *   - opzioni generali definite come proprietà nella classe Build()
     */
    function __construct($options) {

        parent::__construct($options);
        
        $this->_auto_now = $options['auto_now'];
        $this->_auto_now_add = $options['auto_now_add'];
        $this->_view_input = $options['view_input'];
    }
    
    /**
     * @brief Rappresentazione a stringa dell'oggetto
     * @return valore del campo
     */
    function __toString() {
    
    	return (string) $this->_value;
    }

    /**
     * @see Gino.Build::filterWhereClause()
     * 
     * @param array $options
     *   array associativo di opzioni
     *   - @b operator (string): operatore di confronto della data
     */
    public function filterWhereClause($value, $options=array()) {

        $operator = gOpt('operator', $options, null);
        if(is_null($operator)) $operator = '=';

        return $this->_table.".".$this->_name." $operator '".$value."'";
    }
    
    /**
     * @see Gino.Build::clean()
     * 
     * @param array $options array associativo di opzioni
     *   - opzioni della funzione Gino.clean_date()
     * @return string
     */
    public function clean($request_value, $options=null) {

        if($this->_auto_now || $this->_auto_now_add)
        {
            if(!$this->_value || ($this->_value && $this->_auto_now))
            {
                $date = date("Y-m-d H:i:s");
            }
            else
            {
                $date = $this->_value;
            }
            return $date;
        }
        else
        {
        	if(!$options['typeofdate']) {
        		$options['typeofdate'] = 'datetime';
        	}
        	
        	return clean_date($request_value, $options);
        }
    }
}
