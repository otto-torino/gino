<?php
/**
 * @file class_sysfunc.php
 * @brief Contiene la classe sysfunc
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

/**
 * @brief Metodi personalizzati e interfacce a metodi utilizzati da classi molteplici per espandarne le funzionalità
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class sysfunc extends Controller {

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
			"Autenticazione" => array("label"=>_("Boxino di login"), "permissions"=>''),
			"tableLogin" => array("label"=>_("Boxino di login a tabella"), "permissions"=>''),
			"credits" => array("label"=>_("Credits"), "permissions"=>'')
		);

		return $list;
	}

	/**
	 * Box di login
	 * 
	 * @see access::AccessForm()
	 * @see account::linkRegistration()
	 * @param boolean $bool mostra il collegamento alla registrazione autonoma di un utente
	 * @param string $classname nome della classe che fornisce i metodi per le interfacce
	 * @return string
	 */
	public function Autenticazione($bool=false, $classname='user'){

		$GINO = "<div class=\"auth\">\n";
		$GINO .= "<div class=\"auth_title\">"._("login:")."</div>";
		$GINO .= "<div class=\"auth_content\">"; 
		$GINO .= $this->_access->AccessForm();
		
		$registration = new account($classname);
		$GINO .= $registration->linkRegistration($bool);
		
		$GINO .= "</div>\n";
		$GINO .= "</div>\n";
		
		return $GINO;
	}
	
	/**
	 * Box di login in tabella
	 * 
	 * @param boolean $bool mostra il collegamento alla registrazione autonoma di un utente
	 * @param string $classname nome della classe che fornisce i metodi per le interfacce
	 * @return string
	 */
	public function tableLogin($bool=false, $classname='index'){

		$GINO = "<form action=\"\" method=\"post\" id=\"formauth\" name=\"formauth\">\n";
		$GINO .= "<input type=\"hidden\" id=\"action\" name=\"action\" value=\"auth\" />\n";
		$GINO .= "<table>";
		$GINO .= "<tr class=\"authTitle\">";
		$GINO .= "<td></td><td>"._("Area riservata")."</td>";
		$GINO .= "</tr>";
		$GINO .= "<tr class=\"authForm\">";
		$GINO .= "<td class=\"afLabel\">".($this->_u_username_email?"email":"user")."</td>";
		$GINO .= "<td class=\"afField\"><input type=\"text\" id=\"user\" name=\"user\" size=\"25\" maxlength=\"50\" class=\"auth\" /></td>";
		$GINO .= "</tr>";
		$GINO .= "<tr class=\"authForm\">";
		$GINO .= "<td class=\"afLabel\">"._("password")."</td>";
		$GINO .= "<td class=\"afField\"><input type=\"password\" name=\"pwd\" size=\"25\" maxlength=\"15\" class=\"auth\" /></td>";
		$GINO .= "</tr>";
		$GINO .= "<tr class=\"authForm\">";
		$GINO .= "<td class=\"afLabel\"></td>";
		$GINO .= "<td class=\"afField\"><input type=\"submit\" class=\"generic\" name=\"login_user\" value=\""._("login")."\" /></td>";
		$GINO .= "</tr>";
		if($this->_u_aut_registration OR $bool) {
			$class = $classname=='index' ? 'user':$classname;
			$GINO .= "<tr class=\"authRegTitle\">";
			$GINO .= "<td></td><td>"._("Registrazione")."</td>";
			$GINO .= "</tr>";
			$GINO .= "<tr class=\"authRegForm\">";
			$GINO .= "<td class=\"arfLabel\"></td>";
			$GINO .= "<td class=\"arfField\"><input onclick=\"location.href='".$this->_home."?evt[$class-registration]'\" type=\"button\" class=\"generic\" name=\"login_user\" value=\""._("sign up")."\" /></td>";
			$GINO .= "</tr>";
		}
		$GINO .= "</table>";
		$GINO .= "</form>";
		
		return $GINO;
	}

	/**
	 * Credits
	 * 
	 * @return string
	 */
	public function credits() {
	
		$credits = '<h1 class="hidden">' . _('Credits') . '</h1>';
		$credits .= "<a class=\"otto\" href=\"http://www.otto.to.it\" target=\"_blank\">&#160;</a>";
		$credits .= "<div class=\"null\"></div>";

		return $credits;
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

		if(!$title) {
			$title = _("404 Pagina inesistente");
		}

		if(!$message) {
			$message = _("Il contenuto cercato non esiste, è stato rimosso oppure spostato.");
		}

		$view = new view();

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

		if(!$title) {
			$title = _("403 Autorizzazione negata");
		}

		if(!$message) {
			$message = _("Non sei autorizzato a visualizzare il contenuto richiesto.");
		}

		$view = new view();

		$view->setViewTpl('403');
		$view->assign('title', $title);
		$view->assign('message', $message);

		return $view->render();
	}

	/**
	 * Interfaccia per la gestione dei file css dei moduli
	 * 
	 * @see css::manageModuleCss()
	 * @param integer $mdl valore ID del modulo
	 * @param string $class nome della classe
	 * @return string
	 */
	public static function manageCss($mdl, $class) {

		$db = db::instance();

		if($mdl) {
			$query = "SELECT name, label FROM ".TBL_MODULE." WHERE id='$mdl'";
			$a = $db->selectquery($query);
			if(sizeof($a)>0) {
				$name = $a[0]['name']; 
				$label = $a[0]['label']; 
			}
		}
		else {
			$name = $class;
			$label = $db->getFieldFromId(TBL_MODULE_APP, 'label', 'name', $class);
		}

		$css = new css('module', array("class"=>$class, "module"=>$mdl, "name"=>$name, "label"=>$label));

		$GINO = $css->manageModuleCss();

		return $GINO;
	}
	
	/**
	 * Interfaccia per la gestione dei file di front-end dei moduli
	 * 
	 * @see frontend::manageFrontend()
	 * @param integer $mdl valore ID del modulo
	 * @param string $class nome della classe
	 * @return string
	 */
	public function manageFrontend($mdl, $class) {

		$db = db::instance();

		if($mdl) {
			$query = "SELECT name, label FROM ".TBL_MODULE." WHERE id='$mdl'";
			$a = $db->selectquery($query);
			if(sizeof($a)>0) {
				$name = $a[0]['name']; 
				$label = $a[0]['label']; 
			}
		}
		else {
			$name = $class;
			$label = $db->getFieldFromId(TBL_MODULE_APP, 'label', 'name', $class);
		}

		$frontend = new frontend(array("class"=>$class, "module"=>$mdl, "name"=>$name, "label"=>$label));

		$GINO = $frontend->manageFrontend();

		return $GINO;
	}

	/**
	 * Interfaccia per la gestione delle opzioni dei moduli
	 * 
	 * @see options::manageDoc()
	 * @param integer $mdl valore ID del modulo
	 * @param string $class nome della classe
	 * @return string
	 */
	public function manageOptions($mdl, $class) {
	
		$options = new options($class, $mdl);

		return $options->manageDoc();
	}
	
	/**
	 * Interfaccia per la gestione delle email personalizzate dei moduli
	 * 
	 * @see email::manageDoc()
	 * @param integer $mdl valore ID del modulo
	 * @param string $class nome della classe
	 * @return string
	 */
	public static function manageEmail($mdl, $class) {
	
		$email = new email($class, $mdl);

		return $email->manageDoc();
	}
	
	/**
	 * Interfaccia per la gestione dei permessi di accesso alle funzionalità dei moduli
	 * 
	 * @see admin::manageDoc()
	 * @param integer $mdl valore ID del modulo
	 * @param string $class nome della classe
	 * @return string
	 */
	public static function managePermissions($mdl, $class) {
	
		$class_auth = new admin($class, $mdl);

		return $class_auth->manageDoc();
	}
}
?>
