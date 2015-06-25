<?php
/**
 * @file class.ManyToManyFieldBuild.php
 * @brief Contiene la definizione ed implementazione della classe Gino.ManyToManyField
 *
 * @copyright 2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

loader::import('class/fields', '\Gino\FieldBuild');

/**
 * @brief Gestisce i campi di tipo many to many
 *
 * @copyright 2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class ManyToManyFieldBuild extends FieldBuild {

    /**
     * Proprietà dei campi specifiche del tipo di campo
     */
    protected $_m2m, $_m2m_order, $_m2m_where;
    protected $_join_table, $_join_table_id, $_join_table_m2m_id;
    protected $_add_related, $_add_related_url;
    protected $_enum;

    /**
     * @brief Costruttore
     *
     * @see Gino.FieldBuild::__construct()
     * @param array $options array associativo di opzioni del campo del database
     *   - opzioni generali definite come proprietà nella classe FieldBuild()
     *   - @b add_related (bool): includere o meno un bottone che permetta l'inserimento di nuovi modelli correlati nello stesso contesto
     *   - @b add_related_url (string): path alla vista per inserimento modello correlato
     *   - @b m2m (object): classe del modello correlato (nome completo di namespace)
     *   - @b m2m_where (mixed): condizioni della query per filtrare i possibili modelli da associare
     *     - @a string, es. "cond1='$cond1' AND cond2='$cond2'"
     *     - @a array, es. array("cond1='$cond1'", "cond2='$cond2'")
     *   - @b m2m_order (string): ordinamento dei valori (es. name ASC)
     *   - @b m2m_controller (\Gino\Controller): classe Controller del modello m2m, essenziale se il modello appartiene ad un modulo istanziabile
     *   - @b join_table (string): nome tabella di join
     */
    function __construct($options) {

        parent::__construct($options);

        $this->_add_related = array_key_exists('add_related', $options) ? $options['add_related'] : false;
        $this->_add_related_url = array_key_exists('add_related_url', $options) ? $options['add_related_url'] : '';

        $this->_m2m = $options['m2m'];
        $this->_m2m_where = array_key_exists('m2m_where', $options) ? $options['m2m_where'] : null;
        $this->_m2m_order = array_key_exists('m2m_order', $options) ? $options['m2m_order'] : 'id';
        $this->_m2m_controller = array_key_exists('m2m_controller', $options) ? $options['m2m_controller'] : null;
        $this->_join_table = $options['join_table'];
        
        $this->_self = $options['self'];
        $this->_join_table_id = strtolower(get_name_class($this->_self)).'_id';
        //$this->_join_table_id = strtolower(get_name_class($this->_model)).'_id';
        $this->_join_table_m2m_id = strtolower(get_name_class($this->_m2m)).'_id';

        /*
        $db = db::instance();
        $rows = $db->select('*', $this->_join_table, $this->_join_table_id."='".$this->_model->id."'");
        $values = array();
        foreach($rows as $row) {
            $values[] = $row[$this->_join_table_m2m_id];
        }
        $this->_model->addm2m($this->_name, $values);
		*/
    }

    /**
     * @brief Rappresentazione a stringa dell'oggetto
     * @return rappresentazione a stringa dei modelli associati separati da virgola
     */
    public function __toString() {

        $res = array();
        foreach($this->_model->{$this->_name} as $id) {
            if($this->_m2m_controller) {
                $obj = new $this->_m2m($id, $this->_m2m_controller);
            }
            else {
                $obj = new $this->_m2m($id);
            }
            $res[] = (string) $obj;
        }
        return implode(', ', $res);
    }

    /**
     * @brief Getter della proprietà jon_table
     * @return proprietà join_table
     */
    public function getJoinTable() {
        return $this->_join_table;
    }

    /**
     * @brief Getter della proprietà join_table_id (nome della chiave esterna nella tabella di join del modello che ha il campo)
     * @retuirn proprietà join_table_id
     */
    public function getJoinTableId() {
        return $this->_join_table_id;
    }

    /**
     * @brief Getter della proprietà join_table_m2m_id (nome della chiave esterna nella tabella di join del modello m2m)
     * @retuirn proprietà join_table_m2m_id
     */
    public function getJoinTableM2mId() {
        return $this->_join_table_m2m_id;
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
        if($this->_m2m_controller) {
            $m2m = new $this->_m2m(null, $this->_m2m_controller);
        }
        else {
            $m2m = new $this->_m2m(null);
        }
        $rows = $db->select('id', $m2m->getTable(), $this->_m2m_where, array('order' => $this->_m2m_order));
        $enum = array();
        $selected_part = array();
        $not_selected_part = array();
        $this->_value = $this->_model->{$this->_name};
        foreach($rows as $row) {
            if($this->_m2m_controller) {
                $m2m = new $this->_m2m($row['id'], $this->_m2m_controller);
            }
            else {
                $m2m = new $this->_m2m($row['id']);
            }
            //$enum[$row['id']] = (string) $m2m;
            if(is_array($this->_value) and in_array($row['id'], $this->_value)) {
                $selected_part[$row['id']] = (string) $m2m;
            }
            else {
                $not_selected_part[$row['id']] = (string) $m2m;
            }
        }

        $enum = $selected_part + $not_selected_part;

        $this->_enum = $enum;
        $this->_name .= "[]";

        if($this->_add_related) {
            $options['add_related'] = array(
                'title' => _('inserisci').' '.$m2m->getModelLabel(),
                'id' => 'add_'.$this->_name,
                'url' => $this->_add_related_url
            );
        }

        return parent::formElement($form, $options);
    }

    /**
     * @brief Formatta un elemento input per l'inserimento in database
     * @see Gino.Field::clean()
     * @see Gino.cleanVar()
     * @param array $options
     *   array associativo di opzioni
     *   - @b value_type (string): tipo di valore
     *   - @b method (array): metodo di recupero degli elementi del form
     *   - @b escape (boolean): evita che venga eseguito il mysql_real_escape_string sul valore del campo
     *   - @b asforminput (boolean)
     * @return valore ripulito
     */
    public function clean($options=null) {

        $request = \Gino\Http\Request::instance();
        $value_type = $this->_value_type;
        $method = isset($options['method']) ? $options['method'] : $request->POST;
        $escape = gOpt('escape', $options, true);

        $value = cleanVar($method, $this->_name, $value_type, null, array('escape'=>$escape));

        return is_null($value) ? array() : $value;
    }

    /**
     * @brief Definisce la condizione WHERE per il campo
     * @see Gino.Field::filterWhereClause()
     */
    public function filterWhereClause($value) {

        $parts = array();
        foreach($value as $v) {
            $parts[] = $this->_table.".".$this->_name." REGEXP '[[:<:]]".$v."[[:>:]]'";
        }

        return "(".implode(' OR ', $parts).")";
    }
    
    /**
     * @see Gino.FieldBuild::retrieveValue()
     */
    public function retrieveValue() {
    	
    	$db = \Gino\Db::instance();
    	
    	$rows = $db->select('*', $this->_join_table, $this->_join_table_id."='".$this->_value."'");
    	$values = array();
    	$m2m_class = $this->_m2m;
    	
    	foreach($rows as $row) {
    		
    		$m2m_id = $row[$this->_join_table_m2m_id];
    		
    		$values[] = new $m2m_class($m2m_id);
    	}
    	return $values;
    }
}
