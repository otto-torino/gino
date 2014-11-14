<?php
/**
 * @file class_sysClass.php
 * @brief Contiene la classe sysClass
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino\App\SysClass;

require_once('class.ModuleApp.php');

/**
 * @brief Libreria per la gestione dei moduli di sistema
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class sysClass extends \Gino\Controller {

  private $_title;
  private $_action;
  private $_archive_extensions;
  
  function __construct(){

    parent::__construct();

    $this->_title = _("Gestione classi di sistema");

    $this->_action = \Gino\cleanVar($_REQUEST, 'action', 'string', '');

    $this->_archive_extensions = array('zip');
  }

  /**
   * Interfaccia amministrativa per la gestione dei moduli di sistema
   * 
   * @see $_access_admin
   * @return string
   */
  public function manageSysClass() {
    
    $this->requirePerm('can_admin');

    $id = \Gino\cleanVar($_GET, 'id', 'int', '');
    $block = \Gino\cleanVar($_GET, 'block', 'string', null);

    $link_dft = "<a href=\"".$this->_home."?evt[".$this->_class_name."-manageSysClass]\">"._("Informazioni")."</a>";
    $link_list = "<a href=\"".$this->_home."?evt[".$this->_class_name."-manageSysClass]&block=list\">"._("Gestione moduli installati")."</a>";
    $link_install = "<a href=\"".$this->_home."?evt[".$this->_class_name."-manageSysClass]&block=install\">"._("Installazione pacchetto")."</a>";
    $link_minstall = "<a href=\"".$this->_home."?evt[".$this->_class_name."-manageSysClass]&block=minstall\">"._("Installazione manuale")."</a>";
    $sel_link = $link_dft;

    if($block == 'list') {
      $action = \Gino\cleanVar($_GET, 'action', 'string', null);
      if(isset($_GET['trnsl']) and $_GET['trnsl'] == '1') {
        if(isset($_GET['save']) and $_GET['save'] == '1') {
          $this->_trd->actionTranslation();
        }
        else {
          $this->_trd->formTranslation();
        }
      }
      elseif($action == 'modify') {
        $GINO = $this->formEditSysClass($id);
        $GINO .= $this->formUpgradeSysClass($id);
        $GINO .= $this->formActivateSysClass($id);
      }
      elseif($action == 'delete') {
        $GINO = $this->formRemoveSysClass($id);
      }
      else {
        $GINO = $this->sysClassList();
      }
      $sel_link = $link_list;
    }
    elseif($block == 'install') {
      $GINO = $this->formInsertSysClass();
      $sel_link = $link_install;
    }
    elseif($block == 'minstall') {
      $GINO = $this->formManualSysClass();
      $sel_link = $link_minstall;
    }
    else {
      $GINO = $this->info();
    }

    $view = new \Gino\View();
    $view->setViewTpl('tab');
    $dict = array(
      'title' => _('Moduli di sistema'),
      'links' => array($link_minstall, $link_install, $link_list, $link_dft),
      'selected_link' => $sel_link,
      'content' => $GINO
    );
    return $view->render($dict);
  }

  /**
   * Elenco dei moduli di sistema
   * 
   * @param integer $sel_id valore ID del modulo selezionato
   * @return string
   */
  private function sysClassList() {

    $view_table = new \Gino\View();
    $view_table->setViewTpl('table');

    $modules_app = ModuleApp::objects(null);
    if(count($modules_app)) {
      $GINO = "<p class=\"backoffice-info\">"._('Di seguito l\'elenco di tutti i moduli installati sul sistema. Cliccare l\'icona di modifica per cambiare l\'etichetta e la descrizione del modulo, effettuare un upgrade o cambiare lo stato di attivazione.  In caso di eliminazione di un modulo istanziabile verranno eliminate anche tutte le sue istanze.')."</p>";
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

        $link_modify = "<a href=\"$this->_home?evt[$this->_class_name-manageSysClass]&block=list&id=".$module_app->id."&action=modify\">".\Gino\pub::icon('modify', _("modifica/upgrade"))."</a>";
        $link_delete = $module_app->removable ? "<a href=\"$this->_home?evt[$this->_class_name-manageSysClass]&block=list&id=".$module_app->id."&action=delete\">".\Gino\pub::icon('delete', _("elimina"))."</a>" : "";

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

    $view = new \Gino\View();
    $view->setViewTpl('section');
    $dict = array(
      'title' => _('Elenco moduli installati'),
      'class' => 'admin',
      'content' => $GINO
    );
    
    return $view->render($dict);
  }

  /**
   * Form di installazione di un modulo di sistema
   * 
   * @return string
   */
  private function formInsertSysClass() {
    
    $gform = \Gino\Loader::load('Form', array('gform', 'post', true));
    $gform->load('dataform');

    $GINO = "<p class=\"backoffice-info\">"._("Caricare il pacchetto del modulo. Se la procedura di installazione va a buon fine modificare il modulo appena inserito per personalizzarne l'etichetta ed eventualmente altri parametri.")."</p>\n";
    
    $required = 'archive';
    $GINO .= $gform->open($this->_home."?evt[".$this->_class_name."-actionInsertSysClass]", true, $required);
    $GINO .= $gform->cfile('archive', '', _("Archivio"), array("extensions"=>$this->_archive_extensions, "del_check"=>false, "required"=>true));
    $GINO .= $gform->cinput('submit_action', 'submit', _("installa"), '', array("classField"=>"submit"));
    $GINO .= $gform->close();

    $view = new \Gino\View();
    $view->setViewTpl('section');
    $dict = array(
      'title' => _("Installazione modulo di sistema"),
      'class' => 'admin',
      'content' => $GINO
    );
    return $view->render($dict);
  }

  /**
   * Installazione modulo di sistema
   * 
   * Il modulo viene attivato nell'installazione
   * 
   * @see $_access_admin
   */
  public function actionInsertSysClass() {

    $this->requirePerm('can_admin');

    $link_error = $this->_home."?evt[$this->_class_name-manageSysClass]&block=install";

    if(!\Gino\pub::enabledZip())
      exit(error::errorMessage(array('error'=>_("la classe ZipArchive non è supportata"), 'hint'=>_("il pacchetto deve essere installato con procedura manuale")), $link_error));
    
    $archive_name = $_FILES['archive']['name'];
    $archive_tmp = $_FILES['archive']['tmp_name'];

    if(empty($archive_tmp)) {
      exit(error::errorMessage(array('error'=>_("file mancante"), 'hint'=>_("controllare di aver selezionato un file")), $link_error));
    }
    
    $class_name = preg_replace("/[^a-zA-Z0-9].*?.zip/", "", $archive_name);
    if(preg_match("/[\.\/\\\]/", $class_name)) {
      exit(error::errorMessage(array('error'=>_("pacchetto non conforme alle specifiche")), $link_error));
    }
    
    /*
     * dump db 
     */
    $this->_db->dumpDatabase(SITE_ROOT.OS.'backup'.OS.'dump_'.date("d_m_Y_H_i_s").'.sql');

    $class_dir = APP_DIR.OS.$class_name;
    @mkdir($class_dir, 0755) || exit(error::errorMessage(array('error'=>_("impossibile creare la cartella base del modulo"), 'hint'=>_("controllare i permessi di scrittura")), $link_error));

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
      $this->_registry->pub->deleteFileDir($class_dir, true);
      exit(error::errorMessage(array('error'=>_("impossibile scompattare il pacchetto")), $link_error));
    }

    /*
     * Parsering config file
     */
    if(!is_readable($class_dir.OS."config.txt")) {
      $this->_registry->pub->deleteFileDir($class_dir, true);
      exit(error::errorMessage(array('error'=>_("pacchetto non conforme alle specifiche. File di configurazione mancante.")), $link_error));
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
    $dbConfError = false;
    if($db_conf['name'] != $class_name) $dbConfError = true;
    if(!in_array($db_conf['instantiable'], array('1', '0'))) $dbConfError = true;
    if(!in_array($db_conf['removable'], array('1', '0'))) $dbConfError = true;
    if($dbConfError) {
      $this->_registry->pub->deleteFileDir($class_dir, true);
      exit(error::errorMessage(array('error'=>_("pacchetto non conforme alle specifiche.")), $link_error));
    }
    // name check
    $res = ModuleApp::get(array('where' => "name='".$db_conf['name']."'"));
    if($res and count($res)) {
      exit(error::errorMessage(array('error'=>_("modulo con lo stesso nome già presente nel sistema")), $link_error));
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

    $result = $module_app->updateDbData();

    if(!$result) {
      $this->_registry->pub->deleteFileDir($class_dir, true);
      exit(error::errorMessage(array('error'=>_("impossibile installare il pacchetto")), $link_error));
    }
    
    /*
     * Create contents folders
     */
    $created_flds = array();
    if($db_conf['folders']!=null) {
      $folders = explode(",", $db_conf['folders']);
      foreach($folders as $fld) {
        trim($fld);
        if(@mkdir(SITE_ROOT.OS.$fld, 0755)) {
          $created_flds[] = SITE_ROOT.OS.$fld;
        }
        else {
          $this->_registry->pub->deleteFileDir($class_dir, true);
          $module_app->deleteDbData();
          foreach(array_reverse($created_flds) as $created_fld) {
            $this->_registry->pub->deleteFileDir($created_fld, true);
          }
          exit(error::errorMessage(array('error'=>_("impossibile creare le cartelle dei contenuti"), 'hint'=>_("controllare i permessi di scrittura")), $link_error));
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
        $this->_registry->pub->deleteFileDir($class_dir, true);
        $module_app->deleteDbData();
        foreach(array_reverse($created_flds) as $created_fld) {
          $this->_registry->pub->deleteFileDir($created_fld, true);
        }
        exit(error::errorMessage(array('error'=>_("impossibile creare le tabelle")), $link_error));
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

    \Gino\Link::HttpCall($this->_home, $this->_class_name.'-manageSysClass', 'block=list');
  }
  
  /**
   * Form per l'installazione manuale di un modulo di sistema
   * 
   * @return string
   */
  private function formManualSysClass() {
    
    $gform = \Gino\Loader::load('Form', array('mform', 'post', true));
    $gform->load('mdataform');

    $GINO = "<div class=\"backoffice-info\">";
    $GINO .= "<p>"._("Per eseguire l'installazione manuale effettuare il submit del form prendendo come riferimento il file config.txt.");
    $GINO .= "<br />"._("In seguito effettuare la procedura indicata").":</p>\n";
    $GINO .= "<ul>";
    $GINO .= "<li>creare la directory app/nomeclasse e copiare tutti i file della libreria</li>";
    $GINO .= "<li>creare la directory contents/nomeclasse se è previsto l'upload di file</li>";
    $GINO .= "<li>di default le classi vengono create rimovibili</li>";
    $GINO .= "<li>eseguire manualmente le query di creazione delle tabelle presenti nel file SQL</li>";
    $GINO .= "</ul>";
    $GINO .= "</div>";
    
    $required = 'label,name,tblname';
    $GINO .= $gform->open($this->_home."?evt[".$this->_class_name."-actionManualSysClass]", false, $required);
    
    $instance = 'yes';
    $GINO .= $gform->cselect('instantiable', '3', array('1'=>_("istanziabile"), '0'=>_("non istanziabile")), _("Tipo di classe")); // 3 to have no one selected
    
    $GINO .= $gform->cinput('label', 'text', '', _("Etichetta"), array("required"=>true, "size"=>40, "maxlength"=>100));
    $GINO .= $gform->cinput('name', 'text', '', _("Nome classe"), array("required"=>true, "size"=>40, "maxlength"=>100));
    $GINO .= $gform->ctextarea('description', '', _("Descrizione"), array("cols"=>45, "rows"=>4));
    $GINO .= $gform->cinput('tblname', 'text', '', _("Prefisso tabelle"), array("required"=>true, "size"=>40, "maxlength"=>30));
    $GINO .= $gform->cinput('version', 'text', '', _("Versione"), array("required"=>false, "size"=>40, "maxlength"=>200));
    
    $GINO .= $gform->cinput('submit_action', 'submit', _("installa"), '', array("classField"=>"submit"));
    $GINO .= $gform->close();

    $view = new \Gino\View();
    $view->setViewTpl('section');
    $dict = array(
      'title' => _('Installazione manuale'),
      'class' => 'admin',
      'content' => $GINO
    );

    return $view->render($dict);
  }
  
  /**
   * Installazione manuale di un modulo di sistema
   * 
   * Il modulo viene attivato nell'installazione
   * 
   */
  public function actionManualSysClass() {
    
    $this->requirePerm('can_admin');
    
    $gform = \Gino\Loader::load('Form', array('mform', 'post', false));
    $gform->save('mdataform');
    $req_error = $gform->arequired();

    $link_error = $this->_home."?evt[$this->_class_name-manageSysClass]&action=insert";
    
    if($req_error > 0) 
      exit(error::errorMessage(array('error'=>1), $link_error));

    $name = \Gino\cleanVar($_POST, 'name', 'string', '');
    // name check
    if(preg_match("/[\.\/\\\]/", $name)) exit(error::errorMessage(array('error'=>_("pacchetto non conforme alle specifiche")), $link_error));
    $res = ModuleApp::get(array('where' => "name='$name'"));
    if($res and count($res)) {
      exit(error::errorMessage(array('error'=>_("modulo con lo stesso nome già presente nel sistema")), $link_error));
    }

    $module_app = new ModuleApp(null);
    $module_app->label = \Gino\cleanVar($_POST, 'label', 'string', '');
    $module_app->name = $name;
    $module_app->active = 1;
    $module_app->tbl_name = \Gino\cleanVar($_POST, 'tblname', 'string', '');
    $module_app->instantiable = \Gino\cleanVar($_POST, 'instantiable', 'int', '');
    $module_app->description = \Gino\cleanVar($_POST, 'description', 'string', '');
    $module_app->removable = 1;
    $module_app->class_version = \Gino\cleanVar($_POST, 'version', 'string', '');

    $res = $module_app->updateDbData();

    if(!$res) {
      exit(error::errorMessage(array('error'=>_("impossibile installare il pacchetto")), $link_error));
    }

    \Gino\Link::HttpCall($this->_home, $this->_class_name.'-manageSysClass', '');
  }

  /**
   * Form di modifica di un modulo di sistema
   * 
   * Verifica l'esistenza nella classe del modulo dei metodi @a outputFunctions e @a permission
   * 
   * @param integer $id valore ID del modulo
   * @return string
   */
  private function formEditSysClass($id) {
    
    $gform = \Gino\Loader::load('Form', array('gform', 'post', true));
    $gform->load('dataform');

    $module_app = new ModuleApp($id);
    if(!$module_app->id) {
      exit(error::syserrorMessage("sysClass", "formEditSysClass", "ID non associato ad alcuna classe di sistema", __LINE__));
    }


    $required = 'label';
    $GINO = $gform->open($this->_home."?evt[".$this->_class_name."-actionEditSysClass]", '', $required);
    $GINO .= $gform->hidden('id', $id);
    $GINO .= $gform->cinput('label', 'text', $gform->retvar('label', $module_app->label), _("Etichetta"), array("required"=>true, "size"=>40, "maxlength"=>200, 
      "trnsl"=>true, "trnsl_table"=>TBL_MODULE_APP, "field"=>"label", "trnsl_id"=>$id));
    $GINO .= $gform->ctextarea('description', $gform->retvar('description', $module_app->description), _("Descrizione"), array("cols"=>45, "rows"=>4, 
      "trnsl"=>true, "trnsl_table"=>TBL_MODULE_APP, "field"=>"description", "trnsl_id"=>$id));

    $GINO .= $gform->cinput('submit_action', 'submit', _("modifica"), '', array("classField"=>"submit"));
    $GINO .= $gform->close();

    $view = new \Gino\View();
    $view->setViewTpl('section');
    $dict = array(
      'title' => sprintf(_('Modifica il modulo di sistema "%s"'), \Gino\htmlChars($module_app->ml('label'))),
      'class' => 'admin',
      'content' => $GINO
    );

    return $view->render($dict);
  }

  /**
   * Form di attivazione del modulo
   * 
   * @param integer $id valore ID del modulo
   * @return string 
   */
  private function formActivateSysClass($id) {

    $module_app = new ModuleApp($id);

    if(!$module_app->id) {
      exit(error::syserrorMessage("sysClass", "formEditSysClass", "ID non associato ad alcuna classe di sistema", __LINE__));
    }

    $gform = \Gino\Loader::load('Form', array('gform', 'post', true));
    $gform->load('dataform');

    $GINO = "<p class=\"lead\">"._("Attenzione! La disattivazione di alcuni moduli potrebbe causare malfunzionamenti nel sistema")."</p>";

    $GINO .= $gform->open($this->_home."?evt[".$this->_class_name."-actionEditSysClassActive]", '', '');
    $GINO .= $gform->hidden('id', $id);
    $GINO .= $gform->cinput('submit_action', 'submit', $module_app->active ? _('disattiva') : _('attiva'), _('Sicuro di voler procedere?'), array("classField"=>"submit"));
    $GINO .= $gform->close();

    $view = new \Gino\View();
    $view->setViewTpl('section');
    $dict = array(
      'title' => $module_app->active ? _('Disattivazione') : _('Attivazione'),
      'class' => 'admin',
      'content' => $GINO
    );

    return $view->render($dict);
  }

  /**
   * Form di aggiornamento del modulo
   * 
   * @param integer $id valore ID del modulo
   * @param string $version versione del modulo
   * @return string
   */
  private function formUpgradeSysClass($id) {

    $module_app = new ModuleApp($id);
    if(!$module_app->id) {
      exit(error::syserrorMessage("sysClass", "formEditSysClass", "ID non associato ad alcuna classe di sistema", __LINE__));
    }

    $gform = \Gino\Loader::load('Form', array('gform', 'post', true));
    $gform->load('dataform');
    
    $GINO = "<div class=\"backoffice-info\">";
    $GINO .= "<p>"._("La versione del modulo attualmente installato è: ")."<b>".$module_app->class_version."</b></p>\n";
    $GINO .= "<p>"._("Verificare se sono presenti aggiornamenti stabili compatibili con il sistema ed in caso affermativo procedere all'upgrade.")."</p>\n";
    $GINO .= "<p>"._("Nel caso si verificassero errori gravi viene comunque effettuato un dump dell'intero database all'interno della cartella <b>backup</b>.")."</p>\n";
    $GINO .= "</div>";

    $required = 'archive';
    $GINO .= $gform->open($this->_home."?evt[".$this->_class_name."-actionUpgradeSysClass]", true, $required);
    $GINO .= $gform->hidden('id', $id);

    $GINO .= $gform->cfile('archive', '', _("Archivio"), array("extensions"=>$this->_archive_extensions, "del_check"=>false, "required"=>true));
    $GINO .= $gform->cinput('submit_action', 'submit', _("upgrade"), '', array("classField"=>"submit"));

    $GINO .= $gform->close();

    $view = new \Gino\View();
    $view->setViewTpl('section');
    $dict = array(
      'title' => _('Upgrade'),
      'class' => 'admin',
      'content' => $GINO
    );

    return $view->render($dict);
  }

  /**
   * Modifica del modulo
   * 
   * @see $_access_admin
   */
  public function actionEditSysClass() {
  
    $this->requirePerm('can_admin');

    $id = \Gino\cleanVar($_POST, 'id', 'int', '');
    $model_app = new ModuleApp($id);

    $model_app->label = \Gino\cleanVar($_POST, 'label', 'string', '');
    $model_app->description = \Gino\cleanVar($_POST, 'description', 'string', '');

    $model_app->updateDbData();
    
    \Gino\Link::HttpCall($this->_home, $this->_class_name.'-manageSysClass', 'block=list');
  }

  /**
   * Modifica dello stato dell'attivazione del modulo
   * 
   * @see $_access_admin
   */
  public function actionEditSysClassActive() {
  
    $this->requirePerm('can_admin');

    $id = \Gino\cleanVar($_POST, 'id', 'int', '');
    $model_app = new ModuleApp($id);

    $model_app->active = $model_app->active == 1 ? 0 : 1;

    $model_app->updateDbData();
  
    \Gino\Link::HttpCall($this->_home, $this->_class_name.'-manageSysClass', '');
  }

  /**
   * Aggiornamento del modulo
   * 
   * @see $_access_admin
   */
  public function actionUpgradeSysClass() {
    
    $this->accessType($this->_access_admin);

    $id = \Gino\cleanVar($_POST, 'id', 'int', '');
    $module_app = new ModuleApp($id);

    if(!$module_app->id) {
      exit(error::syserrorMessage("sysClass", "actionUpgradeSysClass", "ID non associato ad alcuna classe di sistema", __LINE__));
    }

    $module_class_name = $module_app->name;
    $link_error = $this->_home."?evt[$this->_class_name-manageSysClass]&block=list&id=$id&action=modify";

    $archive_name = $_FILES['archive']['name'];
    $archive_tmp = $_FILES['archive']['tmp_name'];

    if(empty($archive_tmp)) {
      exit(error::errorMessage(array('error'=>_("file mancante"), 'hint'=>_("controllare di aver selezionato un file")), $link_error));
    }

    $class_name = preg_replace("/[^a-zA-Z0-9].*?upgrade.*?.zip/", "", $archive_name);
    if($class_name!=$module_class_name) {
      exit(error::errorMessage(array('error'=>_("upgrade fallito"), 'hint'=>_("il pacchetto non pare essere un upgrade del modulo esistente")), $link_error));
    }
    if(preg_match("/[\.\/\\\]/", $class_name)) {
      exit(error::errorMessage(array('error'=>_("pacchetto non conforme alle specifiche")), $link_error));
    }

    /*
     * dump db 
     */
    $this->_db->dumpDatabase(SITE_ROOT.OS.'backup'.OS.'dump_'.date("d_m_Y_H_i_s").'.sql');

    $class_dir = APP_DIR.OS.$class_name."_inst_tmp";
    $module_dir = APP_DIR.OS.$class_name;
    @mkdir($class_dir, 0755) || exit(error::errorMessage(array('error'=>_("upgrade fallito"), 'hint'=>_("controllare i permessi di scrttura")), $link_error));

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
    if ($res === true) {
      $zip->extractTo($class_dir);
      $zip->close();
    } else {
    $this->_registry->pub->deleteFileDir($class_dir, true);
      exit(error::errorMessage(array('error'=>_("Impossibile scompattare il pacchetto")), $link_error));
    }
    
    /*
     * Parsering config file
     */
    if(!is_readable($class_dir.OS."config.txt")) {
      $this->_registry->pub->deleteFileDir($class_dir, true);
      exit(error::errorMessage(array('error'=>_("Pacchetto non conforme alle specifiche. File di configurazione mancante.")), $link_error));
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
    $dbConfError = false;
    if($db_conf['name'] != $class_name) $dbConfError = true;
    if(!in_array($db_conf['instantiable'], array('1', '0', null))) $dbConfError = true;
    if($dbConfError) {
      $this->_registry->pub->deleteFileDir($class_dir, true);
      exit(error::errorMessage(array('error'=>_("Pacchetto non conforme alle specifiche.")), $link_error));
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
          $this->_registry->pub->deleteFileDir($class_dir, true);
          foreach(array_reverse($created_flds) as $created_fld) {
            $this->_registry->pub->deleteFileDir($created_fld, true);
          }
          exit(error::errorMessage(array('error'=>_("upgrade fallito - impossibile creare le cartelle dei contenuti"), 'hint'=>_("controllare i permessi di scrittura")), $link_error));
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
        $this->_registry->pub->deleteFileDir($class_dir, true);
        foreach(array_reverse($created_flds) as $created_fld) {
          $this->_registry->pub->deleteFileDir($created_fld, true);
        }
        exit(error::errorMessage(array('error'=>_("Upgrade fallito - impossibile creare le tabelle")), $link_error));
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

    $module_app->updateDbData();

    /*
     * Move and overwrite files
     */
    @unlink($class_dir.OS.$archive_name);
    $res = $this->upgradeFolders($class_dir, $module_dir, $noCopyFiles);
    if(!$res) exit(error::errorMessage(array('error'=>_("Si è verificato un errore durante l'upgrade. Uno o più file non sono stati copiati correttamente. Contattare l'amministratore del sistema per risolvere il problema.")), $link_error));
    
    /*
     * Removing tmp install folder
     */
    $this->_registry->pub->deleteFileDir($class_dir, true);

    \Gino\Link::HttpCall($this->_home, $this->_class_name.'-manageSysClass', 'block=list');
  }

  private function upgradeFolders($files_dir, $module_dir, $noCopyFiles) {

    $res = true;
    $files = \Gino\searchNameFile($files_dir);
    foreach($files as $file) {
      if(!in_array($file, $noCopyFiles)) {
        if(is_file($files_dir.OS.$file)) {
          if(copy($files_dir.OS.$file, $module_dir.OS.$file)) {
            $res = $res && true;
          }
          else $res = false;
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
   * Form di eliminazione di un modulo di sistema
   * 
   * @param integer $id valore ID del modulo
   * @return string
   */
  private function formRemoveSysClass($id) {
    
    $gform = \Gino\Loader::load('Form', array('gform', 'post', true));
    $gform->load('dataform');

    $module_app = new ModuleApp($id);

    if(!$module_app->id) {
      exit(error::syserrorMessage("sysClass", "formEditSysClass", "ID non associato ad alcuna classe di sistema", __LINE__));
    }

    $GINO = "<p class=\"lead\">"._("Attenzione! La disinstallazione di un modulo di sistema potrebbe provocare dei malfunzionamenti ed è un'operazione irreversibile.")."</p>\n";
    if($module_app->instantiable) {
      $GINO .= "<p>".sprintf(_("Il modulo %s prevede la creazione di istanze, per tanto la sua eliminazione determina l'eliminazione di ogni istanza e dei dati associati."), $module_app->label)."</p>\n";
      loader::import('module', 'ModuleInstance');
      $mdl_instances = \Gino\App\Module\ModuleInstance::getFromModuleApp($module_app->id);
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

    $required = '';
    $GINO .= $gform->open($this->_home."?evt[".$this->_class_name."-actionRemoveSysClass]", '', $required);
    $GINO .= $gform->hidden('id', $id);
    $GINO .= $gform->cinput('submit_action', 'submit', _("disinstalla"), _("Sicuro di voler procedere?"), array("classField"=>"submit"));
    $GINO .= $gform->close();

    $view = new \Gino\View();
    $view->setViewTpl('section');
    $dict = array(
      'title' => sprintf(_('Disinstallazione modulo di sistema "%s"'), $module_app->label),
      'class' => 'admin',
      'content' => $GINO
    );

    return $view->render($dict);
  }

  /**
   * Eliminazione di un modulo di sistema
   * 
   * @see $_access_admin
   */
  public function actionRemoveSysClass() {
    
    $this->requirePerm('can_admin');

    $id = \Gino\cleanVar($_POST, 'id', 'int', '');

    $module_app = new ModuleApp($id);

    $instance = \Gino\cleanVar($_POST, 'instance', 'string', ''); // @QUI
    $className = $this->_db->getFieldFromId($this->_tbl_module_app, 'name', 'id', $id);

    /*
     * Removing instances if any 
     */
    if($module_app->instantiable) {
      \Gino\Loader::import('module', '\Gino\App\Module\ModuleInstance');
      $mdl_instances = \Gino\App\Module\ModuleInstance::getFromModuleApp($module_app->id);
      foreach($mdl_instances as $mi) {
        $class_obj = new ${$module_app->name}($mi->id);
        $class_obj->deleteInstance();
        $mi->deleteDbData();
      }
    }

    /*
     * Drop class tables and removing contents folders
     */
    $class_elements = call_user_func(array($module_app->name, 'getClassElements'));
    foreach($class_elements['tables'] as $tbl) {
      $result = $this->_db->drop($tbl);
      $this->_trd->deleteTranslations($tbl, 'all');
    }
    foreach($class_elements['folderStructure'] as $fld=>$sub) {
      $this->_registry->pub->deleteFileDir($fld, true);
    }

    /*
     * Removing class directory
     */
    $this->_registry->pub->deleteFileDir(APP_DIR.OS.$className, true);
    
    /*
     * Removing sysclass
     */
    $module_app->deleteDbData();

    \Gino\Link::HttpCall($this->_home, $this->_class_name.'-manageSysClass', 'block=list');
  }

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
    $buffer .= "<li>"._("altri file di complemento (es. class_newsItem.php)")."</li>";
    $buffer .= "</ul>";
    
    $view = new \Gino\View();
    $view->setViewTpl('section');
    $dict = array(
      'title' => _('Informazioni'),
      'class' => 'admin',
      'content' => $buffer
    );

    return $view->render($dict);
  }
}
?>
