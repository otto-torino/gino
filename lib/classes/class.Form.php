<?php
/**
 * @file class.Form.php
 * @brief Contiene la definizione ed implementazione della classe Gino.Form
 *
 * @copyright 2005-2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

use \Gino\App\Language\language;

\Gino\Loader::import('class/exceptions', array('\Gino\Exception\Exception403'));

/**
 * @brief Classe per la creazione ed il salvataggio dati di un form
 *
 * Fornisce gli strumenti per generare gli elementi del form e per gestire l'upload di file
 *
 * @copyright 2005-2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class Form {

	protected $_registry;
	protected $_request;
	protected $_session;
	
	private $_form_id;
	private $_method, $_requestVar;
	private $_validation;
	
	/**
	 * @brief Nome della variabile di sessione dei dati del form
	 * @var string
	 */
	private $_session_value;
	
	private $_hidden;
	
	/**
	 * @brief Multilingua
	 * @var boolean
	 */
	private $_multi_language;
	
	/**
     * @brief Costruttore
     * 
     * @param array $options
     *   array associativo di opzioni
     *   
     *   - @b verifyToken (boolean): verifica il token (contro gli attacchi CSFR)
     *   - @b form_label_width (string): larghezza (%) della colonna con il tag label (default FORM_LABEL_WIDTH)
     *   - @b form_field_width (string): larghezza (%) della colonna con il tag input (default FORM_FIELD_WIDTH)
     * @throws Exception se viene rilevato un attacco CSRF
     * @return istanza di Gino.Form
     * 
     * Le proprietà $_form_id, $_validation, $_method, $_session_value vengono impostate nel metodo render().
     */
    function __construct($options=array()){

    	$this->_registry = registry::instance();
    	$this->_request = $this->_registry->request;
    	$this->_session = Session::instance();
    	
    	$this->_multi_language = $this->_registry->sysconf->multi_language;
    	
    	$this->_form_id = null;
    	$this->_hidden = null;
    	
    	$verify_token = gOpt('verifyToken', $options, false);
    	$form_id = gOpt('form_id', $options, null);	// per l'action form
    	
    	/*		DEFINIRE PRIMA form_id
    	if($verify_token) {
    		if(!$this->verifyFormToken($formId)) {
    			throw new \Exception(_("Rilevato attacco CSRF o submit del form dall'esterno "));
    		}
    	}*/
    }
    
    /**
     * @brief Getter della proprietà $_form_id
     * @return string
     */
    public function getFormId() {
    	
    	return $this->_form_id;
    }
    
    /**
     * @brief Setter della proprietà $_method
     * @description Imposta le proprietà $_method, $_requestVar
     * 
     * @param string $method metodo di passaggio dei dati del form (metodi validi: post, get, request)
     * @return void
     */
    private function setMethod($method){

    	$valid = array('post', 'get', 'request');
    	
    	if(!$method or ($method && !in_array($method, $valid))) {
    		$method = 'post';
    	}
        $this->_method = $method;
        $this->_requestVar = $method == 'post' ? $this->_request->POST : ($method=='get' ? $this->_request->GET : $this->_request->REQUEST);

        if(is_null($this->_session->form)) $this->_session->form = array();
    }

    /**
     * @brief Setter della proprietà $_validation
     * 
     * @param bool $validation indica se eseguire o meno la validazione del form (attiva la chiamata javascript validateForm())
     * @return void
     */
    private function setValidation($validation){

        $this->_validation = (bool) $validation;
    }
    
    private function setDefaultFormId($model) {
    	
    	return 'form'.$model->getTable().$model->id;
    }
    
    private function setDefaultSession($model) {
    	 
    	return 'dataform'.$model->getTable().$model->id;
    }

    /**
     * @brief Genera un token per prevenire attacchi CSRF
     * @param string $form_id
     * @return token
     */
    private function generateFormToken($form_id) {
    	
    	$token = md5(uniqid(microtime(), TRUE));
    	$this->_session->{$form_id.'_token'} = $token;
    	return $token;
    }

    /**
     * @brief Verifica il token per prevenire attacchi CSRF
     * @param string $form_id
     * @return risultato verifica, bool
     */
    private function verifyFormToken($form_id) {
        
    	$index = $form_id.'_token';
        // There must be a token in the session
        if(!isset($this->_session->$index)) return FALSE;
        // There must be a token in the form
        if(!isset($this->_requestVar['token'])) return FALSE;
        // The token must be identical
        if($this->_session->$index !== $this->_requestVar['token']) return FALSE;

        return TRUE;
    }

    /**
     * @brief Recupera i valori inseriti negli input form e salvati nella sessione del form
     *
     * @description Permette di mostrare i campi correttamente compilati a seguito di errore
     *
     * @param array $session_value nome della variabile di sessione nella quale sono salvati i valori degli input
     * @param boolean $clear distrugge la sessione
     * @return global
     */
    public function load($session_value, $clear = TRUE){

        $this->_session->form = array($this->_method => '');
        $form_data = array();
		
		//$this->session->form[$this->_method] = array();	// @todo implementare a partire dalla versione 5.4
		
		if(isset($this->_session->$session_value))
        {
            if(isset($this->_session->GINOERRORMSG) AND !empty($this->_session->GINOERRORMSG))
            {
                for($a=0, $b=count($this->_session->$session_value); $a < $b; $a++)
                {
                    foreach($this->_session->{$session_value}[$a] as $key => $value)
                    {
                        $form_data[$key] = $value;
                    }
                }
                $this->_session->form = array($this->_method => $form_data);
            }

            if($clear) unset($this->_session->$session_value);
        }
    }

    /**
     * @brief Salva i valori dei campi del form in una variabile di sessione
     * 
     * @param string $session_value nome della variabile di sessione, come definito nel metodo load()
     * @return void
     */
    public function saveSession($session_value=null){

    	if(!$session_value) {
    		$session_value = $this->_session_value;
    	}
    	
        $this->_session->{$session_value} = array();
        $session_prop = $this->_session->{$session_value};
        foreach($this->_requestVar as $key => $value) {
        	array_push($session_prop, array($key => $value));
        }

        $this->_session->$session_value = $session_prop;
    }

    /**
     * @brief Recupera il valore di un campo del form precedentemente salvato
     *
     * @see Gino.Form::load()
     * @see Gino.Form::save()
     * @param string $name nome del campo
     * @param mixed $default valore di default
     * @return valore campo
     */
    public function retvar($name, $default = '') {
        
    	return (is_null($this->_session->form) or !isset($this->_session->form[$this->_method][$name])) ? $default : $this->_session->form[$this->_method][$name];
    }

    /**
     * @brief Parte inziale del form, FORM TAG, TOKEN, REQUIRED
     *
     * Per attivare le opzioni @b func_confirm e @b text_confirm occorre istanziare la classe Form con il parametro validation (TRUE)
     *
     * @param string $action indirizzo dell'action
     * @param boolean $upload attiva l'upload di file
     * @param string $list_required lista di elementi obbligatori (separati da virgola)
     * @param array $options
     *   array associativo di opzioni
     *   - @b form_id (string): valore id del tag form
     *   - @b func_confirm (string): nome della funzione js da chiamare (es. window.confirmSend())
     *   - @b text_confirm (string): testo del messaggio che compare nel box di conferma
     *   - @b generateToken (boolean): costruisce l'input hidden token (contro gli attacchi CSFR)
     * @return parte iniziale del form, html
     */
    public function open($action, $upload, $list_required, $options=array()) {

        $form_id = gOpt('form_id', $options, null);
        
        if($form_id) {
        	$this->_form_id = $form_id;
        }
    	
    	$buffer = '';

        $confirm = '';
        if(isset($options['func_confirm']) && $options['func_confirm']) {
        	$confirm = " && ".$options['func_confirm'];
        }
        if(isset($options['text_confirm']) && $options['text_confirm']) {
        	$confirm = " && confirmSubmit('".$options['text_confirm']."')";
        }

        $buffer .= "<form ".($upload?"enctype=\"multipart/form-data\"":"")." id=\"".$this->_form_id."\" name=\"".$this->_form_id."\" action=\"$action\" method=\"$this->_method\"";
        if($this->_validation) {
        	$buffer .= " onsubmit=\"return (gino.validateForm($(this))".$confirm.")\"";
        }
        $buffer .= ">\n";

        if($list_required) {
            $buffer .= "<p class=\"form-info\">"._("I campi in grassetto sono obbligatori.")."</p>";
        }

        if(isset($options['generateToken']) && $options['generateToken']) {
            $buffer .= Input::hidden('token', $this->generateFormToken($this->_form_id));
        }
        if(!empty($list_required)) {
        	$buffer .= Input::hidden('required', $list_required);
        }

        return $buffer;
    }

    /**
     * @brief Chiusura form, FORM TAG
     * @return chiusura form, html
     */
    public function close(){

        return "</form>\n";
    }

    /**
     * @brief Controlla la compilazione dei campi obbligatori
     * @return numero campi obbligatori non compilati
     */
    public function checkRequired(){

        $required = isset($this->_requestVar['required']) ? cleanVar($this->_requestVar, 'required', 'string', '') : '';
        $error = 0;

        if(!empty($required)) {
        	foreach(explode(",", $required) as $fieldname) {
            	if((!isset($this->_requestVar[$fieldname]) or $this->_requestVar[$fieldname] == '') and (!isset($this->_request->FILES[$fieldname]) or $this->_request->FILES[$fieldname] == '')) $error++;
        	}
        }
        return $error;
    }

    /**
     * @brief Widget Captcha
     *
     * Sono previsti due controlli captcha: \n
     * 1. con le librerie reCAPTCHA (attivo automaticamente se sono state inserite le chiavi pubbliche e private reCaptcha nelle 'Impostazioni di sistema')
     * 2. con la classe captcha di gino
     *
     * @see self::reCaptcha()
     * @see self::defaultCaptcha()
     * @param array $options
     *   array associativo di opzioni
     *   - @b classLabel (string): valore CLASS del tag SPAN in <label>
     *   - @b text_add (string): testo che segue il controllo
     * @return widget captcha
     */
    public function captcha($options=null) {

        $public_key = $this->_registry->sysconf->captcha_public;
        $private_key = $this->_registry->sysconf->captcha_private;

        if($public_key && $private_key) {
        	return $this->reCaptcha($public_key, $options);
        }
        else {
        	return $this->defaultCaptcha($options);
        }
     }

    /**
     * @brief Captcha widget attraverso la libreria RECAPTCHA
     * 
     * Nelle Impostazioni di sistema devono essere state inserite le chiavi pubbliche e private reCaptcha
     * 
     * @param string $public_key
     * @param array $options
     *   array associativo di opzioni
     *   - @b text_add (string)
     * @return widget captcha
     */
    private function reCaptcha($public_key, $options=null) {

        $text_add = gOpt('text_add', $options, null);
        
        $required = true;
        
        $buffer = Input::label('captcha_input', _("Inserisci il codice di controllo"), $required)."\n";
        $buffer .= "<div id=\"".$this->_form_id."_recaptcha\"></div>";
        $buffer .= "<script>
            function createCaptcha() {
                if(\$chk($('".$this->_form_id."_recaptcha'))) {
                    Recaptcha.create('$public_key', '".$this->_form_id."_recaptcha', {theme: 'red', callback: Recaptcha.focus_response_field});
                    clearInterval(window.captcha_int);
                }
            }
            window.captcha_int = setInterval(createCaptcha, 50);
        </script>";
        if($text_add) {
        	$buffer .= "<div class=\"form-textadd\">".$text_add."</div>";
        }
        return $buffer;
    }

    /**
     * @brief Captcha widget attraverso la libreria di gino
     *
     * @see Gino.Captcha::render()
     * @param array $options
     *   array associativo di opzioni
     *   - @b text_add (string)
     * @return widget captcha
     */
    private function defaultCaptcha($options) {

        $text_add = gOpt('text_add', $options, null);
        
        $required = true;

        $captcha = Loader::load('Captcha', array('captcha_input'));

        $buffer = Input::label('captcha_input', _("Inserisci il codice dell'immagine"), $required)."\n";
        $buffer .= $captcha->render();
        if($text_add) {
        	$buffer .= "<div class=\"form-textadd\">".$text_add."</div>";
        }

        return $buffer;
    }

    /**
     * @brief Verifica del captcha
     * 
     * @see self::checkReCaptcha()
     * @see self checkDefaultCaptcha()
     * @return risultato verifica, bool
     */
    public function checkCaptcha() {

        $public_key = $this->_registry->sysconf->captcha_public;
        $private_key = $this->_registry->sysconf->captcha_private;

        if($public_key && $private_key) return $this->checkReCaptcha($public_key, $private_key);
        else return $this->checkDefaultCaptcha();
    }

    /**
     * @brief Verifica captcha utilizzando la libreria RECAPTCHA
     *
     * @param string $public_key
     * @param string $private_key
     * @return risultato verifica, bool
     */
    private function checkReCaptcha($public_key, $private_key) {

        require_once(LIB_DIR.OS.'recaptchalib.php');
        $private_key = pub::getConf("captcha_private");
        $resp = recaptcha_check_answer($private_key, $_SERVER["REMOTE_ADDR"], $this->_requestVar["recaptcha_challenge_field"], $this->_requestVar["recaptcha_response_field"]);

        $captcha = cleanVar($this->_requestVar, 'captcha_input', 'string', '');
        return $resp->is_valid ? TRUE : FALSE;
    }

    /**
     * @brief Verifica captcha utilizzando la libreria di gino
     *
     * @see Gino.Captcha::check()
     * @return risultato della verifica, bool
     */
    private function checkDefaultCaptcha() {

        $captcha = Loader::load('Captcha', array('captcha_input'));
        return $captcha->check();
    }

    /**
     * @brief Conteggio di file con stesso nome all'interno di $directory
     * @param string $file_new nome nuovo file
     * @param string $file_old nome file precedente
     * @param bool $resize
     * @param string $prefix_file
     * @param string $prefix_thumb
     * @param string $directory path
     * @return numero files con stesso nome
     */
    /*
    private function countEqualName($file_new, $file_old, $resize, $prefix_file, $prefix_thumb, $directory){

        $listFile = searchNameFile($directory);
        $count = 0;
        if(sizeof($listFile) > 0)
        {
            foreach($listFile AS $value)
            {
                if(!empty($file_old))
                {
                    if($resize)
                    {
                        if(!empty($prefix_file))
                        {
                            if($prefix_file.$file_new == $value AND $prefix_file.$file_old != $value) $count++;
                        }

                        if(!empty($prefix_thumb))
                        {
                            if($prefix_thumb.$file_new == $value AND $prefix_thumb.$file_old != $value) $count++;
                        }
                    }
                    else
                    {
                        if($file_new == $value AND $file_old != $value) $count++;
                    }
                }
                else
                {
                    if($resize)
                    {
                        if(!empty($prefix_file))
                        {
                            if($prefix_file.$file_new == $value) $count++;
                        }

                        if(!empty($prefix_thumb))
                        {
                            if($prefix_thumb.$file_new == $value) $count++;
                        }
                    }
                    else
                    {
                        if($file_new == $value) $count++;
                    }
                }
            }
        }
        return $count;
    }
    */

    /**
     * @brief Upload del file temporaneo nella directory di destinazione
     * @param resource $file_tmp
     * @param string $file_name
     * @param string $uploaddir directory di destinazione
     * @return risultato operazione, bool
     */
    /*
    private function upload($file_tmp, $file_name, $uploaddir){

        $uploadfile = $uploaddir.$file_name;
        if(move_uploaded_file($file_tmp, $uploadfile)) return TRUE;
        else return FALSE;
    }
    */

    /**
     * @brief Imposta il carattere '/' come ultimo carattere della directory
     *
     * @param string $directory nome della directory
     * @return path directory
     */
    /*
    private function dirUpload($directory){

        $directory = (substr($directory, -1) != '/' && $directory != '') ? $directory.'/' : $directory;
        return $directory;
    }
    */

    /**
     * @brief Sostituisce nel nome di un file i caratteri diversi da [a-zA-Z0-9_.-] con il carattere underscore (_)
     *
     * @param string $filename nome del file
     * @param string $prefix prefisso da aggiungere al nome del file
     * @return nome file normalizzato
     */
    /*
    private function checkFilename($filename, $prefix) {

        $filename = preg_replace("#[^a-zA-Z0-9_\.-]#", "_", $filename);
        return $prefix.$filename;
    }
    */

    /**
     * @brief Gestisce l'upload di un file
     *
     * Verifica la conformità del file ed effettua l'upload. Eventualmente crea la directory e ridimensiona l'immagine.
     *
     * @see self::dirUpload()
     * @see self::countEqualName()
     * @see self::upload()
     * @see self::saveImage()
     * @param string $name nome input
     * @param string $old_file nome del file esistente
     * @param boolean $resize ridimensionamento del file
     * @param array $valid_extension estensioni lecite di file
     * @param string $directory directory di upload (/path/to/directory/)
     * @param string $link_error parametri da aggiungere al reindirizzamento
     * @param string $table tabella da aggiornare inserendo il nome del file (UPDATE); se NULL non viene effettuata la query di UPDATE
     * @param string $field nome del campo del file; se NULL non viene effettuata la query di UPDATE
     * @param string $idName nome del campo ID; se NULL non viene effettuata la query di UPDATE
     * @param string $id valore del campo ID; se NULL non viene effettuata la query di UPDATE
     * @param array $options
     *     array associativo di opzioni
     *     - @b check_type (boolean): attiva l'opzione @a types_allowed (TRUE, o 1 per compatibilità => controlla il tipo di file, FALSE => non controllare)
     *     - @b types_allowed (array): array per alcuni tipi di file (mime types)
     *     - @b max_file_size (integer): dimensione massima di un upload (bytes)
     *     - @b thumb (boolean): attiva i thumbnail
     *     - @b prefix (string): per fornire un prefisso a prescindere dal ridimensionamento
     *     - @b prefix_file (string): nel caso resize=TRUE
     *     - @b prefix_thumb (string): nel caso resize=TRUE
     *     - @b width (integer): larghezza alla quale ridimensionare l'immagine
     *     - @b height (integer): altezza alla quale ridimensionare l'immagine
     *     - @b thumb_width (integer): larghezza del thumbnail
     *     - @b thumb_height (integer): altezza del thumbnail
     *     - @b ftp (boolean): permette di inserire il nome del file qualora questo risulti di dimensione superiore al consentito. Il file fisico deve essere poi inserito via FTP
     *     - @b errorQuery (string): query di eliminazione del record qualora non vada a buon fine l'upload del file (INSERT)
     * @return risultato operazione, bool o errori
     */
    /*
	public function manageFile($name, $old_file, $resize, $valid_extension, $directory, $link_error, $table, $field, $idName, $id, $options=null){

        $db = Db::instance();

        $this->setOptions($options);
        $directory = $this->dirUpload($directory);
        if(!is_dir($directory)) mkdir($directory, 0755, TRUE);

        $check_type = !is_null($this->option('check_type')) ? $this->option('check_type') : TRUE;
        $types_allowed = $this->option('types_allowed') ? $this->option('types_allowed') : 
        array(
            "text/plain",
            "text/html",
            "text/xml",
            "image/jpeg",
            "image/gif",
            "image/png",
            "video/mpeg",
            "audio/midi",
            "application/pdf",
            "application/x-compressed",
            "application/x-zip-compressed",
            "application/zip",
            "multipart/x-zip",
            "application/vnd.ms-excel",
            "application/x-msdos-program",
            "application/octet-stream"
        );

        $prefix = !is_null($this->option('prefix')) ? $this->option('prefix') : '';
        $thumb = !is_null($this->option('thumb')) ? $this->option('thumb') : TRUE;
        $prefix_file = !is_null($this->option('prefix_file')) ? $this->option('prefix_file') : '';
        $prefix_thumb = $this->option('prefix_thumb') ? $this->option('prefix_thumb') : $this->_prefix_thumb;
        $max_file_size = $this->option('max_file_size') ? $this->option('max_file_size') : $this->_max_file_size;

        if(isset($_FILES[$name]['name']) AND $_FILES[$name]['name'] != '') {
            $new_file = $_FILES[$name]['name'];
            $new_file_size = $_FILES[$name]['size'];
            $tmp_file = $_FILES[$name]['tmp_name'];

            if($resize) {
                // Verifico la corrispondenza dei prefissi con il nome del file
                if(preg_match("#^($prefix_thumb).+$#", $new_file, $matches))
                $new_file = substr_replace($new_file, '', 0, strlen($prefix_thumb));

                if(preg_match("#^($prefix_file).+$#", $new_file, $matches))
                $new_file = substr_replace($new_file, '', 0, strlen($prefix_file));
            }

            $new_file = $this->checkFilename($new_file, $prefix);

            if($new_file_size > $max_file_size && !$this->option('ftp')) {
                if($this->option("errorQuery")) $db->execCustomQuery($this->option("errorQuery"), array('statement'=>'action'));
                return error::errorMessage(array('error'=>33), $link_error);
            }

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $tmp_file);
            finfo_close($finfo);
            if(
            	!extension($new_file, $valid_extension) ||
           		preg_match('#%00#', $new_file) ||
            	(($check_type || $check_type == 1) && !in_array($mime, $types_allowed))
            ) {
                if($this->option("errorQuery")) $db->execCustomQuery($this->option("errorQuery"), array('statement'=>'action'));
                return error::errorMessage(array('error'=>03), $link_error);
            }

            $count = $this->countEqualName($new_file, $old_file, $resize, $prefix_file, $prefix_thumb, $directory);
            if($count > 0) {
                if($this->option("errorQuery")) $db->execCustomQuery($this->option("errorQuery"), array('statement'=>'action'));
                return error::errorMessage(array('error'=>04), $link_error);
            }
        }
        else {$new_file = '';$new_file_tmp = '';}

        $del_file = (isset($this->_requestVar["check_del_$name"]) && $this->_requestVar["check_del_$name"]=='ok');
        $upload = $delete = FALSE;
        $upload = !empty($new_file);
        $delete = (!empty($new_file) && !empty($old_file)) || $del_file; 

        if($delete && $resize)
        {
            if(is_file($directory.$prefix_file.$old_file)) 
                if(!@unlink($directory.$prefix_file.$old_file)) {
                    if($this->option("errorQuery")) $db->execCustomQuery($this->option("errorQuery"), array('statement'=>'action'));
                    return error::errorMessage(array('error'=>17), $link_error);
                }

            if($thumb && !empty($prefix_thumb)) {
                if(is_file($directory.$prefix_thumb.$old_file))
                    if(!@unlink($directory.$prefix_thumb.$old_file)) {
                        if($this->option("errorQuery")) $db->execCustomQuery($this->option("errorQuery"), array('statement'=>'action'));
                        return error::errorMessage(array('error'=>17), $link_error);
                    }
            }
        }
        elseif($delete && !$resize)
        {
            if(is_file($directory.$old_file)) 
                if(!@unlink($directory.$old_file)) {
                    if($this->option("errorQuery")) $db->execCustomQuery($this->option("errorQuery"), array('statement'=>'action'));
                    return error::errorMessage(array('error'=>17), $link_error);
                }
        }

        if($upload) {
            if(!$this->upload($tmp_file, $new_file, $directory)) {
                if($this->option("errorQuery")) $db->execCustomQuery($this->option("errorQuery"), array('statement'=>'action'));
                return error::errorMessage(array('error'=>16), $link_error);
            }
            else $result = TRUE;
        }
        else $result = FALSE;

        if($result AND $resize) {
            $new_width = $this->option('width') ? $this->option('width') : 800;
            $new_height = $this->option('height') ? $this->option('height') : '';
            $thumb_width = $this->option('thumb_width') ? $this->option('thumb_width') : 200;
            $thumb_height = $this->option('thumb_height') ? $this->option('thumb_height') : '';

            if(!$thumb) { $thumb_width = $thumb_height = null; }

            if(!$this->saveImage($new_file, $directory, $prefix_file, $prefix_thumb, $new_width, $new_height, $thumb_width, $thumb_height)) {
                if($this->option("errorQuery")) $db->execCustomQuery($this->option("errorQuery"), array('statement'=>'action'));
                return error::errorMessage(array('error'=>18), $link_error);
            }
        }

        if($upload) $filename_sql = $new_file;
        elseif($delete) $filename_sql = '';
        else $filename_sql = $old_file;

        if($table && $field && $idName && $id)
        {
            $result = $db->update(array($field=>$filename_sql), $table, "$idName='$id'");

            if(!$result) {
                if($upload && !$resize) {
                    @unlink($directory.$new_file);
                }
                elseif($upload && $resize) {
                    @unlink($directory.$prefix_file.$new_file);
                    @unlink($directory.$prefix_thumb.$new_file);
                }
                if($this->option("errorQuery")) $db->execCustomQuery($this->option("errorQuery"), array('statement'=>'action'));
                return error::errorMessage(array('error'=>16), $link_error);
            }
        }

        return TRUE;
    }
    */

    /**
     * @brief Calcola le dimensioni alle quali deve essere ridimensionata una immagine
     * 
     * @param integer $new_width
     * @param integer $new_height
     * @param integer $im_width
     * @param integer $im_height
     * @return array (larghezza, altezza)
     */
    /*
    private function resizeImage($new_width, $new_height, $im_width, $im_height){

        if(!empty($new_width) AND $im_width > $new_width)
        {
            $width = $new_width;
            $height = ($im_height / $im_width) * $new_width;
        }
        elseif(!empty($new_height) AND $im_height > $new_height)
        {
            $width = ($im_width / $im_height) * $new_height;
            $height = $new_height;
        }
        else
        {
            $width = $im_width;
            $height = $im_height;
        }

        return array($width, $height);
    }
    */

    /**
     * @brief Salva le immagini eventualmente ridimensionandole
     * 
     * Se @b thumb_width e @b thumb_height sono nulli, il thumbnail non viene generato
     * 
     * @param string $filename nome del file
     * @param string $directory percorso della directory del file
     * @param string $prefix_file prefisso da aggiungere al file
     * @param string $prefix_thumb prefisso da aggiungere al thumbnail
     * @param integer $new_width larghezza dell'immagine
     * @param integer $new_height altezza dell'immagine
     * @param integer $thumb_width larghezza del thumbnail
     * @param integer $thumb_height altezza del thumbnail
     * @return risultato operazione, bool
     */
    /*
    public function saveImage($filename, $directory, $prefix_file, $prefix_thumb, $new_width, $new_height, $thumb_width, $thumb_height){

        $thumb = (is_null($thumb_width) && is_null($thumb_height)) ? FALSE : TRUE;
        $file = $directory.$filename;
        list($im_width, $im_height, $type) = getimagesize($file);

        if(empty($prefix_file))
        {
            $rename = $directory.'tmp_'.$filename;
            if(rename($file, $rename))
                $file = $rename;
        }

        $img_file = $directory.$prefix_file.$filename;
        $img_size = $this->resizeImage($new_width, $new_height, $im_width, $im_height);

        if($thumb)
        {
            $thumb_file = $directory.$prefix_thumb.$filename;
            $thumb_size = $this->resizeImage($thumb_width, $thumb_height, $im_width, $im_height);
        }

        if($type == self::_IMAGE_JPG_)
        {
            if($img_size[0] != $im_width AND $img_size[1] != $im_height)
            {
                $sourcefile_id = @imagecreatefromjpeg($file);
                $destfile_id = imagecreatetruecolor($img_size[0], $img_size[1]);
                imagecopyresampled($destfile_id, $sourcefile_id, 0, 0, 0, 0, $img_size[0], $img_size[1], $im_width, $im_height);
                imagejpeg($destfile_id, $img_file);
            }
            else
            {
                copy($file, $img_file);
            }

            if($thumb && $thumb_size[0] != $im_width && $thumb_size[1] != $im_height)
            {
                $sourcefile_id = @imagecreatefromjpeg($file);
                $destfile_id = imagecreatetruecolor($thumb_size[0], $thumb_size[1]);
                imagecopyresampled($destfile_id, $sourcefile_id, 0, 0, 0, 0, $thumb_size[0], $thumb_size[1], $im_width, $im_height);
                imagejpeg($destfile_id, $thumb_file);
            }
            else
            {
                copy($file, $thumb_file);
            }

            @unlink($file);
            return TRUE;
        }
        elseif($type == self::_IMAGE_PNG_)
        {
            if($img_size[0] != $im_width AND $img_size[1] != $im_height)
            {
                $sourcefile_id = @imagecreatefrompng($file);
                $destfile_id = imagecreatetruecolor($img_size[0], $img_size[1]);
                imagecopyresampled($destfile_id, $sourcefile_id, 0, 0, 0, 0, $img_size[0], $img_size[1], $im_width, $im_height);
                imagepng($destfile_id, $img_file);
            }
            else
            {
                copy($file, $img_file);
            }

            if($thumb && $thumb_size[0] != $im_width && $thumb_size[1] != $im_height)
            {
                $sourcefile_id = @imagecreatefrompng($file);
                $destfile_id = imagecreatetruecolor($thumb_size[0], $thumb_size[1]);
                imagecopyresampled($destfile_id, $sourcefile_id, 0, 0, 0, 0, $thumb_size[0], $thumb_size[1], $im_width, $im_height);
                imagepng($destfile_id, $thumb_file);
            }
            else
            {
                copy($file, $thumb_file);
            }

            @unlink($file);
            return TRUE;
        }
        else
        {
            @unlink($file);
            return FALSE;
        }
    }
    */

    /**
     * @brief Ricalcola le dimensioni di un'immagine, dimensionando rispetto al lato lungo
     * @param int $dimension dimensione ridimensionamento
     * @param int $im_width larghezza immagine
     * @param int $im_height altezza immagine
     * @return array(larghezza, altezza)
     */
    /*
    private function dimensionFile($dimension, $im_width, $im_height){

        $width = $im_width;
        $height = $im_height;

        if(!empty($dimension) AND $im_width > $dimension)
        {
            if($im_width > $im_height AND $im_width > $dimension)
            {
                $width = $dimension;
                $height = ($im_height / $im_width) * $dimension;
            }
            elseif($im_height > $im_width AND $im_height > $dimension)
            {
                $height = $dimension;
                $width = ($im_width / $im_height) * $dimension;
            }
        }
        return array($width, $height);
    }
    */

    /**
     * @brief Ridimensiona e crea il thumbnail di una immagine già caricata
     * 
     * @param string $filename nome del file
     * @param string $directory percorso della directory del file
     * @param array $options
     *     array associativo di opzioni
     *     - @b prefix_file (string): prefisso del file
     *     - @b prefix_thumb (string): prefisso del thumbnail
     *     - @b width (integer): dimensione in pixel alla quale ridimensionare il file (larghezza)
     *     - @b thumb_width (integer): dimensione in pixel alla quale creare il thumbnail (larghezza)
     * @return risultato operazione, bool o errore
     */
    /*
    public function createImage($filename, $directory, $options=array()){

        $prefix_file = array_key_exists('prefix_file', $options) ? $options['prefix_file'] : '';
        $prefix_thumb = array_key_exists('prefix_thumb', $options) ? $options['prefix_thumb'] : '';
        $width = array_key_exists('width', $options) ? $options['width'] : 0;
        $thumb_width = array_key_exists('thumb_width', $options) ? $options['thumb_width'] : 0;

        $file = $directory.$filename;
        list($im_width, $im_height, $type) = getimagesize($file);

        if(empty($prefix_file))
        {
            $rename = $directory.'tmp_'.$filename;
            if(rename($file, $rename))
                $file = $rename;
        }

        $img_file = $directory.$prefix_file.$filename;
        $thumb_file = $directory.$prefix_thumb.$filename;

        $img_size = $this->dimensionFile($width, $im_width, $im_height);
        $thumb_size = $this->dimensionFile($thumb_width, $im_width, $im_height);

        if($type == self::_IMAGE_JPG_)
        {
            if($img_size[0] != $im_width AND $img_size[1] != $im_height)
            {
                $sourcefile_id = @imagecreatefromjpeg($file);
                $destfile_id = imagecreatetruecolor($img_size[0], $img_size[1]);
                imagecopyresampled($destfile_id, $sourcefile_id, 0, 0, 0, 0, $img_size[0], $img_size[1], $im_width, $im_height);
                imagejpeg($destfile_id, $img_file);
            }
            else copy($file, $img_file);

            if($thumb_size[0] != $im_width AND $thumb_size[1] != $im_height)
            {
                $sourcefile_id = @imagecreatefromjpeg($file);
                $destfile_id = imagecreatetruecolor($thumb_size[0], $thumb_size[1]);
                imagecopyresampled($destfile_id, $sourcefile_id, 0, 0, 0, 0, $thumb_size[0], $thumb_size[1], $im_width, $im_height);
                imagejpeg($destfile_id, $thumb_file);
            }
            else copy($file, $thumb_file);

            @unlink($file);
            return TRUE;
        }
        elseif($type == self::_IMAGE_PNG_)
        {
            if($img_size[0] != $im_width AND $img_size[1] != $im_height)
            {
                $sourcefile_id = @imagecreatefrompng($file);
                $destfile_id = imagecreatetruecolor($img_size[0], $img_size[1]);
                imagecopyresampled($destfile_id, $sourcefile_id, 0, 0, 0, 0, $img_size[0], $img_size[1], $im_width, $im_height);
                imagepng($destfile_id, $img_file);
            }
            else copy($file, $img_file);

            if($thumb_size[0] != $im_width AND $thumb_size[1] != $im_height)
            {
                $sourcefile_id = @imagecreatefrompng($file);
                $destfile_id = imagecreatetruecolor($thumb_size[0], $thumb_size[1]);
                imagecopyresampled($destfile_id, $sourcefile_id, 0, 0, 0, 0, $thumb_size[0], $thumb_size[1], $im_width, $im_height);
                imagepng($destfile_id, $thumb_file);
            }
            else copy($file, $thumb_file);

            @unlink($file);
            return TRUE;
        }
        else
        {
            @unlink($file);
            return FALSE;
        }
    }
    */

    /**
     * @brief Interfaccia che apre o chiude il form per l'inserimento e la modifica delle traduzioni
     * 
     * Viene richiamato nei metodi della classe Gino.Form: cinput(), ctextarea(), textarea()
     * 
     * @see gino-min.js
     * @param string $type tipologia di input (input, textarea, editor)
     * @param string $field nome del campo con il testo da tradurre
     * @param integer $width lunghezza del tag input o numero di colonne (textarea)
     * @param string $toolbar nome della toolbar dell'editor html
     * @return codice html interfaccia
     */
    public static function formFieldTranslation($type, $table, $field, $id_value, $width, $toolbar='') {

    	$registry = Registry::instance();
    	$multi_language = $registry->sysconf->multi_language;
    	
    	if(!$multi_language) {
    		return null;
    	}
    	
    	Loader::import('language', 'Lang');
    	
    	$buffer = '';

        $langs = \Gino\App\Language\Lang::objects(null, array(
        	'where' => "active='1' AND id != '".$registry->sysconf->dft_language."'"
        ));
        if($langs)
        {
            $first = TRUE;
            $buffer .= "<div class=\"form-trnsl\">";
            
            foreach($langs AS $lang) {
                $label = htmlChars($lang->label);
                $code = $lang->language_code.'_'.$lang->country_code;
                $buffer .= "<span class=\"trnsl-lng\" onclick=\"gino.translations.prepareTrlForm('$code', $(this), '$table', '$field', '$type', '$id_value', '$width', '$toolbar', '".$registry->request->absolute_url."&trnsl=1')\">".$label."</span> &#160;";
                
                $first = FALSE;
            }
            $buffer .= " &nbsp; <span id=\"".$table.$field."\"></span>\n";
            $buffer .= "</div>";
        }

         return $buffer;
    }
    
    /**
     * @brief Wrapper per la stampa del form
     *
     * @see self::printForm()
     * @see self::editUrl()
     * @param \Gino\Model $model_obj istanza di Gino.Model da inserire/modificare
     * @param array $opt array associativo di opzioni
     *   - @b fields (array): 
     *   - @b options_form (array): opzioni del form e del layout
     *     //- @b link_return (string): indirizzo al quale si viene rimandati dopo un esito positivo del form (se non presente viene costruito automaticamente)
     *     - @b allow_insertion (boolean)
     *     - @b edit_deny (array)
     *     - @b form_id (mixed): valore ID del form
     *     - @b session_value (string)
     *     - @b method (string): metodo del form (get/post/request); default post
     *     - @b validation (boolean); attiva il controllo di validazione tramite javascript (default true)
     *     - @b view_folder (string): percorso al file della vista
     *     - @b view_title (string): visualizza l'intestazione del form (default true)
     *     - @b form_title (string): intestazione personalizzata del form
     *     - @b form_description (string): testo che compare tra il titolo ed il form
     *   - @b options_field (array): opzioni dei campi
     * @return Gino.Http.Redirect se viene richiesta una action o si verifica un errore, form html altrimenti
     * 
     * Vengono impostate le proprietà: $_form_id, $_validation, $_method, $_session_value.
     */
    public function render($model_obj, $opt=array()) {
    	
    	$fields = gOpt('fields', $opt, array());
    	$options_form = gOpt('options_form', $opt, array());
    	$options_field = gOpt('options_field', $opt, array());
    	
    	// Opzioni di options_form
    	
    	// 1. opzioni del form
    	$allow_insertion = gOpt('allow_insertion', $options_form, true);
    	$edit_deny = gOpt('edit_deny', $options_form, array());
    	
    	$this->_form_id = gOpt('form_id', $options_form, null);
    	$this->_session_value = gOpt('session_value', $options_form, null);
    	$method = gOpt('method', $options_form, null);
    	$validation = gOpt('validation', $options_form, true);
    	
    	$this->setMethod($method);
    	$this->setValidation($validation);
    	
    	// 2. opzioni del layout
    	$view_folder = gOpt('view_folder', $options_form, null);
    	$view_title = gOpt('view_title', $options_form, null);
    	$form_title = gOpt('form_title', $options_form, null);
    	$form_description = gOpt('form_description', $options_form, null);
    	
    	$view = new View($view_folder);
    	// end
    	
    	// Default settings
    	if(!$this->_form_id) {
    		$this->_form_id = $this->setDefaultFormId($model_obj);
    	}
    	if(!$this->_session_value) {
    		$this->_session_value = $this->setDefaultSession($model_obj);
    	}
    	// end
    	
    	if($view_title)
    	{
    		if($form_title)
    		{
    			$title = $form_title;
    		}
    		else
    		{
    			// edit
    			if($model_obj->id) {
    				if($edit_deny == 'all' || in_array($model_obj->id, $edit_deny)) {
    					throw new \Gino\Exception\Exception403();
    				}
    				$title = sprintf(_("Modifica \"%s\""), htmlChars((string) $model_obj));
    			}
    			// insert
    			else {
    				if(!$allow_insertion) {
    					throw new \Gino\Exception\Exception403();
    				}
    				$title = sprintf(_("Inserimento %s"), $model_obj->getModelLabel());
    			}
    		}
    	}
    	else {
    		$title = null;
    	}
    	
    	$form = $this->printForm($model_obj, $fields, $options_form, $options_field);
    	
    	$view->setViewTpl('admin_table_form');
    	$view->assign('title', $title);
    	$view->assign('form_description', $form_description);
    	$view->assign('form', $form);
    
    	return $view->render();
    }
    
    /**
     * @brief Permessi di modifica dei campo
     * @todo Implementare il metodo che restituisce TRUE se l'utente ha il permesso di agire sul campo, FALSE altrimenti.
     * @param array $options array associativo di opzioni
     * @param string $fname nome del campo
     * @return TRUE
     */
    public function permission($options, $fname) {
    	return true;
    }
    
    /**
     * @brief Setta la proprietà $_hidden (campi hidden del form)
     * @param array $hidden array delle accoppiate nome-valore dei campi hidden non impostati automaticamente
     * @return void
     */
    public function hidden($hidden=array()) {
    	$this->_hidden = $hidden;
    }
    
    /**
     * @brief Generazione automatica del form di inserimento/modifica di un Gino.Model
     *
     * Cicla sulla struttura del modello e per ogni campo costruisce l'elemento del form.
     * 
     * Nella costruzione del form vengono impostati i seguenti parametri di default:
     * - @b formId, valore generato
     * - @b method, post
     * - @b validation, true
     * - @b session_value, valore generato
     * - @b upload, viene impostato a TRUE se l'oggetto di un campo del form appartiene almeno a una classe fileField() o imageField()
     * - @b required, l'elenco dei campi obbigatori viene costruito controllando il valore della proprietà @a $_required dell'oggetto del campo
     * - @b s_name, il nome del submit è 'submit'
     * - @b s_value, il valore del submit è 'salva'
     *
     * Le opzioni degli elementi input sono formattate nel seguente modo: nome_campo=>array(opzione=>valore[,...]) \n
     * È possibile rimuovere gli elementi input dalla struttura del form (@a removeFields) oppure selezionare gli elementi da mostrare (@a viewFields). \n
     * È inoltre possibile aggiungere degli elementi input all'interno della struttura del form indicando come chiave il nome del campo prima del quale inserire ogni elemento (@a addCell). \n
     * Il campo @a instance non viene mostrato nel form, neanche come campo nascosto.
     *
     * @param object $model oggetto del modello
     * @param array $fields elementi del form
     * @param array $options opzioni generali del form
     *   array associativo di opzioni
     *   - @b removeFields (array): elenco dei campi da non mostrare nel form
     *   - @b viewFields (array): elenco dei campi da mostrare nel form
     *   - @b addCell (array): elementi da mostrare nel form in aggiunta agli input form generati dalla struttura. \n
     *     Le chiavi dell'array sono i nomi dei campi che seguono gli elementi aggiuntivi, mentre i valori sono altri array che hanno come chiavi:
     *     - @a name, nome dell'elemento da aggiungere (nome dell'input form o altro)
     *     - @a field, codice da implementare
     *       Riassumento, la struttura di addCell è la seguente:
     *       @code
     *       array('next_field_name' => array('name' => 'name_item_add', 'field' => 'content_item_add'))
     *       @endcode
     *   // layout
     *   - @b only_inputs (boolean): mostra soltanto gli input dei campi (default false)
     *   - @b show_save_and_continue (boolean): mostra il submit "save and continue" (default true)
     *   // tag form
     *   - @b f_action (string): (default '')
     *   - @b f_upload (boolean): (di default viene impostato automaticamente)
     *   - @b f_required (string): campi obbligatori separati da virgola (di default viene impostato automaticamente)
     *   - @b f_func_confirm (string): (default '')
     *   - @b f_text_confirm (string): (default '')
     *   - @b f_generateToken (boolean): (default false)
     *   // input submit
     *   - @b s_name (string): nome dell'input submit (se non indicato viene impostato automaticamente)
     *   - @b s_value (string): valore dell'input submit (default 'salva')
     *   - @b s_classField (string): valore dell'opzione classField dell'input submit (default 'submit')
     * 
     * @param array $inputs opzioni specifiche dei campi del form (passate in Gino.Build::formElement())
     * @return form di inserimento/modifica
     */
    protected function printForm($model, $fields, $options=array(), $inputs=array()) {
    
    	//$verifyToken = array_key_exists('verifyToken', $options) ? $options['verifyToken'] : false;
    	
    	$popup = cleanVar($this->_request->GET, '_popup', 'int');

    	$this->load($this->_session_value);
    
    	// Options
    	
    	// - items
    	$removeFields = gOpt('removeFields', $options, null);
    	$viewFields = gOpt('viewFields', $options, null);
    	$addCell = array_key_exists('addCell', $options) ? $options['addCell'] : null;
    	
    	// - layout
    	$only_inputs = gOpt('only_inputs', $options, false);
    	$show_save_and_continue = gOpt('show_save_and_continue', $options, true);
    	
    	// - tag form ($f_upload e $f_required vengono definite più avanti)
    	$f_action = array_key_exists('f_action', $options) ? $options['f_action'] : '';
    	$f_func_confirm = array_key_exists('f_func_confirm', $options) ? $options['f_func_confirm'] : '';
    	$f_text_confirm = array_key_exists('f_text_confirm', $options) ? $options['f_text_confirm'] : '';
    	$f_generateToken = array_key_exists('f_generateToken', $options) ? $options['f_generateToken'] : false;
    	
    	// - input submit
    	$s_name = array_key_exists('s_name', $options) ? $options['s_name'] : 'submit_'.$this->_form_id;
    	$s_value = array_key_exists('s_value', $options) ? $options['s_value'] : _('salva');
    	$s_classField = array_key_exists('s_classField', $options) ? $options['s_classField'] : 'submit';
    	// /Options
    	
    	$structure = array();
    	$form_upload = false;
    	$form_required = array();
    
    	foreach($fields as $field=>$build)
    	{
    		if($addCell)
    		{
    			foreach($addCell AS $ref_key=>$cell)
    			{
    				if($ref_key == $field)
    				{
    					$structure[$cell['name']] = $cell['field'];
    				}
    			}
    		}
    
    		if($this->permission($options, $field) && (				//////////////////// VEDERE METODO ////////////////////
    			($removeFields && !in_array($field, $removeFields)) ||
    			($viewFields && in_array($field, $viewFields)) ||
    			(!$viewFields && !$removeFields)
    		))
    		{
    			if(isset($inputs[$field])) {
    				$options_input = $inputs[$field];
    			} else {
    				$options_input = array();
    			}
    
    			// Input form
    			$structure[$field] = $build->formElement($this, $options_input);
    			
    			if($build instanceof FileBuild || $build instanceof ImageBuild) {
    				$form_upload = true;
    			}
    
    			if($build->getRequired() == true && $build->getViewInput() == true & $build->getWidget() != 'hidden') {
    				$form_required[] = $field;
    			}
    		}
    	}
    
    	if(sizeof($form_required) > 0) {
    		$form_required = implode(',', $form_required);
    	}
    	
    	// Options (+)
    	$f_upload = array_key_exists('f_upload', $options) ? $options['f_upload'] : $form_upload;
    	$f_required = array_key_exists('f_required', $options) ? $options['f_required'] : $form_required;
    	// /Options
    	
    	$buffer = '';
    
    	if(!$only_inputs) {
    		$buffer .= $this->open($f_action, $f_upload, $f_required,
    			array(
    				'func_confirm'=>$f_func_confirm,
    				'text_confirm'=>$f_text_confirm,
    				'generateToken'=>$f_generateToken
    			)
    		);
    		$buffer .= Input::hidden('_popup', $popup);
    	}
    
    	if(sizeof($this->_hidden) > 0)
    	{
    		foreach($this->_hidden AS $key=>$value)
    		{
    			if(is_array($value))
    			{
    				$h_value = array_key_exists('value', $options) ? $options['value'] : '';
    				$h_id = array_key_exists('id', $options) ? $options['id'] : '';
    				$buffer .= Input::hidden($key, $h_value, array('id'=>$h_id));
    			}
    			else $buffer .= Input::hidden($key, $value);
    		}
    	}
    
    	$form_content = '';
    
    	if(isset($options['fieldsets'])) {
    		foreach($options['fieldsets'] as $legend => $fields) {
    			$form_content .= "<fieldset>\n";
    			$form_content .= "<legend>$legend</legend>\n";
    			foreach($fields as $field) {
    				if(isset($structure[$field])) {
    					$form_content .= $structure[$field];
    				}
    			}
    			$form_content .= "</fieldset>";
    		}
    	}
    	elseif(isset($options['ordering'])) {
    		foreach($options['ordering'] as $field) {
    			$form_content .= $structure[$field];
    		}
    	}
    	else {
    		$form_content = implode('', $structure);
    	}
    
    	$buffer .= $form_content;
    
    	if(!$only_inputs) {
    		$save_and_continue = Input::input('save_and_continue', 'submit', _('salva e continua la modifica'), array('classField' => $s_classField));
    		$buffer .= Input::input_label($s_name, 'submit', $s_value, '', array("classField"=>$s_classField, 'text_add' => ($popup or !$show_save_and_continue) ? '' : $save_and_continue));
    		$buffer .= $this->close();
    	}
    
    	return $buffer;
    }
}
