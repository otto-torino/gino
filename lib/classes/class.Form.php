<?php
/**
 * @file class.Form.php
 * @brief Contiene la definizione ed implementazione della classe Gino.Form
 *
 * @copyright 2005-2019 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

use \Gino\App\Language\language;

\Gino\Loader::import('class/exceptions', array('\Gino\Exception\Exception403'));

/**
 * @brief Classe per la creazione ed il salvataggio dati di un form
 * @description Fornisce gli strumenti per generare gli elementi del form e per gestire l'upload di file
 * 
 * @copyright 2005-2019 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 * 
 * ##Impostazione proprietà
 * La proprietà $_requestVar viene definita congiuntamente alla proprietà $_method nel metodo setMethod().
 * 
 * Nel costruttore vengono definiti i valori predefiniti delle proprietà $_form_id, $_method, $_requestVar. \n
 * Inoltre è possibile definire un valore personalizzato della proprietà $_form_id, necesariamente nel caso di action form e di verifca del token (opzione @a verifyToken). \n
 * Nel metodo open() vengono impostate le proprietà $_form_id. \n
 * Nel metodo render() vengono impostate le proprietà $_form_id, $_method, $_requestVar, $_session_value.
 * 
 * ##Opzioni sui campi nella generazione del form da un modello
 * Con le opzioni @a removeFields, @a viewFields e @a addCell è possibile intervenire sui campi da mostrare o da non mostrare nel form. \n
 * L'opzione @a removeFields permette di non mostrare nel form l'elenco dei campi definiti nell'opzione, 
 * mentre @a viewFields permette di mostrare nel form soltanto i campi definiti nell'opzione. \n
 * L'opzione @a addCell permette di mostrare nel form degli input form (o altro) in aggiunta a quelli dei campi del modello. \n
 * 
 * Il campo @a instance non viene mostrato nel form, neanche come campo nascosto.
 * 
 * ##Definizione delle proprietà (predefinite) del tag form e del submit
 * Nella costruzione del form vengono impostati i seguenti parametri di default:
 * - @b form_id, valore generato
 * - @b method, post
 * - @b session_value, valore generato
 * - @b upload, viene impostato a TRUE se l'oggetto di un campo del form appartiene almeno a una classe Gino.FileField() o Gino.ImageField()
 * - @b required, l'elenco dei campi obbigatori viene costruito controllando il valore della proprietà @a $_required dell'oggetto del campo
 * - @b s_name, il nome del submit è 'submit'
 * - @b s_value, il valore del submit è 'salva'
 * 
 * ##CAPTCHA
 * Sono previsti due meccanismi di controllo captcha: \n
 * - con le librerie reCAPTCHA di google
 * - con la classe captcha di gino
 * 
 * Le librerie reCAPTCHA vengono attivate automaticamente se sono state inserite la "site key" e la "secret key" reCaptcha nelle 'Impostazioni di sistema'.
 * Per utilizzare il servizio reCaptcha di Google occorre inoltre abilitare la funzione allow_url_fopen di PHP in quanto 
 * viene chiamato un indirizzo esterno (https://www.google.com/recaptcha/api/siteverify). Nel file php.ini:
 * @code
 * allow_url_fopen = On
 * @endcode
 */
class Form {

	protected $_registry;
	protected $_request;
	protected $_session;
	
	/**
	 * @brief Valore id del tag form
	 * @var string
	 */
	private $_form_id;
	
	/**
	 * @brief Classe del tag form
	 * @var string
	 */
	private $_form_class;
	
	/**
	 * @brief Metodo di passaggio dei dati del form
	 * @var array $_method
	 */
	private $_method;
	
	/**
	 * @brief Contenitore della variabili passate attraverso il form
	 * @var array $_requestVar
	 */
	private $_requestVar;
	
	/**
	 * @brief Nome della variabile di sessione dei dati del form
	 * @var string
	 */
	private $_session_value;
	
	/**
	 * @brief Contenitore degli input form di tipo hidden
	 * @var array
	 */
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
     *   - @b form_id (string): valore id del tag form; occorre definirla nel caso di action form e di verifca del token
     *   - @b form_class (string): classe del tag form; il valore @a form-inline costruisce un form con gli input inline
     *   - @b verifyToken (boolean): verifica il token (contro gli attacchi CSFR)
     * @throws \Exception se viene rilevato un attacco CSRF
     * @return void, istanza di Gino.Form
     */
    function __construct($options=array()){

    	$this->_registry = registry::instance();
    	$this->_request = $this->_registry->request;
    	$this->_session = Session::instance();
    	
    	$this->_multi_language = $this->_registry->sysconf->multi_language;
    	
    	// Options
    	$form_id = gOpt('form_id', $options, null);
    	$form_class = gOpt('form_class', $options, null);
    	$verify_token = gOpt('verifyToken', $options, false);
    	
    	// Set values
    	$this->setFormId($form_id);
    	$this->setFormClass($form_class);
    	$this->setMethod('POST');
    	$this->_hidden = null;
    	
    	if($verify_token) {
    		if(!$this->verifyFormToken($form_id)) {
    			throw new \Exception(_("Rilevato attacco CSRF o submit del form dall'esterno "));
    		}
    	}
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
    public function setMethod($method) {

    	$valid = array('POST', 'GET', 'REQUEST');
    	$method = strtoupper($method);
    	
    	if(!$method or ($method && !in_array($method, $valid))) {
    		$method = 'POST';
    	}
        $this->_method = $method;
        $this->_requestVar = $method == 'POST' ? $this->_request->POST : ($method=='GET' ? $this->_request->GET : $this->_request->REQUEST);

        if(is_null($this->_session->form)) $this->_session->form = array();
    }
    
    public function setFormId($form_id) {
    	 
    	$this->_form_id = (string) $form_id;
    }
    
    public function setFormClass($form_class) {
    	
    	$this->_form_class = (string) $form_class;
    }
    
    private function setDefaultFormId($model) {
    	
    	return 'form'.$model->getTable().$model->id;
    }
    
    private function setDefaultSession($model) {
    	 
    	return 'dataform'.$model->getTable().$model->id;
    }
    
    /**
     * @brief Permessi di modifica del campo
     * 
     * @param array $options array associativo di opzioni
     * @param string $fname nome del campo
     * @return TRUE
     */
    public function permission($options, $fname) {
    	return true;
    }
    
    /**
     * @brief Imposta la proprietà $_hidden (campi hidden del form)
     * @description Campi di tipo Hidden del form che non vengono impostati automaticamente attraverso il Modello
     * 
     * @param array $hidden array delle accoppiate nome-valore dei campi hidden; la struttura dell'array è la seguente:
     *   [
     *     field_key1(string) => field_value1(mixed), 
     *     field_key2(string) => field_array2['value' => field_value2(mixed), 'options' => field_options2(array)],
     *     ...
     *   ]
     * @return void
     */
    public function setHidden($hidden=[]) {
    	$this->_hidden = $hidden;
    }

    /**
     * @brief Genera un token per prevenire attacchi CSRF
     * @param string $form_id
     * @return string, token
     */
    private function generateFormToken($form_id) {
    	
    	$token = md5(uniqid(microtime(), TRUE));
    	$this->_session->{$form_id.'_token'} = $token;
    	return $token;
    }

    /**
     * @brief Verifica il token per prevenire attacchi CSRF
     * @param string $form_id
     * @return bool, risultato verifica
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
     * @description I valori vengono salvati per ripolare gli input quando si è verificato un errore.
     * L'errore deve essere necessariamente gestito attraverso Gino.Error::errorMessage() in quanto 
     * è questo metodo che crea la variabile di sessione GINOERRORMSG.
     * 
     * @code
     * return \Gino\Error::errorMessage(array('error'=>1), $controller->link(instance, method, array[]));
     * @endcode
     *
     * @param array $session_value nome della variabile di sessione nella quale sono salvati i valori degli input
     * @param boolean $clear distrugge la sessione
     * @return void
     */
    public function load($session_value, $clear = TRUE){

        $this->_session->form = array($this->_method => '');
        $form_data = array();
		
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

            if($clear) {
                unset($this->_session->$session_value);
            }
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
     * @return mixed, valore campo
     */
    public function retvar($name, $default = '') {
        
    	return (is_null($this->_session->form) or !isset($this->_session->form[$this->_method][$name])) ? $default : $this->_session->form[$this->_method][$name];
    }
    
    /**
     * @brief Salva i valori degli input form in un array
     *
     * @param string $var nome della variabile di sessione del form (@see load())
     * @return array(input_name => input_value)
     */
    public function setInputValues($sessionform) {
        
        $input_values = array();
        if(isset($this->_session->$sessionform) and is_array($this->_session->$sessionform)) {
            
            foreach ($this->_session->$sessionform as $sessionvalue) {
                
                if(is_array($sessionvalue) and count($sessionvalue) == 1) {
                    foreach ($sessionvalue as $key => $value) {
                        $input_values[$key] = $value;
                    }
                }
            }
        }
        return $input_values;
    }
    
    /**
     * @brief Recupera il valore di un input
     *
     * @param string $name nome dell'input
     * @param array $values elenco dei valori degli input (@see setInputValues())
     * @return mixed
     */
    public function getInputValue($name, $values) {
        
        if(is_array($values) and count($values) and array_key_exists($name, $values)) {
            $input_value = $values[$name];
        }
        else {
            $input_value = null;
        }
        return $input_value;
    }

    /**
     * @brief Parte inziale del form, FORM TAG, TOKEN, REQUIRED
     * @description Imposta le proprietà $_form_id
     *
     * @param string $action indirizzo dell'action
     * @param boolean $upload attiva l'upload di file
     * @param string $list_required lista di elementi obbligatori (separati da virgola)
     * @param array $options
     *   array associativo di opzioni
     *   - @b form_id (string): valore id del tag form
     *   - @b form_class (string): nome della classe del tag form; il valore @a form-inline costruisce un form con gli input inline
     *   - @b view_info (boolean): visualizzazione delle informazioni (default @a true)
     *   - @b func_confirm (string): nome della funzione js da chiamare (es. window.confirmSend())
     *   - @b text_confirm (string): testo del messaggio che compare nel box di conferma
     *   - @b generateToken (boolean): costruisce l'input hidden token (contro gli attacchi CSFR)
     * @return string, parte iniziale del form
     */
    public function open($action, $upload, $list_required, $options=array()) {

    	/*
         * Le opzioni form_id, form_class, verifyToken possono essere impostate istanziando la classe Gino.Form
         */
    	$form_id = gOpt('form_id', $options, null);
    	$form_class = gOpt('form_class', $options, null);
        $view_info = gOpt('view_info', $options, true);
        
        if($form_id) {
        	$this->setFormId($form_id);
        }
        if($form_class) {
        	$this->setFormClass($form_class);
        }
    	
    	$confirm = '';
        if(isset($options['func_confirm']) && $options['func_confirm']) {
        	$confirm = $options['func_confirm'];
        }
        if(isset($options['text_confirm']) && $options['text_confirm']) {
        	$confirm = "confirmSubmit('".$options['text_confirm']."')";
        }
        
        $buffer = "<form ".($upload?"enctype=\"multipart/form-data\"":"")." id=\"".$this->_form_id."\" name=\"".$this->_form_id."\" action=\"$action\" method=\"$this->_method\"";
        if($this->_form_class) {
        	$buffer .= " class=\"$this->_form_class\"";
        }
        if($confirm) {
            $buffer .= " onsubmit=\"return (".$confirm.")\"";
        }
        $buffer .= ">\n";

        if($list_required && $view_info) {
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
     * @return string, chiusura form
     */
    public function close(){

        return "</form>\n";
    }

    /**
     * @brief Controlla la compilazione dei campi obbligatori
     * @return int, numero campi obbligatori non compilati
     */
    public function checkRequired() {

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
     * 1. con le librerie reCAPTCHA (attivo automaticamente se sono state inserite la site key e la secret key reCaptcha nelle 'Impostazioni di sistema')
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

        $site_key = $this->_registry->sysconf->captcha_public;
        $secret_key = $this->_registry->sysconf->captcha_private;

        if($site_key && $secret_key) {
        	return $this->reCaptcha($site_key, $options);
        }
        else {
        	return $this->defaultCaptcha($options);
        }
     }

    /**
     * @brief Captcha widget attraverso la libreria reCAPTCHA
     * @description Nelle Impostazioni di sistema devono essere state inserite le chiavi pubbliche e private reCaptcha
     * 
     * @param string $site_key
     * @param array $options
     *   array associativo di opzioni
     *   - @b text_add (string)
     *   - @b form_row (boolean)
     * @return widget captcha
     */
    private function reCaptcha($site_key, $options=null) {

        $text_add = gOpt('text_add', $options, null);
        $form_row = gOpt('form_row', $options, false);
        
        $buffer = '';
        
		$captcha = "<div class=\"g-recaptcha\" data-sitekey=\"$site_key\"></div>";
		
		if($form_row) {
		    $buffer .= Input::placeholderRow('', $captcha);
		}
		else {
			$buffer .= $captcha;
			
			if($text_add) {
				$buffer .= "<div class=\"form-textadd\">".$text_add."</div>";
			}
		}
        
        return $buffer;
    }

    /**
     * @brief Captcha widget attraverso la libreria Gino.Captcha
     *
     * @see Gino.Captcha::render()
     * @param array $options
     *   array associativo di opzioni
     *   - @b text_add (string)
     * @return widget captcha
     */
    private function defaultCaptcha($options) {

        $text_add = gOpt('text_add', $options, null);
        
        $captcha = Loader::load('Captcha', array('captcha_input'));
        $captcha_code = $captcha->render();
        if($text_add) {
            $captcha_code .= "<div class=\"form-textadd\">".$text_add."</div>";
        }
        
        $captcha_label = \Gino\Input::label('captcha_input', _("Inserisci il codice dell'immagine"), true);

        $buffer = \Gino\Input::placeholderRow($captcha_label, $captcha_code);

        return $buffer;
    }

    /**
     * @brief Verifica del captcha
     * 
     * @see self::checkReCaptcha()
     * @see self::checkDefaultCaptcha()
     * @return bool or string
     */
    public function checkCaptcha($request) {

        $site_key = $this->_registry->sysconf->captcha_public;
        $secret_key = $this->_registry->sysconf->captcha_private;

        if($site_key && $secret_key) {
        	return $this->checkReCaptcha($request, $secret_key);
        }
        else {
        	return $this->checkDefaultCaptcha();
        }
    }

    /**
     * @brief Verifica captcha utilizzando la libreria reCAPTCHA
     * @description Send a POST request to ensure the token is valid.
     * 
     * @param object $request
     * @param string $secret_key
     * @return bool or string
     */
	private function checkReCaptcha($request, $secret_key) {

    	if(isset($request->POST['g-recaptcha-response']) && !empty($request->POST['g-recaptcha-response'])) {
    		//get verify response data
    		$verifyResponse = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret='.$secret_key.'&response='.$request->POST['g-recaptcha-response']);
    		$responseData = json_decode($verifyResponse);
    		if($responseData->success) {
    			return true;
    		}
    		else {
    			return _('Robot verification failed, please try again.');
    		}
    	}
    	else {
    		return _('Please click on the reCAPTCHA box.');
    	}
    }

    /**
     * @brief Verifica captcha utilizzando la libreria Gino.Captcha
     *
     * @see Gino.Captcha::check()
     * @return bool, risultato della verifica
     */
    private function checkDefaultCaptcha() {

        $captcha = Loader::load('Captcha', array('captcha_input'));
        return $captcha->check();
    }

    /**
     * @brief Interfaccia che apre o chiude il form per l'inserimento e la modifica delle traduzioni
     * 
     * @see gino-min.js
     * @param string $type tipologia di input (input, textarea, editor)
     * @param string $field nome del campo con il testo da tradurre
     * @param integer $width lunghezza del tag input o numero di colonne (textarea)
     * @param string $toolbar nome della toolbar dell'editor html
     * @return string
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
                
                $url = $registry->request->absolute_url;
                if(preg_match("#\?#", $url)) {
                    $url = $url."&trnsl=1";
                }
                else {
                    $url = $url."?trnsl=1";
                }
                
                $onclick = "gino.translations.prepareTrlForm('$code', $(this), '$table', '$field', '$type', '$id_value', '$width', '$toolbar', '".$url."')";
                $buffer .= Input::linkTranslation($label, $onclick)." &#160;";
                
                $first = FALSE;
            }
            $buffer .= " &nbsp; <span id=\"".$table.$field."\"></span>\n";
            $buffer .= "</div>";
        }

        return $buffer;
    }
    
    /**
     * @brief Wrapper per la stampa del form
     * @description Imposta le proprietà $_form_id, $_method, $_requestVar, $_session_value
     * 
     * @see self::makeInputForm()
     * @see self::editUrl()
     * @param \Gino\Model $model_obj istanza di Gino.Model da inserire/modificare
     * @param array $opt array associativo di opzioni
     *   - @b fields (array): campi da mostrare nel form
     *   - @b options_form (array): opzioni del tag form e del layout
     *     - @b opzioni sulle operazioni permesse
     *       - @b allow_insertion (boolean), default true
     *       - @b edit_deny (array)
     *       - @b edit_allow (array), default null
     *     - @b opzioni del tag form (altre opzioni vengono gestite nei metodi self::makeInputForm() e self::open())
     *       - @b form_id (string): valore id del tag form
     *       - @b form_class (string): nome della classe del tag form
     *       - @b session_value (string)
     *       - @b method (string): metodo del form (get/post/request); default post
     *     - @b opzioni del layout
     *       - @b view_folder_section (string): directory del file della vista del contenitore
     *       - @b view_file_section (string): nome del file della vista del contenitore del form (default @a section_form)
     *       - @b view_folder_form (string): directory del file della vista del form; @see makeInputForm()
     *       - @b view_file_form (string): nome del file della vista del form (default @a form); @see makeInputForm()
     *       - @b view_title (boolean): per visualizzare l'intestazione del form (default @a true)
     *       - @b form_title (string): intestazione personalizzata del form
     *       - @b form_description (string): testo che compare tra il titolo ed il form
     *   - @b options_field (array): opzioni dei campi
     * @return Gino.Http.Redirect se viene richiesta una action o si verifica un errore, form html altrimenti
     */
    public function render($model_obj, $opt=array()) {
    	
    	$fields = gOpt('fields', $opt, array());
    	$options_form = gOpt('options_form', $opt, array());
    	$options_field = gOpt('options_field', $opt, array());
    	
    	// Opzioni di options_form
    	
    	// 1. opzioni del form
    	$allow_insertion = gOpt('allow_insertion', $options_form, true);
    	$edit_deny = gOpt('edit_deny', $options_form, array());
    	$edit_allow = gOpt('edit_allow', $options_form, null);
    	
    	$form_id = gOpt('form_id', $options_form, null);
    	$form_class = gOpt('form_class', $options_form, null);
    	$method = gOpt('method', $options_form, null);
    	$this->_session_value = gOpt('session_value', $options_form, null);
    	
    	if($form_id) {
    		$this->setFormId($form_id);
    	}
    	$this->setFormClass($form_class);
    	$this->setMethod($method);
    	
    	// 2. opzioni del layout
    	$view_folder_section = gOpt('view_folder_section', $options_form, null);
    	$view_file_section = gOpt('view_file_section', $options_form, 'section_form');
    	$show_title = gOpt('view_title', $options_form, true);
    	$form_title = gOpt('form_title', $options_form, null);
    	$form_description = gOpt('form_description', $options_form, null);
    	// end
    	
    	// Default settings
    	if(!$this->_form_id) {
    		$this->setFormId($this->setDefaultFormId($model_obj));
    	}
    	if(!$this->_session_value) {
    		$this->_session_value = $this->setDefaultSession($model_obj);
    	}
    	// end
    	
    	if($show_title)
    	{
    		if($form_title)
    		{
    			$title = $form_title;
    		}
    		else
    		{
    			// edit
    			if($model_obj->id) {
    				// deny conditions
    				if((is_array($edit_allow) && !in_array($model_obj->id, $edit_allow)) ||
    					($edit_deny == 'all') ||
    					(is_array($edit_deny) && in_array($model_obj->id, $edit_deny))) {
    					throw new \Gino\Exception\Exception403();
    				}
    				elseif(!is_array($edit_allow) && !is_null($edit_allow)) {
    					throw new \Gino\Exception\Exception500();
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
    	
    	$form = $this->makeInputForm($model_obj, $fields, $options_form, $options_field);
    	
    	$view = new View($view_folder_section);
    	
    	$view->setViewTpl($view_file_section);
    	$view->assign('title', $title);
    	$view->assign('form_description', $form_description);
    	$view->assign('form', $form);
    
    	return $view->render();
    }
    
    /**
     * @brief Generazione automatica del form di inserimento/modifica di un Gino.Model
     * @description Cicla sulla struttura del modello e per ogni campo costruisce l'elemento del form.
     * 
     * @param object $model oggetto del modello
     * @param array $fields elementi del form nel formato array(field_name=>build_object)
     * @param array $options opzioni generali del form
     *   array associativo di opzioni
     *   - @b removeFields (array): elenco dei campi da non mostrare nel form
     *   - @b viewFields (array): elenco dei campi da mostrare nel form
     *   - @b addCell (array): elementi/campi aggiuntivi da mostrare nel form in aggiunta agli input form generati dalla struttura. \n
     *     Le chiavi di questo array sono i nomi dei campi prima dei quali verranno inseriti gli elementi aggiuntivi; 
     *     i valori associati a queste chiavi sono invece altri array con le chiavi @a name e @a field:
     *     - @a name, nome dell'elemento da aggiungere (nome dell'input form o altro); 
     *       un nome particolare è @a last_cell, che inserisce l'elemento alla fine del form
     *     - @a field, codice da implementare
     *       Riassumendo, la struttura di addCell è la seguente:
     *       @code
     *       array('next_field_name' => array('name' => 'name_item_add', 'field' => 'content_item_add'))
     *       @endcode
     *   - @b additional_text (string): blocco di testo da mostrare tra l'ultimo input form e il submit
     *   // layout
     *   - @b view_folder_form (string): directory del file della vista
     *   - @b view_file_form (string): nome del file della vista del form (default @a form)
     *   - @b fieldsets (array): raggruppamenti dei campi in fieldset
     *   - @b ordering (array): elenco ordinato degli input da mostrare
     *   - @b only_inputs (boolean): mostra soltanto gli input dei campi (default @a false)
     *   - @b show_save_and_continue (boolean): mostra il submit "save and continue" (default @a true)
     *   - @b view_info (boolean): visualizzazione delle informazioni (default @a true); @see self::open()
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
     *   - @b s_classField (string): valore dell'opzione classField dell'input submit (default @a submit)
     *   - @b savecontinue_name (string): nome dell'input submit "save and continue" (default @a save_and_continue)
     * 
     * @param array $inputs opzioni specifiche dei campi del form nel formato: array(field_name => array(option=>value[,...]));
     *                      queste opzioni vengono passate in Gino.Build::formElement()
     * @return form di inserimento/modifica
     */
    protected function makeInputForm($model, $fields, $options=array(), $inputs=array()) {
    
    	$popup = cleanVar($this->_request->GET, '_popup', 'int');

    	$this->load($this->_session_value);
    
    	// Options
    	
    	// - items
    	$removeFields = gOpt('removeFields', $options, null);
    	$viewFields = gOpt('viewFields', $options, null);
    	$addCell = array_key_exists('addCell', $options) ? $options['addCell'] : null;
    	$additional_text = array_key_exists('additional_text', $options) ? $options['additional_text'] : null;
    	
    	// - layout
    	$view_folder_form = gOpt('view_folder_form', $options, null);
    	$view_file_form = gOpt('view_file_form', $options, 'form');
    	$only_inputs = gOpt('only_inputs', $options, false);
    	$show_save_and_continue = gOpt('show_save_and_continue', $options, true);
    	$show_info = gOpt('view_info', $options, true);
    	
    	// - opzioni del tag form ($f_upload e $f_required vengono definite più avanti)
    	$f_action = array_key_exists('f_action', $options) ? $options['f_action'] : '';
    	$f_func_confirm = array_key_exists('f_func_confirm', $options) ? $options['f_func_confirm'] : '';
    	$f_text_confirm = array_key_exists('f_text_confirm', $options) ? $options['f_text_confirm'] : '';
    	$f_generateToken = array_key_exists('f_generateToken', $options) ? $options['f_generateToken'] : false;
    	
    	// - input submit
    	$s_name = array_key_exists('s_name', $options) ? $options['s_name'] : 'submit_'.$this->_form_id;
    	$s_value = array_key_exists('s_value', $options) ? $options['s_value'] : _('salva');
    	$s_classField = array_key_exists('s_classField', $options) ? $options['s_classField'] : 'submit';
    	$savecontinue_name = array_key_exists('savecontinue_name', $options) ? $options['savecontinue_name'] : 'save_and_continue';
    	// /Options
    	
    	$structure = array();
    	$form_upload = false;
    	$form_required = array();
        
    	foreach($fields as $field=>$build)
    	{
    		// Additional Rows
    	    if($addCell)
    		{
    			foreach($addCell AS $ref_key => $cell) {
    				if($ref_key == $field) {
    					$structure[$cell['name']] = $cell['field'];
    				}
    			}
    		}
    		// /Additional
    		
    		if($this->permission($options, $field) && (
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
    			$structure[$field] = $build->formElement($this, $options_input); // 'form_inline' => $inline_opt ???
    			
    			// Form settings
    			if($build instanceof ManyToManyThroughBuild) {
    				$m2mtf_file = $model->checkM2mtFileField($field, $model->id);
    			}
    			else {
    				$m2mtf_file = false;
    			}
    			
    			if($build instanceof FileBuild || $build instanceof ImageBuild || $m2mtf_file) {
    				$form_upload = true;
    			}
    
    			if($build->getRequired() == true && $build->getViewInput() == true & $build->getWidget() != 'hidden') {
    				$form_required[] = $field;
    			}
    			// /Form settings
    		}
    	}
    	
    	// The last additional row
    	if($addCell && array_key_exists('last_cell', $addCell)) {
    	    $structure[$addCell['last_cell']['name']] = $addCell['last_cell']['field'];
    	}
    	// /the_last_additional_row
    	
    	if(sizeof($form_required) > 0) {
    		$form_required = implode(',', $form_required);
    	}
    	
    	// Options (+)
    	$f_upload = array_key_exists('f_upload', $options) ? $options['f_upload'] : $form_upload;
    	$f_required = array_key_exists('f_required', $options) ? $options['f_required'] : $form_required;
    	// /Options
    	
    	// Declarations
    	$open_form = null;
    	$a_hidden_inputs = array();
    	$a_inputs = array();
    	$submit = null;
    	
    	if(!$only_inputs) {
    		
    		$open_form = $this->open($f_action, $f_upload, $f_required,
    			array(
    				'view_info' => $show_info,
    				'func_confirm' => $f_func_confirm,
    				'text_confirm' => $f_text_confirm,
    				'generateToken' => $f_generateToken
    			)
    		);
    		$a_hidden_inputs[] = Input::hidden('_popup', $popup);
    	}
    	
    	if(is_array($this->_hidden) and sizeof($this->_hidden) > 0)
    	{
    		foreach($this->_hidden AS $key => $value)
    		{
    			if(is_array($value)) // [value => mixed, options => array]
    			{
    				$h_value = array_key_exists('value', $value) ? $value['value'] : null;
    			    $h_options = array_key_exists('options', $value) ? $value['options'] : [];
    			    $a_hidden_inputs[] = Input::hidden($key, $h_value, $h_options);
    			}
    			else {
    				$a_hidden_inputs[] = Input::hidden($key, $value);
    			}
    		}
    	}
    	
    	$form_content = '';
    	
    	if(isset($options['fieldsets'])) {
    		foreach($options['fieldsets'] as $legend => $fields) {
    			
    			$a_fields = array();
    			
    			foreach($fields as $field) {
    				if(isset($structure[$field])) {
    					$a_fields[] = $structure[$field];
    				}
    			}
    			
    			$a_inputs[] = array('fieldset' => true, 'legend' => $legend, 'fields' => $a_fields);
    		}
    	}
    	elseif(isset($options['ordering'])) {
    		foreach($options['ordering'] as $field) {
    			$a_inputs[] = $structure[$field];
    		}
    	}
    	else {
    		$a_inputs = $structure;
    	}
    	
    	if(!$only_inputs) {
    		$submit = Input::submit($s_name, $s_value, [
    		    "classField" => $s_classField
    		]);
    		$save_and_continue = Input::submit($savecontinue_name, _('salva e continua la modifica'), [
    		    "classField" => $s_classField
    		]);
    		
    		if($popup or !$show_save_and_continue) {
    		    $submit = $submit;
    		}
    		else {
    		    $submit = $submit.' '.$save_and_continue;
    		}
    		
    		$submit = Input::placeholderRow(null, $submit);
    	}
    	
    	$view = new View($view_folder_form);
    	
    	$view->setViewTpl($view_file_form);
    	
    	$view->assign('open', $open_form);
    	$view->assign('hidden_inputs', $a_hidden_inputs);
    	$view->assign('inputs', $a_inputs);
    	$view->assign('additional_text', $additional_text);
    	$view->assign('submit', $submit);
    	
    	return $view->render();
    }
}
