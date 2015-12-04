<?php
/**
 * @file class.SlugBuild.php
 * @brief Contiene la definizione ed implementazione della classe Gino.SlugBuild
 *
 * @copyright 2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

namespace Gino;

Loader::import('class/build', '\Gino\Build');

/**
 * @brief Getisce i campi di tipo SLUG (CHAR, VARCHAR)
 *
 * @copyright 2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class SlugBuild extends Build {

    /**
	 * Proprietà dei campi specifiche del tipo di campo
	 */
	protected $_autofill, $_trnsl;

    /**
     * @brief Costruttore
     *
     * @see Gino.Build::__construct()
     * @param array $options array associativo di opzioni del campo del database
     *   - opzioni generali definite come proprietà nella classe Build()
     */
    function __construct($options) {

        parent::__construct($options);
		
        $this->_autofill = $options['autofill'];
        $this->_trnsl = $options['trnsl'];
    }

    /**
     * @see Gino.Build::formElement()
     * @description Aggiunge il codice javascript che permette l'autoriempimento del campo se è stata passata l'opzione autofill.
     */
    public function formElement($mform, $options=array()) {

        if(!isset($options['field'])) $options['field'] = $this->_name;
        $options['id'] = $this->_name;
        $widget = parent::formElement($mform, $options);

        // autofill solo in inserimento
        if(!$this->_model->id and $this->_autofill) {
            $autofill = is_string($this->_autofill) ? array($this->_autofill) : $this->_autofill;
            $widget .= "<script>";
            $widget .= sprintf("gino.slugControl('%s', '%s')", $this->_name, json_encode($autofill));
            $widget .= "</script>";
        }

        return $widget;
    }
    
    /**
     * @see Gino.Build::clean()
     * @description Controlla la preesistenza del valore nei record della tabella
     * 
     * @param array $options array associativo di opzioni
     *   - opzioni della funzione Gino.clean_text()
     *   - @b model_id (integer): valore id del modello
     * @return string
     */
    public function clean($options=null) {
    
    	parent::clean($options);
    	$value = clean_text($this->_request_value, $options);
    	
    	if(is_null($value)) {
    		return null;
    	}
    	else
    	{
    		$db = \Gino\Db::instance();
    		
    		$model_id = gOpt('model_id', $options, null);
    		$where = $this->_name."='".$value."'";
    		if($model_id) {
    			$where .= " AND id!='".$model_id."'";
    		}
    	
    		$res = $db->select('id', $this->_table, $where);
    		if($res && count($res)) {
    			throw new \Exception(_("Il nome scelto per lo slug è già stato utilizzato.<br />Cambiare nome per proseguire col salvataggio."));
    		}
    		else {
    			return $value;
    		}
    	}
    }
}
