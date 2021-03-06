<?php
/**
 * @file class_page.php
 * @brief Contiene la definizione ed implementazione della classe Gino.App.Page.page.
 */

/**
 * @namespace Gino.App.Page
 * @description Namespace dell'applicazione Page, per la gestione di pagine categorizzate
 */
namespace Gino\App\Page;

use Gino\Http\Request;

use Gino\Http\Response;
use Gino\Http\Redirect;

use \Gino\View;
use \Gino\Document;

require_once('class.PageCategory.php');
require_once('class.PageEntry.php');
require_once('class.PageComment.php');

/**
 * @brief Classe di tipo Gino.Controller per la gestione delle pagine.
 *
 * ##CARATTERISTICHE
 * Modulo di gestione pagine, con feed RSS e predisposizione contenuti per ricerca nel sito e newsletter. \n
 * Per ogni singola pagina è possibile abilitare l'inserimento dei commenti (il form include un controllo captcha).
 *
 * ##PERMESSI
 * - visualizzazione pagine private (@a can_view_private)
 * - redazione (@a can_edit)
 * - redazione singole pagine (@a can_edit_single_page)
 * - pubblicazione (@a can_publish)
 * - amministrazione modulo (@a can_admin)
 * 
 * Il metodo utilizzato per verificare le condizioni di accesso alle pagine è accessPage(). 
 * Questo metodo è pubblico e può essere utilizzato anche dalle altre classi e applicazioni, come ad esempio la classe Gino.App.Menu.menu.
 *
 * ##POLITICHE DI VISUALIZZAZIONE
 * Alla visualizzazione di una pagina concorrono i seguenti elementi:
 * - pubblicazione della pagina (campo @a published)
 * - visualizzazione a utenti con permesso 'visualizza pagine private' (campo @a private)
 * - visualizzazione a utenti specifici (campo @a users)
 *
 * Per un utente non autenticato una pagina è quindi visibile nel caso in cui:
 * - è pubblicata (published=1)
 * - non è associata a specifici utenti (users='')
 * - non è privata (private=0)
 *
 * ##OPZIONI CONFIGURABILI
 * - titolo vetrina pagine più lette + numero + template singolo elemento
 * - template pagina
 * - moderazione commenti
 * - notifica commenti
 * - template per newsletter
 *
 * ##OUTPUT
 * - showcase, vetrina pagine (più lette)
 * - view, visualizzazione pagina
 * - feeRSS, feed RSS
 *
 * ##DIRECTORY DEI CONTENUTI
 * ---------------
 * I contenuti non testuali delle pagine sono strutturati in directory secondo lo schema:
 * - contents/
 * - page/
 * - page/
 * - [page_id]/
 *
 * ##TEMPLATE
 * Quando una pagina viene richiamata da URL viene chiamato il metodo view(). \n
 * In questo caso il template di default è quello che corrisponde al campo @a entry_tpl_code della tabella delle opzioni ('Template vista dettaglio pagina' nelle opzioni vista pagina). \n
 * Questo template può essere sovrascritto compilando il campo "Template pagina intera" (@tpl_code) nel form della pagina.
 *
 * Quando una pagina viene richiamata nel template del layout viene chiamato il metodo box(). \n
 * In questo caso il template di default è quello che corrisponde al campo @a box_tpl_code della tabella delle opzioni ('Template vista dettaglio pagina' nelle opzioni vista pagina inserita nel template). \n
 * Questo template può essere sovrascritto compilando il campo "Template box" (@box_tpl_code) nel form della pagina.
 */
class page extends \Gino\Controller {

	/**
	 * @brief Titolo vista pagine recenti
	 */
    protected $_last_title;
	
	/**
	 * @brief Numero elementi nella vista pagine recenti
	 */
    protected $_last_number;
	
	/**
	 * @brief Template elemento in vista pagine recenti
	 */
    protected $_last_tpl_code;
	
    /**
     * @brief Titolo vista vetrina
     */
    protected $_showcase_title;

    /**
     * @brief Numero elementi in vetrina
     */
    protected $_showcase_number;

    /**
     * @brief Template elemento in vista vetrina
     */
    protected $_showcase_tpl_code;

    /**
     * @brief Avvio automatico animazione vetrina
     */
    protected $_showcase_auto_start;

    /**
     * @brief Intervallo animazione automatica vetrina
     */
    protected $_showcase_auto_interval;

    /**
     * @brief Template pagina
     */
    protected $_entry_tpl_code;

    /**
     * @brief Template pagina inserita nel template
     */
    protected $_box_tpl_code;

    /**
     * @brief Moderazione dei commenti
     */
    protected $_comment_moderation;

    /**
     * @brief Notifica commenti
     */
    protected $_comment_notification;

    /**
     * @brief Numero di elementi proposti per la newsletter
     */
    protected $_newsletter_entries_number;

    /**
     * @brief Template elemento quando inserito in newsletter
     */
    protected $_newsletter_tpl_code;

    /**
     * @brief Elenco delle applicazioni che è teoricamente possibile utilizzare nel template della pagina
     * @var array
     */
    private $_applications;
    
    /**
     * @brief Elenco delle applicazioni che è possibile utilizzare nel template della pagina
     * @var array
     */
    private $_valid_applications;

    /**
     * @brief Costruttore
     * @return void, istanza di Gino.App.Page.page
     */
    function __construct() {

        parent::__construct();

        $this->_applications = array('gallery', 'gmaps');
		$this->_valid_applications = null;
    }

    /**
     * @brief Restituisce alcune proprietà della classe
     * @return array, lista delle proprietà utilizzate per la creazione di istanze di tipo pagina
     */
    public static function getClassElements() {

        return array(
            "tables"=>array(
                'page_category',
                'page_comment',
                'page_entry',
                'page_opt',
            ),
            "css"=>array(
                'page.css'
            ),
            'views' => array(
                'box.php' => _("Template per l'inserimento della pagine nel layout"),
                'showcase.php' => _('Vetrina pagine'),
                'view.php' => _('Dettaglio pagina'),
            	'last.php' => _("Pagine recenti")
            ),
            "folderStructure"=>array (
                CONTENT_DIR.OS.'page'=> null
            )
        );
    }

    /**
     * @brief Definizione dei metodi pubblici che forniscono un output per il front-end
     *
     * Questo metodo viene letto dal motore di generazione dei layout (prende i metodi non presenti nel file ini) e dal motore di generazione di 
     * voci di menu (presenti nel file ini) per presentare una lista di output associati all'istanza di classe.
     *
     * @return array, METHOD_NAME => array('label' => (string) [label], 'permissions' => (array) [permissions list in the format classname.code_perm])
     */
    public static function outputFunctions() {

        $list = array(
            "showcase" => array("label"=>_("Vetrina (più letti)"), "permissions"=>array()),
            "last" => array("label"=>_("Pagine recenti"), "permissions"=>array()),
        );

        return $list;
    }

    /**
     * @brief Linee guida sulla costruzione di un template
     * @return string
     */
    public static function explanationTemplate() {

        $code_exp = _("Le proprietà della pagina devono essere inserite all'interno di doppie parentesi graffe {{ proprietà }}. Proprietà disponibili").":<br/>";
        $code_exp .= "<ul>";
        $code_exp .= "<li><b>img</b>: "._('immagine')."</li>";
        $code_exp .= "<li><b>title</b>: "._('titolo')."</li>";
        $code_exp .= "<li><b>text</b>: "._('testo')."</li>";
        $code_exp .= "<li><b>creation_date</b>: "._('data di creazione')."</li>";
        $code_exp .= "<li><b>creation_time</b>: "._('hh:mm di creazione')."</li>";
        $code_exp .= "<li><b>last_edit_date</b>: "._('data di ultima modifica')."</li>";
        $code_exp .= "<li><b>author</b>: "._('autore post')."</li>";
        $code_exp .= "<li><b>author_img</b>: "._('fotografia autore post')."</li>";
        $code_exp .= "<li><b>tags</b>: "._('tag associati')."</li>";
        $code_exp .= "<li><b>read</b>: "._('numero di letture')."</li>";
        $code_exp .= "<li><b>social</b>: "._('condivisione social')."</li>";
        $code_exp .= "<li><b>comments</b>: "._('numero di commenti con link')."</li>";
        $code_exp .= "<li><b>related_contents</b>: "._('lista di contenuti correlati con link')."</li>";
        $code_exp .= "</ul>";
        $code_exp .= _("Inoltre si possono eseguire dei filtri o aggiungere link facendo seguire il nome della proprietà dai caratteri '|filtro'. Disponibili").":<br />";
        $code_exp .= "<ul>";
        $code_exp .= "<li><b><span style='text-style: normal'>|link</span></b>: "._('aggiunge il link che porta al dettaglio')."</li>";
        $code_exp .= "<li><b><span style='text-style: normal'>img|class:<i>name_class</i></span></b>: "._('aggiunge la classe name_class all\'immagine')."</li>";
        $code_exp .= "<li><b><span style='text-style: normal'>img|size:<i>w</i>x<i>h</i></span></b>: "._('ridimensiona l\'immagine a larghezza (w) e altezza (h) dati')."</li>";
        $code_exp .= "<li><b><span style='text-style: normal'>|chars:n</span></b>: "._('mostra solo n caratteri della proprietà')."</li>";
        $code_exp .= "<li><b><span style='text-style: normal'>|title:&quot;html_title&quot;</span></b>: "._('Aggiunge il titolo fornito alla lista dei contenuti correlati')."</li>";
        $code_exp .= "</ul>";
        $code_exp .= _("Ulteriori proprietà disponibili relative ad altre applicazioni (se installate)").":<br />";
        $code_exp .= "<ul>";
        $code_exp .= "<li><b>map|gid:<i>map_id</i></b>: "._("mostra la mappa indicata")."</li>";
        $code_exp .= "<li><b>show_gallery|gid:<i>gallery_id</i></b>: "._("mostra alcune immagini appartenenti alla galleria indicata")."</li>";
        $code_exp .= "<li><b>link_gallery|gid:<i>gallery_id</i></b>: "._("mostra il collegamento alla galleria immagini indicata")."</li>";
        $code_exp .= "</ul>";

        return $code_exp;
    }

    /**
     * @brief Indirizzo pagina
     * @description Viene utilizzato per le operazione interne a gino (ad es. menu e layout)
     *
     * @param integer $id valore ID della pagina
     * @param boolean $box se TRUE restituisce l'indirizzo per una pagina inserita nel template del layout
     * @return string
     */
    public static function getUrlPage($id, $box = FALSE) {

        return $this->_registry->router->link('page', $box ? 'box' : 'view', array(), array('id' => $id));
    }

    /**
     * @brief Getter dell'opzione comment_notification 
     * @return boolean, proprietà comment_notification
     */
    public function commentNotification() {

        return $this->_comment_notification;
    }

    /**
     * @brief Percorso base alla directory dei contenuti
     *
     * @param string $path tipo di percorso (default abs)
     *   - abs, assoluto
     *   - rel, relativo
     * @return string
     */
    public function getBasePath($path='abs'){

        $directory = '';

        if($path == 'abs')
            $directory = $this->_data_dir.OS;
        elseif($path == 'rel')
            $directory = $this->_data_www.'/';

        return $directory;
    }

    /**
     * @brief Accesso alla visualizzazione di una pagina
     *
     * @param array options
     *   array associativo di opzioni
     *   - @b page_obj (object): oggetto della pagina
     *   - @b page_id (integer): valore ID della pagina
     * @return bool
     */
    public function accessPage($options = array()) {

        $page_obj = array_key_exists('page_obj', $options) ? $options['page_obj'] : null;
        $page_id = array_key_exists('page_id', $options) ? $options['page_id'] : null;

        if(!$page_obj) {
            $page_obj = new PageEntry($page_id);
        }

        $p_private = $page_obj->private;
        $p_users = $page_obj->users;

        if($p_users)
        {
            $users = explode(',', $p_users);
            if(!in_array($this->_registry->user->id, $users)) {
            	return FALSE;
            }
        }

        if($p_private && !$this->userHasPerm('can_view_private')) {
        	return FALSE;
        }

        return TRUE;
    }

    /**
     * @brief Front end vetrina pagine più lette
     * @description Visualizzazione come slider
     * @return string
     */
    public function showcase() {

        $this->_registry->addCss($this->_class_www."/page.css");
        $this->_registry->addJs($this->_class_www."/page.js");

        $options = array(
        	'published' => true, 
        	'user_id' => isset($this->_registry->user->id) ? $this->_registry->user->id : 0,
        	'view_private' => $this->userHasPerm('can_view_private'), 
        	'order' => '\'read\' DESC, creation_date DESC', 
        	'limit' => array(0, $this->_showcase_number),
        	'debug' => false
        );

        $entries = PageEntry::get($options);

        preg_match_all("#{{[^}]+}}#", $this->_showcase_tpl_code, $matches);
        $items = array();
        $ctrls = array();
        $indexes = array();
        $i = 0;
        $tot = count($entries);
        foreach($entries as $entry) {
            $indexes[] = $i;
            $buffer = "<div class='page-showcase-item' style='display: block;z-index:".($tot-$i)."' id=\"entry_$i\">";
            $buffer .= $this->parseTemplate($entry, $this->_showcase_tpl_code, $matches);
            $buffer .= "</div>";
            $items[] = $buffer;

            $onclick = "pageslider.set($i)";
            $ctrls[] = "<div id=\"sym_page_".$i."\" class=\"page-showcase-sym\" onclick=\"$onclick\"><span></span></div>";
            $i++;
        }
        
        $slider_options = '{}';
        if($this->_showcase_auto_start) {
            $slider_options = "{auto_start: true, auto_interval: ".$this->_showcase_auto_interval."}";
        }

        $view = new \Gino\View($this->_view_dir);

        $view->setViewTpl('showcase');
        $view->assign('title', $this->_showcase_title);
        $view->assign('feed_url', $this->link($this->_instance_name, 'feedRSS'));
        $view->assign('items', $items);
        $view->assign('ctrls', $ctrls);
        $view->assign('options', $slider_options);

        return $view->render();
    }

    /**
     * @brief Front end pagina inserita nel template del layout
     *
     * Il template di default impostato nelle opzioni della libreria può essere sovrascritto da un template personalizzato (campo @a box_tpl_code).
     *
     * Parametri GET: \n
     *   - id (integer), valore ID della pagina
     *
     * @see self::accessPage()
     * @see self::parseTemplate()
     * @param int $id valore ID della pagina
     * @return string
     */
    public function box($id=null) {

        $registry = \Gino\registry::instance();
        $registry->addCss($this->_class_www."/prettify.css");
        $registry->addJs($this->_class_www."/prettify.js");
        $registry->addCss($this->_class_www."/page.css");
        $registry->addJs($this->_class_www."/page.js");

        if(!$id) $id = \Gino\cleanVar($_GET, 'id', 'int', '');

        $item = pageEntry::getFromSlug($id, $this);

        if(!$item || !$item->id || !$item->published) {
            return '';
        }

        if(!$this->accessPage(array('page_obj'=>$item)))
            return '';

        $tpl_item = $item->box_tpl_code ? $item->box_tpl_code : $this->_box_tpl_code;

        preg_match_all("#{{[^}]+}}#", $tpl_item, $matches);
        $tpl = $this->parseTemplate($item, $tpl_item, $matches);
        $view = new \Gino\View($this->_view_dir);

        $view->setViewTpl('box');
        $view->assign('section_id', 'view_'.$this->_instance_name.$id);
        $view->assign('tpl', $tpl);

        return $view->render();
    }

    /**
     * @brief Front end pagina 
     *
     * Il template di default impostato nelle opzioni della libreria può essere sovrascritto da un template personalizzato (campo @a tpl_code).
     *
     * @see self::accessPage()
     * @see self::parseTemplate()
     * @see self::formComment()
     * @see Gino.App.Page.PageComment::getTree()
     * @param \Gino\Http\Request istanza di Gino.Http.Request
     * @throws Gino.Exception.Exception404 se la pagina non viene trovata
     * @throws Gino.Exception.Exception403 se non si hanno i permessi per visualizzare la pagina
     * @return Gino.Http.Response
     */
    public function view(\Gino\Http\Request $request) {

        $registry = \Gino\registry::instance();
        $registry->addCss($this->_class_www."/prettify.css");
        $registry->addJs($this->_class_www."/prettify.js");
        $registry->addCss($this->_class_www."/page.css");
        $registry->addJs($this->_class_www."/page.js");
        $registry->addCss(CSS_WWW."/responsive-table.css");

        $slug = \Gino\cleanVar($request->GET, 'id', 'string');

        $item = PageEntry::getFromSlug($slug, $this);
        
        if(!$item || !$item->id || !$item->published) {
            throw new \Gino\Exception\Exception404();
        }

        $registry->title = $registry->sysconf->head_title . ' | '.\Gino\htmlChars($item->ml('title'));
        $registry->description = \Gino\cutHtmlText($item->ml('text'), 200, '...', true, false, true, '');
        $registry->keywords = $item->tags;

        // load sharethis if present
        if($item->social) {
            $registry->js_load_sharethis = TRUE;
        }

        if(!$this->accessPage(array('page_obj'=>$item))) {
            throw new \Gino\Exception\Exception403();
        }

        $tpl_item = $item->tpl_code ? $item->tpl_code : $this->_entry_tpl_code;

        preg_match_all("#{{[^}]+}}#", $tpl_item, $matches);
        $tpl = $this->parseTemplate($item, $tpl_item, $matches);
        
        if($item->view_last_edit_date) {
        	$last_edit_date = date('d/m/Y', strtotime($item->last_edit_date));
        } else {
        	$last_edit_date = null;
        }

        $comments = array();
        $form_comment = '';
        if($item->enable_comments) {
            $form_comment = $this->formComment($item);

            $tree = pageComment::getTree($item->id);
            foreach($tree as $t) {
                $comment = new pageComment($t['id'], $this);
                $recursion = $t['recursion'];
                $replyobj = $comment->reply ? new pageComment($comment->reply, $this) : null;
                $comments[] = array(
                    'id' => $comment->id,
                    'datetime' => date("d/m/Y H:i", strtotime($comment->datetime)),
                    'author' => \Gino\htmlChars($comment->author),
                    'web' => \Gino\htmlChars($comment->web),
                    'text' => \Gino\htmlChars(nl2br($comment->text)),
                    'recursion' => $recursion,
                    'reply' => $replyobj && $replyobj->id ? \Gino\htmlChars($replyobj->author) : null,
                    'avatar' => md5( strtolower(trim( $comment->email)))
                );
            }
        }
        if(!$request->user->id) {
            
			$item->read = $item->read + 1;
        	$item->save(array('only_update'=>'read'));
        }

        $view = new \Gino\View($this->_view_dir);

        $view->setViewTpl('view');
        $view->assign('page', $item);
        $view->assign('tpl', $tpl);
        $view->assign('last_edit_date', $last_edit_date);
        $view->assign('enable_comments', $item->enable_comments);
        $view->assign('form_comment', $form_comment);
        $view->assign('comments', $comments);
        $view->assign('url', $this->link($this->_instance_name, 'view', array('id'=>$item->slug)));
        $view->assign('related_contents_list', $this->relatedContentsList($item));

        $document = new \Gino\Document($view->render());
        return $document();
    }
    
    /**
     * @brief Front end elenco ultime pagine pubblicate
     *
     * @access public
     * @return string
     */
    public function last() {
    	
    	$this->_registry->addCss($this->_class_www."/page.css");
    	$this->_registry->title = $this->_registry->sysconf->head_title . ' | '._("Pagine recenti");
    	
    	$options = array(
    		'published' => true,
    		'user_id' => isset($this->_registry->user->id) ? $this->_registry->user->id : 0,
    		'view_private' => $this->userHasPerm('can_view_private'),
    		'order' => 'creation_date DESC',
    		'limit' => array(0, $this->_last_number)
    	);
    	
    	$entries = PageEntry::get($options);
    	
    	preg_match_all("#{{[^}]+}}#", $this->_last_tpl_code, $matches);
    	$items = array();
    	foreach($entries as $entry) {
    		$items[] = $this->parseTemplate($entry, $this->_last_tpl_code, $matches);
    	}
    	
    	$view = new \Gino\View($this->_view_dir);
    	
    	$view->setViewTpl('last');
    	$view->assign('title', $this->_last_title);
    	$view->assign('items', $items);
    	$view->assign('feed_url', $this->link($this->_instance_name, 'feedRSS'));
    	
    	$document = new \Gino\Document($view->render());
    	return $document();
    }

    /**
     * @brief Form di inserimento commento
     * @return string, form
     */
    private function formComment($entry) {
        
        $item = new PageComment(null);
        
        $mform = \Gino\Loader::load('ModelForm', array($item, array(
            'form_id' => 'form_comment',
        )));
        
        $mform->setHidden(['entry' => $entry->id, 'form_reply' => ['value' => 0, 'options' => ['id'=>'form_reply']]]);
        
        $buffer = '';
        
        if($this->_comment_moderation) {
            $buffer .= "<p>"._('Il tuo commento verrà sottoposto ad approvazione prima di essere pubblicato.')."</p>";
        }
        
        $buffer .= $mform->view(
            array(
                'session_value' => 'dataform',
                'view_title' => false,
                'f_action' => $this->link($this->_instance_name, 'actionComment'),
                's_value' => _("invia"),
                'show_save_and_continue' => false,
                'removeFields' => ['entry', 'reply', 'published'],
                'addCell' => ['last_cell' => ['name' => 'the_last_row', 'field' => $mform->captcha()]]
            ),
            array(
                'author' => ['label' => _("Nome")],
                'email' => ['label' => [_('Email'), _('Non verrà pubblicata')], 'pattern'=>'^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$', 'hint'=>_('Inserire un indirizzo email valido')],
                'notification' => ['label' => _("Inviami un'email quando vengono postati altri commenti")]
            )
        );
        
        return $buffer;
    }

    /**
     * @brief Processa il form di inserimento commento
     * @param \Gino\Http\Request istanza di Gino.Http.Request
     * @throws Gino.Exception.Exception404 se la pagina non viene trovata o i commenti sono disabilitati
     * @return Gino.Http.Redirect
     */
    public function actionComment(\Gino\Http\Request $request) {

        $myform = \Gino\Loader::load('Form', array(array('form_id'=>'form_comment')));
        $myform->saveSession('dataform');
        $req_error = $myform->checkRequired();

        $id = \Gino\cleanVar($request->POST, 'entry', 'int');
        $entry = new PageEntry($id, $this);

        if(!$entry or !$entry->id or !$entry->enable_comments) {
            throw new \Gino\Exception\Exception404();
        }

        $link_error = $this->link('page', 'view', array('id' => $entry->slug)).'#comments';

        if($req_error > 0) { 
            return \Gino\Error::errorMessage(array('error'=>1), $link_error);
        }

        if(!$myform->checkCaptcha()) {
            return \Gino\Error::errorMessage(array('error'=>_('Il codice inserito non è corretto')), $link_error);
        }

        $published = $this->_comment_moderation ? 0 : 1;

        $comment = new PageComment(null);

        $comment->author = \Gino\cleanVar($request->POST, 'author', 'string', '');
        $comment->email = \Gino\cleanVar($request->POST, 'email', 'string', '');
        $comment->web = \Gino\cleanVar($request->POST, 'web', 'string', '');
        $comment->text = \Gino\cutHtmlText(\Gino\cleanVar($request->POST, 'text', 'string', ''), 100000000, '', true, true, true, array());
        $comment->notification = \Gino\cleanVar($request->POST, 'notification', 'int', '');
        $comment->entry = $entry->id;
        $comment->datetime = date('Y-m-d H:i:s');
        $comment->reply = \Gino\cleanVar($request->POST, 'form_reply', 'int', '');
        $comment->published = $published;

        $comment->save();

        // send mail to publishers
        if(!$published) {

            $link = $request->root_absolute_url.$this->link('page', 'view', array('id' => $entry->slug)).'#comment'.$comment->id;
            \Gino\Loader::import('auth', '\Gino\App\Auth\User');

            $user_ids = \Gino\App\Auth\User::getUsersFromPermissions('can_publish', $this);

            foreach($user_ids as $uid) {
                $email = $this->_db->getFieldFromId(\Gino\App\Auth\User::$table, 'email', 'id', $uid);
                if($email) {
                    $subject = sprintf(_("Nuovo commento alla pagina \"%s\" in attesa di approvazione"), $entry->title);
                    $object = sprintf("E' stato inserito un nuovo commento in fase di approvazione da %s il %s, clicca su link seguente (o copia ed incolla nella barra degli indirizzi) per visualizzarlo\r\n%s", $comment->author, $comment->datetime, $link);
                    $from = "From: ".$this->_registry->sysconf->email_from_app;
                    \mail($email, $subject, $object, $from);
                }
            }
        }

        return new Redirect($this->link('page', 'view', array('id' => $entry->slug)).'#comments');
    }

    /**
     * @brief Lista di contenuti correlati per tag
     * @param \Gino\App\Page\PageEntry $page_entry istanza di Gino.App.Page.PageEntry
     * @return string, lista contenuti correlati
     */
    public function relatedContentsList($page_entry) {
    	
        $related_contents = \Gino\GTag::getRelatedContents($this->getClassName(), 'PageEntry', $page_entry->id);
        if(count($related_contents)) {
        	$view = new \Gino\View(null, 'related_contents_list');
        	return $view->render(array('related_contents' => $related_contents));
        }
        else return '';
    }

    /**
     * @brief Parserizzazione dei template inseriti da opzioni
     *
     * @param \Gino\App\Page\PageEntry $entry istanza di Gino.App.Page.PageEntry
     * @param string $tpl codice del template
     * @param array $matches matches delle variabili da sostituire
     * @return string, template parserizzato
     */
    private function parseTemplate($entry, $tpl, $matches) {

        if(isset($matches[0])) {
            foreach($matches[0] as $m) {
                $code = trim(preg_replace("#{|}#", "", $m));
                if($pos = strrpos($code, '|')) {
                    $property = substr($code, 0, $pos);
                    $filter = substr($code, $pos + 1);
                }
                else {
                    $property = $code;
                    $filter = null;
                }

                $replace = $this->replaceTplVar($property, $filter, $entry);
                $tpl = preg_replace("#".preg_quote($m)."#", $replace, $tpl);
            }
        }

        return $tpl;
    }

    /**
     * @brief Replace delle variabili del template
     *
     * @param string $property proprietà da sostituire
     * @param string $filter filtro applicato
     * @param \Gino\App\Page\PageEntry $obj istanza di Gino.App.Page.PageEntry
     * @return string, replace del parametro proprietà
     */
    private function replaceTplVar($property, $filter, $obj) {

        $pre_filter = '';

        if($property == 'img') {
            if(!$obj->image) {
                return '';
            }
            $image = "<img class=\"img-responsive\" src=\"".$obj->imgPath($this)."\" alt=\"img: ".\Gino\jsVar($obj->ml('title'))."\" />";
            if($obj->url_image) {
            	$image = "<a href=\"".$obj->url_image."\">$image</a>";
            }
            $pre_filter = $image;
        }
        elseif($property == 'author_img') {
            $concat = $this->_db->concat(array("firstname", "' '", "lastname"));
            $user_image = $this->_db->getFieldFromId(TBL_USER, 'photo', 'id', $obj->author);
            $user_name = $this->_db->getFieldFromId(TBL_USER, $concat, 'id', $obj->author);
            if(!$user_image) {
                return '';
            }
            $pre_filter = "<img src=\"".CONTENT_WWW."/user/img_".$user_image."\" alt=\"img: ".\Gino\jsVar($user_name)."\" title=\"".\Gino\jsVar($user_name)."\" />";    
        }
        elseif($property == 'creation_date' or $property == 'last_edit_date') {
            $pre_filter = date('d/m/Y', strtotime($obj->{$property}));
        }
        elseif($property == 'creation_time') {
            $pre_filter = date('H:m', strtotime($obj->creation_date));
        }
        elseif($property == 'text' || $property == 'title') {
            $pre_filter = \Gino\htmlChars($obj->ml($property));
            /*
            if($property == 'text') {
            	// Activate lightbox in the view "view"
            	$pre_filter = \Gino\htmlChars($obj->ml($property), 'pageLbox');
            }
            else {
            	$pre_filter = \Gino\htmlChars($obj->ml($property));
            }*/
        }
        elseif($property == 'read') {
            $pre_filter = $obj->read;
        }
        elseif($property == 'author') {
            $concat = $this->_db->concat(array("firstname", "' '", "lastname"));
            $pre_filter = $this->_db->getFieldFromId(TBL_USER, $concat, 'id', $obj->author);
        }
        elseif($property == 'tags') {
            $pre_filter = $obj->tags;
        }
        elseif($property == 'social') {
            if(!$obj->social) {
                return '';
            }
            
            $pre_filter = \Gino\shareAll(
            	array('facebook', 'twitter', 'linkedin', 'google_plus', 'pinterest', 'evernote', 'email'), 
            	$this->link($this->_instance_name, 'view', array('id'=>$obj->slug), '', array('abs'=>true)), 
            	\Gino\htmlChars($obj->ml('title')));
        }
        elseif($property == 'comments') {
            if(!$obj->enable_comments) {
                return _('disabilitati');
            }
            $comments_num = PageComment::getCountFromEntry($obj->id);
            $pre_filter = sprintf('<a href="%s">%s</a>', $this->link('page', 'view', array('id' => $obj->slug)).'#comments', $comments_num);
        }
        elseif($property == 'related_contents') {
            $pre_filter = $this->relatedContentsList($obj);
        }
        // applications
        elseif($property == 'map' || $property == 'show_gallery' || $property == 'link_gallery') {
        	$pre_filter = $property;
        }
        else {
            return '';
        }

        if(is_null($filter)) {
            return $pre_filter;
        }

        if($filter == 'link') {
            return "<a href=\"".$this->link('page', 'view', array('id' => $obj->slug))."\">".$pre_filter."</a>";
        }
        // applications
        elseif(preg_match("#gid:(\d+)#", $filter, $matches)) {
        	if($pre_filter == 'show_gallery' && $this->checkValidApplication('gallery')) {
        	    $classname = "\Gino\App\Gallery\gallery";
        	    $buffer = "<div class=\"show-gallery\">";
        	    $buffer .= $classname::showGalleryImages($matches[1]);
        		$buffer .= "</div>";
        		return $buffer;
        	}
        	elseif($pre_filter == 'link_gallery' && $this->checkValidApplication('gallery')) {
        	    $classname = "\Gino\App\Gallery\gallery";
        	    $buffer = "<div class=\"link-gallery\">";
        	    $buffer .= "<a class=\"btn btn-primary\" href=\"".$classname::getGalleryLink($matches[1])."\">"._("vai alla galleria completa")."</a>";
        		$buffer .= "</div>";
        		return $buffer;
        	}
        	elseif($pre_filter == 'map' && $this->checkValidApplication('gmaps')) {
        		$id = $matches[1];
        		$classname = "\Gino\App\Gmaps\gmaps";
        		$map_instance = $classname::getInstanceValue($id);
        		$map = new $classname($map_instance);
        		return $map->showMap($id);
        	}
        	else {
        		return null;
        	}
        }
        elseif(preg_match("#chars:(\d+)#", $filter, $matches)) {
            return \Gino\cutHtmlText($pre_filter, $matches[1], '...', false, false, true, array('endingPosition'=>'in'));
        }
        elseif(preg_match("#class:(.+)#", $filter, $matches)) {
            if(isset($matches[1]) && ($property == 'img' || $property == 'author_img')) {
                return preg_replace("#<img#", "<img class=\"".$matches[1]."\"", $pre_filter);
            }
            else return $pre_filter;
        }
        elseif(preg_match("#size:(\d+)x(\d+)#", $filter, $matches)) {
            if(isset($matches[1]) && isset($matches[2]) && ($property == 'img' || $property == 'author_img')) {
                $gimage = new \Gino\GImage(absolutePath($obj->imgPath($this)));
                $thumb = $gimage->thumb($matches[1], $matches[2]);
                return preg_replace("#src=\"(.*?)\"#", "src=\"".$thumb->getPath()."\"", $pre_filter);
            }
            else return $pre_filter;
        }
        elseif(preg_match("#title:\"(.*)\"#", $filter, $matches)) {
            if(isset($matches[1]) and $property == 'related_contents') {
                if($pre_filter) {
                    return $matches[1].$pre_filter;
                }
                else {
                    return '';
                }
            }
        }
        else {
            return $pre_filter;
        }
    }
    
    /**
     * @brief Verifica se una applicazione è presente in gino e se è valida
     *
     * @param string $app_name nome dell'applicazione (valore del campo @a name della tabella sys_module_app)
     * @return boolean
     */
	public function checkValidApplication($app_name) {
		
		if(is_null($this->_valid_applications)) {
		
			$list = array();
			if(count($this->_applications)) {
				foreach($this->_applications as $app) {
					$rows = $this->_db->select("id, instantiable", TBL_MODULE_APP, "name='$app' AND active='1'");
					if($rows && count($rows))
					{
						if($rows[0]['instantiable'] == 1) {
							$modules = $this->_db->select("id, name", TBL_MODULE, "module_app='".$rows[0]['id']."' AND active='1'");
							if($modules && count($modules)) {
								$list[] = $app;
							}
						}
						else {
							$list[] = $app;
						}
					}
				}
			}
			$this->_valid_applications = $list;
		}
		
		if(in_array($app_name, $this->_valid_applications)) {
			return true;
		}
		else {
			return false;
		}
    }

    /**
     * @brief Interfaccia di amministrazione del modulo
     * @param \Gino\Http\Request $request istanza di Gino.Http.Request
     * @return Gino.Http.Response interfaccia di back office
     */
    public function managePage(\Gino\Http\Request $request) {

        \Gino\Loader::import('class', '\Gino\AdminTable');

        $block = \Gino\cleanVar($request->GET, 'block', 'string', '');
        $action = \Gino\cleanVar($request->GET, 'action', 'string', '');

        $this->requirePerm(array('can_admin', 'can_publish', 'can_edit', 'can_edit_single_page'));

        $link_frontend = ['link' => $this->linkAdmin(array(), 'block=frontend'), 'label' => _('Frontend')];
        $link_locale = ['link' => $this->linkAdmin(array(), 'block=locale'), 'label' => _('Traduzioni')];
        $link_options = ['link' => $this->linkAdmin(array(), 'block=options'), 'label' => _('Opzioni')];
        $link_comment = ['link' => $this->linkAdmin(array(), 'block=comment'), 'label' => _('Commenti')];
        $link_ctg = ['link' => $this->linkAdmin(array(), 'block=ctg'), 'label' => _('Categorie')];
        $link_dft = ['link' => $this->linkAdmin(), 'label' => _('Contenuti')];

        $sel_link = $link_dft;

        if($block == 'frontend') {
            $backend = $this->manageFrontend();
            $sel_link = $link_frontend;
        }
        elseif($block == 'locale') {
        	$backend = $this->manageLocale();
        	$sel_link = $link_locale;
        }
        elseif($block == 'options' && $this->userHasPerm('can_admin')) {
            $backend = $this->manageOptions();
            $sel_link = $link_options;
        }
        elseif($block == 'ctg') {
            $backend = $this->manageCtg();
            $sel_link = $link_ctg;
        }
        elseif($block == 'comment') {
            $backend = $this->manageComment();
            $sel_link = $link_comment;
        }
        else {
            $backend = $this->manageEntry($request);
        }

        $links_array = array($link_dft);
        if($this->userHasPerm(array('can_admin', 'can_publish'))) {
            $links_array = array_merge(array($link_comment), $links_array);
        }
        if($this->userHasPerm(array('can_admin'))) {
            $links_array = array_merge(array($link_frontend, $link_locale, $link_options, $link_ctg), $links_array);
        }

        if(is_a($backend, '\Gino\Http\Response')) {
            return $backend;
        }

        $view = new View();
        $view->setViewTpl('tabs');
        $dict = array(
            'title' => _('Pagine'),
            'links' => $links_array,
            'selected_link' => $sel_link,
            'content' => $backend
        );

        $document = new Document($view->render($dict));
        return $document();
    }

    /**
     * @brief Interfaccia di amministrazione delle pagine
     *
     * @param \Gino\Http\Request $request istanza di Gino.Http.Request
     * @return Gino.Http.Redirect oppure html, interfaccia di back office delle pagine,
     */
    private function manageEntry($request) {

        //$this->_registry->addJs($this->_class_www.'/page.js');

        $allow_insertion = true;
        $delete_deny = null;
        $edit_deny = null;
        $edit_allow = null;
        
        if(!$this->userHasPerm(array('can_admin', 'can_publish'))) {
            $list_display = array('id', 'category_id', 'last_edit_date', 'title', 'tags', 'published', array('member'=>'getUrl', 'label'=>_('Url'))); 
            $remove_fields = array('author', 'published', 'social', 'private', 'users', 'users_edit', 'read');
            
            if($this->userHasPerm(array('can_edit_single_page'))) {
            	$allow_insertion = false;
            	$delete_deny = 'all';
            	$edit_deny = 'all';
            	$edit_allow = PageEntry::getEditEntry($request->user->id);
            }
        }
        else {
            $list_display = array('id', 'category_id', 'last_edit_date', 'title', 'tags', 'private', 'published', array('member'=>'getUrl', 'label'=>_('Url'))); 
            $remove_fields = array('author', 'read');
        }

        // Controllo unicità slug
        $url = $this->link($this->_instance_name, 'checkSlug');
        $div_id = 'check_slug';
        $availability = "&nbsp;&nbsp;<span class=\"link\" onclick=\"gino.ajaxRequest('post', '$url', 'id='+$('#id').prop('value')+'&slug='+$('#slug').prop('value'), '$div_id')\">"._("verifica disponibilità")."</span>";
        $availability .= "<div id=\"$div_id\" style=\"display:inline; margin-left:10px; font-weight:bold;\"></div>\n";

        $admin_table = new \Gino\AdminTable($this, array(
        		'allow_insertion'=>$allow_insertion, 
        		'delete_deny'=>$delete_deny,
        		'edit_deny'=>$edit_deny, 
        		'edit_allow'=>$edit_allow,
                'form_filters_inline' => true
        ));
        
        $backend = $admin_table->backOffice(
            'PageEntry',
            array(
                'list_display' => $list_display,
                'list_title'=>_("Elenco pagine"), 
                'filter_fields'=>array('title', 'category_id', 'tags', 'published'),
                'advanced_export' => false
            ),
        	array(
                'removeFields' => $remove_fields
            ),
            array(
                'id'=>array(
                    'id'=>'id'
                ),
                'slug'=>array(
                    'text_add'=>$availability,
                ),
                'text'=>array(
                    'widget'=>'editor',
                    'notes'=>TRUE,
                    'img_preview'=>TRUE,
                    'fck_toolbar'=>'Full'
                ),
                'image'=>array(
                    'preview'=>true,
                    'del_check'=>true
                ),
                'url_image'=>array(
                    'size'=>40,
                    'trnsl'=>false
                ),
            	'tpl_code'=>array(
                    'cols' => 40,
                    'rows' => 10, 
                	'typeoftext' => 'html',
                    'trnsl'=>false
                ),
                'box_tpl_code'=>array(
                    'cols' => 40,
                    'rows' => 10, 
                	'typeoftext' => 'html',
                    'trnsl'=>false
                )
            )
        );

        return $backend;
    }

    /**
     * @brief Interfaccia di amministrazione delle categorie
     * @return Gino.Http.Redirect oppure html, interfaccia di back office delle categorie,
     */
    private function manageCtg() {

        $admin_table = new \Gino\AdminTable($this, array());

        $backend = $admin_table->backOffice('PageCategory', 
            array(
                'list_display' => array('id', 'name', 'description'),
                'list_title'=>_("Elenco categorie"), 
                'list_description'=>'', 
                'filter_fields'=>array()
            )
        );

        return $backend;
    }


    /**
     * @brief Interfaccia di amministrazione dei commenti
     * @return Gino.Http.Redirect oppure html, interfaccia di back office dei commenti,
     */
    private function manageComment() {

        $admin_table = new \Gino\AdminTable($this, array());

        $buffer = $admin_table->backOffice(
            'PageComment',
            array(
                'list_display' => array('id', 'datetime', 'entry', 'author', 'email', 'published'),
                'list_title'=>_("Elenco commenti"),
            ),
            array(),
            array()
        );

        return $buffer;
    }

    /**
     * @brief Controlla l'unicità del valore dello slug
     * @param \Gino\Http\Request $request istanza di Gino.Http.Request
     * @return Gino.Http.Response (disponibile / non disponibile)
     */
    public function checkSlug(\Gino\Http\Request $request) {

        $id = \Gino\cleanVar($request->POST, 'id', 'int', '');
        $slug = \Gino\cleanVar($request->POST, 'slug', 'string', '');

        if(!$slug)
        {
            $valid = FALSE;
        }
        else
        {
            $where_add = $id ? " AND id!='$id'" : '';
            $res = $this->_db->select('id', pageEntry::$table, "slug='$slug'".$where_add);
            $valid = ($res && count($res)) ? FALSE : TRUE;
        }

        $content = $valid ? _("disponibile") : _("non disponibile");

        return new \Gino\Http\Response($content);
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

        return array(
            "table"=>PageEntry::$table,
            "selected_fields"=>array("id", "slug", "creation_date", array("highlight"=>true, "field"=>"title"), array("highlight"=>true, "field"=>"text")),
            "required_clauses"=>array("published"=>1),
            "weight_clauses"=>array("title"=>array("weight"=>5), 'tags'=>array('weight'=>3), "text"=>array("weight"=>1))
        );
    }

    /**
     * @brief Definisce la presentazione del singolo item trovato a seguito di ricerca (modulo "Ricerca nel sito")
     *
     * @see Gino.App.SearchSite
     * @see Gino.Search
     * @param array $results array associativo contenente i risultati della ricerca
     * @return string, presentazione item tra i risultati della ricerca
     */
    public function searchSiteResult($results) {

        $obj = new pageEntry($results['id'], $this);

        $buffer = "<div class=\"search-title\"><span class=\"fa fa-file-text-o\"></span> <a href=\"".$this->link($this->_instance_name, 'view', array('id'=>$results['slug']))."\">";
        $buffer .= $results['title'] ? \Gino\htmlChars($results['title']) : \Gino\htmlChars($obj->ml('title'));
        $buffer .= "</a> </div>";

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

        $entries = PageEntry::get(array('order'=>'creation_date DESC', 'limit'=>array(0, $this->_newsletter_entries_number)));

        $items = array();
        foreach($entries as $entry) {
            $items[] = array(
                _('id') => $entry->id,
                _('titolo') => \Gino\htmlChars($entry->ml('title')),
                _('pubblicato') => $entry->published ? _('si') : _('no'),
                _('data creazione') => \Gino\dbDateToDate($entry->creation_date),
            );
        }

        return $items;
    }

    /**
     * @brief Contenuto di una pagina quanto inserita in una newsletter
     * @param int $id identificativo della pagina
     * @return string, contenuto pagina
     */
    public function systemNewsletterRender($id) {

        $entry = new PageEntry($id, $this);

        preg_match_all("#{{[^}]+}}#", $this->_newsletter_tpl_code, $matches);
        $buffer = $this->parseTemplate($entry, $this->_newsletter_tpl_code, $matches);

        return $buffer;
    }

    /**
     * @brief Genera un feed RSS standard che presenta gli ultimi 50 post pubblicati
     * @param \Gino\Http\Request istanza di Gino.Http.Request
     * @return Gino.Http.Response feed RSS
     */
    public function feedRSS(\Gino\Http\Request $request) {

        $title_site = $this->_registry->sysconf->head_title;
        $title = _('Pagine') . '|' . $title_site;
        $description = $this->_db->getFieldFromId(TBL_MODULE, 'description', 'id', $this->_instance);

        $header = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
        $header .= "<rss version=\"2.0\" xmlns:atom=\"http://www.w3.org/2005/Atom\">\n";
        $header .= "<channel>\n";
        $header .= "<atom:link href=\"".$request->absolute_url."\" rel=\"self\" type=\"application/rss+xml\" />\n";
        $header .= "<title>".$title."</title>\n";
        $header .= "<link>".$request->root_absolute_url."</link>\n";
        $header .= "<description>".$description."</description>\n";
        $header .= "<language>".$request->session->lng."</language>";
        $header .= "<docs>http://blogs.law.harvard.edu/tech/rss</docs>\n";

        $body = '';
        $entries = PageEntry::get(array('published'=>TRUE, 'order'=>'creation_date DESC', 'limit'=>array(0, 50)));
        if(count($entries) > 0) {
            foreach($entries as $entry) {
                $id = \Gino\htmlChars($entry->id);
                $title = \Gino\htmlChars($entry->ml('title'));
                $text = \Gino\htmlChars($entry->ml('text'));
                $text = str_replace("src=\"", "src=\"".$request->root_absolute_url, $text);
                $text = str_replace("href=\"", "href=\"".$request->root_absolute_url, $text);

                $date = date('d/m/Y', strtotime($entry->creation_date));

                $body .= "<item>\n";
                $body .= "<title>".$date.". ".$title."</title>\n";
                $body .= "<link>".$request->root_absolute_url.$entry->getUrl()."</link>\n";
                $body .= "<description>\n";
                $body .= "<![CDATA[\n";
                $body .= $text;
                $body .= "]]>\n";
                $body .= "</description>\n";
                $body .= "<guid>".$request->root_absolute_url.$entry->getUrl()."</guid>\n";
                $body .= "</item>\n";
            }
        }

        $footer = "</channel>\n";
        $footer .= "</rss>\n";

        $response = new \Gino\Http\Response($header . $body . $footer);
        $response->setContentType('text/xml');
        return $response;
    }
}
