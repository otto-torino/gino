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
    public static $columns;

    /**
     * @brief Costruttore
     * 
     * @param integer $id valore ID del record
     * @return void, istanza di Gino.App.Page.PageCategory
     */
    function __construct($id) {

        $this->_tbl_data = self::$table;

        parent::__construct($id);

        $this->_model_label = _('Categoria');
    }

    /**
     * @brief Rappresentazione a stringa dell'oggetto
     * @return string, nome categoria
     */
    function __toString() {

        return (string) $this->name;
    }
    
    /**
     * Struttura dei campi della tabella di un modello
     *
     * @return array
     */
    public static function columns() {
    
    	$controller = new page();
    
    	$columns['id'] = new \Gino\IntegerField(array(
    			'name'=>'id',
    			'primary_key'=>true,
    			'auto_increment'=>true,
    	));
    	$columns['name'] = new \Gino\CharField(array(
    			'name'=>'name',
    			'label'=>_("Nome"),
    			'required'=>true,
    			'max_lenght'=>60,
    	));
    	$columns['description'] = new \Gino\TextField(array(
    			'name'=>'description',
    			'label' => _("Descrizione"),
    			'required'=>false
    	));
    	$columns['date'] = new \Gino\DatetimeField(array(
    			'name'=>'date',
    			'label'=>_('Data'),
    			'required'=>true,
    			'auto_now'=>false,
    			'auto_now_add'=>true,
    	));
    	
    	return $columns;
    }
}

PageCategory::$columns=PageCategory::columns();
