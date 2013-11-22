<?php
/**
 * @file core.php
 * @brief File che genera il documento
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

/**
 * @brief Renderizza la pagina richiesta
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class core {

  private $_registry, $_base_path;

  /**
   * Inizializza le variabili di registro
   */
  function __construct() {

    // registro di sistema
    $this->initRegistry();
    // locale
    locale::init();
    // mobile
    $this->initMobile();
    // headers
    $this->setHeaders();
    // check authentication
    $this->_registry->auth->authentication();

  }

  /**
   * Inizializza il registro di sitema
   */
  private function initRegistry() {

    $this->_registry = registry::instance();

    // core
    $this->_registry->pub = new pub();
    $this->_registry->session = session::instance();
    $this->_registry->auth = new Auth();
    $this->_registry->db = db::instance();
    $this->_registry->plink = new Link();

    // layout
    $this->_registry->css = array();
    $this->_registry->js = array();
    $this->_registry->meta = array();
    $this->_registry->head_links = array();

  }

  /**
   * Esegue operazioni relative ai dispositivi mobile
   */
  private function initMobile() {

    /* mobile detection */
    $avoid_mobile = preg_match("#(&|\?)avoid_mobile=(\d)#", $_SERVER['REQUEST_URI'], $matches)
      ? (int) $matches[2]
      : null;

    if($avoid_mobile) {
      unset($this->_registry->session->L_mobile);
      $this->_registry->session->L_avoid_mobile = 1;
    }
    elseif($avoid_mobile === 0) {
      unset($this->_registry->session->L_avoid_mobile);
    }

    if(!(isset($this->_registry->session->L_avoid_mobile) && $this->_registry->session->L_avoid_mobile)) {
      $this->detectMobile();
    }

  }

  /**
   * Esegue il detect di dispositivi mobile, setta una variabile di sessione se il detect è positivo
   * @return void
   */
  private function detectMobile() {

    $detect = new Mobile_Detect();

    if($detect->isMobile()) {
      $this->_registry->session->L_mobile = 1;
    }
  }


  /**
   * Setta gli header php
   */
  private function setHeaders() {
    if(isset($_REQUEST['logout'])) {
      header('cache-control: no-cache,no-store,must-revalidate'); // HTTP 1.1.
      header('pragma: no-cache'); // HTTP 1.0.
      header('expires: 0');
    }
  }

  /**
   * Effettua il render della pagina e invia l'output buffering
   */
  public function renderApp() {

    ob_start();
    $doc = new document();
    $buffer = $doc->render();
    ob_end_flush();
  }
}
