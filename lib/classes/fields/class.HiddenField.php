<?php
/**
 * @file class.HiddenField.php
 * @brief Contiene la definizione ed implementazione della classe Gino.HiddenField
 *
 * @copyright 2005-2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

/**
 * @deprecated Gino.HiddenField Utilizzare al suo posto la tipologia di campo corretta ed impostare il widget hidden
 * @brief Campo di tipo nascosto (estensione)
 * Tipologie di input associabili: campo nascosto
 *
 * @copyright 2005-2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class HiddenField extends Field {

    /**
     * @brief Costruttore
     *
     * @see Gino.Field::__construct()
     * @param array $options array associativo di opzioni del campo del database
     *   - opzioni generali definite come proprietà nella classe field()
     * @return istanza di Gino.HiddenField
     */
    function __construct($options) {

        parent::__construct($options);

        $this->_default_widget = 'hidden';
    }

    /**
     * @brief Widget html per il form
     * @param \Gino\Form $form istanza di Gino.Form
     * @param array $options opzioni
     * @see Gino.Field::formElement()
     * @return widget html
     */
    public function formElement(\Gino\Form $form, $options) {

        return parent::formElement($form, $options);
    }
}
