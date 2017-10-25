<?php
/**
 * @file class_instruments.php
 * @brief Contiene la definizione ed implementazione della classe Gino.App.Instruments.instruments
 *
 * @copyright 2005-2017 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

/**
 * @namespace Gino.App.Instruments
 * @description Namespace dell'applicazione Instruments, per la visualizzazione di utilità quali percorsi e mime types
 */
namespace Gino\App\Instruments;

use \Gino\View;
use \Gino\Document;
use \Gino\App\Layout\layout;

/**
 * @brief Classe di tipo Gino.Controller per la gestione di strumenti aggiuntivi di gino
 *
 * Adesso sono disponibili l'elenco delle risorse disponibili (con i relativi link) e dei mime type. \n
 * Per aggiungere uno strumento è necessario: \n
 *   - creare un record nella tabella @a instruments
 *   - associare nel metodo viewItem() il valore del campo id dello strumento con un suo metodo personalizzato (ad es. itemNew)
 *   - creare il metodo personalizzato (ad es. itemNew)
 *
 * @copyright 2005-2017 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class instruments extends \Gino\Controller {

    /**
     * @brief Costruttore
     * @return Gino.App.Instruments.instruments
     */
    function __construct() {
        parent::__construct();
    }

    /**
     * @brief Interfaccia amministrativa alla gestione degli strumenti
     * @param \Gino\Http\Request $request istanza di Gino.App.Instruments.instruments
     * @return interfaccia di amministrazione
     */
    public function manageInstruments(\Gino\Http\Request $request) {

        $this->requirePerm('can_admin');

        $block = \Gino\cleanVar($request->GET, 'block', 'string', '');
        
        $link_mime = sprintf('<a href="%s">%s</a>', $this->linkAdmin(array(), 'block=mime'), _("Mime-Type"));
        $link_dft = sprintf('<a href="%s">%s</a>', $this->linkAdmin(), _('Collegamenti'));
        $sel_link = $link_dft;

        if($block == 'mime') {
            $content = $this->mimeTypes($request);
            $sel_link = $link_mime;
        }
        else {
            $content = $this->links();
        }

        $dict = array(
            'title' => _('Strumenti'),
            'links' => array($link_mime, $link_dft),
            'selected_link' => $sel_link,
            'content' => $content
        );

        $view = new View(null, 'tab');
        $document = new Document($view->render($dict));

        return $document();
    }

    /**
     * @brief Strumento - mostra l'elenco delle risorse disponibili (con i relativi link)
     *
     * @return html, elenco risporse con link
     */
    private function links(){

    	\Gino\Loader::import('page', 'PageEntry');
        \Gino\Loader::import('module', 'ModuleInstance');
        \Gino\Loader::import('sysClass', 'ModuleApp');
        \Gino\Loader::import('auth', 'Permission');
        
        $GINO = "<p class=\"backoffice-info\">"._('Elenco di tutte le pagine presenti e di tutti gli output dei moduli, con relativi url e permessi di visualizzazione. Quelle elencate qui non sono le uniche viste disponibili dei moduli, ma quelle che non necessitano di parametri e sono quindi includibili in ogni layout.')."</p>";

        $link = $this->link($this->_instance_name, 'manageInstruments');
        
        $GINO .= "<p class=\"right\"><a href=\"".$link."#pages\">"._("pagine")."</a> | <a href=\"".$link."#instances\">"._("istanze di moduli")."</a> | <a href=\"".$link."#modules\">"._("moduli di sistema")."</a></p>";
        
        $rows = $this->_db->select('id', \Gino\App\Page\PageEntry::$table, "published='1'", array('order' => 'title'));
        if($rows and count($rows))
        {
            $GINO .= "<a name=\"pages\"></a><h2>"._("Pagine")."</h2>";

            $view_table = new View(null, 'table');
            $view_table->assign('heads', array(
                _('Titolo'),
                _('Url'),
                _('Permessi'),
            	_('Codice template')
            ));
            $tbl_rows = array();
            foreach($rows as $row) {
                $page = new \Gino\App\Page\PageEntry($row['id']);

                $page_code = layout::getPageData($page);

                $tbl_rows[] = array(
                	$page->title,
                	$page_code['url'],
                	$page_code['perm'],
                	$page_code['code']
                );
            }
            $view_table->assign('rows', $tbl_rows);
            $view_table->assign('class', 'table table-striped table-bordered table-hover');
            $GINO .= $view_table->render();
        }

        $modules = \Gino\App\Module\ModuleInstance::objects(null, array('order' => 'label'));
        
        if(count($modules)) {
            $GINO .= "<a name=\"instances\"></a><h2>"._("Istanze di moduli")."</h2>";

            $view_table = new \Gino\View(null, 'table');
            $view_table->assign('heads', array(
                _('Modulo'),
                _('Url'),
                _('Descrizione'),
                _('Permessi'),
            	_('Codice template')
            ));
            $tbl_rows = array();
            foreach($modules as $module) {
                
            	$class = $module->classNameNs();
                
                if(method_exists($class, 'outputFunctions'))
                {
                    $list = call_user_func(array($class, 'outputFunctions'));
                    foreach($list as $method_name => $method_info)
                    {
                    	$method_code = layout::getMethodData($method_name, $method_info, $module);
                    	
                    	$tbl_rows[] = array(
                    		\Gino\htmlChars($module->label),
                    		$this->link($module->name, $method_name),
                    		$method_code['info'],
                    		$method_code['perm'],
                    		$method_code['code'],
                    	);
                    }
                }
            }
            $view_table->assign('rows', $tbl_rows);
            $view_table->assign('class', 'table table-striped table-bordered table-hover');
            $GINO .= $view_table->render();
        }
        
        $modules = \Gino\App\SysClass\ModuleApp::objects(null, array('where' => "instantiable='0'", 'order' => 'label'));
        
        if(count($modules)) {
        	$GINO .= "<a name=\"modules\"></a><h2>"._("Moduli di sistema")."</h2>";
        
        	$view_table = new \Gino\View(null, 'table');
        	$view_table->assign('heads', array(
        		_('Modulo'),
        		_('Url'),
        		_('Descrizione'),
        		_('Permessi'),
        		_('Codice template')
        	));
        	$tbl_rows = array();
        	foreach($modules as $module) {
        
        		$class = $module->classNameNs();
        
        		if(method_exists($class, 'outputFunctions'))
        		{
        			$list = call_user_func(array($class, 'outputFunctions'));
        			foreach($list as $method_name => $method_info)
        			{
        				$method_code = layout::getMethodData($method_name, $method_info, $module, true);
        				
        				$tbl_rows[] = array(
        					\Gino\htmlChars($module->label),
        					$this->link($module->name, $method_name),
        					$method_code['info'],
        					$method_code['perm'],
        					$method_code['code'],
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
     * @brief Strumento - mostra l'elenco dei mime type (include come iframe il file mime-type-table.html)
     * @param \Gino\Http\Request $request
     * @return html, elenco mime type
     */
    private function mimeTypes(\Gino\Http\Request $request){

        $file = APP_DIR.OS.$this->_class_name.'/doc/mime-type-table.html';

        $GINO = '';
        if(is_file($file))
        {
            $src = $request->root_absolute_url.'/app/'.$this->_class_name.'/doc/mime-type-table.html';
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
