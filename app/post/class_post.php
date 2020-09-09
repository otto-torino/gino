<?php
/**
 * @file class_post.php
 * @brief Contiene la definizione ed implementazione della classe Gino.App.Post.post
 * @version 3.0.0
 */

/**
 * @namespace Gino.App.Post
 * @description Namespace dell'applicazione Post
 */
namespace Gino\App\Post;

use \Gino\Registry;
use \Gino\Loader;
use \Gino\Error;
use \Gino\View;
use \Gino\GTag;
use \Gino\Options;
use \Gino\AdminTable;
use \Gino\App\Module\ModuleInstance;

require_once('class.Item.php');
require_once('class.Category.php');

/**
 * @defgroup post
 * Modulo di gestione dei post
 *
 * Il modulo contiene anche dei css, javascript e file di configurazione.
 */

/**
 * \ingroup post
 * @brief Classe di tipo Gino.Controller per la gestione di post categorizzati
 * 
 * ##CONFIGURATION
 * In INSTALLED_APPS aggiungere:
 * '{instance_name}' => 'bullhorn',
 * 
 * ##OUTPUTS
 * - @a last: ultimi post pubblicati, numero da opzioni (template)
 * - @a archive: archivio post paginato (vista)
 * - @a showcase: vetrina post (template)
 * - @a detail: vista singolo post (vista)
 * - @a feedRSS: feed RSS (vista)
 * 
 * ###last
 * Da opzioni è possibile attivare la visualizzazione dello slideshow; in questo caso è necessario impostare dei post da mostrare nello slideshow.
 * 
 * ##PERMESSI
 * L'applicazione prevede tre livelli di permesso e uno di visualizzazione: \n
 * - can_admin: può modificare i file di frontend (viste e fogli di stile) e le opzioni
 * - can_publish: può pubblicare ed eliminare i post
 * - can_write: può inserire e modificare i post ma non li può pubblicare o eliminare
 * - can_view_private: visualizzazione dei post impostati come privati
 */
class post extends \Gino\Controller {

    /**
     * @brief numero di ultimi post
     */
    protected $_last_post_number;

    /**
     * @brief numero di post per pagina nella vista elenco
     */
    protected $_list_nfp;

    /**
     * @brief numero di post nella vetrina
     */
    protected $_showcase_post_number;

    /**
     * @brief animazione vetrina start automatico
     */
    protected $_showcase_auto_start;

    /**
     * @brief animazione vetrina intervallo animazione
     */
    protected $_showcase_auto_interval;
    
    /**
     * @brief numero di post in evidenza
     */
    protected $_evidence_number;
    
    /**
     * @brief animazione post in evidenza - start automatico
     */
    protected $_evidence_auto_start;
    
    /**
     * @brief animazione post in evidenza - intervallo animazione
     */
    protected $_evidence_auto_interval;

    /**
     * @brief Massima larghezza immagini
     */
    protected $_image_width;

    /**
     * @brief Numero ultimi post esportati in lista newsletter
     */
    protected $_newsletter_post_number;
    
    /**
     * @brief Visualizza lo slideshow nella vista ultimi post
     * @var boolean
     */
    protected $_last_slideshow_view;
    
    /**
     * @brief Numero post visualizzati nello slideshow
     * @var integer
     */
    protected $_last_slideshow_number;

    /**
     * @brief Costruisce un'istanza di tipo post
     *
     * @param int $mdlId id dell'istanza di tipo post
     * @return void, istanza di Gino.App.Post.post
     */
    function __construct($mdlId) {

        parent::__construct($mdlId);
    }

    /**
     * @brief Restituisce alcune proprietà della classe utili per la generazione di nuove istanze
     *
     * @return array associativo di proprietà utilizzate per la creazione di istanze di tipo post (tabelle, css, viste, cartelle)
     */
    public static function getClassElements() {

        return array(
            "tables"=>array(
                'post_item',
                'post_item_category',
                'post_category',
                'post_opt',
            ),
            "css"=>array(
                'post.css'
            ),
            "views" => array(
                'archive.php' => _('Archivio post'),
                'detail.php' => _('Dettaglio post'),
                'last.php' => _('Lista ultimi post'),
                'showcase.php' => _('Vetrina post'),
            	'evidence.php' => _('Post in evidenza'),
                'feed_rss.php' => _('Feed RSS'),
                'newsletter.php' => _('Post esportati in newsletter')
            ),
            "folderStructure"=>array (
                CONTENT_DIR.OS.'post'=> array(
                    'img' => null,
                    'attachment' => null
                )
            )
        );
    }

    /**
     * @brief Eliminazione istanza
     * @desc Si esegue la cancellazione dei dati da db e l'eliminazione di file e directory
     *
     * @return TRUE
     */
    public function deleteInstance() {

        $this->requirePerm('can_admin');

        /* eliminazione items */
        Item::deleteInstance($this);
        /* eliminazione categorie */
        Category::deleteInstance($this);

        /* eliminazione da tabella opzioni */
        $opt_id = $this->_db->getFieldFromId($this->_tbl_options, "id", "instance", $this->_instance);
        \Gino\Translation::deleteTranslations($this->_tbl_options, $opt_id);
        $result = $this->_db->delete($this->_tbl_options, "instance=".$this->_instance);

        /* eliminazione file css */
        $classElements = $this->getClassElements();
        foreach($classElements['css'] as $css) {
            unlink(APP_DIR.OS.$this->_class_name.OS.\Gino\baseFileName($css)."_".$this->_instance_name.".css");
        }

        /* eliminazione views */
        foreach($classElements['views'] as $k => $v) {
            unlink($this->_view_dir.OS.\Gino\baseFileName($k)."_".$this->_instance_name.".php");
        }

        /* eliminazione cartelle contenuti */
        foreach($classElements['folderStructure'] as $fld=>$fldStructure) {
            \Gino\deleteFileDir($fld.OS.$this->_instance_name, true);
        }

        return TRUE;
    }

    /**
     * @brief Definizione dei metodi pubblici che forniscono un output per il front-end
     *
     * Questo metodo viene letto dal motore di generazione dei layout (metodi non presenti nel file ini) e dal motore di generazione
     * di voci di menu (metodi presenti nel file ini) per presentare una lista di output associati all'istanza di classe.
     *
     * @return array, METHOD_NAME => array('label' => (string) [label], 'permissions' => (array) [permissions list in the format classname.code_perm])
     */
    public static function outputFunctions() {

        $list = array(
            "last" => array("label"=>_("Lista utimi post"), "permissions"=>array()),
            "archive" => array("label"=>_("Lista paginata di post"), "permissions"=>array()),
            "showcase" => array("label"=>_("Vetrina"), "permissions"=>array()),
            "feedRSS" => array("label"=>_("Feed RSS"), "permissions"=>array())
        );

        return $list;
    }

    /**
     * @brief Getter larghezza di ridimensionamenteo delle immagini 
     * @return integer, larghezza di ridimensionamento
     */
    public function getImageWidth() {
        return $this->_image_width;
    }

    /**
     * @brief Esegue il download clientside del documento indicato da url ($doc_id)
     * @param \Gino\Http\Request $request istanza di Gino.Http.Request
     * @throws Gino.Exception.Exception404 se il documento non viene trovato
     * @throws Gino.Exception.Exception403 se il documento è associato ad un post che non si può visualizzare
     * @return Gino.Http.ResponseFile
     */
    public function download(\Gino\Http\Request $request) {

        $doc_id = \Gino\cleanVar($request->GET, 'id', 'int');
        if($doc_id) {
            $n = new Item($doc_id, $this);
            if(!$n->id) {
                throw new \Gino\Exception\Exception404();
            }
            if($n->private && !$this->userHasPerm('can_view_private')) {
                throw new \Gino\Exception\Exception403();
            }
            $attachment = $n->attachment;
            if($attachment) {
                $full_path = $this->getBaseAbsPath().OS.'attachment'.OS.$attachment;
                return \Gino\download($full_path); // restituisce un \Gino\Http\ResponseFile
            }
            else {
                throw new \Gino\Exception\Exception404();
            }
        }
        else {
            throw new \Gino\Exception\Exception404();
        }
    }

    /**
     * @brief Frontend vetrina post
     * @return string
     */
    public function showcase() {

        $this->_registry->addCss($this->_class_www."/post_".$this->_instance_name.".css");

        $conditions = ['published' => true];
        if(!$this->userHasPerm('can_view_private')) {
            $conditions['private'] = false;
        }
        
        $where = Item::setConditionWhere($this, $conditions);

        $items = Item::objects($this, array('where' => $where, 'order'=>'date DESC, insertion_date DESC', 'limit'=>array(0, $this->_showcase_post_number)));

        $view = new View($this->_view_dir);

        $view->setViewTpl('showcase_'.$this->_instance_name);
        $dict = array(
            'instance_name' => $this->_instance_name,
            'feed_url' => $this->link($this->_instance_name, 'feedRSS'),
            'items' => $items,
        	'archive_url' => $this->link($this->_instance_name, 'archive'),
            'autointerval' => $this->_showcase_auto_interval
        );

        return $view->render($dict);
    }
    
    /**
     * @brief Frontend post in evidenza
     * @return string
     */
    public function evidence() {
    
    	$this->_registry->addCss($this->_class_www."/post_".$this->_instance_name.".css");
    
    	$conditions = ['published' => true];
    	if(!$this->userHasPerm('can_view_private')) {
    	    $conditions['private'] = false;
    	}
    	$where = Item::setConditionWhere($this, $conditions);
    
    	$items = Item::objects($this, ['where' => $where, 'order'=>'date DESC, insertion_date DESC', 'limit'=>array(0, $this->_evidence_number)]);
    
    	$view = new View($this->_view_dir);
    
    	$view->setViewTpl('evidence_'.$this->_instance_name);
    	$dict = array(
    		'instance_name' => $this->_instance_name,
    		'items' => $items,
    	    'feed_url' => null,
    		'autointerval' => $this->_evidence_auto_interval
    	);
    
    	return $view->render($dict);
    }
    
    /**
     * @brief Post da mostrare nello slideshow
     * @return array(post objects)
     */
    private function slideshow() {
    	
    	$conditions = ['published' => true, 'slideshow' => true];
    	if(!$this->userHasPerm('can_view_private')) {
    	    $conditions['private'] = false;
    	}
    	$where = Item::setConditionWhere($this, $conditions);
    	
    	return Item::objects($this, [
    	    'where' => $where, 
    	    'order'=>'date DESC, insertion_date DESC', 
    	    'limit'=>array(0, $this->_last_slideshow_number)
    	]);
    }

    /**
     * @brief Front end ultimi post
     * @description Per visualizzare lo slideshow occorre impostare dei post da mostrare nello slideshow
     * 
     * @return string, lista ultimi post
     */
    public function last() {

        $request = \Gino\Http\Request::instance();

        $title_site = $this->_registry->sysconf->head_title;
        $module = new ModuleInstance($this->_instance);
        $title = $module->label.' | '.$title_site;

        $this->_registry->addCss($this->_class_www."/post_".$this->_instance_name.".css");
        
        $this->_registry->addHeadLink(array(
            'rel' => 'alternate',
            'type' => 'application/rss+xml',
            'title' => \Gino\jsVar($title),
            'href' => $request->root_absolute_url.$this->link($this->_instance_name, 'feedRSS')
        ));
        
        $conditions = array(
        	'published' => TRUE,
        );
        if(!$this->userHasPerm('can_view_private')) {
            $conditions['private'] = false;
        }
        
        $slideshow_items = array();
        
        if($this->_last_slideshow_view) {
        	
        	$slides = $this->slideshow();
        	if(count($slides) > 1)
        	{
        		$ids = array();
        		foreach ($slides AS $s) {
        			$ids[] = $s->id;
        		}
        		$conditions['remove_id'] = $ids;
        		$slideshow_items = $slides;
        	}
		}

		$where = Item::setConditionWhere($this, $conditions);
		$items = Item::objects($this, array(
            'where' => $where, 
            'order' => 'date DESC, insertion_date DESC', 
            'limit' => array(0, $this->_last_post_number), 
            'debug' => false
        ));

        $view = new View($this->_view_dir, 'last_'.$this->_instance_name);
        $dict = array(
            'instance_name' => $this->_instance_name,
            'items' => $items,
            'feed_url' => $this->link($this->_instance_name, 'feedRSS'),
            'archive_url' => $this->link($this->_instance_name, 'archive'), 
        	'slideshow' => $this->_last_slideshow_view, 
            'slideshow_items' => $slideshow_items,
        );

		return $view->render($dict);
    }

    /**
     * @brief Front end dettaglio post
     * @param \Gino\Http\Request $request istanza di Gino.Http.Request
     * @throws Gino.Exception.Exception404 se lo slug ricavato dalle GET non corrisponde ad alcun post
     * @throws Gino.Exception.Exception403 se l'utente non ha i permessi per visualizzare il post
     * @return Gino.Http.Response, dettaglio post
     */
    public function detail(\Gino\Http\Request $request) {

        $slug = \Gino\cleanVar($request->GET, 'id', 'string');

        $item = Item::getFromSlug($slug, $this);

        if(!$item || !$item->id || !$item->published) {
            throw new \Gino\Exception\Exception404();
        }
        if($item->private && !$this->userHasPerm('can_view_private')) {
            throw new \Gino\Exception\Exception403();
        }
        
        // Breadcrumbs
        if(class_exists('\Gino\BreadCrumbs', false)) {
        	$breadcrumbs = new \Gino\BreadCrumbs($this->_class_name);
        	$breadcrumbs->setItems(array(
        	    array('label' => _("Post"), 'link' => $this->link($this->_instance_name, 'archive')),
        		array('label' => $item->ml('title'), 'current' => true)
        	));
        	$bc = $breadcrumbs->render();
        } else {
        	$bc = null;
        }
        // /Breadcrumbs
        
        // Set Registry
        $this->_registry->addJs(SITE_JS."/lightbox/dist/ekko-lightbox.js");
        $this->_registry->addCss(CSS_WWW."/lightbox/dist/ekko-lightbox.css");
        $this->_registry->addCss($this->_class_www."/post_".$this->_instance_name.".css");
        
        $this->setSEOSettings([
            'title' => $item->ml('title'),
            'description' => $item->ml('text'),
            'keywords' => $item->tags,
            'url' => $this->link($this->_instance_name, 'detail', array('id' => $item->slug), '', array('abs'=>true)),
            'type' => 'article', 
            'image' => \Gino\getDomainUrl().$item->getImgPath()
        ]);
        // /Registry
        
		$view = new view($this->_view_dir, 'detail_'.$this->_instance_name);

        $dict = array(
            'instance_name' => $this->_instance_name,
        	'controller' => $this,
            'item' => $item,
            'related_contents_list' => $this->relatedContentsList($item),
            'social' => \Gino\shareAll('st_all_large', $this->link($this->_instance_name, 'detail', array('id' => $item->slug), '', array('abs'=>true))),
        	'breadcrumbs' => $bc
        );

        $document = new \Gino\Document($view->render($dict));
        return $document();
    }

    /**
     * @brief Lista di contenuti correlati per tag
     * @param \Gino\App\Post\Item $item oggetto @ref Gino.App.Post.Item
     * @return string, lista contenuti correlati
     */
    public function relatedContentsList($item) {
    	
        $related_contents = GTag::getRelatedContents($this->getClassName(), 'Item', $item->id);
        if(count($related_contents)) {
            $view = new View(null, 'related_contents_list');
            return $view->render(array('related_contents' => $related_contents));
        }
        else return '';
    }

    /**
     * @brief Frontend archivio post
     * @param \Gino\Http\Request $request istanza di Gino.Http.Request
     * @return Gino.Http.Response, archivio post
     * 
     * Parametri da link (GET): \n
     * - @a ctg (string), valore dello slug della categoria per i record relativi alla categoria
     * - @a tag (string), valore del tag per i record relativi a un tag
     * - @a month (int) + @a year (int), mese e anno per i record di un dato mese
     * 
     * Parametri da form di ricerca (POST)
     */
    public function archive(\Gino\Http\Request $request) {

        $this->_registry->addJs(SITE_JS."/lightbox/dist/ekko-lightbox.js");
        $this->_registry->addCss(CSS_WWW."/lightbox/dist/ekko-lightbox.css");
        $this->_registry->addCss($this->_class_www."/post_".$this->_instance_name.".css");

    	$this->_registry->title = $this->_registry->sysconf->head_title . ' | '._("Archivio post");
    	
        // Direct link values
        $ctgslug = \Gino\cleanVar($request->GET, 'ctg', 'string');
        $tag = \Gino\cleanVar($request->GET, 'tag', 'string');
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
            'tag' => $tag,
            'date_from' => $date_from ? \Gino\dbDateToDate($date_from) : null,
            'date_to' => $date_to ? \Gino\dbDateToDate($date_to) : null,
        );
        
        $obj_search = $this->setSearchParams($query_params);
        $search_values = $obj_search->getValues();
        
		$conditions = array(
        	'published' => TRUE, 
        	'ctg' => array_key_exists('category', $search_values) ? $search_values['category'] : null, 
        	'tag' => $search_values['tag'], 
        	'text' => $search_values['text'], 
        	'date_from' => $search_values['date_from'],
        	'date_to' => $search_values['date_to'],
        );
		if(!$this->userHasPerm('can_view_private')) {
		    $conditions['private'] = false;
		}
		
		$count = Item::getCount($this, $conditions);
        
		$paginator = Loader::load('Paginator', array($count, $this->_list_nfp));
        $limit = $paginator->limitQuery();
        
        $where = Item::setConditionWhere($this, $conditions);
        
        $items = Item::objects($this, array('where'=>$where, 'order'=>'date DESC, insertion_date DESC', 'limit'=>$limit, 'debug'=>false));
        
        $view = new View($this->_view_dir);
        $view->setViewTpl('archive_'.$this->_instance_name);
        $dict = array(
            'instance_name' => $this->_instance_name,
        	'controller' => $this,
            'feed_url' => $this->link($this->_instance_name, 'feedRSS'),
            'ctg' => $ctg,
        	'tag' => $tag, 
            'items' => $items,
            'pagination' => $paginator->pagination(),
            'search_form' => $obj_search->form($this->link($this->_instance_name, 'archive'), 'form_search_post'),
            'link_form' => $obj_search->linkSearchForm()
        );

        $document = new \Gino\Document($view->render($dict));
        return $document();
    }

    /**
     * @brief Interfaccia di amministrazione del modulo
     * @param \Gino\Http\Request $request istanza di Gino.Http.Request
     * @return Gino.Http.Response, interfaccia amministrazione
     */
    public function manageDoc(\Gino\Http\Request $request) {

        $this->requirePerm(array('can_admin', 'can_publish', 'can_write'));

        $block = \Gino\cleanVar($request->GET, 'block', 'string');

        $link_frontend = ['link' => $this->linkAdmin(array(), 'block=frontend'), 'label' => _('Frontend')];
        $link_options = ['link' => $this->linkAdmin(array(), 'block=options'), 'label' => _('Opzioni')];
        $link_ctg = ['link' => $this->linkAdmin(array(), 'block=ctg'), 'label' => _('Categorie')];
        $link_dft = ['link' => $this->linkAdmin(), 'label' => _('Contenuti')];
        $sel_link = $link_dft;

        if($block == 'frontend' && $this->userHasPerm('can_admin')) {
            $backend = $this->manageFrontend();
            $sel_link = $link_frontend;
        }
        elseif($block == 'options' && $this->userHasPerm('can_admin')) {
            $backend = $this->manageOptions();
            $sel_link = $link_options;
        }
        elseif($block == 'ctg') {
            $backend = $this->manageCategory($request);
            $sel_link = $link_ctg;
        }
        else {
            $backend = $this->managePost($request);
        }

        if(is_a($backend, '\Gino\Http\Response')) {
            return $backend;
        }

        // groups privileges
        if($this->userHasPerm('can_admin')) {
        	$links_array = array($link_frontend, $link_options, $link_ctg, $link_dft);
        }
        else {
        	$links_array = array($link_ctg, $link_dft);
        }

        $module = ModuleInstance::getFromName($this->_instance_name);

        $view = new View(null, 'tabs');
        $dict = array(
            'title' => \Gino\htmlChars($module->label),
            'links' => $links_array,
            'selected_link' => $sel_link,
            'content' => $backend
        );

        $document = new \Gino\Document($view->render($dict));
        return $document();
    }

    /**
     * @brief Interfaccia di amministrazione delle categorie
     * @param \Gino\Http\Request $request istanza di Gino.Http.Request
     * @return Gino.Http.Redirect oppure html, interfaccia di back office
     */
    private function manageCategory(\Gino\Http\Request $request) {

        $admin_table = \Gino\Loader::load('AdminTable', array($this, array()));

        $backend = $admin_table->backOffice(
            'Category',
            array(
                'list_display' => array('id', 'name', 'slug'),
                'list_title' => _("Elenco categorie"),
                'list_description' => "<p>"._('Ciascun post inserito potrà essere associata ad una o più categorie qui definite.')."</p>",
                 ),
            array(),
            array(
                'description' => array(
                    'widget' => 'editor',
                    'notes' => FALSE,
                    'img_preview' => TRUE,
                ),
                'image' => array(
                    'preview' => TRUE
                )
            )
        );

        return $backend;
    }

    /**
     * @brief Interfaccia di amministrazione dei post 
     * @param \Gino\Http\Request $request istanza di Gino.Http.Request
     * @return Gino.Http.Redirect oppure html, interfaccia di back office
     */
    private function managePost(\Gino\Http\Request $request) {

        if(!$this->userHasPerm('can_admin', 'can_publish')) {
            $remove_fields = array('published');
            $delete_deny = 'all';
        }
        else {
            $remove_fields = array();
            $delete_deny = array();
        }

        $admin_table = \Gino\Loader::load('AdminTable', array($this, array('delete_deny'=>$delete_deny)));
        
        $backend = $admin_table->backOffice(
            'Item',
            array(
                'list_display' => array('id', 'date', 'categories', 'title', 'published', 'private'),
                'list_title' => _("Elenco post"),
                'filter_fields' => array('categories', 'title', 'published')
            ),
            array(
                'removeFields' => $remove_fields
            ),
            array(
            	'title' => array('size' => 40),
            	'slug' => array('size' => 40),
                'text' => array(
                    'widget' => 'editor',
                    'notes' => FALSE,
                    'img_preview' => FALSE,
                ),
                'img' => array(
                    'preview' => TRUE
                )
            )
        );

        return $backend;
    }

    /**
     * @brief Metodo per la definizione di parametri da utilizzare per il modulo "Ricerca nel sito"
     *
     * Il modulo "Ricerca nel sito" di Gino base chiama questo metodo per ottenere informazioni riguardo alla tabella, campi, pesi etc...
     * per effettuare la ricerca dei contenuti.
     *
     * @return array[string]mixed array associativo contenente i parametri per la ricerca
     */
    public function searchSite() {

        $view_private = $this->userHasPerm('can_view_private') ? TRUE : FALSE;
        
        $clauses = array("instance" => $this->_instance, 'published' => 1);
        if(!$view_private) {
        	$clauses['private'] = 0;
        }

        return array(
            "table" => Item::$table, 
            "selected_fields" => array("id", "slug", "date", array("highlight"=>true, "field"=>"title"), array("highlight"=>true, "field"=>"text")), 
            "required_clauses" => $clauses, 
            "weight_clauses" => array("title"=>array("weight"=>3), "text"=>array("weight"=>1))
        );
    }

    /**
     * @brief Definisce la presentazione del singolo item trovato a seguito di ricerca (modulo "Ricerca nel sito")
     *
     * @param array $results array associativo contenente i risultati della ricerca
     * @return string, presentazione item tra i risultati della ricerca
     */
    public function searchSiteResult($results) {

        $obj = new Item($results['id'], $this);

        $buffer = "<div class=\"search-title\"><span class=\"fa fa-bullhorn\"></span> ".\Gino\dbDatetimeToDate($results['date'], "/")." <a href=\"".$this->link($this->_instance_name, 'detail', array('id'=>$results['slug']))."\">";
        $buffer .= $results['title'] ? \Gino\htmlChars($results['title']) : \Gino\htmlChars($obj->ml('title'));
        $buffer .= "</a></div>";

        if($results['text']) {
            $buffer .= "<div class=\"search-text\">...".\Gino\htmlChars($results['text'])."...</div>";
        }
        else {
            $buffer .= "<div class=\"search-text\">".\Gino\htmlChars(\Gino\cutHtmlText($obj->ml('text'), 120, '...', false, false, false, array('endingPosition'=>'in')))."</div>";
        }

        return $buffer;
    }

    /**
     * @brief Adattatore per la classe newsletter 
     * @return array di elementi esportabili nella newsletter
     */
    public function systemNewsletterList() {

        $posts = Item::objects($this, array('where' => "instance='".$this->_instance."'", 'order'=>'date DESC, insertion_date DESC', 'limit'=>array(0, $this->_newsletter_post_number)));

        $items = [];
        foreach($posts as $n) {
            $items[] = array(
                _('id') => $n->id,
                _('titolo') => \Gino\htmlChars($n->ml('title')),
                _('privata') => $n->private ? _('si') : _('no'),
                _('pubblicata') => $n->published ? _('si') : _('no'),
                _('data') => \Gino\dbDateToDate($n->date),
            );
        }

        return $items;
    }

    /**
     * @brief Contenuto di un post nella newsletter 
     * @param int $id identificativo del post
     * @return string
     */
    public function systemNewsletterRender($id) {

        $n = new Item($id, $this);

        $view = new View($this->_view_dir, 'newsletter_'.$this->_instance_name);
        $dict = array(
            'item' => $n,
        );

        return $view->render($dict);
    }

    /**
     * @brief Genera un feed RSS standard che presenta gli ultimi 50 post pubblicati
     * @param \Gino\Http\Request $request istanza di Gino.Http.Request
     * @return \Gino\Http\Response, feed RSS
     */
    public function feedRSS(\Gino\Http\Request $request) {

        $title_site = $this->_registry->sysconf->head_title;
        $module = new ModuleInstance($this->_instance);
        $title = $module->label.' | '.$title_site;
        $description = $module->description;

        $items = Item::objects($this, [
            'where' => "instance='".$this->_instance."' AND private='0' AND published='1'",
            'order'=>'date DESC, insertion_date DESC',
            'limit'=>array(0, 50)
        ]);
        
        $view = new \Gino\View($this->_view_dir, 'feed_rss_'.$this->_instance_name);
        $dict = array(
            'title' => $title,
            'description' => $description,
            'request' => $request,
            'items' => $items
        );

        $response = new \Gino\Http\Response($view->render($dict));
        $response->setContentType('text/xml');
        return $response;
    }
}
