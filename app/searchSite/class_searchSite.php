<?php
/**
 * @file class_searchSite.php
 * @brief Contiene la definizione ed implementazione della classe Gino.App.SearchSite.searchSite
 *
 * @copyright 2005-2017 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

/**
 * @namespace Gino.App.SearchSite
 * @description Namespace dell'applicazione SearchSite, che gestisce ricerche full text su tutti i moduli installati che supportano la ricerca
 */
namespace Gino\App\SearchSite;

use \Gino\Loader;
use \Gino\View;
use \Gino\Document;
use \Gino\App\SysClass\ModuleApp;

/**
 * @brief Gestisce le ricerche full text sui contenuti dell'applicazione
 * 
 * @copyright 2005-2017 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class searchSite extends \Gino\Controller {

    public $_optionsLabels;
    protected $_view_dir;
    private $_options,
            $_sys_mdl,
            $_inst_mdl,
            $_view_choices,
            $_title;

    /**
     * @brief @brief Costruttore
     *
     * @return istanza di Gino.App.SearchSite.searchSite
     */
    function __construct() {

        parent::__construct();

        $this->_title = _("Ricerca nel sito");
        $this->_sys_mdl = $this->setOption('sys_mdl', '');
        $this->_inst_mdl = $this->setOption('inst_mdl', '');
        $this->_view_choices = $this->setOption('view_choices', 0);

        $this->_options = Loader::load('Options', array($this));
        $this->_optionsLabels = array(
            "sys_mdl" => array(
                "label" => array(
                    _("Moduli di sistema"),
                    _("Inserire i valori ID dei moduli che si vogliono includere nella ricerca separati da virgole")
                ),
                "required" => FALSE, 
            	'trnsl' => false
            ),
            "inst_mdl"=>array(
                "label" => array(
                    _("Moduli istanziabili"),
                    _("Inserire i valori ID dei moduli che si vogliono includere nella ricerca separati da virgole")
                ),
                "required" => FALSE, 
            	'trnsl' => false
            ),
            "view_choices" => array(
                "label" => _("Visualizzare la scelta di ricerca sui singoli moduli"),
                "required" => true, 
            )
        );
        $this->_view_dir = dirname(__FILE__).OS.'views';

    }

    /**
     * @brief Restituisce alcune proprietà della classe
     * @return array associativo di proprietà utilizzate per la creazione di istanze di tipo pagina (tabelle, css, viste)
     */
    public static function getClassElements() {

        return array(
            "tables"=>array(
                'search_site_opt',
            ),
            'css' => array(
                'searchSite.css'
            ),
            'views' => array(
                'form.php' => _('Form di ricerca'),
                'results.php' => _('Risultati della ricerca')
            ),
        );
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
            "form" => array("label"=>_("Visualizza il form di ricerca"), "permissions"=>array())
        );

        return $list;
    }

    /**
     * @brief Interfaccia amministrativa per la gestione delle ricerche
     * @param \Gino\Http\Request istanza di Gino.Http.Request
     * @return Gino.Http.Response, interfaccia amministrativa
     */
    public function manageSearchSite(\Gino\Http\Request $request) {

        $this->requirePerm('can_admin');

        $block = \Gino\cleanVar($request->GET, 'block', 'string');

        $link_frontend = sprintf('<a href="%s">%s</a>', $this->linkAdmin(array(), 'block=frontend'), _('Frontend'));
        $link_options = sprintf('<a href="%s">%s</a>', $this->linkAdmin(array(), 'block=options'), _('Opzioni'));
        $link_dft = sprintf('<a href="%s">%s</a>', $this->linkAdmin(), _('Informazioni'));
        $sel_link = $link_dft;

        if($block == 'frontend') {
            $backend = $this->manageFrontend();
            $sel_link = $link_frontend;
        }
        elseif($block == 'options') {
            $backend = $this->manageOptions();
            $sel_link = $link_options;
        }
        else {
            $backend = $this->info();
        }

        if(is_a($backend, '\Gino\Http\Response')) {
            return $backend;
        }

        $view = new View();
        $view->setViewTpl('tab');
        $dict = array(
            'title' => $this->_title,
            'links' => array($link_frontend, $link_options, $link_dft),
            'selected_link' => $sel_link,
            'content' => $backend
        );

        $document = new Document($view->render($dict));
        return $document();
    }

    /**
     * @brief Form di ricerca
     * @return html, form di ricerca
     */
    public function form() {

        $registry = \Gino\registry::instance();
        $registry->addCss($this->_class_www."/searchSite.css");
        $registry->addJs($this->_class_www."/searchSite.js");

        $choices = !!($this->_sys_mdl || $this->_inst_mdl);
        if(!$this->_view_choices) {
        	$choices = false;
        }
        $check_options = $this->checkOptions();

        $view = new View($this->_view_dir, 'form');
        $dict = array(
            'form_action' => $this->link($this->_class_name, 'results'),
            'choices' => $choices,
            'check_options' => $check_options
        );

        return $view->render($dict);
    }

    /**
     * @brief Pannello con le opzioni di ricerca
     * @return html, pannello
     */
    private function checkOptions() {

        $buffer = "<div id=\"search_site_check_options\">";
        $buffer .= "<div>";
        $buffer .= "<p class=\"heading\">"._("Ricerca solo in")."</p>";

        $i=1;
        if($this->_sys_mdl)
        {
        	foreach(explode(",", $this->_sys_mdl) as $smid) {
            	$label = $this->_db->getFieldFromId(TBL_MODULE_APP, 'label', 'id', $smid);
            	$buffer .= "<label for=\"sysmdl_$smid\"><input id=\"sysmdl_$smid\" type=\"checkbox\" name=\"sysmdl[]\" value=\"$smid\"> ".\Gino\htmlChars($label)."</label>";
            	if($i++%3==0) $buffer .= "<br />";
        	}
        }
        if($this->_inst_mdl)
        {
        	foreach(explode(",", $this->_inst_mdl) as $mid) {
           		$label = $this->_db->getFieldFromId(TBL_MODULE, 'label', 'id', $mid);
            	$buffer .= "<label for=\"mdl_$mid\"><input id=\"mdl_$mid\" type=\"checkbox\" name=\"instmdl[]\" value=\"$mid\"> ".\Gino\htmlChars($label)."</label>";
            	if($i++%3==0) $buffer .= "<br />";
        	}
        }
        $buffer .= "</div>";
        $buffer .= "</div>";

        return $buffer;
    }

    /**
     * @brief Stampa i risultati di una ricerca
     *
     * La ricerca viene effettuata sui moduli nei quali sono stati definiti i metodi @a searchSite() e @a searchSiteResult()
     *
     * @see Gino.Search::getSearchResults()
     * @param \Gino\Http\Request $request istanza di Gino.Http.Request
     * @return Gino.Http.Response
     */
    public function results(\Gino\Http\Request $request) {

        Loader::import('class', '\Gino\Search');

        $keywords = \htmlspecialchars(\Gino\cleanVar($request->POST, 'search_site', 'string', ''));
        $keywords = \Gino\cutHtmlText($keywords, 500, '', true, false, true);
        
        if($keywords) {
        	$results = $this->search($keywords, $request);
        	
        	$title = _("Ricerca")." \"$keywords\"";
        	$buffer = $results['results'];
        	$tot_results = $results['tot_results'];
        	
        	$name_result = $tot_results == 1 ? _("risultato") : _("risultati");
        	$results_num = "<span class=\"search-result-tot\">".$tot_results." ".$name_result."</span>";
        }
        else {
        	$title = _("Ricerca");
        	$buffer = "<p class=\"message\">"._("Inserire una chiave di ricerca")."</p>";
        	$results_num = null;
        }
        
        $view = new View($this->_view_dir, 'results');
        $dict = array(
            'title' => $title,
            'results_num' => $results_num,
            'content' => $buffer
        );

        $document = new \Gino\Document($view->render($dict));
        return $document();
    }
    
    /**
     * @brief Effettua la ricerca
     * 
     * @param mixed $keywords
     * @param \Gino\Http\Request $request istanza di Gino.Http.Request
     * @return array('tot_results' => integer, 'results' => string)
     */
    private function search($keywords, $request) {
    	
    	$sysmdl = \Gino\cleanVar($request->POST, 'sysmdl', 'array');
    	$instmdl = \Gino\cleanVar($request->POST, 'instmdl', 'array');
    	
    	if(is_null($sysmdl)) $sysmdl = array();
    	if(is_null($instmdl)) $instmdl = array();
    	
    	$opt = !!(count($sysmdl) or count($instmdl));
    	$results = array();
    	$buffer = '';
    	
    	foreach(explode(",", $this->_sys_mdl) as $smdlid) {
    		if(!$opt || in_array($smdlid, $sysmdl)) {
    			
    			if($smdlid)
    			{
    				$module_app = new ModuleApp($smdlid);
    				$class = $module_app->classNameNs();
    				if(method_exists($class, "searchSite")) {
    					$obj = new $class();
    					$data = $obj->searchSite();
    					$searchObj = new \Gino\Search($data['table']);
    					foreach($data['weight_clauses'] as $k=>$v) $data['weight_clauses'][$k]['value'] = $keywords;
    					$results[$class] = $searchObj->getSearchResults(\Gino\Db::instance(), $data['selected_fields'], $data['required_clauses'], $data['weight_clauses']);
    				}
    			}
    		}
    	}
    	foreach(explode(",", $this->_inst_mdl) as $mdlid) {
    		if(!$opt || in_array($mdlid, $instmdl)) {
    			
    			if($mdlid)
    			{
    				$module = new \Gino\App\Module\ModuleInstance($mdlid);
    				$instancename = $module->name;
    				$class = $module->classNameNs();
    				if(method_exists($class, "searchSite")) {
    					$obj = new $class($mdlid);
    					$data = $obj->searchSite();
    					$searchObj = new \Gino\Search($data['table']);
    					foreach($data['weight_clauses'] as $k=>$v) $data['weight_clauses'][$k]['value'] = $keywords;
    					$results[$class."||".$mdlid] = $searchObj->getSearchResults(\Gino\Db::instance(), $data['selected_fields'], $data['required_clauses'], $data['weight_clauses']);
    				}
    			}
    		}
    	}
    	
    	$order_results = array();
    	$final_results = array();
    	
    	if(count($results) > 0)
    	{
    		$i = 0;
    		foreach($results as $classname=>$res) {
    			foreach($res as $k=>$v) {
    				$order_results[$i] = $v['relevance']*1000 + round($v['occurrences']);
    				$final_results[$i] = array_merge(array("class"=>$classname), $v);
    				$i++;
    			}
    		}
    		
    		arsort($order_results);
    	}
    	$tot_results = count($final_results);
    	
    	if($tot_results) {
    		foreach($order_results as $k=>$point) {
    			$fr = $final_results[$k];
    			if(preg_match("#(.*?)\|\|(\d+)#", $fr['class'], $matches)) {
    				$obj = new $matches[1]($matches[2]);
    			}
    			else {
    				$obj = new $fr['class']();
    			}
    			
    			$buffer .= "<div class=\"search-item\">";
    			$buffer .= $obj->searchSiteResult($fr);
    			$buffer .= "</div>";
    		}
    	}
    	else {
    		$buffer .= "<p class=\"message\">"._("La ricerca non ha prodotto risultati")."</p>";
    	}
    	
    	return array('tot_results' => $tot_results, 'results' => $buffer);
    }

    /**
     * @brief Informazioni modulo
     * @return html, informazioni
     */
    public function info() {

        $buffer = "<p>"._("Il modulo mette a disposizione una interfaccia di ricerca nel sito.")."</p>";
        $buffer .= "<p>"._("Nelle <b>Opzioni</b> è possibile indicare i valori ID dei moduli (di sistema e non) che si vogliono includere nella ricerca.")."</p>";
        $buffer .= "<p>"._("Per poter funzionare occorre")."</p>";
        $buffer .= "<ul>";
        $buffer .= "<li>"._("caricare sul database la funzione <b>replace_ci</b> (vedi INSTALL.TXT)")."</li>";
        $buffer .= "<li>"._("nei moduli indicati nella ricerca occorre definire e argomentare i metodi <b>searchSite</b> e <b>searchSiteResult</b>")."</li>";
        $buffer .= "</ul>";

        $view = new View(null, 'section');
        $dict = array(
            'title' => _("Modulo di ricerca nel sito"),
            'class' => 'admin',
            'content' => $buffer
        );

        return $view->render($dict);
    }
}
