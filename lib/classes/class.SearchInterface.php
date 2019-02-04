<?php
/**
 * @file class.SearchInterface.php
 * @brief Contiene la definizione ed implementazione della classe Gino.SearchInterface
 * 
 * @copyright 2016-2018 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

/**
 * @brief Metodi per gestire le ricerche nelle interfacce utente
 *
 * @copyright 2016-2018 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 * 
 * ##UTILIZZO
 * 1. Creare nel modello due metodi statici per definire l'insieme dei campi dei form di ricerca e le condizioni di ricerca dei record.
 * 
 * L'elenco dei campi di ricerca da passare al costruttore è un array di elementi, nel quale le chiavi sono i nomi dei parametri di ricerca input form 
 * e i valori sono degli array che comprendono le opzioni necessarie per costruire gli input form. 
 * Il nome dell'input form viene creato unendo il valore dell'opzione @a before_input del costruttore con il nome del parametro.
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
 *   - @a data (array), valori degli input select e radio
 *   - @a default (mixed), default dell'input radio
 *   - @a options (array), opzioni degli input (sovrascrivono quelle di default)
 * 
 * Seguono due esempi dei metodi.
 * @code
 * public static function setSearchFields($controller, $fields) {
 * 
 *   $search_fields = array(
 *     'category' => array(
 *       'label' => _('Categoria'),
 *       'input' => 'select',
 *       'data' => Category::getForSelect($controller),
 *       'type' => 'int',
 *       'options' => null
 *     ),
 *     'name' => array(
 *       'label' => _('Nome'),
 *       'input' => 'text',
 *       'type' => 'string',
 *       'options' => null
 *     ),
 *     'code' => array(
 *       'label' => _('Codice'),
 *       'input' => 'text',
 *       'type' => 'string',
 *       'options' => array('size' => 8)
 *     ),
 *     'date_from' => array(
 *       'label' => _('Da'),
 *       'input' => 'date',
 *       'type' => 'string',
 *       'options' => null
 *     ),
 *     'date_to' => array(
 *       'label' => _('A'),
 *       'input' => 'date',
 *       'type' => 'string',
 *       'options' => null
 *     )
 *   );
 *   
 *   $array = array();
 *   if(count($fields)) {
 *     foreach($fields AS $field)
 *     {
 *       if(array_key_exists($field, $search_fields)) {
 *         $array[$field] = $search_fields[$field];
 *       }
 *     }
 *   }
 *   return $array;
 * }
 * @endcode
 * 
 * @code
 * public static function setConditionWhere($controller, $options = null) {
 * 
 *   $category = \Gino\gOpt('category', $options, null);
 *   $name = \Gino\gOpt('name', $options, null);
 *   $code = \Gino\gOpt('code', $options, null);
 *   $date_from = \Gino\gOpt('date_from', $options, null);
 *   $date_to = \Gino\gOpt('date_to', $options, null);
 *   
 *   $where = array("instance='".$controller->getInstance()."'");
 *   
 *   if($category) {
 *     $where[] = "category='$category'";
 *   }
 *   if($name) {
 *     $where[] = "name LIKE '%".$name."%'";
 *   }
 *   if($code) {
 *     $where[] = "code LIKE '%".$code."%'";
 *   }
 *   if($date_start) {
 *     $where[] = "insertion_date >= '".$date_start."'";
 *   }
 *   if($date_from) {
 *     $where[] = "insertion_date >= '".$date_from."'";
 *   }
 *   if($date_to) {
 *     $where[] = "insertion_date <= '".$date_to."'";
 *   }
 *   
 *   return implode(' AND ', $where);
 * }
 * @endcode
 * 
 * 
 * 2. Impostare i valori degli eventuali parametri passati attraverso un url
 * 
 * Il formato degli elementi dell'array è [field_name]=>[field_value], dove field_name deve corrispondere 
 * a una chiave dell'elenco dei campi di ricerca (proprietà $_fields).
 * @code
 * $param_values = array(
 *   'category' => $ctg_id,
 *   'date_from' => $date_from ? \Gino\dbDateToDate($date_from) : null,
 * );
 * @endcode
 * 
 * 3. Istanziare la classe
 * 
 * Prima di istanziare la classe impostare i campi da mostrare nel form di ricerca: \n
 * @code
 * $search_fields = ModelItem::setSearchFields($this, array('category', 'name', 'code', 'date_from', 'date_to'));
 * @endcode
 * 
 * @code
 * Loader::import('class', array('\Gino\SearchInterface'));
 * $obj_search = new \Gino\SearchInterface($search_fields, array(
 *   'identifier' => 'appSearch'.$this->_instance,
 *   'param_values' => $param_values
 * ));
 * @endcode
 * 
 * 4. Impostare le chiavi di ricerca in sessione
 * 
 * @code
 * $obj_search->sessionSearch();
 * @endcode
 * 
 * Le fasi 3 e 4 possono venire riunite in un unico metodo nel controllore:
 * @code
 * private function getObjectSearch($fields, $options=array()) {
 * 
 *   $search_fields = ModelItem::setSearchFields($this, $fields);
 *   Loader::import('class', array('\Gino\SearchInterface'));
 *   
 *   $obj_search = new \Gino\SearchInterface($search_fields, $options);
 *   $obj_search->sessionSearch();
 *   return $obj_search;
 * }
 * @endcode
 * 
 * In questo caso per ottenere l'oggetto Gino.SearchInterface basterà richiamare il metodo:
 * @code
 * $obj_search = $this->getObjectSearch(array('category', 'name', 'code', 'date_from', 'date_to'), array(
 *   'identifier' => 'appSearch'.$this->_instance,
 *   'param_values' => $param_values
 * ));
 * @endcode
 * 
 * 5. Ottenere il risultato di una ricerca.
 * 
 * Per recuperare il risultato di una ricerca occorre prima recuperarne i valori, 
 * considerando che i valori provenienti da url sovrascrivono quelli salvati in sessione:
 * @endcode
 * $search_values = $obj_search->getValues();
 * @endcode
 * 
 * I valori vengono poi utilizzati nella definizione delle condizioni della query:
 * @code
 * $conditions = array(
 *   'category' => array_key_exists('category', $search_values) ? $search_values['category'] : null,
 *   'name' => $search_values['name'],
 *   'code' => $search_values['code'],
 *   'date_from' => \Gino\dateToDbDate($search_values['date_from'], '/'),
 *   'date_to' => \Gino\dateToDbDate($search_values['date_to'], '/'),
 * );
 * @endcode
 * 
 * Segue un esempio classico in gino:
 * @code
 * $items_number = ModelItem::getCount($this, $conditions);
 * $paginator = Loader::load('Paginator', array($items_number, $this->_ifp));
 * $limit = $paginator->limitQuery();
 * 
 * $where = ModelItem::setConditionWhere($this, $conditions);
 * $items = ModelItem::objects($this, array('where' => $where, 'limit' => $limit, 'order' => 'insertion_date DESC'));
 * @endcode
 * 
 * 6. Nel dizionario della vista impostare il form e l'apertura del form
 * 
 * @code
 * $dict = array(
 *   ...
 *   'search_form' => $obj_search->formSearch($this->link($this->_instance_name, 'archive'), 'form_search_app'),
 *   'open_form' => $obj_search->getOpenform(),
 * );
 * @endcode
 * 
 * Nella vista
 * @code
 * // 1
 * <h1>
 *   <?= _('Items') ?>
 *   <a style="margin-left: 20px" class="fa fa-rss" href="<?= $feed_url ?>"></a> 
 *   <span class="fa fa-search link" onclick="if($('app_form_search').style.display=='block') $('app_form_search').style.display='none'; else $('app_form_search').style.display='block';"></span>
 * </h1>
 * <div id="app_form_search" style="display: <?= $open_form ? 'block' : 'none'; ?>;">
 *   <?= $search_form ?>
 * </div>
 * // 2
 * <h1>
 *   <?= _('Items') ?>
 *   <a style="margin-left: 20px" class="fa fa-rss" href="<?= $feed_url ?>"></a> 
 *   <span class="fa fa-search link" onclick="$('app_form_search').toggleClass('hidden')"></span>
 * </h1>
 * <div id="app_form_search" class="<?= $open_form ? '' : 'hidden' ?>">
 *   <?= $search_form ?>
 * </div>
 * @endcode
 * 
 * Con due form nella stessa pagina:
 * @code
 * <h1><?= _('Items') ?> 
 * <span class="fa fa-search link" onclick="$('app_form_search').toggleClass('hidden');$('app_form_search2').addClass('hidden')"></span> 
 * <span class="icon fa fa-file-pdf-o icon-tooltip link black transition" onclick="$('app_form_search2').toggleClass('hidden');$('app_form_search').addClass('hidden');"></span>
 * </h1>
 * <div id="app_form_search2" class="<?= $open_form2 ? '' : 'hidden' ?>">
 *   <?= $search_form2 ?>
 * </div>
 * <div id="app_form_search" class="<?= $open_form ? '' : 'hidden' ?>">
 *   <?= $search_form ?>
 * </div>
 * @code
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
