<?php
/**
 * @file class.SearchInterface.php
 * @brief Contiene la definizione ed implementazione della classe Gino.SearchInterface
 * 
 * @copyright 2016 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

/**
 * @brief Metodi per gestire le ricerche nelle interfacce utente
 *
 * @copyright 2016 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 * 
 * ##UTILIZZO
 * 1. Definire l'elenco dei campi di ricerca da passare al costruttore. 
 * L'elenco è un array di elementi, nel quale le chiavi sono i nomi dei parametri di ricerca input form e i valori sono degli array 
 * che comprendono le opzioni necessarie per costruire gli input form (il nome dell'input form viene creato unendo il valore 
 * dell'opzione @a before_input del costruttore e il nome del parametro). \n
 * Le opzioni valide sono: \n
 *   - @a label (string), label dell'input
 *   - @a input (string), tipologie di input:
 *     - text (default), costruisce \Gino\Input::input_label
 *     - date, costruisce \Gino\Input::input_date
 *     - select, costruisce \Gino\Input::select_label
 *     - tag, costruisce \Gino\TagInput::input
 *   - @a type (string), tipologia di dato (int, string)
 *   - @a data (array), valori dell'input select
 *   - @a options (array), opzioni degli input (sovrascrivono quelle di default)
 * 
 * @code
 * $search_fields = array(
 *   'category' => array(
 *     'label' => _('Categoria'),
 *     'input' => 'select',
 *     'data' => Category::getForSelect($this),
 *     'type' => 'int',
 *     'options' => null
 *   ), 
 *   'text' => array(
 *     'label' => _('Titolo/Testo'),
 *     'input' => 'text',
 *     'type' => 'string',
 *     'options' => null
 *   ),
 *   ...
 * );
 * @endcode
 * 
 * 2. Impostare i valori degli eventuali parametri passati attraverso un url. 
 * Il formato è [nome]=>[valore], dove il nome deve corrispondere a una chiave dell'elenco dei campi di ricerca (proprietà $_fields).
 * @code
 * $param_values = array(
 *   'category' => $ctg_id,
 *   'date_from' => $date_from ? \Gino\dbDateToDate($date_from) : null,
 *   'date_to' => $date_to ? \Gino\dbDateToDate($date_to) : null,
 * );
 * @endcode
 * 
 * 3. Istanziare la classe 
 * @code
 * Loader::import('class', array('\Gino\SearchInterface'));
 * $obj_search = new \Gino\SearchInterface($search_fields, array(
 *   'identifier' => 'newsSearch'.$this->_instance,
 *   'param_values' => $param_values
 * ));
 * @endcode
 * 
 * 4. Impostare le chiavi di ricerca in sessione
 * @code
 * $obj_search->sessionSearch();
 * @endcode
 * 
 * 4. Recuperare i valori per la ricerca, considerando che i valori provenienti da url hanno la precedenza su quelli salvati in sessione.
 * @endcode
 * $search_values = $obj_search->getValues();
 * @endcode
 * 
 * I valori vengono poi utilizzati nella definizione delle condizioni della query:
 * @code
 * $conditions = array(
 *   'published' => true, 
 *   'ctg' => array_key_exists('category', $search_values) ? $search_values['category'] : null, 
 *   'text' => $search_values['text'], 
 *   'date_from' => $search_values['date_from'],
 *   'date_to' => $search_values['date_to'],
 * );
 * @endcode
 * 
 * 5. Nel dizionario della vista impostare il form e l'apertura del form
 * @code
 * $dict = array(
 *   ...
 *   'search_form' => $obj_search->formSearch($this->link($this->_instance_name, 'archive'), 'form_search_news'),
 *   'open_form' => $obj_search->getOpenform(),
 * );
 * @endcode
 * 
 * Nella vista
 * @code
 * <h1>
 *   <?= _('Items') ?>
 *   <a style="margin-left: 20px" class="fa fa-rss" href="<?= $feed_url ?>"></a> <span class="fa fa-search link" style="margin-right: 10px;" onclick="if($('app_form_search').style.display == 'block') $('app_form_search').style.display = 'none'; else $('app_form_search').style.display = 'block';"></span>
 * </h1>
 * <div id="app_form_search" style="display: <?= $open_form ? 'block' : 'none'; ?>;">
 *   <?= $search_form ?>
 * </div>
 * @endcode
 */
class SearchInterface {

    private $_registry, $_request, $_session;

    /**
     * Elenco dei campi di ricerca nel formato [nome]=>[valori per costruire gli input]
     * @var array
     */
    private $_fields;
    
    /**
     * Elenco dei nomi dei campi di ricerca
     * @var array
     */
    private $_fields_name;

    /**
     * Identificare del contenitore in sessione dei parametri di ricerca
     * @var string
     */
    private $_identifier;
    
    /**
     * Stringa da anteporre al nome dell'input form
     * @var string
     */
    private $_before_input;
    
    /**
     * Indica l'apertura o la chiusura del form di ricerca
     * @var boolean
     */
    private $_open_form;
    
    /**
     * Contiene i valori dei parametri di ricerca passati attraverso l'url
     * @description Il formato è [nome]=>[valore], dove il nome deve corrispondere al nome nella proprietà $_fields.
     * @var array
     */
    private $_param_values;

    /**
     * @brief Costruttore
     * 
     * @param array $fields elenco dei campi di ricerca
     * @param array $opts array associativo di opzioni
     *   - @b identifier (string): valore id del contenitore in sessione dei parametri di ricerca (default appSearch)
     *   - @b param_values (array): valori dei parametri di ricerca passati attraverso l'url
     *   - @b before_input (string): stringa da anteporre al nome dell'input form (default search_)
     * @return void
     */
    function __construct($fields, $opts = array()) {

		$this->_registry = registry::instance();
		$this->_request = $this->_registry->request;
		$this->_session = $this->_request->session;

        $identifier = gOpt('identifier', $opts, 'appSearch');
        $before_input = gOpt('before_input', $opts, 'search_');
        $param_values = gOpt('param_values', $opts, array());

        $this->_fields = $fields;
        $this->_fields_name = array_keys($fields);
        $this->_identifier = $identifier;
        $this->setParamValues($param_values);
        $this->setOpenform(false);
        
        $this->_before_input = $before_input;
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
     * @brief Form di ricerca
     * 
     * @param string $link indirizzo dell'action form
     * @param string $form_id valore id del form
     * @return html
     */
    public function formSearch($link, $form_id) {
    
    	if(!count($this->_fields)) {
    		return null;
    	}
    	
    	$myform = Loader::load('Form', array());
    	$form_search = $myform->open($link, false, '', array('form_id'=>$form_id));
    	
    	foreach($this->_fields AS $search_field => $search_input)
    	{
    		$input = array_key_exists('input', $search_input) ? $search_input['input'] : 'text';
    		$search_name = $this->_before_input.$search_field;
    		$search_value = htmlInput($this->_session->{$this->_identifier}[$search_field]);
    		
    		if($input == 'text')
    		{
    			$search_options = array_key_exists('options', $search_input) ? $search_input['options'] : array('size'=>20, 'maxlength'=>40);
    			$form_search .= Input::input_label($search_name, 'text', $search_value, $search_input['label'], $search_options);
    		}
    		elseif($input == 'date')
    		{
    			$search_options = array_key_exists('options', $search_input) ? $search_input['options'] : array();
    			$form_search .= Input::input_date($search_name, $search_value, $search_input['label'], $search_options);
    		}
    		elseif($input == 'select')
    		{
    			$search_options = array_key_exists('options', $search_input) ? $search_input['options'] : array();
    			$form_search .= Input::select_label($search_name, $search_value, $search_input['data'], $search_input['label'], $search_options);
    		}
    		elseif($input == 'tag')
    		{
    			$search_options = array_key_exists('options', $search_input) ? $search_input['options'] : array('size'=>20, 'maxlength'=>40);
    			$form_search .= TagInput::input($search_name, $search_value, $search_input['label'], $search_options);
    		}
    	}
    	
    	$submit_all = Input::input('submit_search_all', 'submit', _('tutti'), array('classField'=>'submit'));
    	$form_search .= Input::input_label('submit_search', 'submit', _('cerca'), '', array('classField'=>'submit', 'text_add'=>' '.$submit_all));
    	$form_search .= $myform->close();
    
    	return $form_search;
    }
    
    /**
     * @brief Imposta le chiavi di ricerca in sessione
     * 
     * @return void
     */
    public function sessionSearch() {
    	
    	if(isset($this->_request->POST['submit_search_all'])) {
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
    	
    	if(isset($this->_request->POST['submit_search']) or $check) {
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
     * @description I valori provenienti da url hanno la precedenza su quelli salvati in sessione.
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
    			if($this->_session->{$this->_identifier}[$name])
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
    		if(array_key_exists($key, $this->_param_values) && $this->_param_values[$name]) {
    			$def_values[$key] = $this->_param_values[$name];
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
     * che possono essere generati quando una pagina nella history (compresa la pagina corrente) è richiesta con il metodo POST. \n
     * Una soluzione consiste nel far rimandare il form di ricerca a una pagina che salva le chiavi di ricerca in sessione e redirige alla pagina finale. \n 
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
    		$submit = \Gino\cleanVar($request->POST, 'search_submit', 'string');
    		
    		if($submit) {
    			$this->sessionSearch();
    		}
    	}
    	$response = new \Gino\Http\Redirect($url);
    	return $response;
    }
}
