<?php
/**
 * @file class_calendar.php
 * @brief Contiene la definizione della classe Gino.App.Calendar.calendar
 */

/**
 * @namespace Gino.App.Calendar
 * @description Namespace dell'applicazione Calendario
 */
namespace Gino\App\Calendar;

use \Gino\Error;
use \Gino\Loader;
use \Gino\View;
use \Gino\GTag;
use \Gino\Session;
use \Gino\Javascript;
use \Gino\Registry;
use \Gino\App\Module\ModuleInstance;

require_once('class.Item.php');
require_once('class.Category.php');
require_once('class.Place.php');

/**
 * @brief Classe di tipo Gino.Controller del modulo Calendario
 *
 * @version 1.0.0
 * 
 * ##CONFIGURATION
 * In INSTALLED_APPS aggiungere:
 * '{instance_name}' => 'calendar',
 * 
 * ##OUTPUTS
 * - @a archive: archivio
 * - @a detail: dettaglio
 * - @a calendar: calendario (widget)
 * - @a view: calendario (pagina)
 * - @a showlist: elenco degli appuntamenti da chiamata ajax
 * 
 * ##PERMESSI
 * L'applicazione prevede tre livelli di permesso: \n
 * - can_admin: amministrazione completa di categorie, appuntamenti, opzioni e frontend
 */
class calendar extends \Gino\Controller {

    protected $_monday_first_week_day, $_day_chars, $_open_modal;
	
	/**
	 * @brief numero di record per pagina
	 * @var integer
	 */
    protected $_items_for_page;

    /**
     * @brief Costruttore
     * @param int $mdlId id dell'istanza di tipo calendar
     * @return void
     */
    public function __construct($mdlId) {
    	
        parent::__construct($mdlId);
    }

    /**
     * @brief Restituisce alcune proprietà della classe utili per la generazione di nuove istanze
     * @return array, lista delle proprietà utilizzate per la creazione di istanze di tipo calendar (tabelle, css, viste, folders)
     */
    public static function getClassElements() 
    {
        return array(
            "tables"=>array(
                'calendar_item',
                'calendar_item_category',
                'calendar_category',
                'calendar_opt',
            ),
            "css"=>array(
                'calendar.css',
            ),
            "views" => array(
                'archive.php' => _('Archivio'),
                'detail.php' => _('Dettaglio'),
                'calendar.php' => _('Calendario (vista)'),
            	'view.php' => _("Visualizzazione del calendario (pagina)"),
            	'showlist.php' => _("Elenco degli appuntamenti da chiamata ajax"),
            ),
            "folderStructure" => array()
        );
    }

    /**
     * @brief Metodo invocato quando viene eliminata un'istanza di tipo eventi
     * Si esegue la cancellazione dei dati da db e l'eliminazione di file e directory 
     * @return TRUE
     */
    public function deleteInstance() 
    {
        $this->requirePerm('can_admin');

        /* eliminazione eventi */
        Item::deleteInstance($this);
        /* eliminazione categorie */
        Category::deleteInstance($this);

        /*
         * delete record and translation from table calendar_opt
         */
        $opt_id = $this->_db->getFieldFromId($this->_tbl_opt, "id", "instance", $this->_instance);
        \Gino\Translation::deleteTranslations($this->_tbl_opt, $opt_id);
        $result = $this->_db->delete($this->_tbl_opt, "instance=".$this->_instance);

        /*
         * delete css files
         */
        $classElements = $this->getClassElements();
        foreach($classElements['css'] as $css) {
            unlink(APP_DIR.OS.$this->_class_name.OS.\Gino\baseFileName($css)."_".$this->_instance_name.".css");
        }

        /* eliminazione views */
        foreach($classElements['views'] as $k => $v) {
            unlink($this->_view_dir.OS.\Gino\baseFileName($k)."_".$this->_instance_name.".php");
        }

        return TRUE;
    }

    /**
     * @brief Definizione dei metodi pubblici che forniscono un output per il front-end
     * 
     * Questo metodo viene letto dal motore di generazione dei layout (metodi non presenti nel file ini) e dal motore di generazione
     * di voci di menu (metodi presenti nel file ini) per presentare una lista di output associati all'istanza di classe.
     * 
     * @return array, lista metodi NOME_METODO => array('label' => LABEL, 'permissions' = PERMISSIONS)
     */
    public static function outputFunctions() {
    	
        $list = array(
            "archive" => array("label"=>_("Archivio eventi"), "permissions"=>array()),
        	"view" => array("label"=>_("Visualizzazione del calendario (pagina)"), "permissions"=>array()),
            "calendar" => array("label"=>_("Calendario eventi"), "permissions"=>array()),
        );

        return $list;
    }

    /**
     * @brief Calendario
     * 
     * @return string
     */
    public function calendar() {
		
        Loader::import('class', array(
            '\Gino\Calendar',
        ));
        
        $cal = new \Gino\Calendar();
        $cal->importFiles();
        
        $value = 0;
        $disabled = false;
        
        $select = \Gino\Input::select('category', $value, Category::getForSelect($this), array(
            'noFirst' => true,
            'firstVoice' => _('tutte le categorie'),
            'firstValue' => 0,
        	'maxChars' => 25,
        	'cutWords' => true,
            'js' => "onchange=\"calendar.setRequestData('ctg=' + $(this).value); calendar.requestMonthData()\"",
            'id' => 'category_filter', 
        	'disabled' => $disabled
        ));
        
        $events = $this->getEvents(['calendar' => $cal]);
        $date_click = "function(info) {
            alert('Clicked on: ' + info.dateStr);
        }";
        
        $view = new View($this->_view_dir, 'calendar_'.$this->_instance_name);
        $dict = array(
            'json_url' => $this->link($this->_instance_name, 'getMonthEventsJSON'),
            'feed_url' => null,
            'instance_name' => $this->_instance_name,
            'router' => \Gino\Router::instance(),
            'select' => $select,
            
            'script' => $cal->script([
                'events' => $events,
                'date_click' => $date_click,
                //'insert_modal' => true
            ]),
        );

        return $view->render($dict);
    }
    
    //\Gino\Http\Request $request
    public function getEvents($options=[]) {
        
        $calendar = \Gino\gOpt('calendar', $options, null);
        /*
        // Call from calendar
        $ctg_id = \Gino\cleanVar($request->POST, 'ctg', 'int');
        $month = \Gino\cleanVar($request->POST, 'month', 'int');
        $year = \Gino\cleanVar($request->POST, 'year', 'int');
        
        $conditions = array(
            'month' => array($month, $year),
            'ctg' => $ctg_id
        );
        */
        
        $objs = Item::objects($this, array(
            'where' => Item::setConditionWhere($this, []),
            'order' => 'date ASC'
        ));
        
        $items = array();
        foreach($objs as $event) {
            
            $ctgs = array();
            foreach($event->categories as $ctg_id) {
                $ctg = new Category($ctg_id, $this);
                $ctgs[] = \Gino\jsHtml($ctg->ml('name'));
            }
            
            $description = '';
            if(count($ctgs)) {
                $description .= implode(',', $ctgs)." - ";
            }
            $description .= $event->getPlaceValue(). " ";
            $description .= "(".\Gino\dbTimeToTime($event->time_start)." - ".\Gino\dbTimeToTime($event->time_end).")";
            
            $description = \Gino\jsVar($description);
            
            $item = [
                'title' => \Gino\jsVar($event->name),
                'description' => $description,
                'start' => $event->date,
                'url' => ''
            ];
            
            if($event->duration > 1) {
                if(!is_a($calendar)) {
                    $calendar = new \Gino\Calendar();
                }
                $item['end'] = $calendar->setEndDate($event->date, $event->duration);
            }
            
            $items[] = $item;
        }
        return $items;
    }

    /**
     * @brief Json eventi mese, anno e categoria dati
     * @param \Gino\Http\Redirect $redirect istanza di Gino.Http.Redirect
     * @return Gino.Http.ResponseJson
     */
    public function getMonthEventsJSON(\Gino\Http\Request $request) {
    	
    	// Call from calendar
        $ctg_id = \Gino\cleanVar($request->POST, 'ctg', 'int');
        $month = \Gino\cleanVar($request->POST, 'month', 'int');
        $year = \Gino\cleanVar($request->POST, 'year', 'int');

        $conditions = array(
        	'month' => array($month, $year),
        	'ctg' => $ctg_id
        );

        $cals = Item::objects($this, array(
            'where' => Item::setConditionWhere($this, $conditions),
            'order' => 'date ASC'
        ));
        
        $items = array();
        foreach($cals as $event) {
        	
        	$url = $event->getUrl();
        	$onclick = $this->_open_modal ? $event->modalUrlProperties() : $event->getAjaxUrl();
        	
            $ctgs = array();
            foreach($event->categories as $ctg_id) {
                $ctg = new Category($ctg_id, $this);
                $ctgs[] = \Gino\jsHtml($ctg->ml('name'));
            }
            
            $description = '';
            if(count($ctgs)) {
            	$description .= implode(',', $ctgs)." - ";
            }
            $description .= $event->getPlaceValue(). " ";
            $description .= "(".\Gino\dbTimeToTime($event->time_start)." - ".\Gino\dbTimeToTime($event->time_end).")";
            
            $description = \Gino\jsVar($description);
            
            $items[] = array(
                'name' => \Gino\jsHtml($event->ml('name')),
                'description' => $description,
                'date' => $event->date,
                'day' => substr($event->date, 8, 2),
                'month' => substr($event->date, 5, 2),
                'year' => substr($event->date, 0, 4),
                'url' => $url,
            	'modal' => $this->_open_modal,
            	'onclick' => $onclick,
            );

            if($event->duration > 1) {
                $date = new \Datetime($event->date);
                for($i = 1; $i < $event->duration; $i++) {
                    $date->modify('+1 days');
                    $items[] = array(
                        'name' => \Gino\jsHtml($event->ml('name')),
                        'description' => $description,
                        'date' => $date->format('Y-m-d'),
                        'day' => $date->format('d'),
                        'month' => $date->format('m'),
                        'year' => $date->format('Y'),
                        'url' => $url,
                    	'modal' => $this->_open_modal,
            			'onclick' => $onclick,
                    );
                }
            }
        }

        \Gino\Loader::import('class/http', '\Gino\Http\ResponseJson');
        return new \Gino\Http\ResponseJson($items);
    }

    /**
     * @brief Vista dettaglio appuntamento
     * @description Il metodo puo' essere chiamato con parametro GET ajax=1 per non stampare tutto il documento
     * 
     * @param \Gino\Http\Request $request istanza di Gino.Http.Request
     * @throws Gino.Exception.Exception404 se l'evento non viene trovato
     * @throws Gino.Exception.Exception403 se non si hanno i permessi per visualizzare l'evento
     * @return Gino.Http.Response
     */
    public function detail(\Gino\Http\Request $request) {

        $slug = \Gino\cleanVar($request->GET, 'id', 'string');
        $event = Item::getFromSlug($slug, $this);

        $ajax = \Gino\cleanVar($request->GET, 'ajax', 'int');

        if(!$event->id) {
            throw new \Gino\Exception\Exception404();
        }

		// Set Registry
		$this->_registry->addCss($this->_class_www.'/calendar_'.$this->_instance_name.'.css');
        
		$this->setSEOSettings([
		    'title' => $event->ml('name'),
		    'description' => $event->ml('description'),
		    'keywords' => $event->tags,
		    'url' => $this->link($this->_instance_name, 'detail', array('id' => $event->slug), '', array('abs'=>true)),
		    'type' => 'article',
		    'image' => null
		]);
		// /Registry

        $view = new View($this->_view_dir, 'detail_'.$this->_instance_name);

        // Breadcrumbs
        if(class_exists('\Gino\BreadCrumbs', false) && !$ajax) {
        	$breadcrumbs = new \Gino\BreadCrumbs($this->_class_name);
        	$breadcrumbs->setItems(array(
        		array('label' => _("Agenda"), 'link' => $this->link($this->_instance_name, 'archive')),
        		array('label' => $event->ml('name'), 'current' => true)
        	));
        	$bc = $breadcrumbs->render();
        } else {
        	$bc = null;
        }
        // /Breadcrumbs

        $dict = array(
            'instance_name' => $this->_instance_name,
            'item' => $event,
        	'breadcrumbs' => $bc
        );

        if($ajax) {
            return new \Gino\Http\Response($view->render($dict));
        }

        $document = new \Gino\Document($view->render($dict));
        return $document();
    }
    
    /**
     * @brief Visualizzazione del calendario
     * 
     * @return Gino.Http.Response
     */
    public function view() {
    	
    	$this->_registry->addJs($this->_class_www.'/calendar.js');
    	$this->_registry->addJs($this->_class_www.'/calendar_locale.js');
    	$this->_registry->addCss($this->_class_www.'/calendar_'.$this->_instance_name.'.css');
    	$this->_registry->addCss(CSS_WWW."/responsive-table.css");
    	
    	$view = new View($this->_view_dir, 'view_'.$this->_instance_name);
    	$dict = array(
    		'json_url' => $this->link($this->_instance_name, 'getMonthEventsJSON'),
    		'instance_name' => $this->_instance_name,
    		'url' => $this->link($this->_instance_name, 'ajaxEventsList'),
    	);
    	
    	$document = new \Gino\Document($view->render($dict));
    	return $document();
    }
    
    /**
     * @brief Elenco degli appuntamenti di un mese
     * 
     * @param \Gino\Http\Request $request
     * @return \Gino\Http\Response
     */
    public function ajaxEventsList(\Gino\Http\Request $request) {
    	
    	Loader::import('class/http', '\Gino\Http\Response');
    	
    	$month = \Gino\cleanVar($request->POST, 'month', 'int', '');
    	$year = \Gino\cleanVar($request->POST, 'year', 'int', '');
    	
    	if($month and $year) {
    		$month_days = date('t', mktime(0, 0, 0, $month, 1, $year));
    		$date_from = "$year-".($month < 10 ? '0'.$month : $month).'-01';
    		$date_to = "$year-".($month < 10 ? '0'.$month : $month).'-'.$month_days;
    	}
    	else {
    		$date_from = null;
    		$date_to = null;
    	}
    	
    	$conditions = array(
    		'date_from' => $date_from,
    		'date_to' => $date_to,
    	);
    	
    	$where = Item::setConditionWhere($this, $conditions);
    	$items = Item::objects($this, array('where' => $where, 'limit' => null, 'order' => 'date DESC'));
    	
    	$months = array(
    		1 => _('gennaio'), 
    		2 => _('febbraio'), 
    		3 => _('marzo'), 
    		4 => _('aprile'), 
    		5 => _('maggio'), 
    		6 => _('giugno'), 
    		7 => _('luglio'), 
    		8 => _('agosto'), 
    		9 => _('settembre'), 
    		10 => _('ottobre'), 
    		11 => _('novembre'), 
    		12 => _('dicembre')
    	);
    	
    	$view = new View($this->_view_dir, 'showlist_'.$this->_instance_name);
    	$dict = array(
    		'items' => $items,
    		'modal' => true,
    		'month' => $months[$month]." ".$year
    	);
    	
    	return new \Gino\Http\Response($view->render($dict));
    }

    /**
     * @brief Vista archivio appuntamenti
     * @param \Gino\Http\Request $request istanza di Gino.Http.Request
     * @return Gino.Http.Response
     */
    public function archive(\Gino\Http\Request $request) {

    	$this->_registry->addCss($this->_class_www."/calendar_".$this->_instance_name.".css");
    	
    	// Direct link values
    	$ctgslug = \Gino\cleanVar($request->GET, 'ctg', 'string');
        $month = \Gino\cleanVar($request->GET, 'month', 'int');
        $year = \Gino\cleanVar($request->GET, 'year', 'int');
        
        if($ctgslug) {
        	$ctg = Category::getFromSlug($ctgslug, $this);
        	$ctg_id = $ctg ? $ctg->id : 0;
        }
        else {
        	$ctg = null;
        	$ctg_id = 0;
        }

        if($month and $year) {
            $month_days = date('t', mktime(0, 0, 0, $month, 1, $year));
            $date_from = "$year-".($month < 10 ? '0'.$month : $month).'-01';
            $date_to = "$year-".($month < 10 ? '0'.$month : $month).'-'.$month_days;
        }
        else {
            $date_from = null;
            $date_to = null;
        }
        // /Direct
        
        $query_params = array(
            'category' => $ctg_id,
            'date_from' => $date_from ? \Gino\dbDateToDate($date_from) : null,
            'date_to' => $date_to ? \Gino\dbDateToDate($date_to) : null,
        );
        
        $obj_search = $this->setSearchParams($query_params);
        $search_values = $obj_search->getValues();
        
        $conditions = array(
        	'ctg' => array_key_exists('category', $search_values) ? $search_values['category'] : null,
        	'text' => $search_values['text'],
        	'date_from' => $search_values['date_from'],
        	'date_to' => $search_values['date_to'],
        );
        
        $items_number = Item::getCount($this, $conditions);

        $paginator = Loader::load('Paginator', array($items_number, $this->_items_for_page));
        $limit = $paginator->limitQuery();
        $where = Item::setConditionWhere($this, $conditions);
        
        $items = Item::objects($this, array('where' => $where, 'limit' => $limit, 'order' => 'date DESC'));

        $view = new View($this->_view_dir, 'archive_'.$this->_instance_name);
        $dict = array(
            'instance_name' => $this->_instance_name,
        	'controller' => $this,
            'items' => $items,
            'ctg' => $ctg,
            'pagination' => $paginator->pagination(),
            'search_form' => $obj_search->form($this->link($this->_instance_name, 'archive'), 'form_search_calendar'),
            'link_form' => $obj_search->linkSearchForm()
        );

        $document = new \Gino\Document($view->render($dict));
        return $document();
    }

    /**
     * @brief Interfaccia amministrazione modulo
     * @param \Gino\Http\Request $request istanza di Gino.Http.Request
     * @return Gino.Http.Response
     */
    public function manageDoc(\Gino\Http\Request $request) {
    	
        $this->requirePerm(array('can_admin'));

        $block = \Gino\cleanVar($request->GET, 'block', 'string');

        $link_frontend = ['link' => $this->linkAdmin(array(), 'block=frontend'), 'label' => _('Frontend')];
        $link_options = ['link' => $this->linkAdmin(array(), 'block=options'), 'label' => _('Opzioni')];
        $link_ctg = ['link' => $this->linkAdmin(array(), 'block=ctg'), 'label' => _('Categorie')];
        $link_place = ['link' => $this->linkAdmin(array(), 'block=place'), 'label' => _('Sale')];
        $link_dft = ['link' => $this->linkAdmin(), 'label' => _('Prenotazioni')];
        $sel_link = $link_dft;

        if($block == 'frontend' && $this->userHasPerm('can_admin')) {
            $backend = $this->manageFrontend();
            $sel_link = $link_frontend;
        }
        elseif($block=='options' && $this->userHasPerm('can_admin')) {
            $backend = $this->manageOptions();
            $sel_link = $link_options;
        }
        elseif($block=='ctg') {
            $backend = $this->manageCategory($request);
            $sel_link = $link_ctg;
        }
        elseif($block=='place') {
        	$backend = $this->managePlace($request);
        	$sel_link = $link_place;
        }
        else {
            $backend = $this->manageItem($request);
        }

        if(is_a($backend, '\Gino\Http\Response')) {
            return $backend;
        }

        $links_array = array($link_frontend, $link_options, $link_ctg, $link_place, $link_dft);

        $view = new View(null, 'tabs');
        $dict = array(
			'title' => _('Gestione calendario'),
        	'links' => $links_array,
        	'selected_link' => $sel_link,
        	'content' => $backend
        );

        $document = new \Gino\Document($view->render($dict));
        return $document();
    }

    /**
     * @brief Interfaccia di amministrazione eventi
     * @param \Gino\Http\Request $request istanza di Gino.Http.Request
     * @return Gino.Http.Redirect oppure html, interfaccia di back office
     */
	public function manageItem(\Gino\Http\Request $request) {
		
		$edit = \Gino\cleanVar($request->GET, 'edit', 'int');
		$insert = \Gino\cleanVar($request->GET, 'insert', 'int');

		$remove_fields = array('author');
        $delete_deny = array();
        
        // dft duration
        if(isset($request->POST['duration']) and !$request->POST['duration']) {
            $request->POST['duration'] = 1;
        }
        
        $admin_table = Loader::load('AdminTable', array($this, array('delete_deny' => $delete_deny)));

        $backend = $admin_table->backOffice(
            'Item',
            array(
                'list_display' => array('id', 'name', 'date', 'place', 'categories', 'author'),
                'list_title'=>_("Elenco prenotazioni"),
            	'filter_fields'=>array('date', 'name', 'categories')
            ),
            array(
                'removeFields' => $remove_fields
            ),
            array(
                'name' => array('size' => 40),
            	'slug' => array('size' => 40),
            	'date' => array(
                    'id' => 'date'
                ),
                'description' => array(
                    'widget' => 'editor',
                    'notes' => true,
                    'img_preview' => FALSE,
                ),
            	'duration' => array(
            		'size' => 5
            	)
            )
        );
        
        return $backend;
    }

    /**
     * @brief Interfaccia di amministrazione Category
     * @param \Gino\Http\Request $request istanza di Gino.Http.Request
     * @return Gino.Http.Response
     */
    public function manageCategory(\Gino\Http\Request $request)
    {
        $admin_table = Loader::load('AdminTable', array($this, array()));

        $buffer = $admin_table->backOffice(
            'Category',
            array(
                'list_display' => array('id', 'name', 'slug'),
                'list_title'=>_("Elenco categorie"),
                'list_description'=>"<p>"._('Ciascun appuntamento inserito potrà essere associato ad una o più categorie qui definite.')."</p>",
            ),
            array(),
            array(
                'description' => array(
                    'widget'=>'editor',
                    'notes'=>FALSE,
                    'img_preview'=>FALSE,
                )
            )
        );

        return $buffer;
    }
    
    /**
     * @brief Interfaccia di amministrazione Place
     * @param \Gino\Http\Request $request istanza di Gino.Http.Request
     * @return Gino.Http.Response
     */
    public function managePlace(\Gino\Http\Request $request) {
    	
    	$admin_table = Loader::load('AdminTable', array($this, array()));
    
    	$buffer = $admin_table->backOffice(
    		'Place',
    		array(
    			'list_display' => array('id', 'name', 'slug'),
    			'list_title'=>_("Elenco luoghi"),
    			'list_description'=>"<p>"._('Luoghi degli appuntamenti.')."</p>",
    		),
    		array(),
    		array(
    			'description' => array(
    				'widget'=>'editor',
    				'notes'=>FALSE,
    				'img_preview'=>FALSE,
    			)
    		)
    	);
    
    	return $buffer;
    }
}
