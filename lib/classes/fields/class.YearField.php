<?php
/**
 * @file class.YearField.php
 * @brief Contiene la definizione ed implementazione della classe Gino.YearField
 *
 * @copyright 2005-2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

/**
 * @brief Campo di tipo ANNO
 *
 * @copyright 2005-2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class YearField extends IntegerField {

    /**
     * @brief Costruttore
     *
     * @see Gino.Field::__construct()
     * @param array $options array associativo di opzioni del campo del database
     *   - opzioni generali definite come proprietà nella classe Field()
     *   - opzioni generali definite come proprietà nella classe IntegerField()
     * @return istanza di Gino.YearField
     */
    function __construct($options) {

        parent::__construct($options);
        
        $this->_default_widget = 'text';
    }

    /**
     * @brief Widget html per il form
     * @description Rispetto al parent imposta una maxlength
     * @param \Gino\Form $form istanza di Gino.Form
     * @param array $options opzioni
     * @see Gino.Field::formElement()
     * @return widget html
     */
    public function formElement(\Gino\Form $form, $options) {

        $options['maxlength'] = 4;

        return parent::formElement($form, $options);
    }
}
