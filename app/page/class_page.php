<?php
/**
 * \file class_page.php
 * Contiene la definizione ed implementazione della classe page.
 * 
 * @version 0.1
 * @copyright 2012 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */

/**
 * Caratteristiche, opzioni configurabili da backoffice ed output disponibili per i template e le voci di menu.
 *
 * CARATTERISTICHE
 * ---------------
 * Modulo di gestione pagine, con predisposizione contenuti per ricerca nel sito e newsletter
 * 
 * PERMESSI
 * ---------------
 * Sono previsti tre gruppi di lavoro:
 * - pubblicazione ($_group_1)
 * - redazione ($_group_2)
 * - accesso alle pagine private ($_group_3)
 * 
 * Le persone associate al gruppo redazione non hanno accesso ai campi 'published', 'private', 'users'.
 * 
 * POLITICHE DI VISUALIZZAZIONE
 * ---------------
 * Alla visualizzazione di una pagina concorrono i seguenti elementi:
 * - pubblicazione della pagina (campo @a published)
 * - visualizzazione a utenti appartenenti al gruppo "utenti pagine private" (campo @a private)
 * - visualizzazione a utenti specifici (campo @a users)
 * 
 * ###Utenti non autenticati
 * Una pagina viene visualizzata se:
 * - è pubblicata (published=1)
 * - non è associata a specifici utenti (users='')
 * - non è privata (private=0)
 * 
 * ###Utenti autenticati
 * Una pagina viene visualizzata se:
 * - è pubblicata (published=1)
 * - si appartiene almeno al gruppo "redazione"
 * 
 * Nel caso in cui l'utente non appartenga al gruppo redazione:
 * 1) se la pagina è associata a specifici utenti si controlla se l'utente della sessione è compreso tra questi utenti
 * 2) se la pagina è privata si controlla se l'utente della sessione appartiene al gruppo "utenti pagine private"
 * 3) se la pagina è associata a specifici utenti ed è anche privata dovranno essere valide entrambe le condizioni 1 e 2
 * 
 * ###Metodi
 * Il metodo utilizzato per verificare le condizioni di accesso alle pagine è accessPage(). \n
 * Il metodo defAccessPage() viene invece utilizzato per definire quali pagine possano essere mostrate negli elenchi, aggiungendo alle opzioni del metodo richiamato (archive, last, ...) le seguenti opzioni:
 * - valore ID dell'utente in sessione (@a access_user)
 * - se l'utente può accedere alle pagine private (@a access_private)
 * 
 * Queste opzioni concorrono alla definizione delle condizioni di una selezione, in particolare nel metodo pageEntry::accessWhere(). 
 * 
 * OPZIONI CONFIGURABILI
 * ---------------
 * - titolo ultime pagine pubblicate
 * - titolo archivio pagine
 * - titolo vetrina pagine
 * - titolo tag cloud
 * - numero ultime pagine
 * - template singolo elemento ultime pagine
 * - numero pagine in vetrina
 * - template singolo elemento vetrina
 * - numero di elementi per pagina in archivio
 * - template singolo elemento archivio
 * - template pagina
 * - moderazione commenti
 * - notifica commenti
 *
 * OUTPUTS
 * ---------------
 * - ultime pagine pubblicate
 * - archivio pagine
 * - vetrina pagine
 * - tag cloud
 * - pagina
 * - feed RSS
 */

require_once(CLASSES_DIR.OS."class.category.php");
require_once('class.adminTablePageCategory.php');
require_once('class.pageCategory.php');
require_once('class.pageEntry.php');
require_once('class.pageComment.php');
require_once('class.pageTag.php');

/**
 * @defgroup page
 * Modulo di gestione delle pagine
 *
 * Il modulo contiene anche dei css, javascript e file di configurazione.
 *
 */

/**
 * \ingroup page
 * Classe per la gestione delle pagine.
 *
 * Gli output disponibili sono:
 *
 * - ultime pagine
 * - elenco pagine per categoria
 * - vetrina pagine
 * - tag cloud
 * - pagina
 * - feed RSS
 * 
 * @version 0.1
 * @copyright 2012 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */
class page extends AbstractEvtClass {

	/**
	 * Titolo vista ultimi post
	 */
	private $_last_title;

	/**
	 * Titolo vista archivio
	 */
	private $_archive_title;

	/**
	 * Titolo vista vetrina
	 */
	private $_showcase_title;

	/**
	 * Titolo vista tag cloud
	 */
	private $_cloud_title;

	/**
	 * Numero ultimi post
	 */
	private $_last_number;

	/**
	 * Template elemento in vista ultimi post
	 */
	private $_last_tpl_code;

	/**
	 * Numero post in vetrina
	 */
	private $_showcase_number;

	/**
	 * Template elemento in vista vetrina
	 */
	private $_showcase_tpl_code;

	/**
	 * Avvio automatico animazione vetrina
	 */
	private $_showcase_auto_start;

	/**
	 * Intervallo animazione automatica vetrina
	 */
	private $_showcase_auto_interval;

	/**
	 * Numero di post per pagina
	 */
	private $_archive_efp;

	/**
	 * Template elemento in vista archivio
	 */
	private $_archive_tpl_code;

	/**
	 * Template pagina
	 */
	private $_entry_tpl_code;
	
	/**
	 * Template pagina inserita nel template
	 */
	private $_box_tpl_code;

	/**
	 * Moderazione dei commenti
	 */
	private $_comment_moderation;

	/**
	 * Notifica commenti
	 */
	private $_comment_notification;

	/**
	 * Numero di post proposti per la newsletter
	 */
	private $_newsletter_entries_number;

	/**
	 * Template elemento quando inserito in newsletter
	 */
	private $_newsletter_tpl_code;

	/**
	 * @brief Tabella di opzioni 
	 */
	private $_tbl_opt;

	/**
	 * @brief Tabella di associazione utenti/gruppi 
	 */
	private $_tbl_usr;

	/**
	 * Percorso assoluto alla directory contenente le viste 
	 */
	private $_view_dir;
	
	/**
	 * Contiene gli id dei gruppi abilitati alla pubblicazione e redazione
	 * 
	 * @var array 
	 * @access private
	 */
	private $_group_1;
	
	/**
	 * Contiene gli id dei gruppi abilitati alla redazione
	 * 
	 * @var array 
	 * @access private
	 */
	private $_group_2;
	
	/**
	 * Contiene gli id dei gruppi che possono accedere alle pagine private
	 * 
	 * @var array 
	 * @access private
	 */
	private $_group_3;

	/*
	 * Parametro action letto da url 
	 */
	private $_action;

	/*
	 * Parametro block letto da url 
	 */
	private $_block;

	/**
	 * Costruisce un'istanza di tipo pagina
	 *
	 * @param int $mdlId id dell'istanza di tipo pagina
	 * @return istanza della pagina
	 */
	function __construct($mdlId=null) {

		parent::__construct();

		//$this->_instance = $mdlId;
		$this->_instance = 0;
		$this->_instanceName = $this->_className;

		$this->_data_dir = $this->_data_dir.$this->_os.$this->_instanceName;
		$this->_data_www = $this->_data_www."/".$this->_instanceName;

		$this->_tbl_opt = 'page_opt';
		$this->_tbl_usr = 'page_usr';

		$this->setAccess();
		$this->setGroups();

		$this->_view_dir = dirname(__FILE__).OS.'view';

		$last_tpl_code = "";
		$showcase_tpl_code = "";
		$archive_tpl_code = "";
		$entry_tpl_code = "";
		$box_tpl_code = "";
		$newsletter_tpl_code = "";

		$this->_optionsValue = array(
			'last_title'=>_("Ultime pagine pubblicate"),
			'archive_title'=>_("Pagine"),
			'showcase_title'=>_("In evidenza"),
			'cloud_title'=>_("Categorie"),
			'last_number'=>3,
			'last_tpl_code'=>$last_tpl_code,
			'showcase_number'=>3,
			'showcase_auto_start'=>1,
			'showcase_auto_interval'=>5000,
			'showcase_tpl_code'=>$showcase_tpl_code,
			'archive_efp'=>10,
			'archive_tpl_code'=>$archive_tpl_code,
			'entry_tpl_code'=>$entry_tpl_code, 
			'box_tpl_code'=>$box_tpl_code, 
			'showcase_auto_interval'=>5000,
			'comment_moderation'=>0,
			'comment_notification'=>1,
			'newsletter_entries_number'=>5,
			'newsletter_tpl_code'=>$newsletter_tpl_code,
		);

		$code_exp = _("Le proprietà della pagina devono essere inserite all'interno di doppie parentesi {{ proprietà }}. Proprietà disponibili:<br/>");
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
		$code_exp .= "</ul>";
		$code_exp .= _("Inoltre si possono eseguire dei filtri o aggiungere link facendo seguire il nome della proprietà dai caratteri '|filtro'. Disponibili:<br />");
		$code_exp .= "<ul>";
		$code_exp .= "<li><b><span style='text-style: normal'>|link</span></b>: "._('aggiunge il link che porta al dettaglio del post alla proprietà')."</li>";
		$code_exp .= "<li><b><span style='text-style: normal'>img|class:name_class</span></b>: "._('aggiunge la classe name_class all\'immagine')."</li>";
		$code_exp .= "<li><b><span style='text-style: normal'>|chars:n</span></b>: "._('mostra solo n caratteri della proprietà')."</li>";
		$code_exp .= "</ul>";

		$this->_last_title = htmlChars($this->setOption('last_title', array('value'=>$this->_optionsValue['last_title'], 'translation'=>true)));
		$this->_showcase_title = htmlChars($this->setOption('showcase_title', array('value'=>$this->_optionsValue['showcase_title'], 'translation'=>true)));
		$this->_archive_title = htmlChars($this->setOption('archive_title', array('value'=>$this->_optionsValue['archive_title'], 'translation'=>true)));
		$this->_cloud_title = htmlChars($this->setOption('cloud_title', array('value'=>$this->_optionsValue['cloud_title'], 'translation'=>true)));
		$this->_last_number = $this->setOption('last_number', array('value'=>$this->_optionsValue['last_number']));
		$this->_last_tpl_code = $this->setOption('last_tpl_code', array('value'=>$this->_optionsValue['last_tpl_code'], 'translation'=>true));
		$this->_showcase_number = $this->setOption('showcase_number', array('value'=>$this->_optionsValue['showcase_number']));
		$this->_showcase_auto_start = $this->setOption('showcase_auto_start', array('value'=>$this->_optionsValue['showcase_auto_start']));
		$this->_showcase_auto_interval = $this->setOption('showcase_auto_interval', array('value'=>$this->_optionsValue['showcase_auto_interval']));
		$this->_showcase_tpl_code = $this->setOption('showcase_tpl_code', array('value'=>$this->_optionsValue['showcase_tpl_code'], 'translation'=>true));
		$this->_archive_efp = $this->setOption('archive_efp', array('value'=>$this->_optionsValue['archive_efp']));
		$this->_archive_tpl_code = $this->setOption('archive_tpl_code', array('value'=>$this->_optionsValue['archive_tpl_code'], 'translation'=>true));
		$this->_entry_tpl_code = $this->setOption('entry_tpl_code', array('value'=>$this->_optionsValue['entry_tpl_code'], 'translation'=>true));
		$this->_box_tpl_code = $this->setOption('box_tpl_code', array('value'=>$this->_optionsValue['box_tpl_code'], 'translation'=>true));
		$this->_comment_moderation = $this->setOption('comment_moderation', array('value'=>$this->_optionsValue['comment_moderation']));
		$this->_comment_notification = $this->setOption('comment_notification', array('value'=>$this->_optionsValue['comment_notification']));
		$this->_newsletter_entries_number = $this->setOption('newsletter_entries_number', array('value'=>$this->_optionsValue['newsletter_entries_number']));
		$this->_newsletter_tpl_code = $this->setOption('newsletter_tpl_code', array('value'=>$this->_optionsValue['newsletter_tpl_code'], 'translation'=>true));

		$res_newsletter = $this->_db->getFieldFromId($this->_tbl_module_app, 'id', 'name', 'newsletter');
		if($res_newsletter) {
			$newsletter_module = true;
		}
		else {
			$newsletter_module = false;
		}

		$this->_options = new options($this->_className, $this->_instance);
		$this->_optionsLabels = array(
			"last_title"=>array(
				'label'=>_("Titolo ultimi post"), 
				'value'=>$this->_optionsValue['last_title'], 
				'section'=>true, 
				'section_title'=>_('Titoli delle viste pubbliche')
			),
			"archive_title"=>array(
				'label'=>_("Titolo archivio"),
				'value'=>$this->_optionsValue['archive_title']
			),
			"showcase_title"=>array(
				'label'=>_("Titolo vetrina"),
				'value'=>$this->_optionsValue['showcase_title']
			),
			"cloud_title"=>array(
				'label'=>_("Titolo tag cloud"),
				'value'=>$this->_optionsValue['cloud_title']
			),
			"last_number"=>array(
				'label'=>_("Numero ultime pagine"),
				'value'=>$this->_optionsValue['last_number'],
				'section'=>true, 
				'section_title'=>_('Opzioni vista ultime pagine'),
				'section_description'=>"<p>"._('Il template verrà utilizzato per ogni pagina ed inserito all\'interno di una section')."</p>"
			),
			"last_tpl_code"=>array(
				'label'=>array(_("Template singolo elemento vista ultimi post"), $code_exp), 
				'value'=>$this->_optionsValue['last_tpl_code'],
			), 
			"showcase_number"=>array(
				'label'=>_("Numero elementi in vetrina"),
				'value'=>$this->_optionsValue['showcase_number'],
				'section'=>true, 
				'section_title'=>_('Opzioni vista vetrina'),
				'section_description'=>"<p>"._('Il template verrà utilizzato per ogni pagina ed inserito all\'interno di una section')."</p>"
			),
			"showcase_auto_start"=>array(
				'label'=>_("Avvio automatico animazione"),
				'value'=>$this->_optionsValue['showcase_auto_start'],
			),
			"showcase_auto_interval"=>array(
				'label'=>_("Intervallo animazione automatica (ms)"),
				'value'=>$this->_optionsValue['showcase_auto_interval'],
			),
			"showcase_tpl_code"=>array(
				'label'=>array(_("Template singolo elemento vista vetrina"), _("Vedi 'Template singolo elemento vista ultimi post' per le proprietà e filtri disponibili")), 
				'value'=>$this->_optionsValue['showcase_tpl_code'],
			), 
			"archive_efp"=>array(
				'label'=>_("Numero di elementi per pagina"),
				'value'=>$this->_optionsValue['archive_efp'],
				'section'=>true, 
				'section_title'=>_('Opzioni vista archivio'),
				'section_description'=>"<p>"._('Il template verrà utilizzato per ogni pagina ed inserito all\'interno di una section')."</p>"
			),
			"archive_tpl_code"=>array(
				'label'=>array(_("Template singolo elemento vista archivio"), _("Vedi 'Template singolo elemento vista ultimi post' per le proprietà e filtri disponibili")), 
				'value'=>$this->_optionsValue['archive_tpl_code'],
			), 
			"entry_tpl_code"=>array(
				'label'=>array(_("Template vista dettaglio pagina"), _("Il template viene inserito all'interno di una <b>section</b><br />Vedi 'Template singolo elemento vista ultimi post' per le proprietà e filtri disponibili")), 
				'value'=>$this->_optionsValue['entry_tpl_code'],
				'section'=>true, 
				'section_title'=>_('Opzioni vista pagina')
			), 
			"box_tpl_code"=>array(
				'label'=>array(_("Template vista dettaglio pagina"), _("Il template viene inserito all'interno di una <b>section</b><br />Vedi 'Template singolo elemento vista ultimi post' per le proprietà e filtri disponibili")), 
				'value'=>$this->_optionsValue['box_tpl_code'],
				'section'=>true, 
				'section_title'=>_('Opzioni vista pagina inserita nel template')
			), 
			"comment_moderation"=>array(
				'label'=>array(_("Moderazione commenti"), _('In tal caso i commenti dovranno essere pubblicati da un utente iscritto al gruppo dei \'pubblicatori\'. Tali utenti saranno notificati della presenza di un nuovo commento con una email')),
				'value'=>$this->_optionsValue['comment_moderation'],
				'section'=>true, 
				'section_title'=>_('Opzioni commenti')
			),
			"comment_notification"=>array(
				'label'=>array(_("Notifica commenti"), _('In tal caso l\'autore della pagina riceverà una email per ogni commento pubblicato')),
				'value'=>$this->_optionsValue['comment_notification'],
			),
			"newsletter_entries_number"=>array(
				'label'=>_('Numero di elementi presentati nel modulo newsletter'),
				'value'=>$this->_optionsValue['newsletter_entries_number'],
				'section'=>true, 
				'section_title'=>_('Opzioni newsletter'),
				'section_description'=> $newsletter_module 
					? "<p>"._('La classe si interfaccia al modulo newsletter di GINO installato sul sistema')."</p>"
					: "<p>"._('Il modulo newsletter non è installato')."</p>",
			),
			"newsletter_tpl_code"=>array(
				'label'=>array(_("Template pagina in inserimento newsletter"), $code_exp), 
				'value'=>$this->_optionsValue['newsletter_tpl_code'],
			),
		);

		$this->_action = cleanVar($_REQUEST, 'action', 'string', '');
		$this->_block = cleanVar($_REQUEST, 'block', 'string', '');
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
				'page_category', 
				'page_comment', 
				'page_entry', 
				'page_entry_tag', 
				'page_grp', 
				'page_opt', 
				'page_tag',
				'page_usr'
			),
			"css"=>array(
				'page.css'
			),
			"folderStructure"=>array (
				CONTENT_DIR.OS.'page'=> null
			)
		);
	}

	/**
	 * Setter per le proprietà group
	 *
	 * Definizione dei gruppi che gestiscono l'accesso alle funzionalità amministrative e non
	 *
	 * @return void
	 */
	private function setGroups(){
		
		// Pubblicazione
		$this->_group_1 = array($this->_list_group[0], $this->_list_group[1]);
		
		// Redazione
		$this->_group_2 = array($this->_list_group[0], $this->_list_group[1], $this->_list_group[2]);
		
		// Accesso alle pagine private
		$this->_group_3 = array($this->_list_group[0], $this->_list_group[1], $this->_list_group[2], $this->_list_group[3]);
	}

	/**
	 * Metodo invocato quando viene eliminata un'istanza di tipo pagina
	 *
	 * Si esegue la cancellazione dei dati da db e l'eliminazione di file e directory 
	 * 
	 * @access public
	 * @return bool il risultato dell'operazione
	 */
	public function deleteInstance() {

		$this->accessGroup('');

		return null;
	}

	/**
	 * Definizione dei metodi pubblici che forniscono un output per il front-end 
	 * 
	 * Questo metodo viene letto dal motore di generazione dei layout e dal motore di generazione di voci di menu
	 * per presentare una lista di output associati all'istanza di classe. 
	 * 
	 * @static
	 * @access public
	 * @return array[string]array
	 */
	public static function outputFunctions() {

		$list = array(
			"last" => array("label"=>_("Lista utime pagine"), "role"=>'1'),
			"archive" => array("label"=>_("Elenco pagine categorizzate"), "role"=>'1'),
			"showcase" => array("label"=>_("Vetrina (più letti)"), "role"=>'1'),
			//"tagcloud" => array("label"=>_("Tag cloud"), "role"=>'1')
		);

		return $list;
	}

	/**
	 * Getter della proprietà instanceName 
	 * 
	 * @return nome dell'istanza
	 */
	public function getInstanceName() {

		return $this->_instanceName;
	}

	/**
	 * Getter dell'opzione comment_notification 
	 * 
	 * @return proprietà comment_notification
	 */
	public function commentNotification() {

		return $this->_comment_notification;
	}
	
	/**
	 * Percorso base alla directory dei contenuti
	 *
	 * @param string $path tipo di percorso (default abs)
	 *   - abs, assoluto
	 *   - rel, relativo
	 * @return string
	 */
	public function getBasePath($path='abs'){
	
		$directory = '';
		
		if($path == 'abs')
			$directory = $this->_data_dir.$this->_os;
		elseif($path == 'rel')
			$directory = $this->_data_www.'/';
		
		return $directory;
	}
	
	/**
	 * Percorso della directory di una pagina a partire dal percorso base
	 * 
	 * @param integer $id valore ID della pagina
	 * @return string
	 */
	public function getAddPath($id) {
		
		if(!$id)
			$id = $this->_db->autoIncValue(pageEntry::$tbl_entry);
		
		$directory = $id.$this->_os;
		
		return $directory;
	}
	
	/**
	 * Gestisce l'accesso alla visualizzazione delle pagine
	 * 
	 * @param object $item
	 * @return boolean
	 */
	private function accessPage($item) {
		
		if($this->_access->AccessVerifyGroupIf($this->_className, $this->_instance, $this->_user_group, $this->_group_2))
			return true;
		
		if($item->users)
		{
			$users = explode(',', $item->users);
			if(!in_array($this->_session_user, $users))
				return false;
		}
		
		if($item->private && !$this->_access->AccessVerifyGroupIf($this->_className, $this->_instance, $this->_user_group, $this->_group_3))
			return false;
		
		return true;
	}
	
	/**
	 * Condizioni di accesso alle pagine nel caso in cui l'utente non appartenga al gruppo redazione
	 * 
	 * Se necessario aggiunge alle condizioni definite le condizioni di accesso alle pagine tramite le chiavi @a access_user e @a access_private.
	 * 
	 * @param array $conditions elenco delle condizioni del WHERE
	 * @return array
	 */
	private function defAccessPage($conditions=array()) {
		
		if(!$this->_access->AccessVerifyGroupIf($this->_className, $this->_instance, $this->_user_group, $this->_group_2))
		{
			$conditions['access_user'] = $this->_session_user;
			$conditions['access_private'] = $this->_access->AccessVerifyGroupIf($this->_className, $this->_instance, $this->_user_group, $this->_group_3);
		}
		
		return $conditions;
	}

	/**
	 * Front end elenco ultime pagine 
	 * 
	 * @access public
	 * @see defAccessPage()
	 * @see pageEntry::get()
	 * @return string
	 */
	public function last() {

		$this->setAccess($this->_access_base);

		$title_site = pub::variable('head_title');
		$title = $title_site.($this->_last_title ? " - ".$this->_last_title : "");

		$registry = registry::instance();
		$registry->addCss($this->_class_www."/last_".$this->_instanceName.".css");
		$registry->title = jsVar($title);
		$registry->addHeadLink(array(
			'rel' => 'alternate',
			'type' => 'application/rss+xml',
			'title' => jsVar($title),
			'href' => $this->_url_root.SITE_WWW.'/'.$this->_plink->aLink($this->_instanceName, 'feedRSS') 	
		));

		$options = array('published'=>true, 'order'=>'creation_date DESC', 'limit'=>array(0, $this->_last_number));
		$options = $this->defAccessPage($options);
		
		$entries = pageEntry::get($this, $options);
		
		preg_match_all("#{{[^}]+}}#", $this->_last_tpl_code, $matches);
		$items = array();
		foreach($entries as $entry) {
			$items[] = $this->parseTemplate($entry, $this->_last_tpl_code, $matches);
		}

		$archive = "<a href=\"".$this->_plink->aLink($this->_instanceName, 'archive')."\">"._('archivio')."</a>";

		$view = new view($this->_view_dir);

		$view->setViewTpl('last');
		$view->assign('section_id', 'last_'.$this->_instanceName);
		$view->assign('title', $this->_last_title);
		$view->assign('feed', "<a href=\"".$this->_plink->aLink($this->_instanceName, 'feedRSS')."\">".pub::icon('feed')."</a>");
		$view->assign('items', $items);
		$view->assign('archive', $archive);

		return $view->render();
	}

	/**
	 * Front end archivio 
	 * 
	 * @access public
	 * @return string
	 * 
	 * Parametri GET: \n
	 *   - id (string), nome del tag
	 *   - cid (integer), valore ID della categoria
	 */
	public function archive() {

		$this->setAccess($this->_access_base);

		$category_id = cleanVar($_GET, 'cid', 'integer', '');
		$tagname = cleanVar($_GET, 'id', 'string', '');

		if($tagname) {
			$tag = pageTag::getFromName($tagname, $this);
			$tag_id = $tag ? $tag->id : 0;
		}
		else {
			$tag_id = 0;
		}
		
		$params = array();
		if($category_id)
		{
			$params[] = "cid=$category_id";
		}
		if($tag_id)
		{
			$params[] = 'id='.$tag->name;
		}
		$params = implode('&', $params);
		
		$title_site = pub::variable('head_title');
		$title = $title_site.($this->_archive_title ? " - ".$this->_archive_title : "");

		$registry = registry::instance();
		$registry->addCss($this->_class_www."/prettify.css");
		$registry->addJs($this->_class_www."/prettify.js");
		$registry->addCss($this->_class_www."/page.css");
		$registry->addJs($this->_class_www."/page.js");
		$registry->title = jsVar($title);
		$registry->addHeadLink(array(
			'rel' => 'alternate',
			'type' => 'application/rss+xml',
			'title' => jsVar($title),
			'href' => $this->_url_root.SITE_WWW.'/'.$this->_plink->aLink($this->_instanceName, 'feedRSS') 	
		));
		
		$options_count = array('tag'=>$tag_id, 'published'=>true);
		$options_count = $this->defAccessPage($options_count);
		
		$entries_number = pageEntry::getCount($options_count);

		$pagination = new pagelist($this->_archive_efp, $entries_number, 'array');
		$limit = array($pagination->start(), $this->_archive_efp);

		$options = array('published'=>true, 'tag'=>$tag_id, 'category'=>$category_id, 'order'=>'creation_date DESC', 'limit'=>$limit);
		$options = $this->defAccessPage($options);
		
		$entries = pageEntry::get($this, $options);

		preg_match_all("#{{[^}]+}}#", $this->_archive_tpl_code, $matches);
		$items = array();
		foreach($entries as $entry) {
			$items[] = $this->parseTemplate($entry, $this->_archive_tpl_code, $matches);
		}

		$view = new view($this->_view_dir);
		$view->setViewTpl('archive');
		$view->assign('section_id', 'archive_'.$this->_instanceName);
		$view->assign('title', $this->_archive_title);
		$view->assign('subtitle', $tag_id ? sprintf(_("Pubblicati in %s"), htmlChars($tag->name)) : '');
		$view->assign('feed', "<a href=\"".$this->_plink->aLink($this->_instanceName, 'feedRSS')."\">".pub::icon('feed')."</a>");
		$view->assign('items', $items);
		$view->assign('pagination_summary', $pagination->reassumedPrint());
		$view->assign('pagination_navigation', $pagination->listReferenceGINO($this->_plink->aLink($this->_instanceName, 'archive', $params, '', array("basename"=>false))));

		return $view->render();
	}

	/**
	 * Front end tag cloud 
	 * 
	 * @access public
	 * @return tag cloud
	 */
	public function tagcloud() {

		$this->setAccess($this->_access_base);

		$registry = registry::instance();
		$registry->addCss($this->_class_www."/page.css");

		$tags_freq = pageEntry::getTagFrequency($this);	

		$items = array();
		$max_f = 0;
		foreach($tags_freq as $tid=>$f) {
			$tag = new pageTag($tid, $this);
			$items[] = array(
				"name"=>htmlChars($tag->name),
				"url"=>$this->_plink->aLink($this->_instanceName, 'archive', array('id'=>$tag->name)),
				"f"=>$f
			);
			$max_f = max($f, $max_f);
		}

		$view = new view($this->_view_dir);
		$view->setViewTpl('cloud');
		$view->assign('section_id', 'cloud_'.$this->_instanceName);
		$view->assign('title', $this->_cloud_title);
		$view->assign('items', $items);
		$view->assign('max_f', $max_f);

		return $view->render();
	}

	/**
	 * Front end vetrina pagine più lette 
	 * 
	 * @access public
	 * @see defAccessPage()
	 * @see pageEntry::get()
	 * @return string
	 */
	public function showcase() {
		
		$this->setAccess($this->_access_base);

		$registry = registry::instance();
		$registry->addCss($this->_class_www."/page.css");
		$registry->addJs($this->_class_www."/page.js");

		$options = array('published'=>true, 'order'=>'\'read\' DESC, creation_date DESC', 'limit'=>array(0, $this->_showcase_number));
		$options = $this->defAccessPage($options);
		
		$entries = pageEntry::get($this, $options);

		preg_match_all("#{{[^}]+}}#", $this->_showcase_tpl_code, $matches);
		$items = array();
		$ctrls = array();
		$indexes = array();
		$i = 0;
		$tot = count($entries);
		foreach($entries as $entry) {
			$indexes[] = $i;
			$buffer = "<div class='showcase_item' style='display: block;z-index:".($tot-$i)."' id=\"entry_$i\">";
			$buffer .= $this->parseTemplate($entry, $this->_showcase_tpl_code, $matches);
			$buffer .= "</div>";
			$items[] = $buffer;

			$onclick = "pageslider.set($i)";
			$ctrls[] = "<div id=\"sym_".$this->_instance.'_'.$i."\" class=\"scase_sym\" onclick=\"$onclick\"><span></span></div>";
			$i++;
		}
		
		$options = '{}';
		if($this->_showcase_auto_start) {
			$options = "{auto_start: true, auto_interval: ".$this->_showcase_auto_interval."}";
		}

		$view = new view($this->_view_dir);

		$view->setViewTpl('showcase');
		$view->assign('section_id', 'showcase_'.$this->_instanceName);
		$view->assign('wrapper_id', 'showcase_items_'.$this->_instanceName);
		$view->assign('ctrl_begin', 'sym_'.$this->_instance.'_');
		$view->assign('title', $this->_showcase_title);
		$view->assign('feed', "<a href=\"".$this->_plink->aLink($this->_instanceName, 'feedRSS')."\">".pub::icon('feed')."</a>");
		$view->assign('items', $items);
		$view->assign('ctrls', $ctrls);
		$view->assign('options', $options);

		return $view->render();
	}
	
	/**
	 * Front end pagina inserita nel template del layout
	 * 
	 * @access public
	 * @see pageEntry::getFromSlug()
	 * @see accessPage()
	 * @see parseTemplate()
	 * @see view::setViewTpl()
	 * @see view::assign()
	 * @see view::render()
	 * @param integer $id valore ID della pagina
	 * @return string
	 * 
	 * Parametri GET: \n
	 *   - id (integer), valore ID della pagina
	 */
	public function box($id=null) {

		$this->setAccess($this->_access_base);

		$registry = registry::instance();
		$registry->addCss($this->_class_www."/prettify.css");
		$registry->addJs($this->_class_www."/prettify.js");
		$registry->addCss($this->_class_www."/page.css");
		$registry->addJs($this->_class_www."/page.js");
		
		if(!$id) $id = cleanVar($_GET, 'id', 'int', '');
		
		$item = pageEntry::getFromSlug($id, $this);

		if(!$item || !$item->id || !$item->published) {
			return null;
		}
		
		if(!$this->accessPage($item))
			return null;

		preg_match_all("#{{[^}]+}}#", $this->_box_tpl_code, $matches);
		$tpl = $this->parseTemplate($item, $this->_box_tpl_code, $matches);

		$view = new view($this->_view_dir);

		$view->setViewTpl('box');
		$view->assign('section_id', 'view_'.$this->_instanceName.$id);
		$view->assign('tpl', $tpl);

		return $view->render();
	}
	
	/**
	 * Front end pagina 
	 * 
	 * @see pageEntry::getFromSlug()
	 * @see accessPage()
	 * @see parseTemplate()
	 * @see formComment()
	 * @see pageComment::getTree()
	 * @access public
	 * @return string
	 */
	public function view() {

		$this->setAccess($this->_access_base);

		/*
<div class="left" style="width: 100px;">
<aside>
<time><span class="date">{{ creation_date }}<span><br /><span class="time">{{ creation_time }}</span></time><p>
{{ author_img|class:author }}</p>
{{ social}}
</aside>
</div>
<div class="right" style="width:840px; padding-left:10px;">
<h1>{{ title|link }}</h1>
<p>{{ img|class:left }}</p>
{{ text }}
<aside>
<p>Letto {{ read }} volte | Commenti ({{ comments }}) | <span class="tags">Tags: {{ tags }}</span>
</p>
<p><a href="page/archive/">Torna all'archivio</a></p>
</aside>
</div>
<div class="null"></div>
		*/
		$registry = registry::instance();
		$registry->addCss($this->_class_www."/prettify.css");
		$registry->addJs($this->_class_www."/prettify.js");
		$registry->addCss($this->_class_www."/page.css");
		$registry->addJs($this->_class_www."/page.js");
		
		$slug = cleanVar($_GET, 'id', 'string', '');
		
		$item = pageEntry::getFromSlug($slug, $this);

		if(!$item || !$item->id || !$item->published) {
			error::raise404();
		}
		
		if(!$this->accessPage($item))
			return "<p>"._("I contenuti della pagina non sono disponibili")."</p>";
		
		$tpl_item = $item->tpl_code ? $item->tpl_code : $this->_entry_tpl_code;
		
		preg_match_all("#{{[^}]+}}#", $tpl_item, $matches);
		$tpl = $this->parseTemplate($item, $tpl_item, $matches);

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
					'author' => htmlChars($comment->author),
					'web' => htmlChars($comment->web),
					'text' => htmlChars(nl2br($comment->text)),
					'recursion' => $recursion,
					'reply' => $replyobj && $replyobj->id ? htmlChars($replyobj->author) : null,
					'avatar' => md5( strtolower(trim( $comment->email)))
				);
			}
		}
    	if(!$this->_session_user) {
    		$item->read = $item->read + 1;
    		$item->updateDbData();
    	}

		$view = new view($this->_view_dir);

		$view->setViewTpl('view');
		$view->assign('section_id', 'view_'.$this->_instanceName);
		$view->assign('tpl', $tpl);
		$view->assign('enable_comments', $item->enable_comments);
		$view->assign('form_comment', $form_comment);
		$view->assign('comments', $comments);
		$view->assign('url', $this->_plink->aLink($this->_instanceName, 'view', array('id'=>$item->slug)));

		return $view->render();
	}

	private function formComment($entry) {

		$myform = new form('form_comment', 'post', true, null);
		$myform->load('dataform');

		$buffer = '';

		if($this->_comment_moderation) {
			$buffer .= "<p>"._('Il tuo commento verrà sottoposto ad approvazione prima di essere pubblicato.')."</p>";
		}

		$buffer .= $myform->form($this->_plink->aLink($this->_instanceName, 'actionComment'), false, 'author,email', null);
		$buffer .= $myform->hidden('entry', $entry->id);
		$buffer .= $myform->hidden('form_reply', 0, array('id'=>'form_reply'));
		$buffer .= $myform->cinput('author', 'text', htmlInput($myform->retvar('author', '')), _('Nome'), array('size'=>40, 'maxlength'=>40, 'required'=>true));
		$buffer .= $myform->cinput('email', 'text', htmlInput($myform->retvar('email', '')), array(_('Email'), _('Non verrà pubblicata')), array('pattern'=>'^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$', 'hint'=>_('Inserire un indirizzo email valido'), 'size'=>40, 'maxlength'=>40, 'required'=>true));
		$buffer .= $myform->cinput('web', 'text', htmlInput($myform->retvar('web', '')), _('Sito web'), array('size'=>40, 'maxlength'=>40, 'required'=>false));
		$buffer .= $myform->ctextarea('text', htmlInput($myform->retvar('text', '')), array(_('Testo'), _('Non è consentito l\'utilizzo di alcun tag html')), array('cols'=>42, 'rows'=>8, 'required'=>true));
		$buffer .= $myform->cradio('notification', htmlInput($myform->retvar('notification', '')), array(1=>_('si'), 0=>_('no')), 0, _("Inviami un'email quando vengono postati altri commenti"), null);
		$buffer .= $myform->captcha();
		$buffer .= $myform->cinput('submit', 'submit', _('invia'), '', array('classField'=>'submit'));

		$buffer .= $myform->cform();

		return $buffer;
	}

	/**
	 * Pubblicazione/notifica di un commento 
	 * 
	 * @return void
	 */
	public function actionComment() {

		$myform = new form('form_comment', 'post', true, null);
		$myform->save('dataform');
		$req_error = $myform->arequired();

		$id = cleanVar($_POST, 'entry', 'int', '');
		$entry = new pageEntry($id, $this);

		if(!$entry or !$entry->id or !$entry->enable_comments) {
			error::raise404();
		}

		$link_error = SITE_WWW.'/'.$this->_plink->aLink($this->_instanceName, 'view', array('id'=>$entry->slug)).'#comments';
		
		if($req_error > 0) { 
			exit(error::errorMessage(array('error'=>1), $link_error));
		}
	
		if(!$myform->checkCaptcha()) {
			exit(error::errorMessage(array('error'=>_('Il codice inserito non è corretto')), $link_error));
		}

		$published = $this->_comment_moderation ? 0 : 1;

		$comment = new pageComment(null, $this);

		$comment->author = cleanVar($_POST, 'author', 'string', '');
		$comment->email = cleanVar($_POST, 'email', 'string', '');
		$comment->web = cleanVar($_POST, 'web', 'string', '');
		$comment->text = cutHtmlText(cleanVar($_POST, 'text', 'string', ''), 100000000, '', true, true, true, array());
		$comment->notification = cleanVar($_POST, 'notification', 'int', '');
		$comment->entry = $entry->id;
		$comment->datetime = date('Y-m-d H:i:s');
		$comment->reply = cleanVar($_POST, 'form_reply', 'int', '');
		$comment->published = $published;

		$comment->updateDbData();

		// send mail to publishers
		if(!$published) {

			$link = "http://".$_SERVER['HTTP_HOST'].SITE_WWW.'/'.$this->_plink->aLink($this->_instanceName, 'view', array("id"=>$entry->slug)).'#comment'.$comment->id;

			$admin = new admin('page', $this->_instance);
			$user_ids = $admin->listUserGroup($this->_list_group[1]);

			foreach($user_ids as $uid) {
				$email = $this->_db->getFieldFromId('user_app', 'email', 'user_id', $uid);
				if($email) {
					$subject = sprintf(_("Nuovo commento alla pagina \"%s\" in attesa di approvazione"), $entry->title);
					$object = sprintf("E' stato inserito un nuovo commento in fase di approvazione da %s il %s, clicca su link seguente (o copia ed incolla nella barra degli indirizzi) per visualizzarlo\r\n%s", $comment->author, $comment->datetime, $link);
					$from = "From: ".pub::variable('email_from_app');

					mail($email, $subject, $object, $from);
				}
			}
		}

		header('Location: '.SITE_WWW.'/'.$this->_plink->aLink($this->_instanceName, 'view', array('id'=>$entry->slug)).'#comments');
		exit();
	}

	/**
	 * Parserizzazione dei template inseriti da opzioni 
	 * 
	 * @param newsItem $entry istanza di @ref pageEntry
	 * @param string $tpl codice del template 
	 * @param array $matches matches delle variabili da sostituire
	 * @return template parserizzato
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
	 * Replace delle variabili del template 
	 * 
	 * @param string $property proprietà da sostituire
	 * @param string $filter filtro applicato
	 * @param newsItem $obj istanza di @ref pageEntry
	 * @return replace del parametro proprietà
	 */
	private function replaceTplVar($property, $filter, $obj) {

		$pre_filter = '';

		if($property == 'img') {
			if(!$obj->image) {
				return '';
			}
			$pre_filter = "<img src=\"".$obj->imgPath($this)."\" alt=\"img: ".jsVar($obj->ml('title'))."\" />";	
		}
		elseif($property == 'author_img') {
    		$user_image = $this->_db->getFieldFromId($this->_tbl_user, 'photo', 'user_id', $obj->author);
    		$user_name = $this->_db->getFieldFromId($this->_tbl_user, "CONCAT(firstname, ' ', lastname)", 'user_id', $obj->author);
			if(!$user_image) {
				return '';
			}
			$pre_filter = "<img src=\"".CONTENT_WWW."/user/img_".$user_image."\" alt=\"img: ".jsVar($user_name)."\" title=\"".jsVar($user_name)."\" />";	
		}
		elseif($property == 'creation_date' or $property == 'last_edit_date') {
			$pre_filter = date('d/m/Y', strtotime($obj->{$property}));
		}
    	elseif($property == 'creation_time') {
			$pre_filter = date('H:m', strtotime($obj->creation_date));
		}
		elseif($property == 'text' || $property == 'title') {
			$pre_filter = htmlChars($obj->ml($property));
		}
		elseif($property == 'read') {
			$pre_filter = $obj->read;
		}
		elseif($property == 'author') {
			$pre_filter = $this->_db->getFieldFromId($this->_tbl_user, "CONCAT(firstname, ' ', lastname)", 'user_id', $obj->author);
		}	
		elseif($property == 'tags') {
			$tagobjs = $obj->getTagObjects();
			$tags = array();
			foreach($tagobjs as $t) {
				$tags[] = '<a href="'.$this->_plink->aLink($this->_instanceName, 'archive', array('id'=>$t->name)).'">'.htmlChars($t->name).'</a>';
			}		
			$pre_filter = implode(', ', $tags);
		}
		elseif($property == 'social') {
			$pre_filter = shareAll('all', $this->_url_root.SITE_WWW."/".$this->_plink->aLink($this->_instanceName, 'view', array('id'=>$obj->slug)), htmlChars($obj->ml('title')));
		}
		elseif($property == 'comments') {
			if(!$obj->enable_comments) {
				return _('disabilitati');
			}
			$comments_num = pageComment::getCountFromEntry($obj->id);
			$pre_filter = '<a href="'.$this->_plink->aLink($this->_instanceName, 'view', array('id'=>$obj->slug)).'#comments">'.$comments_num.'</a>';
		}
		else {
			return '';
		}

		if(is_null($filter)) {
			return $pre_filter;
		}

		if($filter == 'link') {
			return "<a href=\"".$this->_plink->aLink($this->_instanceName, 'view', array('id'=>$obj->slug))."\">".$pre_filter."</a>";
		}
		elseif(preg_match("#chars:(\d+)#", $filter, $matches)) {
			return cutHtmlText($pre_filter, $matches[1], '...', false, false, true, array('endingPosition'=>'in'));
		}
		elseif(preg_match("#class:(.+)#", $filter, $matches)) {
			if(isset($matches[1]) && ($property == 'img' || $property == 'author_img')) {
				return preg_replace("#<img#", "<img class=\"".$matches[1]."\"", $pre_filter);
			}
			else return $pre_filter;
		}
		else {
			return $pre_filter;
		}
	}

	/**
	 * Interfaccia di amministrazione del modulo 
	 * 
	 * @return interfaccia di back office
	 */
	public function managePage() {

		$this->accessGroup($this->_group_2);
		
		$method = 'managePage';

		$htmltab = new htmlTab(array("linkPosition"=>'right', "title"=>_("Pagine")));
		$link_admin = "<a href=\"".$this->_home."?evt[$this->_instanceName-$method]&block=permissions\">"._("Permessi")."</a>";
		$link_css = "<a href=\"".$this->_home."?evt[$this->_instanceName-$method]&block=css\">"._("CSS")."</a>";
		$link_options = "<a href=\"".$this->_home."?evt[$this->_instanceName-$method]&block=options\">"._("Opzioni")."</a>";
		$link_comment = "<a href=\"".$this->_home."?evt[$this->_instanceName-$method]&block=comment\">"._("Commenti")."</a>";
		$link_tag = "<a href=\"".$this->_home."?evt[$this->_instanceName-$method]&block=tag\">"._("Tag")."</a>";
		$link_ctg = "<a href=\"".$this->_home."?evt[$this->_instanceName-$method]&block=ctg\">"._("Categorie")."</a>";
		$link_dft = "<a href=\"".$this->_home."?evt[".$this->_instanceName."-$method]\">"._("Contenuti")."</a>";

		$sel_link = $link_dft;

		if($this->_block == 'css') {
			$buffer = sysfunc::manageCss($this->_instance, $this->_className);		
			$sel_link = $link_css;
		}
		elseif($this->_block == 'permissions' && $this->_access->AccessVerifyGroupIf($this->_className, $this->_instance, '', '')) {
			$buffer = sysfunc::managePermissions($this->_instance, $this->_className);		
			$sel_link = $link_admin;
		}
		elseif($this->_block == 'options' && $this->_access->AccessVerifyGroupIf($this->_className, $this->_instance, '', '')) {
			$buffer = sysfunc::manageOptions($this->_instance, $this->_className);		
			$sel_link = $link_options;
		}
		elseif($this->_block == 'tag') {
			$buffer = $this->manageTag();		
			$sel_link = $link_tag;
		}
		elseif($this->_block == 'ctg') {
			$buffer = $this->manageCtg();		
			$sel_link = $link_ctg;
		}
		elseif($this->_block == 'comment') {
			$buffer = $this->manageComment();		
			$sel_link = $link_comment;
		}
		else {
			$buffer = $this->manageEntry();
		}

		// groups privileges
		if($this->_access->AccessVerifyGroupIf($this->_className, $this->_instance, '', '')) {
			$links_array = array($link_admin, $link_css, $link_options, $link_comment, $link_tag, $link_ctg, $link_dft);
		}
		elseif($this->_access->AccessVerifyGroupIf($this->_className, $this->_instance, $this->_user_group, $this->_group_1)) {
			$links_array = array($link_comment, $link_tag, $link_dft);
		}
		else $links_array = array($link_dft);

		$htmltab->navigationLinks = $links_array;
		$htmltab->selectedLink = $sel_link;
		$htmltab->htmlContent = $buffer;

		return $htmltab->render();
	}

	/**
	 * Interfaccia di amministrazione delle pagine 
	 * 
	 * @see pageTag::getAllList()
	 * @see pageEntryAdminTable::backOffice()
	 * @return interfaccia di back office delle pagine
	 */
	private function manageEntry() {
		
		$registry = registry::instance();
		$registry->addJs($this->_class_www.'/page.js');
		$registry->addJs($this->_class_www.'/MooComplete.js');
		$registry->addCss($this->_class_www.'/MooComplete.css');

		$name_onblur = "onblur=\"var date = new Date(); $('slug').value = date.getFullYear() + (date.getMonth().toInt() < 9 ? '0' + (date.getMonth() + 1).toString() : (date.getMonth() + 1).toString()) + (date.getDate().toString().length == 1 ? '0' + date.getDate().toString() : date.getDate().toString()) + '-' + $(this).value.slugify()\"";

		if(!$this->_access->AccessVerifyGroupIf($this->_className, $this->_instance, $this->_user_group, $this->_group_1)) {
			$remove_fields = array('author', 'published', 'private', 'users', 'read');
		}
		else {
			$remove_fields = array('author', 'read');
		}

		require_once('class.pageEntryAdminTable.php');

		$tags = pageTag::getAllList($this->_instance, array('jsescape'=>true));
		$js_tags_list = "['".implode("','", $tags)."']";
		
		$buffer = "<script type=\"text/javascript\">";
		$buffer .= "window.addEvent('load', function() {
			var tag_input = new MooComplete('tags', {
  				list: $js_tags_list, // elements to use to suggest.
  				mode: 'tag', // suggestion mode (tag | text)
  				size: 6 // number of elements to suggest
			});
		})";

		$buffer .= "</script>";
		
		$admin_table = new pageEntryAdminTable($this, array());
		
		$buffer .= $admin_table->backOffice(
			'pageEntry', 
			array(
				'list_display' => array('id', 'creation_date', 'title', 'slug', 'category_id', 'published', 'private'),
				'list_title'=>_("Elenco post"), 
				'filter_fields'=>array('title', 'category_id', 'tags', 'published')
				),
			array(
				'removeFields' => $remove_fields
				), 
			array(
				'category_id'=>array(
					'required'=>false
				), 
				'title'=>array(
					'js'=>$name_onblur
				),
				'slug'=>array(
					'id'=>'slug'
				),
				'tags'=>array(
					'id'=>'tags'
				),
				'text'=>array(
					'widget'=>'editor', 
					'notes'=>true, 
					'img_preview'=>true, 
				),
				'image'=>array(
					'preview'=>true
				),
				'tpl_code'=>array(
					'cols'=>40,
					'rows'=>10
				)
			)
		);

		return $buffer;
	}
	
	/**
	 * Interfaccia di amministrazione delle categorie
	 * 
	 * @return interfaccia di back office
	 */
	private function manageCtg() {
		
		$category = new category($this);
		$buffer = $category->backOffice('pageCategory', 
			array(
				'list_title'=>_("Elenco categorie"), 
				'list_description'=>'', 
				'filter_fields'=>array(), 
				'link'=>$this->_home."?evt[$this->_instanceName-managePage]", 
				'add_params_url'=>array('block'=>'ctg')
			)
		);
		
		return $buffer;
	}

	/**
	 * Interfaccia di amministrazione dei tag
	 * 
	 * @see pageEntry::getAssociatedTags()
	 * @return interfaccia di back office
	 */
	private function manageTag() {

		$associated_tags = pageEntry::getAssociatedTags($this);

		$admin_table = new adminTable($this, array('delete_deny' => $associated_tags, 'edit_deny'=>$associated_tags));

		$buffer = $admin_table->backOffice(
			'pageTag', 
			array(
				'list_display' => array('id', 'name'),
				'list_title'=>_("Elenco tag"), 
				'list_description'=>"<p>"._('I tag che compaiono in questo elenco saranno quelli proposti per l\'autocompletamento quando si inserisce una pagina. I tag inseriti in una pagina vengono automaticamente inseriti anche in questa tabella. I tag associati a pagine esistenti pubblicati non possono essere eliminati o modificati')."</p>",
			     ),
			array(), 
			array()
		);

		return $buffer;
	}

	/**
	 * Interfaccia di amministrazione dei commenti 
	 * 
	 * @return interfaccia di back office
	 */
	private function manageComment() {

		$admin_table = new adminTable($this, array());

		$buffer = $admin_table->backOffice(
			'pageComment', 
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
	 * Metodo per la definizione di parametri da utilizzare per il modulo "Ricerca nel sito"
	 *
	 * Il modulo "Ricerca nel sito" di Gino base chiama questo metodo per ottenere informazioni riguardo alla tabella, campi, pesi etc...
	 * per effettuare la ricerca dei contenuti.
	 *
	 * @access public
	 * @return array[string]mixed array associativo contenente i parametri per la ricerca
	 */
	public function searchSite() {
		
		return array(
			"table"=>pageEntry::$tbl_entry, 
			"selected_fields"=>array("id", "slug", "creation_date", array("highlight"=>true, "field"=>"title"), array("highlight"=>true, "field"=>"text")), 
			"required_clauses"=>array("instance"=>$this->_instance, "published"=>1), 
			"weight_clauses"=>array("title"=>array("weight"=>5), 'tags'=>array('weight'=>3), "text"=>array("weight"=>1))
		);
	}

	/**
	 * Definisce la presentazione del singolo item trovato a seguito di ricerca (modulo "Ricerca nel sito")
	 *
	 * @param mixed array array[string]string array associativo contenente i risultati della ricerca
	 * @access public
	 * @return void
	 */
	public function searchSiteResult($results) {
	
		$obj = new pageEntry($results['id'], $this);

		$buffer = "<div>".dbDatetimeToDate($results['creation_date'], "/")." <a href=\"".$this->_plink->aLink($this->_instanceName, 'view', array('id'=>$results['slug']))."\">";
		$buffer .= $results['title'] ? htmlChars($results['title']) : htmlChars($obj->ml('title'));
		$buffer .= "</a></div>";

		if($results['text']) {
			$buffer .= "<div class=\"search_text_result\">...".htmlChars($results['text'])."...</div>";
		}
		else {
			$buffer .= "<div class=\"search_text_result\">".htmlChars(cutHtmlText($obj->ml('text'), 120, '...', false, false, false, array('endingPosition'=>'in')))."</div>";
		}
		
		return $buffer;
	}

    /**
     * Adattatore per la classe newsletter 
     * 
     * @access public
     * @return array di elementi esportabili nella newsletter
     */
    public function systemNewsletterList() {
        
        $entries = pageEntry::get($this, array('order'=>'creation_date DESC', 'limit'=>array(0, $this->_newsletter_entries_number)));

        $items = array();
        foreach($entries as $entry) {
            $items[] = array(
                _('id') => $entry->id,
                _('titolo') => htmlChars($entry->ml('title')),
                _('pubblicato') => $entry->published ? _('si') : _('no'),
                _('data creazione') => dbDateToDate($entry->creation_date),
            ); 
        }

        return $items;
    }

    /**
     * Contenuto di un post quanto inserito in una newsletter 
     * 
     * @param int $id identificativo del post
     * @access public
     * @return contenuto post
     */
    public function systemNewsletterRender($id) {

        $entry = new pageEntry($id, $this);

        preg_match_all("#{{[^}]+}}#", $this->_newsletter_tpl_code, $matches);
        $buffer = $this->parseTemplate($entry, $this->_newsletter_tpl_code, $matches);

        return $buffer;
    }

	/**
	 * Genera un feed RSS standard che presenta gli ultimi 50 post pubblicati
	 *
	 * @access public
	 * @return string xml che definisce il feed RSS
	 */
	public function feedRSS() {

		$this->accessType($this->_access_base);

		header("Content-type: text/xml; charset=utf-8");

		$function = "feedRSS";
		$title_site = pub::variable('head_title');
		$title = $title_site.($this->_archive_title ? " - ".$this->_archive_title : "");
		$description = $this->_db->getFieldFromId(TBL_MODULE, 'description', 'id', $this->_instance);

		$header = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
		$header .= "<rss version=\"2.0\" xmlns:atom=\"http://www.w3.org/2005/Atom\">\n";
		$header .= "<channel>\n";
		$header .= "<atom:link href=\"".$this->_url_root.$this->_home."?pt%5B$this->_instanceName-".$function."%5D\" rel=\"self\" type=\"application/rss+xml\" />\n";
		$header .= "<title>".$title."</title>\n";
		$header .= "<link>".$this->_url_root.$this->_home."</link>\n";
		$header .= "<description>".$description."</description>\n";
		$header .= "<language>$this->_lng_nav</language>";
		$header .= "<copyright> Copyright 2012 Otto srl </copyright>\n";
		$header .= "<docs>http://blogs.law.harvard.edu/tech/rss</docs>\n";

		echo $header;

		$entries = pageEntry::get($this, array('published'=>true, 'order'=>'creation_date DESC', 'limit'=>array(0, 50)));
		if(count($entries) > 0) {
			foreach($entries as $entry) {
				$id = htmlChars($entry->id);
				$title = htmlChars($entry->ml('title'));
				$text = htmlChars($entry->ml('text'));
				$text = str_replace("src=\"", "src=\"".substr($this->_url_root,0,strrpos($this->_url_root,"/")), $text);
				$text = str_replace("href=\"", "href=\"".substr($this->_url_root,0,strrpos($this->_url_root,"/")), $text);

				$date = date('d/m/Y', strtotime($entry->creation_date));

				echo "<item>\n";
				echo "<title>".$date.". ".$title."</title>\n";
				echo "<link>".$this->_url_root.SITE_WWW."/".$this->_plink->aLink($this->_instanceName, 'view', array("id"=>$entry->slug))."</link>\n";
				echo "<description>\n";
				echo "<![CDATA[\n";
				echo $text;
				echo "]]>\n";
				echo "</description>\n";
				echo "<guid>".$this->_url_root.SITE_WWW.$this->_plink->aLink($this->_instanceName, 'view', array("id"=>$entry->slug))."</guid>\n";
				echo "</item>\n";
			}
		}

		$footer = "</channel>\n";
		$footer .= "</rss>\n";

		echo $footer;
		exit;
	}
}
?>