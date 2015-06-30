<?php
/**
 * @file class.ForeignKeyBuild.php
 * @brief Contiene la definizione ed implementazione delal classe Gino.ForeignKeyBuild
 *
 * @copyright 2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

loader::import('class/build', '\Gino\Build');

/**
 * @brief Campo di tipo chiave esterna
 *
 * I valori da associare al campo risiedono in una tabella esterna e i parametri per accedervi devono essere definiti nelle opzioni del campo. \n
 * Tipologie di input associabili: select, radio
 *
 * @copyright 2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class ForeignKeyBuild extends Build {

	/**
	 * @brief Proprietà dei campi
	 */
	protected $_foreign, $_foreign_where, $_foreign_order, $_add_related, $_add_related_url;
	
	protected $_choice;
	
    /**
     * @brief Costruttore
     *
     * @see Gino.Build::__construct()
     * @param array $options array associativo di opzioni del campo del database
     *   - opzioni generali definite come proprietà nella classe Build()
     */
    function __construct($options) {

        parent::__construct($options);
        
        $this->_foreign = $options['foreign'];
        $this->_foreign_where = $options['foreign_where'];
        $this->_foreign_order = $options['foreign_order'];
        $this->_foreign_controller = $options['foreign_controller'];
        $this->_add_related = $options['add_related'];
        $this->_add_related_url = $options['add_related_url'];
    }

    /**
     * @brief Rappresentazione a stringa dell'oggetto
     * @return valore campo
     */
    public function __toString() {

    	if($this->_foreign_controller) {
        	$obj = new $this->_foreign($this->_model->{$this->_name}, $this->_foreign_controller);
        }
        else {
        	$obj = new $this->_foreign($this->_model->{$this->_name});
        }
        return (string) $obj;
    }

    /**
     * @brief Getter della proprietà choice (scelte disponibili)
     * @return array
     */
    public function getChoice() {

        return $this->_choice;
    }

    /**
     * @brief Widget html per il form
     * @param \Gino\Form $form istanza di Gino.Form
     * @param array $options opzioni
     * @see Gino.Field::formElement()
     * @return widget html
     */
    public function formElement(\Gino\Form $form, $options) {

        $db = db::instance();
        if($this->_foreign_controller) {
            $foreign = new $this->_foreign(null, $this->_foreign_controller);
        }
        else {
            $foreign = new $this->_foreign(null);
        }
        $rows = $db->select('id', $foreign->getTable(), $this->_foreign_where, array('order' => $this->_foreign_order));
        $choice = array();
        foreach($rows as $row) {
            if($this->_foreign_controller) {
                $f = new $this->_foreign($row['id'], $this->_foreign_controller);
            }
            else {
                $f = new $this->_foreign($row['id']);
            }
            $choice[$f->id] = (string) $f;
        }

        $this->_choice = $choice;

        if($this->_add_related && (!isset($options['is_filter']) or !$options['is_filter'])) {
            $options['add_related'] = array(
                'title' => _('inserisci').' '.$foreign->getModelLabel(),
                'id' => 'add_'.$this->_name,
                'url' => $this->_add_related_url
            );
        }

        return parent::formElement($form, $options);
    }
    
    /**
     * @see Gino.FieldBuild::retrieveValue()
     * @return object
     */
    public function retrieveValue() {
    	 
    	if(is_object($this->_value)) {
    		return new $this->_foreign((int) $this->_value->id);
    	}
    	elseif(is_null($this->_value))
    		return null;
    	else
    		return new $this->_foreign((int) $this->_value);
    }
}
