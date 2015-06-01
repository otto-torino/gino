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

    const _IMAGE_GIF_ = 1;
    const _IMAGE_JPG_ = 2;
    const _IMAGE_PNG_ = 3;

    private $_registry;
    private $_request;
    private $_method, $_requestVar;
    private $_validation;
    private $_form_name;
    private $_options;

    private $_max_file_size;

    private $session, $_trd;
    private $_lng_trl, $_multi_language;
    private $_trnsl_table, $_trnsl_id;

    private $_input_size, $_input_max;
    private $_prefix_file, $_prefix_thumb;

    private $_tbl_attachment_ctg;
    private $_tbl_attachment;

    private $_extension_denied;

    private $_div_label_width, $_div_field_width;

    private $_input_field, $_textarea_field, $_fckeditor_field;

    private $_ico_calendar_path;

    /**
     * @brief Costruttore
     * 
     * @param mixed $formId valore ID del form
     * @param string $method metodo del form (get/post)
     * @param boolean $validation attiva il controllo di validazione tramite javascript
     * @param array $options
     *     array associativo di opzioni
     *     - @b trnsl_table (string): nome della tabella per le traduzioni
     *     - @b trnsl_id (integer): riferimento da passare alla tabella per le traduzioni
     *     - @b verifyToken (boolean): verifica il token (contro gli attacchi CSFR)
     *     - @b form_label_width (string): larghezza (%) della colonna con il tag label (default FORM_LABEL_WIDTH)
     *     - @b form_field_width (string): larghezza (%) della colonna con il tag input (default FORM_FIELD_WIDTH)
     * @throws Exception se viene rilevato un attacco CSRF
     * @return istanza di Gino.Form
     */
    function __construct($formId, $method, $validation, $options=null){

        $this->_registry = registry::instance();
        $this->_request = $this->_registry->request;
        $this->session = Session::instance();

        $this->_formId = $formId;
        $this->setMethod($method);
        $this->setValidation($validation);    // js:validateForm();
        $this->_trnsl_table = isset($options['trnsl_table'])?$options['trnsl_table']:null;
        $this->_trnsl_id = isset($options['trnsl_id'])?$options['trnsl_id']:null;
        if(isset($options['verifyToken']) && $options['verifyToken']) {
            if(!$this->verifyFormToken($formId)) {
                throw new \Exception(_("Rilevato attacco CSRF o submit del form dall'esterno "));
            }
        }

        $this->_max_file_size = MAX_FILE_SIZE;

        $this->_lng_trl = new language;
        $this->_multi_language = $this->_registry->sysconf->multi_language;
        $this->_trd = new translation($this->session->lng, $this->session->lngDft);

        $this->_prefix_file = 'img_';
        $this->_prefix_thumb = 'thumb_';

        $this->_tbl_attachment_ctg = "attachment_ctg";
        $this->_tbl_attachment = "attachment";

        $this->_input_field = 'input';
        $this->_textarea_field = 'textarea';
        $this->_editor_field = 'editor';

        $this->_extension_denied = array(
        	'php', 'phps', 'js', 'py', 'asp', 'rb', 'cgi', 'cmd', 'sh', 'exe', 'bin'
        );

        $this->_ico_calendar_path = SITE_IMG."/ico_calendar.png";
    }

    /**
     * @brief Imposta le opzioni
     * @param array $options
     * @return void
     */
    private function setOptions($options) {
        $this->_options = $options;
    }

    /**
     * @brief Valore di un'opzione
     * @param string $opt opzione da recuperare
     * @return valore opzione
     */
    private function option($opt) {

        if($opt=='trnsl_id') return isset($this->_options['trnsl_id']) ? $this->_options['trnsl_id'] : $this->_trnsl_id;
        if($opt=='trnsl_table') return isset($this->_options['trnsl_table']) ? $this->_options['trnsl_table'] : $this->_trnsl_table;

        return isset($this->_options[$opt]) ? $this->_options[$opt] : null;
    }

    /**
     * @brief Setter della proprietà method
     * @param string $method 'post' o 'get'
     * @return void
     */
    private function setMethod($method){

        $this->_method = $method;
        $this->_requestVar = $method == 'post' ? $this->_request->POST : ($method=='get' ? $this->_request->GET : $this->_request->REQUEST);

        if(is_null($this->session->form)) $this->session->form = array();
    }

    /**
     * @brief Setter della proprietà validation (eseguire o meno la validazione del form)
     * @param bool $validation
     * @return void
     */
    private function setValidation($validation){

        $this->_validation = (bool) $validation;
    }

    /**
     * @brief Genera un token per prevenire attacchi CSRF
     * @param string $form_name
     * @return token
     */
    private function generateFormToken($form_name) {
            $token = md5(uniqid(microtime(), TRUE));
            $this->session->{$form_name.'_token'} = $token;
            return $token;
    }

    /**
     * @brief Verifica il token per prevenire attacchi CSRF
     * @param string $form_name
     * @return risultato verifica, bool
     */
    private function verifyFormToken($form_name) {
        $index = $form_name.'_token';
        // There must be a token in the session
        if(!isset($this->session->$index)) return FALSE;
        // There must be a token in the form
        if(!isset($this->_requestVar['token'])) return FALSE;
        // The token must be identical
        if($this->session->$index !== $this->_requestVar['token']) return FALSE;

        return TRUE;
    }

    /**
     * @brief Recupera i dati dalla sessione del form
     *
     * @description Permette di mostrare i campi correttamente compilati a seguito di errore
     *
     * @param array $session_value nome della variabile di sessione nella quale sono salvati i valori degli input
     * @param boolean $clear distrugge la sessione
     * @return global
     */
    public function load($session_value, $clear = TRUE){

        $this->session->form = array($this->_method => '');
        $form_data = array();
		
		//$this->session->form[$this->_method] = array();	// @todo implementare a partire dalla versione 5.4
		
		if(isset($this->session->$session_value))
        {
            if(isset($this->session->GINOERRORMSG) AND !empty($this->session->GINOERRORMSG))
            {
                for($a=0, $b=count($this->session->$session_value); $a < $b; $a++)
                {
                    foreach($this->session->{$session_value}[$a] as $key => $value)
                    {
                        $form_data[$key] = $value;
                    }
                }
                $this->session->form = array($this->_method => $form_data);
            }

            if($clear) unset($this->session->$session_value);
        }

    }

    /**
     * @brief Salva i valori dei campi del form in una variabile di sessione
     * 
     * @param string $session_value nome della variabile di sessione, come definito nel metodo load()
     * @return void
     */
    public function save($session_value){

        $this->session->{$session_value} = array();
        $session_prop = $this->session->{$session_value};
        foreach($this->_requestVar as $key => $value)
            array_push($session_prop, array($key => $value));

        $this->session->$session_value = $session_prop;

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
    public function retvar($name, $default = ''){
        return (is_null($this->session->form) or !isset($this->session->form[$this->_method][$name])) ? $default : $this->session->form[$this->_method][$name];
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
     *     array associativo di opzioni
     *     - @b func_confirm (string): nome della funzione js da chiamare (es. window.confirmSend())
     *     - @b text_confirm (string): testo del messaggio che compare nel box di conferma
     *     - @b generateToken (boolean): costruisce l'input hidden token (contro gli attacchi CSFR)
     * @return parte iniziale del form, html
     */
    public function open($action, $upload, $list_required, $options=null){

        $GFORM = '';

        $confirm = '';
        if(isset($options['func_confirm']) && $options['func_confirm'])
            $confirm = " && ".$options['func_confirm'];
        if(isset($options['text_confirm']) && $options['text_confirm'])
            $confirm = " && confirmSubmit('".$options['text_confirm']."')";

        $GFORM .= "<form ".($upload?"enctype=\"multipart/form-data\"":"")." id=\"".$this->_formId."\" name=\"".$this->_formId."\" action=\"$action\" method=\"$this->_method\"";
        if($this->_validation) $GFORM .= " onsubmit=\"return (gino.validateForm($(this))".$confirm.")\"";
        $GFORM .= ">\n";

        if($list_required) {
            $GFORM .= "<p class=\"form-info\">"._("I campi in grassetto sono obbligatori.")."</p>";
        }

        if(isset($options['generateToken']) && $options['generateToken']) 
            $GFORM .= $this->hidden('token', $this->generateFormToken($this->_formId));
        if(!empty($list_required)) $GFORM .= $this->hidden('required', $list_required);

        return $GFORM;
    }

    /**
     * @brief Chiusura form, /FORM TAG
     * @return chiusura form, html
     */
    public function close(){

        $GFORM = "</form>\n";

        return $GFORM;
    }

    /**
     * @brief Inizializza l'editor visuale CKEditor
     * 
     * @param string $name
     * @param string $value
     * @param array $options
     *   - @b toolbar (string): nome della toolbar
     *   - @b width (string): larghezza dell'editor (pixel o %)
     *   - @b height (integer): altezza dell'editor (pixel)
     * @return string, script js
     */
    public function editorHtml($name, $value, $options=null){

    	$toolbar = gOpt('toolbar', $options, null);
    	$width = gOpt('width', $options, '100%');
    	$height = gOpt('height', $options, 300);
    	
    	$height .= 'px';
    	
    	if(empty($value)) $value = '';
    	if(!$toolbar) $toolbar = 'Full';

        $this->_registry->addCustomJs(SITE_WWW.'/ckeditor/ckeditor.js', array('compress'=>false, 'minify'=>false));
        
        // Replace the textarea id $name
        $buffer = "<script>
        CKEDITOR.replace('$name', {
        	customConfig: '".SITE_CUSTOM_CKEDITOR."/config.js',
        	contentsCss: '".SITE_CUSTOM_CKEDITOR."/stylesheet.css', 
        	toolbar: '$toolbar', 
        	width: '$width',
        	height: '$height',
        });
        ";
        
        if($toolbar == 'Basic')
        {
        	$buffer .= "
        	CKEDITOR.replace('$name', {
				toolbarGroups: [
				{ name: 'document',	   groups: [ 'mode', 'document' ] },
 				{ name: 'clipboard',   groups: [ 'clipboard', 'undo' ] },
 				'/',
 				{ name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
 				{ name: 'links' }
				]
        	});";
        }
        
        $buffer .= "</script>";
        
        return $buffer;
    }

    /**
     * @brief TAG LABEL
     *
     * @param string $name nome dell'etichetta
     * @param mixed $text testo dell'etichetta, testo o array (array-> array('label'=>_("..."), 'description'=>_("...")))
     * @param boolean $required campo obbligatorio
     * @return label tag, html
     */
    public function label($name, $text, $required){

        if(!$text) return '<label></label>';

        if(is_array($text)) {
            $label = isset($text['label']) ? $text['label'] : $text[0];
        }
        else {
            $label = $text;
        }

        $GFORM = "<label for=\"$name\"".($required ? "class=\"req\"":"").">";
        $GFORM .= $label;
        $GFORM .= "</label>";

        return $GFORM;
    }

    /**
     * @brief Controlla la compilazione dei campi obbligatori
     * @return numero campi obbligatori non compilati
     */
    public function arequired(){

        $required = isset($this->_requestVar['required']) ? cleanVar($this->_requestVar, 'required', 'string', '') : '';
        $error = 0;

        if(!empty($required))
            foreach(explode(",", $required) as $fieldname)
                if((!isset($this->_requestVar[$fieldname]) or $this->_requestVar[$fieldname] == '') and (!isset($this->_request->FILES[$fieldname]) or $this->_request->FILES[$fieldname] == '')) $error++;
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
     *     array associativo di opzioni
     *     - @b classLabel (string): valore CLASS del tag SPAN in <label>
     *     - @b text_add (string): testo che segue il controllo
     * @return widget captcha
     */
    public function captcha($options=null) {

        $public_key = $this->_registry->sysconf->captcha_public;
        $private_key = $this->_registry->sysconf->captcha_private;

        if($public_key && $private_key) return $this->reCaptcha($public_key, $options);
        else return $this->defaultCaptcha($options);
     }

    /**
     * @brief Captcha widget attraverso la libreria RECAPTCHA
     * 
     * Nelle Impostazioni di sistema devono essere state inserite le chiavi pubbliche e private reCaptcha
     * 
     * @param string $public_key
     * @param array $options
     *     array associativo di opzioni
     * @return widget captcha
     */
    private function reCaptcha($public_key, $options=null) {

        $options["required"] = TRUE;
        $this->setOptions($options);
        $GFORM .= $this->label('captcha_input', _("Inserisci il codice di controllo"), $this->option('required'))."\n";
        $GFORM .= "<div id=\"".$this->_formId."_recaptcha\"></div>";
        $GFORM .= "<script>
            function createCaptcha() {
                if(\$chk($('".$this->_formId."_recaptcha'))) {
                    Recaptcha.create('$public_key', '".$this->_formId."_recaptcha', {theme: 'red', callback: Recaptcha.focus_response_field});
                    clearInterval(window.captcha_int);
                }
            }
            window.captcha_int = setInterval(createCaptcha, 50);
        </script>";
        if($this->option('text_add')) $GFORM .= "<div class=\"form-textadd\">".$this->option('text_add')."</div>";
        return $GFORM;
    }

    /**
     * @brief Captcha widget attraverso la libreria di gino
     *
     * @see Gino.Captcha::render()
     * @param array $options
     *     array associativo di opzioni
     * @return widget captcha
     */
    private function defaultCaptcha($options) {

        $options["required"] = TRUE;
        $options["id"] = "captcha_input";
        $options["size"] = "20";
        $options["maxlength"] = "20";
        $this->setOptions($options);

        $captcha = Loader::load('Captcha', array('captcha_input'));

        $GFORM = $this->label('captcha_input', _("Inserisci il codice dell'immagine"), $this->option('required'), $this->option('classLabel'))."\n";
        $GFORM .= $captcha->render();
        if($this->option('text_add')) $GFORM .= "<div class=\"form-textadd\">".$this->option('text_add')."</div>";

        return $GFORM;
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
     * @brief Simula un campo ma senza input
     *
     * @param string $label contenuto della prima colonna
     *     - string
     *     - array, ad esempio array(_("etichetta"), _("spiegazione"))
     * @param string $value contenuto della seconda colonna
     * @param array $options
     *     array associativo di opzioni
     *     - @b id (string): ID del tag TD della label
     *     - @b style (string): stile del tag TR
     *     - @b other (string): altro nel tag TD della label
     *     - @b class_label (string): classe del tag TD della label
     *     - @b class (string): classe dello span della label
     * @return codice html riga del form
     */
    public function noinput($label, $value, $options=null) {

        $this->setOptions($options);

        $id = $this->option('id') ? "id=\"".$this->option('id')."\"" : '';
        $style = $this->option('style') ? "style=\"".$this->option('style')."\"" : '';
        $other = $this->option('other') ? $this->option('other') : '';
        $class_label = $this->option('class_label') ? $this->option('class_label') : 'form_label';
        $class = $this->option('class') ? $this->option('class') : 'form_text_label';

        $GFORM = '';
        if(!empty($label) OR !empty($value))
        {
            $GFORM = "<div class=\"form-row\">";
            $GFORM .= $this->label('', $label, FALSE);
            $GFORM .= "<div class=\"form-noinput\">$value</div>\n";
            $GFORM .= "</div>";
        }

        return $GFORM;
    }

    /**
     * @brief Input hidden
     *
     * @param string $name nome del tag
     * @param mixed $value valore del tag
     * @param array $options
     *     array associativo di opzioni
     *     - @b id (string): valore ID del tag
     * @return widget html
     */
    public function hidden($name, $value, $options=null) {

        $GFORM = '';
        $this->setOptions($options);
        $GFORM .= "<input type=\"hidden\" name=\"$name\" value=\"$value\" ".($this->option("id")?"id=\"{$this->option("id")}\"":"")."/>";

        return $GFORM;
    }

    /**
     * @brief Input tag
     * 
     * @param string $name nome input
     * @param string $type valore della proprietà @a type (text)
     * @param string $value valore attivo
     * @param array $options
     *     array associativo di opzioni
     *     - @b id (string): valore ID del tag
     *     - @b pattern (string): espressione regolare che verifica il valore dell'elemento input
     *     - @b hint (string): placeholder
     *     - @b size (integer): lunghezza del tag
     *     - @b maxlength (integer): numero massimo di caratteri consentito
     *     - @b classField (string): nome della classe del tag
     *     - @b js (string): javascript
     *     - @b readonly (boolean): campo di sola lettura
     *     - @b other (string): altro nel tag
     * @return widget html
     */
    public function input($name, $type, $value, $options=null){

        $this->setOptions($options);
        $GFORM = "<input type=\"$type\" name=\"$name\" value=\"$value\" ";

        $GFORM .= $this->option('id')?"id=\"{$this->option('id')}\" ":"";
        $GFORM .= $this->option('required')?"required ":"";
        $GFORM .= $this->option('pattern')?"pattern=\"{$this->option('pattern')}\" ":"";
        $GFORM .= $this->option('hint')?"placeholder=\"{$this->option('hint')}\" ":"";
        $GFORM .= $this->option('classField')?"class=\"{$this->option('classField')}\" ":"";
        $GFORM .= $this->option('size')?"size=\"{$this->option('size')}\" ":"";
        $GFORM .= $this->option('maxlength')?"maxlength=\"{$this->option('maxlength')}\" ":"";
        $GFORM .= $this->option('readonly')?"readonly=\"readonly\" ":"";
        $GFORM .= $this->option('js')?$this->option('js')." ":"";
        $GFORM .= $this->option('other')?$this->option('other')." ":"";

        $GFORM .= "/>";

        if(isset($options['helptext'])) {
            $title = $options['helptext']['title'];
            $text = $options['helptext']['text'];
            $GFORM .= " <span class=\"fa fa-question-circle label-tooltipfull\" title=\"".attributeVar($title.'::'.$text)."\"></span>";
        }

        return $GFORM;
    }

    /**
     * @brief Input con label
     * 
     * @see self::label()
     * @see self::formFieldTranslation()
     * @param string $name nome input
     * @param string $type valore della proprietà @a type (text)
     * @param string $value valore attivo
     * @param mixed $label testo <label>
     * @param array $options
     *     array associativo di opzioni (aggiungere quelle del metodo input())
     *     - @b required (boolean): campo obbligatorio
     *     - @b classLabel (string): valore CLASS del tag SPAN in <label>
     *     - @b trnsl (boolean): attiva la traduzione
     *     - @b trnsl_table (string): nome della tabella con il campo da tradurre
     *     - @b trnsl_id (integer): valore dell'ID del record di riferimento per la traduzione
     *     - @b field (string): nome del campo con il testo da tradurre
     *     - @b size (integer): lunghezza del tag
     *     - @b text_add (string): testo dopo il tag input
     * @return codice html riga form, label + input
     */
    public function cinput($name, $type, $value, $label, $options){

        $this->setOptions($options);
        $GFORM = "<div class=\"form-row\">";
        $GFORM .= $this->label($name, $label, $this->option('required'), $this->option('classLabel'))."\n";
        if(is_array($label)) {
            $options['helptext'] = array(
                'title' => isset($label['label']) ? $label['label'] : $label[0],
                'text' => isset($label['description']) ? $label['description'] : $label[1]
            );
        }
        $GFORM .= $this->input($name, $type, $value, $options);
        if($this->option('trnsl') AND $this->_multi_language) {
            if($this->option('trnsl_id'))
                $GFORM .= "<div class=\"form-trnsl\">".$this->formFieldTranslation($this->_input_field, $this->option('trnsl_table'), $this->option('field'), $this->option('trnsl_id'), $this->option('size'), '')."</div>";
        }

        if($this->option('text_add')) $GFORM .= "<div class=\"form-textadd\">".$this->option('text_add')."</div>";
        $GFORM .= "</div>";

        return $GFORM;
    }

    /**
     * @brief Input di tipo data con label
     * 
     * @see self::label()
     * @see self::input()
     * @param string $name nome input
     * @param string $value valore attivo
     * @param mixed $label testo <label>
     * @param array $options
     *     array associativo di opzioni (aggiungere quelle del metodo input())
     *     - @b required (boolean): campo obbligatorio
     *     - @b classLabel (string): valore CLASS del tag SPAN in <label>
     *     - @b inputClickEvent (boolean): per attivare l'evento sulla casella di testo
     *     - @b text_add (string): testo dopo il tag input
     * @return codice html riga form, input + label
     */
    public function cinput_date($name, $value, $label, $options){

        $this->setOptions($options);
        if($this->option('inputClickEvent')) $options['other'] = "onclick=\"gino.printCalendar($(this).getNext('img'), $(this))\"";
        $options['id'] = $name;
        $options['size'] = 10;
        $options['maxlength'] = 10;
        $options['pattern'] = "^\d\d/\d\d/\d\d\d\d$";
        $options['hint'] = _("dd/mm/yyyy");

        $GFORM = "<div class=\"form-row\">";
        $GFORM .= $this->label($name, $label, $this->option('required'), $this->option('classLabel'));
        if(is_array($label)) {
            $options['helptext'] = array(
                'title' => isset($label['label']) ? $label['label'] : $label[0],
                'text' => isset($label['description']) ? $label['description'] : $label[1]
            );
        }
        $GFORM .= $this->input($name, 'text', $value, $options);
        $days = "['"._("Domenica")."', '"._("Lunedì")."', '"._("Martedì")."', '"._("Mercoledì")."', '"._("Giovedì")."', '"._("Venerdì")."', '"._("Sabato")."']";
        $months = "['"._("Gennaio")."', '"._("Febbraio")."', '"._("Marzo")."', '"._("Aprile")."', '"._("Maggio")."', '"._("Giugno")."', '"._("Luglio")."', '"._("Agosto")."', '"._("Settembre")."', '"._("Ottobre")."', '"._("Novembre")."', '"._("Dicembre")."']";

        $GFORM .= "<span style=\"margin-left:5px;margin-bottom:2px;cursor:pointer;\" class=\"fa fa-calendar calendar-tooltip\" title=\""._("calendario")."\" id=\"cal_button_$name\" src=\"".$this->_ico_calendar_path."\" onclick=\"gino.printCalendar($(this), $(this).getPrevious('input'), $days, $months)\"></span>";
        if($this->option('text_add')) $GFORM .= "<div class=\"form-textadd\">".$this->option('text_add')."</div>";
        $GFORM .= "</div>";

        return $GFORM;
    }

    /**
     * @brief Tectarea con label
     *
     * @see self::label()
     * @see self::formFieldTranslation()
     * @param string $name nome input
     * @param string $value valore attivo
     * @param string $label testo del tag label
     * @param array $options
     *     array associativo di opzioni (aggiungere quelle del metodo textarea())
     *     - @b classLabel (string): nome della classe del tag span nel tag label
     *     - @b text_add (string): testo aggiuntivo stampato sotto il box
     *     - @b trnsl (boolean): attiva la traduzione
     *     - @b trsnl_id (integer): valore dell'ID del record di riferimento per la traduzione
     *     - @b trsnl_table (string): nome della tabella con il campo da tradurre
     *     - @b field (string): nome del campo da tradurre
     *     - @b cols (integer): numero di colonne
     * @return codice htlm riga form, textarea + label
     */
    public function ctextarea($name, $value, $label, $options=null){

        $this->setOptions($options);
        $GFORM = "<div class=\"form-row\">";
        $GFORM .= $this->label($name, $label, $this->option('required'), $this->option('classLabel'))."\n";

        if(is_array($label)) {
            $options['helptext'] = array(
                'title' => isset($label['label']) ? $label['label'] : $label[0],
                'text' => isset($label['description']) ? $label['description'] : $label[1]
            );
        }
        $GFORM .= $this->textarea($name, $value, $options);
        if($this->option('trnsl') AND $this->_multi_language) {
            if($this->option('trnsl_id'))
                $GFORM .= "<div class=\"form-trnsl\">".$this->formFieldTranslation($this->_textarea_field, $this->option('trnsl_table'), $this->option('field'), $this->option('trnsl_id'), $this->option('cols'), '')."</div>";
        }

        if($this->option('text_add')) $GFORM .= "<div class=\"form-textadd\">".$this->option('text_add')."</div>";
        $GFORM .= "</div>";

        return $GFORM;
    }

    /**
     * @brief Textarea
     *
     * @see imagePreviewer()
     * @see editorHtml()
     * @param string $name nome input
     * @param string $value valore attivo
     * @param array $options array associativo di opzioni
     *     opzioni del textarea
     *     - @b id (string): valore della proprietà id del tag
     *     - @b required (boolean): campo obbligatorio
     *     - @b classField (string): nome della classe del tag
     *     - @b rows (integer): numero di righe
     *     - @b cols (integer): numero di colonne
     *     - @b readonly (boolean): campo di sola lettura
     *     - @b js (string): javascript
     *     - @b other (string): altro nel tag
     *     - @b maxlength (integer): numero massimo di caratteri consentiti \n
     *     - @b helptext (array)
     *       - @a title
     *       - @a text
     *     opzioni del tag label
     *     - @b classLabel (string): nome della classe del tag label \n
     *     opzioni dell'editor html
     *     - @b label (string): label
     *     - @b ckeditor (boolean): attiva l'editor html
     *     - @b ckeditor_toolbar (string): nome della toolbar dell'editor html
     *     - @b ckeditor_container (boolean): racchiude l'input editor in un contenitore div
     *     - @b width (string): larghezza dell'editor (pixel o %)
     *     - @b height (integer): altezza dell'editor (pixel)
     *     - @b notes (boolean): mostra le note
     *     - @b img_preview (boolean): mostra il browser di immagini di sistema
     *     - @b text_add (boolean): testo aggiuntivo
     *     - @b trnsl (boolean): attiva la traduzione
     *     - @b trnsl_table (string): nome della tabella con il campo da tradurre
     *     - @b trnsl_id (integer): valore dell'ID del record di riferimento per la traduzione
     *     - @b field (string): nome del campo con il testo da tradurre
     * @return string, codice html
     */
    public function textarea($name, $value, $options){

        $ckeditor = gOpt('ckeditor', $options, false);
        $id = gOpt('id', $options, null);
        
        if($ckeditor && !$id) $id = $name;
        
    	$this->setOptions($options);
    	
    	$buffer = '';
    	
        $textarea = "<textarea name=\"$name\" ";
        $textarea .= $id ? "id=\"$id\" " : "";
        $textarea .= $this->option('required') ? "required=\"required\" ":"";
        $textarea .= $this->option('classField')?"class=\"{$this->option('classField')}\" ":"";
        $textarea .= $this->option('rows')?"rows=\"{$this->option('rows')}\" ":"";
        $textarea .= $this->option('cols')?"cols=\"{$this->option('cols')}\" ":"";
        $textarea .= $this->option('readonly')?"readonly=\"readonly\" ":"";
        $textarea .= $this->option('js')?$this->option('js')." ":"";
        $textarea .= $this->option('other')?$this->option('other')." ":"";
        $textarea .= ">";
        $textarea .= "$value</textarea>";
        
        if($ckeditor)
        {
        	$label = gOpt('label', $options, null);
        	$notes = gOpt('notes', $options, false);
        	$ckeditor_toolbar = gOpt('ckeditor_toolbar', $options, null);
        	$ckeditor_container = gOpt('ckeditor_container', $options, true);
        	$width = gOpt('width', $options, null);
        	$height = gOpt('height', $options, null);
        	
        	if($ckeditor_container)
        	{
        		$text_note = '';
        		if($notes) {
        			$text_note .= "[Enter] "._("inserisce un &lt;p&gt;");
        			$text_note .= " - [Shift+Enter] "._("inserisce un &lt;br&gt;");
        		}
        		
        		$buffer .= "<div class=\"form-row\">";
        		$buffer .= "<div class=\"form-ckeditor\">\n";
        		$buffer .= $this->label($name, $label, $this->option('required'), $this->option('classLabel'));
        		if($text_note) $buffer .= "<div>".$text_note."</div>";
        		if($this->option('img_preview')) $buffer .= $this->imagePreviewer();
        	}
        	
        	$buffer .= $textarea;
        	
        	$buffer .= $this->editorHtml($name, $value, array('toolbar'=>$ckeditor_toolbar, 'width'=>$width, 'height'=>$height));
        	
        	if($ckeditor_container)
        	{
        		if($this->option('trnsl') AND $this->_multi_language) {
        			if($this->option('trnsl_id'))
        				$buffer .= "<div class=\"form-trnsl\">".$this->formFieldTranslation(
        					$this->_editor_field, 
        					$this->option('trnsl_table'), 
        					$this->option('field'), 
        					$this->option('trnsl_id'), 
        					$width, 
        					$ckeditor_toolbar
        				)."</div>";
        		}
        		
        		if($this->option('text_add')) $buffer .= "<div class=\"form-textadd\">".$this->option('text_add')."</div>";
        		$buffer .= "</div>\n";
        		$buffer .= "</div>\n";
        	}
        }
        else 
        {
        	$buffer .= $textarea;
        	
        	if(isset($options['helptext'])) {
            	$title = $options['helptext']['title'];
           		$text = $options['helptext']['text'];
            	$buffer .= " <span class=\"fa fa-question-circle label-tooltipfull\" title=\"".attributeVar($title.'::'.$text)."\"></span>";
        	}

        	if($this->option('maxlength') AND $this->option('maxlength') > 0)
        	{
            	// Limite caratteri con visualizzazione del numero di quelli restanti
            	$buffer .= $this->jsCountCharText();
            	$buffer .= "<script type=\"text/javascript\" language=\"javascript\">initCounter($$('#$this->_formId textarea[name=$name]')[0], {$this->option('maxlength')})</script>";
        	}
        }
        
        return $buffer;
    }

    /**
     * @brief Codice per la visualizzazione allegati contestualmente all'editor CKEDITOR
     * @see Gino.App.Attachment.attachment::editorList()
     * @return codice html
     */
    private function imagePreviewer() {

        $onclick = "if(typeof window.att_win == 'undefined' || !window.att_win.showing) {
            window.att_win = new gino.layerWindow({
            'title': '"._('Allegati')."',
            'width': 1000,
            'overlay': false,
            'maxHeight': 600,
            'url': '".HOME_FILE."?evt[attachment-editorList]'
            });
            window.att_win.display();
        }";
        $GFORM = "<p><span class=\"link\" onclick=\"$onclick\">"._("Visualizza file disponibili in allegati")."</span></p>";

        return $GFORM;
    }

    /**
     * @brief Input radio con label
     * 
     * @see self::label()
     * @param string $name nome input
     * @param string $value valore attivo
     * @param array $data elementi dei pulsanti radio (array(value=>text[,]))
     * @param mixed $default valore di default
     * @param mixed $label testo <label>
     * @param array $options
     *     array associativo di opzioni (aggiungere quelle del metodo radio())
     *     - @b required (boolean): campo obbligatorio
     *     - @b classLabel (string): valore CLASS del tag SPAN in <label>
     *     - @b text_add (boolean): testo aggiuntivo stampato sotto il box
     * @return codice html riga form, input radio + label
     */
    public function cradio($name, $value, $data, $default, $label, $options=null){
        $this->setOptions($options);
        $GFORM = "<div class=\"form-row\">";
        $GFORM .= $this->label($name, $label, $this->option('required'))."\n";
        if(is_array($label)) {
            $options['helptext'] = array(
                'title' => isset($label['label']) ? $label['label'] : $label[0],
                'text' => isset($label['description']) ? $label['description'] : $label[1]
            );
        }
        $GFORM .= $this->radio($name, $value, $data, $default, $options);
        if($this->option('text_add')) $GFORM .= "<div class=\"form-textadd\">".$this->option('text_add')."</div>";
        $GFORM .= "</div>\n";
        return $GFORM;
    }

    /**
     * @brief Input radio
     * 
     * @param string $name nome input
     * @param string $value valore attivo
     * @param array $data elementi dei pulsanti radio (array(value=>text[,]))
     * @param mixed $default valore di default
     * @param array $options
     *     array associativo di opzioni
     *     - @b aspect (string): col valore 'v' gli elementi vengono messi uno sotto l'altro
     *     - @b id (string): valore ID del tag <input>
     *     - @b classField (string): valore CLASS del tag <input>
     *     - @b js (string): javascript
     *     - @b other (string): altro nel tag
     * @return widget html
     */
    public function radio($name, $value, $data, $default, $options){

        $this->setOptions($options);
        $GFORM = '';
        $comparison = is_null($value)? $default:$value;
        $space = $this->option('aspect')=='v'? "<br />":"&nbsp;";
        $container = $this->option('aspect')=='v'? TRUE : FALSE;

        if($container) {
            $GFORM .= "<div class=\"form-radio-group\">";
        }
        if(is_array($data)) {
            $i=0;
            foreach($data AS $k => $v) {
                $GFORM .= ($i?$space:'')."<input type=\"radio\" name=\"$name\" value=\"$k\" ".(!is_null($comparison) && $comparison==$k?"checked=\"checked\"":"")." ";
                $GFORM .= $this->option('id')?"id=\"{$this->option('id')}\" ":"";
                $GFORM .= $this->option('classField')?"class=\"{$this->option('classField')}\" ":"";
                $GFORM .= $this->option('js')?$this->option('js')." ":"";
                $GFORM .= $this->option('other')?$this->option('other')." ":"";
                $GFORM .= "/> ".$v;
                $i++;
            }
        }
        if(isset($options['helptext'])) {
            $title = $options['helptext']['title'];
            $text = $options['helptext']['text'];
            $GFORM .= " <span class=\"fa fa-question-circle label-tooltipfull\" title=\"".attributeVar($title.'::'.$text)."\"></span>";
        }
        if($container) {
            $GFORM .= "</div>";
        }

        return $GFORM;
    }

    /**
     * @brief Input checkbox con label
     *
     * Esempio
     * @code
     * $buffer = $gform->ccheckbox('public', $value=='yes'?TRUE:FALSE, 'yes', _("Pubblico"));
     * @endcode
     *
     * @see self::label()
     * @see self::checkbox()
     * @param string $name nome input
     * @param boolean $checked valore selezionato
     * @param mixed $value valore del tag input
     * @param string $label testo <label>
     * @param array $options
     *     array associativo di opzioni (aggiungere quelle del metodo checkbox())
     *     - @b required (boolean): campo obbligatorio
     *     - @b classLabel (string): valore CLASS del tag SPAN in <label>
     *     - @b text_add (string): testo da aggiungere dopo il checkbox
     * @return codice html riga form, input + label
     */
    public function ccheckbox($name, $checked, $value, $label, $options=null){

        $this->setOptions($options);
        $GFORM = "<div class=\"form-row\">";
        $GFORM .= $this->label($name, $label, $this->option('required'), $this->option('classLabel'))."\n";
        $GFORM .= $this->checkbox($name, $checked, $value, $options);
        if($this->option('text_add')) $GFORM .= "<div class=\"form-textadd\">".$this->option('text_add')."</div>";
        $GFORM .= "</div>\n";
        return $GFORM;
    }

    /**
     * @brief Input checkbox
     *
     * @param string $name nome input
     * @param boolean $checked valore selezionato
     * @param mixed    $value valore del tag input
     * @param array $options
     *     array associativo di opzioni
     *     - @b id (string): valore ID del tag input
     *     - @b classField (string): nome della classe del tag input
     *     - @b js (string): javascript
     *     - @b other (string): altro nel tag
     * @return widget html
     */
    public function checkbox($name, $checked, $value, $options){

        $this->setOptions($options);
        $GFORM = '';

        $GFORM .= "<input type=\"checkbox\" name=\"$name\" value=\"$value\" ".($checked?"checked=\"checked\"":"")." ";
        $GFORM .= $this->option('id')?"id=\"{$this->option('id')}\" ":"";
        $GFORM .= $this->option('classField')?"class=\"{$this->option('classField')}\" ":"";
        $GFORM .= $this->option('js')?$this->option('js')." ":"";
        $GFORM .= $this->option('other')?$this->option('other')." ":"";
        $GFORM .= "/>";

        return $GFORM;
    }

    /**
     * @brief Iput checkbox multiplo (many to many) con label
     * 
     * @param string $name nome input
     * @param array $checked valori degli elementi selezionati
     * @param mixed $data
     *     - string, query
     *     - array, elementi del checkbox (value_check=>text)
     * @param string $label testo <label>
     * @param array $options
     *     array associativo di opzioni
     *     - @b id (string)
     *     - @b classField (string)
     *     - @b readonly (boolean)
     *     - @b js (string)
     *     - @b other (string)
     *     - @b required (string)
     *     - @b classLabel (string)
     *     - @b checkPosition (stringa): posizionamento del checkbox (left)
     *     - @b table (string): nome della tabella con il campo da tradurre
     *     - @b field (mixed): nome o nomi dei campi da recuperare
     *         - string: nome del campo con il testo da tradurre
     *         - array: nomi dei campi da concatenare
     *     - @b idName (string): nome del campo di riferimento
     *     - @b encode_html (boolean): attiva la conversione del testo dal database ad html (default TRUE)
     * @return codice html riga form, input multicheck + label
     */
    public function multipleCheckbox($name, $checked, $data, $label, $options=null){

        if(!is_array($checked)) {
            $checked = array();
        }

        $this->setOptions($options);
        $encode_html = is_bool($this->option('encode_html')) ? $this->option('encode_html') : TRUE;

        $GFORM = "<div class=\"form-row\">";
        $GFORM .= $this->label($name, $label, $this->option('required'), $this->option('classLabel'))."\n";

        if(is_array($label)) {
            $options['helptext'] = array(
                'title' => isset($label['label']) ? $label['label'] : $label[0],
                'text' => isset($label['description']) ? $label['description'] : $label[1]
            );
        }

        $GFORM .= "<div class=\"form-multicheck\">\n";
        $GFORM .= "<table class=\"table table-hover table-striped table-bordered\">\n";

        if(is_string($data))
        {
            $db = db::instance();
            $a = $db->select(null, null, null, array('custom_query'=>$data));
            if(sizeof($a) > 0)
            {
                $GFORM .= "<thead>";
                if(sizeof($data) > 10) {
                        $GFORM .= "<tr>";
                        $GFORM .= "<th class=\"light\">"._("Filtra")."</th>";
                        $GFORM .= "<th class=\"light\"><input type=\"text\" class=\"no-check no-focus-padding\" size=\"6\" onkeyup=\"gino.filterMulticheck($(this), $(this).getParents('.form-multicheck')[0])\" /></th>";
                        $GFORM .= "</tr>";
                }
                $GFORM .= "<tr>";
                $GFORM .= "<th class=\"light\">"._("Seleziona tutti/nessuno")."</th>";
                $GFORM .= "<th style=\"text-align: right\" class=\"light\"><input type=\"checkbox\" onclick=\"gino.checkAll($(this), $(this).getParents('.form-multicheck')[0]);\" /></th>";
                $GFORM .= "</tr>";
                $GFORM .= "</thead>";
                foreach($a AS $b)
                {
                    $b = array_values($b);
                    $val1 = $b[0];
                    $val2 = $b[1];

                    if(in_array($val1, $checked)) $check = "checked=\"checked\""; else $check = '';

                    $GFORM .= "<tr>\n";

                    $checkbox = "<input type=\"checkbox\" name=\"$name\" value=\"$val1\" $check";
                    $checkbox .= $this->option('id')?"id=\"{$this->option('id')}\" ":"";
                    $checkbox .= $this->option('classField')?"class=\"{$this->option('classField')}\" ":"";
                    $checkbox .= $this->option('readonly')?"readonly=\"readonly\" ":"";
                    $checkbox .= $this->option('js')?$this->option('js')." ":"";
                    $checkbox .= $this->option('other')?$this->option('other')." ":"";
                    $checkbox .= " />";

                    $field = $this->option('field');
                    if(is_array($field) && count($field))
                    {
                        if(sizeof($field) > 1)
                        {
                            $array = array();
                            foreach($field AS $value)
                            {
                                $array[] = $value;
                                $array[] = '\' \'';
                            }
                            array_pop($array);

                            $fields = $db->concat($array);
                        }
                        else $fields = $field[0];

                        $record = $db->select($fields." AS v", $this->option('table'), $this->option('idName')."='$val1'");
                        if(!$record)
                            $value_name = '';
                        else
                        {
                            foreach($record AS $r)
                            {
                                $value_name = $r['v'];
                            }
                        }
                    }
                    elseif(is_string($field))
                    {
                        $value_name = $this->_trd->selectTXT($this->option('table'), $field, $val1, $this->option('idName'));
                    }
                    else $value_name = '';

                    if($encode_html && $value_name) $value_name = htmlChars($value_name);

                    if($this->option("checkPosition")=='left') {
                        $GFORM .= "<td style=\"text-align:left\">$checkbox</td>";
                        $GFORM .= "<td>".$value_name."</td>";
                    }
                    else {
                        $GFORM .= "<td>".$value_name."</td>";
                        $GFORM .= "<td style=\"text-align:right\">$checkbox</td>";
                    }
                    $GFORM .= "</tr>\n";

                }

            }
            else $GFORM .= "<tr><td>"._("non risultano scelte disponibili")."</td></tr>";
        }
        elseif(is_array($data))
        {
            $i = 0;
            if(sizeof($data)>0)
            {
                                $GFORM .= "<thead>";
                                if(sizeof($data) > 10) {
                                        $GFORM .= "<tr>";
                                        $GFORM .= "<th class=\"light\">"._("Filtra")."</th>";
                                        $GFORM .= "<th class=\"light\"><input type=\"text\" class=\"no-check no-focus-padding\" size=\"6\" onkeyup=\"gino.filterMulticheck($(this), $(this).getParents('.form-multicheck')[0])\" /></th>";
                                        $GFORM .= "</tr>";
                                }
                                $GFORM .= "<tr>";
                                $GFORM .= "<th class=\"light\">"._("Seleziona tutti/nessuno")."</th>";
                                $GFORM .= "<th style=\"text-align: right\" class=\"light\"><input type=\"checkbox\" onclick=\"gino.checkAll($(this), $(this).getParents('.form-multicheck')[0]);\" /></th>";
                                $GFORM .= "</tr>";
                                $GFORM .= "</thead>";
                foreach($data as $k=>$v)
                {
                    $check = in_array($k, $checked)? "checked=\"checked\"": "";
                    $value_name = $v;
                    if($encode_html && $value_name) $value_name = htmlChars($value_name);

                    $GFORM .= "<tr>\n";

                    $checkbox = "<input type=\"checkbox\" name=\"$name\" value=\"$k\" $check";
                    $checkbox .= $this->option('id')?"id=\"{$this->option('id')}\" ":"";
                    $checkbox .= $this->option('classField')?"class=\"{$this->option('classField')}\" ":"";
                    $checkbox .= $this->option('readonly')?"readonly=\"readonly\" ":"";
                    $checkbox .= $this->option('js')?$this->option('js')." ":"";
                    $checkbox .= $this->option('other')?$this->option('other')." ":"";
                    $checkbox .= " />";

                    if($this->option("checkPosition")=='left') {
                    $GFORM .= "<td style=\"text-align:left\">$checkbox</td>";
                    $GFORM .= "<td>$value_name</td>";
                    }
                    else {
                    $GFORM .= "<td>$value_name</td>";
                    $GFORM .= "<td style=\"text-align:right\">$checkbox</td>";
                    }

                    $GFORM .= "</tr>\n";

                    $i++;
                }
                $GFORM .= "</table>\n";
            }
            else $GFORM .= "<tr><td>"._("non risultano scelte disponibili")."</td></tr>";
        }

        $GFORM .= "</table>\n";
        $GFORM .= "</div>\n";

        if(isset($options['helptext'])) {
            $title = $options['helptext']['title'];
            $text = $options['helptext']['text'];
            $GFORM .= " <span class=\"fa fa-question-circle label-tooltipfull\" title=\"".attributeVar($title.'::'.$text)."\"></span>";
        }
        if(isset($options['add_related'])) {
            $title = $options['add_related']['title'];
            $id = $options['add_related']['id'];
            $url = $options['add_related']['url'];
            $GFORM .= " <a target=\"_blank\" href=\"".$url."\" onclick=\"return gino.showAddAnotherPopup($(this))\" id=\"".$id."\" class=\"fa fa-plus-circle form-addrelated\" title=\"".attributeVar($title)."\"></a>";
        }
        $GFORM .= "</div>\n";

        return $GFORM;
    }

    /**
     * @brief Input select con label
     *
     * @see self::label()
     * @param string $name nome input
     * @param string $value elemento selezionato (ad es. valore da 'modifica')
     * @param mixed $data elementi del select
     * @param mixed $label testo del tag label
     * @param array $options
     *     array associativo di opzioni (aggiungere quelle del metodo select())
     *     - @b required (boolean): campo obbligatorio
     *     - @b text_add (string): testo dopo il select
     *     - @b classLabel (string): valore CLASS del tag SPAN in <label>
     * @return codice html riga form, select + label
     */
    public function cselect($name, $value, $data, $label, $options=null) {

        $this->setOptions($options);
        $GFORM = "<div class=\"form-row\">";
        $GFORM .= $this->label($name, $label, $this->option('required'))."\n";
        if(is_array($label)) {
            $options['helptext'] = array(
                'title' => isset($label['label']) ? $label['label'] : $label[0],
                'text' => isset($label['description']) ? $label['description'] : $label[1]
            );
        }
        $GFORM .= $this->select($name, $value, $data, $options);
        if($this->option('text_add')) $GFORM .= "<div class=\"form-textadd\">".$this->option('text_add')."</div>";
        $GFORM .= "</div>";

        return $GFORM;
    }

    /**
     * @brief Input select
     *
     * @param string $name nome input
     * @param mixed $selected elemento selezionato
     * @param mixed $data elementi del select (query-> recupera due campi, array-> key=>value)
     * @param array $options
     *     array associativo di opzioni (aggiungere quelle del metodo select())
     *     - @b id (string): ID del tag select
     *     - @b classField (string): nome della classe del tag select
     *     - @b size (integer)
     *     - @b multiple (boolean): scelta multipla di elementi
     *     - @b js (string): utilizzare per eventi javascript (ad es. onchange=\"jump\")
     *     - @b other (string): altro da inserire nel tag select
     *     - @b noFirst (boolean): FALSE-> mostra la prima voce vuota
     *     - @b firstVoice (string): testo del primo elemento
     *     - @b firstValue (mixed): valore del primo elemento
     *     - @b maxChars (integer): numero massimo di caratteri del testo
     *     - @b cutWords (boolean): taglia l'ultima parola se la stringa supera il numero massimo di caratteri
     * @return widget html
     */
    public function select($name, $selected, $data, $options) {

        $this->setOptions($options);
        $GFORM = "<select name=\"$name\" ";
        $GFORM .= $this->option('id')?"id=\"{$this->option('id')}\" ":"";
        $GFORM .= $this->option('required')?"required ":"";
        $GFORM .= $this->option('classField')?"class=\"{$this->option('classField')}\" ":"";
        $GFORM .= $this->option('size')?"size=\"{$this->option('size')}\" ":"";
        $GFORM .= $this->option('multiple')?"multiple=\"multiple\" ":"";
        $GFORM .= $this->option('js')?$this->option('js')." ":"";
        $GFORM .= $this->option('other')?$this->option('other')." ":"";
        $GFORM .= ">\n";

        if(!$this->option('noFirst')) $GFORM .= "<option value=\"\"></option>\n";
        elseif($this->option('firstVoice')) $GFORM .= "<option value=\"".$this->option('firstValue')."\">".$this->option("firstVoice")."</option>";

        if(is_array($data)) {
            if(sizeof($data) > 0) {
                foreach ($data as $key=>$value) {
                    if($this->option('maxChars'))
                    {
                        $value = cutHtmlText($value, $this->option('maxChars'), '...', TRUE, $this->option('cutWords')?$this->option('cutWords'):FALSE, TRUE);
                    }
                    $value = htmlChars($value);

                    $GFORM .= "<option value=\"$key\" ".($key==$selected?"selected=\"selected\"":"").">".$value."</option>\n";
                }
            }
            //else return _("non risultano opzioni disponibili");
        }
        elseif(is_string($data)) {

            $db = Db::instance();
            $a = $db->select(null, null, null, array('custom_query'=>$data));
            if(sizeof($a) > 0)
            {
                foreach($a AS $b)
                {
                    $b = array_values($b);
                    $val1 = $b[0];
                    $val2 = $b[1];

                    if($this->option('maxChars')) $value = cutHtmlText($val2, $this->option('maxChars'), '...', TRUE, $this->option('cutWords')?$this->option('cutWords'):FALSE, TRUE);
                    else $value = $val2;
                    $GFORM .= "<option value=\"".htmlInput($val1)."\" ".($val1==$selected?"selected=\"selected\"":"").">".htmlChars($value)."</option>\n";
                }
            }
        }

        $GFORM .= "</select>\n";

            if(isset($options['helptext'])) {
                $title = $options['helptext']['title'];
                $text = $options['helptext']['text'];
                $GFORM .= " <span class=\"fa fa-question-circle label-tooltipfull\" title=\"".attributeVar($title.'::'.$text)."\"></span>";
            }

        if(isset($options['add_related'])) {
            $title = $options['add_related']['title'];
            $id = $options['add_related']['id'];
            $url = $options['add_related']['url'];
            $GFORM .= " <a target=\"_blank\" href=\"".$url."\" onclick=\"return gino.showAddAnotherPopup($(this))\" id=\"".$id."\" class=\"fa fa-plus-circle form-addrelated\" title=\"".attributeVar($title)."\"></a>";
        }

        return $GFORM;
    }

    /**
     * @brief Input file con label
     *
     * Integra il checkbox di eliminazione del file e non è gestita l'obbligatorietà del campo.
     *
     * @code
     * $obj->cfile('image', $filename, _("testo label"), array("extensions"=>array('jpg', ...), "preview"=>TRUE, "previewSrc"=>/path/to/image);
     * @endcode
     *
     * @see self::label()
     * @param string $name nome input
     * @param string $value nome del file
     * @param string $label testo <label>
     * @param array $options
     *     array associativo di opzioni (aggiungere quelle del metodo input())
     *     - @b extensions (array): estensioni valide
     *     - @b classLabel (string): valore CLASS del tag SPAN in <label>
     *     - @b preview (boolean): mostra l'anteprima di una immagine
     *     - @b previewSrc (string): percorso relativo dell'immagine
     *     - @b text_add (string): testo da aggiungere in coda al tag input
     * @return codice html riga form, input file + label
     */
    public function cfile($name, $value, $label, $options){

        $this->setOptions($options);

        $text_add = $this->option('text_add') ? $this->option('text_add') : '';
        $valid_extension = $this->option('extensions');
        $text = (is_array($valid_extension) AND sizeof($valid_extension) > 0) ? "[".(count($valid_extension) ? implode(', ', $valid_extension) : _("non risultano formati permessi."))."]":"";
        $finLabel = array();
        $finLabel['label'] = is_array($label) ? $label[0]:$label;
        $finLabel['description'] = (is_array($label) && $label[1]) ? $text."<br/>".$label[1]:$text;

        $GFORM = "<div class=\"form-row\">";
        $GFORM .= $this->label($name, $finLabel, $this->option('required'), $this->option('classLabel'))."\n";

        if(is_array($finLabel)) {
            $options['helptext'] = array(
                'title' => _('Formati consentiti'),
                'text' => $finLabel['description']
            );
        }

        if(is_array($label)) {
            $options['helptext'] = array(
                'title' => isset($label['label']) ? $label['label'] : $label[0],
                'text' => isset($label['description']) ? $label['description'] : $label[1]
            );
        }

        if(!empty($value)) {
            $value_link = ($this->option('preview') && $this->option('previewSrc'))
                ? ($this->isImage($this->option('previewSrc'))
                    ? sprintf('<span onclick="Slimbox.open(\'%s\')" class="link">%s</span>', $this->option('previewSrc'), $value)
                    : sprintf('<a target="_blank" href="%s">%s</a>', $this->option('previewSrc'), $value))
                : $value;
            $required = $options['required'];
            $options['required'] = FALSE;
            $GFORM .= $this->input($name, 'file', $value, $options);
            $GFORM .= "<div class=\"form-file-check\">";
            if(!$required) {
                $GFORM .= "<input type=\"checkbox\" name=\"check_del_$name\" value=\"ok\" />";
                $GFORM .= " "._("elimina")." ";
            }
            $GFORM .= _("file caricato").": <b>$value_link</b>";
            $GFORM .= "</div>";
            $GFORM .= $text_add;
        }
        else
        {
            $GFORM .= $this->input($name, 'file', $value, $options);
            $GFORM .= $text_add;
        }

        if($value) 
            $GFORM .= $this->hidden('old_'.$name, $value);

        $GFORM .= "</div>";

        return $GFORM;
    }

    /*+
     * @brief Controlla che il path sia di un'immagine
     * @param string $path
     * @return TRUE se immagine, FALSE altrimenti
     */
    private function isImage($path) {

        $info = pathinfo($path);

        return in_array($info['extension'], array('jpg', 'jpeg', 'png', 'gif', 'bmp', 'tif'));
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

    /**
     * @brief Upload del file temporaneo nella directory di destinazione
     * @param resource $file_tmp
     * @param string $file_name
     * @param string $uploaddir directory di destinazione
     * @return risultato operazione, bool
     */
    private function upload($file_tmp, $file_name, $uploaddir){

        $uploadfile = $uploaddir.$file_name;
        if(move_uploaded_file($file_tmp, $uploadfile)) return TRUE;
        else return FALSE;
    }

    /**
     * @brief Imposta il carattere '/' come ultimo carattere della directory
     *
     * @param string $directory nome della directory
     * @return path directory
     */
    private function dirUpload($directory){

        $directory = (substr($directory, -1) != '/' && $directory != '') ? $directory.'/' : $directory;
        return $directory;
    }

    /**
     * @brief Sostituisce nel nome di un file i caratteri diversi da [a-zA-Z0-9_.-] con il carattere underscore (_)
     *
     * @param string $filename nome del file
     * @param string $prefix prefisso da aggiungere al nome del file
     * @return nome file normalizzato
     */
    private function checkFilename($filename, $prefix) {

        $filename = preg_replace("#[^a-zA-Z0-9_\.-]#", "_", $filename);
        return $prefix.$filename;
    }

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

    /**
     * @brief Calcola le dimensioni alle quali deve essere ridimensionata una immagine
     * 
     * @param integer $new_width
     * @param integer $new_height
     * @param integer $im_width
     * @param integer $im_height
     * @return array (larghezza, altezza)
     */
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

    /**
     * @brief Ricalcola le dimensioni di un'immagine, dimensionando rispetto al lato lungo
     * @param int $dimension dimensione ridimensionamento
     * @param int $im_width larghezza immagine
     * @param int $im_height altezza immagine
     * @return array(larghezza, altezza)
     */
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

    /**
     * @brief Funzione javascript che conta il numero dei caratteri ancora disponibili all'interno di un textarea
     *
     * @code
     * $buffer = "<script type=\"text/javascript\" language=\"javascript\">initCounter($('id_elemento'), {$this->option('maxlength')})</script>";
     * @endcode
     *
     * @return codice html
     */
    public function jsCountCharText(){

        $GFORM = "<script type=\"text/javascript\">\n";
        $GFORM .= "
        function countlimit(field, limit){
            chars = field.get('value').length;
            return limit-chars;
        }

        function initCounter(field, limit){

            act_limit = countlimit(field, limit);

            var limit_text = new Element('div', {style:'font-weight:bold;'});
            var limit_number = new Element('span');
            limit_number.set('text', act_limit);
            limit_text.set('html', '"._("Caratteri rimasti: ")."');
            limit_number.inject(limit_text, 'bottom');

            field.addEvent('keypress', function(e) {
                var left_chars = countlimit(field, limit);
                if(left_chars<1) {
                    var event = new DOMEvent(e);
                    if(event.key != 'delete' && event.key != 'backspace') {
                        e.stopPropagation();
                        return false;
                    }
                }

            });

            field.addEvent('keyup', function(e) {
                var left_chars = countlimit(field, limit);
                limit_number.set('text', left_chars);

            });

            limit_text.inject(field, 'after');

        }";
        $GFORM .= "</script>";
        return $GFORM;
    }

    /**
     * @brief Interfaccia che apre o chiude il form per l'inserimento e la modifica delle traduzioni
     * 
     * Viene richiamato nei metodi della classe Gino.Form: cinput(), ctextarea(), textarea()
     * 
     * @see gino-min.js
     * @param string $type tipologia di input (input, textarea, fckeditor)
     * @param string $tbl nome della tabella con il campo da tradurre
     * @param string $field nome del campo con il testo da tradurre
     * @param integer $id_value valore dell'ID del record di riferimento per la traduzione
     * @param integer $width lunghezza del tag input o numero di colonne (textarea)
     * @param string $toolbar nome della toolbar dell'editor html
     * @return codice html interfaccia
     */
    private function formFieldTranslation($type, $tbl, $field, $id_value, $width, $toolbar='') {

        Loader::import('language', 'Lang');

        $GINO = '';

        if(empty($id_name)) $id_name = 'id';

        $langs = \Gino\App\Language\Lang::objects(null, array(
            'where' => "active='1' AND id != '".$this->_registry->sysconf->dft_language."'"
        ));
        if($langs)
        {
            $first = TRUE;
            foreach($langs AS $lang) {
                $label = htmlChars($lang->label);
                $code = $lang->language_code.'_'.$lang->country_code;
                $GINO .= "<span class=\"trnsl-lng\" onclick=\"gino.translations.prepareTrlForm('$code', $(this), '$tbl', '$field', '$type', '$id_value', '$width', '$toolbar', '".$this->_registry->request->absolute_url."&trnsl=1')\">".$label."</span> &#160;";
                $first = FALSE;
            }
            $GINO .= " &nbsp; <span id=\"".$tbl.$field."\"></span>\n";
        }

         return $GINO;
    }

}
