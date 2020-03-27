<?php
/**
 * @file class.Item.php
 * @brief Contiene la definizione ed implementazione della classe Gino.App.Calendar.Item
 * @author marco guidotti <marco.guidotti@otto.to.it>
 * @author abidibo <abidibo@gmail.com>
 * @copyright 2017 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 */

namespace Gino\App\Calendar;

use \Gino\Loader;
use \Gino\TagField;
use \Gino\ImageField;
use \Gino\FileField;
use \Gino\BooleanField;
use \Gino\ManyToManyField;
use \Gino\SlugField;
use \Gino\Db;

/**
 * @brief Classe tipo Gino.Model che rappresenta un elemento del calendario.
 *
 * @version 1.0.0
 * @copyright 2017 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @author Marco Guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class Item extends \Gino\Model {
	
    public static $table = 'calendar_item';
    public static $table_ctgs = 'calendar_item_category';
    public static $columns;

    /**
     * @brief Costruttore
     * @param int $id id dell'evento
     * @param object $controller
     * @return void
     */
    public function __construct($id, $controller) {
    	
        $this->_controller = $controller;
        $this->_tbl_data = self::$table;

        parent::__construct($id);

        $this->_model_label = _('Appuntamento');
    }

    /**
     * @brief Rappresentazione a stringa dell'oggetto
     * @return string
     */
	function __toString() {
		
		return (string) $this->ml('name');
	}
	
	/**
	 * @see Gino.Model::properties()
	 */
	protected static function properties($model, $controller) {
		 
		$instance = $controller->getInstance();
	
		$property['place'] = array(
			'foreign_where' => 'instance=\''.$instance.'\'',
			'foreign_controller' => $controller,
		);
		$property['categories'] = array(
			'm2m_where' => 'instance=\''.$instance.'\'',
			'm2m_controller' => $controller,
			'add_related_url' => $controller->linkAdmin(array(), 'block=ctg&insert=1')
		);
		
		return $property;
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
		$columns['date'] = new \Gino\DateField(array(
			'name' => 'date',
			'label' => _("Data"),
			'required' => true,
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
			'label' => array(_('Slug'), _('url parlante, viene calcolato automaticamente inserendo prima la data e poi il nome.')),
			'required' => true,
			'max_lenght' => 200,
			'autofill' => array('date', 'name'),
		));
		$columns['duration'] = new \Gino\IntegerField(array(
			'name'=>'duration',
			'label'=> array(_('Durata'), _("durata in giorni")), 
			'max_lenght' => 4,
			'default' => 1,
		));
		$columns['description'] = new \Gino\TextField(array(
			'name' => 'description',
			'label' => _("Descrizione"),
		));
		$columns['time_start'] = new \Gino\TimeField(array(
			'name' => 'time_start',
			'label' => array(_('Ora di inizio'), _("formato hh:mm")), 
			'required' => true,
		));
		$columns['time_end'] = new \Gino\TimeField(array(
			'name' => 'time_end',
			'label' => array(_('Ora di fine'), _("formato hh:mm")),
			'required' => true,
		));
		$columns['place'] = new \Gino\ForeignKeyField(array(
			'name' => 'place',
			'label' => _("Luogo"),
			'required' => false,
			'foreign' => '\Gino\App\Calendar\Place',
			'foreign_order' => 'name ASC',
			'add_related' => false,
		));
		$columns['author'] = new \Gino\ForeignKeyField(array(
			'name' => 'author',
			'label' => _("Autore"),
			'required' => true,
			'foreign' => '\Gino\App\Auth\User',
			'foreign_order' => 'lastname ASC, firstname ASC',
			'add_related' => false,
		));
		$columns['insertion_date'] = new \Gino\DatetimeField(array(
			'name' => 'insertion_date',
			'label' => _('Data inserimento'),
			'required' => true,
			'auto_now' => FALSE,
			'auto_now_add' => TRUE
		));
		$columns['last_edit_date'] = new \Gino\DatetimeField(array(
			'name' => 'last_edit_date',
			'label' => _('Data ultima modifica'),
			'required'=>true,
			'auto_now' => TRUE,
			'auto_now_add' => TRUE
		));
		$columns['categories'] = new \Gino\ManyToManyField(array(
			'name' => 'categories',
			'label' => _("Categorie"),
			'm2m' => '\Gino\App\Calendar\Category',
			'm2m_where' => null,
			'm2m_controller' => null,
			'join_table' => self::$table_ctgs,
			'add_related' => TRUE,
			'add_related_url' => null
		));
	
		return $columns;
	}
	
	/**
	 * @brief Restituisce il numero di record che soddisfano le condizioni date
	 *
	 * @param \Gino\App\Calendar\calendar $controller istanza del controller Gino.App.Calendar.calendar
	 * @param array $options opzioni per la definizione delle condizioni della query (@see setConditionWhere())
	 * @return integer
	 */
	public static function getCount($controller, $options = null) {
	
		$db = Db::instance();
	
		$where = self::setConditionWhere($controller, $options);
		
		return $db->getNumRecords(self::$table, $where);
	}
	
	/**
	 * @brief Imposta le condizioni di ricerca dei record
	 * @param object $controller istanza del controller Gino.App.Calendar.calendar
	 * @param array $options array associativo di opzioni
	 *   - @b private (boolean)
	 *   - @b published (boolean)
	 *   - @b ctg (integer)
	 *   - @b tag (string)
	 *   - @b text (string): fa riferimento ai campi name e description
	 *   - @b date_form (string)
	 *   - @b date_to (string)
	 *   - @b month (array): eventi del mese, nel formato array((int) month, (int) year)
	 * @return string
	 */
	public static function setConditionWhere($controller, $options = null) {
		
		$ctg = \Gino\gOpt('ctg', $options, null);
		$text = \Gino\gOpt('text', $options, null);
		$date_from = \Gino\gOpt('date_from', $options, null);
		$date_to = \Gino\gOpt('date_to', $options, null);
		$month_year = \Gino\gOpt('month', $options, null);
		
		$where = array("instance='".$controller->getInstance()."'");
		
		if($ctg) {
			$where[] = "id IN (SELECT item_id FROM ".self::$table_ctgs." WHERE category_id='".$ctg."')";
		}
		if($text) {
			$where[] = "(name LIKE '%".$text."%' OR description LIKE '%".$text."%')";
		}
		if($date_from) {
			$where[] = "date >= '".\Gino\dateToDbDate($date_from)."'";
		}
		if($date_to) {
			$where[] = "date <= '".\Gino\dateToDbDate($date_to)."'";
		}
		if(is_array($month_year) && count($month_year) == 2) {
			$month = $month_year[0];
			$year = $month_year[1];
			
			$month = $month < 10 ? '0'.$month : $month;
			$month_days = date('t', mktime(0, 0, 0, $month, 1, $year));
			
			$where[] = "date <= '".$year."-".$month."-".$month_days."' AND DATE_ADD(date, INTERVAL duration DAY) >= '".$year."-".$month."-1'";
		}
		
		return implode(' AND ', $where);
	}
	
	/**
	 * @brief Valore del campo @a place
	 * @return \Gino\App\Calendar\Place
	 */
	public function getPlaceValue() {
		
		return new Place($this->place, $this->_controller);
	}

    /**
     * @brief Url dettaglio scheda
     * @return string
     */
    public function getUrl() {
    	
    	return $this->_registry->router->link($this->_controller->getInstanceName(), 'detail', array('id' => $this->slug));
    }

    /**
     * @brief Collegamento alla modale
     * @return string, tag a
     */
    public function getModalUrl() {
    	
    	return "<a href=\"".$this->getAjaxUrl()."\" class=\"modal-overlay\" data-type=\"ajax\" data-esc-close=\"true\" data-overlay=\"false\" data-title=\""._("Appuntamento")."\">";
    }
    
    /**
     * @brief Ritorna le proprietà del tag A relative al collegamento a una modale
     * @description Da passare al javascript per costruire il tag
     * @return array
     */
    public function modalUrlProperties() {
    	
    	return array(
    		'href' => $this->getAjaxUrl(),
    		'prop_class' => 'modal-overlay',
    		'prop_type' => 'ajax',
    		'prop_esc_close' => 'true',
    		'prop_overlay' => 'false',
    		'prop_title' => _("Appuntamento")
    	);
    }
    
    /**
     * @brief Url dettaglio evento per chiamate ajax
     * @return string
     */
    public function getAjaxUrl() {
    	
    	return $this->_registry->router->link($this->_controller->getInstanceName(), 'detail', array('id' => $this->slug), "ajax=1");
	}

    /**
     * @brief Data nel formato 'domenica 5 febbraio 2014'
     * @param \Datetime $date oggetto Datetime
     * @return string
     */
    private function letterDate($date) {
        $days = array(_('domenica'), _('lunedì'), _('martedì'), _('mercoledì'), _('giovedì'), _('venerdì'), _('sabato'));
        $months = array(_('gennaio'), _('febbraio'), _('marzo'), _('aprile'), _('maggio'), _('giugno'), _('luglio'), _('agosto'), _('settembre'), _('ottobre'), _('novembre'), _('dicembre'));
        return sprintf('%s <span>%d %s</span> %d', $days[$date->format('w')], $date->format('j'), $months[$date->format('n') - 1], $date->format('Y'));
    }

    /**
     * @brief Data inizio evento in formato letterale
     * @see self::letterDate
     * @return string
     */
    public function beginLetterDate() {
        return $this->letterDate(new \Datetime($this->date));
    }

    /**
     * @brief Data fine evento in formato letterale
     * @see self::letterDate
     * @return string
     */
    public function endLetterDate() {
        $end_date = new \Datetime($this->date);
        $end_date->modify('+'.($this->duration - 1).'days');
        return $this->letterDate($end_date);
    }

    /**
     * @brief Data inizio in formato iso 8601
     *
     * @return string
     */
    public function startDateIso() {
        $datetime = new \Datetime($this->date);
        return $datetime->format('c');
    }

    /**
     * @brief Data fine in formato iso 8601
     *
     * @return string
     */
    public function endDateIso()
    {
        $end_date = new \Datetime($this->date);
        $end_date->modify('+'.($this->duration - 1).'days');
        return $end_date->format('c');
    }
    
    /**
     * @see Gino.Model::save()
     * @description Sovrascrive il metodo di Gino.Model per salvare l'autore dell'inserimento/modifica
     */
	public function save($options=array()) {
		
		$session = \Gino\session::instance();
		$this->author = $session->user_id;
		return parent::save($options);
	}
}

Item::$columns=Item::columns();
