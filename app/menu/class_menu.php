<?php
/**
 * @file class_menu.php
 * @brief Contiene la definizione ed implementazione della classe Gino.App.Menu.menu
 * 
 * @copyright 2005-2017 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

/**
 * @namespace Gino.App.Menu
 * @description Namespace dell'applicazione Menu, che gestisce istanze di menu
 */
namespace Gino\App\Menu;

use \Gino\View;
use \Gino\Document;
use \Gino\Error;
use \Gino\Http\Response;
use \Gino\Http\Redirect;
use \Gino\App\SysClass\ModuleApp;
use \Gino\App\Module\ModuleInstance;

require_once('class.MenuVoice.php');

/**
 * @brief Classe di tipo Gino.Controller per la gestione dei menu
 * 
 * @version 1.0.0
 * @copyright 2005-2017 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 * 
 * ##DESCRIZIONE
 * Il menu utilizza il plugin jQuery SmartMenus (@see https://www.smartmenus.org/).
 */
class menu extends \Gino\Controller {

    private static $_menu_functions_list = 'menuFunctionsList';

    private $_tbl_opt;

    private $_options;
    public $_optionsLabels;
    private $_title;
    private $_cache;
    
    /**
     * Visualizzazione della voce di menu che rimanda all'area amministrativa
     * @var bool
     */
    private $_view_admin_voice;
    
    /**
     * Visualizzazione del logout
     * @var bool
     */
    private $_view_logout_voice;
    
    private $_ico_more;

    /**
     * @brief Costruttore
     * @param int $instance id istanza
     * @return istanza di Gino.App.Menu.menu
     */
    function __construct($instance) {

        parent::__construct($instance);

        $this->_tbl_opt = "sys_menu_opt";

        $this->_ico_more = " / ";
        $this->_view_dir = dirname(__FILE__).OS.'views';
        
        $this->setAppOptions();
	}
    
	private function setAppOptions() {
		
		$this->_optionsValue = array(
			'title' => _("Menu"),
        	'cache' => 0,
        	'view_admin_voice' => 0,
        	'view_logout_voice' => 0
		);
		
		if(!$this->_registry->apps->instanceExists($this->_instance_name)) {
		
			$this->_title = \Gino\htmlChars($this->setOption('title', array('value'=>$this->_optionsValue['title'], 'translation'=>true)));
			$this->_cache = $this->setOption('cache', array('value'=>$this->_optionsValue['cache']));
			$this->_view_admin_voice = (bool) $this->setOption('view_admin_voice', array('value'=>$this->_optionsValue['view_admin_voice']));
			$this->_view_logout_voice = (bool) $this->setOption('view_logout_voice', array('value'=>$this->_optionsValue['view_logout_voice']));
			
			$this->_registry->apps->{$this->_instance_name} = array(
				'title' => $this->_title,
				'cache' => $this->_cache,
				'view_admin_voice' => $this->_view_admin_voice,
				'view_logout_voice' => $this->_view_logout_voice,
			);
		}
		else {
			$this->_title = $this->_registry->apps->{$this->_instance_name}['title'];
			$this->_cache = $this->_registry->apps->{$this->_instance_name}['cache'];
			$this->_view_admin_voice = $this->_registry->apps->{$this->_instance_name}['view_admin_voice'];
			$this->_view_logout_voice = $this->_registry->apps->{$this->_instance_name}['view_logout_voice'];
		}
		
		$this->_options = \Gino\Loader::load('Options', array($this));
		$this->_optionsLabels = array(
			"title" => array(
				'label' => _("Titolo"),
				'value' => $this->_optionsValue['title'],
			),
			"cache" => array(
				'label' => array(_("Tempo di caching dei contenuti (s)"), _("Se non si vogliono tenere in cache o non si è sicuri del significato lasciare vuoto o settare a 0")),
				'value' => $this->_optionsValue['cache'],
				'required' => false
			),
			"view_admin_voice" => array(
				'label' => _("Visualizzazione della voce di menu che rimanda all'area amministrativa"),
				'value' => $this->_optionsValue['view_admin_voice']
			),
			"view_logout_voice" => array(
				'label' => _("Visualizzazione del logout"),
				'value'=>$this->_optionsValue['view_logout_voice'],
			),
		);
	}

    /**
     * @brief Restituisce alcune proprietà della classe
     * @return array associativo contenente le tabelle, viste e struttura directory contenuti
     */
    public static function getClassElements() {

        return array(
        	"tables" => array('sys_menu_voices', 'sys_menu_opt'),
            "css" => array('menu.css'),
            'views' => array(
                'render.php' => _('Stampa il menu')
            )
        );
    }

    /**
     * @brief Eliminazione di una istanza
     * @return bool, risultato operazione
     */
    public function deleteInstance() {

        $this->requirePerm('can_admin');

        /*
         * delete menu voices and translations
         */
        MenuVoice::deleteInstanceVoices($this->_instance);

        /*
         * delete record and translation from table menu_opt
         */
        $opt_id = $this->_db->getFieldFromId($this->_tbl_opt, "id", "instance", $this->_instance);
        \Gino\App\Language\Language::deleteTranslations($this->_tbl_opt, $opt_id);

        $res = $this->_db->delete($this->_tbl_opt, "instance='$this->_instance'");

        /* eliminazione file css */
        $classElements = $this->getClassElements();
        foreach($classElements['css'] as $css) {
            unlink(APP_DIR.OS.$this->_class_name.OS.\Gino\baseFileName($css)."_".$this->_instance_name.".css");
        }

        /* eliminazione views */
        foreach($classElements['views'] as $k => $v) {
            unlink($this->_view_dir.OS.\Gino\baseFileName($k)."_".$this->_instance_name.".php");
        }

        return $res;
    }

    /**
     * @brief Definizione dei metodi pubblici che forniscono un output per il front-end
     *
     * Questo metodo viene letto dal motore di generazione dei layout (prende i metodi non presenti nel file ini) e dal motore di generazione di 
     * voci di menu (presenti nel file ini) per presentare una lista di output associati all'istanza di classe.
     *
     * @return array associativo metodi pubblici metodo => array('label' => label, 'permissions' => permissions)
     */
    public static function outputFunctions() {

        $list = array(
            "render" => array("label"=>_("visualizzazione menu"), "permissions"=>array()),
            "breadCrumbs" => array("label"=>_("Briciole di pane"), "permissions"=>array())
        );

        return $list;
    }

    /**
     * @brief Visualizzazione menu
     * 
     * @see Gino.App.Menu.MenuVoice::getSelectedVoice()
     * @return string, menu
     */
    public function render() {

        $session = \Gino\Session::instance();
        $sel_voice = MenuVoice::getSelectedVoice($this->_instance);
        
        $this->_registry->addCustomJs($this->_class_www.'/smartmenus/jquery.smartmenus.min.js', array('compress'=>false, 'minify'=>false));
        $this->_registry->addCustomJs($this->_class_www."/smartmenus/addons/bootstrap/jquery.smartmenus.bootstrap.min.js", array('compress'=>false, 'minify'=>false));
        $this->_registry->addCss($this->_class_www."/smartmenus/addons/bootstrap/jquery.smartmenus.bootstrap.css");
        
        $this->_registry->addCss($this->_class_www."/menu_".$this->_instance_name.".css");
        
        $cache = new \Gino\OutputCache($buffer, $this->_cache ? true : false);
        if($cache->start($this->_instance_name, "view".$sel_voice.$session->lng, $this->_cache)) {

        	$request = \Gino\Http\Request::instance();
            $tree = $this->getTree();
            
            if($this->_view_admin_voice && $request->user->hasPerm('core', 'is_staff')) {
            	$admin_voice = "index/admin_page";
            }
            else {
            	$admin_voice = null;
            }
            if($this->_view_logout_voice && $session->user_id) {
            	$logout_voice = "index.php?action=logout";
            }
            else {
            	$logout_voice = null;
            }
            
            $view = new View($this->_view_dir);
            $view->setViewTpl('render_'.$this->_instance_name);
            $dict = array(
                'selected' => $sel_voice,
                'tree' => $tree,
            	'admin_voice' => $admin_voice,
            	'logout_voice' => $logout_voice
            );

            $GINO = $view->render($dict);

            $cache->stop($GINO);
        }

        return $buffer;
    }

    /**
     * @brief Costruisce il tree delle voci di menu
     * @param int $parent id della voce parent
     * @return array con il tree, chiavi: id, type, url, label, sub (altro tree, ricorsivo)
     */
    private function getTree($parent = 0) {

        $tree = array();

        $voices = MenuVoice::get(array("where"=>"instance='$this->_instance' AND parent='$parent'", "order"=>"order_list"));

        foreach($voices as $v) {
            if($v->userCanSee()) {
                $tree[] = array(
                    "id"=>$v->id, 
                    "type"=>$v->type, 
                    "url"=>$v->url,
                    "label"=>\Gino\htmlChars($v->ml('label')),
                    "sub"=>$this->getTree($v->id)    
                );
            }
        }
        
        return $tree;
    }

    /**
     * @brief Briciole di pane
     * @return string, briciole di pane
     */
    public function breadCrumbs() {

        $sel_voice = MenuVoice::getSelectedVoice($this->_instance);
        $GINO = '';

        $cache = new \Gino\OutputCache($GINO, $this->_cache ? true : false);
        if($cache->start($this->_instance_name, "breadcrumbs".$sel_voice.$this->_registry->request->session->lng, $this->_cache)) {
            $this->_registry->addCss($this->_class_www."/menu_".$this->_instance_name.".css");
            $buffer = $this->pathToSelectedVoice();

            $view = new View(null, 'section');
            $dict = array(
                'id' => "menu-breadcrumbs-".$this->_instance_name,
                'content' => $buffer
            );

            $buffer = $view->render($dict);

            $cache->stop($buffer);
        }

        return $GINO;
    }

    /**
     * @brief Percorso alla voce di menu selezionata
     * @see self::breadCrumbs()
     * @return string
     */
    private function pathToSelectedVoice() {

        $s = MenuVoice::getSelectedVoice($this->_instance);
        $sVoice = new MenuVoice($s);
        $buffer = $sVoice->url ?"<a href=\"".$sVoice->url."\">".\Gino\htmlChars($sVoice->ml('label'))."</a>" : \Gino\htmlChars($sVoice->ml('label'));
        $parent = $sVoice->parent;
        while($parent!=0) {
            $pVoice = new MenuVoice($parent);
            $buffer = ($pVoice->url ? "<a href=\"".$sVoice->url."\">".\Gino\htmlChars($pVoice->ml('label'))."</a>" : \Gino\htmlChars($pVoice->ml('label')))." ".$this->_ico_more." ".$buffer;    
            $parent = $pVoice->parent;
        }
        return $buffer;
    }

    /**
     * @brief Interfaccia amministrativa per la gestione del menu
     * @param \Gino\Http\Request $request istanza di Gino.Http.Request
     * @return Gino.Http.Response
     */
    public function manageDoc(\Gino\Http\Request $request) {

        $this->requirePerm(array('can_admin', 'can_edit'));

        $action = \Gino\cleanVar($request->GET, 'action', 'string');
        $block = \Gino\cleanVar($request->GET, 'block', 'string', '');

        $link_frontend = sprintf('<a href="%s">%s</a>', $this->linkAdmin(array(), 'block=frontend'), _('Frontend'));
        $link_options = sprintf('<a href="%s">%s</a>', $this->linkAdmin(array(), 'block=options'), _('Opzioni'));
        $link_dft = sprintf('<a href="%s">%s</a>', $this->linkAdmin(), _('Gestione'));
        $sel_link = $link_dft;

        if($block == 'frontend') {
            $backend = $this->manageFrontend();
            $sel_link = $link_frontend;
        }
        elseif($block == 'options' && $this->userHasPerm('can_admin')) {
            $backend = $this->manageOptions();
            $sel_link = $link_options;
        }
        else {

            $id = \Gino\cleanVar($request->GET, 'id', 'int', '');
            $parent = \Gino\cleanVar($request->GET, 'parent', 'int', '');
            $voice = ($parent)?null:$id;
            $menuVoice = new MenuVoice($voice);

            if($action == 'delete') {
                return $this->actionDelMenuVoice($request);
            }
            elseif($request->checkGETKey('trnsl', '1')) {
                return $this->_trd->manageTranslation($request);
            }
            elseif($action == 'insert') {
                $backend = $this->formMenuVoice($menuVoice, $parent);
            }
            elseif($voice) {
                $backend = $this->formMenuVoice($menuVoice, $menuVoice->parent);
            }
            else {
                $backend = $this->listMenu();
            }
        }

        if(is_a($backend, '\Gino\Http\Response')) {
            return $backend;
        }

        if($this->userHasPerm('can_admin'))
            $links_array = array($link_frontend, $link_options, $link_dft);
        else
            $links_array = array($link_dft);

        $view = new View();
        $view->setViewTpl('tab');
        $dict = array(
            'title' => $this->_title,
            'links' => $links_array,
            'selected_link' => $sel_link,
            'content' => $backend
        );

        $document = new Document($view->render($dict));
        return $document();
    }

    /**
     * @brief Lista voci di menu aria amministrativa
     * @return string
     */
    private function listMenu() {

        $link_insert = sprintf('<a href="%s">%s</a>', $this->linkAdmin(array(), "action=insert"), \Gino\icon('insert', array('scale' => 2, 'text' => _("nuova voce"))));

        $GINO = $this->jsSortLib();
        $GINO .= '<p>'.sprintf(_('Per modificare l\'ordinamento delle voci di menu trascinare l\'icona %s nella posizione desiderata'), \Gino\icon('sort')).'</p>';
        $GINO .= $this->renderMenuAdmin(0);

        $view = new View(null, 'section');
        $dict = array(
            'title' => _('Menu'),
            'header_links' => $link_insert,
            'class' => 'admin',
            'content' => $GINO
        );

        return $view->render($dict);
    }

    /**
     * @brief Voci di menu con gli strumenti per la loro modifica, area amministrativa
     *
     * @see jsSortLib()
     * @param integer $parent valore ID della voce di menu alla quale la voce corrente è collegata
     * @return string
     */
    private function renderMenuAdmin($parent=0) {

        $GINO = '';
        $rows = $this->_registry->db->select('id', MenuVoice::$tbl_voices, "instance='$this->_instance' AND parent='$parent'", array('order' => 'order_list'));
        $sort = count($rows)>1 ? true : false;
        if($rows and count($rows)) {
            $GINO = "<ul id=\"".($sort ? "sortContainer".$parent : "")."\" class=\"menu-admin list-group\">";
            foreach($rows as $row) {
                $voice = new MenuVoice($row['id']);

                $link_modify = sprintf('<a href="%s">%s</a>', $this->linkAdmin(array(), "id={$voice->id}"), \Gino\icon('modify'));
                $link_delete = "<a href=\"javascript:if(gino.confirmSubmit('"._("l\'eliminazione è definitiva e comporta l\'eliminazione delle eventuali sottovoci, continuare?")."')) location.href='".$this->linkAdmin(array(), "id={$voice->id}&action=delete")."'\">".\Gino\icon('delete')."</a>";
                $link_subvoice = sprintf('<a href="%s">%s</a>', $this->linkAdmin(array(), "id={$voice->id}&action=insert&parent={$voice->id}"), \Gino\icon('insert', array('text' => _("nuova sottovoce"))));
                $handle = $sort ? "<span class=\"link sort_handler\">".\Gino\icon('sort')."</span> " : "";
                $links = $sort ? array($handle) : array();
                $links[] = $link_subvoice;
                $links[] = $link_modify;
                $links[] = $link_delete;
                $title = ($parent?"<img style=\"padding-bottom:4px\" src=\"".SITE_IMG."/list_mini.gif\" /> &#160;":"").\Gino\htmlChars($voice->label);
                $GINO .= "<li class=\"list-group-item\" id=\"id$voice->id\">".$title."<span class=\"badge\" style=\"background: #fff;\">".implode(' &#160; ', $links)."</span>".$this->renderMenuAdmin($voice->id)."</li>";
            }
            $GINO .= "</ul>";
        }

        return $GINO;
    }

    /**
     * @brief Aggiorna l'ordinamento delle voci di menu
     * 
     * @param \Gino\Http\Request $request istanza di Gino.Http.Request
     * @return \Gino\Http\Response, risultato ordinamento
     */
    public function actionUpdateOrder(\Gino\Http\Request $request) {

        $this->requirePerm(array('can_admin', 'can_edit'));

        $res = true;

        $order = \Gino\cleanVar($request->POST, 'order', 'string', '');
        $items = explode(",", $order);
        $i=1;
        foreach($items as $item) {
            $voice = new menuVoice($item);
            $voice->order_list = $i;
            if(!$voice->save(array('only_update' => 'order_list'))) {
            	$res = false;
            }
            $i++;
        }

        $content = $res ? _("Ordinamento effettuato con successo") : _("Ordinamento non effettuato");

        return new Response($content);
    }

    /**
     * @brief Form inserimento/modifica voce di menu
     * @param \Gino\App\Menu\MenuVoice istanza di Gino.App.Menu.MenuVoice
     * @param int $parent id voce parent
     * @return string, form
     */
    private function formMenuVoice($voice, $parent) {

        $buffer =  $voice->formVoice($this->link($this->_instance_name, 'actionMenuVoice'), $parent);

        $content = $this->searchModules();
        $buffer .= $content->getContent();

        return $buffer;
     }

    /**
     * @brief Processa il form di inserimento/modifica voce di menu
     * 
     * @param \Gino\Http\Request $request istanza di Gino.Http.Request
     * @return \Gino\Http\Redirect
     */
    public function actionMenuVoice(\Gino\Http\Request $request) {

        $this->requirePerm(array('can_admin', 'can_edit'));

        $gform = \Gino\Loader::load('Form', array());
        $gform->saveSession('dataform');
        $req_error = $gform->checkRequired();

        $id = \Gino\cleanVar($request->POST, 'id', 'int');
        $action = \Gino\cleanVar($request->POST, 'action', 'string');

        $link_params = array();
        if($action) $link_params['action'] = $action;
        if($id) $link_params['id'] = $id;

        $link_error = $this->linkAdmin(array(), $link_params);

        if($req_error > 0) {
        	return Error::errorMessage(array('error'=>1), $link_error);
        }

        $menu_voice = new MenuVoice($id);

        $menu_voice->instance = $this->_instance;
        $menu_voice->parent = \Gino\cleanVar($request->POST, 'parent', 'int', null);
        $menu_voice->label = \Gino\cleanVar($request->POST, 'label', 'string', null);
        $menu_voice->url = \Gino\cleanVar($request->POST, 'url', 'string', null);
        $menu_voice->type = \Gino\cleanVar($request->POST, 'type', 'string', null);

        if(!$id) $menu_voice->initOrderList();

        $perms = \Gino\cleanVar($request->POST, 'perm', 'array', null);
        $menu_voice->perms = count($perms) ? implode(';', $perms) : '';

        $menu_voice->save();

        return new Redirect($this->linkAdmin());
    }

    /**
     * @brief Eliminazione di una voce di menu
     * 
     * @param \Gino\Http\Request $request istanza di Gino.Http.Request
     * @return \Gino\Http\Redirect
     */
    public function actionDelMenuVoice($request) {

        $this->requirePerm(array('can_admin', 'can_edit'));

        $id = \Gino\cleanVar($request->GET, 'id', 'int', '');

        $link_error = $this->linkAdmin();
        if(!$id)
            return Error::errorMessage(array('error'=>9), $link_error);

        $voice = new MenuVoice($id);
        $voice->deleteVoice();
        $voice->updateOrderList();

        return new Redirect($this->_registry->router->link($this->_instance_name, 'manageDoc'));
    }

    /**
     * @brief Form di ricerca moduli e pagine collegabili a voci di menu
     * @return \Gino\Http\Response
     */
    public function searchModules(){

        $this->requirePerm(array('can_admin', 'can_edit'));

        $buffer = "<p class=\"backoffice-info\">"._('Utilizzando il modulo di ricerca viste i campi url e permessi verranno autocompilati con i valori corretti per la vista selezionata.')."</p>";
        $gform = new \Gino\Form();
        $gform->setValidation(false);
        $buffer .= $this->jsSearchModulesLib();
        $buffer .= "<div class=\"text-center\">\n";
        $buffer .= _("pagine").": <input type=\"text\" id=\"s_page\" name=\"s_page\" size=\"10\" />&nbsp; &nbsp; ";
        $buffer .= _("moduli").": <input type=\"text\" id=\"s_class\" name=\"s_class\" size=\"10\" />\n";
        $buffer .= "&nbsp; ";
        $buffer .= \Gino\Input::input('s_all', 'button', _("mostra tutti"), array("classField"=>"generic", "id"=>"s_all"));

        $buffer .= "</div>\n";

        $buffer .= "<div id=\"items_list\"></div>\n";

        $view = new View(null, 'section');
        $dict = array(
            'title' => _('Ricerca viste'),
            'class' => 'admin',
            'content' => $buffer
        );

        return new Response($view->render($dict));
    }

    /**
     * @brief Libreria javascript per l'ordinamento delle voci di menu
     * 
     * Chiamate Ajax: \n
     *     - actionUpdateOrder()
     *
     * @see actionUpdateOrder()
     * @return string, codice js
     */
    private function jsSortLib() {

        $GINO = "<script type=\"text/javascript\">\n";
        $GINO .= "function menuMessage(response) { alert(response) }";
        $GINO .= "
        (function() {
        	window.addEvent('load', function() { 
				$$('ul[id^=sortContainer]').each(function(ul) {
					var menuSortables = new Sortables(ul, {
						constrain: false,
						handle: '.sort_handler',
						clone: false,
						revert: { duration: 500, transition: 'elastic:out' },
						onComplete: function() {
							var order = this.serialize(1, function(element, index) {
								return element.getProperty('id').replace('id', '');
							}).join(',');
							gino.ajaxRequest('post', '".$this->link($this->_instance_name, 'actionUpdateOrder')."', 'order='+order, null, {'callback':menuMessage});
						}
					});
				})
    		});
		})()";
        $GINO .= "</script>";
        return $GINO;
    }
    
    /**
     * @brief Libreria javascript per la ricerca dei moduli
     * 
     * Chiamate Ajax: \n
     *     - printItemsList()
     * 
     * @return string, codice js
     */
    private function jsSearchModulesLib() {

        $buffer = "<script type=\"text/javascript\">\n";
        $buffer .= "window.addEvent('load', function() {

                    var myclass, mypage, all, active, other;
                    var url = '".$this->link($this->_instance_name, 'printItemsList')."';
                    $$('#s_class', '#s_page').each(function(el) {
                        el.addEvent('keyup', function(e) {
                            active = el.getProperty('id');
                            other = (active=='s_class')? 's_page':'s_class';
                            $(other).setProperty('value', '');
                            gino.ajaxRequest('post', url, active+'='+$(active).value, 'items_list', {'load':'items_list', 'cache':true});
                        })
                    })

                    $('s_all').addEvent('click', function() {

                            $$('#s_page', '#s_class').setProperty('value', '');
                            gino.ajaxRequest('post', url, 'all=all', 'items_list', {'load':'items_list', 'cache':true});
                        }
                    );

                });\n";
        $buffer .= "</script>\n";

        return $buffer;
    }

    /**
     * @brief Mostra le interfacce che le classi mettono a disposizione del menu e le pagine
     * 
     * @see self::printItemsClass()
     * @see self::printItemsPage()
     * @param \Gino\Http\Request $request istanza di Gino.Http.Request
     * @return \Gino\Http\Response
     */
    public function printItemsList(\Gino\Http\Request $request) {

        \Gino\Loader::import('sysClass', 'ModuleApp');
        \Gino\Loader::import('module', 'ModuleInstance');
        \Gino\Loader::import('page', 'PageEntry');

        $this->requirePerm(array('can_admin', 'can_edit'));

        $class = \Gino\cleanVar($request->POST, 's_class', 'string', '');
        $page = \Gino\cleanVar($request->POST, 's_page', 'string', '');
        $all = \Gino\cleanVar($request->POST, 'all', 'string', '');

        if(!($class || $page || $all)) return '';

        $GINO = "<div style=\"max-height:600px;overflow:auto; border: 2px solid #eee; margin-top: 10px; padding: 10px;\">";

        if(!empty($class)) {
            $modules_app = ModuleApp::objects(null, array('where' => "active='1' AND label LIKE '$class%' AND instantiable='0'"));
            $modules = ModuleInstance::objects(null, array('where' => "active='1' AND label LIKE '$class%'"));
            $GINO .= $this->printItemsClass($modules_app, $modules);
        }
        elseif(!empty($page)) {
            $pages = \Gino\App\Page\PageEntry::objects(null, array('where' => "title LIKE '%$page%' AND published='1'"));
            $GINO .= $this->printItemsPage($pages);
        }
        elseif(!empty($all) && $all=='all') {
            $pages = \Gino\App\Page\PageEntry::objects(null, array('where' => "published='1'"));
            $GINO .= $this->printItemsPage($pages);

            $modules_app = ModuleApp::objects(null, array('where' => "active='1' AND instantiable='0'"));
            $modules = ModuleInstance::objects(null, array('where' => "active='1'"));
            $GINO .= $this->printItemsClass($modules_app, $modules);
        }

        $GINO .= "</div>";

        return new Response($GINO);
    }

    /**
     * @brief Elenco pagine che è possibile collegare a una voce di menu
     * 
     * @see Gino.App.Auth.Permission::getFromFullCode()
     * @param array $array_search la chiave è il valore ID e il valore il titolo della pagina
     * @return string
     */
    private function printItemsPage($pages){

        \Gino\Loader::import('auth', 'Permission');

        if(count($pages)) {
            $GINO = "<h3>"._("Pagine")."</h3>";
            $view_table = new View(null, 'table');
            $view_table->assign('class', 'table table-striped table-hover table-bordered');
            $view_table->assign('heads', array(
                _('titolo'),
                _('url'),
                _('permessi'),
                ''
            ));
            $tbl_rows = array();
            foreach($pages AS $page) {
                $page_perm = '';
                if($page->private) $page_perm .= _("pagina privata");
                if($page->private && $page->users) $page_perm .= " / ";
                if($page->users) $page_perm .= _("pagina limitata ad utenti selezionati");

                $p = \Gino\App\Auth\Permission::getFromFullCode('page.can_view_private');

                $button = "<input data-private=\"".$page->private."\" type=\"button\" value=\""._("aggiungi dati")."\" onclick=\"
                    $('url').set('value', '".$page->getUrl()."');
                    $$('.form-multicheck input[type=checkbox][value]').removeProperty('checked');
                    var private = $(this).get('data-private');
                    if(private.toInt()) {
                        $$('input[value=".$p->id.",0]').setProperty('checked', 'checked');
                    }
                    location.hash = 'top';
                \" />\n";

                $tbl_rows[] = array(
                    \Gino\htmlChars($page->title),
                    $page->getUrl(),
                    $page_perm,
                    $button
                );
            }
            $view_table->assign('rows', $tbl_rows);
            $GINO .= $view_table->render();
        }
        else {
            $GINO = '';
        }


        return $GINO;
    }

    /**
     * @brief Interfacce che le classi dei moduli mettono a disposizione del menu
     * @description Si richiamano i metodi outputFunctions() delle classi dei moduli e dei moduli di sistema
     * 
     * @see Gino.App.Auth.Permission::getFromFullCode()
     * @param array $array_search array di array con le chiavi id, name, label, role1
     * @return string
     */
    private function printItemsClass($modules_app, $modules){

        \Gino\Loader::import('auth', 'Permission');

        $GINO = '';

        if(count($modules_app)) {
            $GINO .= "<h3>"._("Moduli di sistema")."</h3>";
            $view_table = new \Gino\View(null, 'table');
            $view_table->assign('class', 'table table-striped table-hover table-bordered');
            $view_table->assign('heads', array(
                _('modulo'),
                _('vista'),
                _('url'),
                _('permessi'),
                ''
            ));
            $tbl_rows = array();
            $cnt = 0;
            foreach($modules_app AS $module_app) {

                $class = $module_app->classNameNs();
                $class_name = $module_app->className();

                if(method_exists($class, 'outputFunctions')) {
                    $list = call_user_func(array($class, 'outputFunctions'));
                    //@todo aggiungere controllo che sia nell'ini
                    foreach($list as $func => $desc) {
                        $method_check = parse_ini_file(APP_DIR.OS.$class_name.OS.$class_name.".ini", TRUE);
                        $public_method = @$method_check['PUBLIC_METHODS'][$func];
                        if(isset($public_method)) {
                            $cnt++;
                            $permissions_code = $desc['permissions'];
                            $description = $desc['label'];
                            $permissions = array();
                            $perms_js = array();
                            if($permissions_code and count($permissions_code)) {
                                foreach($permissions_code as $permission_code) {
                                	if(!preg_match('#\.#', $permission_code)) {
                                		$permission_code = $class_name.'.'.$permission_code;
                                	}
                                    $p = \Gino\App\Auth\Permission::getFromFullCode($permission_code);
                                    $permissions[] = $p->label;
                                    $perms_js[] = $p->id;
                                }
                            }

                            $url = $this->_registry->router->link($class_name, $func);

                            $button = "<input data-perm=\"".implode(';', $perms_js)."\" type=\"button\" value=\""._("aggiungi dati")."\" onclick=\"
                                $('url').set('value', '".$url."');
                                $$('.form-multicheck input[type=checkbox][value]').removeProperty('checked');
                                perms = $(this).get('data-perm');
                                if(perms) {
                                    perms.split(';').each(function(p) {
                                        $$('input[value=' + p + ',0]').setProperty('checked', 'checked');
                                    })
                                }
                                location.hash = 'top';
                            \" />\n";

                            $tbl_rows[] = array(
                                \Gino\htmlChars($module_app->label),
                                $description,
                                $url,
                                implode(', ', $permissions),
                                $button
                            );
                        }
                    }
                }
            }
            $view_table->assign('rows', $tbl_rows);
            $GINO .= $cnt ? $view_table->render() : "<p>"._('Nessun risultato')."</p>";
        }

        if(count($modules)) {
            $GINO .= "<h3>"._("Istanze")."</h3>";
            $view_table = new View(null, 'table');
            $view_table->assign('class', 'table table-striped table-hover table-bordered');
            $view_table->assign('heads', array(
                _('modulo'),
                _('vista'),
                _('url'),
                _('permessi'),
                ''
            ));
            $tbl_rows = array();
            $cnt = 0;
            foreach($modules AS $module) {

                $class = $module->classNameNs();
                $class_name = $module->className();
                $module_name = $module->name;

                if(method_exists($class, 'outputFunctions')) {
                    $list = call_user_func(array($class, 'outputFunctions'));
                    foreach($list as $func => $desc) {
                        $method_check = parse_ini_file(APP_DIR.OS.$class_name.OS.$class_name.".ini", TRUE);
                        $public_method = @$method_check['PUBLIC_METHODS'][$func];
                        if(isset($public_method)) {
                            $cnt++;
                            $permissions_code = $desc['permissions'];
                            $description = $desc['label'];
                            $permissions = array();
                            $perms_js = array();
                            if($permissions_code and count($permissions_code)) {
                                foreach($permissions_code as $permission_code) {
                                    if(!preg_match('#\.#', $permission_code)) {
                                        $permission_code = $class_name.'.'.$permission_code;
                                    }
                                    $p = \Gino\App\Auth\Permission::getFromFullCode($permission_code);
                                    $permissions[] = $p->label;
                                    $perms_js[] = $p->id;
                                }
                            }

                            $url = $this->_registry->router->link($module_name, $func);

                            $button = "<input data-perm=\"".implode(';', $perms_js)."\" type=\"button\" value=\""._("aggiungi dati")."\" onclick=\"
                            $('url').set('value', '".$url."');
                            perms = $(this).get('data-perm');
                            $$('.form-multicheck input[type=checkbox][value]').removeProperty('checked');
                            if(perms) {
                                    perms.split(';').each(function(p) {
                                            $$('input[value=' + p + ',".$module->id."]').setProperty('checked', 'checked');
                                    })
                            }
                            location.hash = 'top';
                            \" />\n";

                            $tbl_rows[] = array(
                                \Gino\htmlChars($module->label),
                                $description,
                                $url,
                                implode(', ', $permissions),
                                $button
                            );
                        }
                    }
                }
            }
            $view_table->assign('rows', $tbl_rows);
            $GINO .= $cnt ? $view_table->render() : "<p>"._('Nessun risultato')."</p>";
        }

        return $GINO;
    }
}
