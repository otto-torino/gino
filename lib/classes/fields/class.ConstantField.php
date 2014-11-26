<?php
/**
 * @file class.ConstantField.php
 * @brief Contiene la definizione ed implementazione della classe Gino.ConstantField
 *
 * @copyright 2005-2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

/**
 * @brief Campo di tipo nascosto che mostra anche il valore corrispondente senza input
 *
 * Tipologie di input associabili: campo nascosto
 * Il valore costante è il valore del campo di un record di un'altra tabella
 *
 * @deprecated Gino.ConstantField Utilizzare il tipo di campo adeguato e lavorare sulla visibilità e editabilità del widget
 * @copyright 2005-2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class ConstantField extends Field {

    /**
     * Proprietà dei campi specifiche del tipo di campo
     */
    protected $_view_value, $_const_table, $_const_id, $_const_field;

    /**
     * @brief Costruttore
     *
     * @see Gino.Field::__construct()
     * @param array $options array associativo di opzioni del campo del database
     *   - opzioni generali definite come proprietà nella classe field()
     *   - @b const_table (string): nome della tabella dei dati
     *   - @b const_id (string): nome del campo della chiave nel SELECT (default: id)
     *   - @b const_field (mixed): nome del campo o dei campi dei valori nel SELECT
     *     - @a string, nome del campo
     *     - @a array, nomi dei campi da concatenare, es. array('firstname', 'lastname')
     * @return istanza di Gino.ConstantField
     */
    function __construct($options) {

        parent::__construct($options);

        $this->_default_widget = 'constant';
        $this->_value_type = 'int';

        $this->_const_table = array_key_exists('const_table', $options) ? $options['const_table'] : null;
        $this->_const_id = array_key_exists('const_id', $options) ? $options['const_id'] : 'id';
        $this->_const_field = array_key_exists('const_field', $options) ? $options['const_field'] : null;
    }

    /**
     * @brief Rappresentazione a stringa dell'oggetto
     * @return valore del campo
     */
    public function __toString() {

        $db = Db::instance();
        $value = $db->getFieldFromId($this->_const_table, $this->_const_field, $this->_const_id, $this->_value);

        return (string) $value;
    }

    /**
     * @brief Getter della proprietà view_value
     * @return proprietà view_value
     */
    public function getViewValue() {

        return $this->_view_value;
    }

    /**
     * @brief Setter della proprietà view_value
     * @param string $value
     * @return void
     */
    public function setViewValue($value) {

        $this->_view_value = $value;
    }

    /**
     * @brief Getter della proprietà const_table
     * @return proprietà const_table
     */
    public function getConstantTable() {

        return $this->_const_table;
    }

    /**
     * @brief Setter della proprietà const_table
     * @param string $value
     * @return void
     */
    public function setConstantTable($value) {

        $this->_const_table = $value;
    }

    /**
     * @brief Getter della proprietà const_id
     * @return proprietà const_id
     */
    public function getConstantId() {

        return $this->_const_id;
    }

    /**
     * @brief Setter della proprietà const_id
     * @param int $value
     * @return void
     */
    public function setConstantId($value) {

        $this->_const_id = $value;
    }

    /**
     * @brief Getter della proprietà const_field
     * @return proprietà const_field
     */
    public function getConstantField() {

        return $this->_const_field;
    }

    /**
     * @brief Setter della proprietà const_field
     * @param string $value
     * @return void
     */
    public function setConstantField($value) {

        $this->_const_field = $value;
    }

    /**
     * @brief Widget html per il form
     * @param \Gino\Form $form istanza di Gino.Form
     * @param array $options opzioni
     * @see Gino.Field::formElement()
     * @return widget html
     */
    public function formElement(\Gino\Form $form, $options) {

        $this->_view_value = $this ? $this : $this->_value;

        return parent::formElement($form, $options);
    }
}
?>
