<?php
/**
 * @file class_instruments.php
 * @brief Contiene la classe instruments
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino\App\Instruments;

/**
 * @brief Strumenti aggiuntivi di gino
 * 
 * Adesso sono disponibili l'elenco delle risorse disponibili (con i relativi link) e dei mime type. \n
 * Per aggiungere uno strumento è necessario: \n
 *   - creare un record nella tabella @a instruments
 *   - associare nel metodo viewItem() il valore del campo id dello strumento con un suo metodo personalizzato (ad es. itemNew)
 *   - creare il metodo personalizzato (ad es. itemNew)
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class instruments extends \Gino\Controller {

	private $_title;
	private $_action, $_block;

	function __construct() {
	
		parent::__construct();

		$this->_title = _('Strumenti');
		
		$this->_block = \Gino\cleanVar($_REQUEST, 'block', 'string', '');
	}

	/**
	 * Interfaccia amministrativa alla gestione degli strumenti
	 * 
	 * @return string
	 */
	public function manageInstruments() {
	
		$this->requirePerm('can_admin');
		
		$link_mime = "<a href=\"".$this->_home."?evt[$this->_class_name-manageInstruments]&block=mime\">"._("Mime-Type")."</a>";
		$link_dft = "<a href=\"".$this->_home."?evt[".$this->_class_name."-manageInstruments]\">"._("Collegamenti")."</a>";
		$sel_link = $link_dft;

		if($this->_block == 'mime') {
			$GINO = $this->mimeTypes();
			$sel_link = $link_mime;
		}
		else {
			$GINO = $this->links();
		}
			
		$dict = array(
		'title' => $this->_title,
		'links' => array($link_mime, $link_dft),
		'selected_link' => $sel_link,
		'content' => $GINO
		);

		$view = new \Gino\View(null, 'tab');
		return $view->render($dict);
	}

	/**
	 * Strumento - mostra l'elenco delle risorse disponibili (con i relativi link)
	 * 
	 * @see page::getLinkPage()
	 * @return string
	 */
	private function links(){

		\Gino\Loader::import('page', '\Gino\App\Page\PageEntry');
		\Gino\Loader::import('module', '\Gino\App\Module\ModuleInstance');
		\Gino\Loader::import('sysClass', '\Gino\App\SysClass\ModuleApp');
		\Gino\Loader::import('auth', '\Gino\App\Auth\Permission');
		
		$GINO = "<p class=\"backoffice-info\">"._('Elenco di tutte le pagine presenti e di tutti gli output dei moduli, con relativi url e permessi di visualizzazione. Quelle elencate qui non sono le uniche viste disponibili dei moduli, ma quelle che non necessitano di parametri e sono quindi includibili in ogni layout.')."</p>";
		
		$rows = $this->_db->select('id', PageEntry::$tbl_entry, "published='1'", array('order' => 'title'));
		if($rows and count($rows))
		{
			$GINO .= "<h2>"._("Pagine")."</h2>";

			$view_table = new \Gino\View(null, 'table');
			$view_table->assign('heads', array(
				_('Titolo'),
				_('Url'),
				_('Permessi')
			));
			$tbl_rows = array();
			foreach($rows as $row) {
				$page = new \Gino\App\Page\PageEntry($row['id']);

				$access_txt = '';
				if($page->private)
					$access_txt .= _("pagina privata")."<br />";
				if($page->users)
					$access_txt .= _("pagina limitata ad utenti selezionati");

				$tbl_rows[] = array(
				$page->title,
				$page->getUrl(),
				$access_txt
				);
			}
			$view_table->assign('rows', $tbl_rows);
			$view_table->assign('class', 'table table-striped table-bordered table-hover');
			$GINO .= $view_table->render();
		}

		$modules = \Gino\App\Module\ModuleInstance::get();
		$modules = array_merge($modules, \Gino\App\SysClass\ModuleApp::get());
		
		if(count($modules)) {
			$GINO .= "<h2>"._("Moduli di sistema e istanze")."</h2>";

			$view_table = new \Gino\View(null, 'table');
			$view_table->assign('heads', array(
				_('Modulo'),
				_('Url'),
				_('Descrizione'),
				_('Permessi')
			));
			$tbl_rows = array();
			foreach($modules as $module) {
				$class_name = $module->className();
				if(method_exists($class_name, 'outputFunctions'))
				{
					$list = call_user_func(array($class_name, 'outputFunctions'));
					foreach($list as $func => $desc)
					{
						$description = $desc['label'];
						$permissions_code = $desc['permissions'];
						$permissions = array();
						if($permissions_code and count($permissions_code)) {
							foreach($permissions_code as $permission_code) {
								$p = \Gino\App\Auth\Permission::getFromFullCode($permission_code);
								$permissions[] = $p->label;
							}
						}
						$tbl_rows[] = array(
							\Gino\htmlChars($module->label),
							$this->_registry->plink->aLink($module->name, $func),
							$description,
							implode(', ', $permissions)
						);
					}
				}
			}
			$view_table->assign('rows', $tbl_rows);
			$view_table->assign('class', 'table table-striped table-bordered table-hover');
			$GINO .= $view_table->render();
		}
		
		$view = new \Gino\View(null, 'section');
		$dict = array(
		'title' => _('Collegamenti'),
		'class' => 'admin',
		'content' => $GINO
		);

		return $view->render($dict);
	}
	
	/**
	 * Strumento - mostra l'elenco dei mime type (include come iframe il file mime-type-table.html)
	 * 
	 * @return string
	 */
	private function mimeTypes(){
		
		$file = APP_DIR.OS.$this->_class_name.'/doc/mime-type-table.html';
		
		$GINO = '';
		if(is_file($file))
		{
			$src = $this->_registry->pub->getRootUrl().'/app/'.$this->_class_name.'/doc/mime-type-table.html';
			$GINO .= "<iframe src=\"$src\" frameborder=\"0\" width=\"100%\" height=\"500\"></iframe>";
		}
		
		$view = new \Gino\View(null, 'section');

		$dict = array(
		'title' => _('Mime-Type'),
		'class' => 'admin',
		'content' => $GINO
		);

		return $view->render($dict);
	}
}

?>
