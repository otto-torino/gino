<?php
/**
 * @file class.ImageField.php
 * @brief Contiene la definizione ed implementazione della classe Gino.ImageField
 *
 * @copyright 2005-2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

loader::import('class/fields', array('\Gino\Field', '\Gino\FileField'));

/**
 * @brief Campo di tipo IMMAGINE
 *
 * Tipologie di input associabili: input file
 *
 * @copyright 2005-2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class ImageField extends FileField {

    /**
     * @brief Costruttore
     *
     * @see Gino.Field::__construct()
     * @param array $options array associativo di opzioni del campo del database
     *   - opzioni generali definite come proprietà nella classe Field()
     *   - opzioni generali definite come proprietà nella classe FileField()
     */
    function __construct($options) {

        parent::__construct($options);

        $this->_default_widget = 'image';
    }
}
