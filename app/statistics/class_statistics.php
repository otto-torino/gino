<?php
/**
 * @file class_statistics.php
 * @brief Contiene la definizione ed implementazione della classe statistics
 *
 * @copyright 2005-2018 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

/**
 * @namespace Gino.App.Statistics
 * @description Namespace dell'applicazione Statistics, che gestisce le statistiche di accesso all'area privata
 */
namespace Gino\App\Statistics;

use \Gino\Loader;
use \Gino\View;
use \Gino\Document;
use \Gino\Http\Response;

require_once('class.LogAccess.php');

/**
 * @brief Gestisce le statistiche del sito: accessi all'area privata e statistiche Google Analytics
 *
 * @copyright 2005-2018 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 * 
 * ##LIBRERIA GAPI
 * L'accesso alle statistiche Google Analytics viene gestito dalla libreria GAPI (Google Analytics PHP5 Interface).
 * Con GAPI, quando i dati vengono restituiti da Google, vengono automaticamente convertiti in un oggetto PHP nativo, 
 * con un'interfaccia che consente di "ottenere" il valore di qualsiasi dimensione o metrica. \n
 * In gino si utilizza la libreria GAPI per ottenere il token di autorizzazione.
 * 
 * L'indirizzo della libreria è @link https://github.com/erebusnz/gapi-google-analytics-php-interface.
 * 
 * ##PROCEDURA DI ATTIVAZIONE DEL SERVIZIO
 * GAPI (poiché ora le API di Google Analytics supportano solo OAuth2) richiede di creare un account di servizio (service account) 
 * e quindi di scaricare un file .P12 da caricare nell'applicazione.
 * 
 * ###Prerequisiti
 * Per poter accedere alla visualizzazione delle statistiche di Google Analytics occorre creare un account gmail 
 * e con questo account attivare un account per il dominio servito da gino (@link https://analytics.google.com/analytics/web):
 * Admin -> Account -> Create new account
 * 
 * In Admin -> Property Settings dell'account del dominio recuperare il valore di 'Tracking Id'; questo valore deve essere salvato 
 * nelle Impostazioni di gino nel campo 'codice google analytics'.
 * 
 * ###Creare un progetto Google Developers
 * Creare o selezionare un progetto già preesistente @link https://console.developers.google.com/cloud-resource-manager
 * 
 * ###Creare un service account sotto questo progetto
 * Gestore API -> Credenziali -> Crea Credenziali -> Chiave account di servizio -> Nuovo account di servizio
 * Ad esempio creare l'account 'domain-analytics' con tipo di chiave P12
 * 
 * oppure
 * 
 * IAM e amministrazione -> Account di servizio -> Crea account di servizio
 * e una volta creato, nelle opzioni selezionare 'Crea chiave' P12
 * 
 * ###Scaricare il file .p12 di questo service account
 * Spostare il file nella stessa directory del file gapi.class.php
 * 
 * ###Abilitare 'Analytics API' nella console Google Developers
 * Gestore API -> Libreria -> selezionare 'Analytics API' -> Abilita
 * 
 * ###Impostare Google Analytics
 * Nell'interfaccia amministrativa di Google Analytics selezionare l'account (dominio) da agganciare 
 * @link https://analytics.google.com/analytics/web/
 * 
 * Admin -> User Management -> Add permissions for ->
 * inserire il valore di 'ID account di servizio' (formato email) creato precedentemente e come permessi lasciare 'Read and Analyse'.
 * 
 * ###Recuperare il valore di visualizzazione delle statistiche
 * In Google Analytics, in Admin -> View Settings, recuperare il valore di 'View ID'.
 * 
 * ###Impostare gino
 * Nel file configuration.php impostare i valori delle costanti:
 * - GOOGLE_ANALYTICS_VIEW_FILE, nome del file della chiave dell'account di servizio (.p12)
 * - GOOGLE_ANALYTICS_VIEW_ACCOUNT, ID dell'account di servizio (valore formato email)
 * - GOOGLE_ANALYTICS_VIEW_ID, View ID di Google Analytics
 * 
 * ##DOCUMENTAZIONE
 * Documentazione può essere reperita ai seguenti indirizzi:
 * - Overview of the Google Analytics Embed API @link https://developers.google.com/analytics/devguides/reporting/embed/v1/
 * - Reference @link https://developers.google.com/analytics/devguides/reporting/embed/v1/component-reference
 * 
 */
class statistics extends \Gino\Controller {

	/**
	 * @brief nome della tabella dei log di accesso
	 * @var string
	 */
    private $_tbl_log_access;
    
    /**
     * @brief Percorso assoluto alla directory della libreria GAPI
     * @var string
     */
    private $_ga_lib_dir;
    
    /**
     * @brief Percorso assoluto alla chiave GAPI
     * @var string
     */
    private $_ga_file_key;

    /**
     * @brief Costruttore
     * @return void, istanza di Gino.App.Statistics.statistics
     */
    function __construct() {

        parent::__construct();
        
        $this->_tbl_log_access = TBL_LOG_ACCESS;
        $this->_ga_lib_dir = LIB_DIR."/gapi-google-analytics-php-interface/";
        $this->_ga_file_key = $this->_ga_lib_dir.GOOGLE_ANALYTICS_VIEW_FILE;
    }
    
    /**
     * @brief Restituisce alcune proprietà della classe
     * @return array, lista delle proprietà dell'applicazione
     */
    public static function getClassElements() {
    
    	return array(
    		"tables"=>array(
    			'sys_log_access',
    		),
    		"css"=>array(
    			'statistics.css'
    		),
    		'views' => array(
    			'stats' => _("Visualizzazione delle statistiche"),
    			'analytics.php' => _("Visualizzazione delle statistiche Google Analytics"),
    		),
    		"folderStructure" => array ()
    	);
    }

    /**
     * @brief Interfaccia di visualizzazione delle statistiche
     * 
     * @param \Gino\Http\Request $request istanza di Gino.Http.Request
     * @return Gino.Http.Response
     */
    public function manageStatistics(\Gino\Http\Request $request) {

        $this->requirePerm('can_admin');

        $block = \Gino\cleanVar($request->GET, 'block', 'string', '');

        $link_dft = ['link' => $this->linkAdmin(), 'label' => _('Analytics')];
        $link_log_access = ['link' => $this->linkAdmin(array(), 'block=log_access'), 'label' => _('Accessi area privata')];
        $sel_link = $link_dft;

        if($block == 'log_access') {
            $backend = $this->viewLogAccess();
            $sel_link = $link_log_access;
        }
        else {
            $backend = $this->stats($request);
        }

        if(is_a($backend, '\Gino\Http\Response')) {
            return $backend;
        }

        $view = new View();
        $view->setViewTpl('tabs');
        $dict = array(
            'title' => _('Centro statistiche'),
            'links' => array($link_log_access, $link_dft),
            'selected_link' => $sel_link,
            'content' => $backend
        );

        $document = new Document($view->render($dict));
        return $document();
    }

    /**
     * @brief Statistiche sugli accessi all'area privata
     * @return string
     */
    private function viewlogAccess() {

        Loader::import('auth', 'User');

        $link_export = sprintf('<a href="%s">%s</a>', $this->link($this->_class_name, 'export'), \Gino\icon('export', array('text' => _('esporta log completo'), 'scale' => 2)));

        $users = \Gino\App\Auth\User::objects(null, array('where' => "active='1'", 'order'=>'lastname, firstname'));
        $view_table = new View(null, 'table');
        $view_table->assign('class', 'table table-striped table-hover');
        $heads = array(
            _('utente'),
            _('accessi totali'),
            _('ultimo accesso')
        );
        $view_table->assign('heads', $heads);

        $tbl_rows = array();
        foreach($users as $user) {
            $tot_accesses = LogAccess::getCountForUser($user->id);
            $last_access = LogAccess::getLastForUser($user->id);

            $tbl_rows[] = array(
                \Gino\htmlChars((string) $user),
                $tot_accesses,
                is_null($last_access) ? '' : \Gino\dbDatetimeToDate($last_access->date).' '.\Gino\dbDatetimeToTime($last_access->date)
            );
        }
        $view_table->assign('rows', $tbl_rows);
        $table = $view_table->render();

        $view = new View(null, 'section');
        $dict = array(
            'title' => _('Log accesso utenti registrati'),
            'class' => 'admin',
            'header_links' => $link_export,
            'content' => $table
        );

        return $view->render($dict);
    }

    /**
     * @brief Pagina statistiche
     * 
     * @param $request object Gino.Http.Request
     * @return string
     */
    private function stats(\Gino\Http\Request $request){

    	$this->_registry->addCss($this->_class_www."/statistics.css");
    	
    	if(!$this->checkGaKey()) {
    		
    		$buffer = "<p>"._("Per attivare la visualizzazione di alcune statistiche di Google Analytics occorre impostare un account di servizio in un progetto di <i>Google Developers</i> e attivare questo account in <i>Google Analytics</i>.")."</p>";
    		$buffer .= "<p>".sprintf(_("Seguire la procedura descritta nel file %s ed inpostare i valori delle apposite costanti nel file %s."), "app/statistics/class_statistics.php", "configuration.php")."</p>";
    		return $buffer;
    	}
    	
    	$log_access = $this->_registry->sysconf->log_access;

        $alert = null;
        if(!$log_access) {
        	$link_conf = $this->link('sysconf', 'manageSysconf');
            $alert = "<p class=\"lead\">".sprintf(_("Attenzione, attualmente il log degli accessi è disattivato. Per attivarlo modificare il settaggio dalle %sImpostazioni di sistema%s."), "<a href=\"".$link_conf."\">", "</a>")."</p>\n";
        }
        
        $view = new View($this->_view_dir, 'stats');
        $dict = array(
            'alert' => $alert,
        	'stats' => $this->getGaStats($request),
        );
        
        return $view->render($dict);
    }
    
    /**
     * @brief Input select del periodo di visualizzazione delle statistiche di Google Analytics
     * @param string $value
     * @return string
     */
    private function selectDate($value) {
    	
    	if(!$value) {
    		$value = '15daysAgo';
    	}
    	$data = array(
    		"15daysAgo" => _("last 15 days"),
    		"30daysAgo" => _("last month"),
    		"90daysAgo" => _("last three months"),
    		"365daysAgo" => _("last year"),
    	);
    	
    	$url = $this->link($this->_instance_name, 'manageStatistics');
    	$onchange = "document.location.href = '$url?timedelta='+$(this).value";
    	
    	$select = \Gino\Input::select('timedelta', $value, $data, array(
    		'firstVoice' => null,
    		'firstValue' => null,
    		'js' => "onchange=\"$onchange\"",
    		'id' => 'timedelta',
    		'disabled' => false
    	));
    	
    	return $select;
    }
    
    /**
     * @brief Label del periodo di visualizzazione delle statistiche di Google Analytics
     * @param string $key
     * @return string|NULL
     */
    private function timedeltaLabel($key) {
    	
    	$data = array(
    		"15daysAgo" => _("last 15 days"),
    		"30daysAgo" => _("last month"),
    		"90daysAgo" => _("last three months"),
    		"365daysAgo" => _("last year"),
    	);
    	if(array_key_exists($key, $data)) {
    		return $data[$key];
    	}
    	else {
    		return null;
    	}
    }
    
    /**
     * @brief Verifica l'esistenza del file con la chiave dell'account di servizio
     * @return boolean
     */
    private function checkGaKey() {
    	
    	if(is_file($this->_ga_file_key)) {
    		return true;
    	}
    	else {
    		return false;
    	}
    }
    
    /**
     * @brief Pagina di informazioni Google Analytics
     * 
     * @see https://github.com/erebusnz/gapi-google-analytics-php-interface
     * @param $request object Gino.Http.Request
     * @return string
     */
    private function getGaStats(\Gino\Http\Request $request) {
    	
    	// Filters
    	$timedelta = \Gino\cleanVar($request->GET, 'timedelta', 'string');	// ajax -> POST
    	
    	if(!$timedelta) {
    		$timedelta = '15daysAgo';
    	}
    	// /Filters
    	
    	require_once(LIB_DIR.'/gapi-google-analytics-php-interface/gapi.class.php');
    	
    	$ga = new \gapi(GOOGLE_ANALYTICS_VIEW_ACCOUNT, $this->_ga_file_key);
    	$token = $ga->getToken();
    	
    	$view = new View($this->_view_dir, 'analytics');
    	$dict = array(
    		'token' => $token,
    		'ga_view_id' => GOOGLE_ANALYTICS_VIEW_ID,
    		'ga' => $ga,
    		'start_date' => $timedelta,
    		'end_date' => 'yesterday',
    		'filter' => $this->selectDate($timedelta),
    		'label' => $this->timedeltaLabel($timedelta),
    	);
    	
    	return $view->render($dict);
    }

    /**
     * @brief Esportazione delle statistiche sugli accessi all'area privata
     * @return Gino.Http.ResponseFile
     */
    public function export() {

        $this->requirePerm('can_admin');

        $export = \Gino\Loader::load('Export');

        $data = array();
        $data[0] = array(_("id"), _("cognome"), _("nome"), _("data"), _("ora"));

        $logs = LogAccess::objects(null, array('order' => 'date DESC'));
        foreach($logs as $log) {
            $user = new \Gino\App\Auth\User($log->user_id);
            $data[] = array(
                $log->id,
                $user->lastname,
                $user->firstname,
                \Gino\dbDatetimeToDate($log->date),
                \Gino\dbDatetimeToTime($log->date)
            );
        }

        $export->setData($data);

        $filename = "log_access_".date("YmdHis").".csv";
        return $export->exportData($filename, 'csv');
    }
}
