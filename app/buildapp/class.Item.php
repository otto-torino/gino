<?php
/**
 * @file class.Item.php
 * Contiene la definizione ed implementazione della classe Gino.App.BuildApp.Item.
 * 
 * @copyright 2017 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */
namespace Gino\App\BuildApp;

/**
 * @brief Classe tipo Gino.Model che rappresenta una una applicazione generata
 *
 * @copyright 2017 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */
class Item extends \Gino\Model {

    public static $table = 'buildapp_item';
    public static $columns;

    /**
     * Costruttore
     * 
     * @param integer $id valore ID del record
     * @param object $instance istanza del controller
     */
    function __construct($id) {

        $this->_tbl_data = self::$table;
        
        $this->_controller = new buildapp();
        
        parent::__construct($id);
        
        $this->_model_label = _("Applicazione");
    }

    /**
     * @brief Rappresentazione a stringa dell'oggetto
     * @return titolo
     */
    function __toString() {

        return (string) $this->label;
    }
    
    /**
     * Struttura dei campi della tabella di un modello
     *
     * @return array
     */
    public static function columns() {

        $columns['id'] = new \Gino\IntegerField(array(
			'name' => 'id',
			'primary_key' => true,
			'auto_increment' => true,
        	'max_lenght' => 11,
		));
		$columns['creation_date'] = new \Gino\DatetimeField(array(
			'name' => 'creation_date',
			'label' => _('Inserimento'),
			'required' => true,
			'auto_now' => false,
			'auto_now_add' => true,
		));
		$columns['label'] = new \Gino\CharField(array(
			'name' => 'label',
			'label' => _("Nome"),
			'required' => true,
			'max_lenght' => 200,
		));
		$columns['controller_name'] = new \Gino\CharField(array(
			'name' => 'controller_name',
			'label' => array(_("Nome del Controller"), _("La lettera iniziale deve essere minuscola")),
			'required' => true,
			'max_lenght' => 50,
		));
        $columns['description'] = new \Gino\TextField(array(
        	'name' => 'description',
        	'label' => _("Descrizione"),
        	'required' => true
        ));
        $columns['istantiable'] = new \Gino\BooleanField(array(
        	'name'=>'istantiable',
        	'label' => _("Modulo istanziabile"),
        	'required' => true,
        	'default' => 0
        ));
        $columns['model_name'] = new \Gino\CharField(array(
        	'name' => 'model_name',
        	'label' => array(_("Nome del Modello"), _("La lettera iniziale deve essere maiuscola")),
        	'required' => true,
        	'max_lenght' => 50,
        ));
        $columns['model_label'] = new \Gino\CharField(array(
        	'name' => 'model_label',
        	'label' => _("Label del Modello (ad esempio: Item)"),
        	'required' => true,
        	'max_lenght' => 100,
        ));

        return $columns;
    }

    /**
     * @brief Url relativo record
     * @return url
     */
    public function getUrl() {

        return $this->_registry->router->link('buildapp', 'view', array('id'=>$this->slug));
    }

    /**
     * @brief Restituisce oggetti di tipo @ref Gino.App.BuidApp.Item 
     * 
     * @param array $options array associativo di opzioni
     *   - @b where (string): condizioni personalizzate
     *   - @b order (string)
     *   - @b limit (string)
     *   - @b debug (boolean): default false
     * @return array di istanze di tipo Gino.App.BuidApp.Item
     */
    public static function get($options = null) {

        $res = array();
		
        $where_opt = \Gino\gOpt('where', $options, null);
        $order = \Gino\gOpt('order', $options, 'creation_date');
        $limit = \Gino\gOpt('limit', $options, null);
        $debug = \Gino\gOpt('debug', $options, false);

        $db = \Gino\Db::instance();
        
        $where = self::setConditionWhere(array(
        	'custom' => $where_opt,
        ));
        
        $rows = $db->select('id', self::$table, $where, array('order' => $order, 'limit' => $limit, 'debug' => $debug));
        if(count($rows)) {
            foreach($rows as $row) {
                $res[] = new Item($row['id']);
            }
        }

        return $res;
    }

    /**
     * @brief Restituisce il numero di oggetti Gino.App.BuidApp.Item selezionati 
     * 
     * @param array $options array associativo di opzioni
     * @return numero record
     */
    public static function getCount($options = null) {

        $res = 0;

        $db = \Gino\Db::instance();
        
        $where = self::setConditionWhere(array());

        $rows = $db->select('COUNT(id) AS tot', self::$table, $where);
        if($rows and count($rows)) {
            $res = $rows[0]['tot'];
        }

        return $res;
    }
    
    /**
     * @brief Imposta le condizioni di ricerca dei record
     * 
     * @param array $options array associativo di opzioni
     *   - @b text (string): fa riferimento ai campi label e description
     *   - @b custom (string): condizioni personalizzate
     * @return string
     */
    public static function setConditionWhere($options = null) {

    	$text = \Gino\gOpt('text', $options, null);
        $custom = \Gino\gOpt('custom', $options, null);
    	
    	$controller = new Item();
    	$where = array();
    	
    	if($text) {
    		$where[] = "(label LIKE '%".$text."%' OR description LIKE '%".$text."%')";
    	}
    	
    	if($custom) {
    		$conditions = $custom;
    	}
    	else {
    		$conditions = implode(' AND ', $where);
    	}
    	
    	return $conditions;
    }
}

Item::$columns=Item::columns();
