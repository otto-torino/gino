<?php
/**
 * @file class.error.php
 * @brief Contiene la classe error
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

/**
 * @brief Gestisce gli errori e contiene i codice di errore
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class error {

	/**
	 * Elenco dei codici di errore
	 * 
	 * @return array
	 */
	public static function codeMessages() {

		return array(
			1=>_("non sono stati compilati tutti i campi obbligatori"),
			2=>_("non è stato compilato almeno un campo"),
			3=>_("il file non può essere inserito perché non conforme alle specifiche"),
			4=>_("il nome del file è già presente. cambiare nome al file"),
			5=>_("non sono stati compilati tutti i campi"),
			6=>_("le due password non corrispondono"),
			7=>_("l'email non è valida"),
			8=>_("il nome utente scelto è già utilizzato"),
			9=>_("errore tecnico. contattare l'amministratore di sistema"),
			10=>_("l'accesso alla pagina è concesso previa autenticazione"),
			11=>_("la pagina richiesta non è disponibile"),
			12=>_("il codice lingua è già presente"),
			13=>_("scegliere almeno un campo di ricerca"),
			14=>_("i file hanno lo stesso nome. cambiare nome a uno di essi"),
			15=>_("il testo eccede il numero massimo di caratteri"),
			16=>_("l'upload è fallito"),
			17=>_("non è stato possibile eliminare il file"),
			18=>_("le operazioni di ridimensionamento del file sono fallite"),
			19=>_("la password inserita non rispetta il numero di caratteri richiesto"),
			20=>_("l'email inserita è già presente nel sistema"),
			21=>_("il carrello risulta vuoto"),
			22=>_("sessione non valida o utente non registrato"),
			23=>_("il numero di ordine non è valido"),
			24=>_("il codice di controllo inserito non è valido"),
			25=>_("le due email non coincidono"),
			26=>_("è necessario accettare i termini e le condizioni d'uso"),
			27=>_("errore nella creazione della directory"),
			28=>_("errore nel trasferimento del file"),
			29=>_("il formato dell'orario non è valido"),
			30=>_("il formato numerico non è valido"),
			31=>_("permessi non validi per l'esecuzione dell'operazione"),
			32=>_("la directory non è stata creata. controllare i permessi"),
			33=>_("la dimensione del file supera il limite consentito dal sistema"),
			34=>_("errore nell'esecuzione della query"),
			35=>_("il codice inserito è già presente, scegliere un altro codice"),
			36=>_("il formato del codice non è valido"),
			37=>_("impossibile eliminare il record")
		);
	}

	/**
	 * Gestione dell'errore con reindirizzamento alla pagina costruita nel metodo
	 * 
	 * Da utilizzare essenzialmente per gli errori di sistema
	 * 
	 * @param string $class nome della classe che genera l'errore
	 * @param string $function nome del metodo che genera l'errore
	 * @param string $message testo dell'errore
	 * @param integer $line numero di linea dell'errore (la costante magica __LINE__ riporta il numero di linea corrente del file)
	 * @return print page
	 * 
	 * Esempio
	 * @code
	 * exit(error::syserrorMessage("document", "renderModule", "Tipo di modulo sconosciuto", __LINE__));
	 * @endcode
	 */
	public static function syserrorMessage($class, $function, $message, $line=null) {

		$buffer = "<html>\n";
		$buffer .= "<head>\n";
		$buffer .= "<style type=\"text/css\">";
		$buffer .= "@import url('".CSS_WWW."/syserror.css')";
		$buffer .= "</style>\n";
		$buffer .= "</head>\n\n";
		$buffer .= "<body>\n";
		$buffer .= "<div>\n";
		$buffer .= "<div id=\"errorImg\">\n";
		$buffer .= "</div>\n";
		$buffer .= "<table border=\"1\">\n";
		$buffer .= "<tr>";
		$buffer .= "<th>"._("Classe")."</th><th>"._("Funzione")."</th><th>"._("Messaggio")."</th>";
		if($line) $buffer .= "<th>"._("Linea")."</th>";
		$buffer .= "</tr>\n";
		$buffer .= "<tr>";
		$buffer .= "<td>".$class."</td><td>".$function."</td><td>".$message."</td>";
		if($line) $buffer .= "<td>".$line."</td>";
		$buffer .= "</tr>\n";
		$buffer .= "</table>\n";
		$buffer .= "</div>\n";
		$buffer .= "</body>\n";
		$buffer .= "</html>";

		echo $buffer;
	}
	
	/**
	 * Gestione dell'errore con reindirizzamento a un indirizzo indicato
	 * 
	 * @param mixed $message
	 *   - @a string: testo personalizzato dell'errore
	 *   - @a array: codice di errore
	 * @param string $link collegamento al quale reindirizzare a seguito dell'errore
	 * @return redirect
	 * 
	 * Esempio
	 * @code
	 * exit(error::errorMessage(array('error'=>1), $this->_home."?evt[$this->_instanceName-manageDoc]&id=$id"));
	 * @endcode
	 */
	public static function errorMessage($message, $link) {

		$codeMessages = self::codeMessages();
		
		$msg = (is_int($message['error']))? $codeMessages[$message['error']]:$message['error'];

		$buffer = _("Errore: ");
		$buffer .= " ".jsVar($msg)."\\n";
		if(isset($message['hint'])) {
			$buffer .= _("Suggerimenti:");
			$buffer .= " ".jsVar($message['hint']);
		}
		$session = session::instance();
		$session->GINOERRORMSG = $buffer;

		header("Location: $link");
	}

	/**
	 * Genera un errore 404 
	 * 
	 * @static
	 * @access public
	 * @return reindirizza alla pagina 404
	 */
	public static function raise404() {

		$site = (substr(SITE_WWW, -1) != '/' && SITE_WWW != '') ? SITE_WWW.'/' : SITE_WWW;
		
		if(substr($site, 0, 1) != '/')
			$site = '/'.$site;
		
		$plink = new link();
		header("Location: "."http://".$_SERVER['HTTP_HOST'].$site.$plink->alink('sysfunc', 'page404'));
		exit();
	}

	/**
	 * Recupera il messaggio di errore
	 * 
	 * @return string
	 */
	public static function getErrorMessage() {

		$session = session::instance();
		
		if(isset($session->GINOERRORMSG))
		{
			$errorMsg = $session->GINOERRORMSG;
			unset($session->GINOERRORMSG);
		}
		else $errorMsg = '';
		
		return $errorMsg;
	}
}
?>
