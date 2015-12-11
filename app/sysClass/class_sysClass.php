<?php
/**
 * @file class_sysClass.php
 * @brief Contiene la definizione ed implementazione della classe Gino.App.SysClass.sysClass
 *
 * @copyright 2005-2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

/**
 * @namespace Gino.App.SysClass
 * @description Namespace dell'applicazione SysClass, che gestisce l'installazione/upgrade/rimozionie di classi di sistema
 */
namespace Gino\App\SysClass;

use \Gino\Error;
use \Gino\Loader;
use \Gino\View;
use \Gino\Document;
use \Gino\App\Module\ModuleInstance;
use \Gino\Http\Response;
use \Gino\Http\Redirect;

require_once('class.ModuleApp.php');

/**
 * @brief Classe di tipo Gino.Controller per la gestione dei moduli di sistema
 *
 * @copyright 2005-2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class sysClass extends \Gino\Controller {

    const PACKAGE_EXTENSION = 'zip';

    private $_title;

    /**
     * @brief Costruttore
     * @return istanza di Gino.App.SysClass.sysclass
     */
    function __construct(){

        parent::__construct();
        $this->_title = _("Gestione classi di sistema");
    }

    /**
     * @brief Interfaccia amministrativa per la gestione dei moduli di sistema
     * @param \Gino\Http\Request $request istanza di Gino.Http.Request
     * @return Gino.Http.Response
     */
    public function manageSysClass(\Gino\Http\Request $request) {

        $this->requirePerm('can_admin');

        $id = \Gino\cleanVar($request->GET, 'id', 'int', '');
        $block = \Gino\cleanVar($request->GET, 'block', 'string', null);

        $link_dft = "<a href=\"".$this->_home."?evt[".$this->_class_name."-manageSysClass]\">"._("Informazioni")."</a>";
        $link_list = "<a href=\"".$this->_home."?evt[".$this->_class_name."-manageSysClass]&block=list\">"._("Gestione moduli installati")."</a>";
        $link_install = "<a href=\"".$this->_home."?evt[".$this->_class_name."-manageSysClass]&block=install\">"._("Installazione pacchetto")."</a>";
        $link_minstall = "<a href=\"".$this->_home."?evt[".$this->_class_name."-manageSysClass]&block=minstall\">"._("Installazione manuale")."</a>";
        $sel_link = $link_dft;

        if($block == 'list') {
            $action = \Gino\cleanVar($request->GET, 'action', 'string', null);
            if($request->checkGETKey('trnsl', '1')) {
                return $this->_trd->manageTranslation($request);
            }
            elseif($action == 'modify') {
                $backend = $this->formEditSysClass($id);
                $backend .= $this->formUpgradeSysClass($id);
                $backend .= $this->formActivateSysClass($id);
            }
            elseif($action == 'delete') {
                $backend = $this->formRemoveSysClass($id);
            }
            else {
                $backend = $this->sysClassList();
            }
            $sel_link = $link_list;
        }
        elseif($block == 'install') {
            $backend = $this->formInsertSysClass();
            $sel_link = $link_install;
        }
        elseif($block == 'minstall') {
            $backend = $this->formManualSysClass();
            $sel_link = $link_minstall;
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
            'title' => _('Moduli di sistema'),
            'links' => array($link_minstall, $link_install, $link_list, $link_dft),
            'selected_link' => $sel_link,
            'content' => $backend
        );

        $document = new Document($view->render($dict));
        return $document();
    }

    /**
     * @brief Elenco dei moduli di sistema
     *
     * @param integer $sel_id valore ID del modulo selezionato
     * @return html, elenco moduli
     */
    private function sysClassList() {

        $view_table = new View();
        $view_table->setViewTpl('table');

        $modules_app = ModuleApp::objects(null);
        if(count($modules_app)) {
            $GINO = "<p class=\"backoffice-info\">"._('Di seguito l\'elenco di tutti i moduli installati sul sistema. Cliccare l\'icona di modifica per cambiare l\'etichetta e la descrizione del modulo, effettuare un upgrade o cambiare lo stato di attivazione.    In caso di eliminazione di un modulo istanziabile verranno eliminate anche tutte le sue istanze.')."</p>";
            $heads = array(
                _('id'),
                _('etichetta'),
                _('istanziabile'),
                _('attivo'),
                _('versione'),
                '',
            );
            $tbl_rows = array();
            foreach($modules_app as $module_app) {

                $link_modify = "<a href=\"$this->_home?evt[$this->_class_name-manageSysClass]&block=list&id=".$module_app->id."&action=modify\">".\Gino\icon('modify', array('text'=>_("modifica/upgrade")))."</a>";
                $link_delete = $module_app->removable ? "<a href=\"$this->_home?evt[$this->_class_name-manageSysClass]&block=list&id=".$module_app->id."&action=delete\">".\Gino\icon('delete', array('text'=>_("elimina")))."</a>" : "";

                $tbl_rows[] = array(
                    $module_app->id,
                    $module_app->ml('label'),
                    $module_app->instantiable ? _('si') : _('no'),
                    $module_app->active ? _('si') : _('no'),
                    $module_app->class_version,
                    implode(' &#160; ', array($link_modify, $link_delete))
                );
            }
            $dict = array(
                'class' => 'table table-striped table-hover',
                'heads' => $heads,
                'rows' => $tbl_rows
            );
            $GINO .= $view_table->render($dict);
        }
        else {
            $GINO = _('Non risultano moduli di sistema');
        }

        $view = new View();
        $view->setViewTpl('section');
        $dict = array(
            'title' => _('Elenco moduli installati'),
            'class' => 'admin',
            'content' => $GINO
        );

        return $view->render($dict);
    }

    /**
     * @brief Form di installazione di un modulo di sistema
     * @return html, form
     */
    private function formInsertSysClass() {

        $gform = Loader::load('Form', array());
        $gform->load('dataform');

        $GINO = "<p class=\"backoffice-info\">"._("Caricare il pacchetto del modulo. Se la procedura di installazione va a buon fine modificare il modulo appena inserito per personalizzarne l'etichetta ed eventualmente altri parametri.")."</p>\n";

        $required = 'archive';
        $GINO .= $gform->open($this->_home."?evt[".$this->_class_name."-actionInsertSysClass]", true, $required);
        $GINO .= \Gino\Input::input_file('archive', '', _("Archivio"), array("extensions"=>array(self::PACKAGE_EXTENSION), "del_check"=>false, "required"=>true));
        $GINO .= \Gino\Input::input_label('submit_action', 'submit', _("installa"), '', array("classField"=>"submit"));
        $GINO .= $gform->close();

        $view = new View();
        $view->setViewTpl('section');
        $dict = array(
            'title' => _("Installazione modulo di sistema"),
            'class' => 'admin',
            'content' => $GINO
        );
        return $view->render($dict);
    }

    /**
     * @brief Processa il form di installazione modulo di sistema
     * @description Il modulo viene attivato durtante l'installazione
     * @see self::formInsertSysClass()
     * @param \Gino\Http\Request $request istanza di Gino.Http.Request
     * @return Gino.Http.Redirect
     */
    public function actionInsertSysClass(\Gino\Http\Request $request) {

        $this->requirePerm('can_admin');

        $link_error = $this->linkAdmin(array(), array('block' => 'install'));
        if(!\Gino\enabledZip()) {
            return Error::errorMessage(array('error'=>_("la classe ZipArchive non è supportata"), 'hint'=>_("il pacchetto deve essere installato con procedura manuale")), $link_error);
        }

        $archive_name = $request->FILES['archive']['name'];
        $archive_tmp = $request->FILES['archive']['tmp_name'];

        if(empty($archive_tmp)) {
            return Error::errorMessage(array('error'=>_("file mancante"), 'hint'=>_("controllare di aver selezionato un file")), $link_error);
        }

        $class_name = preg_replace("/[^a-zA-Z0-9].*?.zip/", "", $archive_name);
        if(preg_match("/[\.\/\\\]/", $class_name)) {
            return Error::errorMessage(array('error'=>_("pacchetto non conforme alle specifiche")), $link_error);
        }

        /*
         * dump db
         */
        $this->_db->dumpDatabase(SITE_ROOT.OS.'backup'.OS.'dump_'.date("d_m_Y_H_i_s").'.sql');

        $class_dir = APP_DIR.OS.$class_name;
        if(!@mkdir($class_dir, 0755))
        	return Error::errorMessage(array('error'=>_("impossibile creare la cartella base del modulo"), 'hint'=>_("controllare i permessi di scrittura")), $link_error);

        /*
         * Extract archive
         */
        $uploadfile = $class_dir.OS.$archive_name;
        if(move_uploaded_file($archive_tmp, $uploadfile)) $up = 'ok';
        else $up = 'ko';

        $zip = new \ZipArchive;
        $res = $zip->open($uploadfile);
        if($res === true) {
            $zip->extractTo($class_dir);
            $zip->close();
        }
        else {
            \Gino\deleteFileDir($class_dir, true);
            return Error::errorMessage(array('error'=>_("impossibile scompattare il pacchetto")), $link_error);
        }
        
        /*
         * Parsering config file
         */
        if(!is_readable($class_dir.OS."config.txt")) {
            \Gino\deleteFileDir($class_dir, true);
            return Error::errorMessage(array('error'=>_("pacchetto non conforme alle specifiche. File di configurazione mancante.")), $link_error);
        }

        $config = file_get_contents($class_dir.OS."config.txt");
        $config = preg_replace("/\/\*(.|\n)*?\*\/\n/", "", $config);

        $db_conf = array('name'=>$class_name, 'version'=>null, 'tbl_name'=>null, 'instantiable'=>0, 'description'=>null, 'removable'=>1, 'folders'=>null);

        $config_params = explode(",", $config);
        foreach($config_params AS $cp) {
            preg_match("/^(\w+?):\"(.*?)\"/", $cp, $matches);
            if(array_key_exists($matches[1], $db_conf)) {
                $db_conf[$matches[1]]=$matches[2];
            }
        }

        /*
         * Check $db_conf elements
         */
        $dbConfError = FALSE;
        if($db_conf['name'] != $class_name) $dbConfError = true;
        if(!in_array($db_conf['instantiable'], array('1', '0'))) $dbConfError = true;
        if(!in_array($db_conf['removable'], array('1', '0'))) $dbConfError = true;
        if($dbConfError) {
            \Gino\deleteFileDir($class_dir, TRUE);
            return Error::errorMessage(array('error'=>_("pacchetto non conforme alle specifiche.")), $link_error);
        }
        // name check
        $res = ModuleApp::objects(null, array('where' => "name='".$db_conf['name']."'"));
        if($res and count($res)) {
            return Error::errorMessage(array('error'=>_("modulo con lo stesso nome già presente nel sistema")), $link_error);
        }

        /*
         * Insert DB record
         */
        $module_app = new ModuleApp(null);
        $module_app->label = $db_conf['name'];
        $module_app->name = $db_conf['name'];
        $module_app->active = 1;
        $module_app->tbl_name = $db_conf['tbl_name'];
        $module_app->instantiable = $db_conf['instantiable'];
        $module_app->description = $db_conf['description'];
        $module_app->removable = $db_conf['removable'];
        $module_app->class_version = $db_conf['version'];

        $result = $module_app->save();

        if(!$result) {
            \Gino\deleteFileDir($class_dir, true);
            return Error::errorMessage(array('error'=>_("impossibile installare il pacchetto")), $link_error);
        }

        /*
         * Create contents folders
         */
        $created_flds = array();
        if($db_conf['folders']!=null) {
            $folders = explode(";", $db_conf['folders']);
            foreach($folders as $fld) {
                trim($fld);
                if(@mkdir(SITE_ROOT.OS.$fld, 0755)) {
                    $created_flds[] = SITE_ROOT.OS.$fld;
                }
                else {
                    \Gino\deleteFileDir($class_dir, true);
                    $module_app->deleteDbData();
                    foreach(array_reverse($created_flds) as $created_fld) {
                        \Gino\deleteFileDir($created_fld, true);
                    }
                    return Error::errorMessage(array('error'=>_("impossibile creare le cartelle dei contenuti"), 'hint'=>_("controllare i permessi di scrittura")), $link_error);
                }
            }
        }

        /*
         * Exec sql statements
         */
        if(is_readable($class_dir.OS.$class_name.".sql")) {
            $sql = file_get_contents($class_dir.OS.$class_name.".sql");
            $res = $this->_db->multiActionquery($sql);
            if(!$res) {
                \Gino\deleteFileDir($class_dir, true);
                $module_app->deleteDbData();
                foreach(array_reverse($created_flds) as $created_fld) {
                    \Gino\deleteFileDir($created_fld, true);
                }
                return Error::errorMessage(array('error'=>_("impossibile creare le tabelle")), $link_error);
            }
        }

        /*
         * Removing installations' files
         */
        @unlink($uploadfile);
        @unlink($class_dir.OS."config.txt");
        if(is_readable($class_dir.OS.$class_name.".sql")) {
            @unlink($class_dir.OS.$class_name.".sql");
        }

        return new Redirect($this->linkAdmin(array(), array('block' => 'list')));
    }

    /**
     * @brief Form per l'installazione manuale di un modulo di sistema
     *
     * @return html, form
     */
    private function formManualSysClass() {

        $gform = Loader::load('Form', array(array('form_id'=>'mform')));
        $gform->load('mdataform');

        $GINO = "<div class=\"backoffice-info\">";
        $GINO .= "<p>"._("Per eseguire l'installazione manuale effettuare il submit del form prendendo come riferimento il file config.txt.");
        $GINO .= "<br />"._("In seguito effettuare la procedura indicata").":</p>\n";
        $GINO .= "<ul>";
        $GINO .= "<li>"._("creare la directory app/nomeclasse e copiare tutti i file della libreria")."</li>";
        $GINO .= "<li>"._("creare la directory contents/nomeclasse se è previsto l'upload di file")."</li>";
        $GINO .= "<li>"._("di default le classi vengono create rimovibili")."</li>";
        $GINO .= "<li>"._("eseguire manualmente le query di creazione delle tabelle presenti nel file SQL")."</li>";
        $GINO .= "</ul>";
        $GINO .= "</div>";

       $GINO .= $gform->open($this->_home."?evt[".$this->_class_name."-actionManualSysClass]", false, 'label,name,tblname');

        $GINO .= \Gino\Input::select_label('instantiable', '3', array('1'=>_("istanziabile"), '0'=>_("non istanziabile")), _("Tipo di classe")); // 3 to have no one selected

        $GINO .= \Gino\Input::input_label('label', 'text', '', _("Etichetta"), array("required"=>true, "size"=>40, "maxlength"=>100));
        $GINO .= \Gino\Input::input_label('name', 'text', '', _("Nome classe"), array("required"=>true, "size"=>40, "maxlength"=>100));
        $GINO .= \Gino\Input::textarea_label('description', '', _("Descrizione"), array("required"=>true, "cols"=>45, "rows"=>4));
        $GINO .= \Gino\Input::input_label('tblname', 'text', '', _("Prefisso tabelle"), array("required"=>true, "size"=>40, "maxlength"=>30));
        $GINO .= \Gino\Input::input_label('version', 'text', '', _("Versione"), array("required"=>false, "size"=>40, "maxlength"=>200));

        $GINO .= \Gino\Input::input_label('submit_action', 'submit', _("installa"), '', array("classField"=>"submit"));
        $GINO .= $gform->close();

        $view = new View();
        $view->setViewTpl('section');
        $dict = array(
            'title' => _('Installazione manuale'),
            'class' => 'admin',
            'content' => $GINO
        );

        return $view->render($dict);
    }

    /**
     * @brief Processa il form di installazione manuale di un modulo di sistema
     *
     * @description Il modulo viene attivato nell'installazione
     * @param \Gino\Http\Request $request istanza di Gino.Http.Request
     * @return Gino.Http.Redirect
     */
    public function actionManualSysClass(\Gino\Http\Request $request) {

        $this->requirePerm('can_admin');

        $gform = Loader::load('Form', array(array('form_id'=>'mform')));
        $gform->saveSession('mdataform');
        $req_error = $gform->checkRequired();

        $link_error = $this->linkAdmin(array(), array('action' => 'insert'));

        if($req_error > 0) {
            return Error::errorMessage(array('error'=>1), $link_error);
        }

        $name = \Gino\cleanVar($request->POST, 'name', 'string', '');
        // name check
        if(preg_match("/[\.\/\\\]/", $name)) return error::errorMessage(array('error'=>_("pacchetto non conforme alle specifiche")), $link_error);
        $res = ModuleApp::objects(null, array('where' => "name='$name'"));
        if($res and count($res)) {
            return Error::errorMessage(array('error'=>_("modulo con lo stesso nome già presente nel sistema")), $link_error);
        }

        $module_app = new ModuleApp(null);
        $module_app->label = \Gino\cleanVar($request->POST, 'label', 'string', '');
        $module_app->name = $name;
        $module_app->active = 1;
        $module_app->tbl_name = \Gino\cleanVar($request->POST, 'tblname', 'string', '');
        $module_app->instantiable = \Gino\cleanVar($request->POST, 'instantiable', 'int', '');
        $module_app->description = \Gino\cleanVar($request->POST, 'description', 'string', '');
        $module_app->removable = 1;
        $module_app->class_version = \Gino\cleanVar($request->POST, 'version', 'string', '');

        $res = $module_app->save();

        if($res !== true) {
            return Error::errorMessage(array('error'=>_("impossibile installare il pacchetto")), $link_error);
        }

        return new Redirect($this->linkAdmin());
    }

    /**
     * @brief Form di modifica di un modulo di sistema
     *
     * @param integer $id valore ID del modulo
     * @return html, form
     */
    private function formEditSysClass($id) {

        $gform = Loader::load('Form', array());
        $gform->load('dataform');

        $module_app = new ModuleApp($id);
        if(!$module_app->id) {
            throw new \Exception("ID non associato ad alcuna classe di sistema");
        }

        $GINO = $gform->open($this->_home."?evt[".$this->_class_name."-actionEditSysClass]", '', 'label');
        $GINO .= \Gino\Input::hidden('id', $id);
        $GINO .= \Gino\Input::input_label('label', 'text', $gform->retvar('label', $module_app->label), _("Etichetta"), array("required"=>true, "size"=>40, "maxlength"=>200, 
            "trnsl"=>true, "trnsl_table"=>TBL_MODULE_APP, "field"=>"label", "trnsl_id"=>$id));
        $GINO .= \Gino\Input::textarea_label('description', $gform->retvar('description', $module_app->description), _("Descrizione"), array("cols"=>45, "rows"=>4, 
            "trnsl"=>true, "trnsl_table"=>TBL_MODULE_APP, "field"=>"description", "trnsl_id"=>$id));

        $GINO .= \Gino\Input::input_label('submit_action', 'submit', _("modifica"), '', array("classField"=>"submit"));
        $GINO .= $gform->close();

        $view = new View();
        $view->setViewTpl('section');
        $dict = array(
            'title' => sprintf(_('Modifica il modulo di sistema "%s"'), \Gino\htmlChars($module_app->ml('label'))),
            'class' => 'admin',
            'content' => $GINO
        );

        return $view->render($dict);
    }

    /**
     * @brief Form di attivazione del modulo
     * 
     * @param int $id valore ID del modulo
     * @return html, form
     */
    private function formActivateSysClass($id) {

        $module_app = new ModuleApp($id);

        if(!$module_app->id) {
            throw new \Exception("ID non associato ad alcuna classe di sistema");
        }

        $gform = Loader::load('Form', array());
        $gform->load('dataform');

        $GINO = "<p class=\"lead\">"._("Attenzione! La disattivazione di alcuni moduli potrebbe causare malfunzionamenti nel sistema")."</p>";

        $GINO .= $gform->open($this->_home."?evt[".$this->_class_name."-actionEditSysClassActive]", '', '');
        $GINO .= \Gino\Input::hidden('id', $id);
        $GINO .= \Gino\Input::input_label('submit_action', 'submit', $module_app->active ? _('disattiva') : _('attiva'), _('Sicuro di voler procedere?'), array("classField"=>"submit"));
        $GINO .= $gform->close();

        $view = new View();
        $view->setViewTpl('section');
        $dict = array(
            'title' => $module_app->active ? _('Disattivazione') : _('Attivazione'),
            'class' => 'admin',
            'content' => $GINO
        );

        return $view->render($dict);
    }

    /**
     * @brief Form di aggiornamento del modulo
     *
     * @param int $id valore ID del modulo
     * @return html, form
     */
    private function formUpgradeSysClass($id) {

        $module_app = new ModuleApp($id);
        if(!$module_app->id) {
            throw new \Exception("ID non associato ad alcuna classe di sistema");
        }

        $gform = Loader::load('Form', array());
        $gform->load('dataform');

        $GINO = "<div class=\"backoffice-info\">";
        $GINO .= "<p>"._("La versione del modulo attualmente installato è: ")."<b>".$module_app->class_version."</b></p>\n";
        $GINO .= "<p>"._("Verificare se sono presenti aggiornamenti stabili compatibili con il sistema ed in caso affermativo procedere all'upgrade.")."</p>\n";
        $GINO .= "<p>"._("Nel caso si verificassero errori gravi viene comunque effettuato un dump dell'intero database all'interno della cartella <b>backup</b>.")."</p>\n";
        $GINO .= "</div>";

        $required = 'archive';
        $GINO .= $gform->open($this->_home."?evt[".$this->_class_name."-actionUpgradeSysClass]", true, $required);
        $GINO .= \Gino\Input::hidden('id', $id);

        $GINO .= \Gino\Input::input_file('archive', '', _("Archivio"), array("extensions"=>array(self::PACKAGE_EXTENSION), "del_check"=>false, "required"=>true));
        $GINO .= \Gino\Input::input_label('submit_action', 'submit', _("upgrade"), '', array("classField"=>"submit"));

        $GINO .= $gform->close();

        $view = new View();
        $view->setViewTpl('section');
        $dict = array(
            'title' => _('Upgrade'),
            'class' => 'admin',
            'content' => $GINO
        );

        return $view->render($dict);
    }

    /**
     * @brief Processa il form di modifica di un modulo di sistema
     * @see self::formEditSysClass()
     * @param \Gino\Http\Request $request istanza di Gino.Http.Request
     * @return Gino.Http.Redirect
     */
    public function actionEditSysClass(\Gino\Http\Request $request) {

        $this->requirePerm('can_admin');

        $id = \Gino\cleanVar($request->POST, 'id', 'int', '');
        $model_app = new ModuleApp($id);

        $model_app->label = \Gino\cleanVar($request->POST, 'label', 'string', '');
        $model_app->description = \Gino\cleanVar($request->POST, 'description', 'string', '');

        $model_app->save();

        return new Redirect($this->linkAdmin(array(), array('block' => 'list')));
    }

    /**
     * @brief Processa il form di modifica dello stato dell'attivazione del modulo
     * @see self::formActivateSysClass()
     * @param \Gino\Http\Request $request istanza di Gino.Http.Request
     * @return Gino.Http.Redirect
     */
    public function actionEditSysClassActive(\Gino\Http\Request $request) {

        $this->requirePerm('can_admin');

        $id = \Gino\cleanVar($request->POST, 'id', 'int', '');

        $model_app = new ModuleApp($id);
        $model_app->active = $model_app->active == 1 ? 0 : 1;
        $model_app->save();

        return new Redirect($this->linkAdmin());
    }

    /**
     * @brief Processa il form di aggiornamento del modulo
     * @see self::formUpgradeSysClass()
     * @param \Gino\Http\Request $request istanza di Gino.Http.Request
     * @return Gino.Http.Redirect
     */
    public function actionUpgradeSysClass(\Gino\Http\Request $request) {

        $this->requirePerm('can_admin');

        $id = \Gino\cleanVar($request->POST, 'id', 'int', '');
        $module_app = new ModuleApp($id);

        if(!$module_app->id) {
            throw new \Exception("ID non associato ad alcuna classe di sistema");
        }

        $module_class_name = $module_app->name;
        $link_error = $this->_home."?evt[$this->_class_name-manageSysClass]&block=list&id=$id&action=modify";

        $archive_name = $request->FILES['archive']['name'];
        $archive_tmp = $request->FILES['archive']['tmp_name'];

        if(empty($archive_tmp)) {
            return Error::errorMessage(array('error'=>_("file mancante"), 'hint'=>_("controllare di aver selezionato un file")), $link_error);
        }

        $class_name = preg_replace("/[^a-zA-Z0-9].*?upgrade.*?.zip/", "", $archive_name);
        if($class_name!=$module_class_name) {
            return Error::errorMessage(array('error'=>_("upgrade fallito"), 'hint'=>_("il pacchetto non pare essere un upgrade del modulo esistente")), $link_error);
        }
        if(preg_match("/[\.\/\\\]/", $class_name)) {
            return Error::errorMessage(array('error'=>_("pacchetto non conforme alle specifiche")), $link_error);
        }

        /*
         * dump db
         */
        $this->_db->dumpDatabase(SITE_ROOT.OS.'backup'.OS.'dump_'.date("d_m_Y_H_i_s").'.sql');

        $class_dir = APP_DIR.OS.$class_name."_inst_tmp";
        $module_dir = APP_DIR.OS.$class_name;
        if(!@mkdir($class_dir, 0755))
                return Error::errorMessage(array('error'=>_("upgrade fallito"), 'hint'=>_("controllare i permessi di scrttura")), $link_error);

        $db_conf = array('name'=>$class_name, 'version'=>null, 'tbl_name'=>null, 'instantiable'=>null, 'description'=>null, 'folders'=>null);
        $noCopyFiles = array('config.txt', $class_name.'.sql');
        /*
         * Extract archive
         */
        $uploadfile = $class_dir.OS.$archive_name;
        if(move_uploaded_file($archive_tmp, $uploadfile)) $up = 'ok';
        else $up = 'ko';

        $zip = new \ZipArchive;
        $res = $zip->open($uploadfile);
        if ($res === TRUE) {
            $zip->extractTo($class_dir);
            $zip->close();
        } else {
        \Gino\deleteFileDir($class_dir, true);
            return Error::errorMessage(array('error'=>_("Impossibile scompattare il pacchetto")), $link_error);
        }

        /*
         * Parsering config file
         */
        if(!is_readable($class_dir.OS."config.txt")) {
            \Gino\deleteFileDir($class_dir, true);
            return Error::errorMessage(array('error'=>_("Pacchetto non conforme alle specifiche. File di configurazione mancante.")), $link_error);
        }

        $config = file_get_contents($class_dir.OS."config.txt");
        $config = preg_replace("/\/\*(.|\n)*?\*\/\n/", "", $config);

        $config_params = explode(",", $config);
        foreach($config_params AS $cp) {
            preg_match("/^(\w+?):\"(.*?)\"/", $cp, $matches);
            if(array_key_exists($matches[1], $db_conf)) {
                $db_conf[$matches[1]]=$matches[2];
            }
        }

        /*
         * Check $db_conf elements
         */
        $dbConfError = FALSE;
        if($db_conf['name'] != $class_name) $dbConfError = true;
        if(!in_array($db_conf['instantiable'], array('1', '0', null))) $dbConfError = TRUE;
        if($dbConfError) {
            \Gino\deleteFileDir($class_dir, true);
            return Error::errorMessage(array('error'=>_("Pacchetto non conforme alle specifiche.")), $link_error);
        }

        /*
         * Create contents folders
         */
        $created_flds = array();
        if($db_conf['folders']!=null) {
            $folders = explode(",", $db_conf['folders']);
            foreach($folders as $fld) {
                if(@mkdir(SITE_ROOT.OS.$fld, 0755)) {
                    $created_flds[] = SITE_ROOT.OS.$fld;
                }
                else {
                    \Gino\deleteFileDir($class_dir, true);
                    foreach(array_reverse($created_flds) as $created_fld) {
                        \Gino\deleteFileDir($created_fld, true);
                    }
                    return Error::errorMessage(array('error'=>_("upgrade fallito - impossibile creare le cartelle dei contenuti"), 'hint'=>_("controllare i permessi di scrittura")), $link_error);
                }
            }
        }

        /*
         * Exec sql statements
         */
        if(is_readable($class_dir.OS.$class_name.".sql")) {
            $sql = file_get_contents($class_dir.OS.$class_name.".sql");
            $res = $this->_db->multiActionquery($sql);
            if(!$res) {
                \Gino\deleteFileDir($class_dir, TRUE);
                foreach(array_reverse($created_flds) as $created_fld) {
                    \Gino\deleteFileDir($created_fld, TRUE);
                }
                return Error::errorMessage(array('error'=>_("Upgrade fallito - impossibile creare le tabelle")), $link_error);
            }
        }

        /*
         * Update DB data tbl module app
         */
        $sets = $unsets = array();
        $module_app->class_version = $db_conf['version'];
        $module_app->tbl_name = $db_conf['tbl_name'];
        $module_app->instantiable = $db_conf['instantiable'];
        $module_app->description = $db_conf['description'];

        $module_app->save();

        /*
         * Move and overwrite files
         */
        @unlink($class_dir.OS.$archive_name);
        $res = $this->upgradeFolders($class_dir, $module_dir, $noCopyFiles);
        if(!$res) return Error::errorMessage(array('error'=>_("Si è verificato un errore durante l'upgrade. Uno o più file non sono stati copiati correttamente. Contattare l'amministratore del sistema per risolvere il problema.")), $link_error);

        /*
         * Removing tmp install folder
         */
        \Gino\deleteFileDir($class_dir, TRUE);

        return new Redirect($this->linkAdmin(array(), array('block' => 'list')));
    }

    /**
     * @brief Aggiunge e sovrascrive files nella directory del modulo durante il processo di upgrade
     * @param string $files_dir
     * @param string $module_dir
     * @param array $noCopyFiles array di nommi di files da non copiare/sovrascrivere
     * @return risultato operazione, bool
     */
    private function upgradeFolders($files_dir, $module_dir, $noCopyFiles) {

        $res = true;
        $files = \Gino\searchNameFile($files_dir);
        foreach($files as $file) {
            if(!in_array($file, $noCopyFiles)) {
                if(is_file($files_dir.OS.$file)) {
                    if(copy($files_dir.OS.$file, $module_dir.OS.$file)) {
                        $res = $res && TRUE;
                    }
                    else $res = FALSE;
                }
                elseif(is_dir($files_dir.OS.$file)) {
                    @mkdir($module_dir.OS.$file);
                    $this->upgradeFolders($files_dir.OS.$file, $module_dir.OS.$file, $noCopyFiles);
                }
            }
        }
        return $res;
    }

    /**
     * @brief Form di eliminazione di un modulo di sistema
     *
     * @param int $id valore ID del modulo
     * @return html, form
     */
    private function formRemoveSysClass($id) {

        $module_app = new ModuleApp($id);

        if(!$module_app->id) {
            throw new \Exception("ID non associato ad alcuna classe di sistema");
        }

        $GINO = "<p class=\"lead\">"._("Attenzione! La disinstallazione di un modulo di sistema potrebbe provocare dei malfunzionamenti ed è un'operazione irreversibile.")."</p>\n";
        if($module_app->instantiable) {
            $GINO .= "<p>".sprintf(_("Il modulo %s prevede la creazione di istanze, per tanto la sua eliminazione determina l'eliminazione di ogni istanza e dei dati associati."), $module_app->label)."</p>\n";
            Loader::import('module', 'ModuleInstance');
            $mdl_instances = ModuleInstance::getFromModuleApp($module_app->id);
            if(count($mdl_instances)) {
                $GINO .= "<p>"._("Attualmente nel sitema sono presenti le seguenti istanze:")."</p>\n";
                $GINO .= "<ul>";
                foreach($mdl_instances as $mi) {
                    $GINO .= "<li>".$mi->label."</li>";
                }
                $GINO .= "</ul>";
            }
            else {
                $GINO .= "<p>"._("Attualmente nel sistema non sono presenti istanze.")."</p>\n";
            }
        }
        $GINO .= "<p>"._("La disinstallazione non determina la rimozione dei moduli all'interno dei template.")."</p>\n";

        $gform = Loader::load('Form', array());
        $gform->load('dataform');
        
        $GINO .= $gform->open($this->_home."?evt[".$this->_class_name."-actionRemoveSysClass]", '', '');
        $GINO .= \Gino\Input::hidden('id', $id);
        $GINO .= \Gino\Input::input_label('submit_action', 'submit', _("disinstalla"), _("Sicuro di voler procedere?"), array("classField"=>"submit"));
        $GINO .= $gform->close();

        $view = new View();
        $view->setViewTpl('section');
        $dict = array(
            'title' => sprintf(_('Disinstallazione modulo di sistema "%s"'), $module_app->label),
            'class' => 'admin',
            'content' => $GINO
        );

        return $view->render($dict);
    }

    /**
     * @brief Processa il form di eliminazione di un modulo di sistema
     * @see self::formRemoveSysClass()
     * @param \Gino\Http\Request $request istanza di Gino.Http.Request
     * @return Gino.Http.Redirect
     */
    public function actionRemoveSysClass(\Gino\Http\Request $request) {

        $this->requirePerm('can_admin');

        $id = \Gino\cleanVar($request->POST, 'id', 'int', '');
        $instance = \Gino\cleanVar($request->POST, 'instance', 'string', '');
        $module_app = new ModuleApp($id);

        /*
         * Removing instances if any
         */
        if($module_app->instantiable) {
            Loader::import('module', 'ModuleInstance');
            $mdl_instances = ModuleInstance::getFromModuleApp($module_app->id);
            foreach($mdl_instances as $mi) {
                $class = $module_app->classNameNs();
                $class_obj = new $class($mi->id);
                $class_obj->deleteInstance();
                $mi->deleteDbData();
            }
        }

        /*
         * Drop class tables and remove contents folders
         */
        $class_elements = call_user_func(array($module_app->classNameNs(), 'getClassElements'));
        foreach($class_elements['tables'] as $tbl) {
            $result = $this->_db->drop($tbl);
            $this->_trd->deleteTranslations($tbl, 'all');
        }
        foreach($class_elements['folderStructure'] as $fld=>$sub) {
            \Gino\deleteFileDir($fld, TRUE);
        }
        /*
         * Drop permissions
         */
        $this->_db->delete(TBL_PERMISSION, "class='".$module_app->name."'");

        /*
         * Removing class directory
         */
        \Gino\deleteFileDir(APP_DIR.OS.$module_app->name, TRUE);

        /*
         * Removing sysclass
         */
        $module_app->deleteDbData();

        return new Redirect($this->linkAdmin(array(), array('block' => 'list')));
    }

    /**
     * @brief Informazioni modulo
     * @return html, informazioni
     */
    private function info() {

        $buffer = "<p>"._("In questa sezione si gestiscono le classi fondamentali del sistema. E' possibile installare nuove classi, rimuovere quelle presenti (non tutte) e fare gli aggiornamenti. Ciascuna modifica deve essere fatta con criterio, sapendo che è possibile compromettere la stabilità del sistema in caso di operazioni errate.")."</p>\n";

        $buffer .= "<p>"._("Per costruire un pacchetto di installazione sono necessari").":</p>\n";
        $buffer .= "<ul>";
        $buffer .= "<li>"._("file della classe (es. class_news.php)")."</li>";
        $buffer .= "<li>"._("file ini (es. news.ini)")."</li>";
        $buffer .= "<li>"._("file di configurazione con i parametri corretti (config.txt)")."</li>";
        $buffer .= "</ul>";

        $buffer .= "<p>"._("Nel file 'config.txt' il parametro 'name' deve corrispondere al nome della classe.")."</p>\n";
        $buffer .= "<p>"._("Il pacchetto deve avere il formato 'nome_classe'_'qualcosa'.zip (es. news_pkg.zip) e deve contenere tutti i file a partire dalla stessa directory.")."</p>\n";

        $buffer .= "<p>"._("Altri file utili").":</p>\n";
        $buffer .= "<ul>";
        $buffer .= "<li>"._("file con le query delle tabelle (es. news.sql); se non è presente è necessario eseguire le query a mano successivamente. Il nome del file deve corrispondere al nome della classe.")."</li>";
        $buffer .= "<li>"._("file css (es. news.css)")."</li>";
        $buffer .= "<li>"._("altri file di complemento (es. class.Item.php)")."</li>";
        $buffer .= "</ul>";

        $view = new View();
        $view->setViewTpl('section');
        $dict = array(
            'title' => _('Informazioni'),
            'class' => 'admin',
            'content' => $buffer
        );

        return $view->render($dict);
    }
}
