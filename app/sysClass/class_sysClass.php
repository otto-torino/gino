<?php
/**
 * @file class_sysClass.php
 * @brief Contiene la classe sysClass
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

/**
 * @brief Libreria per la gestione dei moduli di sistema
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class sysClass extends Controller {

  private $_title;
  private $_action;
  private $_archive_extensions;
  
  function __construct(){

    parent::__construct();

    $this->_title = _("Gestione classi di sistema");

    $this->_action = cleanVar($_REQUEST, 'action', 'string', '');

    $this->_archive_extensions = array('zip');
  }
  
  private function nameRole($role)
  {
    $query = "SELECT name FROM ".$this->_tbl_user_role." WHERE role_id='$role'";
    $a = $this->_db->selectquery($query);
    if(sizeof($a) > 0)
    {
      foreach($a AS $b)
      {
        $name = htmlChars($b['name']);
      }
    }
    return $name;
  }
  
  private function accessRoleValue($name, $valuedb, $text, $role_list){
    
    $GINO = "<p class=\"line\"><span class=\"subtitle\">$text</span><br />";
    foreach($role_list AS $key => $value)
    {
      if(!$this->_access->AccessVerifyRoleIDIf($key)) $disabled = 'disabled'; else $disabled = '';
      if($key == $valuedb) $checked = 'checked'; else $checked = '';

      $GINO .= "<input type=\"radio\" id=\"$name\" name=\"$name\" value=\"$key\" $checked $disabled /> $value<br />";
    }
    $GINO .= "</p>\n";
    
    return $GINO;
  }

  /**
   * Interfaccia amministrativa per la gestione dei moduli di sistema
   * 
   * @see $_access_admin
   * @return string
   */
  public function manageSysClass() {
    
    $this->requirePerm('can_admin');

    $id = cleanVar($_GET, 'id', 'int', '');
    $block = cleanVar($_GET, 'block', 'string', null);

    $link_dft = "<a href=\"".$this->_home."?evt[".$this->_class_name."-manageSysClass]\">"._("Informazioni")."</a>";
    $link_list = "<a href=\"".$this->_home."?evt[".$this->_class_name."-manageSysClass]&block=list\">"._("Moduli installati")."</a>";
    $link_install = "<a href=\"".$this->_home."?evt[".$this->_class_name."-manageSysClass]&block=install\">"._("Installazione")."</a>";
    $sel_link = $link_dft;

    if($block == 'cippa') {
        $GINO .= "<div class=\"vertical_2\">\n";
    if($id && $this->_action==$this->_act_modify) $GINO .= $this->formEditSysClass($id);
    elseif($id && $this->_action==$this->_act_delete) $GINO .= $this->formRemoveSysClass($id);
    elseif($this->_action == 'insert')
    {
      $GINO .= $this->formInsertSysClass();
      $GINO .= $this->formManualSysClass();
    }
    else $GINO .= $this->info();
    $GINO .= "</div>\n";
    $GINO .= "<div class=\"clearfix\"></div>\n";

    }
    elseif($block == 'list') {
      $GINO = $this->sysClassList($id);
      $sel_link = $link_list;
    }
    elseif($block == 'install') {
      $GINO = $this->formInsertSysClass();
      $sel_link = $link_install;
    }
    else {
      $GINO = $this->info();
    }

    $view = new view();
    $view->setViewTpl('tab');
    $dict = array(
      'title' => _('Moduli di sistema'),
      'links' => array($link_install, $link_list, $link_dft),
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
  private function sysClassList($sel_id) {

    $link_insert = "<a href=\"$this->_home?evt[$this->_class_name-manageSysClass]&action=insert\">".pub::icon('insert', _("installa nuovo modulo"))."</a>";

    $view_table = new view();
    $view_table->setViewTpl('table');

    $rows = $this->_registry->db->select("id, label, name, masquerade, removable, class_version", TBL_MODULE_APP, null, array('order'=>"order_list ASC"));		
    if($rows and count($rows)) {

      $GINO = "<p>"._('Di seguito l\'elenco di tutti i moduli installati sul sistema. Alcuni moduli non sono rimuovibili in quanto necessari al corretto funzionamento del sistema. In caso di eliminazione di un modulo istanziabile verranno eliminate anche tutte le sue istanze.')."</p>";

      $heads = array(
        _('id'),
        _('etichetta'),
        _('attivo'),
        _('versione'),
        '',
      );
      $tbl_rows = array();
      foreach($rows as $row) {
        $id = htmlChars($row['id']);
        $label = htmlChars($this->_trd->selectTXT(TBL_MODULE_APP, 'label', $row['id']));
        $removable = htmlChars($row['removable']);
        $masquerade = htmlChars($row['masquerade']);
        $version = htmlChars($row['class_version']);
        $active = ($masquerade=='no')?_("si"):_("no");
        $selected = ($id===$sel_id)?true:false;
        $link_modify = "<a href=\"$this->_home?evt[$this->_class_name-manageSysClass]&id=$id&action=modify\">".pub::icon('modify', _("modifica/upgrade"))."</a>";
        $link_delete = ($removable=='yes')? "<a href=\"$this->_home?evt[$this->_class_name-manageSysClass]&id=$id&action=delete\">".pub::icon('delete', _("elimina"))."</a>":"";

        $tbl_rows[] = array(
          $id,
          $label,
          $active,
          $version,
          implode(' &#160; ', array($link_modify, $link_delete))
        );

        $text = "$label<br/>ID: $id - <span style=\"font-weight:normal\">"._("versione: ").$version;
        //$GINO .= $htmlList->item($text, array($link_delete, $link_modify), $selected, true);

      }
      $dict = array(
        'class' => 'table table-striped table-hover',
        'heads' => $heads,
        'rows' => $tbl_rows
      );
      $GINO .= $view_table->render($dict);
      //$GINO .= $htmlList->end();
    }
    else {
      $GINO = _('Non risultano moduli di sistema');
    }

    $view = new view();
    $view->setViewTpl('section');
    $dict = array(
      'title' => _('Elenco'),
      'class' => 'admin',
      'links' => $link_insert,
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
    
    $gform = loader::load('Form', array('gform', 'post', true));
    $gform->load('dataform');

    $GINO = "<p>"._("Caricare il pacchetto del modulo. Se la procedura di installazione va a buon fine modificare il modulo appena inserito per personalizzarne l'etichetta ed eventualmente altri parametri.")."</p>\n";
    
    $required = 'archive';
    $GINO .= $gform->open($this->_home."?evt[".$this->_class_name."-actionInsertSysClass]", true, $required);
    $GINO .= $gform->cfile('archive', '', _("Archivio"), array("extensions"=>$this->_archive_extensions, "del_check"=>false, "required"=>true));
    $GINO .= $gform->cinput('submit_action', 'submit', _("installa"), '', array("classField"=>"submit"));
    $GINO .= $gform->close();

    $view = new view();
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
    
    $this->accessType($this->_access_admin);

    $link_error = $this->_home."?evt[$this->_class_name-manageSysClass]&action=$this->_act_insert";

    if(!pub::enabledZip())
      exit(error::errorMessage(array('error'=>_("la classe ZipArchive non è supportata"), 'hint'=>_("il pacchetto deve essere installato con procedura manuale")), $link_error));
    
    $archive_name = $_FILES['archive']['name'];
    $archive_tmp = $_FILES['archive']['tmp_name'];

    if(empty($archive_tmp)) exit(error::errorMessage(array('error'=>_("file mancante"), 'hint'=>_("controllare di aver selezionato un file")), $link_error));
    
    $class_name = preg_replace("/[^a-zA-Z0-9].*?.zip/", "", $archive_name);
    if(preg_match("/[\.\/\\\]/", $class_name)) exit(error::errorMessage(array('error'=>_("pacchetto non conforme alle specifiche")), $link_error));
    
    /*
     * dump db 
     */
    $this->_db->dumpDatabase(SITE_ROOT.OS.'backup'.OS.'dump_'.date("d_m_Y_H_i_s").'.sql');

    $class_dir = APP_DIR.OS.$class_name;
    @mkdir($class_dir, 0755) || exit(error::errorMessage(array('error'=>_("impossibile creare la cartella base del modulo"), 'hint'=>_("controllare i permessi di scrittura")), $link_error));

    $db_conf = array('name'=>$class_name, 'version'=>null, 'type'=>"class", 'role1'=>1, 'role2'=>1, 'role3'=>1, 'role_group'=>0, 'tbl_name'=>null, 'instance'=>'no', 'description'=>null, 'removable'=>'yes', 'folders'=>null);

    /*
     * Extract archive
     */
    $uploadfile = $class_dir.OS.$archive_name;
    if(move_uploaded_file($archive_tmp, $uploadfile)) $up = 'ok';
    else $up = 'ko';
    
    $zip = new ZipArchive;
    $res = $zip->open($uploadfile);
    if ($res === true) {
            $zip->extractTo($class_dir);
            $zip->close();
        } else {
      $this->deleteFileDir($class_dir, true);
            exit(error::errorMessage(array('error'=>_("impossibile scompattare il pacchetto")), $link_error));
        }

    /*
     * Parsering config file
     */
    if(!is_readable($class_dir.OS."config.txt")) {
      $this->deleteFileDir($class_dir, true);
      exit(error::errorMessage(array('error'=>_("pacchetto non conforme alle specifiche. File di configurazione mancante.")), $link_error));
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
    if(!in_array($db_conf['type'], array('class', 'func'))) $dbConfError = true;
    if(!preg_match("/^\d$/", $db_conf['role1'])) $dbConfError = true;
    if(!preg_match("/^\d$/", $db_conf['role2'])) $dbConfError = true;
    if(!preg_match("/^\d$/", $db_conf['role3'])) $dbConfError = true;
    if(!preg_match("/^\d$/", $db_conf['role_group'])) $dbConfError = true;
    if(!in_array($db_conf['instance'], array('yes', 'no'))) $dbConfError = true;
    if(!in_array($db_conf['removable'], array('yes', 'no'))) $dbConfError = true;
    if($dbConfError) {
      $this->deleteFileDir($class_dir, true);
      exit(error::errorMessage(array('error'=>_("pacchetto non conforme alle specifiche.")), $link_error));
    }

    /*
     * Insert DB record
     */
    $query = "SELECT id FROM ".$this->_tbl_module_app." WHERE name='".$db_conf['name']."'";
    $a = $this->_db->selectquery($query);
    if(sizeof($a)>0) {
      $this->deleteFileDir($class_dir, true);
      exit(error::errorMessage(array('error'=>_("modulo con lo stesso nome già presente nel sistema")), $link_error));
    }

    $query = "SELECT MAX(order_list) AS mo FROM ".$this->_tbl_module_app;
    $a = $this->_db->selectquery($query);
    $ol = $a[0]['mo']+1;

    $query = "INSERT INTO ".$this->_tbl_module_app." (label, name, type, role1, role2, role3, masquerade, role_group, tbl_name, order_list, instance, description, removable, class_version) VALUES ('".$db_conf['name']."', '".$db_conf['name']."', '".$db_conf['type']."', '".$db_conf['role1']."', '".$db_conf['role2']."', '".$db_conf['role3']."', 'no', '".$db_conf['role_group']."', '".$db_conf['tbl_name']."', '$ol', '".$db_conf['instance']."', '".$db_conf['description']."', '".$db_conf['removable']."', '".$db_conf['version']."')";	
    $result = $this->_db->actionquery($query);

    if(!$result) {
      $this->deleteFileDir($class_dir, true);
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
          $this->deleteFileDir($class_dir, true);
          $query = "DELETE FROM ".$this->_tbl_module_app." WHERE name='".$db_conf['name']."'";
          $result = $this->_db->actionquery($query);
          foreach(array_reverse($created_flds) as $created_fld) {
            $this->deleteFileDir($created_fld, true);
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
        $this->deleteFileDir($class_dir, true);
        $query = "DELETE FROM ".$this->_tbl_module_app." WHERE name='".$db_conf['name']."'";
        $result = $this->_db->actionquery($query);
        foreach(array_reverse($created_flds) as $created_fld) {
          $this->deleteFileDir($created_fld, true);
        }
        exit(error::errorMessage(array('error'=>_("impossibile creare le tabelle")), $link_error));
      }
    }	
    
    /*
     * Removing installations' files
     */
    @unlink($uploadfile);
    @unlink($class_dir.OS."config.txt");
    if(is_readable($class_dir.OS.$class_name.".sql")) @unlink($class_dir.OS.$class_name.".sql");

    EvtHandler::HttpCall($this->_home, $this->_class_name.'-manageSysClass', '');
  }
  
  /**
   * Form per l'installazione manuale di un modulo di sistema
   * 
   * @return string
   */
  private function formManualSysClass() {
    
    $gform = loader::load('Form', array('mform', 'post', true));
    $gform->load('mdataform');

    $GINO = "<p>"._("Per eseguire l'installazione manuale effettuare il submit del form prendendo come riferimento il file config.txt.");
    $GINO .= "<br />"._("In seguito effettuare la procedura indicata").":</p>\n";
    $GINO .= "<ul>";
    $GINO .= "<li>creare la directory app/nomeclasse e copiare tutti i file della libreria</li>";
    $GINO .= "<li>creare la directory contents/nomeclasse se è previsto l'upload di file</li>";
    $GINO .= "<li>di default le classi vengono create rimovibili</li>";
    $GINO .= "<li>eseguire manualmente le query di creazione delle tabelle presenti nel file SQL</li>";
    $GINO .= "</ul>";
    
    $required = 'label,name,rolegroup,tblname';
    $GINO .= $gform->open($this->_home."?evt[".$this->_class_name."-actionManualSysClass]", false, $required);
    
    $instance = 'yes';
    $js = "onchange=\"ajaxRequest('post', '{$this->_home}?pt[{$this->_class_name}-instanceClass]', 'opt='+$(this).value, 'instance_class')\"";
    $GINO .= $gform->cselect('instance', $instance, array('yes'=>_("istanziabile"), 'no'=>_("non istanziabile")), _("Tipo di classe"), array('js'=>$js));
    //$GINO .= $gform->cell($this->instanceClass($instance), array("id"=>"instance_class"));
    $GINO .= $this->instanceClass($instance);
    
    $GINO .= $gform->cinput('label', 'text', '', _("Etichetta"), array("required"=>true, "size"=>40, "maxlength"=>100));
    $GINO .= $gform->cinput('name', 'text', '', _("Nome classe"), array("required"=>true, "size"=>40, "maxlength"=>100));
    $GINO .= $gform->cinput('rolegroup', 'text', '', _("ID gruppo amministratore della classe"), array("required"=>true, "size"=>2, "maxlength"=>2));
    $GINO .= $gform->ctextarea('description', '', _("Descrizione"), array("cols"=>45, "rows"=>4));
    $GINO .= $gform->cinput('tblname', 'text', '', _("Nome radice delle tabelle"), array("required"=>true, "size"=>40, "maxlength"=>30));
    $GINO .= $gform->cinput('version', 'text', '', _("Versione"), array("required"=>false, "size"=>40, "maxlength"=>200));
    
    $GINO .= $gform->cinput('submit_action', 'submit', _("installa"), '', array("classField"=>"submit"));
    $GINO .= $gform->close();

    $view = new view();
    $view->setViewTpl('section');
    $dict = array(
      'title' => _('Installazione manuale'),
      'class' => 'admin',
      'content' => $GINO
    );

    return $view->render();
  }
  
  public function instanceClass($instance='') {
  
    $ajax = cleanVar($_POST, 'opt', 'string', '');
    if($ajax != '')
      $instance = $ajax;
    
    $gform = new Form('mform', 'post', false);
    $gform->load('mdataform');
    
    $GINO = '';
    
    if($instance == 'no')
    {
      $GINO .= $gform->startTable();
      $role_list = $this->_access->listRole();
      $role = $this->_access->default_role;
      $GINO .= $gform->cradio('role1', $role, $role_list, '', _("Permessi di visualizzazione"), array("aspect"=>"v"));
      $GINO .= $gform->cradio('role2', $role, $role_list, '', _("Ruolo 2"), array("aspect"=>"v"));
      $GINO .= $gform->cradio('role3', $role, $role_list, '', _("Ruolo 3"), array("aspect"=>"v"));
      $GINO .= $gform->endTable();
    }
    
    return $GINO;
  }

  /**
   * Installazione manuale di un modulo di sistema
   * 
   * Il modulo viene attivato nell'installazione
   * 
   * @see $_access_admin
   */
  public function actionManualSysClass() {
    
    $this->accessType($this->_access_admin);
    
    $gform = new Form('mform', 'post', false);
    $gform->save('mdataform');
    $req_error = $gform->arequired();

    $link_error = $this->_home."?evt[$this->_class_name-manageSysClass]&action=$this->_act_insert";
    
    if($req_error > 0) 
      exit(error::errorMessage(array('error'=>1), $link_error));

    $instance = cleanVar($_POST, 'instance', 'string', '');
    $label = cleanVar($_POST, 'label', 'string', '');
    $name = cleanVar($_POST, 'name', 'string', '');
    $rolegroup = cleanVar($_POST, 'rolegroup', 'int', '');
    $description = cleanVar($_POST, 'description', 'string', '');
    $tblname = cleanVar($_POST, 'tblname', 'string', '');
    $version = cleanVar($_POST, 'version', 'string', '');
    
    $role1 = cleanVar($_POST, 'role1', 'int', '');
    $role2 = cleanVar($_POST, 'role2', 'int', '');
    $role3 = cleanVar($_POST, 'role3', 'int', '');
    
    if(preg_match("/[\.\/\\\]/", $name)) exit(error::errorMessage(array('error'=>_("pacchetto non conforme alle specifiche")), $link_error));
    
    // Default values
    $type = 'class';
    $removable = 'yes';

    $query = "SELECT id FROM ".$this->_tbl_module_app." WHERE name='$name'";
    $a = $this->_db->selectquery($query);
    if(sizeof($a)>0) {
      exit(error::errorMessage(array('error'=>_("modulo con lo stesso nome già presente nel sistema")), $link_error));
    }

    $query = "SELECT MAX(order_list) AS mo FROM ".$this->_tbl_module_app;
    $a = $this->_db->selectquery($query);
    $ol = $a[0]['mo']+1;

    $query = "INSERT INTO ".$this->_tbl_module_app." (label, name, type, role1, role2, role3, masquerade, role_group, tbl_name, order_list, instance, description, removable, class_version) VALUES 
    ('$label', '$name', '$type', '$role1', '$role2', '$role3', 'no', '$rolegroup', '$tblname', '$ol', '$instance', '$description', '$removable', '$version')";	
    $result = $this->_db->actionquery($query);

    if(!$result) {
      exit(error::errorMessage(array('error'=>_("impossibile installare il pacchetto")), $link_error));
    }
    
    EvtHandler::HttpCall($this->_home, $this->_class_name.'-manageSysClass', '');
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
    
    $gform = new Form('gform', 'post', true);
    $gform->load('dataform');

    $className = $this->_db->getFieldFromId($this->_tbl_module_app, 'name', 'id', $id);
    $query = "SELECT * FROM ".$this->_tbl_module_app." WHERE id='$id'";
    $a = $this->_db->selectquery($query);
    if(sizeof($a)>0) {
      $label = htmlInput($a[0]['label']);
      $description = htmlInput($a[0]['description']);
      $instance = htmlInput($a[0]['instance']);
      $masquerade = htmlInput($a[0]['masquerade']);
      $version = htmlInput($a[0]['class_version']);
      $active = ($masquerade=='no')?'yes':'no';
      $role1 = htmlInput($a[0]['role1']);
      $role2 = htmlInput($a[0]['role2']);
      $role3 = htmlInput($a[0]['role3']);
    }
    else exit(error::syserrorMessage("sysClass", "formEditSysClass", "ID non associato ad alcuna classe di sistema", __LINE__));

    $htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'h1', 'headerLabel'=>_("Modifica")." $label"));

    $required = 'label';
    $GINO = $gform->form($this->_home."?evt[".$this->_class_name."-actionEditSysClass]", '', $required);
    $GINO .= $gform->hidden('id', $id);
    $GINO .= $gform->cinput('label', 'text', $gform->retvar('label', $label), _("Etichetta"), array("required"=>true, "size"=>40, "maxlength"=>200, 
      "trnsl"=>true, "trnsl_table"=>TBL_MODULE_APP, "field"=>"label", "trnsl_id"=>$id));
    $GINO .= $gform->ctextarea('description', $gform->retvar('description', $description), _("Descrizione"), array("cols"=>45, "rows"=>4, 
      "trnsl"=>true, "trnsl_table"=>TBL_MODULE_APP, "field"=>"description", "trnsl_id"=>$id));
    
    $role_list = $this->_access->listRole();

    if($instance == 'no') {
      if(method_exists($className, 'outputFunctions')) {
        $GINO .= $gform->cradio('role1', $role1, $role_list, '', _("Permessi di visualizzazione"), array("required"=>true, "aspect"=>"v"));
      }

      // Metodi aggiuntivi
      if(method_exists($className, 'permission'))
      {
        $class = new $className;
        $permission = $class->permission();
      
        if(!empty($permission[0]))
        {
          $GINO .= $gform->cradio('role2', $role2, $role_list, '', $permission[0], array("required"=>true, "aspect"=>"v"));
        }
        if(!empty($permission[1]))
        {
          $GINO .= $gform->cradio('role3', $role3, $role_list, '', $permission[1], array("required"=>true, "aspect"=>"v"));
        }
      }
    }

    $GINO .= $gform->cinput('submit_action', 'submit', _("modifica"), '', array("classField"=>"submit"));
    $GINO .= $gform->cform();

    $htmlsection->content = $GINO;
    
    $GINO = $htmlsection->render();

    //$GINO .= $this->formActivateSysClass($id, $active);
    
    $GINO .= $this->formUpgradeSysClass($id, $version);

    return $GINO;
  }

  /**
   * Form di attivazione del modulo
   * 
   * @param integer $id valore ID del modulo
   * @param string $active stato dell'attivazione
   * @return string 
   */
  private function formActivateSysClass($id, $active) {
    
    $gform = new Form('gform', 'post', true);
    $gform->load('dataform');

    $htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'h1', 'headerLabel'=>_("Attivazione")));
    
    $required = 'active';
    $GINO = $gform->form($this->_home."?evt[".$this->_class_name."-actionEditSysClassActive]", '', $required);
    $GINO .= $gform->hidden('id', $id);

    $GINO .= $gform->cradio('active', $active, array("yes"=>_("si"),"no"=>_("no")), 'no', array(_("Attivo"), _("Attenzione! La disattivazione di alcuni moduli potrebbe causare malfunzionamenti nel sistema")), array("required"=>true));
    $GINO .= $gform->cinput('submit_action', 'submit', _("modifica"), '', array("classField"=>"submit"));

    $GINO .= $gform->cform();

    $htmlsection->content = $GINO;
    
    return $htmlsection->render();
  }

  /**
   * Form di aggiornamento del modulo
   * 
   * @param integer $id valore ID del modulo
   * @param string $version versione del modulo
   * @return string
   */
  private function formUpgradeSysClass($id, $version) {
    
    $gform = new Form('gform', 'post', true);
    $gform->load('dataform');

    $htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'h1', 'headerLabel'=>_("Upgrade")));
    
    $GINO = "<p>"._("La versione del modulo attualmente installato è: ")."<b>".$version."</b></p>\n";
    $GINO .= "<p>"._("Verificare se sono presenti aggiornamenti stabili compatibili con il sistema ed in caso affermativo procedere all'upgrade.")."</p>\n";
    $GINO .= "<p>"._("Nel caso si verificassero errori gravi viene comunque effettuato un dump dell'intero database all'interno della cartella <b>backup</b>.")."</p>\n";

    $required = 'archive';
    $GINO .= $gform->form($this->_home."?evt[".$this->_class_name."-actionUpgradeSysClass]", true, $required);
    $GINO .= $gform->hidden('id', $id);

    $GINO .= $gform->cfile('archive', '', _("Archivio"), array("extensions"=>$this->_archive_extensions, "del_check"=>false, "required"=>true));
    $GINO .= $gform->cinput('submit_action', 'submit', _("procedi"), _("Upgrade del modulo"), array("classField"=>"submit"));

    $GINO .= $gform->cform();

    $htmlsection->content = $GINO;
    
    return $htmlsection->render();
  }

  /**
   * Modifica del modulo
   * 
   * @see $_access_admin
   */
  public function actionEditSysClass() {
  
    $this->accessType($this->_access_admin);

    $id = cleanVar($_POST, 'id', 'int', '');
    $label = cleanVar($_POST, 'label', 'string', '');
    $description = cleanVar($_POST, 'description', 'string', '');
    $role1 = cleanVar($_POST, 'role1', 'int', '');
    $role2 = cleanVar($_POST, 'role2', 'int', '');
    $role3 = cleanVar($_POST, 'role3', 'int', '');

    $query = "UPDATE ".$this->_tbl_module_app." SET label='$label', description='$description'";
    if(isset($_POST['role1'])) $query .= ", role1='$role1'";
    if(isset($_POST['role2'])) $query .= ", role2='$role2'";
    if(isset($_POST['role3'])) $query .= ", role3='$role3'";
    $query .= " WHERE id='$id'";
    $result = $this->_db->actionquery($query);
    
    EvtHandler::HttpCall($this->_home, $this->_class_name.'-manageSysClass', '');
  }

  /**
   * Modifica dello stato dell'attivazione del modulo
   * 
   * @see $_access_admin
   */
  public function actionEditSysClassActive() {
  
    $this->accessType($this->_access_admin);

    $id = cleanVar($_POST, 'id', 'int', '');
    $active = cleanVar($_POST, 'active', 'string', '');
    $masquerade = ($active=='no')?'yes':'no';

    $query = "UPDATE ".$this->_tbl_module_app." SET masquerade='$masquerade' WHERE id='$id'";
    $result = $this->_db->actionquery($query);
    
    EvtHandler::HttpCall($this->_home, $this->_class_name.'-manageSysClass', '');
  }

  /**
   * Aggiornamento del modulo
   * 
   * @see $_access_admin
   */
  public function actionUpgradeSysClass() {
    
    $this->accessType($this->_access_admin);

    $id = cleanVar($_POST, 'id', 'int', '');
    $module_class_name = $this->_db->getFieldFromId($this->_tbl_module_app, 'name', 'id', $id);
    $old_instance = $this->_db->getFieldFromId($this->_tbl_module_app, 'instance', 'id', $id);
    $old_tbl_name = $this->_db->getFieldFromId($this->_tbl_module_app, 'tbl_name', 'id', $id);
    $old_version = $this->_db->getFieldFromId($this->_tbl_module_app, 'version', 'id', $id);
    $old_description = $this->_db->getFieldFromId($this->_tbl_module_app, 'description', 'id', $id);
    $link_error = $this->_home."?evt[$this->_class_name-manageSysClass]&id=$id&action=$this->_act_modify";

    $archive_name = $_FILES['archive']['name'];
    $archive_tmp = $_FILES['archive']['tmp_name'];

    if(empty($archive_tmp)) exit(error::errorMessage(array('error'=>_("file mancante"), 'hint'=>_("controllare di aver selezionato un file")), $link_error));
    
    $class_name = preg_replace("/[^a-zA-Z0-9].*?upgrade.*?.zip/", "", $archive_name);
    if($class_name!=$module_class_name) exit(error::errorMessage(array('error'=>_("upgrade fallito"), 'hint'=>_("il pacchetto non pare essere un upgrade del modulo esistente")), $link_error));
    if(preg_match("/[\.\/\\\]/", $class_name)) exit(error::errorMessage(array('error'=>_("pacchetto non conforme alle specifiche")), $link_error));

    /*
     * dump db 
     */
    $this->_db->dumpDatabase(SITE_ROOT.OS.'backup'.OS.'dump_'.date("d_m_Y_H_i_s").'.sql');

    $class_dir = APP_DIR.OS.$class_name."_inst_tmp";
    $module_dir = APP_DIR.OS.$class_name;
    @mkdir($class_dir, 0755) || exit(error::errorMessage(array('error'=>_("upgrade fallito"), 'hint'=>_("controllare i permessi di scrttura")), $link_error));

    $db_conf = array('name'=>$class_name, 'version'=>null, 'tbl_name'=>null, 'instance'=>null, 'description'=>null, 'folders'=>null);
    $noCopyFiles = array('config.txt', $class_name.'.sql');
    /*
     * Extract archive
     */
    $uploadfile = $class_dir.OS.$archive_name;
    if(move_uploaded_file($archive_tmp, $uploadfile)) $up = 'ok';
    else $up = 'ko';
    
    $zip = new ZipArchive;
    $res = $zip->open($uploadfile);
    if ($res === true) {
            $zip->extractTo($class_dir);
            $zip->close();
        } else {
      $this->deleteFileDir($class_dir, true);
            exit(error::errorMessage(array('error'=>_("Impossibile scompattare il pacchetto")), $link_error));
        }
    
    /*
     * Parsering config file
     */
    if(!is_readable($class_dir.OS."config.txt")) {
      $this->deleteFileDir($class_dir, true);
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
    if(!in_array($db_conf['instance'], array('yes', 'no', null))) $dbConfError = true;
    if($dbConfError) {
      $this->deleteFileDir($class_dir, true);
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
          $this->deleteFileDir($class_dir, true);
          foreach(array_reverse($created_flds) as $created_fld) {
            $this->deleteFileDir($created_fld, true);
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
        $this->deleteFileDir($class_dir, true);
        foreach(array_reverse($created_flds) as $created_fld) {
          $this->deleteFileDir($created_fld, true);
        }
        exit(error::errorMessage(array('error'=>_("Upgrade fallito - impossibile creare le tabelle")), $link_error));
      }
    }	

    /*
     * Update DB data tbl module app
     */
    $sets = $unsets = array();
    if($db_conf['version']) {$sets[] = "class_version='".$db_conf['version']."'";$unsets[] = "class_version='$old_version'";}
    if($db_conf['tbl_name']) {$sets[] = "tbl_name='".$db_conf['tbl_name']."'";$unsets[] = "tbl_name='$old_tbl_name'";}
    if($db_conf['instance']) {$sets[] = "instance='".$db_conf['instance']."'";$unsets[] = "instance='$old_instance'";}
    if($db_conf['description']) {$sets[] = "description='".$db_conf['description']."'";$unsets[] = "description='$old_description'";}
    if(count($sets)) {
      $query = "UPDATE ".$this->_tbl_module_app." SET ".implode(",", $sets)." WHERE name='".$db_conf['name']."'";
      $result = $this->_db->actionquery($query);
    }

    /*
     * Move and overwrite files
     */
    @unlink($class_dir.OS.$archive_name);
    $res = $this->upgradeFolders($class_dir, $module_dir, $noCopyFiles);
    if(!$res) exit(error::errorMessage(array('error'=>_("Si è verificato un errore durante l'upgrade. Uno o più file non sono stati copiati correttamente. Contattare l'amministratore del sistema per risolvere il problema.")), $link_error));
    
    /*
     * Removing tmp install folder
     */
    $this->deleteFileDir($class_dir, true);

    EvtHandler::HttpCall($this->_home, $this->_class_name.'-manageSysClass', '');
  }

  private function upgradeFolders($files_dir, $module_dir, $noCopyFiles) {

    $res = true;
    $files = searchNameFile($files_dir);
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
    
    $gform = new Form('gform', 'post', true);
    $gform->load('dataform');

    $className = $this->_db->getFieldFromId($this->_tbl_module_app, 'name', 'id', $id);
    $query = "SELECT * FROM ".$this->_tbl_module_app." WHERE id='$id'";
    $a = $this->_db->selectquery($query);
    if(sizeof($a)>0) {
      $label = htmlInput($a[0]['label']);
      $name = htmlChars($a[0]['name']);
      $instance = htmlInput($a[0]['instance']);
    }
    else exit(error::syserrorMessage("sysClass", "formEditSysClass", "ID non associato ad alcuna classe di sistema", __LINE__));

    $htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'h1', 'headerLabel'=>_("Disinstallazione modulo di sitema")));

    $GINO = "<p>"._("Attenzione! La disinstallazione di un modulo di sistema potrebbe provocare dei malfunzionamenti.")."</p>\n";
    if($instance=='yes') {
      $GINO .= "<p>"._("Il modulo ").$label._(" prevede la creazione di istanze, per tanto la sua eliminazione determina l'eliminazione di ogni istanza e dei dati associati.")."</p>\n";
      $mdlInstances = array();
      $query = "SELECT label FROM ".$this->_tbl_module." WHERE class='$name' ORDER BY label";
      $a = $this->_db->selectquery($query);
      if(sizeof($a)>0) {
        foreach($a as $b) {
          $mdlInstances[] = "<b>".htmlChars($b['label'])."</b>";
        }
      }
      if(count($mdlInstances)) {
        $GINO .= "<p>"._("Attualmente nel sitema sono presenti le seguenti istanze: ").implode(",", $mdlInstances)."</p>\n";
      }
      else 
        $GINO .= "<p>"._("Attualmente nel sistema non sono presenti istanze.")."</p>\n";
    }
    $GINO .= "<p>"._("La disinstallazione non determina la rimozione dei moduli all'interno dei template.")."</p>\n";

    $required = '';
    $GINO .= $gform->form($this->_home."?evt[".$this->_class_name."-actionRemoveSysClass]", '', $required);
    $GINO .= $gform->hidden('id', $id);
    $GINO .= $gform->hidden('instance', $instance);
    $GINO .= $gform->cinput('submit_action', 'submit', _("disinstalla"), _("Sicuro di voler procedere?"), array("classField"=>"submit"));
    $GINO .= $gform->cform();

    $htmlsection->content = $GINO;
    
    return $htmlsection->render();
  }

  /**
   * Eliminazione di un modulo di sistema
   * 
   * @see $_access_admin
   */
  public function actionRemoveSysClass() {
    
    $this->accessType($this->_access_admin);

    $id = cleanVar($_POST, 'id', 'int', '');
    $instance = cleanVar($_POST, 'instance', 'string', '');
    $className = $this->_db->getFieldFromId($this->_tbl_module_app, 'name', 'id', $id);

    /*
     * Removing instances if any 
     */
    if($instance=='yes') {
      $query = "SELECT id FROM ".$this->_tbl_module." WHERE class='$className'";
      $a = $this->_db->selectquery($query);
      if(sizeof($a)>0) {
        foreach($a as $b) {
          $classObj = new $className($b['id']);
          $classObj->deleteInstance();
        }
      }
      $query = "DELETE FROM ".$this->_tbl_module." WHERE class='$className'";	
      $result = $this->_db->actionquery($query);
    }

    /*
     * Drop class tables and removing contents folders
     */
    $classElements = call_user_func(array($className, 'getClassElements'));
    foreach($classElements['tables'] as $tbl) {
      $query = "DROP TABLE $tbl";	
      $result = $this->_db->actionquery($query);
      language::deleteTranslations($tbl, 'all');
    }
    foreach($classElements['folderStructure'] as $fld=>$sub) {
      $this->deleteFileDir($fld, true);
    }

    /*
     * Removing class directory
     */
    $this->deleteFileDir(APP_DIR.OS.$className, true);
    
    /*
     * Removing from DB
     */
    $query = "DELETE FROM ".$this->_tbl_module_app." WHERE id='$id'";	
    $result = $this->_db->actionquery($query);

    EvtHandler::HttpCall($this->_home, $this->_class_name.'-manageSysClass', '');
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
    
    $view = new view();
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
