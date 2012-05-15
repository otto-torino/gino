<?php

class error {

	public static function codeMessages() {

		return	array(
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
			36=>_("il formato del codice non è valido")
		);
	}

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
