<?php
/**
 * @file class.PageForm.php
 * Contiene la definizione ed implementazione della classe Gino.App.Page.PageForm.
 * 
 * @copyright 2015 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */
namespace Gino\App\Page;

\Gino\Loader::import('class', array('\Gino\ModelForm'));

/**
 * @brief Classe tipo Gino.Form che rappresenta il form di una pagina
 *
 * @copyright 2015 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */
class PageForm {

    /**
     * Costruttore
     * 
     * @param integer $id valore ID del record
     * @param object $instance istanza del controller
     */
    function __construct($model, $options=array()) {

    	return new \Gino\ModelForm($model, $options);
    }
    
    
}
