<?php
/**
 * @file class_sysfunc.php
 * @brief Contiene la classe sysfunc
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino\App\Sysfunc;

/**
 * @brief Metodi personalizzati e interfacce a metodi utilizzati da classi molteplici per espandarne le funzionalità
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class sysfunc extends \Gino\Controller {

	function __construct(){

		parent::__construct();

	}
	
	/**
	 * Pagina di errore contenuto non disponibile 
	 * 
	 * @param string $title titolo della pagina 
	 * @param string $message messaggio mostrato
	 * @access public
	 * @return pagina di errore
	 */
	public function page404($title = '', $message = '') {

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
   *  Pagina di errore contenuto forbidden 
	 * 
	 * @param string $title titolo della pagina 
	 * @param string $message messaggio mostrato
	 * @access public
	 * @return pagina di errore
	 */
	public function page403($title = '', $message = '') {

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
	 * @param string $title titolo della pagina 
	 * @param string $message messaggio mostrato
	 * @access public
	 * @return pagina di errore
	 */
	public function page500($title = '', $message = '') {

        header("HTTP/1.0 500 Internal Server Error");

		if(!$title) {
			$title = _("500 Server error");
		}

		if(!$message) {
			$message = _("Si è verificato un errore interno al sistema.");
		}

		$view = new view();

		$view->setViewTpl('500');
		$view->assign('title', $title);
		$view->assign('message', $message);

		return $view->render();
    }

}
