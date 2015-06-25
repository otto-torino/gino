<?php
/**
 * @file class.YearFieldBuild.php
 * @brief Contiene la definizione ed implementazione della classe Gino.YearFieldBuild
 *
 * @copyright 2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

/**
 * @brief Gestisce i campi di tipo ANNO
 *
 * @copyright 2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class YearFieldBuild extends IntegerFieldBuild {

    /**
     * @brief Costruttore
     *
     * @see Gino.FieldBuild::__construct()
     * @param array $options array associativo di opzioni del campo del database
     *   - opzioni generali definite come proprietà nella classe FieldBuild()
     *   - opzioni generali definite come proprietà nella classe IntegerFieldBuild()
     */
    function __construct($options) {

        parent::__construct($options);
    }
}
