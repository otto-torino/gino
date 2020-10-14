<?php
/**
 * @file class.Input.php
 * @brief Contiene la definizione ed implementazione della classe Gino.Input
 */
namespace Gino;

use \Gino\View;

require_once LIB_DIR.OS.'datepicker.php';

/**
 * @brief Input form
 */
class Input {

    private static $_view_folder = VIEWS_DIR.OS.'inputs';
    
	/**
	 * @brief Costruttore
	 */
	function __construct() {

    }
    
    /**
     * @brief Imposta la label string
     * @param mixed $value
     * @return string
     */
    private static function setLabelString($value) {
        
        if(is_array($value)) {
            $label = array_key_exists('label', $value) ? $value['label'] : $value[0];
        }
        else {
            $label = $value;
        }
        return $label;
    }
    
    /**
     * @brief Imposta le classi del tag label
     * @param boolean $required
     * @param string $classes
     * @return string
     */
    private static function setLabelClasses($required=false, $classes=null) {
        
        $buffer = '';
        
        if($classes) {
            $buffer .= $classes.' ';
        }
        if($required) {
            $buffer .= 'req';
        }
        return $buffer;
    }
    
    /**
     * @brief Imposta l'helper di un input
     * 
     * @param array $helptext with keys: @a title | @a text
     * @return string|NULL
     */
    private static function setHelper($helptext) {
        
        if(is_array($helptext)) {
            $title = $helptext['title'];
            $text = $helptext['text'];
            
            return "<span class=\"fa fa-question-circle\" 
            data-toggle=\"tooltip\" data-placement=\"right\" 
            title=\"".$title.":\n".$text."\"></span>";
        }
        else {
            return null;
        }
    }
    
    /**
     * @brief Imposta il link di inserimento record correlato a un input
     * @param array $add_related con chiavi: title|id|url
     * @return string|NULL
     */
    private static function setAddRelated($add_related) {
        
        if(is_array($add_related) && count($add_related)) {
            $title = $add_related['title'];
            $id = $add_related['id'];
            $url = $add_related['url'];
            
            $buffer = "<a target=\"_blank\" 
href=\"".$url."\" 
id=\"".$id."\" 
class=\"fa fa-plus-circle form-addrelated\" 
title=\"".attributeVar($title)."\">
</a>";
            $buffer .= "
<script>
$(document).ready(function() {
    $('#' + $.escapeSelector('".$id."')).click(function() {
        return gino.showAddAnotherPopup(this);
    });
});
</script>";
            return $buffer;
        }
        else {
            return null;
        }
    }
    
    /**
     * @brief Imposta la directory del file della vista
     * @param string $custom_folder
     * @return string
     */
    private static function setViewFolder($custom_folder) {
        
        if($custom_folder) {
            $path_to_dir = $custom_folder;
        }
        else {
            $path_to_dir = self::$_view_folder;
        }
        return $path_to_dir;
    }
    
    /**
     * @brief Imposta il file della vista
     * @description Nel caso in cui il form sia inline, il metodo aggiunge '_inline' in coda al nome della vista.
     * 
     * @param string $view_file file di default della vista
     * @param string $custom_file file personalizzato della vista
     * @param boolean $form_inline indica se il form viene mostrato inline
     * @return string
     */
    private static function setViewFile($view_file, $custom_file, $form_inline) {
        
        if($custom_file) {
            $filename = $custom_file;
        }
        else {
            $filename = $view_file;
            if($form_inline) {
                $filename .= '_inline';
            }
        }
        return $filename;
    }
    
	/**
     * @brief LABEL Tag
     *
     * @param string $name nome dell'etichetta
     * @param mixed $text testo dell'etichetta; if array: [label(string), description(string)]
     * @param boolean $required campo obbligatorio
     * @param array $options
     *   - @b classes (string)
     * @return string
     */
    public static function label($name, $text, $required, $options=[]){
        
        $classes = gOpt('classes', $options, null);
        
        $classname = self::setLabelClasses($required, $classes);
        if($classname) {
            $classname = " class=\"".$classname."\"";
        }
        
    	if(is_array($text)) {
    		$label = isset($text['label']) ? $text['label'] : $text[0];
    	}
    	else {
    		$label = $text;
    	}

    	$buffer = "<label for=\"$name\"".$classname.">";
    	$buffer .= $label;
    	$buffer .= "</label>";
    
    	return $buffer;
    }
    
    /**
     * @brief Collegamento all'input form della traduzione
     * @param string $label
     * @param string $onclick
     * @param array $options
     * @return string
     */
    public static function linkTranslation($label, $onclick, $options=[]) {
        
        $classes = gOpt('classes', $options, null);
        
        $view = new View(self::$_view_folder, 'link_translation');
        $dict = [
            'classes' => $classes,
            'label' => $label,
            'onclick' => $onclick
        ];
        
        return $view->render($dict);
    }
    
    /**
     * @brief Contenitore delle righe di input
     *
     * @param mixed $label testo della label (prima colonna); string or array, ad esempio [_("etichetta"), _("spiegazione")]
     * @param string $value contenuto della seconda colonna
     * @param array $options array associativo di opzioni
     *   - @b additional_class (string)
     * @return string
     */
    public static function placeholderRow($label, $value, $options=[]) {
    	
        $additional_class = gOpt('additional_class', $options, null);
        
    	$buffer = '';
    	if(!empty($label) OR !empty($value)) {
    	    $input = $value;
    	}
    	else {
    	    $input = null;
    	}
    	
    	$view = new View(self::$_view_folder, 'input_placeholder');
    	$dict = [
    	    'additional_class' => $additional_class,
    	    'label_string' => self::setLabelString($label),
    	    'label_class' => self::setLabelClasses(),
    	    'value' => $input
    	];
    	
    	return $view->render($dict);
    }
    
    /**
     * @brief Contenitore delle righe di input
     * @todo per vecchie versioni, da eliminare
     * @see placeholderRow()
     * @return string
     */
    public static function noinput($label, $value, $options=[]) {
        
        return self::placeholderRow($label, $value, $options);
    }
    
    /**
     * @brief Input hidden
     *
     * @param string $name nome del tag
     * @param mixed $value valore del tag
     * @param array $options
     *   array associativo di opzioni
     *   - @b id (string): valore ID del tag
     * @return widget html
     */
    public static function hidden($name, $value, $options=array()) {
    
    	$id = gOpt('id', $options, null);
    	
    	return "<input type=\"hidden\" name=\"$name\" value=\"$value\" ".($id ? "id=\"".$id."\"" : '')."/>";
    }
    
    /**
     * @brief Submit Button Tag
     * 
     * @param string $name
     * @param string $value
     * @param array $options
     *   - @b type (string): tipologia di button, valori validi: @a submit (default), @a button
     *     - @a submit processa il form e redirige all'indirizzo specificato
     *     - @b button processa l'onclick e non redirige
     *   - @b classField (string): valore della proprietà class
     *   - @b id (string): valore ID del tag
     *   - @b onclick (string): valore della proprietà onclick
     * @return string
     */
    public static function submit($name, $value, $options=[]) {
        
        $type = gOpt('type', $options, 'submit');
        $classField = gOpt('classField', $options, null);
        $id = gOpt('id', $options, null);
        $onclick = gOpt('onclick', $options, null);
        
        if(!is_string($classField)) {
            $classField = null;
        }
        
        $view = new View(self::$_view_folder, 'input_button');
        $dict = [
            'classes' => $classField,
            'name' => $name,
            'value' => $value,
            'type' => $type,
            'id' => $id,
            'onclick' => $onclick
        ];
        
        return $view->render($dict);
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
     *     - @b helptext (array): help text, @see setHelper()
     *     - @b input_type (string): default @a input
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
    	
    	// for overwrite input type
    	$input_type = gOpt('input_type', $options, 'input');
    	
    	$buffer = "<input type=\"$type\" name=\"$name\" value=\"$value\" ";
    	
    	$buffer .= $id ? "id=\"$id\" ":"";
    	$buffer .= $required ? "required ":"";
    	$buffer .= $pattern ? "pattern=\"$pattern\" ":"";
    	$buffer .= $hint ? "placeholder=\"$hint\" ":"";
    	
    	// Set class
    	$class = '';
    	if($input_type == 'file') {
    	    $class = 'form-control-file';
    	}
    	elseif($input_type == 'input') {
    	    $class = 'form-control';
    	}
    	if($classField) {
    	    $class .= ' '.$classField;
    	}
    	// /Set
    	
    	$buffer .= $class ? "class=\"".trim($class)."\" " : '';
    	$buffer .= $size ? "size=\"$size\" ":"";
    	$buffer .= $maxlength ? "maxlength=\"$maxlength\" ":"";
    	$buffer .= $readonly ? "readonly=\"readonly\" ":"";
    	$buffer .= $js ? $js." ":"";
    	$buffer .= $other ? $other." ":"";
        
    	$buffer .= "/>";
        
    	if(is_array($helptext)) {
    	    $buffer .= self::setHelper(array(
    	        'title' => $helptext['title'],
    	        'text' => $helptext['text']
    	    ));
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
     *   // Grid
     *   - @b additional_class (string)
     *   - @b form_inline (boolean): indica se l'input è da visualizzare inline (default @a false)
     *   - @b custom_folder (string): percorso alla directory personalizzata della vista
     *   - @b custom_file (string): file personalizzato della vista
     * @return string, label + input
     */
    public static function input_label($name, $type, $value, $label, $options=array()){
    
    	$required = gOpt('required', $options, false);
    	$size = gOpt('size', $options, null);
    	$text_add = gOpt('text_add', $options, null);
    	// Grid
    	$additional_class = gOpt('additional_class', $options, null);
    	$form_inline = gOpt('form_inline', $options, false);
    	$custom_folder = gOpt('custom_folder', $options, null);
    	$custom_file = gOpt('custom_file', $options, null);
    	// Translations
    	$trnsl = gOpt('trnsl', $options, false);
    	$trnsl_id = gOpt('trnsl_id', $options, null);
    	$trnsl_table = gOpt('trnsl_table', $options, null);
    	
    	if(is_array($label)) {
    	    $helptext = self::setHelper(array(
    	        'title' => isset($label['label']) ? $label['label'] : $label[0],
    	        'text' => isset($label['description']) ? $label['description'] : $label[1]
    	    ));
    	}
    	else {
    	    $helptext = null;
    	}
    	
    	$input = self::input($name, $type, $value, $options);
    	
    	if($trnsl && $trnsl_id && $trnsl_table) {
    	    $trnsl_input = Form::formFieldTranslation('input', $trnsl_table, $name, $trnsl_id, $size);
    	}
    	else {
    	    $trnsl_input = null;
    	}
    	
    	$view = new View(self::setViewFolder($custom_folder), self::setViewFile('input_text', $custom_file, $form_inline));
    	$dict = [
    	    'additional_class' => $additional_class,
    	    'label_for' => $name,
    	    'label_string' => self::setLabelString($label),
    	    'label_class' => self::setLabelClasses($required),
    	    'input' => $input,
    	    'trnsl_input' => $trnsl_input, 
    	    'text_add' => $text_add,
    	    'helper' => $helptext
    	];
    	
    	return $view->render($dict);
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
     *   - @b datepickers (array): opzioni del calendario (@see lib/datepicker.php)
     *   
     *   - @b additional_class (string)
     *   - @b form_inline (boolean): indica se l'input è da visualizzare inline (default @a false)
     *   - @b custom_folder (string): percorso alla directory personalizzata della vista
     *   - @b custom_file (string): file personalizzato della vista
     * @return string, input + label
     */
    public static function input_date($name, $value, $label, $options=array()){
        
        //$inputClickEvent = gOpt('inputClickEvent', $options, false);
        $required = gOpt('required', $options, false);
        $text_add = gOpt('text_add', $options, null);
        $datepickers = gOpt('datepickers', $options, []);
        // Grid
        $additional_class = gOpt('additional_class', $options, null);
        $form_inline = gOpt('form_inline', $options, false);
        $custom_folder = gOpt('custom_folder', $options, null);
        $custom_file = gOpt('custom_file', $options, null);
        
        $options['id'] = $name;
        $options['size'] = 10;
        $options['maxlength'] = 10;
        $options['pattern'] = "^\d\d/\d\d/\d\d\d\d$";
        $options['hint'] = _("dd/mm/yyyy");
        
        if(is_array($label)) {
            $helptext = self::setHelper(array(
                'title' => isset($label['label']) ? $label['label'] : $label[0],
                'text' => isset($label['description']) ? $label['description'] : $label[1]
            ));
        }
        else {
            $helptext = null;
        }
        
        $input = self::input($name, 'text', $value, $options);
        
        $view = new View(self::setViewFolder($custom_folder), self::setViewFile('input_date', $custom_file, $form_inline));
        $dict = [
            'additional_class' => $additional_class,
            'label_for' => $name,
            'label_string' => self::setLabelString($label),
            'label_class' => self::setLabelClasses($required),
            'input' => $input,
            'text_add' => $text_add,
            'helper' => $helptext,
            'datepicker' => \Gino\getDatePicker($name, $datepickers)
        ];
        
        return $view->render($dict);
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
     *   
     *   - @b additional_class (string)
     *   - @b form_inline (boolean): indica se l'input è da visualizzare inline (default @a false)
     *   - @b custom_folder (string): percorso alla directory personalizzata della vista
     *   - @b custom_file (string): file personalizzato della vista
     * @return string, textarea + label
     */
    public static function textarea_label($name, $value, $label, $options=array()){
    
    	$required = gOpt('required', $options, false);
    	$cols = gOpt('cols', $options, null);
    	$text_add = gOpt('text_add', $options, null);
    	// Grid
    	$additional_class = gOpt('additional_class', $options, null);
    	$form_inline = gOpt('form_inline', $options, false);
    	$custom_folder = gOpt('custom_folder', $options, null);
    	$custom_file = gOpt('custom_file', $options, null);
    	// Translations
    	$trnsl = gOpt('trnsl', $options, false);
    	$trnsl_id = gOpt('trnsl_id', $options, null);
    	$trnsl_table = gOpt('trnsl_table', $options, null);
    	
    	if(is_array($label)) {
    	    $options['helptext'] = array(
    	        'title' => isset($label['label']) ? $label['label'] : $label[0],
    	        'text' => isset($label['description']) ? $label['description'] : $label[1]
    	    );
    	}
    	
    	$input = self::textarea($name, $value, $options);
    	
    	if($trnsl && $trnsl_id && $trnsl_table) {
    	    $trnsl_input = Form::formFieldTranslation('textarea', $trnsl_table, $name, $trnsl_id, $cols);
    	}
    	else {
    	    $trnsl_input = null;
    	}
    	
    	$view = new View(self::setViewFolder($custom_folder), self::setViewFile('input_textarea', $custom_file, $form_inline));
    	$dict = [
    	    'additional_class' => $additional_class,
    	    'label_for' => $name,
    	    'label_string' => self::setLabelString($label),
    	    'label_class' => self::setLabelClasses($required),
    	    'input' => $input,
    	    'trnsl_input' => $trnsl_input,
    	    'text_add' => $text_add
    	];
    	
    	return $view->render($dict);
    }
    
    /**
     * @brief Textarea
     * @description Gestisce anche l'input editor.
     *
     * @see Gino.CKEditor::replace()
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
    	$img_preview = gOpt('img_preview', $options, false);
    	$form_id = gOpt('form_id', $options, null);
    	// Translations
    	$trnsl = gOpt('trnsl', $options, false);
    	$trnsl_id = gOpt('trnsl_id', $options, null);
    	$trnsl_table = gOpt('trnsl_table', $options, null);
    	
    	if($ckeditor && !$id) $id = $name;
    
    	$buffer = '';
    
    	$textarea = "<textarea name=\"$name\" ";
    	$textarea .= $id ? "id=\"$id\" " : "";
    	$textarea .= $required ? "required=\"required\" ":"";
    	
    	// Set class
    	$class = 'form-control';
    	if($classField) {
    	    $class .= ' '.$classField;
    	}
    	// /Set
    	
    	$textarea .= $class ? "class=\"".$class."\" " : '';
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
    		
    		$text_note = null;
    		$trigger_modal = null;
    		$script_modal = null;
    		$render_modal = null;
    		$trnsl_input = null;
    		
    		if($ckeditor_container)
    		{
    			if($notes) {
    				$text_note = "[Enter] "._("inserisce un &lt;p&gt;");
    				$text_note .= " - [Shift+Enter] "._("inserisce un &lt;br&gt;");
    			}
    			
    			if($img_preview) {
    			    $modal = new \Gino\Modal(['modal_id' => "attModal", 'modal_title_id' => 'attModalTitle']);
    			    $modal->setModalTrigger('attachmentModalList');
    			    $trigger_modal = $modal->getModalTrigger();
    			    
    			    $router = \Gino\Router::instance();
    			    $script_modal = $modal->loadDinamycData($router->link('attachment', 'editorList'));
    			    $render_modal = $modal->render('Elenco Allegati', null, ['size_modal' => 'extra-large']);
    			}
    			
    			if($trnsl && $trnsl_id && $trnsl_table) {
    			    $trnsl_input = Form::formFieldTranslation('editor', $trnsl_table, $name, $trnsl_id, $width, $ckeditor_toolbar);
    			}
    		}
    		
    		require_once CLASSES_DIR.OS.'class.CKEditor.php';
    		
    		$input = $textarea;
    		$input .= \Gino\CKEditor::replace($name, $value, ['toolbar' => $ckeditor_toolbar, 'width' => $width, 'height' => $height]);
    		
    		$view = new View(self::$_view_folder, 'input_ckeditor');
    		$dict = [
    		    'text_note' => $text_note,
    		    'label_for' => $name,
    		    'label_string' => self::setLabelString($label),
    		    'label_class' => self::setLabelClasses($required),
    		    'input' => $input,
    		    'trnsl_input' => $trnsl_input,
    		    'text_add' => $text_add,
    		    'img_preview' => $img_preview,
    		    'trigger_modal' => $trigger_modal,
    		    'script_modal' => $script_modal,
    		    'render_modal' => $render_modal
    		];
    		
    		$buffer .= $view->render($dict);
    	}
    	else
    	{
    		$buffer .= $textarea;
    		
    		if(is_array($helptext)) {
    		    $buffer .= self::setHelper(array(
    		        'title' => $helptext['title'],
    		        'text' => $helptext['text']
    		    ));
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
     * @return string
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
     *   - @b required (boolean): campo obbligatorio (default false)
     *   - @b text_add (string): testo da aggiungere dopo il checkbox
     *   - @b inline (boolean): default false
     *   - @b show_label (boolean): default false
     *   - @b return_string (boolean): default false
     *   // Grid
     *   - @b additional_class (string)
     *   - @b form_inline (boolean): indica se l'input è da visualizzare inline (default @a false)
     *   - @b custom_folder (string): percorso alla directory personalizzata della vista
     *   - @b custom_file (string): file personalizzato della vista
     * @return string, input + label
     */
    public static function checkbox_label($name, $checked, $value, $label, $options=array()){
    
    	$required = gOpt('required', $options, false);
    	$text_add = gOpt('text_add', $options, null);
    	$inline = gOpt('inline', $options, false);
    	$show_label = gOpt('show_label', $options, false);
    	$return_string = gOpt('return_string', $options, false);
    	// Grid
    	$additional_class = gOpt('additional_class', $options, null);
    	$form_inline = gOpt('form_inline', $options, false);
    	$custom_folder = gOpt('custom_folder', $options, null);
    	$custom_file = gOpt('custom_file', $options, null);
    	
    	$options['return_string'] = $return_string;
    	$options['show_label'] = $show_label;
    	$options['label'] = $label;
    	$options['inline'] = $inline;
    	$input = self::checkbox($name, $checked, $value, $options);
    	
    	$view = new View(self::setViewFolder($custom_folder), self::setViewFile('input_radio', $custom_file, $form_inline));
    	$dict = [
    	    'additional_class' => $additional_class,
    	    // the legend parameters are the same as the label
    	    'legend_string' => self::setLabelString($label),
    	    'legend_class' => self::setLabelClasses($required),
    	    'checkboxes' => $input,
    	    'inline' => $inline,
    	    'text_add' => $text_add
    	];
    	
    	return $view->render($dict);
    }
    
    /**
     * @brief Input (single) checkbox
     *
     * @param string $name nome input
     * @param boolean $checked valore selezionato
     * @param mixed $value valore del tag input
     * @param array $options array associativo di opzioni
     *   - @b id (string): valore ID del tag input
     *   - @b classField (string): nome della classe del tag input
     *   - @b js (string): javascript
     *   - @b other (string): altro nel tag
     *   - @b show_label (boolean): default @a true
     *   - @b label (mixed): string or array
     *   - @b return_string (boolean): default @a true
     *   - @b show_bootstrap (boolean): permette di non richiamare la classe di bootstrap @a form-check-input; default @a true
     * @return mixed, array [input=>string, label=>string] or string
     */
    public static function checkbox($name, $checked, $value, $options=array()){
    
    	$id = gOpt('id', $options, null);
    	$classField = gOpt('classField', $options, null);
    	$js = gOpt('js', $options, null);
    	$other = gOpt('other', $options, null);
    	$show_label = gOpt('show_label', $options, true);
    	$label = gOpt('label', $options, null);
    	$return_string = gOpt('return_string', $options, true);
    	$show_bootstrap = gOpt('show_bootstrap', $options, true);
    	
    	// Set class
    	$class = '';
    	if($show_bootstrap) {
    	    $class = 'form-check-input';
    	}
    	if($classField) {
    	    $class .= ' '.$classField;
    	}
    	// /Set
    	
    	$input = "<input type=\"checkbox\" name=\"$name\" value=\"$value\" ".($checked ? "checked=\"checked\"":"")." ";
    	$input .= $id ? "id=\"$id\" ":"";
    	$input .= $class ? "class=\"".trim($class)."\" " : '';
    	$input .= $js ? $js." ":"";
    	$input .= $other ? $other." ":"";
    	$input .= "/>";
    	
    	// Set Label
    	$label_string = null;
    	
    	if((is_array($label) && count($label)) or $label) {
    	    $label_string = self::setLabelString($label);
    	    
    	    if($show_label) {
    	        $label_string = "<label class=\"form-check-label\" for=\"$id\">$label_string</label>";
    	    }
    	}
    	// /Label
        
    	if($return_string) {
    	    $buffer = $input;
    	    if($label_string) {
    	        $buffer .= ' '.$label_string;
    	    }
    	    return $buffer;
    	}
    	else {
    	    return ['input' => $input, 'label' => $label];
    	}
    }
    
    /**
     * @brief Input file con label
     * @description Integra il checkbox di eliminazione del file e non è gestita l'obbligatorietà del campo.
     * Se il campo è readonly viene mostrato soltanto il nome del file.
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
     *   - @b readonly (boolean): campo di sola lettura (@see placeholderRow())
     *   // Grid
     *   - @b additional_class (string)
     *   - @b form_inline (boolean): indica se l'input è da visualizzare inline (default @a false)
     *   - @b custom_folder (string): percorso alla directory personalizzata della vista
     *   - @b custom_file (string): file personalizzato della vista
     * @return string, input file + label
     */
    public static function input_file($name, $value, $label, $options=array()){
    
    	$required = gOpt('required', $options, false);
    	$valid_extension = gOpt('extensions', $options, null);
    	$preview = gOpt('preview', $options, false);
    	$previewSrc = gOpt('previewSrc', $options, null);
    	$text_add = gOpt('text_add', $options, null);
    	$readonly = gOpt('readonly', $options, false);
    	// Grid
    	$additional_class = gOpt('additional_class', $options, null);
    	$form_inline = gOpt('form_inline', $options, false);
    	$custom_folder = gOpt('custom_folder', $options, null);
    	$custom_file = gOpt('custom_file', $options, null);
    	
    	// Readonly field
    	if($readonly) {
    	    return self::placeholderRow($label, $value);
    	}
    	// /Readonly
        
    	$text = (is_array($valid_extension) AND sizeof($valid_extension) > 0) ? "[".(count($valid_extension) ? implode(', ', $valid_extension) : _("non risultano formati permessi."))."]":"";
    	$finLabel = array();
    	$finLabel['label'] = is_array($label) ? $label[0]:$label;
    	$finLabel['description'] = (is_array($label) && $label[1]) ? $text."<br/>".$label[1]:$text;
        
    	if(is_array($finLabel)) {
    		$helptext = self::setHelper(array(
    		    'title' => _('Formati consentiti'),
    		    'text' => $finLabel['description']
    		));
    	}
    	else {
    	    $helptext = null;
    	}
    	
    	$options['input_type'] = 'file';
    	$checkbox_delete = null;
    	$value_link = null;
        
    	if(!empty($value)) {
    		
    		if($preview && $previewSrc) {
    			if(self::isImage($previewSrc)) {
    				
    			    $modal_body = "<img src=\"$previewSrc\" />";
    			    
    			    $modal = new \Gino\Modal([
    			        'modal_id' => $name.'ModalCenter',
    			        'modal_title_id' => $name.'ModalCenterTitle',
    			    ]);
    				$value_link = $modal->trigger($value, ['class' => 'btn btn-secondary btn-sm']);
    				$value_link .= $modal->render(_("Media"), $modal_body);
    			}
    			else {
    				$value_link = sprintf('<a target="_blank" href="%s">%s</a>', $previewSrc, $value);
    			}
    		}
    		else {
    			$value_link = $value;
    		}
    		
    		// Ridefinizione dell'opzione required
    		$options['required'] = FALSE;
    		
    		$input = self::input($name, 'file', $value, $options);
    		
    		$form_file_check = true;
    		if(!$required) {
    			$checkbox_delete = "<input type=\"checkbox\" name=\"check_del_$name\" value=\"ok\" />";
    		}
    	}
    	else
    	{
    	    $form_file_check = false;
    	    $input = self::input($name, 'file', $value, $options);
    	}
        
    	if($value) {
    		$input_hidden = self::hidden('old_'.$name, $value);
    	}
    	else {
    	    $input_hidden = null;
    	}
    	
    	$view = new View(self::setViewFolder($custom_folder), self::setViewFile('input_file', $custom_file, $form_inline));
    	$dict = [
    	    'additional_class' => $additional_class,
    	    'label_for' => $name,
    	    'label_string' => self::setLabelString($finLabel['label']),
    	    'label_class' => self::setLabelClasses($required),
    	    'input' => $input,
    	    'form_file_check' => $form_file_check,
    	    'checkbox_delete' => $checkbox_delete,
    	    'link_file' => $value_link,
    	    'input_hidden' => $input_hidden,
    	    'text_add' => $text_add,
    	    'helper' => $helptext
    	];
    	
    	return $view->render($dict);
    }
    
    /**
     * @brief Controlla che il path sia di un'immagine
     * @param string $path
     * @return TRUE se immagine, FALSE altrimenti
     */
    private static function isImage($path) {
    
    	$info = pathinfo($path);
    
    	return in_array(strtolower($info['extension']), array('jpg', 'jpeg', 'png', 'gif', 'bmp', 'tif'));
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
     *   - @b add_related (array): @see SetAddRelated()
     *   // Grid
     *   - @b additional_class (string)
     *   - @b form_inline (boolean): indica se l'input è da visualizzare inline (default @a false)
     *   - @b custom_folder (string): percorso alla directory personalizzata della vista
     *   - @b custom_file (string): file personalizzato della vista
     *   opzioni delle traduzioni
     *   - @b table (string): nome della tabella con il campo da tradurre
     *   - @b field (mixed): nome o nomi dei campi da recuperare
     *     - string: nome del campo con il testo da tradurre
     *     - array: nomi dei campi da concatenare
     *   - @b idName (string): nome del campo di riferimento (default id)
     * @return string, input multicheck + label
     */
    public static function multipleCheckbox($name, $checked, $data, $label, $options=[]){
    
    	$id = gOpt('id', $options, null);
    	$required = gOpt('required', $options, false);
    	$classField = gOpt('classField', $options, null);
    	$readonly = gOpt('readonly', $options, false);
    	$js = gOpt('js', $options, null);
    	$other = gOpt('other', $options, null);
    	$checkPosition = gOpt('checkPosition', $options, null);
    	$encode_html = gOpt('encode_html', $options, true);
    	$add_related = gOpt('add_related', $options, null);
    	// Grid
    	$additional_class = gOpt('additional_class', $options, null);
    	$form_inline = gOpt('form_inline', $options, false);
    	$custom_folder = gOpt('custom_folder', $options, null);
    	$custom_file = gOpt('custom_file', $options, null);
    	
    	$table = gOpt('table', $options, null);
    	$field = gOpt('field', $options, null);
    	$idName = gOpt('idName', $options, 'id');
    	
    	if(!is_array($checked)) {
    		$checked = array();
    	}
    	
    	if(is_array($label)) {
    	    $helptext = self::setHelper(array(
    	        'title' => isset($label['label']) ? $label['label'] : $label[0],
    	        'text' => isset($label['description']) ? $label['description'] : $label[1]
    	    ));
    	}
    	else {
    	    $helptext = null;
    	}
        
    	$multicheck = "<div class=\"table-wrapper-scroll-y form-multicheck\">\n";
    	$multicheck .= "<table class=\"table table-hover table-striped table-bordered\">\n";
    	
    	if(is_string($data))
    	{
    		$db = Db::instance();
    		$a = $db->select(null, null, null, array('custom_query' => $data));
    		if(sizeof($a) > 0)
    		{
    		    $multicheck .= "<thead>";
    			if(sizeof($data) > 10) {
    			    $multicheck .= "<tr>";
    			    $multicheck .= "<th class=\"light\">"._("Filtra")."</th>";
    			    $multicheck .= "<th class=\"light\"><input type=\"text\" class=\"no-check no-focus-padding\" size=\"6\" onkeyup=\"gino.filterMulticheck($(this), $(this).parent('.form-multicheck')[0])\" /></th>";
    			    $multicheck .= "</tr>";
    			}
    			$multicheck .= "<tr>";
    			$multicheck .= "<th class=\"light\">"._("Seleziona tutti/nessuno")."</th>";
    			$multicheck .= "<th style=\"text-align: right\" class=\"light\"><input type=\"checkbox\" onclick=\"gino.checkAll($(this), $(this).parent('.form-multicheck')[0]);\" /></th>";
    			$multicheck .= "</tr>";
    			$multicheck .= "</thead>";
    			
    			foreach($a AS $b)
    			{
    				$b = array_values($b);
    				$val1 = $b[0];
    				$val2 = $b[1];
                    
    				if(in_array($val1, $checked)) $check = true; else $check = false;
    				
    				$checkbox = self::checkbox($name, $check, $val1, [
    				    'classField' => $classField, 
    				    'readonly' => $readonly,
    				    'id' => $id, 
    				    'js' => $js, 
    				    'other' => $other, 
    				    'show_label' => false,
    				    'show_bootstrap' => false
    				]);
                    
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
    				
    				$multicheck .= "<tr>\n";
    				
    				if($checkPosition == 'left') {
    				    $multicheck .= "<td style=\"text-align:left\">$checkbox</td>";
    				    $multicheck .= "<td>".$value_name."</td>";
    				}
    				else {
    				    $multicheck .= "<td>".$value_name."</td>";
    				    $multicheck .= "<td style=\"text-align:right\">$checkbox</td>";
    				}
    				$multicheck .= "</tr>\n";
    			}
    		}
    		else {
    		    $multicheck .= "<tr><td>"._("non risultano scelte disponibili")."</td></tr>";
    		}
    	}
    	elseif(is_array($data))
    	{
    		$i = 0;
    		if(sizeof($data)>0)
    		{
    		    $multicheck .= "<thead>";
    			if(sizeof($data) > 10) {
    			    $multicheck .= "<tr>";
    			    $multicheck .= "<th class=\"light\">"._("Filtra")."</th>";
    			    $multicheck .= "<th class=\"light\">
<input type=\"text\" class=\"no-check no-focus-padding\" size=\"6\" 
onkeyup=\"gino.filterMulticheck($(this), $(this).parents('.form-multicheck')[0])\" />
</th>";
    			    $multicheck .= "</tr>";
    			}
    			$multicheck .= "<tr>";
    			$multicheck .= "<th class=\"light\">"._("Seleziona tutti/nessuno")."</th>";
    			$multicheck .= "<th style=\"text-align: right\" class=\"light\">
<input type=\"checkbox\" onclick=\"gino.checkAll($(this), $(this).parents('.form-multicheck')[0]);\" />
</th>";
    			$multicheck .= "</tr>";
    			$multicheck .= "</thead>";
    			foreach($data as $k=>$v)
    			{
    				$value_name = $v;
    				if($encode_html && $value_name) $value_name = htmlChars($value_name);
    
    				if(in_array($k, $checked)) $check = true; else $check = false;
    				
    				$checkbox = self::checkbox($name, $check, $k, [
    				    'classField' => $classField,
    				    'readonly' => $readonly,
    				    'id' => $id,
    				    'js' => $js,
    				    'other' => $other,
    				    'show_label' => false,
    				    'show_bootstrap' => false
    				]);
    				
    				$multicheck .= "<tr>\n";
    				
    				if($checkPosition == 'left') {
    				    $multicheck .= "<td style=\"text-align:left\">$checkbox</td>";
    				    $multicheck .= "<td>$value_name</td>";
    				}
    				else {
    				    $multicheck .= "<td>$value_name</td>";
    				    $multicheck .= "<td style=\"text-align:right\">$checkbox</td>";
    				}
    
    				$multicheck .= "</tr>\n";
    
    				$i++;
    			}
    			$multicheck .= "</table>\n";
    		}
    		else {
    		    $multicheck .= "<tr><td>"._("non risultano scelte disponibili")."</td></tr>";
    		}
    	}
    
    	$multicheck .= "</table>\n";
    	$multicheck .= "</div>\n";
    
    	$add_related = self::setAddRelated($add_related);
    	
    	$view = new View(self::setViewFolder($custom_folder), self::setViewFile('input_mcheckbox', $custom_file, $form_inline));
    	$dict = [
    	    'additional_class' => $additional_class,
    	    'legend_string' => self::setLabelString($label),
    	    'legend_class' => self::setLabelClasses($required),
    	    'checkboxes' => $multicheck,
    	    'add_related' => $add_related,
    	    'helper' => $helptext
    	];
    	
    	return $view->render($dict);
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
     * @param mixed $label testo label/legend
     * @param array $options
     *   array associativo di opzioni (aggiungere quelle del metodo radio())
     *   - @b required (boolean): campo obbligatorio (default false)
     *   - @b text_add (boolean): testo da aggiungere dopo i pulsanti radio
     *   - @b inline (boolean): pulsanti radio inline
     *   // Grid
     *   - @b additional_class (string)
     *   - @b form_inline (boolean): indica se l'input è da visualizzare inline (default @a false)
     *   - @b custom_folder (string): percorso alla directory personalizzata della vista
     *   - @b custom_file (string): file personalizzato della vista
     * @return string, input radio + label
     * 
     * ##Viste
     * La viste richiamate sono @a input_radio e @a input_radio_inline (@see self::setViewFile()).
     */
    public static function radio_label($name, $value, $data, $default, $label, $options=array()){
    	
    	$required = gOpt('required', $options, false);
    	$text_add = gOpt('text_add', $options, null);
    	$inline = gOpt('inline', $options, false);
    	$return_string = gOpt('return_string', $options, false);
    	// Grid
    	$additional_class = gOpt('additional_class', $options, null);
    	$form_inline = gOpt('form_inline', $options, false);
    	$custom_folder = gOpt('custom_folder', $options, null);
    	$custom_file = gOpt('custom_file', $options, null);
    	
    	if(is_array($label)) {
    		$options['helptext'] = array(
    			'title' => isset($label['label']) ? $label['label'] : $label[0],
    			'text' => isset($label['description']) ? $label['description'] : $label[1]
    		);
    	}
    	
    	$options['return_string'] = $return_string;
    	$options['inline'] = $inline;
    	$input = self::radio($name, $value, $data, $default, $options);
    	
    	$view = new View(self::setViewFolder($custom_folder), self::setViewFile('input_radio', $custom_file, $form_inline));
    	$dict = [
    	    'additional_class' => $additional_class,
    	    'legend_string' => self::setLabelString($label),
    	    'legend_class' => self::setLabelClasses($required),
    	    'radios' => $input,
    	    'inline' => $inline,
    	    'text_add' => $text_add
    	];
    	
    	return $view->render($dict);
    }
    
    /**
     * @brief Input radio
     *
     * @param string $name nome input
     * @param string $value valore attivo
     * @param array $data elementi dei pulsanti radio (array(value=>label[,]))
     * @param mixed $default valore di default
     * @param array $options
     *     array associativo di opzioni
     *     - @b id (string): valore ID del tag <input>
     *     - @b classField (string): valore CLASS del tag <input>
     *     - @b js (string): javascript
     *     - @b other (string): altro nel tag
     *     - @b helptext (array)
     *     - @b inline (boolean): pulsanti radio inline (default false)
     *     - @b return_string (boolean): default true
     * @return mixed, array or string
     */
    public static function radio($name, $value, $data, $default, $options=array()){
    
    	$id = gOpt('id', $options, null);
    	$classField = gOpt('classField', $options, null);
    	$js = gOpt('js', $options, null);
    	$other = gOpt('other', $options, null);
    	$helptext = gOpt('helptext', $options, null);
    	$inline = gOpt('inline', $options, false);
    	$return_string = gOpt('return_string', $options, true);
    	
    	$buffer = '';
    	$comparison = is_null($value) ? $default : $value;
    	
    	// Set class
    	$class = 'form-check-input';
    	if($classField) {
    	    $class .= ' '.$classField;
    	}
    	// /Set
        
    	$radios = [];
    	
    	if(is_array($data) && count($data)) {
    		$i = 0;
    		foreach($data AS $k => $v) {
    		    
    		    $input = "<input type=\"radio\" name=\"$name\" value=\"$k\" ".(!is_null($comparison) && $comparison==$k?"checked=\"checked\"":"")." ";
    		    $input .= $id ? "id=\"$id\" ":"";
    		    $input .= $class ? "class=\"".$class."\" " : '';
    		    $input .= $js ? $js." ":"";
    		    $input .= $other ? $other." ":"";
    		    $input .= "/>";
    		    
    		    $label = "<label class=\"form-check-label\" for=\"$id\">$v</label>";
    		    
    		    $radios[$i] = ['input' => $input, 'label' => $label]; // 'disabled' => 
    			$i++;
    		}
    	}
    	
    	if($return_string) {
    	    
    	    $buffer = '';
    	    if(count($radios)) {
    	        foreach ($radios AS $radio) {
    	            $buffer .= $radio['input'].' '.$radio['label'];
    	            if($inline) {
    	                $buffer .= "&nbsp;";
    	            }
    	            else {
    	                $buffer .= "<br />";
    	            }
    	        }
    	    }
    	    
    	    if(is_array($helptext) && count($helptext)) {
    	        $buffer .= self::setHelper(array(
    	            'title' => $helptext['title'],
    	            'text' => $helptext['text']
    	        ));
    	    }
    	    return $buffer;
    	}
    	else {
    	    return $radios;
    	}
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
     *   - @b add_related (array): array, @see setAddRelated()
     *   // Grid
     *   - @b additional_class (string)
     *   - @b form_inline (boolean): indica se l'input è da visualizzare inline (default @a false)
     *   - @b custom_folder (string): percorso alla directory personalizzata della vista
     *   - @b custom_file (string): file personalizzato della vista
     * @return string, select + label
     */
    public static function select_label($name, $value, $data, $label, $options=array()) {
    
    	$required = gOpt('required', $options, false);
    	$text_add = gOpt('text_add', $options, null);
    	$add_related = gOpt('add_related', $options, null);
    	// Grid
    	$additional_class = gOpt('additional_class', $options, null);
    	$form_inline = gOpt('form_inline', $options, false);
    	$custom_folder = gOpt('custom_folder', $options, null);
    	$custom_file = gOpt('custom_file', $options, null);
    	
    	if(is_array($label)) {
    		$helptext = self::setHelper(array(
    		    'title' => isset($label['label']) ? $label['label'] : $label[0],
    		    'text' => isset($label['description']) ? $label['description'] : $label[1]
    		));
    	}
    	else {
    	    $helptext = null;
    	}
    	
    	$add_related = self::setAddRelated($add_related);
    	$options['add_related'] = null;
    	
    	$input = self::select($name, $value, $data, $options);
    	
    	$view = new View(self::setViewFolder($custom_folder), self::setViewFile('input_select', $custom_file, $form_inline));
    	$dict = [
    	    'additional_class' => $additional_class,
    	    'label_for' => $name,
    	    'label_string' => self::setLabelString($label),
    	    'label_class' => self::setLabelClasses($required),
    	    'input' => $input,
    	    'text_add' => $text_add,
    	    'helper' => $helptext,
    	    'add_related' => $add_related
    	];
    	
    	return $view->render($dict);
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
     *   - @b cutWords (boolean): gestisce come troncare la stringa quando questa è superiore al numero di caratteri impostato (@a maxChars)
     *     - false (default), visualizza la stringa fino alla parola precedente a quella che ricade nel numero massimo di caratteri impostato
     *     - true, mostra la stringa fino al numero massimo di caratteri
     *   - @b helptext (array)
     *   - @b add_related (array): array, @see setAddRelated()
     *   - @b disabled (boolean): disabilita la selezione dell'input
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
    	$disabled = gOpt('disabled', $options, false);
    	
    	$buffer = "<select name=\"$name\" ";
    	$buffer .= $id ? "id=\"$id\" " : "";
    	$buffer .= $required ? "required " : "";
    	$buffer .= $disabled ? "disabled " : '';
    	
    	// Set class
    	$class = 'form-control';
    	if($classField) {
    	    $class .= ' '.$classField;
    	}
    	// /Set
    	
    	$buffer .= $class ? "class=\"".$class."\" " : '';
    	$buffer .= $size ? "size=\"$size\" " : "";
    	$buffer .= $multiple ? "multiple=\"multiple\" " : "";
    	$buffer .= $js ? $js." " : "";
    	$buffer .= $other ? $other." " : "";
    	$buffer .= ">\n";
    
    	if(!$noFirst) {
    		$buffer .= "<option value=\"\"></option>\n";
    	}
    	elseif($firstVoice) {
    		$buffer .= "<option value=\"".$firstValue."\">".$firstVoice."</option>";
    	}
    
    	if(is_array($data)) {
    		if(sizeof($data) > 0) {
    			foreach ($data as $key=>$value) {
    				if($maxChars) {
    					$value = cutHtmlText($value, $maxChars, '...', TRUE, $cutWords, TRUE);
    				}
    				$value = htmlChars($value);
    
    				$buffer .= "<option value=\"$key\" ".($key==$selected?"selected=\"selected\"":"").">".$value."</option>\n";
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
    				$buffer .= "<option value=\"".htmlInput($val1)."\" ".($val1==$selected?"selected=\"selected\"":"").">".htmlChars($value)."</option>\n";
    			}
    		}
    	}
    
    	$buffer .= "</select>\n";
        
    	if(is_array($helptext) && count($helptext)) {
    	    $buffer .= self::setHelper(array(
    	        'title' => $helptext['title'],
    	        'text' => $helptext['text']
    	    ));
    	}
    	
    	$add_related = self::setAddRelated($add_related);
    	if($add_related) {
    	    $buffer .= ' '.$add_related;
    	}
        
    	return $buffer;
    }
}
