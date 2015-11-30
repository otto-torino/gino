<?php
/**
 * @file class.Core.php
 * @brief Contiene la definizione ed implementazione della classe Gino.Core
 *
 * @copyright 2005-2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

use \Gino\Http\ResponseNotFound;

/**
 * @brief Gestisce una Gino.Http.Request ed invia una Gino.Http.Response adeguata
 *
 * @copyright 2005-2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class Core {

    private $_registry, $_db;

    /**
     * @brief Costruttore
     * @description Include le classi fondamentali per il funzionamento del sistema,
     *              setta il locale corretto ed inizializza il registro
     */
    function __construct() {

        Loader::import('class', array(
            '\Gino\Logger',
            '\Gino\Singleton',
            '\Gino\Db',
            '\Gino\Locale',
            '\Gino\Translation',
            '\Gino\Error',
            '\Gino\Session',
            '\Gino\Router',
            '\Gino\EventDispatcher',
            '\Gino\GImage',
            '\Gino\GTag',
            '\Gino\Document'
        ));

        Loader::import('class/exceptions', array(
            '\Gino\Exception\Exception404',
            '\Gino\Exception\Exception403',
            '\Gino\Exception\Exception500',
        ));

        Loader::import('class/http', array(
            '\Gino\Http\Request',
            '\Gino\Http\Response',
            '\Gino\Http\Redirect',
            '\Gino\Http\ResponseNotFound',
            '\Gino\Http\ResponseForbidden',
            '\Gino\Http\ResponseServerError'
        ));

        Loader::import('class/mvc', array(
            '\Gino\Model',
            '\Gino\Controller',
            '\Gino\View'
        ));

        Loader::import('class/fields', array(
            '\Gino\Field',
            '\Gino\BooleanField',
            '\Gino\CharField',
            '\Gino\SlugField',
            '\Gino\DateField',
            '\Gino\DatetimeField',
            '\Gino\DirectoryField',
            '\Gino\EmailField',
            '\Gino\EnumField',
            '\Gino\FileField',
            '\Gino\FloatField',
            '\Gino\ForeignKeyField',
            '\Gino\ImageField',
            '\Gino\IntegerField',
            '\Gino\ManyToManyField',
            '\Gino\ManyToManyThroughField',
            '\Gino\MulticheckField',
            '\Gino\TextField',
            '\Gino\TimeField',
            '\Gino\YearField',
            '\Gino\TagField',
        ));
        
        Loader::import('class/build', array(
			'\Gino\Build',
			'\Gino\BooleanBuild',
			'\Gino\CharBuild',
			'\Gino\SlugBuild',
			'\Gino\DateBuild',
			'\Gino\DatetimeBuild',
			'\Gino\DirectoryBuild',
			'\Gino\EmailBuild',
			'\Gino\EnumBuild',
			'\Gino\FileBuild',
			'\Gino\FloatBuild',
			'\Gino\ForeignKeyBuild',
			'\Gino\ImageBuild',
			'\Gino\IntegerBuild',
			'\Gino\ManyToManyBuild',
			'\Gino\ManyToManyThroughBuild',
			'\Gino\MulticheckBuild',
			'\Gino\TextBuild',
			'\Gino\TimeBuild',
			'\Gino\YearBuild',
			'\Gino\TagBuild',
        ));
        
        Loader::import('class/widget', array(
        	'\Gino\Widget',
        	'\Gino\HiddenWidget',
        	'\Gino\ConstantWidget',
        	'\Gino\SelectWidget',
        	'\Gino\RadioWidget',
        	'\Gino\CheckboxWidget',
        	'\Gino\MulticheckWidget',
        	'\Gino\EditorWidget',
        	'\Gino\TextareaWidget',
        	'\Gino\FloatWidget',
        	'\Gino\DateWidget',
        	'\Gino\DatetimeWidget',
        	'\Gino\TimeWidget',
        	'\Gino\PasswordWidget',
        	'\Gino\FileWidget',
        	'\Gino\ImageWidget',
        	'\Gino\EmailWidget',
        	'\Gino\TextWidget',
        	'\Gino\UnitWidget',
        ));
        
        Loader::import('class/input', array(
        	'\Gino\Input',
        ));

        // gettext
        Locale::initGettext();
        // registro di sistema
        $this->initRegistry();
        // locale, setta l'oggetto trd per le traduzioni nel registro
        Locale::init();
        // mobile
        if(!!$this->_registry->sysconf->mobile) {
            $this->initMobile();
        }
    }

    /**
     * @brief Inizializza il registro di sistema
     * @return void
     */
    private function initRegistry() {

        Loader::import('sysconf', 'Conf');
        $this->_registry = Loader::singleton('\Gino\Registry');

        // core
        $this->_registry->access = Loader::load('Access');
        $this->_registry->db = Loader::singleton('\Gino\Db');
        $this->_registry->sysconf = new \Gino\App\Sysconf\Conf(1);

        // layout
        $this->_registry->css = array();
        $this->_registry->js = array();
        $this->_registry->meta = array();
        $this->_registry->head_links = array();
    }

    /**
     * @brief Esegue operazioni relative ai dispositivi mobile
     * @description Controlla ed imposta la variabile di sessione che gestisce risposte adatte per il mobile
     * @return void
     */
    private function initMobile() {

        $session = \Gino\Session::instance();

        /* mobile detection */
        $avoid_mobile = preg_match("#(&|\?)avoid_mobile=(\d)#", $_SERVER['REQUEST_URI'], $matches)
            ? (int) $matches[2]
            : null;

        if($avoid_mobile) {
            unset($session->L_mobile);
            $session->L_avoid_mobile = 1;
        }
        elseif($avoid_mobile === 0) {
            unset($session->L_avoid_mobile);
        }

        if(!(isset($session->L_avoid_mobile) && $session->L_avoid_mobile)) {
            $this->detectMobile($session);
        }
    }

    /**
     * @brief Esegue il detect di dispositivi mobile, setta una variabile di sessione se il detect è positivo
     * @param \Gino\Session $session istanza di Gino.Session
     * @return void
     */
    private function detectMobile(\Gino\Session $session) {

        $detect = Loader::load('MobileDetect');

        if($detect->isMobile()) {
            $session->L_mobile = 1;
        }
    }

    /**
     * @brief Invia la risposta HTTP al client e chiude la connessione al DB
     * @description Se la risposta ricevuta dal @ref Gino.Router non è una Gino.Http.Response
     *              invia una Gino.Http.ResponseNotFound (404)
     * @return void
     */
    public function answer() {

        // set the request object
        $this->_registry->request = \Gino\Http\Request::instance();
        // set the router object
        $this->_registry->router = Router::instance();

        // check authentication
        if(!($response = $this->_registry->access->authentication($this->_registry->request))) {
            $response = $this->_registry->router->route();
        }

        // risposta valida
        if(is_a($response, "\Gino\Http\Response")) {
            $response();
        }
        else {
            $response = new ResponseNotFound();
            $response();
        }

        $this->_registry->db->closeConnection();
    }
}
