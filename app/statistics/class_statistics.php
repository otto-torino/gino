<?php
/**
 * @file class_statistics.php
 * @brief Contiene la definizione ed implementazione della classe statistics
 *
 * @copyright 2005-2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
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
 * @brief Statistiche degli accessi all'area privata
 *
 * @copyright 2005-2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class statistics extends \Gino\Controller {

    private $_tbl_log_access;

    /**
     * @brief Costruttore
     * @return istanza di Gino.App.Statistics.statistics
     */
    function __construct() {

        parent::__construct();
        $this->_tbl_log_access = TBL_LOG_ACCESS;
    }

    /**
     * @brief Interfaccia di visualizzazione delle statistiche
     * @param \Gino\Http\Request $request istanza di Gino.Http.Request
     * @return Gino.Http.Response
     */
    public function manageStatistics(\Gino\Http\Request $request) {

        $this->requirePerm('can_admin');

        $block = \Gino\cleanVar($request->GET, 'block', 'string', '');

        $link_dft = sprintf('<a href="%s">%s</a>', $this->linkAdmin(), _('Informazioni'));
        $link_log_access = sprintf('<a href="%s">%s</a>', $this->linkAdmin(array(), 'block=log_access'), _('Accessi area privata'));
        $sel_link = $link_dft;

        if($block == 'log_access') {
            $backend = $this->viewLogAccess();
            $sel_link = $link_log_access;
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
     * @return html, statistiche
     */
    private function viewlogAccess() {

        Loader::import('auth', 'User');

        $link_export = sprintf('<a href="%s">%s</a>', $this->link($this->_class_name, 'export'), \Gino\icon('export', array('text' => 'esporta log completo', 'scale' => 2)));

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
     * @brief Pagina di informazioni
     * @return html, informazioni
     */
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

        $view = new View(null, 'section');
        $dict = array(
            'title' => _('Informazioni'),
            'class' => 'admin',
            'content' => $GINO
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
