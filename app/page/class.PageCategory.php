<?php
/**
 * @file class.PageCategory.php
 * Contiene la definizione ed implementazione della classe Gino.App.Page.PageCategory.
 * @copyright 2013-2014 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */

namespace Gino\App\Page;

/**
 * @brief Classe tipo Gino.Model che rappresenta una categoria di pagine
 *
 * @copyright 2013-2014 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */
class PageCategory extends \Gino\Model {

    public static $table = "page_category";

    /**
     * @brief Costruttore
     * 
     * @param integer $id valore ID del record
     * @return istanza di Gino.App.Page.PageCategory
     */
    function __construct($id) {

        $this->_controller = new page();
        $this->_tbl_data = self::$table;

        $this->_fields_label = array(
            'name'=>_("Nome"), 
            'description'=>_('Descrizione')
        );

        parent::__construct($id);

        $this->_model_label = _('Categoria');
    }

    /**
     * @brief Rappresentazione a stringa dell'oggetto
     * @return nome categoria
     */
    function __toString() {

        return (string) $this->name;
    }
}
