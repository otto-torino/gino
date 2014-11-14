<?php
/**
 * @file core.php
 * @brief File che genera il documento
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

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
      '\Gino\Singleton', 
      '\Gino\Db', 
      '\Gino\Locale', 
      '\Gino\Translation', 
      '\Gino\Error', 
      '\Gino\Session',
      '\Gino\EventDispatcher', 
      '\Gino\GImage',
      '\Gino\GTag'
    ));

    loader::import('class/mvc', array(
      '\Gino\Model', 
      '\Gino\Controller', 
      '\Gino\View'
    ));

    loader::import('class/fields', array(
      '\Gino\Field',
      '\Gino\BooleanField', 
      '\Gino\CharField', 
      '\Gino\ConstantField', 
      '\Gino\DateField', 
      '\Gino\DatetimeField', 
      '\Gino\DirectoryField', 
      '\Gino\EmailField', 
      '\Gino\EnumField', 
      '\Gino\FileField', 
      '\Gino\FloatField', 
      '\Gino\ForeignKeyField', 
      '\Gino\HiddenField',
      '\Gino\ImageField',
      '\Gino\IntegerField',
      '\Gino\ManyToManyField', 
      '\Gino\ManyToManyThroughField', 
      '\Gino\ManyToManyInlineField', 
      '\Gino\TextField', 
      '\Gino\TimeField', 
      '\Gino\YearField',
      '\Gino\TagField'
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

    Loader::import('sysconf', '\Gino\App\Sysconf\Conf');
    $this->_registry = Loader::singleton('\Gino\Registry');

    // core
    $this->_registry->session = Loader::singleton('\Gino\Session');
    $this->_registry->pub = Loader::load('Pub');
    $this->_registry->access = Loader::load('Access');
    $this->_registry->db = Loader::singleton('\Gino\Db');
    $this->_registry->plink = Loader::load('Link');
    $this->_registry->sysconf = new \Gino\App\Sysconf\Conf(1);

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

    $detect = Loader::load('MobileDetect');

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
    $doc = Loader::load('Document');
    $buffer = $doc->render();
    ob_end_flush();
  }
}
