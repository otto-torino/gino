<?php
/**
 * @mainpage Libreria per l'invio delle email
 * 
 * Plugin per l'invio delle email con la libreria PHPMailer (A full-featured email creation and transfer class for PHP)
 *   @link https://github.com/PHPMailer/PHPMailer
 *   @link https://packagist.org/packages/phpmailer/phpmailer
 * 
 * ##INSTALLATION
 * ---------------
 * Official installation method is via composer and its packagist package phpmailer/phpmailer (@link https://packagist.org/packages/phpmailer/phpmailer).
 * @code
 * $ composer require phpmailer/phpmailer
 * @endcode
 * 
 * ##FILE AGGIUNTIVI
 * ---------------
 * lib/plugin/template-html-email.php
 * 
 * ##UTILIZZO
 * ---------------
 * 1. Includere nel metodo che invia le email il file plugin.phpmailer.php
 * @code
 * require_once PLUGIN_DIR.OS.'plugin.phpmailer.php';
 * @endcode
 * 
 * 2. Utilizzare il metodo sendMail() per inviare le email; questo metodo si interfaccia alla libreria PHPMailer. \n
 * La scelta dell'utilizzo della libreria PHPMailer o della funzione base di php (mail) per l'invio di una email avviene tramite l'opzione @a use_mail: 
 *   - @a true, funzione mail (default)
 *   - @a false, libreria PHPMailer
 * 
 * Optando per la libreria PHPMailer è inoltre possibile scegliere la tipologia di mailer; di default il valore è @a mail.
 * 
 * ###ESEMPIO
 * Esempio di metodo per l'invio di una email con la tipologia di mailer @a smtp
 * @code
 * public function sendTestEmail(){
 *   require_once PLUGIN_DIR.OS.'plugin.phpmailer.php';
 *   $mailer = new \Gino\Plugin\plugin_phpmailer();
 *   $send = $mailer->sendMail("support@test.com", "info@test.com", "Prova invio da plugin", "Testo dell'email", 
 *   [
 *	   'use_mail'=>false, 
 *	   'mailer'=>'smtp', 
 *	   'debug'=>2, 
 *	   'reply_email'=>'reply@test.com', 
 *	   'reply_name'=>'Test Email', 
 *	   'ishtml'=>false, 
 *	   'smtp_server'=>'smtp.example.com', 
 *	   'smtp_auth'=>true, 
 *	   'smtp_user'=>"smtp@test.com", 
 *	   'smtp_password'=>"password"
 *   ]);
 *   
 *   return $send;
 * }
 * @endcode
 * 
 * ##CONTENUTO DELL'EMAIL
 * ---------------
 * Una email può essere in formato html o testo. Per definire il tipo di formato impostare l'opzione @a ishtml nel metodo sendMail()
 * @code
 * array(
 *   ...
 *   'ishtml'=>[true|false], 
 * )
 * @endcode
 * 
 * ###ALLEGATI
 * Per allegare dei file all'email utilizzare l'opzione @a attachments nel metodo sendMail(). 
 * Ogni file viene definito come un array con le chiavi @a path e @a name
 * @code
 * array(
 *   ...
 *   'attachments'=>array(
 *	 array(
 *	   'path'=>CONTENT_DIR.OS.'attachment'.OS.'prova1.pdf', 
 *	   'name'=>'pippo.pdf'
 *	 ), 
 *	 array(
 *	   'path'=>CONTENT_DIR.OS.'attachment'.OS.'prova2.pdf', 
 *	   'name'=>'pluto.pdf'
 *	 )
 *   ), 
 * )
 * @endcode
 * 
 * ###FORMATO HTML
 * Utilizzando il formato html è possibile formattare il corpo dell'email con i tag html. 
 * Il contenuto dell'email può essere definito nel codice all'interno di una variabile oppure richiamando una specifica vista. 
 * In questo ultimo caso ci supporta il file @a template-html-email.php, un template di esempio per la costruzione di una email html.
 * 
 * Esempio di contenuto dell'email passato in blocco come variabile:
 * @code
 * $contents = "
 * <table align=\"center\" width=\"600\" border=\"0\" bgcolor=\"FFFFFF\">
 *   <tr>
 *	 <td><div style=\"text-align:left; padding:8px 4px; background-color:#F2F2F2; font-size:10px;\">Testo dell'email</div></td>
 *   </tr>
 * </table>";
 * @endcode
 * 
 * Nel caso della vista il contenuto viene definito nella classe controller attraverso la classe Gino.View; ad esempio potremmo avere:
 * @code
 * $view = new \Gino\View($this->_view_dir);
 * $view->setViewTpl('template-html-email.php');
 * 
 * $root_absolute_url = $this->_registry->request->root_absolute_url;
 * $dict = array(
 *   'image' => $root_absolute_url.'app/blog/img/image.gif',
 *   'title' => _("Mailing"), 
 *   'subtitle' => null, 
 *   'items' => $items,
 * );
 * $contents = $view->render($dict);
 * @endcode
 * 
 * ###CREARE UN MESSAGGIO DA UNA STRINGA HTML
 * Per creare un messaggio in modo automatico a partire da una stringa html occorre attivare l'opzione @a automatic_html nel metodo sendMail()
 * @code
 * array(
 *   ...
 *   'automatic_html'=>true, 
 * )
 * @endcode
 * 
 * Con questa opzione si richiama PHPMailer::MsgHTML() che effettua in modo automatico le modifiche per le immagini inline e i background, 
 * e crea una versione di solo testo convertendo il codice html. 
 * PHPMailer::MsgHTML() sovrascrive qualsiasi valore esistente in PHPMailer::$Body e PHPMailer::$AltBody. \n
 * Il contenuto dell'email è quello passato nel parametro @a $contents di sendMail(), come ad esempio
 * @code
 * $contents = file_get_contents('contents.html');
 * @endcode
 * 
 * ###IMMAGINI INLINE
 * Se si vuole costruire una email con delle immagini incorporate nel testo è necessario allegare le immagini e collegarle con il tag 
 * @code
 * <img src="cid:CID" />
 * @endcode
 * dove CID è il content ID dell'allegato, ovvero il riferimento dell'immagine. \n
 * Le immagini devono essere allegate con l'opzione @a embedded_images nel metodo sendMail(). Ogni file viene definito come un array con le chiavi @a path, @a cid e @a name
 * @code
 * array(
 *   ...
 *   'embedded_images'=>array(
 *	 array(
 *	   'path'=>SITE_ROOT.OS.'app/blog/img/image.gif', 
 *	   'cid'=>'img1'
 *	 )
 *   ), 
 * )
 * @endcode
 * 
 * ###NOTE SULLA COSTRUZIONE DELLE EMAIL IN FORMATO HTML
 * 1. Usare CSS inline per il testo e i link (ovvero senza servirsi di file css esterni o caricati sul server): inserire gli stili CSS direttamente nel corpo dell'email. 
 * Outlook non riconosce gli sfondi nelle tabelle perciò usate solo tinte unite tramite l’attributo bgcolor dei CSS. \n
 * 2. Usare solo Tabelle e non elementi DIV. Purtroppo Microsoft scansa accuratamente i DIV e le E-mail vanno costruite con le Tabelle. \n
 * 3. Usare inline anche gli attributi di stile delle tabelle, ovvero direttamente nel TAG TABLE (p.e. <TR style=\"...\">). \n
 * 4. Usare le immagini jpeg (scordarsi la trasparenza dei png)
 * 
 * ##ECCEZIONI
 * ---------------
 * Per attivare le eccezioni impostare l'opzione @a exception nel metodo sendMail()
 * @code
 * array(
 *   ...
 *   'exception'=>true, 
 * )
 * @endcode
 * 
 * Esempio di eccezioni
 * @code
 * $mail = new PHPMailer(true);
 * $mail->IsSMTP();
 * try {
 *   $mail->Host	   = "mail.yourdomain.com";
 *   $mail->SMTPDebug  = 2;
 *   ...
 *   $mail->Send();
 *   echo "Message Sent OK<p></p>\n";
 * } catch (phpmailerException $e) {
 *   echo $e->errorMessage(); //Pretty error messages from PHPMailer
 * } catch (Exception $e) {
 *   echo $e->getMessage(); //Boring error messages from anything else!
 * }
 * @endcode
 */

/**
 * @file plugin.phpmailer.php
 * @brief Contiene la classe plugin_phpmailer
 * 
 * @copyright 2013-2017 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

/**
 * @namespace Gino.Plugin
 * @description Namespace che comprende classi di tipo plugin
 */
namespace Gino\Plugin;

// Import PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

//Load composer's autoloader
require 'vendor/autoload.php';

/**
 * @brief Interfaccia alla classe PHPMailer
 * 
 * @copyright 2013-2017 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class plugin_phpmailer {
	
	/**
	 * @brief Percorso alla cartella contenente le viste specifiche del modulo
	 * @return string
	 */
	private $_view_folder;
	
	/**
	 * @brief Percorso alla cartella che contiene le viste di sistema
	 * @return string
	 */
	private $_dft_view_folder;
	
	/**
	 * Costruttore
	 * 
	 * @return void
	 */
	function __construct() {
		
		$this->_dft_view_folder = VIEWS_DIR;
	}
	
	/**
	 * @brief Parametri di specifici server SMTP
	 * 
	 * @param string $name nome del servizio smtp
	 *   - @a gmail
	 * @return array
	 */
	private function setSmtpParams($name) {
		
		$params = array();
		if($name == 'gmail')
		{
			$params['smtp_auth'] = true;
			$params['smtp_secure'] = 'tls';
			$params['smtp_server'] = "smtp.gmail.com";
			$params['smtp_port'] = 587;
		}
		return $params;
	}
	
	/**
	 * @brief Invio email con la funzione mail di PHP
	 * 
	 * @see mail()
	 * @param string $to indirizzo del destinatario; alcuni esempi: 
	 *   - user@test.com
	 *   - user@test.com, anotheruser@test.com
	 *   - User <user@test.com>
	 *   - User <user@test.com>, Another User <anotheruser@test.com>
	 * @param string $subject oggetto dell'email da inviare
	 * @param string $message messaggio da inviare; ogni linea deve essere separata con un LF (\\n) e le linee non devono essere più larghe di 70 caratteri
	 * @param array $options
	 *   array associativo di opzioni
	 *   - @b sender_email (string): 
	 *   - @b sender_name (string): 
	 *   - @b reply_email (string): 
	 *   - @b reply_name (string): 
	 *   - @b cc (string): aggiungere uno o più destinatari come Copia Conoscenza (separati da virgola e senza spazi); ad esempio:
	 *	 - dest <mail@dest.it>,dest2 <mail@dest2.it>
	 *	 - mail@dest.it,mail@dest2.it
	 *   - @b ccn (string): aggiungere uno o più destinatari come Copia Conoscenza Nascosta
	 *   - @b view_mailer (boolean): visualizza il mailer, ovvero la versione di php (default @a false)
	 *   - @b charset (string): set di caratteri (default @a UTF-8)
	 *   - @b crlf (string): default "\r\n"
	 *   - @b additional_headers: extra headers personalizzati; sovrascrive gli extra headers generati a partire dalle opzioni 
	 *	 sender_email, reply_email, view_mailer, cc, ccn
	 *   - @b additional_parameters: parametro che può essere utilizzato per passare flags addizionali come opzioni di linea di comando 
	 *	 al programma configurato per inviare le email, come definito nell'impostazione 'sendmail_path'
	 * @return boolean
	 * 
	 * Gli additional headers sono una stringa che viene inserita alla fine dell'header dell'email; 
	 * tipicamente viene utilizzata per aggiungere extra headers (From, Cc, and Bcc) che dovranno essere separati tra loro con un CRLF (\\r\\n).
	 */
	private function sendPhpMail($to, $subject, $message, $options=array()) {
		
		$sender_email = array_key_exists('sender_email', $options) ? $options['sender_email'] : null;
		$sender_name = array_key_exists('sender_name', $options) ? $options['sender_name'] : null;
		$reply_email = array_key_exists('reply_email', $options) ? $options['reply_email'] : null;
		$reply_name = array_key_exists('reply_name', $options) ? $options['reply_name'] : null;
		$cc = array_key_exists('cc', $options) ? $options['cc'] : null;
		$ccn = array_key_exists('ccn', $options) ? $options['ccn'] : null;
		$view_mailer = array_key_exists('view_mailer', $options) ? $options['view_mailer'] : false;
		$charset = array_key_exists('charset', $options) ? $options['charset'] : 'UTF-8';
		$crlf = array_key_exists('crlf', $options) && $options['crlf'] ? $options['crlf'] : "\r\n";
		$additional_headers = \Gino\gOpt('', $options, null);
		$additional_parameters = \Gino\gOpt('', $options, null);
		
		if($sender_email || $reply_email || $view_mailer || $cc || $ccn)
		{
			$headers = array();
			
			if($sender_email)
			{
				$text = "From:";
				if($sender_name) $text .= $sender_name." <";
				$text .= $sender_email;
				if($sender_name) $text .= ">";
				
				$headers[] = $text;
			}
			if($reply_email)
			{
				$text = "Reply-To:";
				if($reply_name) $text .= $reply_name." <";
				$text .= $reply_email;
				if($reply_name) $text .= ">";
				
				$headers[] = $text;
			}
			if($view_mailer)
			{
				$headers[] = "X-Mailer: PHP/" . phpversion();
			}
			if($cc)
			{
				$headers[] = "Cc:".$cc;
			}
			if($ccn)
			{
				$headers[] = "Bcc:".$ccn;
			}
			
			$extra_headers = implode($crlf, $headers);
		}
		
		if(!$additional_headers) {
			$additional_headers = $extra_headers;
		}
		
		if($charset == 'UTF-8')
		{
			$header_charset = 'MIME-Version: 1.0'."\r\n".'Content-type: text/plain; charset=UTF-8'.$crlf;
			$subject = "=?UTF-8?B?".base64_encode($subject).'?=';
			$additional_headers = $header_charset.$additional_headers;
		}
		
		$send = \mail($to, $subject, $message, $additional_headers, $additional_parameters);
		
		return $send;
	}
	
	/**
	 * @brief Invio email
	 * 
	 * @see sendPhpMail()
	 * @see setSmtpParams()
	 * @param string $recipient_email indirizzo email del destinatario
	 * @param string $sender_email indirizzo email del mittente
	 * @param string $subject oggetto dell'email
	 * @param string $contents contenuto dell'email
	 * @param array $options opzioni
	 *   array associativo di opzioni
	 *   - @b use_mail (boolean): indica se utilizzare la funzione mail() di PHP (@see sendPhpMail()) per inviare una email (default @a true)
	 *   - @b mailer (string): tipologia di mailer utilizzato per l'invio
	 *	   - @a mail (default)
	 *	   - @a sendmail
	 *	   - @a qmail
	 *	   - @a smtp
	 *   - // Recipients
	 *   - @b sender_name (string)
	 *   - @b reply_email (string)
	 *   - @b reply_name (string)
	 *   - @b cc (string): aggiungere uno o più destinatari come Copia Conoscenza (separati da virgola)
	 *   - @b ccn (string): aggiungere uno o più destinatari come Copia Conoscenza Nascosta (utile in un sistema di Newsletter se inserita all'interno di un ciclo)
	 *   - @b notification (string): indirizzo per la conferma di lettura (se richiesta)
	 *   - // SMTP settings
	 *   - @b smtp_service_name (string): nome del servizio del server smtp (@see setSmtpParams)
	 *   - @b smtp_secure (string): enable TLS or ssl encryption
	 *     - @a tls
	 *     - @a ssl
	 *   - @b smtp_server (string): indirizzo smtp
	 *   - @b smtp_port (integer): numero della porta del servizio smtp (default 25)
	 *   - @b smtp_auth (boolean): autenticazione smtp (default false)
	 *   - @b smtp_auth_type (string): tipologia di autenticazione; LOGIN (default), PLAIN, NTLM, CRAM-MD5
	 *   - @b smtp_user (string): account per l'autenticazione SMTP (deve essere una casella attiva e funzionante sul server, altrimenti potrà essere considerata SPAM)
	 *   - @b smtp_password (string): password dell'account SMTP
	 *   - // Server settings
	 *   - @b exception (boolean): per generare le eccezioni esterne (throw exceptions); default @a false
	 *   - @b debug (integer): informazioni per il debug:
	 *	   - 0, No output (default)
	 *	   - 1, Commands
	 *	   - 2, Data and commands
	 *	   - 3, As 2 plus connection status
	 *	   - 4, Low-level data output
	 *   - @b ishtml (boolean): dichiara che è una email html (default @a true)
	 *   - @b charset (string): set di caratteri (default @a utf-8)
	 *   - @b priority (integer): priorità (default @a 3)
	 *   - @b view_mailer (boolean): visualizza il mailer nell'invio di una email con il metodo sendPhpMail (default @a false)
	 *   - // Attachments
	 *   - @b attachments (array): elenco degli allegati dove ogni file è un array [path => string, name => string]
	 *	   - @a path: percorso dell'allegato, @example /var/tmp/file.tar.gz @example /tmp/image.jpg
	 *	   - @a name: se definito sovrascrive il nome dell'allegato (opzionale), @example new.jpg
	 *   - @b embedded_images (array): immagini inline (ovvero incorporate nel testo) 
	 *     dove ogni immagine è un array [path => string, cid => string, name => string]
	 *	   - @a path: percorso dell'allegato
	 *	   - @a cid: content id dell'allegato, ovvero il riferimento per collagarlo al tag IMG
	 *	   - @a name: se definito sovrascrive il nome dell'allegato (opzionale)
	 *   - // Formatting
	 *   - @b automatic_html (boolean): crea un messaggio in modo automatico a partire da una stringa html (default @a false); @see PHPMailer::msgHTML()
	 *   - @b alternative_text (string): messaggio in formato testo (nel caso in cui il destinatario non possa vedere il formato html)
	 *   - @b crlf (string): default "\r\n"
	 *   - @b newline (string): default "\r\n"
	 *   - @b word_wrap (integer): numero di caratteri di una riga (default 50)
	 * @return boolean
	 */
	public function sendMail($recipient_email, $sender_email, $subject, $contents, $options=array()) {

		$use_mail = array_key_exists('use_mail', $options) ? $options['use_mail'] : true;
		$mailer = array_key_exists('mailer', $options) ? $options['mailer'] : 'mail';
		
		// Recipients
		$sender_name = array_key_exists('sender_name', $options) ? $options['sender_name'] : '';
		$reply_email = array_key_exists('reply_email', $options) ? $options['reply_email'] : '';
		$reply_name = array_key_exists('reply_name', $options) ? $options['reply_name'] : '';
		$cc = array_key_exists('cc', $options) ? $options['cc'] : '';
		$ccn = array_key_exists('ccn', $options) ? $options['ccn'] : '';
		$notification = array_key_exists('notification', $options) ? $options['notification'] : '';
		
		// Attachments
		$attachments = (array_key_exists('attachments', $options) && is_array($options['attachments'])) ? $options['attachments'] : array();
		$embedded_images = (array_key_exists('embedded_images', $options) && is_array($options['embedded_images'])) ? $options['embedded_images'] : array();
		
		// SMTP settings
		$smtp_service_name = array_key_exists('smtp_service_name', $options) ? $options['smtp_service_name'] : null;
		$smtp_secure = array_key_exists('smtp_secure', $options) ? $options['smtp_secure'] : null;
		$smtp_server = array_key_exists('smtp_server', $options) ? $options['smtp_server'] : '';
		$smtp_port = array_key_exists('smtp_port', $options) ? $options['smtp_port'] : 25;
		$smtp_auth = array_key_exists('smtp_auth', $options) ? $options['smtp_auth'] : false;
		$smtp_auth_type = array_key_exists('smtp_auth_type', $options) ? $options['smtp_auth_type'] : null;
		$smtp_user = array_key_exists('smtp_user', $options) ? $options['smtp_user'] : '';
		$smtp_password = array_key_exists('smtp_password', $options) ? $options['smtp_password'] : '';
		
		// Server settings
		$exception = array_key_exists('exception', $options) ? $options['exception'] : false;
		$debug = array_key_exists('debug', $options) ? $options['debug'] : 0;
		$ishtml = array_key_exists('ishtml', $options) ? $options['ishtml'] : true;
		$charset = array_key_exists('charset', $options) ? $options['charset'] : 'UTF-8';
		$priority = array_key_exists('priority', $options) ? $options['priority'] : 3;
		$view_mailer = array_key_exists('view_mailer', $options) ? $options['view_mailer'] : false;
		
		// Formatting
		$automatic_html = array_key_exists('automatic_html', $options) ? $options['automatic_html'] : false;
		$alternative_text = array_key_exists('alternative_text', $options) ? $options['alternative_text'] : null;
		$crlf = array_key_exists('crlf', $options) && $options['crlf'] ? $options['crlf'] : "\r\n";
		$newline = array_key_exists('newline', $options) && $options['newline'] ? $options['newline'] : "\r\n";
		$word_wrap = array_key_exists('word_wrap', $options) && $options['word_wrap'] ? $options['word_wrap'] : 50;
		
		if($use_mail)
		{
			return $this->sendPhpMail($recipient_email, $subject, $contents, 
				array(
					'sender_email' => $sender_email, 
					'sender_name' => $sender_name, 
					'reply_email' => $reply_email, 
					'reply_name' => $reply_name, 
					'cc' => $cc, 
					'ccn' => $ccn,
					'charset' => $charset, 
					'view_mailer' => $view_mailer
				)
			);
		}
		
		$mail = new PHPMailer($exception);
		
		// Define mailer
		if($mailer == 'mail') {
			$mail->isMail();
		}
		elseif($mailer == 'sendmail') {
			$mail->isSendmail();
		}
		elseif($mailer == 'qmail') {
			$mail->isQmail();
		}
		elseif($mailer == 'smtp') {
		    $mail->isSMTP();              // Set mailer to use SMTP
		}
		else {
			return false;
		}
		
		// Server settings
		$mail->SMTPDebug = $debug;        // Enable verbose debug output
		$mail->Debugoutput = 'html';
		$mail->Priority = $priority;
		$mail->CharSet = $charset;
		
		if($smtp_service_name)
		{
		    $params = $this->setSmtpParams($smtp_service_name);
		    
		    if(count($params))
		    {
		        if(array_key_exists('smtp_auth', $params)) {
		            $smtp_auth = $params['smtp_auth'];
		        }
		        if(array_key_exists('smtp_secure', $params)) {
		            $smtp_secure = $params['smtp_secure'];
		        }
		        if(array_key_exists('smtp_server', $params)) {
		            $smtp_server = $params['smtp_server'];
		        }
		        if(array_key_exists('smtp_port', $params)) {
		            $smtp_port = $params['smtp_port'];
		        }
		    }
		}
		
		if($smtp_secure) {
		    $mail->SMTPSecure = $smtp_secure; // Enable TLS or ssl encryption
		}
		if($smtp_server) {
		    $mail->Host = $smtp_server;       // Specify main and backup SMTP servers - @example smtp1.example.com;smtp2.example.com
		}
		if($smtp_port) {
		    $mail->Port = $smtp_port;         // TCP port to connect to
		}
		
		if($smtp_auth)
		{
		    $mail->SMTPAuth = $smtp_auth;         // Enable SMTP authentication
		    $mail->Username = $smtp_user;         // SMTP username
		    $mail->Password = $smtp_password;     // SMTP password
		    
		    if($smtp_auth_type) {
		        $mail->AuthType = $smtp_auth_type;
		    }
		}
		
		// Recipients
		$mail->setFrom($sender_email, $sender_name);          // @example setFrom('from@example.com', 'Mailer')
		$mail->addAddress($recipient_email);                  // Add a recipient (name is optional) - @example addAddress('joe@example.net', 'Joe User')
		if($reply_email) {
		    $mail->addReplyTo($reply_email, $reply_name);     // @example addReplyTo('info@example.com', 'Information')
		}
		if($cc) {
		    $mail->addCC($cc);		
		}
		if($ccn) {
		    $mail->addBCC($ccn);
		}
		if($notification) {
		    $mail->ConfirmReadingTo = $notification;
		}
		
		//Content
		$mail->isHTML($ishtml);       // Set email format to HTML
		$mail->Subject = $subject;
		$mail->Body	= $contents;
		$mail->WordWrap = $word_wrap;
		
		if($ishtml) {
			if(!$alternative_text) {
				$alternative_text = 'To view this email message, open it in a program that understands HTML!';
			}
			$mail->AltBody = $alternative_text;
		}
		
		// Inline images
		if(count($embedded_images))
		{
			foreach($embedded_images AS $array) {
				if(array_key_exists('path', $array) && array_key_exists('cid', $array))
				{
					$tmp_name = array_key_exists('name', $array) && $array['name'] ? $array['name'] : '';
					$mail->addEmbeddedImage($array['path'], $array['cid'], $tmp_name);
				}
			}
		}
		
		//Attachments
		if(count($attachments))
		{
			foreach($attachments AS $array) {
				
				if(array_key_exists('path', $array))
				{
					$tmp_name = array_key_exists('name', $array) && $array['name'] ? $array['name'] : '';
					$mail->addAttachment($array['path'], $tmp_name);
				}
			}
		}
		
		if($automatic_html) {
			$mail->msgHTML($contents);
		}
		
		if($exception) {	
			try {
				$mail->send();
				echo 'Message has been sent';
			} catch (Exception $e) {
				echo 'Message could not be sent.';
				echo 'Mailer Error: ' . $mail->ErrorInfo;
			}
		}
		else {
		    if($mail->send()) {
		        return true;
		    }
		    else {
		        if($debug != 0)
		        {
		            echo 'Message was not sent.';
		            echo 'Mailer Error: '.$mail->ErrorInfo;
		        }
		        return false;
		    }
		}
	}
}
