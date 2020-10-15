<?php
/**
 * @file class.SearchEventInterface.php
 * @brief Contiene la definizione ed implementazione della classe Gino.SearchEventInterface
 * 
 * @copyright 2018 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

/**
 * @brief Interfaccia per ricerche che comprendono degli input select collegati tra loro con eventi onChange
 * 
 * @copyright 2018 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 * 
 * ##DESCRIZIONE
 * Seguono degli esempi di come vengono definiti nella classe i valori di alcune variabili
 * @code
 * field_name   category
 * input_name   prefix[search_]+category
 * filter_name  prefix[search_]+category+suffix[_filter]
 * @endcode
 * 
 * ##UTILIZZO
 * L'impiego di questa libreria necessita di: \n
 *   - inserimento del form di ricerca in una interfaccia di visualizzazione
 *   - implementazione di un metodo che definisce gli input della ricerca
 *   - implementazione di un metodo per recuperare l'azione del form (metodo pubblico e richiamabile da url)
 *   - implementazione di un metodo per effettuare l'aggiornamento ajax degli input select nel form di ricerca (metodo pubblico e richiamabile da url)
 * 
 * 1. Istanziare la classe definendo l'elenco dei campi di ricerca costituiti da input select collegati tra loro con eventi onChange;
 * se necessario, definire inoltre altre opzioni tra le quali i campi di ricerca aggiuntivi agli input select.
 * È consigliabile implementare un metodo che istanzia l'oggetto Gino.SearchEventInterface.
 * 
 * @code
 * Loader::import('class', array('\Gino\SearchEventInterface'));
 * return new \Gino\SearchEventInterface(
 *   ['category', 'activity', 'sector'],
 *   [
 *     'prefix_name' => 'reportactivities_',
 *     'submit_name' => 'reportactivities_submit',
 *     'other_fields' => ['date_from' => 'string', 'date_to' => 'string']
 *   ]
 * );
 * @endcode
 * 
 * 2. Nel metodo di visualizzazione ricercare i valori da utilizzare per la definizione delle condizioni della query 
 * e costruire il form di ricerca
 * @code
 * $params = $search->searchValues();
 * // values for query
 * $category = array_key_exists('category', $params) ? $params['category'] : null;
 * [...]
 * @endcode
 * 
 * @code
 * $search->FormSearch([
 *   'action' => $this->link($this->_instance_name, 'actionsearch'),
 *   'inputs' => $this->inputsSearchForm($search, [...]),
 * ]);
 * @endcode
 * In questo esempio il metodo actionsearch() è l'azione del form, mentre inputsSearchForm() definisce gli input della ricerca.
 * 
 * 3. Nel metodo che definisce gli input della ricerca recuperare i valori della ricerca salvati in sessione e 
 * implementare gli input: \n
 *   - gli input select con selectInput()
 *   - gli altri input con i metodi di Gino.Input
 * 
 * @code
 * $category = \Gino\gOpt('category', $options, null);
 * $activity = \Gino\gOpt('activity', $options, null);
 * [...]
 * 
 * $category = $search->getSessionValue('category', $category);
 * $activity = $search->getSessionValue('activity', $activity);
 * [...]
 * $buffer = "<div class=\"search_input\">";
 * $buffer .= $search->selectInput('category',
 *   array(
 *     'link' => $this->link($this->_instance_name, 'ajaxItemsSearch'),
 *     'input_value' => $category,
 *     'items' => Category::getForSelect($this),
 *     'onchange_params' => ['category'=>'this', 'activity'=>null],
 *     'first_voice' => _("Categoria")
 *   )
 * );
 * $buffer .= "</div>";
 * $buffer .= "<div class=\"search_input\">";
 * $buffer .= $search->selectInput('field_name',
 *   array(
 *     'link' => $this->link($this->_instance_name, 'ajaxItemsSearch'),
 *     'input_value' => $field_name,
 *     'items' => $category ? Activity::getForSelect($this, ['ctg' => $category]) : array(),
 *     'onchange_params' => ['category'=>'name', 'activity'=>'this'],
 *     'first_voice' => _("...")
 *   )
 * );
 * $buffer .= "</div>";
 * [...]
 * @endcode
 * In questo esempio ajaxItemsSearch() viene richiamato per effettuare l'aggiornamento ajax degli input select nel form.
 * 
 * 4. Nel metodo che recupera l'azione del form (in questo esempio actionsearch) istanziare la classe, o richiamare il metodo che la istanzia, e 
 * richiamare ActionSearch() passandogli l'indirizzo di reindirizzamento
 * 
 * @code
 * $search = $this->setSearchInterface();
 * $response = $search->ActionSearch(['return_link' => $this->link($this->_instance_name, 'report', [], "code=codename")]);
 * return $response;
 * @endcode
 * 
 * 5. Nel metodo che effettua l'aggiornamento ajax degli input select (in questo esempio ajaxItemsSearch) 
 * istanziare la classe, o richiamare il metodo che la istanzia, recuperare i valori POST dei select e rchiamare 
 * il metodo che definisce gli input della ricerca (in questo esempio inputsSearchForm)
 * 
 * @code
 * $category = \Gino\cleanVar($request->POST, 'category', 'int');
 * $activity = \Gino\cleanVar($request->POST, 'activity', 'int');
 * [...]
 * $search = $this->setSearchInterface();
 * $buffer = $this->inputsSearchForm($search, [
 *   'category' => $category,
 *   'activity' => $activity, 
 *   [...],
 *   'ajax' => true,
 * ]);
 * $response = new \Gino\Http\Response($buffer);
 * return $response;	// when request from ajax; else: return $response();
 * @endcode
 * 
 */
class SearchEventInterface {

    private $_registry, $_request, $_session;
    
    /**
     * @brief Elenco dei nomi dei campi di ricerca
     * @var array
     */
    private $_fields_name;
    
    /**
     * @brief Prefisso dei nomi dei campi di ricerca utilizzato per definire i nomi degli input form
     * @var string
     */
    private $_prefix_name;
    
    /**
     * @brief Elenco dei nomi degli input form
     * @var array
     */
    private $_inputs_name;
    
    /**
     * @brief Suffisso degli input form utilizzato per definire i nomi dei filtri
     * @var string
     */
    private $_filter_suffix;
    
    /**
     * @brief Nome del submit di ricerca
     * @var string
     */
    private $_submit_name;
    
    /**
     * @brief Valore del submit di ricerca
     * @var string
     */
    private $_submit_value;
    
    /**
     * @brief Nome del tag FORM
     * @var string
     */
    private $_form_name;
    
    /**
     * @brief Valore id del teag DIV richiamato via ajax
     * @var string
     */
    private $_ajax_id;
    
    /**
     * @brief Campi di ricerca aggiuntivi con l'indicazione del tipo di dato
     * @var array
     */
    private $_other_fields;

    /**
     * @brief Costruttore
     * 
     * @param array $fields elenco dei campi di ricerca (@example ['category', 'activity', 'sector'])
     * @param array $opts array associativo di opzioni
     *   - @b prefix_name (string): prefisso del campo (default @a search_)
     *   - @b filter_suffix (string): suffisso dell'input form (default @a _filter)
     *   - @b submit_name (string): nome del submit di ricerca (default @a submit_search)
     *   - @b submit_value (string): valore del submit (default @a cerca)
     *   - @b form_name (string)
     *   - @b ajax_id (string)
     *   - @b other_fields (array): campi di ricerca aggiuntivi ai select nel formato [field_name => type_value[string|int|...][, ...]]
     * @return void
     */
    function __construct($fields, $opts = array()) {

		$this->_registry = registry::instance();
		$this->_request = $this->_registry->request;
		$this->_session = $this->_request->session;
		
		$this->_fields_name = $fields;
		$this->_prefix_name = gOpt('prefix_name', $opts, 'search_');
		
		$this->_filter_suffix = gOpt('filter_suffix', $opts, '_filter');
		
		$this->_submit_name = gOpt('submit_name', $opts, 'submit_search');
		$this->_submit_value = gOpt('submit_value', $opts, _("cerca"));
		
		$this->_form_name = $this->_prefix_name.'search_form';
		$this->_ajax_id = $this->_prefix_name.'search_aj';
		
		$this->_other_fields = gOpt('other_fields', $opts, []);
		
		if(!is_array($this->_fields_name)) {
		    $this->_fields_name = [];
		}
		
		$this->setOtherFieldsName();
		$this->setInputsName();
    }
    
    /**
     * @brief Aggiunge i campi impostati nella proprietà $_other_fields alla proprietà $_fields_name
     */
    private function setOtherFieldsName() {
        
        if(count($this->_other_fields)) {
            foreach($this->_other_fields AS $field=>$value) {
                $this->_fields_name[] = $field;
            }
        }
    }
    
    /**
     * @brief Imposta la proprietà @a $_inputs_name
     */
    private function setInputsName() {
        
        $this->_inputs_name = [];
        
        if(count($this->_fields_name)) {
            foreach($this->_fields_name AS $field) {
                $this->_inputs_name[] = $this->_prefix_name.$field;
            }
        }
    }
    
    /**
     * @brief Nome dell'input form
     * @param string $field nome del campo
     * @return string
     */
    public function getInputName($field) {
        return $this->_prefix_name.$field;
    }
    
    /**
     * @brief Recupera il valore della proprietà @a $_submit_name
     * @return string
     */
    public function getSubmitName() {
        return $this->_submit_name;
    }
    
    /**
     * @brief Imposta il valore della proprietà @a $_submit_name
     * @param string $value
     * @return void
     */
    public function setSubmitName($value) {
        $this->_submit_name = (string) $value;
    }
    
    /**
     * @brief Imposta il valore della proprietà @a $_submit_value
     * @param string $value
     * @return void
     */
    public function setSubmitValue($value) {
        $this->_submit_value = (string) $value;
    }
    
    /**
     * @brief Imposta il valore della proprietà @a $_form_name
     * @param string $value
     * @return void
     */
    public function setFormName($value) {
        $this->_form_name = (string) $value;
    }
    
    /**
     * @brief Imposta il valore della proprietà @a $_ajax_id
     * @param string $value
     * @return void
     */
    public function setAjaxId($value) {
        $this->_ajax_id = (string) $value;
    }
    
    /**
     * @brief Valori GET
     * 
     * @return array [field_name => value]
     */
    public function getGetValues($options=[]) {
        
        $params = $this->_fields_name;
        $values = array();
        
        if($this->_request->GET and count($params))
        {
            foreach($params AS $param) {
                
                if(isset($this->_request->GET[$param])) {
                    
                    if(array_key_exists($param, $this->_other_fields)) {
                        $type = $this->_other_fields[$param];
                    }
                    else {
                        $type = 'int';
                    }
                    
                    $value = \Gino\cleanVar($this->_request->GET, $param, $type);
                    if($value) {
                        $values[$param] = $value;
                    }
                }
            }
        }
        return $values;
    }
    
    /**
     * @brief Valori definiti dai parametri GET e dai campi di ricerca (input form)
     * 
     * @return array [field_name => value]
     */
    public function searchValues() {
        
        $items = $this->_fields_name;
        $values = $this->getGetValues();
        
        foreach($this->_inputs_name AS $fname)
        {
            $filter_name = $fname.$this->_filter_suffix;
            if($this->_prefix_name) {
                $param = substr($fname, strlen($this->_prefix_name));
            }
            
            if(isset($this->_session->$filter_name) && $this->_session->$filter_name) {
                $items[$param] = $this->_session->$filter_name;
            }
        }
        return $items;
    }
    
    /**
     * @brief Form di ricerca
     * 
     * @param array $options array associativo di opzioni
     *   - @b action (string): indirizzo dell'action
     *   - @b inputs (string): inputs form
     * @return string
     */
    public function FormSearch($options = []) {
        
        $action = gOpt('action', $options, null);
        $inputs = gOpt('inputs', $options, null);
        
        $this->setSessionSearch();
        
        $buffer = "<form name=\"".$this->_form_name."\" action=\"$action\" method=\"post\">";
        $buffer .= "<div id=\"".$this->_ajax_id."\">";
        $buffer .= $inputs;
        $buffer .= "</div>";
        $buffer .= "<input type=\"submit\" name=\"".$this->_submit_name."\" value=\"".$this->_submit_value."\" />";
		$buffer .= "</form>";
		
		return $buffer;
    }
    
    /**
     * @brief Azione della ricerca
     * @description Imposta le variabili di sessione e prepara il redirect
     * 
     * @param array $options array associativo di opzioni
     *   - @b $return_link (string): indirizzo di reindirizzamento; se non presente reindirizza alla home page
     * @return \Gino\Http\Redirect
     */
    public function ActionSearch($options = []) {
        
        $return_link = gOpt('return_link', $options, null);
        
        if($this->_request->POST) {
            $this->setSessionSearch();
        }
        
        if(is_null($return_link)) {
            $return_link = $this->_request->root_absolute_url;
        }
        
        $response = new \Gino\Http\Redirect($return_link);
        return $response;
    }
    
    /**
     * @brief Resetta i valori della ricerca precedente
     * 
     * @param array $options
     * @return void
     */
    private function resetSearchParams($options=[]) {
        
        if(isset($this->_request->POST[$this->_submit_name])) {
            
            // delete other search params
        }
    }
    
    /**
     * @brief Variabili di sessione usate per filtrare i record
     * @description Il nome delle chiavi è il nome degli elementi della ricerca senza il prefisso: [type_search]_search_
     *
     * @return array
     */
    private function getSessionSearch() {
        
        $filters = array();
        
        foreach($this->_inputs_name AS $value) {
            
            $filter_name = $value.$this->_filter_suffix;
            
            if($this->_session->$filter_name) {
                $item = substr($value, strlen($this->_prefix_name));
                $filters[$item] = $this->_session->$filter_name;
            }
        }
        
        return $filters;
    }
    
    /**
     * @brief Valore del parametro filtrato in sessione
     * @param string $param
     * @param integer $value
     * @return integer
     */
    public function getSessionValue($param, $value) {
        
        $filter_name = $this->_prefix_name.$param.$this->_filter_suffix;
        
        if($this->_session->$filter_name && !$value) {
            $value = $this->_session->$filter_name;
        }
        
        return $value;
    }
    
    /**
     * @brief Imposta le variabili di sessione usate per filtrare i record
     * @description Al nome della variabile di sessione associata all'input form viene aggiunto 
     *              il suffisso definito nella proprietà @a $_filter_suffix
     * 
     * @return void
     */
    private function setSessionSearch() {
        
        $this->resetSearchParams();
        
        foreach($this->_inputs_name as $fname) {
            
            $filter_name = $fname.$this->_filter_suffix;
            if(!isset($this->_session->$filter_name)) {
                $this->_session->$filter_name = null;
            }
        }
        
        if(isset($this->_request->POST[$this->_submit_name])) {
            
            foreach($this->_inputs_name as $fname) {
                
                $filter_name = $fname.$this->_filter_suffix;
                if(isset($this->_request->POST[$fname]) && $this->_request->POST[$fname] !== '') {
                    
                    $this->_session->$filter_name = \Gino\cleanVar($_POST, $fname, 'string');
                }
                else {
                    $this->_session->$filter_name = null;
                }
            }
        }
    }
    
    /**
     * @brief Select Input
     * 
     * @param string $name nome del campo di ricerca
     * @param array $options array associativo di opzioni
     *   - @b link (string): indirizzo dell'ajax request
     *   - @b onchange_params (array): elementi necessari per definire l'onChange del select; 
     *     l'array deve essere costruito nel seguente formato [field_name=>[this|name|null][, ...]]:
     *     - 'this', il valore di 'field_name' viene recuperato dal select input che richiama l'onChange ('category='+$(this).value)
     *     - 'name', il valore di 'field_name' viene recuperato da un altro select input ('category='+$('[prefix_name]category').value)
     *     - null, il parametro non compare nell'onChange
     *     @example ['category'=>'this', 'activity'=>null]
     *     @example ['category'=>'name', 'activity'=>'this']
     *   - opzioni di Gino.Input.select
     * @return string
     */
    public function selectInput($name, $options=array()) {
        
        $link = \Gino\gOpt('link', $options, null);
        $onchange_params = \Gino\gOpt('onchange_params', $options, null);
        
        $input_value = \Gino\gOpt('input_value', $options, null);
        $items = \Gino\gOpt('items', $options, array());
        $first_voice = \Gino\gOpt('first_voice', $options, null);
        $max_chars = \Gino\gOpt('max_chars', $options, null);
        $cut_words = \Gino\gOpt('cut_words', $options, true);
        $nofirst = $first_voice ? true : false;
        
        $input_name = $this->_prefix_name.$name;
        
        // Define Ajax Request
        $count = count($onchange_params);
        $params = '';
        if(is_array($onchange_params) and $count) {
            
            $i = 1;
            foreach ($onchange_params AS $key=>$value) {
                
                if($i == 1) {
                    $params .= "'".$key."='";
                }
                else {
                    $params .= "'&".$key."='";
                }
                
                if($value == 'this') {
                    $params .= "+$(this).value";
                }
                elseif($value == 'name') {
                    $params .= "+$('".$this->_prefix_name.$key."').value";
                }
                
                if($i < $count) {
                    $params .= "+";
                }
                $i++;
            }
            
            $onchange = "gino.ajaxRequest('post', '$link', $params, '".$this->_ajax_id."')";
        }
        else {
            $onchange = null;
        }
        // /ajax_request
        
        $input = \Gino\Input::select($input_name, $input_value, $items,
            array(
                'noFirst' => $nofirst,
                'firstVoice' => $first_voice,
                "firstValue" => null,
                'id' => $input_name,
                'js' => $onchange ? "onchange=\"$onchange\"" : null,
                'maxChars' => $max_chars,
                'cutWords' => $cut_words,
            )
        );
        
        return $input;
    }
}
