<?php
/**
 * @file class_menu.php
 * @brief Contiene la classe menu
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino\App\Menu;

// Include il file class_menuVoice.php
require_once('class.MenuVoice.php');

/**
 * @brief Libreria per la gestione dei menu
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class menu extends \Gino\Controller {

  private static $_menu_functions_list = 'menuFunctionsList';

  private $_tbl_opt;
  
  private $_options;
  public $_optionsLabels;
  private $_title;
  private $_cache;
  private $_ico_more;

  private $_block;

  function __construct($instance) {

    parent::__construct($instance);

    $this->_tbl_opt = "sys_menu_opt";

    /*
      Opzioni
    */

    $this->_title = \Gino\htmlChars($this->setOption('title', true));
    $this->_cache = $this->setOption('cache', array("value"=>0));

    // the second paramether will be the class instance
    $this->_options = \Gino\Loader::load('Options', array($this));
    $this->_optionsLabels = array(
      "title"=>_("Titolo"),
      "cache"=>array("label"=>array(_("Tempo di caching dei contenuti (s)"), _("Se non si vogliono tenere in cache o non si è sicuri del significato lasciare vuoto o settare a 0")), "required"=>false)
    );
    $this->_action = (isset($_POST['action']) || isset($_GET['action'])) ? $_REQUEST['action'] : null;

    $this->_ico_more = " / ";

    $this->_block = \Gino\cleanVar($_REQUEST, 'block', 'string', '');
  }
  
  /**
   * Fornisce i riferimenti della classe, da utilizzare nel processo di creazione e di eliminazione di una istanza 
   * 
   * @return array
   */
  public static function getClassElements() {

    return array("tables"=>array('sys_menu_voices', 'sys_menu_opt'),
      "css"=>array('menu.css'),
      'views' => array(
        'render.php' => _('Stampa il menu')
      )
    );
  }
  
  /**
   * Eliminazione di una istanza
   * 
   * @return boolean
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
    
    $query = "DELETE FROM ".$this->_tbl_opt." WHERE instance='$this->_instance'";	
    $result = $this->_db->actionquery($query);

    return $result;
  }

  /**
   * Elenco dei metodi che possono essere richiamati dal menu e dal template
   * 
   * @return array
   */
  public static function outputFunctions() {

    $list = array(
      "render" => array("label"=>_("visualizzazione menu"), "permissions"=>array()),
      "breadCrumbs" => array("label"=>_("Briciole di pane"), "permissions"=>array())
    );

    return $list;
  }

  /**
   * Interfaccia per visualizzare il menu
   * 
   * @see menuVoice::getSelectedVoice()
   * @see renderMenu()
   * @see $_access_base
   * @return string
   */
  public function render() {

      $session = \Gino\Session::instance();
      $sel_voice = MenuVoice::getSelectedVoice($this->_instance);
      $this->_registry->addCss($this->_class_www."/menu_".$this->_instance_name.".css");

      $cache = new \Gino\OutputCache($buffer, $this->_cache ? true : false);
      if($cache->start($this->_instance_name, "view".$sel_voice.$session->lng, $this->_cache)) {

      $tree = $this->getTree();
      $view = new \Gino\View($this->_view_dir);
      $view->setViewTpl('render_'.$this->_instance_name);
      $dict = array(
        'instance_name' => $this->_instance_name,
        'title' => $this->_title,
        'selected' => $sel_voice,
        'tree' => $tree,
      );

      $GINO = $view->render($dict);


      $cache->stop($GINO);
    }

    return $buffer;
  }

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
   * Interfaccia per visualizzare le briciole di pane
   * 
   * @see $_access_base
   * @return string
   */
  public function breadCrumbs() {
    
    $sel_voice = MenuVoice::getSelectedVoice($this->_instance);
    $GINO = '';

    $cache = new \Gino\OutputCache($GINO, $this->_cache ? true : false);
    if($cache->start($this->_instance_name, "breadcrumbs".$sel_voice.$this->_registry->session->lng, $this->_cache)) {
      $this->_registry->addCss($this->_class_www."/menu_".$this->_instance_name.".css");
      $buffer = $this->pathToSelectedVoice();

      $view = new \Gino\View(null, 'section');
      $dict = array(
        'id' => "menu-breadcrumbs-".$this->_instance_name,
        'content' => $buffer
      );

      $buffer = $view->render($dict);

      $cache->stop($buffer);
    }

    return $GINO;
  }

  private function pathToSelectedVoice() {
  
    $s = MenuVoice::getSelectedVoice($this->_instance);
    $sVoice = new MenuVoice($s);
    $buffer = $sVoice->url ?"<a href=\"".$this->_plink->linkFromDB($sVoice->url)."\">".\Gino\htmlChars($sVoice->ml('label'))."</a>" : \Gino\htmlChars($sVoice->ml('label'));
    $parent = $sVoice->parent;
    while($parent!=0) {
      $pVoice = new menuVoice($parent);
      $buffer = ($pVoice->url ? "<a href=\"".$this->_plink->linkFromDB($sVoice->url)."\">".\Gino\htmlChars($pVoice->ml('label'))."</a>" : \Gino\htmlChars($pVoice->ml('label')))." ".$this->_ico_more." ".$buffer;	
      $parent = $pVoice->parent;	
    }
    return $buffer;
  }

  /**
   * Interfaccia amministrativa per la gestione del menu
   * 
   * @return string
   */
  public function manageDoc() {
    
    $this->requirePerm(array('can_admin', 'can_edit'));

	$this->_registry->addCss($this->_class_www."/menu_".$this->_instance_name.".css");

    $link_admin = "<a href=\"".$this->_home."?evt[$this->_instance_name-manageDoc]&block=permissions\">"._("Permessi")."</a>";
    $link_options = "<a href=\"".$this->_home."?evt[$this->_instance_name-manageDoc]&block=options\">"._("Opzioni")."</a>";
    $link_frontend = "<a href=\"".$this->_home."?evt[$this->_instance_name-manageDoc]&block=frontend\">"._("Frontend")."</a>";
    $link_dft = "<a href=\"".$this->_home."?evt[".$this->_instance_name."-manageDoc]\">"._("Gestione")."</a>";
    $sel_link = $link_dft;
    
    if($this->_block == 'frontend') {
      $GINO = $this->manageFrontend();		
      $sel_link = $link_frontend;
    }
    elseif($this->_block == 'options') {
      $GINO = $this->manageOptions();
      $sel_link = $link_options;
    }
    else {

      $id = \Gino\cleanVar($_GET, 'id', 'int', '');
      $parent = \Gino\cleanVar($_GET, 'parent', 'int', '');
      $voice = ($parent)?null:$id;
      $menuVoice = new MenuVoice($voice);

      if($this->_action == 'delete') {
        $this->actionDelMenuVoice();
        exit();
      }
      elseif(isset($_GET['trnsl']) and $_GET['trnsl'] == '1') {
        if(isset($_GET['save']) and $_GET['save'] == '1') {
          $this->_trd->actionTranslation();
        }
        else {
          $this->_trd->formTranslation();
        }
      }
      elseif($this->_action == 'insert') {
        $GINO = $this->formMenuVoice($menuVoice, $parent);
      }
      elseif($voice) {
        $GINO = $this->formMenuVoice($menuVoice, $menuVoice->parent);
      }
      else {
        $GINO = $this->listMenu($id);
      }
    }
      
    if($this->userHasPerm('can_admin'))
      $links_array = array($link_frontend, $link_options, $link_dft);
    else
      $links_array = array($link_options, $link_dft);

    $dict = array(
      'title' => $this->_title,
      'links' => $links_array,
      'selected_link' => $sel_link,
      'content' => $GINO
    );

    $view = new \Gino\View(null, 'tab');
    return $view->render($dict);
  }

  private function listMenu($id) {
    
    $link_insert = "<a href=\"$this->_home?evt[$this->_instance_name-manageDoc]&amp;action=insert\">".$this->_registry->pub->icon('insert', array('scale' => 2, 'text' => _("nuova voce")))."</a>";

    $GINO = $this->jsSortLib();
    $GINO .= '<p>'.sprintf(_('Per modificare l\'ordinamento delle voci di menu trascinare l\'icona %s nella posizione desiderata'), $this->_registry->pub->icon('sort')).'</p>';
    $GINO .= $this->renderMenuAdmin(0);
    
    $view = new \Gino\View(null, 'section');
    $dict = array(
      'title' => _('Menu'),
      'header_links' => $link_insert,
      'class' => 'admin',
      'content' => $GINO
    );

    return $view->render($dict);
  }
  
  /**
   * Voci di menu con gli strumenti per la loro modifica
   * 
   * @see jsSortLib()
   * @param integer $parent valore ID della voce di menu alla quale la voce corrente è collegata
   * @param integer $s valore ID della voce di menu corrente
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
        $link_modify = "<a href=\"$this->_home?evt[$this->_instance_name-manageDoc]&id={$voice->id}\">".\Gino\pub::icon('modify')."</a>";
        $link_delete = "<a href=\"javascript:if(gino.confirmSubmit('"._("l\'eliminazione è definitiva e comporta l\'eliminazione delle eventuali sottovoci, continuare?")."')) location.href='$this->_home?evt[$this->_instance_name-manageDoc]&id={$voice->id}&action=delete'\">".\Gino\pub::icon('delete')."</a>";
        $link_subvoice = "<a href=\"$this->_home?evt[$this->_instance_name-manageDoc]&id={$voice->id}&action=insert&parent={$voice->id}\">".\Gino\pub::icon('insert', array('text' => _("nuova sottovoce")))."</a>";
        $handle = $sort ? "<span class=\"link sort_handler\">".$this->_registry->pub->icon('sort')."</span> ":"";
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
   * Aggiorna l'ordinamento delle voci di menu
   * 
   */
  public function actionUpdateOrder() {
  
    $this->requirePerm(array('can_admin', 'can_edit'));

    $order = \Gino\cleanVar($_POST, 'order', 'string', '');
    $items = explode(",", $order);
    $i=1;
    foreach($items as $item) {
      $voice = new menuVoice($item);
      $voice->order_list = $i;
      $voice->save();
      $i++;
    }
  }

  private function formMenuVoice($voice, $parent) {
    
    $buffer =  $voice->formVoice($this->_home."?evt[$this->_instance_name-actionMenuVoice]", $parent);
    $buffer .=  $this->searchModules();

    return $buffer;
  }

  /**
   * Inserimento e modifica di una voce di menu
   * 
   */
  public function actionMenuVoice() {
    
    $this->requirePerm(array('can_admin', 'can_edit'));

    $gform = \Gino\Loader::load('Form', array('gform', 'post', false));
    $gform->save('dataform');
    $req_error = $gform->arequired();

    $id = \Gino\cleanVar($_POST, 'id', 'int', '');

    $link_params = "action=$this->_action";
    if($id) $link_params .= "&id=$id";

    $link_error = $this->_home."?evt[$this->_instance_name-manageDoc]&$link_params";

    if($req_error > 0) 
      exit(error::errorMessage(array('error'=>1), $link_error));

    $menu_voice = new MenuVoice($id);

    $menu_voice->instance = $this->_instance;
    $menu_voice->parent = \Gino\cleanVar($_POST, 'parent', 'int', null);
    $menu_voice->label = \Gino\cleanVar($_POST, 'label', 'string', null);
    $menu_voice->url = \Gino\cleanVar($_POST, 'url', 'string', null);
    $menu_voice->type = \Gino\cleanVar($_POST, 'type', 'string', null);

    if(!$id) $menu_voice->initOrderList();

    $perms = \Gino\cleanVar($_POST, 'perm', 'array', null);
    $menu_voice->perms = implode(';', $perms);

    $menu_voice->save();

    \Gino\Link::HttpCall($this->_home, $this->_instance_name.'-manageDoc', '');
  }
  
  /**
   * Eliminazione di una voce di menu
   * 
   */
  public function actionDelMenuVoice() {
    
    $this->requirePerm(array('can_admin', 'can_edit'));

    $id = \Gino\cleanVar($_GET, 'id', 'int', '');

    $link_error = $this->_home."?evt[$this->_instance_name-manageDoc]";
    if(!$id)
      exit(error::errorMessage(array('error'=>9), $link_error));

    $voice = new MenuVoice($id);
    $voice->deleteVoice();
    $voice->updateOrderList();

    \Gino\Link::HttpCall($this->_home, $this->_instance_name.'-manageDoc', '');
  }

  /**
   * Ricerca moduli
   * 
   * @see jsSearchModulesLib()
   * @see $_group_1
   * @return string
   */
  public function searchModules(){

    $this->requirePerm(array('can_admin', 'can_edit'));

    $buffer = "<p class=\"backoffice-info\">"._('Utilizzando il modulo di ricerca viste i campi url e permessi verranno autocompilati con i valori corretti per la vista selezionata.')."</p>";
    $gform = new \Gino\Form('gform', 'post', false);
    $buffer .= $this->jsSearchModulesLib();
    $buffer .= "<div class=\"text-center\">\n";
    $buffer .= _("pagine").": <input type=\"text\" id=\"s_page\" name=\"s_page\" size=\"10\" />&nbsp; &nbsp; ";
    $buffer .= _("moduli").": <input type=\"text\" id=\"s_class\" name=\"s_class\" size=\"10\" />\n";
    $buffer .= "&nbsp; ";
    $buffer .= $gform->input('s_all', 'button', _("mostra tutti"), array("classField"=>"generic", "id"=>"s_all"));

    $buffer .= "</div>\n";
    
    $buffer .= "<div id=\"items_list\"></div>\n";
    
    $view = new \Gino\View(null, 'section');
    $dict = array(
      'title' => _('Ricerca viste'),
      'class' => 'admin',
      'content' => $buffer
    );

    return $view->render($dict);
  }
  
  /**
   * Libreria javascript per l'ordinamento delle voci di menu
   * 
   * @see actionUpdateOrder()
   * @return string
   * 
   * Chiamate Ajax: \n
   *   - actionUpdateOrder()
   */
  private function jsSortLib() {
  
    $GINO = "<script type=\"text/javascript\">\n";
    $GINO .= "function menuMessage() { alert('"._("Ordinamento effettuato con successo")."')}";
    $GINO .= "window.addEvent('load', function() { 
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
                      gino.ajaxRequest('post', '$this->_home?pt[$this->_instance_name-actionUpdateOrder]', 'order='+order, null, {'callback':menuMessage});
                    }
                  });
                })
              })";
    $GINO .= "</script>";
    return $GINO;
  }

  /**
   * Libreria javascript per la ricerca dei moduli
   * 
   * @see printItemsList()
   * @return string
   * 
   * Chiamate Ajax: \n
   *   - printItemsList()
   */
  private function jsSearchModulesLib() {
  
    $buffer = "<script type=\"text/javascript\">\n";
    $buffer .= "window.addEvent('load', function() {
          
          var myclass, mypage, all, active, other;
          var url = '".$this->_home."?pt[".$this->_instance_name."-printItemsList]';
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
   * Mostra le interfacce che le classi mettono a disposizione del menu e le pagine
   * 
   * @see printItemsClass()
   * @see printItemsPage()
   * @return string
   */
  public function printItemsList() {

    \Gino\Loader::import('sysClass', 'ModuleApp');
    \Gino\Loader::import('module', 'ModuleInstance');
    \Gino\Loader::import('page', 'PageEntry');

    $this->requirePerm(array('can_admin', 'can_edit'));

    $class = \Gino\cleanVar($_POST, 's_class', 'string', '');
    $page = \Gino\cleanVar($_POST, 's_page', 'string', '');
    $all = \Gino\cleanVar($_POST, 'all', 'string', '');

    if(!($class || $page || $all)) return '';

    $GINO = "<div style=\"max-height:600px;overflow:auto; border: 2px solid #eee; margin-top: 10px; padding: 10px;\">";

    if(!empty($class)) {
      $modules_app = \Gino\App\SysClass\ModuleApp::get(array('where' => "active='1' AND label LIKE '$class%' AND instantiable='0'"));
      $modules = \Gino\App\Module\ModuleInstance::get(array('where' => "active='1' AND label LIKE '$class%'"));
      $GINO .= $this->printItemsClass($modules_app, $modules);
    }
    elseif(!empty($page)) {
      $pages = \Gino\App\Page\PageEntry::getAll(array('where' => "title LIKE '%$page%' AND published='1'"));
      $GINO .= $this->printItemsPage($pages);
    }
    elseif(!empty($all) && $all=='all') {
      $pages = \Gino\App\Page\PageEntry::getAll(array('where' => "published='1'"));
      $GINO .= $this->printItemsPage($pages);

      $modules_app = \Gino\App\SysClass\ModuleApp::objects(null, array('where' => "active='1'"));
      $modules = \Gino\App\Module\ModuleInstance::objects(null, array('where' => "active='1'"));
      $GINO .= $this->printItemsClass($modules_app, $modules);
    }

    $GINO .= "</div>";

    return $GINO;
  }
  
  /**
   * Elenco pagine che è possibile inserire come voce di menu
   * 
   * @see page::getUrlPage()
   * @see Permission::getFromFullCode()
   * @param array $array_search la chiave è il valore ID e il valore il titolo della pagina
   * @return string
   */
  private function printItemsPage($pages){

    \Gino\Loader::import('auth', 'Permission');

    if(count($pages)) {
      $GINO = "<h3>"._("Pagine")."</h3>";
      $view_table = new \Gino\View(null, 'table');
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
   * Interfacce che le classi dei moduli mettono a disposizione del menu
   * 
   * Si richiamano i metodi outputFunctions() delle classi dei moduli e dei moduli di sistema
   * 
   * @see Permission::getFromFullCode()
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
          foreach($list as $func => $desc) {
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

            $url = $this->_registry->plink->aLink($class_name, $func);
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
      $view_table->assign('rows', $tbl_rows);
      $GINO .= $cnt ? $view_table->render() : "<p>"._('Nessun risultato')."</p>";
    }

    if(count($modules)) {
      $GINO .= "<h3>"._("Istanze")."</h3>";
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
      foreach($modules AS $module) {
        
      	$class = $module->classNameNs();
      	$class_name = $module->className();
        $module_name = $module->name;
        
		if(method_exists($class, 'outputFunctions')) {
			
			$list = call_user_func(array($class, 'outputFunctions'));
			foreach($list as $func => $desc) {
				$cnt++;
				$permissions_code = $desc['permissions'];
				$description = $desc['label'];
				$permissions = array();
				$perms_js = array();
				if($permissions_code and count($permissions_code)) {
					foreach($permissions_code as $permission_code) {
						$p = \Gino\App\Auth\Permission::getFromClassCode($class, $permission_code);
						$permissions[] = $p->label;
						$perms_js[] = $p->id;
					}
				}
				
				$url = $this->_registry->plink->aLink($module_name, $func);
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
      $view_table->assign('rows', $tbl_rows);
      $GINO .= $cnt ? $view_table->render() : "<p>"._('Nessun risultato')."</p>";
    }

    return $GINO;

  }
}
?>
