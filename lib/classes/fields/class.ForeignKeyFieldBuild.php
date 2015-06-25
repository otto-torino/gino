<?php
/**
 * @file class.ForeignKeyField.php
 * @brief Contiene la definizione ed implementazione delal classe Gino.ForeignKeyField
 *
 * @copyright 2005-2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

loader::import('class/fields', '\Gino\FieldBuild');

/**
 * @brief Campo di tipo chiave esterna
 *
 * I valori da associare al campo risiedono in una tabella esterna e i parametri per accedervi devono essere definiti nelle opzioni del campo. \n
 * Tipologie di input associabili: select, radio
 *
 * @copyright 2005-2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class ForeignKeyFieldBuild extends FieldBuild {

    /**
     * Proprietà dei campi specifiche del tipo di campo
     */
    protected $_foreign, $_foreign_where, $_foreign_order;
    protected $_add_related, $_add_related_url;
    protected $_enum;

    /**
     * @brief Costruttore
     *
     * @see Gino.Field::__construct()
     * @param array $options array associativo di opzioni del campo del database
     *   - opzioni generali definite come proprietà nella classe field()
     *   - @b foreign (string): nome della classe della chiave esterna
     *   - @b foreign_where (mixed): condizioni della query
     *     - @a string, es. "cond1='$cond1' AND cond2='$cond2'"
     *     - @a array, es. array("cond1='$cond1'", "cond2='$cond2'")
     *   - @b foreign_order (string): ordinamento dei valori (es. name ASC); default 'id'
     *   - @b foreign_controller (object): oggetto del controller della classe della chiave esterna
     * @return istanza di Gino.ForeignKeyField
     */
    function __construct($options) {

        parent::__construct($options);

        $this->_foreign = $options['foreign'];
        $this->_foreign_where = array_key_exists('foreign_where', $options) ? $options['foreign_where'] : null;
        $this->_foreign_order = array_key_exists('foreign_order', $options) ? $options['foreign_order'] : 'id';
        $this->_foreign_controller = array_key_exists('foreign_controller', $options) ? $options['foreign_controller'] : null;
        $this->_add_related = array_key_exists('add_related', $options) ? $options['add_related'] : false;
        $this->_add_related_url = array_key_exists('add_related_url', $options) ? $options['add_related_url'] : '';
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
     * @brief Getter della proprietà enum (scelte disponibili)
     * @return proprietà enum
     */
    public function getEnum() {

        return $this->_enum;
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
        $enum = array();
        foreach($rows as $row) {
            if($this->_foreign_controller) {
                $f = new $this->_foreign($row['id'], $this->_foreign_controller);
            }
            else {
                $f = new $this->_foreign($row['id']);
            }
            $enum[$f->id] = (string) $f;
        }

        $this->_enum = $enum;

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
