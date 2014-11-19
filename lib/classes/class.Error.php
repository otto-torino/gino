<?php
/**
 * @file class.error.php
 * @brief Contiene la classe Error
 * 
 * @copyright 2005-2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

use \Gino\Http\Redirect;

/**
 * @brief Classe per la gestione centralizzata degli errori
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class Error {

    /**
     * @brief Elenco dei codici di errore
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
            37=>_("impossibile eliminare il record"),
            38=>_("impossibile eliminare i dati associati")
        );
    }

    /**
     * Gestione dell'errore con reindirizzamento a un indirizzo indicato
     * 
     * @param array $message
     *     array associativo di opzioni
     *     - @b error (mixed)
     *         - @a string: testo personalizzato dell'errore
     *         - @a integer: codice di errore
     *     - @b hint (string): testo dei suggerimenti
     * @param string $link collegamento al quale reindirizzare a seguito dell'errore
     * @return redirect
     * 
     * Esempio
     * @code
     * exit(Error::errorMessage(array('error'=>1), $this->_home."?evt[$this->_instanceName-manageDoc]&id=$id"));
     * @endcode
     */
    public static function errorMessage($message, $link) {

        $codeMessages = self::codeMessages();
        $msg = (is_int($message['error']))? $codeMessages[$message['error']]:$message['error'];

        $buffer = $msg;
        if(isset($message['hint'])) {
            $buffer .= "<p><b>"._("Suggerimenti:")."</b></p>";
            $buffer .= $message['hint'];
        }
        $session = session::instance();
        $session->GINOERRORMSG = $buffer;

        return new Redirect($link);
    }

    /**
     * @brief Stampa warning se si è in DEBUG TRUE
     *
     * @param string $message messaggio
     * @return void
     * 
     * Esempio
     * @code
     * error::warning('modello inesistente');
     * @endcode
     */
    public static function warning($message) {

        if(DEBUG) {
            $buffer = "<div class=\"error-warning\" style=\"padding: 10px;background: orange; border: 1px solid #000;\">";
            $buffer .= "<h1>"._('Warning')."</h1>";
            $buffer .= $message;
            $buffer .= "</div>";

            echo $buffer;
        }
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
