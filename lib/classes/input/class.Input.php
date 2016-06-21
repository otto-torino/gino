<?php
/**
 * @file class.Input.php
 * @brief Contiene la definizione ed implementazione della classe Gino.Input
 *
 * @copyright 2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

/**
 * @brief Input form
 *
 * @copyright 2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class Input {

	/**
	 * @brief Costruttore
	 */
	function __construct() {

    }
    
	/**
     * @brief TAG LABEL
     *
     * @param string $name nome dell'etichetta
     * @param mixed $text testo dell'etichetta, testo o array (array-> array('label'=>_("..."), 'description'=>_("...")))
     * @param boolean $required campo obbligatorio
     * @return label tag, html
     */
    public static function label($name, $text, $required){
    
    	if(!$text) return '<label></label>';
    
    	if(is_array($text)) {
    		$label = isset($text['label']) ? $text['label'] : $text[0];
    	}
    	else {
    		$label = $text;
    	}
    
    	$buffer = "<label for=\"$name\"".($required ? "class=\"req\"":"").">";
    	$buffer .= $label;
    	$buffer .= "</label>";
    
    	return $buffer;
    }
    
    /**
     * @brief Simula un campo ma senza input
     *
     * @param string $label contenuto della prima colonna
     *   - string
     *   - array, ad esempio array(_("etichetta"), _("spiegazione"))
     * @param string $value contenuto della seconda colonna
     * @return codice html riga del form
     */
    public static function noinput($label, $value) {
    	
    	$buffer = '';
    	if(!empty($label) OR !empty($value))
    	{
    		$buffer = "<div class=\"form-row\">";
    		$buffer .= self::label('', $label, FALSE);
    		$buffer .= "<div class=\"form-noinput\">$value</div>\n";
    		$buffer .= "</div>";
    	}
    
    	return $buffer;
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
    public static function hidden($name, $value, $options=array()) {
    
    	$id = gOpt('id', $options, null);
    	
    	return "<input type=\"hidden\" name=\"$name\" value=\"$value\" ".($id ? "id=\"".$id."\"" : '')."/>";
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
     *     - @b required (boolean)
     *     - @b pattern (string): espressione regolare che verifica il valore dell'elemento input
     *     - @b hint (string): placeholder
     *     - @b size (integer): lunghezza del tag
     *     - @b maxlength (integer): numero massimo di caratteri consentito
     *     - @b classField (string): nome della classe del tag
     *     - @b js (string): javascript
     *     - @b readonly (boolean): campo di sola lettura
     *     - @b other (string): altro nel tag
     *     - @b helptext (array): help text (array(title=>string, text=>string))
     * @return widget html
     */
    public static function input($name, $type, $value, $options=array()) {
    
    	$id = gOpt('id', $options, null);
    	$required = gOpt('required', $options, false);
    	$pattern = gOpt('pattern', $options, null);
    	$hint = gOpt('hint', $options, null);
    	$classField = gOpt('classField', $options, null);
    	$size = gOpt('size', $options, null);
    	$maxlength = gOpt('maxlength', $options, null);
    	$readonly = gOpt('readonly', $options, false);
    	$js = gOpt('js', $options, null);
    	$other = gOpt('other', $options, null);
    	$helptext = gOpt('helptext', $options, null);
    	
    	$buffer = "<input type=\"$type\" name=\"$name\" value=\"$value\" ";
    	
    	$buffer .= $id ? "id=\"$id\" ":"";
    	$buffer .= $required ? "required ":"";
    	$buffer .= $pattern ? "pattern=\"$pattern\" ":"";
    	$buffer .= $hint ? "placeholder=\"$hint\" ":"";
    	$buffer .= $classField ? "class=\"$classField\" ":"";
    	$buffer .= $size ? "size=\"$size\" ":"";
    	$buffer .= $maxlength ? "maxlength=\"$maxlength\" ":"";
    	$buffer .= $readonly ? "readonly=\"readonly\" ":"";
    	$buffer .= $js ? $js." ":"";
    	$buffer .= $other ? $other." ":"";
    
    	$buffer .= "/>";
    
    	if(is_array($helptext)) {
    		$title = $helptext['title'];
    		$text = $helptext['text'];
    		$buffer .= " <span class=\"fa fa-question-circle label-tooltipfull\" title=\"".attributeVar($title.'::'.$text)."\"></span>";
    	}
    
    	return $buffer;
    }
    
    /**
     * @brief Input con label
     *
     * @see self::label()
     * @see Form::formFieldTranslation()
     * @param string $name nome input
     * @param string $type valore della proprietà @a type (text)
     * @param string $value valore attivo
     * @param mixed $label testo <label>
     * @param array $options
     *   array associativo di opzioni (aggiungere quelle del metodo input())
     *   - @b required (boolean): campo obbligatorio
     *   - @b size (integer): lunghezza del tag
     *   - @b text_add (string): testo dopo il tag input
     *   - @b trnsl (boolean): attiva la traduzione
     *   - @b trnsl_id (integer): valore id del record del modello
     *   - @b trnsl_table (string): nome della tabella del modello
     * @return codice html riga form, label + input
     */
    public static function input_label($name, $type, $value, $label, $options=array()){
    
    	$required = gOpt('required', $options, false);
    	$size = gOpt('size', $options, null);
    	$text_add = gOpt('text_add', $options, null);
    	
    	$trnsl = gOpt('trnsl', $options, false);
    	$trnsl_id = gOpt('trnsl_id', $options, null);
    	$trnsl_table = gOpt('trnsl_table', $options, null);
    	
    	$buffer = "<div class=\"form-row\">";
    	$buffer .= self::label($name, $label, $required)."\n";
    	
    	if(is_array($label)) {
    		$options['helptext'] = array(
    			'title' => isset($label['label']) ? $label['label'] : $label[0],
    			'text' => isset($label['description']) ? $label['description'] : $label[1]
    		);
    	}
    	$buffer .= self::input($name, $type, $value, $options);
    	if($trnsl && $trnsl_id && $trnsl_table) {
    		$buffer .= Form::formFieldTranslation('input', $trnsl_table, $name, $trnsl_id, $size);
    	}
    	
    	if($text_add) {
    		$buffer .= "<div class=\"form-textadd\">".$text_add."</div>";
    	}
    	$buffer .= "</div>";
    	
    	return $buffer;
    }
    
    /**
     * @brief Input di tipo data con label
     *
     * @see label()
     * @see input()
     * @param string $name nome input
     * @param string $value valore attivo
     * @param mixed $label testo <label>
     * @param array $options
     *   array associativo di opzioni (aggiungere quelle del metodo input())
     *   - @b required (boolean): campo obbligatorio (default false)
     *   - @b inputClickEvent (boolean): per attivare l'evento sulla casella di testo (default false)
     *   - @b text_add (string): testo da aggiungere dopo il tag input
     * @return codice html riga form, input + label
     */
    public static function input_date($name, $value, $label, $options=array()){
    
    	$inputClickEvent = gOpt('inputClickEvent', $options, false);
    	$required = gOpt('required', $options, false);
    	$text_add = gOpt('text_add', $options, null);
    	
    	if($inputClickEvent) {
    		$options['other'] = "onclick=\"gino.printCalendar($(this).getNext('img'), $(this))\"";
    	}
    	$options['id'] = $name;
    	$options['size'] = 10;
    	$options['maxlength'] = 10;
    	$options['pattern'] = "^\d\d/\d\d/\d\d\d\d$";
    	$options['hint'] = _("dd/mm/yyyy");
    	
    	$ico_calendar_path = SITE_IMG."/ico_calendar.png";
    	
    	$buffer = "<div class=\"form-row\">";
    	$buffer .= self::label($name, $label, $required);
    	
    	if(is_array($label)) {
    		$options['helptext'] = array(
    			'title' => isset($label['label']) ? $label['label'] : $label[0],
    			'text' => isset($label['description']) ? $label['description'] : $label[1]
    		);
    	}
    	$buffer .= self::input($name, 'text', $value, $options);
    	$days = "['"._("Domenica")."', '"._("Lunedì")."', '"._("Martedì")."', '"._("Mercoledì")."', '"._("Giovedì")."', '"._("Venerdì")."', '"._("Sabato")."']";
    	$months = "['"._("Gennaio")."', '"._("Febbraio")."', '"._("Marzo")."', '"._("Aprile")."', '"._("Maggio")."', '"._("Giugno")."', '"._("Luglio")."', '"._("Agosto")."', '"._("Settembre")."', '"._("Ottobre")."', '"._("Novembre")."', '"._("Dicembre")."']";
    
    	$buffer .= "<span style=\"margin-left:5px;margin-bottom:2px;cursor:pointer;\" class=\"fa fa-calendar calendar-tooltip\" title=\""._("calendario")."\" id=\"cal_button_$name\" src=\"".$ico_calendar_path."\" onclick=\"gino.printCalendar($(this), $(this).getPrevious('input'), $days, $months)\"></span>";
    	if($text_add) {
    		$buffer .= "<div class=\"form-textadd\">".$text_add."</div>";
    	}
    	$buffer .= "</div>";
    
    	return $buffer;
    }
    
    /**
     * @brief Textarea con label
     *
     * @see label()
     * @see Form::formFieldTranslation()
     * @param string $name nome input
     * @param string $value valore attivo
     * @param string $label testo del tag label
     * @param array $options array associativo di opzioni
     *   opzioni del metodo textarea()
     *   opzioni specifiche
     *   - @b required (boolean): campo obbligatorio (default false)
     *   - @b text_add (string): testo da aggiungere stampato sotto il textarea
     *   - @b cols (integer): numero di colonne
     *   - @b trnsl (boolean): attiva la traduzione
     *   - @b trnsl_id (integer): valore id del record del modello
     *   - @b trsnl_table (string): nome della tabella con il campo da tradurre
     * @return codice htlm riga form, textarea + label
     */
    public static function textarea_label($name, $value, $label, $options=array()){
    
    	$required = gOpt('required', $options, false);
    	$cols = gOpt('cols', $options, null);
    	$text_add = gOpt('text_add', $options, null);
    	
    	$trnsl = gOpt('trnsl', $options, false);
    	$trnsl_id = gOpt('trnsl_id', $options, null);
    	$trnsl_table = gOpt('trnsl_table', $options, null);
    	
    	$buffer = "<div class=\"form-row\">";
    	$buffer .= self::label($name, $label, $required)."\n";
    
    	if(is_array($label)) {
    		$options['helptext'] = array(
    			'title' => isset($label['label']) ? $label['label'] : $label[0],
    			'text' => isset($label['description']) ? $label['description'] : $label[1]
    		);
    	}
    	$buffer .= self::textarea($name, $value, $options);
    	if($trnsl && $trnsl_id && $trnsl_table) {
    		$buffer .= Form::formFieldTranslation('textarea', $trnsl_table, $name, $trnsl_id, $cols);
    	}
    
    	if($text_add) {
    		$buffer .= "<div class=\"form-textadd\">".$text_add."</div>";
    	}
    	$buffer .= "</div>";
    
    	return $buffer;
    }
    
    /**
     * @brief Textarea
     *
     * @see imagePreviewer()
     * @see editorHtml()
     * @param string $name nome input
     * @param string $value valore attivo
     * @param array $options array associativo di opzioni
     *   opzioni del textarea
     *     - @b id (string): valore della proprietà id del tag
     *     - @b required (boolean): campo obbligatorio (default false)
     *     - @b classField (string): nome della classe del tag textarea
     *     - @b rows (integer): numero di righe
     *     - @b cols (integer): numero di colonne
     *     - @b readonly (boolean): campo di sola lettura (default false)
     *     - @b js (string): javascript
     *     - @b other (string): altro nel tag
     *     - @b maxlength (integer): numero massimo di caratteri consentiti \n
     *     - @b helptext (array)
     *       - @a title
     *       - @a text
     *     - @b text_add (boolean): testo aggiuntivo
     *     - @b form_id (string): valore id del tag form (viene utilizzato in combinazione con maxlength)
     *   opzioni dell'editor html
     *     - @b label (string): label
     *     - @b ckeditor (boolean): attiva l'editor html
     *     - @b ckeditor_toolbar (string): nome della toolbar dell'editor html
     *     - @b ckeditor_container (boolean): racchiude l'input editor in un contenitore div
     *     - @b width (string): larghezza dell'editor (pixel o %)
     *     - @b height (integer): altezza dell'editor (pixel)
     *     - @b notes (boolean): mostra le note
     *     - @b img_preview (boolean): mostra il browser di immagini di sistema
     *   opzioni delle traduzioni
     *     - @b trnsl (boolean): attiva la traduzione
     *     - @b trnsl_id (integer): valore id del record del modello
     *     - @b trnsl_table (string): nome della tabella con il campo da tradurre
     * @return string, codice html
     */
    public static function textarea($name, $value, $options=array()){
    
    	$ckeditor = gOpt('ckeditor', $options, false);
    	$id = gOpt('id', $options, null);
    	$required = gOpt('required', $options, false);
    	$classField = gOpt('classField', $options, null);
    	$rows = gOpt('rows', $options, null);
    	$cols = gOpt('cols', $options, null);
    	$readonly = gOpt('readonly', $options, false);
    	$js = gOpt('js', $options, null);
    	$other = gOpt('other', $options, null);
    	$maxlength = gOpt('maxlength', $options, null);
    	$helptext = gOpt('helptext', $options, null);
    	$text_add = gOpt('text_add', $options, null);
    	$img_preview = gOpt('img_preview', $options, null);
    	$form_id = gOpt('form_id', $options, null);
    	
    	$trnsl = gOpt('trnsl', $options, false);
    	$trnsl_id = gOpt('trnsl_id', $options, null);
    	$trnsl_table = gOpt('trnsl_table', $options, null);
    	
    	
    	if($ckeditor && !$id) $id = $name;
    
    	$buffer = '';
    
    	$textarea = "<textarea name=\"$name\" ";
    	$textarea .= $id ? "id=\"$id\" " : "";
    	$textarea .= $required ? "required=\"required\" ":"";
    	$textarea .= $classField ? "class=\"$classField\" ":"";
    	$textarea .= $rows ? "rows=\"$rows\" ":"";
    	$textarea .= $cols ? "cols=\"$cols\" ":"";
    	$textarea .= $readonly ? "readonly=\"readonly\" ":"";
    	$textarea .= $js ? $js." ":"";
    	$textarea .= $other ? $other." ":"";
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
    			$buffer .= self::label($name, $label, $required);
    			
    			if($text_note) $buffer .= "<div>".$text_note."</div>";
    			if($img_preview) $buffer .= self::imagePreviewer();
    		}
    		 
    		$buffer .= $textarea;
    		 
    		$buffer .= self::editorHtml($name, $value, array('toolbar'=>$ckeditor_toolbar, 'width'=>$width, 'height'=>$height));
    		 
    		if($ckeditor_container)
    		{
    			if($trnsl && $trnsl_id && $trnsl_table) {
    				$buffer .= Form::formFieldTranslation('editor', $trnsl_table, $name, $trnsl_id, $width, $ckeditor_toolbar);
    			}
    
    			if($text_add) {
    				$buffer .= "<div class=\"form-textadd\">".$text_add."</div>";
    			}
    			$buffer .= "</div>\n";
    			$buffer .= "</div>\n";
    		}
    	}
    	else
    	{
    		$buffer .= $textarea;
    		 
    		if($helptext) {
    			$title = $helptext['title'];
    			$text = $helptext['text'];
    			$buffer .= " <span class=\"fa fa-question-circle label-tooltipfull\" title=\"".attributeVar($title.'::'.$text)."\"></span>";
    		}
    
    		if(is_int($maxlength) AND $maxlength > 0)	// Limite caratteri con visualizzazione del numero di quelli restanti
    		{
    			$buffer .= self::jsCountCharText();
    			$buffer .= "<script type=\"text/javascript\" language=\"javascript\">initCounter($$('#$form_id textarea[name=$name]')[0], $maxlength)</script>";
    		}
    	}
    
    	return $buffer;
    }
    
    /**
     * @brief Funzione javascript che conta il numero dei caratteri ancora disponibili all'interno di un textarea
     *
     * @code
     * $buffer = "<script type=\"text/javascript\" language=\"javascript\">initCounter($('id_elemento'), maxlength})</script>";
     * @endcode
     *
     * @return codice html
     */
    public static function jsCountCharText(){
    
    	$buffer = "<script type=\"text/javascript\">\n";
    	$buffer .= "
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
    	$buffer .= "</script>";
    	return $buffer;
    }
    
    
    /**
     * @brief Inizializza l'editor visuale CKEditor
     *
     * @param string $name
     * @param string $value
     * @param array $options array associativo di opzioni
     *   - @b toolbar (string): nome della toolbar
     *   - @b width (string): larghezza dell'editor (pixel o %)
     *   - @b height (integer): altezza dell'editor (pixel)
     * @return string, script js
     */
    public static function editorHtml($name, $value, $options=array()){
    
    	$toolbar = gOpt('toolbar', $options, null);
    	$width = gOpt('width', $options, '100%');
    	$height = gOpt('height', $options, 300);
    
    	$height .= 'px';
    
    	if(empty($value)) $value = '';
    	if(!$toolbar) $toolbar = 'Full';
    	
    	$registry = Registry::instance();
    
    	$registry->addCustomJs(SITE_WWW.'/ckeditor/ckeditor.js', array('compress'=>false, 'minify'=>false));
    	
    	// Replace the textarea id $name
    	$buffer = "<script>
    	CKEDITOR.replace('$name', {
    	customConfig: '".SITE_CUSTOM_CKEDITOR."/config.js',
        	contentsCss: '".SITE_CUSTOM_CKEDITOR."/stylesheet.css',
            	toolbar: '$toolbar',
            	width: '$width',
            	height: '$height',
    	});";
    
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
     * @brief Codice per la visualizzazione allegati contestualmente all'editor CKEDITOR
     * @see Gino.App.Attachment.attachment::editorList()
     * @return codice html
     */
    private static function imagePreviewer() {
    
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
    	$buffer = "<p><span class=\"link\" onclick=\"$onclick\">"._("Visualizza file disponibili in allegati")."</span></p>";
    
    	return $buffer;
    }
    
    /**
     * @brief Input checkbox con label
     *
     * @see self::label()
     * @see self::checkbox()
     * @param string $name nome input
     * @param boolean $checked valore selezionato
     * @param mixed $value valore del tag input
     * @param string $label testo <label>
     * @param array $options array associativo di opzioni
     *   opzioni del metodo checkbox()
     *   opzioni specifiche
     *     - @b required (boolean): campo obbligatorio (default false)
     *     - @b text_add (string): testo da aggiungere dopo il checkbox
     * @return codice html riga form, input + label
     */
    public static function checkbox_label($name, $checked, $value, $label, $options=array()){
    
    	$required = gOpt('required', $options, false);
    	$text_add = gOpt('text_add', $options, null);
    	
    	$buffer = "<div class=\"form-row\">";
    	$buffer .= self::label($name, $label, $required)."\n";
    	$buffer .= self::checkbox($name, $checked, $value, $options);
    	
    	if($text_add) {
    		$buffer .= "<div class=\"form-textadd\">".$text_add."</div>";
    	}
    	$buffer .= "</div>\n";
    	return $buffer;
    }
    
    /**
     * @brief Input checkbox
     *
     * @param string $name nome input
     * @param boolean $checked valore selezionato
     * @param mixed    $value valore del tag input
     * @param array $options array associativo di opzioni
     *   - @b id (string): valore ID del tag input
     *   - @b classField (string): nome della classe del tag input
     *   - @b js (string): javascript
     *   - @b other (string): altro nel tag
     * @return widget html
     */
    public static function checkbox($name, $checked, $value, $options=array()){
    
    	$id = gOpt('id', $options, null);
    	$classField = gOpt('classField', $options, null);
    	$js = gOpt('js', $options, null);
    	$other = gOpt('other', $options, null);
    	
    	$buffer = "<input type=\"checkbox\" name=\"$name\" value=\"$value\" ".($checked ? "checked=\"checked\"":"")." ";
    	$buffer .= $id ? "id=\"$id\" ":"";
    	$buffer .= $classField ? "class=\"$classField\" ":"";
    	$buffer .= $js ? $js." ":"";
    	$buffer .= $other ? $other." ":"";
    	$buffer .= "/>";
    
    	return $buffer;
    }
    
    /**
     * @brief Input file con label
     * @description Integra il checkbox di eliminazione del file e non è gestita l'obbligatorietà del campo.
     *
     * @see self::label()
     * @param string $name nome input
     * @param string $value nome del file
     * @param string $label testo <label>
     * @param array $options
     *   array associativo di opzioni (aggiungere quelle del metodo input())
     *   - @b required (boolean): campo obbligatorio (default false)
     *   - @b extensions (array): elenco delle estensioni valide
     *   - @b preview (boolean): mostra l'anteprima di una immagine (default false)
     *   - @b previewSrc (string): percorso relativo dell'immagine
     *   - @b text_add (string): testo da aggiungere in coda al tag input
     * @return codice html riga form, input file + label
     */
    public static function input_file($name, $value, $label, $options=array()){
    
    	$required = gOpt('required', $options, false);
    	$valid_extension = gOpt('extensions', $options, null);
    	$preview = gOpt('preview', $options, false);
    	$previewSrc = gOpt('previewSrc', $options, null);
    	$text_add = gOpt('text_add', $options, null);
    
    	$text = (is_array($valid_extension) AND sizeof($valid_extension) > 0) ? "[".(count($valid_extension) ? implode(', ', $valid_extension) : _("non risultano formati permessi."))."]":"";
    	$finLabel = array();
    	$finLabel['label'] = is_array($label) ? $label[0]:$label;
    	$finLabel['description'] = (is_array($label) && $label[1]) ? $text."<br/>".$label[1]:$text;
    
    	$GFORM = "<div class=\"form-row\">";
    	$GFORM .= self::label($name, $finLabel, $required)."\n";
    
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
    		$value_link = ($preview && $previewSrc)
    		? (self::isImage($previewSrc)
    			? sprintf('<span onclick="Slimbox.open(\'%s\')" class="link">%s</span>', $previewSrc, $value)
    			: sprintf('<a target="_blank" href="%s">%s</a>', $previewSrc, $value))
    			: $value;
    		
    		// Ridefinizione dell'opzione required
    		$options['required'] = FALSE;
    		
    		$GFORM .= self::input($name, 'file', $value, $options);
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
    		$GFORM .= self::input($name, 'file', $value, $options);
    		$GFORM .= $text_add;
    	}
    
    	if($value) {
    		$GFORM .= self::hidden('old_'.$name, $value);
    	}
    
    	$GFORM .= "</div>";
    
    	return $GFORM;
    }
    
    /**
     * @brief Controlla che il path sia di un'immagine
     * @param string $path
     * @return TRUE se immagine, FALSE altrimenti
     */
    private static function isImage($path) {
    
    	$info = pathinfo($path);
    
    	return in_array($info['extension'], array('jpg', 'jpeg', 'png', 'gif', 'bmp', 'tif'));
    }
    
    /**
     * @brief Iput checkbox multiplo (many to many) con label
     *
     * @param string $name nome input
     * @param array $checked valori degli elementi selezionati
     * @param mixed $data
     *   - string, query
     *   - array, elementi del checkbox (value_check=>text)
     * @param string $label testo del tag label
     * @param array $options
     *   array associativo di opzioni
     *   - @b id (string)
     *   - @b classField (string)
     *   - @b readonly (boolean)
     *   - @b js (string)
     *   - @b other (string)
     *   - @b required (string): campo obbligatorio (default false)
     *   - @b checkPosition (stringa): posizionamento del checkbox (left|right)
     *   - @b encode_html (boolean): attiva la conversione del testo dal database ad html (default TRUE)
     *   - @b add_related (array): array(title=>string, id=>int, url=>string)
     *   opzioni delle traduzioni
     *   - @b table (string): nome della tabella con il campo da tradurre
     *   - @b field (mixed): nome o nomi dei campi da recuperare
     *     - string: nome del campo con il testo da tradurre
     *     - array: nomi dei campi da concatenare
     *   - @b idName (string): nome del campo di riferimento (default id)
     * @return codice html riga form, input multicheck + label
     */
    public static function multipleCheckbox($name, $checked, $data, $label, $options=array()){
    
    	$id = gOpt('id', $options, null);
    	$required = gOpt('required', $options, false);
    	$classField = gOpt('classField', $options, null);
    	$readonly = gOpt('readonly', $options, false);
    	$js = gOpt('js', $options, null);
    	$other = gOpt('other', $options, null);
    	$checkPosition = gOpt('checkPosition', $options, null);
    	$encode_html = gOpt('encode_html', $options, true);
    	$add_related = gOpt('add_related', $options, null);
    	
    	$table = gOpt('table', $options, null);
    	$field = gOpt('field', $options, null);
    	$idName = gOpt('idName', $options, 'id');
    	
    	if(!is_array($checked)) {
    		$checked = array();
    	}
    	
    	$GFORM = "<div class=\"form-row\">";
    	$GFORM .= self::label($name, $label, $required)."\n";
    
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
    		$db = Db::instance();
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
    				$checkbox .= $id ?"id=\"$id\" ":"";
    				$checkbox .= $classField ? "class=\"$classField\" ":"";
    				$checkbox .= $readonly ? "readonly=\"readonly\" ":"";
    				$checkbox .= $js ? $js." ":"";
    				$checkbox .= $other ? $other." ":"";
    				$checkbox .= " />";
    
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
    
    					$record = $db->select($fields." AS v", $table, $idName."='$val1'");
    					if(!$record) {
    						$value_name = '';
    					}
    					else
    					{
    						foreach($record AS $r)
    						{
    							$value_name = $r['v'];
    						}
    					}
    				}
    				elseif(is_string($field)) {
    					$value_name = ModelForm::translationValue($table, $field, $val1, $idName);
    				}
    				else $value_name = '';
    
    				if($encode_html && $value_name) $value_name = htmlChars($value_name);
    
    				if($checkPosition == 'left') {
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
    				$checkbox .= $id ? "id=\"$id\" ":"";
    				$checkbox .= $classField ? "class=\"$classField\" ":"";
    				$checkbox .= $readonly ? "readonly=\"readonly\" ":"";
    				$checkbox .= $js ? $js." ":"";
    				$checkbox .= $other ? $other." ":"";
    				$checkbox .= " />";
    
    				if($checkPosition == 'left') {
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
    	if(is_array($add_related) && count($add_related)) {
    		$title = $add_related['title'];
    		$id = $add_related['id'];
    		$url = $add_related['url'];
    		$GFORM .= " <a target=\"_blank\" href=\"".$url."\" onclick=\"return gino.showAddAnotherPopup($(this))\" id=\"".$id."\" class=\"fa fa-plus-circle form-addrelated\" title=\"".attributeVar($title)."\"></a>";
    	}
    	$GFORM .= "</div>\n";
    
    	return $GFORM;
    }
    
    /**
     * @brief Input radio con label
     *
     * @see self::label()
     * @see self::radio()
     * @param string $name nome input
     * @param string $value valore attivo
     * @param array $data elementi dei pulsanti radio (array(value=>text[,]))
     * @param mixed $default valore di default
     * @param mixed $label testo <label>
     * @param array $options
     *   array associativo di opzioni (aggiungere quelle del metodo radio())
     *   - @b required (boolean): campo obbligatorio (default false)
     *   - @b text_add (boolean): testo da aggiungere dopo i pulsanti radio
     * @return codice html riga form, input radio + label
     */
    public static function radio_label($name, $value, $data, $default, $label, $options=array()){
    	
    	$required = gOpt('required', $options, false);
    	$text_add = gOpt('text_add', $options, null);
    	
    	$GFORM = "<div class=\"form-row\">";
    	$GFORM .= self::label($name, $label, $required)."\n";
    	if(is_array($label)) {
    		$options['helptext'] = array(
    			'title' => isset($label['label']) ? $label['label'] : $label[0],
    			'text' => isset($label['description']) ? $label['description'] : $label[1]
    		);
    	}
    	
    	$GFORM .= self::radio($name, $value, $data, $default, $options);
    	if($text_add) {
    		$GFORM .= "<div class=\"form-textadd\">".$text_add."</div>";
    	}
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
     *     - @b helptext (array)
     * @return widget html
     */
    public static function radio($name, $value, $data, $default, $options=array()){
    
    	$id = gOpt('id', $options, null);
    	$classField = gOpt('classField', $options, null);
    	$js = gOpt('js', $options, null);
    	$other = gOpt('other', $options, null);
    	$aspect = gOpt('aspect', $options, null);
    	$helptext = gOpt('helptext', $options, null);
    	
    	$GFORM = '';
    	$comparison = is_null($value) ? $default : $value;
    	$space = $aspect == 'v'? "<br />" : "&nbsp;";
    	$container = $aspect == 'v'? TRUE : FALSE;
    
    	if($container) {
    		$GFORM .= "<div class=\"form-radio-group\">";
    	}
    	if(is_array($data)) {
    		$i=0;
    		foreach($data AS $k => $v) {
    			$GFORM .= ($i?$space:'')."<input type=\"radio\" name=\"$name\" value=\"$k\" ".(!is_null($comparison) && $comparison==$k?"checked=\"checked\"":"")." ";
    			$GFORM .= $id ? "id=\"$id\" ":"";
    			$GFORM .= $classField ? "class=\"$classField\" ":"";
    			$GFORM .= $js ? $js." ":"";
    			$GFORM .= $other ? $other." ":"";
    			$GFORM .= "/> ".$v;
    			$i++;
    		}
    	}
    	if(is_array($helptext) && count($helptext)) {
    		$title = $helptext['title'];
    		$text = $helptext['text'];
    		$GFORM .= " <span class=\"fa fa-question-circle label-tooltipfull\" title=\"".attributeVar($title.'::'.$text)."\"></span>";
    	}
    	if($container) {
    		$GFORM .= "</div>";
    	}
    
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
     * @param array $options array associativo di opzioni
     *   opzioni del metodo select()
     *   opzioni specifiche
     *   - @b required (boolean): campo obbligatorio (default false)
     *   - @b text_add (string): testo da aggiungere dopo il select
     * @return codice html riga form, select + label
     */
    public static function select_label($name, $value, $data, $label, $options=array()) {
    
    	$required = gOpt('required', $options, false);
    	$text_add = gOpt('text_add', $options, null);
    	
    	$buffer = "<div class=\"form-row\">";
    	$buffer .= self::label($name, $label, $required)."\n";
    	if(is_array($label)) {
    		$options['helptext'] = array(
    			'title' => isset($label['label']) ? $label['label'] : $label[0],
    			'text' => isset($label['description']) ? $label['description'] : $label[1]
    		);
    	}
    	$buffer .= self::select($name, $value, $data, $options);
    	if($text_add) {
    		$buffer .= "<div class=\"form-textadd\">".$text_add."</div>";
    	}
    	$buffer .= "</div>";
    
    	return $buffer;
    }
    
    /**
     * @brief Input select
     *
     * @param string $name nome input
     * @param mixed $selected elemento selezionato
     * @param mixed $data elementi del select (query-> recupera due campi, array-> key=>value)
     * @param array $options
     *   array associativo di opzioni
     *   - @b id (string): ID del tag select
     *   - @b required (boolean): campo obbligatorio (default false)
     *   - @b classField (string): nome della classe del tag select
     *   - @b size (integer)
     *   - @b multiple (boolean): scelta multipla di elementi (default false)
     *   - @b js (string): utilizzare per eventi javascript (ad es. onchange=\"jump\")
     *   - @b other (string): altro da inserire nel tag select
     *   - @b noFirst (boolean): col valore FALSE mostra la prima voce vuota (default false)
     *   - @b firstVoice (string): testo del primo elemento
     *   - @b firstValue (mixed): valore del primo elemento
     *   - @b maxChars (integer): numero massimo di caratteri del testo
     *   - @b cutWords (boolean): taglia l'ultima parola se la stringa supera il numero massimo di caratteri (default false)
     *   - @b helptext (array)
     *   - @b add_related (array): array(title=>string, id=>int, url=>string)
     * @return widget html
     */
    public static function select($name, $selected, $data, $options=array()) {
    
    	$id = gOpt('id', $options, null);
    	$required = gOpt('required', $options, false);
    	$classField = gOpt('classField', $options, null);
    	$size = gOpt('size', $options, null);
    	$multiple = gOpt('multiple', $options, false);
    	$js = gOpt('js', $options, null);
    	$other = gOpt('other', $options, null);
    	$noFirst = gOpt('noFirst', $options, false);
    	$firstVoice = gOpt('firstVoice', $options, null);
    	$firstValue = gOpt('firstValue', $options, null);
    	$maxChars = gOpt('maxChars', $options, null);
    	$cutWords = gOpt('cutWords', $options, false);
    	$helptext = gOpt('helptext', $options, null);
    	$add_related = gOpt('add_related', $options, null);
    	
    	$GFORM = "<select name=\"$name\" ";
    	$GFORM .= $id ? "id=\"$id\" ":"";
    	$GFORM .= $required ? "required ":"";
    	$GFORM .= $classField ? "class=\"$classField\" ":"";
    	$GFORM .= $size ? "size=\"$size\" ":"";
    	$GFORM .= $multiple ? "multiple=\"multiple\" ":"";
    	$GFORM .= $js ? $js." ":"";
    	$GFORM .= $other ? $other." ":"";
    	$GFORM .= ">\n";
    
    	if(!$noFirst) {
    		$GFORM .= "<option value=\"\"></option>\n";
    	}
    	elseif($firstVoice) {
    		$GFORM .= "<option value=\"".$firstValue."\">".$firstVoice."</option>";
    	}
    
    	if(is_array($data)) {
    		if(sizeof($data) > 0) {
    			foreach ($data as $key=>$value) {
    				if($maxChars) {
    					$value = cutHtmlText($value, $maxChars, '...', TRUE, $cutWords, TRUE);
    				}
    				$value = htmlChars($value);
    
    				$GFORM .= "<option value=\"$key\" ".($key==$selected?"selected=\"selected\"":"").">".$value."</option>\n";
    			}
    		}
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
    
    				if($maxChars) {
    					$value = cutHtmlText($val2, $maxChars, '...', TRUE, $cutWords, TRUE);
    				}
    				else {
    					$value = $val2;
    				}
    				$GFORM .= "<option value=\"".htmlInput($val1)."\" ".($val1==$selected?"selected=\"selected\"":"").">".htmlChars($value)."</option>\n";
    			}
    		}
    	}
    
    	$GFORM .= "</select>\n";
    
    	if(is_array($helptext) && count($helptext)) {
    		$title = $helptext['title'];
    		$text = $helptext['text'];
    		$GFORM .= " <span class=\"fa fa-question-circle label-tooltipfull\" title=\"".attributeVar($title.'::'.$text)."\"></span>";
    	}
    
    	if(is_array($add_related) && count($add_related)) {
    		$title = $add_related['title'];
    		$id = $add_related['id'];
    		$url = $add_related['url'];
    		$GFORM .= " <a target=\"_blank\" href=\"".$url."\" onclick=\"return gino.showAddAnotherPopup($(this))\" id=\"".$id."\" class=\"fa fa-plus-circle form-addrelated\" title=\"".attributeVar($title)."\"></a>";
    	}
    
    	return $GFORM;
    }
}
