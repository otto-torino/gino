<?php
/**
 * @file class.SearchInterface.php
 * @brief Contiene la definizione ed implementazione della classe Gino.SearchInterface
 * 
 * @copyright 2016-2020 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 */
namespace Gino;

/**
 * @brief Metodi per gestire le ricerche nelle interfacce utente
 *
 * ##UTILIZZO
 *
 * 1. Impostare l'elenco dei campi sui quali effettuare la ricerca nella variabile @a $search_fields nel file @a utils.php.\n
 * L'elenco dei campi è un array nel quale le chiavi sono i nomi dei campi e i valori sono degli array con le seguenti chiavi valide:\n
 *   - @a label (string), label dell'input
 *   - @a input (string), tipologie di input:
 *     - text (default), costruisce \Gino\Input::input_label
 *     - date, costruisce \Gino\Input::input_date
 *     - select, costruisce \Gino\Input::select_label
 *     - radio, costruisce \Gino\Input::radio_label
 *     - tag, costruisce \Gino\TagInput::input
 *   - @a type (string), tipologia di dato (int, string)
 *   - @a data (array), valori degli input select e radio, ad esempio Category::getForSelect($this)
 *   - @a default (mixed), default dell'input radio
 *   - @a options (array), opzioni degli input (sovrascrivono quelle di default)
 *
 * Il nome dell'input form viene creato unendo il valore dell'opzione @a before_input del costruttore con il nome del parametro.
 *
 * 2. Verificare se l'interfaccia accetta query string (probabilmente di tipo GET) come parametri per restringere l'insieme
 * degli elementi. In questo caso recuperateli e caricateli in un array, ad esempio:
 *
 * @code
 * $ctgslug = \Gino\cleanVar($request->GET, 'ctg', 'string');
 * $tag = \Gino\cleanVar($request->GET, 'tag', 'string');
 *
 * if($ctgslug) {
 *   $ctg = Category::getFromSlug($ctgslug, $this);
 *   $ctg_id = $ctg ? $ctg->id : 0;
 * }else {
 *   $ctg_id = 0;
 * }
 * $query_params = array('category' => $ctg_id, 'tag' => $tag,);
 * @endcode
 *
 * 3. Richiamate il metodo Gino.Controller::setSearchParams e recuperate i valori dei campi (da GET e di ricerca) per utilizzarli
 * nelle condizioni della query.
 *
 * @code
 * $obj_search = $this->setSearchParams($query_params);
 * $search_values = $obj_search->getValues();
 *
 * $conditions = [
 *   'ctg' => array_key_exists('category', $search_values) ? $search_values['category'] : null,
 *   'tag' => $search_values['tag'],
 *   ...
 * ]
 * @endcode
 *
 * 4. Inserire nella vista il form e il collegamento alla sua apertura/chiusura utilizzando i metodi @a form() e @a linkSearchForm():
 * @code
 * $dict = [
 *   ...,
 *   'search_form' => $obj_search->form($this->link($this->_instance_name, 'archive'), 'form_search_post'),
 *   'link_form' => $obj_search->linkSearchForm()
 * ]
 * @endcode
 */
class SearchInterface {

    private $_registry, $_request, $_session;

    /**
     * @brief Elenco dei campi di ricerca nel formato [nome]=>[valori per costruire gli input]
     * @var array
     */
    private $_fields;
    
    /**
     * @brief Elenco dei nomi dei campi di ricerca
     * @var array
     */
    private $_fields_name;

    /**
     * @brief Identificare del contenitore in sessione dei parametri di ricerca
     * @var string
     */
    private $_identifier;
    
    /**
     * Stringa da anteporre al nome dell'input form
     * @var string
     */
    private $_before_input;
    
    /**
     * @brief Indica l'apertura o la chiusura del form di ricerca
     * @var boolean
     */
    private $_open_form;
    
    /**
     * Contiene i valori dei parametri di ricerca passati attraverso l'url
     * @description Gli elementi dell'array sono nel formato [field_name]=>[field_value], 
     * dove field_name deve corrispondere al nome di un campo nella proprietà $_fields.
     * @var array
     */
    private $_param_values;
    
    /**
     * @brief Nome del submit di ricerca
     * @var string
     */
    private $_submit_name;
    
    /**
     * @brief Nome del submit di ricerca di tutti i record
     * @var string
     */
    private $_submit_all_name;

    /**
     * @brief Costruttore
     * 
     * 
 * 
 * Le opzioni valide per ogni tipo di campo di ricerca sono: \n
 *   - @a label (string), label dell'input
 *   - @a input (string), tipologie di input:
 *     - text (default), costruisce \Gino\Input::input_label
 *     - date, costruisce \Gino\Input::input_date
 *     - select, costruisce \Gino\Input::select_label
 *     - radio, costruisce \Gino\Input::radio_label
 *     - tag, costruisce \Gino\TagInput::input
 *   - @a type (string), tipologia di dato (int, string)
 *   - @a data (array), valori dell'input select
 *   - @a options (array), opzioni degli input (sovrascrivono quelle di default)
     * 
     * @param array $fields elenco dei campi di ricerca nel formato [field_name => [input_options][, ...]]; 
     *   dove le chiavi (field_name) sono i nomi dei parametri di ricerca e i valori sono degli array (input_options) che 
     *   comprendono le opzioni necessarie per costruire gli input form.
     *   Le opzioni valide per ogni tipo di campo di ricerca sono:
     *   - @a label (string), label dell'input
     *   - @a input (string), tipologie di input:
     *     - text (default), costruisce \Gino\Input::input_label
     *     - date, costruisce \Gino\Input::input_date
     *     - select, costruisce \Gino\Input::select_label
     *     - radio, costruisce \Gino\Input::radio_label
     *     - tag, costruisce \Gino\TagInput::input
     *   - @a type (string), tipologia di dato (int, string)
     *   - @a data (array), valori degli input select e radio
     *   - @a default (mixed), default dell'input radio
     *   - @a options (array), opzioni degli input (sovrascrivono quelle di default)
     * @param array $opts array associativo di opzioni
     *   - @b identifier (string): valore id del contenitore in sessione dei parametri di ricerca (default appSearch)
     *   - @b param_values (array): valori dei parametri di ricerca passati attraverso l'url; gli elementi dell'array sono nel formato:
     *     [field_name]=>[field_value], dove field_name deve corrispondere al nome di un campo nella proprietà $_fields
     *   - @b before_input (string): stringa da anteporre al nome dell'input form (default search_)
     *   - @b submit_name (string): nome del submit di ricerca (default @a submit_search)
     *   - @b submit_all_name (string): nome del submit di ricerca di tutti i record (default @a submit_search_all)
     * @return void
     */
    function __construct($fields, $opts = array()) {

		$this->_registry = registry::instance();
		$this->_request = $this->_registry->request;
		$this->_session = $this->_request->session;

        $identifier = gOpt('identifier', $opts, 'appSearch');
        $before_input = gOpt('before_input', $opts, 'search_');
        $param_values = gOpt('param_values', $opts, array());
        $submit_name = gOpt('submit_name', $opts, 'submit_search');
        $submit_all_name = gOpt('submit_all_name', $opts, 'submit_search_all');

        $this->_fields = $fields;
        $this->_fields_name = array_keys($fields);
        $this->_identifier = $identifier;
        $this->setParamValues($param_values);
        $this->setOpenform(false);
        
        $this->_before_input = $before_input;
        $this->setSubmitName($submit_name);
        $this->setSubmitAllName($submit_all_name);
    }
    
    /**
     * @brief Recupera il valore della proprietà $_open_form
     * @return boolean
     */
    public function getOpenform() {
    	return $this->_open_form;
    }
    
    /**
     * @brief Imposta il valore della proprietà $_open_form
     * @param boolean $value
     * @return void
     */
    public function setOpenform($value) {
    	$this->_open_form = (bool) $value;
    }
    
    /**
     * @brief Imposta il valore della proprietà $_param_values
     * @param array $value
     * @return void
     */
    public function setParamValues($value) {
    	$this->_param_values = is_array($value) ? $value : array();
    }
    
    /**
     * @brief Recupera il valore della proprietà $_submit_name
     * @return string
     */
    public function getSubmitName() {
    	return $this->_submit_name;
    }
    
    /**
     * @brief Imposta il valore della proprietà $_submit_name
     * @param string $value
     * @return void
     */
    public function setSubmitName($value) {
    	$this->_submit_name = (string) $value;
    }
    
    /**
     * @brief Recupera il valore della proprietà $_submit_all_name
     * @return string
     */
    public function getSubmitAllName() {
    	return $this->_submit_all_name;
    }
    
    /**
     * @brief Imposta il valore della proprietà $_submit_all_name
     * @param string $value
     * @return void
     */
    public function setSubmitAllName($value) {
    	$this->_submit_all_name = (string) $value;
    }
    
    /**
     * @brief Form di ricerca
     * 
     * @param string $link indirizzo dell'action form
     * @param string $form_id valore id del form
     * @param array $options array associativo di opzioni
     *   - @b submit_value (string): valore del submit di ricerca (default 'cerca')
     *   - @b submit_text_add (string): testo da aggiungere di seguito agli input submit
     *   - @b view_submit_all (boolean): visualizza il submit di ricerca di tutti i record (default true)
     * @return string
     */
    public function formSearch($link, $form_id, $options=array()) {
    
    	if(!count($this->_fields)) {
    		return null;
    	}
    	
    	$submit_value = gOpt('submit_value', $options, _("cerca"));
    	$view_submit_all = gOpt('view_submit_all', $options, true);
    	$submit_text_add = gOpt('submit_text_add', $options, null);
    	
    	$myform = Loader::load('Form', array());
    	$form_search = $myform->open($link, false, '', array('form_id' => $form_id));
    	
    	foreach($this->_fields AS $search_field => $search_input)
    	{
    		$input = array_key_exists('input', $search_input) ? $search_input['input'] : 'text';
    		$search_name = $this->_before_input.$search_field;
    		
    		if(is_array($this->_session->{$this->_identifier}) && array_key_exists($search_field, $this->_session->{$this->_identifier})) {
    			$search_value = htmlInput($this->_session->{$this->_identifier}[$search_field]);
    		}
    		else {
    			$search_value = null;
    		}
    		
    		if($input == 'text')
    		{
    			if(array_key_exists('options', $search_input)) {
    			    $search_options = $search_input['options'];
    			}
    			else {
    			    $search_options = ['size' => 20, 'maxlength' => 40];
    			}
    			
    			$form_search .= Input::input_label($search_name, 'text', $search_value, $search_input['label'], $search_options);
    		}
    		elseif($input == 'date')
    		{
    			if(array_key_exists('options', $search_input)) {
    			    $search_options = $search_input['options'];
    			}
    			else {
    			    $search_options = [];
    			}
    			
    			$form_search .= Input::input_date($search_name, $search_value, $search_input['label'], $search_options);
    		}
    		elseif($input == 'select')
    		{
    			if(array_key_exists('options', $search_input)) {
    			    $search_options = $search_input['options'];
    			}
    			else {
    			    $search_options = [];
    			}
    			
    			$form_search .= Input::select_label($search_name, $search_value, $search_input['data'], $search_input['label'], $search_options);
    		}
    		elseif($input == 'radio')
    		{
    		    if(array_key_exists('options', $search_input)) {
    		        $search_options = $search_input['options'];
    		    }
    		    else {
    		        $search_options = [];
    		    }
    		    
    		    $form_search .= Input::radio_label($search_name, $search_value, $search_input['data'], $search_input['default'], $search_input['label'], $search_options);
    		}
    		elseif($input == 'tag')
    		{
    			if(array_key_exists('options', $search_input)) {
    			    $search_options = $search_input['options'];
    			}
    			else {
    			    $search_options = ['size' => 20, 'maxlength' => 40];
    			}
    			$form_search .= TagInput::input($search_name, $search_value, $search_input['label'], $search_options);
    		}
    	}
    	
    	// Submits
    	$form_search .= Input::submit($this->_submit_name, $submit_value);
    	
    	if($view_submit_all) {
    	    $form_search .= ' '.Input::submit($this->_submit_all_name, _('tutti'));
    	}
    	if($submit_text_add) {
    	    $form_search .= ' '.$submit_text_add;
    	}
    	// /Submits
    	
    	$form_search .= $myform->close();
    
    	return $form_search;
    }
    
    /**
     * @brief Form di ricerca
     *
     * @param string $link indirizzo dell'action form
     * @param string $form_id valore id del form
     * @param array $options array associativo di opzioni
     *   - @b div_id (string): valore id del contenitore del form
     *   - @b submit_value (string): valore del submit di ricerca (default 'cerca')
     *   - @b submit_text_add (string): testo da aggiungere di seguito agli input submit
     *   - @b view_submit_all (boolean): visualizza il submit di ricerca di tutti i record (default true)
     * @return string
     */
    public function form($link, $form_id, $options=[]) {
        
        $div_id = gOpt('div_id', $options, 'g_form_search');
        
        $form = $this->formSearch($link, $form_id, $options);
        
        $check_open_form = $this->getOpenform();
        if($check_open_form) {
            $d = 'block';
        }
        else {
            $d = 'none';
        }
        
        $form = "<div id=\"$div_id\" style=\"display: ".$d.";\">".$form."</div>";
        
        return $form;
    }
    
    /**
     * @brief Link di apertura/chiusura del form di ricerca
     *
     * @param array $options array associativo di opzioni
     *   - @b div_id (string): valore id del contenitore del form
     *   - @b span_class (string): classi aggiuntive del tag span
     * @return string
     */
    public function linkSearchForm($options=[]) {
        
        $div_id = gOpt('div_id', $options, 'g_form_search');
        $span_class = gOpt('span_class', $options, null);
        
        $buffer = "<span class=\"fa fa-search link";
        if($span_class) {
            $buffer .= " ".$span_class;
        }
        $buffer .= "\" onclick=\"";
        $buffer .= "if($('#$div_id').css('display') == 'block') $('#$div_id').css('display', 'none'); else $('#$div_id').css('display', 'block');";
        $buffer .= "\"></span>";
        return $buffer;
    }
    
    /**
     * @brief Imposta le chiavi di ricerca in sessione
     * 
     * @return void
     */
    public function sessionSearch() {
    	
    	if(isset($this->_request->POST[$this->_submit_all_name])) {
    		$search = null;
    		$this->_session->{$this->_identifier} = $search;
    	}
    	
    	if(!$this->_session->{$this->_identifier}) {
    		
    		$search = array();
    		foreach($this->_fields_name AS $name) {
    			$search[$name] = null; 
    		}
    	}
    	
    	$check = false;
    	if(is_array($this->_param_values) && count($this->_param_values))
    	{
    		foreach($this->_param_values AS $key=>$value)
    		{
    			if($value) {
    				$check = true;
    			}
    		}
    	}
    	
    	if(isset($this->_request->POST[$this->_submit_name]) or $check) {
    		if($check)
    		{	
    			foreach($this->_fields_name AS $name) {
    				if(array_key_exists($name, $this->_param_values)) {
    					$search[$name] = $this->_param_values[$name];
    				}
    				else {
    					$search[$name] = null;
    				}
    			}
    		}
    		else
    		{
    			foreach($this->_fields AS $field_name=>$field_opt) {
    				
    				$s_name = $this->_before_input.$field_name;
    				$s_type = array_key_exists('type', $field_opt) ? $field_opt['type'] : 'string';
    				
    				if(isset($this->_request->POST[$s_name])) {
    					$search[$field_name] = \Gino\cleanVar($this->_request->POST, $s_name, $s_type);
    				}
    				else {
    					$search[$field_name] = null;
    				}
    			}
    		}
    		$this->_session->{$this->_identifier} = $search;
    	}
    }
    
    /**
     * @brief Recupera i valori per la ricerca
     * @description I valori provenienti da url sovrascrivono quelli salvati in sessione.
     * 
     * @return array
     */
    public function getValues() {
    	
    	$this->setOpenform(false);
    	
    	$search_values = array();
    	
    	// Set search params at null
    	foreach($this->_fields_name AS $name) {
    		$search_values[$name] = null;
    	}
    	
    	// Get search values
    	if($this->_session->{$this->_identifier})
    	{
    		foreach($this->_fields_name AS $name)
    		{
    			if(array_key_exists($name, $this->_session->{$this->_identifier}) && $this->_session->{$this->_identifier}[$name])
    			{
    				$search_values[$name] = $this->_session->{$this->_identifier}[$name];
    				$this->setOpenform(true);
    			}
    		}
    	}
    	// /search values
    	
    	// Merge search values and link params
    	$def_values = array();
    	
    	foreach($search_values AS $key=>$value)
    	{
    		if(array_key_exists($key, $this->_param_values) && $this->_param_values[$key]) {
    			$def_values[$key] = $this->_param_values[$key];
    		}
    		else {
    			$def_values[$key] = $value;
    		}
    	}
    	
    	return $def_values;
    }
    
    /**
     * @brief Salva i valori di una ricerca in sessione e redirige alla pagina ai risultati
     * 
     * @param \Gino\Http\Request $request
     * @param string $url indirizzo del redirect
     * @return \Gino\Http\Redirect
     * 
     * @description Questo metodo viene utilizzato per superare il problema dei warning "Page Has Expired" 
     * che si generano quando una pagina nella history (compresa la pagina corrente) è richiesta con il metodo POST. \n
     * Una soluzione consiste nel far rimandare il form di ricerca a una pagina che salva le chiavi di ricerca in sessione e 
     * redirige alla pagina finale. \n 
     * Segue un esempio di utilizzo:
     * 
     * @code
     * // nel metodo 'archive' di visualizzazione dei contenuti ->
     * $search_form = $obj_search->formSearch($this->link($this->_instance_name, 'search'), 'form_search_example');
     * #endcode
     * 
     * @code
     * public function search(\Gino\Http\Request $request) {
     *   Loader::import('class', array('\Gino\SearchInterface'));
     *   $obj_search = new \Gino\SearchInterface($this->searchFields(), array('identifier' => 'exampleSearch'.$this->_instance));
     *   return $obj_search->redirect($request, $this->link($this->_instance_name, 'archive'));
     * }
     * #endcode
     */
    public function redirect(\Gino\Http\Request $request, $url) {
    	
    	if($request->POST)
    	{
    	    $submit = \Gino\cleanVar($request->POST, $this->_submit_name, 'string');
    	    $submit_all = \Gino\cleanVar($request->POST, $this->_submit_all_name, 'string');
    	    
    	    if($submit or $submit_all) {
    			$this->sessionSearch();
    		}
    	}
    	$response = new \Gino\Http\Redirect($url);
    	return $response;
    }
}
