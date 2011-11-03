<?php

class Form {

	const _IMAGE_GIF_ = 1;
	const _IMAGE_JPG_ = 2;
	const _IMAGE_PNG_ = 3;

	private $_method, $_requestVar;
	private $_validation;
	private $_form_name;
	private $_options;

	private $_max_file_size;
	
	private $_trd;
	private $_lng_trl, $_multi_language, $_show_trnsl;
	private $_trnsl_table, $_trnsl_id;
	private $_tblLayout;
	
	private $_input_size, $_input_max;
	private $_prefix_file, $_prefix_thumb;
	
	private $_tbl_attached_ctg;
	private $_tbl_attached;
	
	private $_extension_denied;
	
	private $_div_label_width, $_div_field_width;
	
	private $_input_field, $_textarea_field, $_fckeditor_field;
	
	private $_ico_calendar_path;
	
	function __construct($formId, $method, $validation, $options=null){
		
		$this->_formId = $formId;
		$this->setMethod($method);
		$this->setValidation($validation);	// js:validateForm();
		$this->setDimensions($options);
		$this->_trnsl_table = isset($options['trnsl_table'])?$options['trnsl_table']:null;
		$this->_trnsl_id = isset($options['trnsl_id'])?$options['trnsl_id']:null;
		if(isset($options['verifyToken']) && $options['verifyToken']) 
			if(!$this->verifyFormToken($formId)) exit(error::syserrorMessage("form", "construct", _("Rilevato attacco CSRF o submit del form dall'esterno ")));

		$this->_tblLayout = isset($options['tblLayout'])?$options['tblLayout']:true;
		
		$this->_max_file_size = MAX_FILE_SIZE;

		$this->_lng_trl = new language;
		$this->_multi_language = pub::getMultiLanguage();
		$this->_show_trnsl = SHOW_TRNSL;
		$this->_trd = new translation($_SESSION['lng'], $_SESSION['lngDft']);
		
		if(!isset($_SESSION)) session_start();
		
		$this->_prefix_file = 'img_';
		$this->_prefix_thumb = 'thumb_';
		
		$this->_tbl_attached_ctg = "attached_ctg";
		$this->_tbl_attached = "attached";
		
		$this->_input_field = 'input';
		$this->_textarea_field = 'textarea';
		$this->_fckeditor_field = 'fckeditor';
		
		$this->_extension_denied = array(
		'php', 'phps', 'js', 'py', 'asp', 'rb', 'cgi', 'cmd', 'sh', 'exe', 'bin'
		);
		
		$this->_ico_calendar_path = SITE_IMG."/ico_calendar.png";
	}

	private function setOptions($options) {
		$this->_options = $options;
	}

	private function option($opt) {

		if($opt=='mode') return isset($this->_options['mode'])?$this->_options['mode']:"table";

		if($opt=='trnsl_id') return isset($this->_options['trnsl_id'])?$this->_options['trnsl_id']:$this->_trnsl_id;
		if($opt=='trnsl_table') return isset($this->_options['trnsl_table'])?$this->_options['trnsl_table']:$this->_trnsl_table;

		if($opt=='fck_width') return isset($this->_options['fck_width'])?$this->_options['fck_width']:'100%';
		if($opt=='fck_height') return isset($this->_options['fck_height'])?$this->_options['fck_height']:300;
		if($opt=='fck_toolbar') return isset($this->_options['fck_toolbar'])?$this->_options['fck_toolbar']:'Basic';

		return isset($this->_options[$opt])? $this->_options[$opt]:null;
	}

	private function setMethod($method){
		
		$this->_method = $method;
		$this->_requestVar = $method=='post'?$_POST:($method=='get'?$_GET:$_REQUEST);	
		
		if(!isset($GLOBALS[$this->_method])) $GLOBALS[$this->_method] = array();
	}
	
	private function setValidation($validation){
		
		$this->_validation = (bool) $validation;
	}
	
	private function setDimensions($options) {
		
		$this->_form_label_width = isset($options['form_label_width'])? $options['form_label_width']:FORM_LABEL_WIDTH;
		$this->_form_field_width = isset($options['form_field_width'])? $options['form_field_width']:FORM_FIELD_WIDTH;
	}
	
	private function generateFormToken($formName) {
  		$token = md5(uniqid(microtime(), true));
  		$_SESSION[$formName.'_token'] = $token;
  		return $token;
	}

	private function verifyFormToken($formName) {
  		$index = $formName.'_token';
		// There must be a token in the session
  		if (!isset($_SESSION[$index])) return false;
  		// There must be a token in the form
  		if (!isset($_POST['token'])) return false;
  		// The token must be identical
  		if ($_SESSION[$index] !== $_POST['token']) return false;
  		return true;
	}

	/**
	 * Recupero dati da sessione
	 *
	 * @param array $session_value	nome della variabile di sessione
	 * @param boolean $clear		distrugge la sessione
	 * @return global
	 */
	public function load($session_value, $clear=true){
		if(isset($_SESSION[$session_value]))
		{
			if(isset($_SESSION['GINOERRORMSG']) AND !empty($_SESSION['GINOERRORMSG']))
			{
				for($a=0, $b=count($_SESSION[$session_value]); $a < $b; $a++)
				{
					foreach($_SESSION[$session_value][$a] as $key => $value)
					{
						$GLOBALS[$this->_method][$key] = $value;
					}
				}
			}
			
			if($clear) unset($_SESSION[$session_value]);
		}
	}
	
	public function save($session_value){
		
		$_SESSION[$session_value] = Array();
		foreach($this->_requestVar as $key => $value)
			array_push($_SESSION[$session_value], Array($key => $value));
	}
	
	public function retvar($name, $default){
		return isset($GLOBALS[$this->_method][$name]) ? $GLOBALS[$this->_method][$name] : $default;
	}
	
	/*
	 * Opzioni:
	 * func_confirm		string		nome della funzione js da chiamare (es. window.confirmSend())
	 * text_confirm		string		testo del messaggio che compare nel box di conferma
	 * - per attivarle occorre istanziare la classe 'Form' con il parametro: $validation=true
	 */
	public function form($action, $upload, $list_required, $options=null){
		
		$GFORM = '';
		
		$confirm = '';
		if(isset($options['func_confirm']) && $options['func_confirm'])
			$confirm = " && ".$options['func_confirm'];
		if(isset($options['text_confirm']) && $options['text_confirm'])
			$confirm = " && confirmSubmit('".$options['text_confirm']."')";
		
		$GFORM .= "<form ".($upload?"enctype=\"multipart/form-data\"":"")." id=\"".$this->_formId."\" name=\"".$this->_formId."\" action=\"$action\" method=\"$this->_method\"";
		if($this->_validation) $GFORM .= " onsubmit=\"return (validateForm($(this))".$confirm.")\"";
		$GFORM .= ">\n";
	
		if($this->_tblLayout) $GFORM .= $this->startTable();
		if(isset($options['generateToken']) && $options['generateToken']) 
			$GFORM .= $this->hidden('token', $this->generateFormToken($this->_formId));
		if(!empty($list_required)) $GFORM .= $this->frequired($list_required);

		return $GFORM;
	}

	public function startTable() {

		$GFORM = "<table class=\"formTbl\">\n";
		$GFORM .= "<tr style=\"visibility:collapse\"><td style=\"width:$this->_form_label_width;border:0px solid #999;\"></td><td style=\"width:$this->_form_field_width;border:0px solid #999;\"></td></tr>";
		return $GFORM;

	}

	public function cform(){
		
		$GFORM = '';
		if($this->_tblLayout) $GFORM = $this->endTable();
		$GFORM .= "</form>\n";
		
		return $GFORM;
	}
	
	public function endTable() {

		$GFORM = "</table>";
		return $GFORM;
	}

	public function cell($content, $options=null) {

		$style = isset($options['style']) ? "style=\"".$options['style']."\"" : '';
		
		$GFORM = "<tr $style>";
		$GFORM .= "<td colspan=\"2\" ".(isset($options['id'])?"id=\"".$options['id']."\"":"")." class=\"formCell\" ".(isset($options['other'])?$options['other']:"").">";
		$GFORM .= $content;
		$GFORM .= "</td>";
		$GFORM .= "</tr>";

		return $GFORM;
	}

	public function editorHtml($name, $value, $toolbar, $width, $height, $replace=false){
		
		if($width == '100%') $width = '98%'; // TODO correct directly in classes

		if(empty($value)) $value = '';	// Default text in editor
		
		include(SITE_ROOT."/ckeditor/ckeditor.php");

		$oCKeditor = new CKeditor(SITE_WWW.'/ckeditor/');

		$oCKeditor->returnOutput = true;

		$oCKeditor->config['toolbar'] = $toolbar == 'Basic' ? 'Basic' : 'Full';
		$oCKeditor->config['contentsCss']  = SITE_CUSTOM_CKEDITOR.'/stylesheet.css';
		$oCKeditor->config['customConfig']  = SITE_CUSTOM_CKEDITOR.'/config.js';
		$oCKeditor->config['width']  = $width;
		$oCKeditor->config['height'] = $height;
		
		$output = $replace ? $oCKeditor->replace($name) : $oCKeditor->editor($name, $value);
		
		return $output;
	}
	
	/**
	 * Label
	 *
	 * @param string	 	$name
	 * @param string|array 	$text		se array: array('label'=>_("..."), 'description'=>_("..."))
	 * @param boolean		$required
	 * @param string		$class		classe dello span (class=\"\")
	 * @return string
	 */
	public function label($name, $text, $required, $class=null){

		if(empty($name) || !$text) return '';

		if(!$class) $class = "class=\"form_text_label\"";
		
		$GFORM = "<label for=\"$name\"".($required ? "class=\"req\"":"").">";
		$GFORM .= "<span class=\"form_star\">".($required?"*&#160;":"&#160;&#160;")."</span>";
		if(is_array($text) && count($text)==2) {
			$GFORM .= "<span $class>".(isset($text['label'])?$text['label']:$text[0])."</span>";
			$GFORM .= "<br/><span class=\"form_text_label_exp\">".(isset($text['description'])?$text['description']:$text[1])."</span>";
		}
		else $GFORM .= "<span $class>$text</span>";
		$GFORM .= "</label>";
		
		return $GFORM;
	}
		
	private function frequired($list){
		
		return !empty($list)? $this->hidden('required', $list):'';
	}
	
	public function arequired(){
		
		$required = isset($_REQUEST['required'])? cleanVar($_REQUEST, 'required', 'string', '') : '';
		
		$error = 0;
		
		if(!empty($required))
			foreach(explode(",", $required) as $fieldname)
				if($_REQUEST[$fieldname] == '') $error++;
		return $error;
	}
	
	public function captcha($options=null) {

		$public_key = pub::variable("captcha_public");
		$private_key = pub::variable("captcha_private");

		if($public_key && $private_key) return $this->reCaptcha($public_key, $options);
		else return $this->defaultCaptcha($options);

	}

	private function reCaptcha($public_key, $options=null) {

		$options["required"] = true;
		$this->setOptions($options);
		$GFORM = "<tr>\n";
		$GFORM .= "<td class=\"form_label\">".$this->label('captcha_input', _("Inserisci il codice di controllo"), $this->option('required'), $this->option('classLabel'))."</td>\n";
		$GFORM .= "<td class=\"form_field\">\n";
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
		if($this->option('text_add')) $GFORM .= $this->option('text_add');
		$GFORM .= "</td>\n";
		$GFORM .= "</tr>\n";
		
		return $GFORM;

	}

	private function defaultCaptcha($options) {
	
		$options["required"] = true;
		$options["id"] = "captcha_input";
		$options["size"] = "20";
		$options["maxlength"] = "20";
		$this->setOptions($options);
		$GFORM = "<tr>\n";
		$GFORM .= "<td class=\"form_label\">".$this->label('captcha_input', _("Inserisci il codice dell'immagine"), $this->option('required'), $this->option('classLabel'))."</td>\n";
		$GFORM .= "<td class=\"form_field\">\n";
		$GFORM .= "<img style=\"margin:2px 15px 2px 2px;vertical-align:middle;\" src=\"lib/captchaImage.php\" alt=\"captcha image\"/>";
		$GFORM .= $this->input('captcha_input', 'text', '', $options);
		if($this->option('text_add')) $GFORM .= $this->option('text_add');
		$GFORM .= "</td>\n";
		$GFORM .= "</tr>\n";
		
		return $GFORM;
	}

	public function checkCaptcha() {

		$public_key = pub::variable("captcha_public");
		$private_key = pub::variable("captcha_private");

		if($public_key && $private_key) return $this->checkReCaptcha($public_key, $private_key);
		else return $this->checkDefaultCaptcha();
		
	}

	private function checkReCaptcha($public_key, $private_key) {
	
		require_once(LIB_DIR.OS.'recaptchalib.php');
		$private_key = pub::variable("captcha_private");
		$resp = recaptcha_check_answer($private_key, $_SERVER["REMOTE_ADDR"], $_POST["recaptcha_challenge_field"], $_POST["recaptcha_response_field"]);

		$captcha = cleanVar($_REQUEST, 'captcha_input', 'string', '');
		return $resp->is_valid ? true:false;

	}

	private function checkDefaultCaptcha() {
	
		$captcha = cleanVar($_REQUEST, 'captcha_input', 'string', '');
		$result = $captcha == $_SESSION['pass']? true:false;
		unset($_SESSION['pass']);

		return $result;

	}

	// Tabella senza Input Form
	public function noinput($label, $value, $options=null) {
		
		$this->setOptions($options);
		
		$id = $this->option('id') ? "id=\"".$this->option('id')."\"" : '';
		$style = $this->option('style') ? "style=\"".$this->option('style')."\"" : '';
		$other = $this->option('other') ? $this->option('other') : '';
		$class = $this->option('class') ? $this->option('class') : 'form_text_label';
		
		$GFORM = '';
		
		if(!empty($label) OR !empty($value))
		{
			$GFORM .= "<tr $style>\n";
			$GFORM .= "<td $id class=\"form_label\" $other>";
			$GFORM .= "<span class=\"$class\">$label</span>";
			$GFORM .= "</td>\n";
			$GFORM .= "<td>$value</td>\n";
			$GFORM .= "</tr>\n";
		}
		
		return $GFORM;
	}
	
	public function hidden($name, $value, $options=null) {

		$GFORM = '';
		$this->setOptions($options);
		if($this->_tblLayout) $GFORM = "<tr style=\"display:none\"><td colspan=\"2\">";
		$GFORM .= "<input type=\"hidden\" name=\"$name\" value=\"$value\" ".($this->option("id")?"id=\"{$this->option("id")}\"":"")."/>";
		if($this->_tblLayout) $GFORM .= "</td></tr>\n";

		return $GFORM;
	}
// name, type, value, options('required', 'pattern', 'size', 'maxlength', 'id', 'classLabel', 'classField', 'js', 'readonly', 'trnsl', 'tbl_trnsl', 'field_trnsl', 'id_trnsl', 'other', 'text_add')
	public function input($name, $type, $value, $options=null){

		$this->setOptions($options);
		$GFORM = "<input type=\"$type\" name=\"$name\" value=\"$value\" ";
		
		$GFORM .= $this->option('id')?"id=\"{$this->option('id')}\" ":"";
		$GFORM .= $this->option('pattern')?"pattern=\"{$this->option('pattern')}\" ":"";
		$GFORM .= $this->option('hint')?"placeholder=\"{$this->option('hint')}\" ":"";
		$GFORM .= $this->option('classField')?"class=\"{$this->option('classField')}\" ":"";
		$GFORM .= $this->option('size')?"size=\"{$this->option('size')}\" ":"";
		$GFORM .= $this->option('maxlength')?"maxlength=\"{$this->option('maxlength')}\" ":"";
		$GFORM .= $this->option('readonly')?"readonly=\"readonly\" ":"";
		$GFORM .= $this->option('js')?$this->option('js')." ":"";
		$GFORM .= $this->option('other')?$this->option('other')." ":"";
	
		$GFORM .= "/>";

		return $GFORM;
	}
	

// name, type, value, options('required', 'pattern', 'size', 'maxlength', 'classLabel', 'classField', 'js', 'readonly', 'trnsl', 'trnsl_table', 'field', 'trnsl_id', 'other', 'text_add')	
	
	public function cinput($name, $type, $value, $label, $options){

		$this->setOptions($options);
		$GFORM = "<tr>\n";
		$GFORM .= "<td class=\"form_label\">".$this->label($name, $label, $this->option('required'), $this->option('classLabel'))."</td>\n";
		$GFORM .= "<td class=\"form_field\">\n";
		$GFORM .= $this->input($name, $type, $value, $options);
		if($this->option('trnsl') AND $this->_multi_language == 'yes') {
			if($this->option('trnsl_id') && $this->_show_trnsl)
				$GFORM .= "<div>".$this->_lng_trl->viewFieldTranslation($this->option('trnsl_table'), $this->option('field'), $this->option('trnsl_id'))."</div>";
			if($this->option('trnsl_id'))
				$GFORM .= $this->_lng_trl->formFieldTranslation($this->_input_field, $this->option('trnsl_table'), $this->option('field'), $this->option('trnsl_id'), $this->option('size'), '');
		}
		
		if($this->option('text_add')) $GFORM .= $this->option('text_add');
		
		$GFORM .= "</td>\n";
		$GFORM .= "</tr>\n";
		
		return $GFORM;
	}
	
	public function cinput_date($name, $value, $label, $options){

		$this->setOptions($options);
		if($this->option('inputClickEvent')) $options['other'] = "onclick=\"printCalendar($(this).getNext('img'), $(this))\"";
		$options['id'] = $name;
		$options['size'] = 10;
		$options['maxlength'] = 10;
		$options['pattern'] = "^\d\d/\d\d/\d\d\d\d$";
		$options['hint'] = _("inserire la data nel formato dd/mm/yyyy");
		
		$GFORM = '';
		if($this->_tblLayout)
		{
			$GFORM .= "<tr>\n";
			$GFORM .= "<td class=\"form_label\">\n";
			$GFORM .= $this->label($name, $label, $this->option('required'), $this->option('classLabel'));
			$GFORM .= "</td>\n";
			$GFORM .= "<td class=\"form_field\">\n";
		}
		
		$GFORM .= $this->input($name, 'text', $value, $options);
		$days = "['"._("Domenica")."', '"._("Lunedì")."', '"._("Martedì")."', '"._("Mercoledì")."', '"._("Giovedì")."', '"._("Venerdì")."', '"._("Sabato")."']";
		$months = "['"._("Gennaio")."', '"._("Febbraio")."', '"._("Marzo")."', '"._("Aprile")."', '"._("Maggio")."', '"._("Giugno")."', '"._("Luglio")."', '"._("Agosto")."', '"._("Settembre")."', '"._("Ottobre")."', '"._("Novembre")."', '"._("Dicembre")."']";

		$GFORM .= "<img style=\"margin-left:5px;margin-bottom:2px;cursor:pointer;\" class=\"tooltip\" title=\""._("calendario")."\" id=\"cal_button_$name\" src=\"".$this->_ico_calendar_path."\" onclick=\"printCalendar($(this), $(this).getPrevious('input'), $days, $months)\" />";
		if($this->option('text_add')) $GFORM .= $this->option('text_add');
		
		if($this->_tblLayout)
		{
			$GFORM .= "</td>\n";
			$GFORM .= "</tr>\n";
		}
		return $GFORM;
	}
	
	/**
	 * Textarea
	 *
	 * @param string $name
	 * @param string $value
	 * @param string $label
	 * @param array $options
	 * @return string
	 * 
	 * Opzioni:
	 * id			string		attivazione proprietà 'id'
	 * required		boolean		campo obbligatorio
	 * classLabel	string
	 * classField	string
	 * rows			integer		numero di righe
	 * cols			integer		numero di colonne
	 * readonly
	 * js
	 * other
	 * text_add		string		testo aggiuntivo stampato sotto il box
	 * maxlength	integer		numero massimo di caratteri consentiti
	 * 
	 * trnsl		boolean		attivazione della traduzione
	 * trsnl_id		integer		nome del campo identificativo
	 * trsnl_table	string		nome della tabella
	 * field		string		nome del campo da tradurre
	 */
	public function ctextarea($name, $value, $label, $options=null){

		$this->setOptions($options);
		$GFORM = "<tr>\n";
		$GFORM .= "<td class=\"form_label\">\n";
		$GFORM .= $this->label($name, $label, $this->option('required'), $this->option('classLabel'));
		$GFORM .= "</td>\n";
		$GFORM .= "<td class=\"form_field\">\n";
		$GFORM .= $this->textarea($name, $value, $options);
		if($this->option('trnsl') AND $this->_multi_language == 'yes') {
			if($this->option('trnsl_id') && $this->_show_trnsl)
				$GFORM .= "<div>".$this->_lng_trl->viewFieldTranslation($this->option('trnsl_table'), $this->option('field'), $this->option('trnsl_id'))."</div>";
			if($this->option('trnsl_id'))
				$GFORM .= $this->_lng_trl->formFieldTranslation($this->_textarea_field, $this->option('trnsl_table'), $this->option('field'), $this->option('trnsl_id'), $this->option('cols'), '');
		}
		
		if($this->option('text_add')) $GFORM .= $this->option('text_add');
		
		$GFORM .= "</td>\n";
		$GFORM .= "</tr>\n";
		
		return $GFORM;
	}

	public function textarea($name, $value, $options){
		
		$this->setOptions($options);
		$GFORM = "<textarea name=\"$name\" ";
		$GFORM .= $this->option('id')?"id=\"{$this->option('id')}\" ":"";
		$GFORM .= $this->option('required') ? "required=\"required\" ":"";
		$GFORM .= $this->option('classField')?"class=\"{$this->option('classField')}\" ":"";
		$GFORM .= $this->option('rows')?"rows=\"{$this->option('rows')}\" ":"";
		$GFORM .= $this->option('cols')?"cols=\"{$this->option('cols')}\" ":"";
		$GFORM .= $this->option('readonly')?"readonly=\"readonly\" ":"";
		$GFORM .= $this->option('js')?$this->option('js')." ":"";
		$GFORM .= $this->option('other')?$this->option('other')." ":"";

		$GFORM .= ">";
		$GFORM .= "$value</textarea>";

		if($this->option('maxlength') AND $this->option('maxlength') > 0)
		{
			// Limite caratteri con visualizzazione del numero di quelli restanti
			$GFORM .= $this->jsCountCharText();
			$GFORM .= "<script type=\"text/javascript\" language=\"javascript\">initCounter($$('#$this->_formId textarea[name=$name]')[0], {$this->option('maxlength')})</script>";
		}
		
		return $GFORM;
	}
	
	/**
	 * FCKEditor completo
	 *
	 * @param string $name			nome input
	 * @param string $value			valore attivo
	 * @param string $label			testo <label>
	 * @param array $options		opzioni
	 * @return string
	 * 
	 * Opzioni:
	 * --------------
	 * string $required			campo obbligatorio ('req')
	 * string $style1			stile <label>
	 * string $style2			stile <p>
	 * string|boolean $note		note (true->note di default, false->nessuna nota, [string]->note ad hoc)
	 * string $fck_toolbar		toolbarset (Basic, Full)
	 * string $fck_width		larghezza(%)
	 * integer $fck_height		altezza (pixel)
	 * bool $img_prew			mostrare o meno il browser di immagini di sistema
	 * bool $trnsl				traduzione 
	 * string $tbl				tabella in cui è presente il campo da tradurre
	 * string $field			campo da tradurre
	 * string $id_name   		nome del campo id nella tabella
	 * int $id_value      		valore del campo id
	 * 
	 * @example $this->_gform->fcktextarea('ctext', $value, _("testo"), array("required"=>true, "notes"=>true, "img_preview"=>true, "trnsl"=>true, "field"=>"ctext", "fck_toolbar"=>$_fck_toolbar)
	 */
	public function fcktextarea($name, $value, $label, $options){

		$this->setOptions($options);

		$text_note = '';
		if($this->option('notes')) {
			$ico_plain_text = "<img src=\"".SITE_IMG."/fck_pastetext.gif\" alt=\"paste as plain text\" />";
			$ico_image = "<img src=\"".SITE_IMG."/fck_image.gif\" alt=\"insert image\" />";
				
			$text_note .= "[Enter] "._("inserisce un &lt;p&gt;");
			$text_note .= " - [Shift+Enter] "._("inserisce un &lt;br&gt;");
		}
		
		if($this->option('mode')=="table") $GFORM = "<tr><td colspan=\"2\" class=\"form_fck\">\n";
		elseif($this->option('mode')=="div") $GFORM = "<div>\n";
		$GFORM .= $this->label($name, $label, $this->option('required'), $this->option('classLabel'));
		if($text_note) $GFORM .= "<div>".$text_note."</div>";
		$GFORM .= $this->editorHtml($name, $value, $this->option('fck_toolbar'), $this->option('fck_width'), $this->option('fck_height'));

		if($this->option('trnsl') AND $this->_multi_language == 'yes') {
			if($this->option('trnsl_id') && $this->_show_trnsl)
				$GFORM .= "<div>".$this->_lng_trl->viewFieldTranslation($this->option('trnsl_table'), $this->option('field'), $this->option('trnsl_id'))."</div>";
			if($this->option('trnsl_id'))
				$GFORM .= $this->_lng_trl->formFieldTranslation($this->_fckeditor_field, $this->option('trnsl_table'), $this->option('field'), $this->option('trnsl_id'), $this->option('fck_width'), $this->option('fck_toolbar'));
		}
		
		if($this->option('text_add')) $GFORM .= $this->option('text_add');
		
		if($this->option('img_preview')) $GFORM .= $this->imagePreviewer();
		if($this->option('mode')=="table") $GFORM .= "</td></tr>\n";
		elseif($this->option('mode')=="div") $GFORM .= "</div>\n";
		return $GFORM;
	}

	private function imagePreviewer() {
		
		$GFORM = '';
		
		$db = new db;

		$query = "SELECT id, name FROM ".$this->_tbl_attached_ctg." ORDER BY name";
		$a = $db->selectquery($query);
		if(sizeof($a)>0) {
			$data = $this->select('attached_ctg', '', $query, array("js"=>"onchange=\"ajaxRequest('post', 'index.php?pt[attached-slideShow]', 'ctg='+this.value, 'image_viewer', {'load':'image_viewer', 'cache':true, 'script':true})\"", "id"=>"attached_ctg"));
			$GFORM = "<p>"._("Archivio file disponibili in 'Allegati'")." $data</p>";
		}
		else {
		}

		$GFORM .= "<div id=\"image_viewer\"></div>";
		
		return $GFORM;
	}
	
	public function cradio($name, $value, $data, $default, $label, $options=null){
		
		$this->setOptions($options);
		$GFORM = "<tr>\n";
		$GFORM .= "<td class=\"form_label\">".$this->label($name, $label, $this->option('required'), $this->option('classLabel'))."</td>\n";
		$GFORM .= "<td class=\"form_field\">\n";
		$GFORM .= $this->radio($name, $value, $data, $default, $options);
		if($this->option('text_add')) $GFORM .= $this->option('text_add');
		$GFORM .= "</td>\n";
		$GFORM .= "</tr>\n";
		return $GFORM;
	}

	public function radio($name, $value, $data, $default, $options){
		
		$this->setOptions($options);
		$GFORM = '';

		$comparison = is_null($value)? $default:$value;
		$space = $this->option('aspect')=='v'? "<br />":"&nbsp;";
			
		if(is_array($data)) {
			$i=0;
			foreach($data AS $k => $v) {
				$GFORM .= ($i?$space:'')."<input type=\"radio\" name=\"$name\" value=\"$k\" ".($comparison==$k?"checked=\"checked\"":"")." ";
				$GFORM .= $this->option('id')?"id=\"{$this->option('id')}\" ":"";
				$GFORM .= $this->option('classField')?"class=\"{$this->option('classField')}\" ":"";
				$GFORM .= $this->option('js')?$this->option('js')." ":"";
				$GFORM .= $this->option('other')?$this->option('other')." ":"";
				$GFORM .= "/>".$v;
				$i++;
			}
		}
		
		return $GFORM;
	}
	
	/**
	 * Checkbox completo
	 *
	 * @param string	$name
	 * @param boolean 	$checked	true: di default la casella ha il check
	 * @param string	$value
	 * @param string	$label
	 * @param array		$options
	 * @return string
	 * 
	 * $checked = $value=='yes' ? true:false;
	 * $buffer .= $gform->ccheckbox('cycle', $checked, 'yes', _("Ciclo"));
	 */
	public function ccheckbox($name, $checked, $value, $label, $options=null){
		
		$this->setOptions($options);
		$GFORM = "<tr>\n";
		$GFORM .= "<td class=\"form_label\">".$this->label($name, $label, $this->option('required'), $this->option('classLabel'))."</td>\n";
		$GFORM .= "<td class=\"form_field\">\n";
		$GFORM .= $this->checkbox($name, $checked, $value, $options);
		if($this->option('text_add')) $GFORM .= $this->option('text_add');
		$GFORM .= "</td>\n";
		$GFORM .= "</tr>\n";
		return $GFORM;
	}
	
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

	public function multipleCheckbox($name, $checked, $data, $label, $options=null){
		
		$this->setOptions($options);
		$GFORM = "<tr>\n";
		$GFORM .= "<td class=\"form_label\">".$this->label($name, $label, $this->option('required'), $this->option('classLabel'))."</td>\n";
		$GFORM .= "<td class=\"form_field\">\n";

		if($this->option("maxHeight"))
			$GFORM .= "<div style=\"max-height:".$this->option("maxHeight")."px;overflow: auto;border:1px solid #000000;\">\n";
		
		$odd = true;
		if(is_string($data))
		{
			$result = mysql_query($data);
			if(mysql_num_rows($result) > 0)
			{
				$GFORM .= "<table style=\"width:100%;\">\n";
				while ($row = mysql_fetch_array($result))
				{
					list($val1, $val2) = $row;
					if(in_array($val1, $checked)) $check = "checked=\"checked\""; else $check = '';
					
					if($odd) $class = "mc_form_tr1"; else $class = "mc_form_tr2";
					$GFORM .= "<tr class=\"$class\">\n";
		
					$checkbox = "<input type=\"checkbox\" name=\"$name\" value=\"$val1\" $check";
					$checkbox .= $this->option('id')?"id=\"{$this->option('id')}\" ":"";
					$checkbox .= $this->option('classField')?"class=\"{$this->option('classField')}\" ":"";
					$checkbox .= $this->option('readonly')?"readonly=\"readonly\" ":"";
					$checkbox .= $this->option('js')?$this->option('js')." ":"";
					$checkbox .= $this->option('other')?$this->option('other')." ":"";
					$checkbox .= " />";

					if($this->option("checkPosition")=='left') {
						$GFORM .= "<td style=\"text-align:left\">$checkbox</td>";
						$GFORM .= "<td>".htmlChars($this->_trd->selectTXT($this->option('table'), $this->option('field'), $val1, $this->option('idName')))."</td>";
					}
					else {
						$GFORM .= "<td>".htmlChars($this->_trd->selectTXT($this->option('table'), $this->option('field'), $val1, $this->option('idName')))."</td>";
						$GFORM .= "<td style=\"text-align:right\">$checkbox</td>";
					}
					$GFORM .= "</tr>\n";
					
					$odd = !$odd;
				}
				$GFORM .= "</table>\n";
			}
			else $GFORM .= _("non risultano scelte disponibili");
		}
		elseif(is_array($data))
		{
			$i = 0;
			if(sizeof($data)>0)
			{
				$GFORM .= "<table style=\"width:100%;\">\n";
				foreach($data as $k=>$v)
				{
					$check = in_array($k, $checked)? "checked=\"checked\"": "";
					
					if($odd) $class = "mc_form_tr1"; else $class = "mc_form_tr2";
					$GFORM .= "<tr class=\"$class\">\n";
					
					$checkbox = "<input type=\"checkbox\" name=\"$name\" value=\"$k\" $check";
					$checkbox .= $this->option('id')?"id=\"{$this->option('id')}\" ":"";
					$checkbox .= $this->option('classField')?"class=\"{$this->option('classField')}\" ":"";
					$checkbox .= $this->option('readonly')?"readonly=\"readonly\" ":"";
					$checkbox .= $this->option('js')?$this->option('js')." ":"";
					$checkbox .= $this->option('other')?$this->option('other')." ":"";
					$checkbox .= " />";

					if($this->option("checkPosition")=='left') {
						$GFORM .= "<td style=\"text-align:left\">$checkbox</td>";
						$GFORM .= "<td>$v</td>";
					}
					else {
						$GFORM .= "<td>$v</td>";
						$GFORM .= "<td style=\"text-align:right\">$checkbox</td>";
					}
										
					$GFORM .= "</tr>\n";
					
					$odd = !$odd;
					$i++;
				}
				$GFORM .= "</table>\n";
			}
			else $GFORM .= _("non risultano scelte disponibili");
		}
		
		if($this->option("maxHeight")) $GFORM .= "</div>\n";
		$GFORM .= "</td>\n";
		$GFORM .= "</tr>\n";
		
		return $GFORM;
	}
	
		
	/**
	 * Select (ALL)
	 *
	 * @param string $name		nome input
	 * @param string $value		valore da 'modifica'
	 * @param string $label		testo <label>
	 * @param string $required	campo obbligatorio ('req')
	 * @param string $style1	stile <label>
	 * @param string $style2	stile <p>
	 * @param string $style3	stile <select>
	 * @param string $data		query: recupera due campi
	 * 							array: key=>value
	 * @param string $aspect	<select> a capo (v) o in linea (h) rispetto all'etichetta
	 * @param string $js		onchange
	 * @return string
	 */
	public function cselect($name, $value, $data, $label, $options=null) {

		$this->setOptions($options);
		$GFORM = "<tr>\n";
		$GFORM .= "<td class=\"form_label\">".$this->label($name, $label, $this->option('required'), $this->option('classLabel'))."</td>";
		$GFORM .= "<td class=\"form_field\">";
		$GFORM .= $this->select($name, $value, $data, $options);
		if($this->option('text_add')) $GFORM .= $this->option('text_add');
		$GFORM .= "</td>\n";
		$GFORM .= "</tr>\n";
		
		return $GFORM;
	}
	
	public function select($name, $selected, $data, $options) {
		
		$this->setOptions($options);
		$GFORM = "<select name=\"$name\" ";
		$GFORM .= $this->option('id')?"id=\"{$this->option('id')}\" ":"";
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
					if($this->option('maxChars')) $value = cutHtmlText($value, $this->option('maxChars'), '...', true, $this->option('cutWords')?$this->option('cutWords'):false, true);
					$GFORM .= "<option value=\"$key\" ".($key==$selected?"selected=\"selected\"":"").">".$value."</option>\n";
				}
			}
			//else return _("non risultano opzioni disponibili");
		}
		elseif(is_string($data)) {
			$result = mysql_query($data);
			if(mysql_num_rows($result) > 0) {
				while ($row = mysql_fetch_array($result)) {
					if($this->option('maxChars')) $value = cutHtmlText($row[1], $this->option('maxChars'), '...', true, $this->option('cutWords')?$this->option('cutWords'):false, true);
					else $value = $row[1];
					$GFORM .= "<option value=\"".htmlInput($row[0])."\" ".($row[0]==$selected?"selected=\"selected\"":"").">".htmlChars($value)."</option>\n";
				}
			}
			//else return _("non risultano opzioni disponibili");
		}

		$GFORM .= "</select>\n";

		return $GFORM;
	}
	
	/**
	 * Gestione completa del file. Integra il checkbox di eliminazione del file.
	 * Non è gestita l'obbligatorietà del campo.
	 *
	 * @param string $name			nome dell'input file
	 * @param string $value			nome file
	 * @param string $label			testo <label>
	 * @return string
	 * 
	 * Opzioni:
	 * --------------
	 * extensions		array	estensioni valide
	 * classLabel
	 * preview			boolean	mostra l'anteprima di una immagine
	 * previewSrc		string	percorso relativo dell'immagine
	 * del_check		boolean	mostra il check di eliminazione del file
	 * text_add			string	testo aggiuntivo
	 * 
	 * in input()
	 * ...
	 * @param string $style1		stile <label>
	 * @param string $style2		stile <p>
	 * @param integer $size			size
	 * @param integer $max			maxlength
	 * @param string $other			altro nel tag input, ad esempio javascript
	 * @param string $action		insert | modify
	 * @param string $link_action	link ajax
	 * 
	 * @example
	 * $this->_gform->cfile('file1', $filename, _("testo label"), array("extensions"=>$extension, "del_check"=>true, "preview"=>true, "previewSrc"=>/www/address/file1))
	 */
	public function cfile($name, $value, $label, $options){

		$this->setOptions($options);

		$text_add = $this->option('text_add') ? $this->option('text_add') : '';
		$valid_extension = $this->option('extensions');
		
		$text = (is_array($valid_extension) AND sizeof($valid_extension) > 0)? "[".pub::allowedFile($valid_extension)."]":"";
		$finLabel = array();
		$finLabel['label'] = is_array($label) ? $label[0]:$label;
		$finLabel['description'] = (is_array($label) && $label[1]) ? $text."<br/>".$label[1]:$text;
		
		$GFORM = "<tr>\n";
		$GFORM .= "<td class=\"form_label\">".$this->label($name, $finLabel, $this->option('required'), $this->option('classLabel'))."</td>\n";
		$GFORM .= "<td class=\"form_field\">\n";
		if(!empty($value)) {
			$value_link = ($this->option('preview') && $this->option('previewSrc'))
				? "<span onclick=\"Slimbox.open('".$this->option('previewSrc')."')\" class=\"link\">$value</span>"
				:$value;
			$GFORM .= "<div>";
			if($this->option('del_check')) {
				$GFORM .= "<input type=\"checkbox\" name=\"check_del_$name\" value=\"ok\" />";
				$GFORM .= " "._("elimina")." ";
			}
			$GFORM .= _("file caricato").": <b>$value_link</b>";
			$GFORM .= "</div>";
			$GFORM .= "<div style=\"margin-top:10px;\">";
			$GFORM .= $this->input($name, 'file', $value, $options);
			$GFORM .= $text_add;
			$GFORM .= "</div>";

		}
		else
		{
			$GFORM .= $this->input($name, 'file', $value, $options);
			$GFORM .= $text_add;
		}

		$GFORM .= "</td>\n";
		$GFORM .= "</tr>\n";		
		if($value) 
			$GFORM .= $this->hidden('old_'.$name, $value);
		
		return $GFORM;
	}
	
	/*
		Funzioni per i File
	*/
	
	/**
	 * Procedura di gestione dell'upload di un file:
	 *
	 * 1. controllo del file
	 * 
	 * $array_file1 = $this->_gform->verifyFile('filename', $old_file1, $check_file1, true, 'img_', 'thumb_', $path_dir, $this->_extension_attach, $this->_className.'-manageDoc', $link_error);
	 * 
	 * 2. query
	 * 
	 * utilizzare come variabile per il campo del 'nome file': $array_file1[3]
	 * 
	 * 3. operazioni di upload e/o cancellazione
	 * 
	 * if($result) ---> da query
	 * 
	 * $this->_gform->operationFile($array_file1[0], $filename_tmp, $old_file1, $array_file1[2], $array_file1[1], true, 'img_', 'thumb_', 200, 80, $path_dir, $this->_className.'-manageDoc', $link_error);
	 * 
	 * $this->_gform->operationFile($array_file1[0], $filename_tmp, $old_file1, $array_file1[2], $array_file1[1], true, $this->_prefix_img, $this->_prefix_thumb, $this->_height_img, $this->_height_thumb, $directory, $redirect, $link);
	 * 
	 */
	
	// funzioni esterne: searchNameFile(), extension()
	
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
		
		/*
		if(in_array($this->_prefix_file.$media_name, $listFile))
		EvtHandler::HttpCall($this->_home, $this->_className.'-manageDoc', "error=04");
		*/
		
		return $count;
	}
	
	private function upload($file_tmp, $file_name, $uploaddir){
		
		$uploadfile = $uploaddir.$file_name;
		if(move_uploaded_file($file_tmp, $uploadfile)) return true;
		else return false;
	}
	
	/**
	 * Imposta '/' come ultimo carattere della directory
	 *
	 * @param string $directory
	 * @return string
	 */
	private function dirUpload($directory){
		
		$directory = (substr($directory, -1) != '/' && $directory != '') ? $directory.'/' : $directory;
		return $directory;
	}
	
	private function checkFilename($filename, $prefix) {
	
		$filename = preg_replace("#[^a-zA-Z0-9_\.-]#", "_", $filename);
		return $prefix.$filename;
	}
	
	private function linkModify($string){
		
		$string = trim($string);
		
		if(!empty($string) AND $string{strlen($string)-1} != '&')	// substr($string, -1, 1)
		$string = $string.'&';
		
		return $string;
	}
	
	/**
	 * Verifica l'unicità del nome del file e ritorna le indicazioni
	 * di upload e cancellazione
	 *
	 * @param string $name				nome dell'input file
	 * @param string $old_file			nome file esistente
	 * @param boolean $resize			ridimensionamento del file (true|false)
	 * @param array $valid_extension	estensioni lecite di file
	 * @param string $directory			directory di upload (/path/to/directory/)
	 * @param string $link_error		parametri da aggiungere al reindirizzamento
	 * @param string $table				tabella da aggiornare inserendo il nome del file (UPDATE)
	 * @param string $field				nome del campo del file
	 * @param string $idName			nome del campo ID
	 * @param string $id				valore del campo ID
	 * @param array $options
	 * @return boolean
	 * 
	 * Opzioni:
	 * --------------
	 * check_type		integer		1=controlla il tipo di file, 0=non controllare	-> attiva 'types_allowed'
	 * types_allowed	array		array per alcuni tipi di file (mime types)
	 * prefix			string		per fornire un prefisso a prescindere dal ridimensionamento
	 * prefix_file		string		nel caso: $resize = true
	 * prefix_thumb		string		nel caso: $resize = true
	 * max_file_size	integer		dimensione massima di un upload (bytes)
	 * width
	 * thumb_width
	 * height
	 * thumb_height
	 */
	public function manageFile($name, $old_file, $resize, $valid_extension, $directory, $link_error, $table, $field, $idName, $id, $options=null){

		$this->setOptions($options);
		$directory = $this->dirUpload($directory);
		
		if(!is_dir($directory)) mkdir($directory, 0755, true);

		$check_type = !is_null($this->option('check_type')) ? $this->option('check_type') : 1;
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
			"application/x-zip-compressed",
			"application/vnd.ms-excel",
			"application/x-msdos-program",
			"application/octet-stream"
		);
		
		$prefix = !is_null($this->option('prefix')) ? $this->option('prefix') : '';
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
				if($this->option("errorQuery")) mysql_query($this->option("errorQuery"));
				exit(error::errorMessage(array('error'=>33), $link_error));
			}
			
			if(
				!extension($new_file, $valid_extension) ||
				preg_match('#%00#', $new_file) ||
				($check_type == 1 && !in_array( $_FILES[$name]['type'], $types_allowed))
			) {
				if($this->option("errorQuery")) mysql_query($this->option("errorQuery"));
				exit(error::errorMessage(array('error'=>03), $link_error));
			}
			
			$count = $this->countEqualName($new_file, $old_file, $resize, $prefix_file, $prefix_thumb, $directory);
			if($count > 0) {
				if($this->option("errorQuery")) mysql_query($this->option("errorQuery"));
				exit(error::errorMessage(array('error'=>04), $link_error));
			}
		}
		else {$new_file = '';$new_file_tmp = '';}

		$del_file = (isset($this->_requestVar["check_del_$name"]) && $this->_requestVar["check_del_$name"]=='ok');
		$upload = $delete = false;
		$upload = !empty($new_file);
		$delete = (!empty($new_file) && !empty($old_file)) || $del_file; 

		if($delete && $resize)
		{
			if(is_file($directory.$prefix_file.$old_file)) 
				if(!@unlink($directory.$prefix_file.$old_file)) {
					if($this->option("errorQuery")) mysql_query($this->option("errorQuery"));
					exit(error::errorMessage(array('error'=>17), $link_error));
			}
			
			if(!empty($prefix_thumb)) {
				if(is_file($directory.$prefix_thumb.$old_file))
					if(!@unlink($directory.$prefix_thumb.$old_file)) {
						if($this->option("errorQuery")) mysql_query($this->option("errorQuery"));
						exit(error::errorMessage(array('error'=>17), $link_error));
					}
			}
		}
		elseif($delete && !$resize)
		{
			if(is_file($directory.$old_file)) 
				if(!@unlink($directory.$old_file)) {
					if($this->option("errorQuery")) mysql_query($this->option("errorQuery"));
					exit(error::errorMessage(array('error'=>17), $link_error));
				}
		}
		
		if($upload) {
			if(!$this->upload($tmp_file, $new_file, $directory)) { 
				if($this->option("errorQuery")) mysql_query($this->option("errorQuery"));
				exit(error::errorMessage(array('error'=>16), $link_error));
			}
			else $result = true;
		}
		else $result = false;
		
		if($result AND $resize) {
			$new_width = $this->option('width')? $this->option('width'):800;
			$thumb_width = $this->option('thumb_width')? $this->option('thumb_width'):200;
			$new_height = $this->option('height')? $this->option('height'):'';
			$thumb_height = $this->option('thumb_height')? $this->option('thumb_height'):'';

			if(!$this->saveImage($new_file, $directory, $prefix_file, $prefix_thumb, $new_width, $new_height, $thumb_width, $thumb_height)) {
				if($this->option("errorQuery")) mysql_query($this->option("errorQuery"));
				exit(error::errorMessage(array('error'=>18), $link_error));
			}
		}
		
		if($upload) $filename_sql = $new_file;
		elseif($delete) $filename_sql = '';
		else $filename_sql = $old_file;

		$db = new db;
		$query = "UPDATE $table SET $field='$filename_sql' WHERE $idName='$id'";
		$result = $db->actionquery($query);

		if(!$result) {
			if($upload && !$resize) {
				@unlink($directory.$new_file);
			}
			elseif($upload && $resize) {
				@unlink($directory.$prefix_file.$new_file);
				@unlink($directory.$prefix_thumb.$new_file);
			}
			if($this->option("errorQuery")) mysql_query($this->option("errorQuery"));
			exit(error::errorMessage(array('error'=>16), $link_error));
		}

		return true;
	}
	
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
	
	public function saveImage($filename, $directory, $prefix_file, $prefix_thumb, $new_width, $new_height, $thumb_width, $thumb_height){

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

		$img_size = $this->resizeImage($new_width, $new_height, $im_width, $im_height);
		$thumb_size = $this->resizeImage($thumb_width, $thumb_height, $im_width, $im_height);
		
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
			
			if($thumb_size[0] != $im_width AND $thumb_size[1] != $im_height)
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
			return true;
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
			
			if($thumb_size[0] != $im_width AND $thumb_size[1] != $im_height)
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
			return true;
		}
		else
		{
			@unlink($file);
			return false;
		}
	}
	
	// End File
	
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
	 * 
	 * @param $filename
	 * @param $directory
	 * 
	 * Opzioni:
	 * --------------
	 * prefix_file		prefisso del file
	 * prefix_thumb		prefisso del thumb
	 * file_dim			dimensione massima del file (larghezza/altezza) => ridimensionamento
	 * thumb_dim		dimensione massima del thumbnail => ridimensionamento
	 * 
	 * 
	 * Utilizzo
	 * --------------
	 * Caricamento di file da procedura multifile
	 * 
$mfile = new mFile();
$upload = $mfile->mAction('mfile', $directory, $link_error, array('chmod_dir'=>0755, 'overwrite'=>0, 'valid_ext'=>$this->_valid_image));
if(sizeof($upload) > 0)
{
	foreach($upload AS $key=>$value)
	{
		...
		$form = new Form('', '', '');
		$resize = $form->createImage($key, $directory, array('prefix_file'=>$this->_prefix_img, 'prefix_thumb'=>$this->_prefix_thumb, 'file_dim'=>$this->_img_width, 'thumb_dim'=>$this->_thumb_width));
	 * 
	 */
	public function createImage($filename, $directory, $options=array()){

		$prefix_file = array_key_exists('prefix_file', $options) ? $options['prefix_file'] : '';
		$prefix_thumb = array_key_exists('prefix_thumb', $options) ? $options['prefix_thumb'] : '';
		$file_dim = array_key_exists('file_dim', $options) ? $options['file_dim'] : 0;
		$thumb_dim = array_key_exists('thumb_dim', $options) ? $options['thumb_dim'] : 0;
		
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

		$img_size = $this->dimensionFile($file_dim, $im_width, $im_height);
		$thumb_size = $this->dimensionFile($thumb_dim, $im_width, $im_height);
		
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
			
			if($thumb_size[0] != $im_width AND $thumb_size[1] != $im_height)
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
			return true;
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
			
			if($thumb_size[0] != $im_width AND $thumb_size[1] != $im_height)
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
			return true;
		}
		else
		{
			@unlink($file);
			return false;
		}
	}
	
	// Esempio utilizzo:
	// $GFORM .= "<br /><script type=\"text/javascript\" language=\"javascript\">initCounter($('id_elemento'), {$this->option('maxlength')})</script>";
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
	
}

?>
