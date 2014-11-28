<?php
/**
 * @file class.SlugField.php
 * @brief Contiene la definizione ed implementazione della classe Gino.SlugField
 *
 * @copyright 2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

namespace Gino;

loader::import('class/fields', '\Gino\Field');

/**
 * @brief Campo di tipo SLUG (CHAR, VARCHAR)
 *
 * I campi slug sono utilizzati per l'inserimento della parte caratterizzante di un pretty url,
 * in genere utilizzato al posto dell'id per identificare un oggetto
 *
 * @copyright 2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class SlugField extends Field {

    private $_autofill,
            $_js;

    /**
     * @brief Costruttore
     *
     * @see Gino.Field::__construct()
     * @param array $options array associativo di opzioni del campo del database
     *   - opzioni generali definite come proprietà nella classe field()
     *   - @b autofill (string|array): nome o array di nomi dei campi da utilizzare per calcolare lo slug. Se vengono dati più campi vengono concatenati con un dash '-'.
     * @return istanza di Gino.SlugField
     */
    function __construct($options) {

        parent::__construct($options);

        $this->_autofill = \Gino\gOpt('autofill', $options, null);
        $this->_js = \Gino\gOpt('js', $options, null);

        $this->_default_widget = 'text';
        $this->_value_type = 'string';

        $this->_trnsl = FALSE;
    }

    /**
     * @brief Widget html per il form
     * @description Aggiunge il codice javascript che permette l'autoriempimento del campo
     *              se è stata passata l'opzione autofill.
     * @param \Gino\Form $form istanza di Gino.Form
     * @param array $options opzioni
     * @see Gino.Field::formElement()
     * @return widget html
     */
    public function formElement(\Gino\Form $form, $options) {

        if(!isset($options['field'])) $options['field'] = $this->_name;
        $options['id'] = $this->_name;
        $widget = parent::formElement($form, $options);

        // autofill solo in inserimento
        if(!$this->_model->id and $this->_autofill) {
            $autofill = is_string($this->_autofill) ? array($this->_autofill) : $this->_autofill;
            $widget .= "<script>";
            $widget .= sprintf("gino.slugControl('%s', '%s')", $this->_name, json_encode($autofill));
            $widget .= "</script>";
        }

        return $widget;
    }

}
