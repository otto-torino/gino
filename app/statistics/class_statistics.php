<?php
/**
 * @file class_statistics.php
 * @brief Contiene la classe statistics
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

require_once('class.LogAccess.php');

/**
 * @brief Statistiche degli accessi all'area privata
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class statistics extends Controller {
	
	private $_tbl_log_access;
	private $_block;
		
	function __construct() {
		
		parent::__construct();

		$this->_tbl_log_access = TBL_LOG_ACCESS;
		$this->_block = cleanVar($_REQUEST, 'block', 'string', '');

	}
	
	/**
	 * Interfaccia alla visualizzazione delle statistiche
	 * 
	 * @see $_access_admin
	 * @return string
	 */
	public function manageStatistics() {
		
		$this->requirePerm('can_admin');
		
    $link_dft = "<a href=\"".$this->_home."?evt[".$this->_class_name."-manageStatistics]\">"._("Informazioni")."</a>";
    $link_log_access = "<a href=\"".$this->_home."?evt[".$this->_class_name."-manageStatistics]&block=log_access\">"._("Accessi area privata")."</a>";
    $sel_link = $link_dft;

    if($this->_block == 'log_access') {
      $GINO = $this->viewLogAccess();
      $sel_link = $link_log_access;
    }
    else {
      $GINO = $this->info();
    }

    $view = new view();
    $view->setViewTpl('tab');
    $dict = array(
      'title' => _('Centro statistiche'),
      'links' => array($link_log_access, $link_dft),
      'selected_link' => $sel_link,
      'content' => $GINO
    );
    return $view->render($dict);

	}
	
	/**
	 * Intestazione delle statistiche sugli accessi all'area privata
	 * 
	 * @return string
	 */
	private function viewlogAccess() {

    loader::import('auth', 'User');
	
		$link_export = "<a href=\"$this->_home?evt[$this->_class_name-export]\">".pub::icon('export', array('text' => 'esporta log completo', 'scale' => 2))."</a>";

    $users = User::get(array('where' => "active='1'", 'order'=>'lastname, firstname'));
    $view_table = new view();
    $view_table->setViewTpl('table');
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
        htmlChars($user->lastname.' '.$user->firstname),
        $tot_accesses,
        dbDatetimeToDate($last_access->date).' '.dbDatetimeToTime($last_access->date)
      );
    }
    $view_table->assign('rows', $tbl_rows);
    $table = $view_table->render();

    $view = new view();
    $view->setViewTpl('section');
    $dict = array(
      'title' => _('log accesso utenti registrati'),
      'class' => 'admin',
      'header_links' => $link_export,
      'content' => $table
    );

    return $view->render($dict);
	}
	
	private function info(){

		$log_access = $this->_registry->sysconf->log_access;

		$GINO = '';
		if(!$log_access) {
			$GINO .= "<p class=\"lead\">".sprintf(_("Attenzione, attualmente il log degli accessi è disattivato. Per attivarlo modificare il settaggio dalle %sImpostazioni di sistema%s."), "<a href=\"".$this->_home."?evt[sysconf-manageSysconf]\">", "</a>")."</p>\n";
    }

		$GINO .= "<dl>";
		$GINO .= "<dt>"._("Accessi area privata")."</dt>";
		$GINO .= "<dd>"._("Resoconto degli accessi al sistema (sito principale e sito secondario) da parte degli utenti registrati, con numero totale di accessi, data e ora dell'ultimo accesso effettuato.")."</dd>";
		$GINO .= "</dl>\n";

    $view = new view();
    $view->setViewTpl('section');
    $dict = array(
      'title' => _('Informazioni'),
      'class' => 'admin',
      'content' => $GINO
    );

    return $view->render($dict);
	}

	/**
	 * Esportazione delle statistiche sugli accessi all'area privata
	 * 
	 * @see $_access_admin
	 * @return file
	 */
	public function export() {
	
		$this->requirePerm('can_admin');

    Loader::import('auth', 'User');
    $export = Loader::load('Export');

		$data = array();
		$data[0] = array(_("id"), _("cognome"), _("nome"), _("data"), _("ora"));

    $logs = LogAccess::get(array('order' => 'date DESC'));
    foreach($logs as $log) {
      $user = new User($log->user_id);
      $data[] = array(
        $log->id,
        $user->lastname,
        $user->firstname,
        dbDatetimeToDate($log->date),
        dbDatetimeToTime($log->date)
      );
    }

		$export->setData($data);

		$filename = "log_access_".date("YmdHis").".csv";
		ob_clean();
		$export->exportData($filename, 'csv');
	}
}
?>
