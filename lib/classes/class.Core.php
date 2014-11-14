<?php
/**
 * @file class.Core.php
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

    loader::import('class', array(
      'Logger', 
      'Singleton', 
      'Db', 
      'Locale', 
      'Translation', 
      'Error', 
      'Session',
      'EventDispatcher', 
      'GImage',
      'GTag'
    ));

    loader::import('class/mvc', array(
      'Model', 
      'Controller', 
      'View'
    ));

    loader::import('class/fields', array(
      'Field',
      'BooleanField', 
      'CharField', 
      'ConstantField', 
      'DateField', 
      'DatetimeField', 
      'DirectoryField', 
      'EmailField', 
      'EnumField', 
      'FileField', 
      'FloatField', 
      'ForeignKeyField', 
      'HiddenField',
      'ImageField',
      'IntegerField',
      'ManyToManyField', 
      'ManyToManyThroughField', 
      'ManyToManyInlineField', 
      'TextField', 
      'TimeField', 
      'YearField',
      'TagField'
    ));

    // gettext
    locale::initGettext();
    // registro di sistema
    $this->initRegistry();
    // locale
    locale::init();
    // mobile
    $this->initMobile();
    // headers
    $this->setHeaders();
    // check authentication
    $this->_registry->access->authentication();

  }

  /**
   * Inizializza il registro di sitema
   */
  private function initRegistry() {

    Loader::import('sysconf', 'Conf');
    $this->_registry = loader::singleton('Registry');

    // core
    $this->_registry->session = loader::singleton('Session');
    $this->_registry->pub = loader::load('Pub');
    $this->_registry->access = loader::load('Access');
    $this->_registry->db = loader::singleton('Db');
    $this->_registry->plink = loader::load('Link');
    $this->_registry->sysconf = new Conf(1);

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

    $detect = loader::load('MobileDetect');

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
    $doc = loader::load('Document');
    $buffer = $doc->render();
    ob_end_flush();
  }
}
