<?php
/**
 * @file class_index.php
 * @brief Contiene la classe index
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

/**
 * @brief 
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class index extends Controller{

  private $_page;
  
  function __construct(){

    parent::__construct();

  }
  
  /**
   * Elenco dei metodi che possono essere richiamati dal menu e dal template
   * 
   * @return array
   */
  public static function outputFunctions() {

    $list = array(
      "admin_page" => array("label"=>_("Home page amministrazione"), "role"=>'2')
    );

    return $list;
  }

  /**
   * Pagina di autenticazione
   * 
   * @see sysfunc::tableLogin()
   * @return string
   */
  public function auth_page(){

    $registration = cleanVar($_GET, 'reg', 'int', '');
    
    if($registration == 1) $control = true; else $control = false;
    
    $GINO = "<div id=\"section_indexAuth\" class=\"section\">";

    $GINO .= "<p>"._("Per procedere è necessario autenticarsi.")."</p>";
    
    $func = new sysfunc();
    $GINO .= $func->tableLogin($control, $this->_class_name);
    $GINO .= "</div>";
    
    return $GINO;
  }

  /**
   * Home page amministrazione
   * 
   * @return string
   */
  public function admin_page(){

    if(!$this->_access->getAccessAdmin()) {
      $this->session->auth_redirect = "$this->_home?evt[".$this->_class_name."-admin_page]";
      Link::HttpCall($this->_home, $this->_class_name.'-auth_page', '');
    }

    $buffer = '';
    $sysMdls = $this->sysModulesManageArray();
    $mdls = $this->modulesManageArray();
    if(count($sysMdls)) {
      $GINO = "<table class=\"table table-striped table-hover table-bordered\">";
      foreach($sysMdls as $sm) {
        $GINO .= "<tr>";
        $GINO .= "<th><a href=\"$this->_home?evt[".$sm['name']."-manage".ucfirst($sm['name'])."]\">".htmlChars($sm['label'])."</a></th>";
        $GINO .= "<td class=\"mdlDescription\">".htmlChars($sm['description'])."</td>";
        $GINO .= "</tr>";
      }
      $GINO .= "</table>\n";

      $view = new View();
      $view->setViewTpl('section');
      $view->assign('class', 'admin');
      $view->assign('title', _("Amministrazione sistema"));
      $view->assign('content', $GINO);

      $buffer .= $view->render();
    }
    if(count($mdls)) {
    
      $GINO = "<table class=\"table table-striped table-hover table-bordered\">";
      foreach($mdls as $m) {
        $GINO .= "<tr>";
        $GINO .= "<th><a href=\"$this->_home?evt[".$m['name']."-manageDoc]\">".htmlChars($m['label'])."</a></th>";
        $GINO .= "<td>".htmlChars($m['description'])."</td>";
        $GINO .= "</tr>";
      }
      $GINO .= "</table>\n";

      $view = new View();
      $view->setViewTpl('section');
      $view->assign('class', 'admin');
      $view->assign('title', _("Amministrazione moduli"));
      $view->assign('content', $GINO);

      $buffer .= $view->render();

    }
    return $buffer;
  }

  /**
   * Elenco dei moduli di sistema visualizzabili nell'area amministrativa
   * 
   * @return array
   */
  public function sysModulesManageArray() {

    if(!$this->_access->getAccessAdmin()) {
      return array();
    }

    // @todo write sysClass as model
    $list = array();
    $rows = $this->_db->select('id, label, name, description', TBL_MODULE_APP, " masquerade='no' AND instance='no'", "order_list");
    if($rows and count($rows)) {
      foreach($rows as $row) {
        if($this->_registry->user->hasAdminPerm($row['name']) and method_exists($row['name'], 'manage'.ucfirst($row['name']))) {
          //if($this->_access->AccessVerifyGroupIf($b['name'], 0, '', 'ALL') && method_exists($b['name'], 'manage'.ucfirst($b['name'])))
          $list[$row['id']] = array("label"=>$this->_trd->selectTXT(TBL_MODULE_APP, 'label', $row['id']), "name"=>$row['name'], "description"=>$this->_trd->selectTXT(TBL_MODULE_APP, 'description', $row['id']));
        }
      }
    }

    return $list;
  }
  
  /**
   * Elenco dei moduli non di sistema visualizzabili nell'area amministrativa
   * 
   * @return array
   */
  public function modulesManageArray() {

    if(!$this->_access->getAccessAdmin()) {
      return array();
    }

    $list = array();
    $rows = $this->_db->select('id, label, name, class, description', TBL_MODULE, " masquerade='no' AND type='class'", "label");
    if($rows and count($rows)) {
      foreach($rows as $row) {
        if($this->_registry->user->hasAdminPerm($row['class'], $row['id']) and method_exists($row['class'], 'manageDoc')) {
          $list[$row['id']] = array("label"=>$this->_trd->selectTXT(TBL_MODULE, 'label', $row['id']), "name"=>$row['name'], "class"=>$row['class'], "description"=>$this->_trd->selectTXT(TBL_MODULE, 'description', $row['id']));
        }
      }
    }

    return $list;
  }
}
?>
