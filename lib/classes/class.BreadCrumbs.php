<?php
/**
 * @file class.BreadCrumbs.php
 * @brief Contiene la definizione ed implementazione della classe Gino.BreadCrumbs
 *
 * @copyright 2016 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

/**
 * @brief Briciole di pane
 *
 * @copyright 2016 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 * 
 * ##DEFINIZIONE DEGLI ELEMENTI
 * Gli elementi devono essere definiti come array di array; segue un esempio: \n
 * @code
 * array(
 *   array('label' => _("Gallerie"), 'link' => $this->link('gallery', 'index')), 
 *   array('label' => $category->ml('name'), 'current' => true)
 * )
 * @endcode
 * 
 * Per ogni elemento possono essere definite le seguenti chiavi: \n
 * - label (string), definizione da mostrare
 * - link (string), indirizzo da collegare a label
 * - current (boolean), elemento corrente
 * 
 * ##ESEMPIO DI UTILIZZO
 * 1. Ricavare le briciole di pane per passarle come variabile alla vista
 * @code
 * $obj = new \Gino\BreadCrumbs($this->_class_name);
 * $obj->setItems(array(
 *   array('label' => _("Gallerie"), 'link' => $this->link('gallery', 'index')), 
 *   array('label' => $category->ml('name'), 'current' => true)
 * ));
 * $breadcrumbs = $obj->render();
 * @endcode
 * 
 * 2. nella vista
 * @code
 * <? if($breadcrumbs): ?>
 *   <?= $breadcrumbs ?>
 * <? endif ?>
 * <section id="gallery-category">
 *   ...
 * </section>
 * @endcode
 */
class BreadCrumbs {
	
	private $_id, $_items;

    /**
     * Costruttore
     *
     * @return void
     */
    public function __construct($id=null) {
    	
        $this->_id = $id;
    	$this->_items = array();
    }
    
    /**
     * Recupera tutti gli elementi in ordine (parent->son)
     *
     * @return string
     */
    public function getId() {
    	return $this->_id;
    }
    
    /**
     * Imposta l'identificativo delle briciole di pane
     *
     * @param string $id identificativo breadcrumbs
     * @return void
     */
    public function setId($id) {
    	$this->_id = $id;
    }
    
    /**
     * Recupera tutti gli elementi in ordine (parent->son)
     *
     * @return array di elementi
     */
    public function getItems() {
    	return $this->_items;
    }
    
    /**
     * Imposta gli elementi che compongono le briciole di pane
     * 
     * @param array $items elementi briciole di pane
     * @return void
     */
    public function setItems($items) {
		$this->_items = $items;
    }

    /**
     * Stampa gli elementi in cascata
     * 
     * @access public
     * @return string
     */
    public function render() {
    	
        $view = new View();
        $view->setViewTpl('breadcrumbs');
        $dict = array(
            'id' => $this->_id,
        	'items' => $this->_items,
        );
        $buffer = $view->render($dict);
        return $buffer;
    }
}
