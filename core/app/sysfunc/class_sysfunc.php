<?php
/**
 * @file class_sysfunc.php
 * @brief Contiene la definizione ed implemetazione della classe Gino.App.Sysfunc.sysfunc
 * 
 * @copyright 2005-2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

/**
 * @namespace Gino.App.Sysfunc
 * @description Namespace dell'applicazione Sysfunc, per la gestione della visualizzazione ddi pagine di errore
 */
namespace Gino\App\Sysfunc;

/**
 * @brief Classe di tipo Gino.Controller che gestisce metodi che stampano pagine di errore
 * 
 * @copyright 2005-2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class sysfunc extends \Gino\Controller {

    /**
     * @brief Costruttore
     * @return Gino.App.Sysfunc.sysfunc
     */
    function __construct(){

        parent::__construct();
    }

    /**
     * @brief Pagina di errore contenuto non disponibile 
     * 
     * @see Gino.Http.ResponseNotFound
     * @see Gino.Exception.Exception404
     * @param string $title titolo della pagina 
     * @param string $message messaggio mostrato
     * @return string
     */
    public static function page404($title = '', $message = '') {

        header("HTTP/1.0 404 Not Found");

        if(!$title) {
            $title = _("404 Pagina inesistente");
        }

        if(!$message) {
            $message = _("Il contenuto cercato non esiste, è stato rimosso oppure spostato.");
        }

        $view = new \Gino\View();

        $view->setViewTpl('404');
        $view->assign('title', $title);
        $view->assign('message', $message);

        return $view->render();
    }

    /**
     *  @brief Pagina di errore contenuto forbidden 
     *
     * @see Gino.Http.ResponseForbidden
     * @see Gino.Exception.Exception403
     * @param string $title titolo della pagina 
     * @param string $message messaggio mostrato
     * @access public
     * @return string
     */
    public static function page403($title = '', $message = '') {

        header("HTTP/1.1 403 Unauthorized");

        if(!$title) {
            $title = _("403 Autorizzazione negata");
        }

        if(!$message) {
            $message = _("Non sei autorizzato a visualizzare il contenuto richiesto.");
        }

        $view = new \Gino\View();

        $view->setViewTpl('403');
        $view->assign('title', $title);
        $view->assign('message', $message);

        return $view->render();
    }

    /**
     * Pagina di errore server error
     *
     * @see Gino.Http.ResponseServerError
     * @see Gino.Exception.Exception500
     * @param string $title titolo della pagina 
     * @param string $message messaggio mostrato
     * @access public
     * @return string
     */
    public static function page500($title = '', $message = '') {

        header("HTTP/1.0 500 Internal Server Error");

        if(!$title) {
            $title = _("500 Server error");
        }

        if(!$message) {
            $message = _("Si è verificato un errore interno al sistema.");
        }

        $view = new \Gino\View();

        $view->setViewTpl('500');
        $view->assign('title', $title);
        $view->assign('message', $message);

        return $view->render();
    }

}
