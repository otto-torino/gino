<?php
/**
 * @file class_layout.php
 * @brief Contiene la classe layout
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino\App\Layout;

/**
 * @brief Gestisce il layout dell'applicazione raggruppando le funzionalità fornite dalle librerie dei css, template e skin
 * 
 * @see class.css.php
 * @see class.template.php
 * @see class.skin.php
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 * 
 * Fornisce le interfacce per la modifica dei file di frontend generali di gino: \n
 *   - file css presenti nella directory @a css
 *   - file delle viste presenti nella directory @a views
 * 
 * PROCESSO DI GESTIONE DEL LAYOUT
 * ---------------
 * Lo schema del layout viene stampato dal metodo template::manageTemplate() che legge il file di template e identifica porzioni di codice tipo:
 * @code
 * <div id="nav_3_1_1" style="width:100%;">
 * {module sysclassid=8 func=printFooterPublic}
 * </div>
 * @endcode
 * Queste porzioni di codice vengono passate con la funzione preg_replace_callback() al metodo template::renderNave() che recupera il tipo di blocco nello schema del template utilizzando delle funzioni di preg_match(). \n
 * L'elenco dei moduli/pagine disponibili viene gestito dal metodo layout::modulesList().
 * 
 * TRADUZIONE DEL LAYOUT NELLA VISUALIZZAZIONE DI UNA PAGINA
 * ---------------
 * Per quanto riguarda la visualizzazione di una pagina, il layout viene gestito dal metodo document::render() che identifica il file del template, lo legge ed effettua un preg_replace_callback() col metodo document::renderNave(). \n
 * Quando document::renderNave() identifica la stringa @a module richiama document::renderModule() che in base al tipo riconosciuto richiama un metodo tra:
 *   - modPage()
 *   - modClass()
 *   - modFunc()
 *   - modUrl()
 * 
 */
class layout extends \Gino\Controller {

	private $_tbl_skin, $_tbl_css;
	private $_relativeUrl;
	private $_template;
	private $_css;

	private $_action, $_block;

	function __construct() {

		parent::__construct();

		$this->_tbl_skin = 'sys_layout_skin';
		$this->_tbl_css = 'sys_layout_css';

		$this->_action = \Gino\cleanVar($_REQUEST, 'action', 'string', '');
		$this->_block = \Gino\cleanVar($_REQUEST, 'block', 'string', 'skin');
		if(empty($this->_block)) $this->_block = 'css';
	}

	/**
	 * Interfaccia amministrativa per la gestione del layout
	 * 
	 * @see manageStyleCss()
	 * @see layoutList()
	 * @see template::manageTemplate()
	 * @return string
	 */
	public function manageLayout() {

		$this->requirePerm('can_admin');
		
		$block = \Gino\cleanVar($_GET, 'block', 'string', null);

		$link_dft = "<a href=\"".$this->_home."?evt[".$this->_class_name."-manageLayout]\">"._("Informazioni")."</a>";
		$link_tpl = "<a href=\"".$this->_home."?evt[".$this->_class_name."-manageLayout]&block=template\">"._("Template")."</a>";
		$link_skin = "<a href=\"".$this->_home."?evt[".$this->_class_name."-manageLayout]&block=skin\">"._("Skin")."</a>";
		$link_css = "<a href=\"".$this->_home."?evt[".$this->_class_name."-manageLayout]&block=css\">"._("CSS")."</a>";
		$link_view = "<a href=\"".$this->_home."?evt[".$this->_class_name."-manageLayout]&block=view\">"._("Viste")."</a>";
		$sel_link = $link_dft;

    if($block == 'template') {
      $GINO = $this->manageTemplate();
      $sel_link = $link_tpl;
    }
    elseif($block == 'skin') {
      $GINO = $this->manageSkin();
      $sel_link = $link_skin;
    }
    elseif($block == 'css') {
      $GINO = $this->manageCss();
      $sel_link = $link_css;
    }
    elseif($block == 'view') {
      $GINO = $this->manageView();
      $sel_link = $link_view;
    }
    else {
      $GINO = $this->info();
    }

		$view = new \Gino\View();
		$view->setViewTpl('tab');
		$dict = array(
			'title' => _('Layout'),
			'links' => array($link_view, $link_css, $link_skin, $link_tpl, $link_dft),
			'selected_link' => $sel_link,
			'content' => $GINO
		);
		return $view->render($dict);

	}

	private function manageTemplate() {
		
		$id = \Gino\cleanVar($_REQUEST, 'id', 'int', '');
		$tpl = \Gino\Loader::load('Template', array($id));

    if($this->_action == 'mngblocks') {
      echo $tpl->tplBlockForm(); 
      exit();
    }
    elseif($this->_action == 'mngtpl') {
        $css_id = \Gino\cleanVar($_POST, 'css', 'int', '');
        $css = \Gino\Loader::load('Css', array('layout', array('id'=>$css_id)));
        echo $tpl->manageTemplate($css, $id);
        exit();
    }
    elseif($this->_action == 'insert' || $this->_action == 'modify') {
      $free = \Gino\cleanVar($_GET, 'free', 'int', null);
      if($free) {
        $buffer = $tpl->formFreeTemplate();
      }
      else {
        $buffer = $tpl->formTemplate();
      }
    }
    elseif($this->_action == 'delete') {
      $buffer = $tpl->formDelTemplate();
    }
    elseif($this->_action == 'outline') {
      $buffer = $tpl->formOutline();
    }
    elseif($this->_action=='addblocks') {
      echo $tpl->addBlockForm();
      exit();
    }
    elseif($this->_action=='copy') {
      $buffer = $tpl->formCopyTemplate();
    }
    elseif($this->_action=='copytpl') {
      return $tpl->actionCopyTemplate();
    }
    else {
      $buffer = $this->templateList();
    }

    return $buffer;
  }

  private function manageSkin() {

    $id = \Gino\cleanVar($_REQUEST, 'id', 'int', '');
    $skin = \Gino\Loader::load('Skin', array($id));

    if($this->_action == 'insert' || $this->_action == 'modify') {
      $buffer = $skin->formSkin();
    }
    elseif($this->_action == 'sortup') {
      $skin->sortUp();
      \Gino\Link::HttpCall($this->_home, $this->_class_name.'-manageLayout', "block=skin");
    }
    elseif($this->_action == 'delete') {
      $buffer = $skin->formDelSkin();
    }
    else {
      $buffer = $this->skinList();
    }

    return $buffer;
  }

  private function manageCss() {

    $id = \Gino\cleanVar($_REQUEST, 'id', 'int', '');
    $css = \Gino\Loader::load('Css', array('layout', array('id' => $id)));

    if($this->_action == 'insert' || $this->_action == 'modify') {
      $buffer = $css->formCssLayout();
    }
    elseif($this->_action == 'delete') {
      $buffer = $css->formDelCssLayout();
    }
    elseif($this->_action == 'edit') {
      $fname = \Gino\cleanVar($_GET, 'fname', 'string', null);
      $buffer = $this->formFiles($fname, 'css');
    }
    else {
      $buffer = $this->cssList();
    }

    return $buffer;
  }

  private function manageView() {

    if($this->_action == 'edit') {
      $fname = \Gino\cleanVar($_GET, 'fname', 'string', null);
      $buffer = $this->formFiles($fname, 'view');
    }
    else {
      $buffer = $this->viewList();
    }

    return $buffer;
  }

	private function skinList() {

		\Gino\Loader::import('class', '\Gino\Template');
		\Gino\Loader::import('class', '\Gino\Css');

		$link_insert = "<a href=\"$this->_home?evt[$this->_class_name-manageLayout]&block=skin&action=insert\">".\Gino\pub::icon('insert', array('text' => _("nuova skin"), 'scale'=>2))."</a>";

		$skin_list = \Gino\Skin::getAll();
		if(count($skin_list)) {
			$view_table = new \Gino\View();
			$view_table->setViewTpl('table');
			$view_table->assign('heads', array(
			_('etichetta'),
			_('template'),
			_('css'),
			_('autenticazione'),
			_('cache'),
			''
			));
			$tbl_rows = array();

			$i = 0;
			foreach($skin_list as $skin) {
				$link_modify = "<a href=\"$this->_home?evt[$this->_class_name-manageLayout]&block=skin&id={$skin->id}&action=modify\">".\Gino\pub::icon('modify')."</a>";
				$link_delete = "<a href=\"$this->_home?evt[$this->_class_name-manageLayout]&block=skin&id={$skin->id}&action=delete\">".\Gino\pub::icon('delete')."</a>";
        $link_sort = $i ? "<a href=\"$this->_home?evt[$this->_class_name-manageLayout]&block=skin&id={$skin->id}&action=sortup\">".\Gino\pub::icon('sort-up')."</a>" : '';
        $tpl = new \Gino\Template($skin->template);
        $css = new \Gino\Css('layout', array('id' => $skin->css));
        $tbl_rows[] = array(
          $skin->ml('label'),
          $tpl->ml('label'),
          $css->label, // @TODO use css object
          $skin->auth == 'yes' ? _('si') : ($skin->auth == 'no' ? _('no') : _('si & no')),
          $skin->cache ? _('si') : _('no'),
          array('text' => implode(' &#160; ', array($link_modify, $link_delete, $link_sort)), 'class' => 'nowrap')
        );
				$i++;
			}	
			$view_table->assign('class', 'table table-striped', 'table-hover');
			$view_table->assign('rows', $tbl_rows);
			$buffer = "<p class=\"backoffice-info\">"._('Le skin sono elencate in ordine di priorità crescente. Per modificare le priorità agire sull\'icona a forma di freccia.')."</p>";
			$buffer .= $view_table->render();
		}
		else {
			$buffer = "<p>"._("Non risultano skin registrati")."</p>\n";
		}

		$view = new \Gino\View();
		$view->setViewTpl('section');
		$dict = array(
			'title' => _('Elenco skin'),
			'class' => 'admin',
			'header_links' => $link_insert,
			'content' => $buffer
		);
    
		return $view->render($dict);
	}
	
	private function templateList() {

		$link_insert = "<a href=\"$this->_home?evt[$this->_class_name-manageLayout]&block=template&action=insert\">".\Gino\pub::icon('insert', array('text' => _("nuovo template a blocchi"), 'scale'=>2))."</a>";
		$link_insert_free = "<a href=\"$this->_home?evt[$this->_class_name-manageLayout]&block=template&action=insert&free=1\">".\Gino\pub::icon('code', array('text' => _("nuovo template libero"), 'scale'=>2))."</a>";
	
		$tpl_list = \Gino\Template::getAll();
		if(count($tpl_list)) {
			$view_table = new \Gino\View();
			$view_table->setViewTpl('table');
			$view_table->assign('heads', array(
			_('etichetta'),
			_('file'),
			_('descrizione'),
			''
			));
			$tbl_rows = array();
			foreach($tpl_list as $tpl) {
				$link_modify = "<a href=\"$this->_home?evt[$this->_class_name-manageLayout]&block=template&id={$tpl->id}&action=modify&free=".$tpl->free."\">".\Gino\pub::icon('modify', array('text' => _("modifica il template")))."</a>";
				$link_outline = "<a href=\"$this->_home?evt[$this->_class_name-manageLayout]&block=template&id={$tpl->id}&action=outline\">".\Gino\pub::icon('layout', array('text' => _("modifica lo schema")))."</a>";
				$link_copy = "<a href=\"$this->_home?evt[$this->_class_name-manageLayout]&block=template&id={$tpl->id}&action=copy\">".\Gino\pub::icon('copy', array('text' => _("crea una copia")))."</a>";
				$link_delete = "<a href=\"$this->_home?evt[$this->_class_name-manageLayout]&block=template&id={$tpl->id}&action=delete\">".\Gino\pub::icon('delete')."</a>";
				
				$links = $tpl->free 
				? array($link_delete, $link_modify)
				: array($link_delete, $link_copy, $link_modify, $link_outline);
				
				$tbl_rows[] = array(
					$tpl->ml('label'),
					$tpl->filename,
					$tpl->ml('description'),
					implode(' &#160; ', $links)
				);
			}
			$view_table->assign('class', 'table table-striped', 'table-hover');
			$view_table->assign('rows', $tbl_rows);
			$buffer = $view_table->render();
		}
		else {
			$buffer = "<p>"._("Non risultano template registrati")."</p>\n";
		}
		
		$view = new \Gino\View();
		$view->setViewTpl('section');
		$dict = array(
		'title' => _('Elenco template'),
		'class' => 'admin',
		'header_links' => array($link_insert, $link_insert_free),
		'content' => $buffer
		);
    
		return $view->render($dict);
	}

	private function cssList() {
	
		$link_insert = "<a href=\"$this->_home?evt[$this->_class_name-manageLayout]&block=css&action=insert\">".pub::icon('insert', array('text' => _("nuovo file css"), 'scale'=>2))."</a>";

		$view_table = new \Gino\View();
		$view_table->setViewTpl('table');
		$view_table->assign('heads', array(
			_('etichetta'),
			_('file'),
			_('descrizione'),
			''
		));
		$tbl_rows = array();

		$dir = CSS_DIR;
		$files = array();
		if(is_dir($dir)) {
			if($dh = opendir($dir)) {
				while (($file = readdir($dh)) !== false) {
					if($file != "." && $file != ".." && preg_match('#^[0-9a-zA-Z]+[0-9a-zA-Z_.\-]+\.css$#', $file)) {
						$files[] = $file;
					}
				}
				closedir($dh);
			}
		}

    foreach($files as $file) {
      $css = \Gino\Css::getFromFilename($file);
      $link_edit = "<a href=\"$this->_home?evt[$this->_class_name-manageLayout]&block=css&fname=$file&action=edit\">".\Gino\pub::icon('write', array('text' => _('modifica file')))."</a>";
      if($css and $css->id) {
        $link_modify = "<a href=\"$this->_home?evt[$this->_class_name-manageLayout]&block=css&id={$css->id}&action=modify\">".\Gino\pub::icon('modify')."</a>";
        $link_delete = "<a href=\"$this->_home?evt[$this->_class_name-manageLayout]&block=css&id={$css->id}&action=delete\">".\Gino\pub::icon('delete')."</a>";
        $tbl_rows[] = array(
          \Gino\htmlChars($css->ml('label')),
          $file,
          \Gino\htmlChars($css->ml('description')),
          implode(' &#160; ', array($link_edit, $link_modify, $link_delete))
        );
      }
      else {
        $tbl_rows[] = array(
          _('CSS di sistema'),
          $file,
          '',
          $link_edit
        );
      }
    }

		$view_table->assign('class', 'table table-striped', 'table-hover');
		$view_table->assign('rows', $tbl_rows);

		$buffer = "<div class=\"backoffice-info\">";
		$buffer .= "<p>"._('In questa sezione è possibile modificare fogli di stile di sistema (propri di gino), e fogli di stile custom, inseribili ed eliminabili da questa interfaccia. I fogli di stile di sistema non sono eliminabili in quanto inclusi automaticamente all\'interno del documento.')."</p>";
		$buffer .= "</div>";
		$buffer .= $view_table->render();

		$view = new \Gino\View();
		$view->setViewTpl('section');
		$dict = array(
			'title' => _('Elenco fogli di stile'),
			'class' => 'admin',
			'header_links' => $link_insert,
			'content' => $buffer
		);
    
		return $view->render($dict);
	}

  private function viewList() {
	
    $view_table = new \Gino\View();
    $view_table->setViewTpl('table');
    $view_table->assign('heads', array(
      _('file'),
      ''
    ));
    $tbl_rows = array();

    $dir = VIEWS_DIR;
    $files = array();
    if(is_dir($dir)) {
      if($dh = opendir($dir)) {
        while (($file = readdir($dh)) !== false) {
          if($file != "." && $file != ".." && preg_match('#^[0-9a-zA-Z]+[0-9a-zA-Z_.\-]+\.php$#', $file)) {
            $files[] = $file;
          }
        }
        closedir($dh);
      }
    }

    foreach($files as $file) {
      $link_edit = "<a href=\"$this->_home?evt[$this->_class_name-manageLayout]&block=view&fname=$file&action=edit\">".\Gino\pub::icon('write', array('text' => _('modifica file')))."</a>";
      $tbl_rows[] = array(
          $file,
          $link_edit
      );
    }

    $view_table->assign('class', 'table table-striped', 'table-hover');
    $view_table->assign('rows', $tbl_rows);

    $buffer = "<div class=\"backoffice-info\">";
    $buffer .= "<p>"._('Queste sono le viste generali utilizzate da tutto il sistema e da molti moduli, pertanto eventuali modifiche si ripercuoteranno sulla visualizzazione di molte parti del sito.')."</p>";
    $buffer .= "<p>"._('Per modificare la visualizzazione di viste appartenenti a singoli moduli accedere all\'apposita interfaccia nella sezione di amministrazione del modulo.')."</p>";
    $buffer .= "</div>";
    $buffer .= $view_table->render();

    $view = new \Gino\View();
    $view->setViewTpl('section');
    $dict = array(
      'title' => _('Elenco viste generali di sistema'),
      'class' => 'admin',
      'content' => $buffer
    );
    
    return $view->render($dict);
	}

	private function info() {

    $GINO = "<p>"._("In questa sezione è possibile gestire il layout del sito. Ad ogni request viene associata una skin, la quale caricherà il template associato ed eventualmente un foglio di stile. I passi da seguire per personalizzare il layout di una pagina o sezione del sito sono i seguenti:")."</p>";
    $GINO .= "<ul>";
    $GINO .= "<li>"._("Creare ed uploadare un foglio di stile se necessario")."</li>";
    $GINO .= "<li>"._("Creare un template a blocchi utilizzando il motore di <i>gino</i> (file .tpl) oppure un template libero (file .php)")."</li>";
    $GINO .= "<li>"._("Creare una skin alla quale associare il template ed eventualmente il foglio di stile. La skin viene poi associata alla pagina o alla sezione desiderata definendo url, espressioni regolari di url oppure variabili di sessione.")."</li>";
    $GINO .= "<li>"._("Settare la priorità della skin spostandola in alto o in basso.")."</li>";
    $GINO .= "</ul>";
    $GINO .= \Gino\Css::layoutInfo();
    $GINO .= \Gino\Template::layoutInfo();
    $GINO .= \Gino\Skin::layoutInfo();
    $GINO .= "<h2>"._('Viste')."</h2>";
    $GINO .= "<p>"._('In questa sezione si possono modificare le viste di sistema di gino. Sono viste generali utilizzate da buona parte dei moduli e dalla stessa area amministrativa.')."</p>";

    $view = new \Gino\View();
    $view->setViewTpl('section');
    $dict = array(
      'title' => _('Layout'),
      'class' => 'admin',
      'content' => $GINO
    );
    
    return $view->render($dict);
	}
	
	public function actionSkin() {
		
		$this->requirePerm('can_admin');

		$id = \Gino\cleanVar($_POST, 'id', 'int', '');
		$skin = new \Gino\Skin($id);
		$skin->actionSkin();

		exit();
	}
	
	public function actionDelSkin() {
		
		$this->requirePerm('can_admin');

		$id = \Gino\cleanVar($_POST, 'id', 'int', '');
		$skin = new \Gino\Skin($id);
		$skin->actionDelSkin();

		exit();
	}
	
	public function actionCss() {
		
		$this->requirePerm('can_admin');

		$id = \Gino\cleanVar($_POST, 'id', 'int', '');
		$css = new \Gino\Css('layout', array('id'=>$id));
		$css->actionCssLayout();

		exit();
	}
	
	public function actionDelCss() {
		
		$this->requirePerm('can_admin');

		$id = \Gino\cleanVar($_POST, 'id', 'int', '');
		$css = new \Gino\Css('layout', array('id'=>$id));
		$css->actionDelCssLayout();

		exit();
	}
	
	public function actionTemplate() {

		$this->requirePerm('can_admin');

		$id = \Gino\cleanVar($_POST, 'id', 'int', '');
		$free = \Gino\cleanVar($_POST, 'free', 'int', '');
		
		$tpl = new \Gino\Template($id);
		if($free) {
			$tpl->actionFreeTemplate();
		}
		else {
			$tpl->actionTemplate();
		}

		exit();
	}

	public function actionDelTemplate() {
		
		$this->requirePerm('can_admin');

		$id = \Gino\cleanVar($_POST, 'id', 'int', '');
		$tpl = new \Gino\Template($id);
		$tpl->actionDelTemplate();

		exit();
	}

  /**
	 * Elenco dei moduli e delle pagine disponibili
	 * 
	 * @see page::getUrlPage()
	 * @return string
	 */
	public function modulesCodeList() {

		$this->requirePerm('can_admin');

		\Gino\Loader::import('page', '\Gino\App\Page\PageEntry');
		\Gino\Loader::import('auth', '\Gino\App\Auth\Permission');
		\Gino\Loader::import('module', 'Gino\App\Module\ModuleInstance');
		\Gino\Loader::import('sysClass', '\Gino\App\SysClass\ModuleApp');

		$view_table = new \Gino\View();
		$view_table->setViewTpl('table');
		$view_table->assign('class', 'table table-striped table-hover table-bordered table-layout');
		$tbl_rows = array();

		/*
		 * Pages
		 */
		$tbl_rows[] = array(
			array('text' => _('Pagine'), 'colspan'=>3, 'class'=>'header', 'header'=>true)
		);
		$tbl_rows[] = array(
			array('text' =>_('titolo'), 'header' => true),
			array('text' =>_('vista'), 'header' => true),
			array('text' =>_('permessi'), 'header' => true),
			array('text' =>_('codice'), 'header' => true)
		);

		$pages = \Gino\App\Page\PageEntry::get(array('where' => "published='1'", 'order' => 'title'));
		if(count($pages)) {
			foreach($pages as $page) {
				$access_txt = '';
				if($page->private)
				{
					$perm = $page->getController()->permissions();
					$access_txt .= $page->getController()->$perm['can_view_private']."<br />";
				}
				if($page->users)
					$access_txt .= _("pagina limitata ad utenti selezionati");
				if(!$page->private and !$page->users) 
					$access_txt .= _('pubblica');
				
				$code_full = "{module pageid=".$page->id." func=full}";
				$url = $page->getIdUrl(true);
				$tbl_rows[] = array(
					\Gino\htmlChars($page->ml('title')),
					_("Pagina completa"),
					$access_txt,
					$code_full
				);
			}
		}
    
		/*
		 * Modules sys_module
     */
    $tbl_rows[] = array(
      array('text' => _('Istanze di moduli'), 'colspan'=>3, 'class'=>'header', 'header'=>true)
    );
    $tbl_rows[] = array(
      array('text' =>_('nome'), 'header' => true),
      array('text' =>_('vista'), 'header' => true),
      array('text' =>_('permessi'), 'header' => true),
      array('text' =>_('codice'), 'header' => true)
    );

		$modules = \Gino\App\Module\ModuleInstance::get(array('where' => "active='1'", 'order' => 'label'));
		if(count($modules)) {
			foreach($modules as $module) {
				$class = $module->className();
				$output_functions = method_exists($class, 'outputFunctions') 
				? call_user_func(array($class, 'outputFunctions'))
				: array();
				
				if(count($output_functions)) {
					$first = true;
					foreach($output_functions as $func=>$data) {
						$permissions_code = $data['permissions'];
						$permissions = array();
						if($permissions_code and count($permissions_code)) {
							foreach($permissions_code as $permission_code) {
								$p = \Gino\App\Auth\Permission::getFromFullCode($permission_code);
								$permissions[] = $p->label;
							}
						}
						$code = "{module classid=".$module->id." func=".$func."}";
						$row = array(
							$data['label'],
							count($permissions) ? implode(', ', $permissions) : _('pubblico'),
							$code
						);
						if($first) {
							$tbl_rows[] = array_merge(array(array('text' => \Gino\htmlChars($module->label), 'rowspan' => count($output_functions))), $row);
							$first = false;
						}
						else {
							$tbl_rows[] = $row;
						}
					}
				}
			}
		}

		/*
		 * Modules sys_module_app
		 */
    $tbl_rows[] = array(
      array('text' => _('Moduli di sistema'), 'colspan'=>3, 'class'=>'header', 'header'=>true)
    );
    $tbl_rows[] = array(
      array('text' =>_('nome'), 'header' => true),
      array('text' =>_('vista'), 'header' => true),
      array('text' =>_('permessi'), 'header' => true),
      array('text' =>_('codice'), 'header' => true)
    );

		$modules_app = \Gino\App\SysClass\ModuleApp::get(array('where' => "instantiable='0' AND active='1'", 'order' => 'label'));
		if(count($modules_app)) {
			foreach($modules_app as $module_app) {
				$class = $module_app->className();
				$output_functions = method_exists($class, 'outputFunctions') 
				? call_user_func(array($class, 'outputFunctions'))
				: array();
				
				if(count($output_functions)) {
					$first = true;
					foreach($output_functions as $func=>$data) {
						$permissions_code = $data['permissions'];
						$permissions = array();
						if($permissions_code and count($permissions_code)) {
							foreach($permissions_code as $permission_code) {
								$p = \Gino\App\Auth\Permission::getFromFullCode($permission_code);
								$permissions[] = $p->label;
							}
						}
						$code = "{module sysclassid=".$module_app->id." func=".$func."}";
						$row = array(
							$data['label'],
							count($permissions) ? implode(', ', $permissions) : _('pubblico'),
							$code
						);
						if($first) {
							$tbl_rows[] = array_merge(array(array('text' => htmlChars($module_app->label), 'rowspan' => count($output_functions))), $row);
							$first = false;
						}
						else {
							$tbl_rows[] = $row;
						}
					}	
				}
			}
		}

		/*
		 * Url module
		 */
		$tbl_rows[] = array(
			array('text' => _('Moduli segnaposto'), 'colspan'=>3, 'class'=>'header', 'header'=>true)
		);
		$tbl_rows[] = array(
			array('text' =>_('nome'), 'colspan'=>2, 'header' => true),
			array('text' =>_('permessi'), 'header' => true),
			array('text' =>_('codice'), 'header' => true)
		);
		$code = "{module id=0}";
		$tbl_rows[] = array(
			array(
				'text' => _("Modulo da url"),
				'colspan' => 2
			),
			_("Prende i permessi del modulo chiamato"),
			$code
		);

		$buffer = "<div>";
		$view_table->assign('rows', $tbl_rows);
		$buffer .= $view_table->render();
		$buffer .= "</div>";

		return $buffer;
	}


	/**
	 * Elenco dei moduli e delle pagine disponibili
	 * 
	 * @see page::getUrlPage()
	 * @return string
	 */
	public function modulesList() {

		$this->requirePerm('can_admin');

		\Gino\Loader::import('page', 'PageEntry');
		\Gino\Loader::import('auth', 'Permission');
		\Gino\Loader::import('module', 'ModuleInstance');
		\Gino\Loader::import('sysClass', 'ModuleApp');

		$nav_id = \Gino\cleanVar($_GET, 'nav_id', 'string', '');
		$refillable_id = \Gino\cleanVar($_GET, 'refillable_id', 'string', '');
		$fill_id = \Gino\cleanVar($_GET, 'fill_id', 'string', '');

		$view_table = new \Gino\View();
		$view_table->setViewTpl('table');
		$view_table->assign('class', 'table table-striped table-hover table-bordered table-layout');
		$tbl_rows = array();

		/*
		 * Pages
		 */
		$tbl_rows[] = array(
			array('text' => _('Pagine'), 'colspan'=>3, 'class'=>'header', 'header'=>true)
		);
		$tbl_rows[] = array(
			array('text' =>_('titolo'), 'header' => true),
			array('text' =>_('vista'), 'header' => true),
			array('text' =>_('permessi'), 'header' => true)
		);

		$pages = \Gino\App\Page\PageEntry::get(array('where' => "published='1'", 'order' => 'title'));
		if(count($pages)) {
			foreach($pages as $page) {
				$access_txt = '';
				if($page->private)
				{
					$perm = $page->getController()->permissions();
					$access_txt .= $perm['can_view_private']."<br />";
				}
				if($page->users)
					$access_txt .= _("pagina limitata ad utenti selezionati");
				if(!$page->private and !$page->users)
					$access_txt .= _('pubblica');
				
				$code_full = "{module pageid=".$page->id." func=full}";
				
				$url = $page->getIdUrl(true);
				$tbl_rows[] = array(
					\Gino\htmlChars($page->ml('title')),
					"<span class=\"link\" onclick=\"gino.ajaxRequest('post', '$url', '', '".$fill_id."', {'script':true});closeAll('$nav_id', '$refillable_id', '".\Gino\jsVar(\Gino\htmlChars($page->title))."', '$code_full')\";>"._("Pagina completa")."</span>",
					$access_txt
				);
			}
		}
    
		/*
		 * Modules sys_module
		 */
    $tbl_rows[] = array(
      array('text' => _('Istanze di moduli'), 'colspan'=>3, 'class'=>'header', 'header'=>true)
    );
    $tbl_rows[] = array(
      array('text' =>_('nome'), 'header' => true),
      array('text' =>_('vista'), 'header' => true),
      array('text' =>_('permessi'), 'header' => true)
    );

		$modules = \Gino\App\Module\ModuleInstance::get(array('where' => "active='1'", 'order' => 'label'));
		if(count($modules)) {
			foreach($modules as $module) {
				$class = $module->className();
				$output_functions = method_exists($class, 'outputFunctions') 
				? call_user_func(array($class, 'outputFunctions'))
				: array();
				
				if(count($output_functions)) {
					$first = true;
					foreach($output_functions as $func=>$data) {
						$permissions_code = $data['permissions'];
						$permissions = array();
						if($permissions_code and count($permissions_code)) {
							foreach($permissions_code as $permission_code) {
								$p = \Gino\App\Auth\Permission::getFromFullCode($permission_code);
								$permissions[] = $p->label;
							}
						}
						$code = "{module classid=".$module->id." func=".$func."}";
						
						$row = array(
							"<span class=\"link\" onclick=\"gino.ajaxRequest('post', '$this->_home?pt[".$module->name."-$func]', '', '".$fill_id."', {'script':true});closeAll('$nav_id', '$refillable_id', '".\Gino\htmlChars($module->label)." - ".\Gino\jsVar($data['label'])."', '$code')\";>{$data['label']}</span>",
							count($permissions) ? implode(', ', $permissions) : _('pubblico')
						);
						if($first) {
							$tbl_rows[] = array_merge(array(array('text' => htmlChars($module->label), 'rowspan' => count($output_functions))), $row);
							$first = false;
						}
						else {
							$tbl_rows[] = $row;
						}
					}
				}
			}
		}

		/*
		 * Modules sys_module_app
		 */
    $tbl_rows[] = array(
      array('text' => _('Moduli di sistema'), 'colspan'=>3, 'class'=>'header', 'header'=>true)
    );
    $tbl_rows[] = array(
      array('text' =>_('nome'), 'header' => true),
      array('text' =>_('vista'), 'header' => true),
      array('text' =>_('permessi'), 'header' => true)
    );

		$modules_app = \Gino\App\SysClass\ModuleApp::get(array('where' => "instantiable='0' AND active='1'", 'order' => 'label'));
		if(count($modules_app)) {
			foreach($modules_app as $module_app) {
				$class = $module_app->className();
				$output_functions = method_exists($class, 'outputFunctions') 
				? call_user_func(array($class, 'outputFunctions'))
				: array();
				
				if(count($output_functions)) {
					$first = true;
					foreach($output_functions as $func=>$data) {
						$permissions_code = $data['permissions'];
						$permissions = array();
						if($permissions_code and count($permissions_code)) {
							foreach($permissions_code as $permission_code) {
								$p = \Gino\App\Auth\Permission::getFromFullCode($permission_code);
								$permissions[] = $p->label;
							}
						}
						$code = "{module sysclassid=".$module_app->id." func=".$func."}";
						
						$row = array(
							"<span class=\"link\" onclick=\"gino.ajaxRequest('post', '$this->_home?pt[".$module_app->name."-$func]', '', '".$fill_id."', {'script':true});closeAll('$nav_id', '$refillable_id', '".\Gino\htmlChars($module_app->label)." - ".\Gino\jsVar($data['label'])."', '$code')\";>{$data['label']}</span>",
							count($permissions) ? implode(', ', $permissions) : _('pubblico')
						);
						if($first) {
							$tbl_rows[] = array_merge(array(array('text' => \Gino\htmlChars($module_app->label), 'rowspan' => count($output_functions))), $row);
							$first = false;
						}
						else {
							$tbl_rows[] = $row;
						}
					}
				}
			}
		}

		/*
		 * Url module
     */
    $tbl_rows[] = array(
      array('text' => _('Moduli segnaposto'), 'colspan'=>3, 'class'=>'header', 'header'=>true)
    );
    $tbl_rows[] = array(
      array('text' =>_('nome'), 'colspan'=>2, 'header' => true),
      array('text' =>_('permessi'), 'header' => true)
    );
		$code = "{module id=0}";
    $tbl_rows[] = array(
      array(
        'text' => "<span class=\"link mdlTitle\" onclick=\"closeAll('$nav_id', '$refillable_id', '"._("Modulo da url")."', '$code')\";>"._("Modulo da url")."</span>",
        'colspan' => 2
      ),
      _("Prende i permessi del modulo chiamato")
    );

		$buffer = "<div>";
		$view_table->assign('rows', $tbl_rows);
		$buffer .= $view_table->render();
		$buffer .= "</div>";

		return $buffer;
	}
	
	private function formFiles($filename, $code) {

		$this->_registry->addJs(SITE_JS."/CodeMirror/codemirror.js");
		$this->_registry->addCss(CSS_WWW."/codemirror.css");
		
		if($code == 'css')
		{
		  $this->_registry->addJs(SITE_JS."/CodeMirror/css.js");
			$title = sprintf(_("Modifica il foglio di stile \"%s\""), $filename);
			$dir = CSS_DIR;
			$block = "css";
      $options = "{
        lineNumbers: true,
        matchBrackets: true,
        indentUnit: 4,
        indentWithTabs: true,
        enterMode: \"keep\",
        tabMode: \"shift\"
      }";
		}
		elseif($code == 'view')
		{
		  $this->_registry->addJs(SITE_JS."/CodeMirror/htmlmixed.js");
		  $this->_registry->addJs(SITE_JS."/CodeMirror/matchbrackets.js");
		  $this->_registry->addJs(SITE_JS."/CodeMirror/css.js");
		  $this->_registry->addJs(SITE_JS."/CodeMirror/xml.js");
		  $this->_registry->addJs(SITE_JS."/CodeMirror/clike.js");
		  $this->_registry->addJs(SITE_JS."/CodeMirror/php.js");
			$title = sprintf(_("Modifica la vista \"%s\""), $filename);
			$dir = VIEWS_DIR;
      $block = "view";
      $options = "{
        lineNumbers: true,
        matchBrackets: true,
        mode: \"application/x-httpd-php\",
        indentUnit: 4,
        indentWithTabs: true,
        enterMode: \"keep\",
        tabMode: \"shift\"
      }";
		}
		
		$buffer = '';
		$pathToFile = $dir.OS.$filename;
		$action = 'modify';
		$link_return = $this->_home."?evt[$this->_class_name-manageLayout]&block=$block";
		
		if(is_file($pathToFile))
		{
			$gform = \Gino\Loader::load('Form', array('gform', 'post', true, array("tblLayout"=>false)));
			$gform->load('dataform');
			$buffer = $gform->open($this->_home."?evt[$this->_class_name-actionFiles]", '', '');
			$buffer .= $gform->hidden('fname', $filename);
			$buffer .= $gform->hidden('code', $code);
			$buffer .= $gform->hidden('action', $action);

			$contents = file_get_contents($pathToFile);
			$buffer .= "<div class=\"form-row\">";
			$buffer .= "<textarea id=\"codemirror\" class=\"form-no-check\" name=\"file_content\" style=\"width:98%; padding-top: 10px; padding-left: 10px; height:580px;overflow:auto;\">".$contents."</textarea>\n";
			$buffer .= "</div>";
			
			$buffer .= "<div class=\"form-row\">";
			$buffer .= $gform->input('submit_action', 'submit', _("salva"), array("classField"=>"submit"));
			$buffer .= " ".$gform->input('cancel_action', 'button', _("annulla"), array("js"=>"onclick=\"location.href='$link_return'\" class=\"generic\""));
			$buffer .= "</div>";
			
			$buffer .= "<script>var myCodeMirror = CodeMirror.fromTextArea(document.getElementById('codemirror'), $options);</script>";

			$buffer .= $gform->close();
		}
		
		$view = new \Gino\View();
		$view->setViewTpl('section');
		$dict = array(
		'title' => $title,
		'class' => 'admin',
		'content' => $buffer
		);

		return $view->render($dict);
	}
	
	/**
	 * Salva il file del frontend
	 */
	public function actionFiles() {

		$this->requirePerm('can_admin');
	
		$action = \Gino\cleanVar($_POST, 'action', 'string', '');
		$filename = \Gino\cleanVar($_POST, 'fname', 'string', '');
		$code = \Gino\cleanVar($_POST, 'code', 'string', '');

		if($code == 'css')
		{
			$dir = CSS_DIR;
			$block = "css";
		}
		elseif($code == 'view')
		{
			$dir = VIEWS_DIR;
			$block = "view";
		}
		
		if(is_file($dir.OS.$filename))
		{
			$file_content = $_POST['file_content'];
			if($fo = fopen($dir.OS.$filename, 'wb'))
			{
				fwrite($fo, $file_content);
				fclose($fo);
			}
		}

		Link::HttpCall($this->_home, $this->_class_name.'-manageLayout', "block=$block");
	}
}
?>
