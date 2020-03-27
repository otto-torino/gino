<?php
/**
 * @file class.Place.php
 * @brief Contiene la definizione ed implementazione della classe Gino.App.Calendar.Category
 * @author marco guidotti <marco.guidotti@otto.to.it>
 * @author abidibo <abidibo@gmail.com>
 * @copyright 2017 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 */

namespace Gino\App\Calendar;

use \Gino\Db;
use \Gino\SlugField;

/**
 * @brief Classe tipo Gino.Model che rappresenta un luogo.
 *
 * @version 1.1.0
 * @copyright 2017 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */
class Place extends \Gino\Model
{
    public static $table = 'calendar_place';
    public static $columns;

    /**
     * @brief Costruttore
     * @param int $id id del luogo
     * @param object $controller
     * @return void
     */
    public function __construct($id, $controller)
    {
        $this->_controller = $controller;
        $this->_tbl_data = self::$table;

        parent::__construct($id);

        $this->_model_label = _('Luogo');
    }

    /**
     * @brief Rappresentazione a stringa dell'oggetto
     * @return string, nome luogo
     */
    function __toString()
    {
        return (string) $this->ml('name');
    }
    
    /**
	 * Struttura dei campi della tabella di un modello
	 *
	 * @return array
	 */
	public static function columns() {
	
		$columns['id'] = new \Gino\IntegerField(array(
			'name'=>'id',
			'primary_key'=>true,
			'auto_increment'=>true,
			'max_lenght'=>11,
		));
		$columns['instance'] = new \Gino\IntegerField(array(
			'name'=>'instance',
			'required'=>true,
			'max_lenght'=>11,
		));
		$columns['name'] = new \Gino\CharField(array(
			'name'=>'name',
			'label'=>_("Nome"),
			'required'=>true,
			'max_lenght'=>200,
		));
		$columns['slug'] = new \Gino\SlugField(array(
			'name' => 'slug',
			'unique_key' => true,
			'label' => array(_("Slug"), _('utilizzato per creare un permalink alla risorsa')),
			'required' => true,
			'max_lenght' => 200,
			'autofill' => array('name'),
		));
		$columns['description'] = new \Gino\TextField(array(
			'name' => 'description',
			'label' => _("Descrizione"),
		));
		
		return $columns;
	}
	
	/**
	 * @brief Restituisce una lista id=>name da utilizzare per un input select
	 *
	 * @param \Gino\App\Calendar\calendar $controller istanza del controller
	 * @return array associativo id=>name
	 */
	public static function getForSelect(\Gino\App\Calendar\calendar $controller) {
	
		$objs = self::objects($controller, array('where' => "instance='".$controller->getInstance()."'", 'order' => 'name ASC'));
		$res = array();
		foreach($objs as $obj) {
			$res[$obj->id] = \Gino\htmlInput($obj->name);
		}
		 
		return $res;
	}
}

Place::$columns=Place::columns();
