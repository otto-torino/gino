<?php
/**
 * @file class_searchSite.php
 * @brief Contiene la classe searchSite
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino\App\SearchSite;

/**
 * @brief Gestisce le ricerche full text sui contenuti dell'applicazione
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class searchSite extends \Gino\Controller {

    public $_optionsLabels;
    protected $_view_dir;
    private $_options,
            $_sys_mdl,
            $_inst_mdl,
            $_title;

    /**
     * @brief Costruttore
     *
     * @return istanza di searchSite
     */
    function __construct() {

        parent::__construct();

        $this->_title = _("Ricerca nel sito");
        $this->_sys_mdl = $this->setOption('sys_mdl', '');
        $this->_inst_mdl = $this->setOption('inst_mdl', '');

        $this->_options = \Gino\Loader::load('Options', array($this->_class_name, $this->_instance));
        $this->_optionsLabels = array(
            "sys_mdl"=>array(
                "label"=>array(
                    _("Moduli di sistema"), 
                    _("Inserire gli ID dei moduli che si vogliono includere nella ricerca separati da virgole")
                ), 
                "required"=>false
            ),
            "inst_mdl"=>array(
                "label"=>array(
                    _("Moduli istanziabili"), 
                    _("Inserire gli ID dei moduli che si vogliono includere nella ricerca separati da virgole")
                ), 
                "required"=>false
            )
        );
        $this->_view_dir = dirname(__FILE__).OS.'views';

    }

    /**
     * Restituisce alcune proprietà della classe
     *
     * @static
     * @return lista delle proprietà utilizzate per la creazione di istanze di tipo pagina
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
     * Elenco dei metodi che possono essere richiamati dal menu e dal template
     * 
     * @return array
     */
    public static function outputFunctions() {

        $list = array(
            "form" => array("label"=>_("Visualizza il form di ricerca"), "permissions"=>array())
        );

        return $list;
    }

    /**
     * Interfaccia amministrativa per la gestione delle ricerche
     * 
     * @return string
     */
    public function manageSearchSite() {
    
        $this->requirePerm('can_admin');

        $block = \Gino\cleanVar($_REQUEST, 'block', 'string', '');
        
        $link_frontend = "<a href=\"".$this->_home."?evt[$this->_class_name-manageSearchSite]&block=frontend\">"._("Frontend")."</a>";
        $link_options = "<a href=\"".$this->_home."?evt[$this->_class_name-manageSearchSite]&block=options\">"._("Opzioni")."</a>";
        $link_dft = "<a href=\"".$this->_home."?evt[".$this->_class_name."-manageSearchSite]\">"._("Informazioni")."</a>";
        $sel_link = $link_dft;
        
        if($block == 'frontend') {
            $GINO = $this->manageFrontend();
            $sel_link = $link_frontend;
        }
        elseif($block == 'options') {
            $GINO = $this->manageOptions();		
            $sel_link = $link_options;
        }
        else {
            $GINO = $this->info();
        }

        $dict = array(
            'title' => $this->_title,
            'links' => array($link_frontend, $link_options, $link_dft),
            'selected_link' => $sel_link,
            'content' => $GINO
        );

        $view = new \Gino\View(null, 'tab');
        return $view->render($dict);
    }

    /**
     * @brief Form di ricerca
     * 
     * @return form di ricerca
     */
    public function form() {

        $registry = \Gino\registry::instance();
        $registry->addCss($this->_class_www."/searchSite.css");
        $registry->addJs($this->_class_www."/searchSite.js");

        $choices = ($this->_sys_mdl || $this->_inst_mdl) ? true : false;
        $check_options = $this->checkOptions();

        $view = new \Gino\View($this->_view_dir, 'form');
        $dict = array(
            'form_action' => $this->_home."?evt[".$this->_class_name."-results]",
            'choices' => $choices,
            'check_options' => $check_options
        );

        return $view->render($dict);
    }

    /**
     * @brief Pannello con le opzioni di ricerca
     * 
     * @return pannello
     */
    private function checkOptions() {
    
        $buffer = "<div id=\"search_site_check_options\" style=\"display:none; position:absolute;text-align:left;\">";
        $buffer .= "<div>";
        $buffer .= "<p class=\"lead\"><strong>"._("Ricerca solo in")."</strong></p>";

        $i=1;
        if($this->_sys_mdl)
        {
        foreach(explode(",", $this->_sys_mdl) as $smid) {
            $label = $this->_db->getFieldFromId(TBL_MODULE_APP, 'label', 'id', $smid);
            $buffer .= "<label for=\"sysmdl_$smid\"><input id=\"sysmdl_$smid\" type=\"checkbox\" name=\"sysmdl[]\" value=\"$smid\"> ".htmlChars($label)."</label>";
            if($i++%3==0) $buffer .= "<br />";
        }
        }
        if($this->_inst_mdl)
        {
        foreach(explode(",", $this->_inst_mdl) as $mid) {
            $label = $this->_db->getFieldFromId(TBL_MODULE, 'label', 'id', $mid);
            $buffer .= "<label for=\"mdl_$mid\"><input id=\"mdl_$mid\" type=\"checkbox\" name=\"instmdl[]\" value=\"$mid\"> ".htmlChars($label)."</label>";
            if($i++%3==0) $buffer .= "<br />";
        }
        }
        $buffer .= "</div>";
        $buffer .= "</div>";

        return $buffer;
    }

    /**
     * Stampa i risultati di una ricerca
     * 
     * La ricerca viene effettuata sui moduli nei quali sono stati definiti i metodi @a searchSite() e @a searchSiteResult()
     * 
     * @see search::getSearchResults()
     * @return string
     */
    public function results() {

        \Gino\Loader::import('class', '\Gino\Search');

        $keywords = \Gino\cleanVar($_POST, 'search_site', 'string', '');
        $keywords = \Gino\cutHtmlText($keywords, 500, '', true, false, true);
        $sysmdl = \Gino\cleanVar($_POST, 'sysmdl', 'array', '');
        $instmdl = \Gino\cleanVar($_POST, 'instmdl', 'array', '');

        $opt = (!count($sysmdl) && !count($instmdl)) ? false : true;
        $results = array();
        $buffer = '';

        foreach(explode(",", $this->_sys_mdl) as $smdlid) {
            if(!$opt || in_array($smdlid, $sysmdl)) {
                $classname = $this->_db->getFieldFromId(TBL_MODULE_APP, 'name', 'id', $smdlid);
                if(method_exists($classname, "searchSite")) {
                    $obj = new $classname();
                    $data = $obj->searchSite();
                    $searchObj = new \Gino\search($data['table']);
                    foreach($data['weight_clauses'] as $k=>$v) $data['weight_clauses'][$k]['value'] = $keywords;
                    $results[$classname] = $searchObj->getSearchResults(\Gino\db::instance(), $data['selected_fields'], $data['required_clauses'], $data['weight_clauses']);
                }
            }
        }
        foreach(explode(",", $this->_inst_mdl) as $mdlid) {
            if(!$opt || in_array($mdlid, $instmdl)) {
                $module = new \Gino\App\Module\ModuleInstance($mdlid);
                $instancename = $module->name;
                $classname = $module->className();
                if(method_exists($classname, "searchSite")) {
                    $obj = new $classname($mdlid);
                    $data = $obj->searchSite();
                    $searchObj = new \Gino\search($data['table']);
                    foreach($data['weight_clauses'] as $k=>$v) $data['weight_clauses'][$k]['value'] = $keywords;
                    $results[$classname."||".$mdlid] = $searchObj->getSearchResults(\Gino\db::instance(), $data['selected_fields'], $data['required_clauses'], $data['weight_clauses']);
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

        $title = _("Ricerca")." \"$keywords\"";
        $name_result = $tot_results == 1 ? _("risultato") : _("risultati");
        $results_num = "<span class=\"search-result-tot\">".$tot_results." ".$name_result."</span>";

        if($tot_results) {
            $buffer .= "<dl class=\"search-results\">";
            foreach($order_results as $k=>$point) {
                $fr = $final_results[$k];
                if(preg_match("#(.*?)\|\|(\d+)#", $fr['class'], $matches)) $obj = new $matches[1]($matches[2]);
                else $obj = new $fr['class']();
                $buffer .= $obj->searchSiteResult($fr);
            }
            $buffer .= "</dl>";
        }
        else $buffer .= "<p class=\"message\">"._("La ricerca non ha prodotto risultati")."</p>";

        $view = new \Gino\View($this->_view_dir, 'results');
        $dict = array(
            'title' => $title,
            'results_num' => $results_num,
            'content' => $buffer
        );

        return $view->render($dict);
    }

    public function info() {
    
        $buffer = "<p>"._("Il modulo mette a disposizione un'interfaccia di ricerca nel sito.")."</p>";
        $buffer .= "<p>"._("Nelle <b>Opzioni</b> è possibile indicare i valori ID dei moduli (di sistema e non) che si vogliono includere nella ricerca.")."</p>";
        $buffer .= "<p>"._("Per poter funzionare occorre")."</p>";
        $buffer .= "<ul>";
        $buffer .= "<li>"._("caricare sul database la funzione <b>replace_ci</b> (vedi INSTALL.TXT)")."</li>";
        $buffer .= "<li>"._("nei moduli indicati nella ricerca occorre definire e argomentare i metodi <b>searchSite</b> e <b>searchSiteResult</b>")."</li>";
        $buffer .= "</ul>";

        $view = new \Gino\View(null, 'section');
        $dict = array(
            'title' => _("Modulo di ricerca nel sito"),
            'class' => 'admin',
            'content' => $buffer
        );

        return $view->render($dict);
    }
}
