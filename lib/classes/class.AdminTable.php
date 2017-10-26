<?php
/**
 * @file class.AdminTable.php
 * @brief Contiene la definizione ed implementazione della classe Gino.AdminTable
 *
 * @copyright 2005-2017 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

/**
 * @brief Gestisce l'interfaccia di amministrazione di un modello con inserimento, modifica ed eliminazione
 *
 * Fornisce gli strumenti per gestire la parte amministrativa di un modulo, mostrando gli elementi e interagendo con loro (inserimento, modifica, eliminazione). \n
 * Nel metodo backOffice() viene ricercato automaticamente il parametro 'id' come identificatore del record sul quale interagire. 
 * Non utilizzare il parametro 'id' per altri riferimenti.
 *
 * Il campo di nome @a instance viene trattato diversamente dagli altri campi: non compare nel form e 
 * il valore gli viene passato direttamente dall'istanza. \n
 *
 * ##FILTRI/ORDINAMENTO
 * Per attivare i filtri di ricerca nella pagina di visualizzazione dei record occorre indicare i campi sui quali 
 * applicare il filtro nella chiave @a filter_fields (opzioni della vista). \n
 * Nella tabella di visualizzazione dei record i campi sui quali è possibile ordinare i risultati sono quelli per i quali 
 * la tipologia è "ordinabile", ovvero il metodo @a Gino.Field::canBeOrdered() ritorna il valore @a true. \n    
 * 
 * ##GESTIONE DEI PERMESSI SUI CAMPI
 * La gestione delle autorizzazioni a operare sulle funzionalità del modulo avviene impostando opportunamente le opzioni 
 * @a allow_insertion, @a edit_allow, @a edit_deny, @a delete_deny quando si istanzia la classe AdminTable(). \n
 * Esempio:
 * @code
 * $admin_table = new AdminTable($this, array('allow_insertion' => true, 'delete_deny' => 'all'));
 * @endcode
 *
 * La gestione fine delle autorizzazioni a operare sui singoli campi della tabella avviene indicando i gruppi autorizzati 
 * nell'array delle opzioni della funzionalità utilizzando la chiave @a permission. \n
 * Il formato è il seguente:
 * @code
 * $buffer = $admin_table->backOffice('elearningCtg', 
 *     array(
 *         'list_display' => array('id', 'name'),
 *         'add_params_url'=>array('block'=>'ctg')
 *     ),
 *     array(
 *         'permission'=>array(
 *             'view' => [group], 
 *             'fields' => array(
 *                'field1' => [group], 
 *                'field2' => [group],
 *             )
 *         )
 *     )
 * );
 * @endcode
 * dove @a group (mixed) indica il o i gruppi autorizzati a una determinata funzione/campo. \n
 * La chiave @a view contiene il permesso di accedere alla singola funzionalità (view, edit, delete), e per il momento non viene utilizzata. \n
 * 
 * ##ESPORTAZIONI
 * Sono previste due tipologie di esportazione dei record in formato CSV. \n
 * Una esportazione standard, attivabile con l'opzione @a basic_export, che esporta soltanto i campi visualizzati nell'elenco (opzione @a list_display), 
 * e una esportazione avanzata, attivabile con l'opzione @a advanced_export, che permette di scegliere quali campi da esportare. 
 * In ogni caso vengono esportati soltanto i record visualizzati nell'elenco.
 * 
 * @code
 * $backend = $admin_table->backOffice(
 *   'Item',
 *   array(
 *     'list_display' => array([...]),
 *     'filter_fields' => array([...]),
 *     'basic_export' => false,
 *     'advanced_export' => true
 *   ), // display options
 *   array(), // form options
 *   array()  // fields options
 * );
 * @endcode
 * 
 * @copyright 2005-2017 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class AdminTable {

    protected $_controller;
    protected $_registry,
              $_request,
              $_db,
              $_session;
    
    protected $_view;

    protected $_allow_insertion,
              $_edit_deny,
              $_delete_deny;
    
    /**
     * Valori id dei record che posssono essere modificati da un utente
     * @var array
     */
    protected $_edit_allow;

    /**
     * Filtri per la ricerca automatica (corrispondono ai nomi dei campi della tabella)
     * @var array
     */
    protected $_filter_fields;

    /**
     * Filtri aggiuntivi associati ai campi della tabella di riferimento (concorrono alla definizione delle loro condizioni)
     * @var array
     */
    protected $_filter_join;

    /**
     * Filtri aggiuntivi non collegati ai campi della tabella di riferimento
     * @var array
     */
    protected $_filter_add;

    protected $_list_display, $_list_remove;
    protected $_ifp;

    /**
     * Nome della classe Gino.ModelForm
     * @var string
     */
    private $_model_form;
    
    /**
     * @brief Costruttore
     *
     * @param \Gino\Controller $controller istanza di Gino.Controller che gestisce il backend
     * @param array $opts
     *   array associativo di opzioni
     *   - @b view_folder (string): percorso della directory contenente la vista da caricare
     *   - @b allow_insertion (boolean): indica se permettere o meno l'inserimento di nuovi record
     *   - @b edit_allow (array): valori id dei record che posssono essere modificati da un utente; 
     *     col valore null (default) non viene attivata la condizione specifica
     *   - @b edit_deny (mixed): indica quali sono gli ID dei record che non posssono essere modificati
     *     - @a string, 'all' -> tutti
     *     - @a array, elenco ID
     *   - @b delete_deny (mixed): indica quali sono gli ID dei record che non posssono essere eliminati
     *     - @a string, 'all' -> tutti
     *     - @a array, elenco ID
     * @return istanza di Gino.AdminTable
     */
    function __construct($controller, $opts = array()) {

        Loader::import('class', array('\Gino\Form'));

        $this->_registry = registry::instance();
        $this->_request = $this->_registry->request;
        $this->_controller = $controller;
        $this->_db = $this->_registry->db;
        $this->_session = $this->_request->session;

        $view_folder = gOpt('view_folder', $opts, null);

        $this->_view = new view($view_folder);

        $this->_allow_insertion = gOpt('allow_insertion', $opts, true);
        $this->_edit_allow = gOpt('edit_allow', $opts, null);
        $this->_edit_deny = gOpt('edit_deny', $opts, array());
        $this->_delete_deny = gOpt('delete_deny', $opts, array());
        
        $this->_model_form = '\Gino\ModelForm';
    }
    
    /**
     * @brief Imposta una classe personalizzata che estende Gino.ModelForm
     * 
     * @param string $class nome della classe da istanziare
     * @return void
     */
    public function setModelForm($class) {
    	
    	$this->_model_form = $class;
    }
    
    /**
     * @brief Definisce l'ordinamento della query
     *
     * @param string $order_dir
     * @param string $table
     * @param string $name
     * @return order clause
     */
    private function adminListOrder($order_dir, $table, $name) {
    
    	return $table.".".$name." ".$order_dir;
    }

    /**
     * @brief Gestisce il backoffice completo di un modello (wrapper)
     * 
     * @see Translation::manageTranslation()
     * @see ModelForm::form()
     * @see self::action()
     * @see self::delete()
     * @see self::adminList()
     * @param string $model_class_name nome della classe del modello
     * @param array $options_view opzioni della vista (comprese le autorizzazioni a visualizzare singoli campi)
     * @param array $options_form opzioni del form (comprese le autorizzazioni a mostrare l'input di singoli campi e a salvarli)
     * @param array $options_field opzioni degli elementi nel form
     * @return interfaccia di amministrazione
     * 
     * GET params: \n
     * - insert, int
     * - edit, int
     * - delete, int
     * - export, int
     * - trnsl, int
     * - action, string
     */
    public function backOffice($model_class_name, $options_view=array(), $options_form=array(), $options_field=array()) {

        $id = cleanVar($this->_request->REQUEST, 'id', 'int', '');
        $model_class = get_model_app_name_class_ns(get_name_class($this->_controller), $model_class_name);
        $model_obj = new $model_class($id, $this->_controller);

        $insert = cleanVar($this->_request->GET, 'insert', 'int', '');
        $edit = cleanVar($this->_request->GET, 'edit', 'int', '');
        $delete = cleanVar($this->_request->GET, 'delete', 'int', '');
        $export = cleanVar($this->_request->GET, 'export', 'int', '');
        $trnsl = cleanVar($this->_request->GET, 'trnsl', 'int', '');
        $action = cleanVar($this->_request->GET, 'action', 'string', '');
        
        // Advanced export interface (modal)
        if($action == 'getdata') {
        	
        	if($this->_request->method === 'POST') {
        		return $this->actionGetData($options_view);
        	}
        	else {
        		return $this->formGetData($options_view);
        	}
        }
        // /Advanced export

        if($trnsl) {
            return $this->_registry->trd->manageTranslation($this->_request);
        }
        elseif($insert or $edit) {
            
        	$mform = new $this->_model_form($model_obj);
        	
        	$options_form['view_section_file'] = 'admin_table_form';
        	$options_form['allow_insertion'] = $this->_allow_insertion;
        	$options_form['edit_deny'] = $this->_edit_deny;
        	$options_form['edit_allow'] = $this->_edit_allow;
        	
        	if($this->_request->method === 'POST') {
        		return $this->action($mform, $options_form, $options_field);
        	}
        	else {
        		return $mform->view($options_form, $options_field);
        	}
        }
        elseif($delete) {
        	
        	$link_delete = gOpt('link_delete', $options_form, null);
        	$opt = array('link_delete' => $link_delete);
        	
        	return $this->delete($model_obj, $opt);
        }
        else {
            $options_view['export'] = $export;
            return $this->adminList($model_obj, $options_view);
        }
    }
    
    /**
     * @brief Eliminazione di un record (richiesta di conferma soltanto da javascript)
     *
     * @see self::backOffice()
     * @param \Gino\Model $model modello da eliminare
     * @param array $options_form
     *   array associativo di opzioni
     *   - @b link_delete (string): indirizzo al quale si viene rimandati dopo la procedura di eliminazione del record (se non presente viene costruito automaticamente)
     * @return Gino.Http.Redirect
     */
    public function delete($model, $options) {
    	 
    	$link_delete = gOpt('link_delete', $options, null);
    	
    	if($this->_delete_deny == 'all' || in_array($model->id, $this->_delete_deny)) {
    		throw new \Gino\Exception\Exception403();
    	}
    	
    	$result = $model->delete();
    	
    	$link_return = $link_delete ? $link_delete : $this->editUrl(array(), array('delete', 'id'));
    	
    	if($result === TRUE) {
    		return new \Gino\Http\Redirect($link_return);
    	}
    	else {
    		return Error::errorMessage($result, $link_return);
    	}
    }
    
    /**
     * @brief Azione del form
     * 
     * @param object $model_form oggetto Gino.ModelForm
     * @param array $options_form
     *   array associativo di opzioni
     *   - @b link_return (string): indirizzo al quale si viene rimandati dopo un esito positivo del form (se non presente viene costruito automaticamente)
     * @param array $options_field
     * @return response or redirect
     */
    public function action($model_form, $options_form, $options_field) {
    	
    	$model = $model_form->getModel();
    	
    	$insert = !$model->id;
    	$popup = cleanVar($this->_request->POST, '_popup', 'int');
    	
    	// link error
    	$link_error = $this->editUrl(array(), array());
    	$options_form['link_error'] = $link_error;
    	
    	$action_result = $model_form->save($options_form, $options_field);
    	
    	// link success
    	if(isset($options_form['link_return']) and $options_form['link_return']) {
    		$link_return = $options_form['link_return'];
    	}
    	else {
    		if(isset($this->_request->POST['save_and_continue']) and !$insert) {
    			$link_return = $this->editUrl(array(), array());
    		}
    		elseif(isset($this->_request->POST['save_and_continue']) and $insert) {
    			$link_return = $this->editUrl(array('edit' => 1, 'id' => $model->id), array('insert'));
    		}
    		else {
    			$link_return = $this->editUrl(array(), array('insert', 'edit', 'id'));
    		}
    	}
    	if($action_result === TRUE and $popup) {
    		$script = "<script>opener.gino.dismissAddAnotherPopup(window, '$model->id', '".htmlspecialchars((string) $model, ENT_QUOTES)."' );</script>";
    		return new \Gino\Http\Response($script, array('wrap_in_document' => FALSE));
    	}
    	elseif($action_result === TRUE) {
    		return new \Gino\Http\Redirect($link_return);
    	}
    	else {
    		return Error::errorMessage($action_result, $link_error);
    	}
    }

    /**
     * @brief Lista dei record del modello
     * 
     * @see self::backOffice()
     * @param object $model
     * @param array $options_view
     *     array associativo di opzioni
     *     - @b filter_fields (array): campi sui quali applicare il filtro per la ricerca automatica
     *     - @b filter_join (array): contiene le proprietà degli input form da associare ai campi ai quali viene applicato il filtro; 
     *       i valori in arrivo da questi input concorrono alla definizione delle condizioni dei campi ai quali sono associati
     *       - @a field (string): nome del campo di riferimento; l'input form viene posizionato dopo questo campo
     *       - @a name (string): nome dell'input
     *       - @a label (string): nome della label
     *       - @a data (array): elementi che compongono gli input form radio e select
     *       - @a default (string): valore di default
     *       - @a input (string): tipo di input form, valori validi: radio (default), select
     *       - @a where_clause (string): nome della chiave da passare alle opzioni del metodo addWhereClauses(); per i campi data: @a operator
     *         inoltre contiene le opzioni da passare al metodo clean()
     *       - @a value_type (string): tipo di dato (default string)
     *       - @a method (array): default $this->_request->POST
     *       - @a escape (boolean): default true \n
     *         Esempio:
     *         @code
     *         array(
     *             'field' => 'date_end', 
     *             'label' => '', 
     *             'name' => 'op', 
     *             'data' => array(1=>'<=', 2=>'=', 3=>'>='), 
     *             'where_clause' => 'operator'
     *         )
     *         @endcode
     *     - @b filter_add (array): elenco dei filtri che vengono aggiunti nella ricerca automatica; 
     *       ogni filtro a sua volta è un array i cui elementi permettono di definire l'input form; 
     *       le chiavi dell'array filtro sono le seguenti:
     *       - @a field (string): nome del campo che precede l'input form aggiuntivo nel form di ricerca
     *       - @a name (string): nome dell'input
     *       - @a label (string): nome della label
     *       - @a data (array): elementi che compongono gli input form radio e select
     *       - @a default (string): valore di default
     *       - @a input (string): tipo di input form, valori disponibili: radio (default), select, date, text
     *       - @a filter (string): nome del metodo da richiamare per ricavare la condizione per la query; il metodo dovrà essere creato in una classe che estende @a AdminTable()
     *       inoltre ogni filtro contiene le opzioni da passare al metodo clean
     *       - @a value_type (string): tipo di dato (default string)
     *       - @a method (array): default $this->_request->POST
     *       - @a escape (boolean): default true
     *     - @b list_display (array): nomi dei campi da mostrare nella lista (se vuoto mostra tutti); 
     *       al posto del nome di un campo è possibile indicare un array con le seguenti chiavi
     *       - @a member (string): nome del metodo del modello da richiamare e il cui output verrà mostrato nelle righe della colonna
     *       - @a label (string): intestazione della colonna
     *         Esempio:
     *         @code
     *         'list_display' => array('id', 'category', 'file', 'last_edit_date', 
     *           array('label' => _('URL relativo'), 'member' => 'pathView'), 
     *           array('label' => _('URL download'), 'member' => 'pathDownload'), 
     *         ),
               @endcode
     *     - @b list_remove (array): campi da non mostrare nella lista (default: instance)
     *     - @b items_for_page (integer): numero di record per pagina
     *     - @b list_display_options (array): opzioni di visualizzazione dei campi, nel formato array([fieldname|membername] => array([options,]))
     *         dove le chiavi dell'array sono i nomi dei campi o dei metodi da richiamare (@see list_display option and member key)
     *         Esempio:
     *         @code
     *         'list_display_options' => array('file' => array('maxchars' => 30), 'pathView' => array('maxchars' => null))
     *         @endcode
     *         Le opzioni disponibili per i campi sono:
     *         - @a maxchars (integer): numero massimo di caratteri da mostrare
     *     - @b list_title (string): titolo
     *     - @b list_description (string): descrizione sotto il titolo (informazioni aggiuntive)
     *     - @b list_where (array): condizioni della query che estrae i dati dell'elenco
     *     - @b link_fields (array): campi sui quali impostare un collegamento, nel formato nome_campo=>array('link'=>indirizzo, 'param_id'=>'ref')
     *       - @a link (string), indirizzo del collegamento
     *       - @a param_id (string), nome del parametro identificativo da aggiungere all'indirizzo (default: id[=valore_id])
     *         Esempio:
     *         @code
     *         array('link_fields' => array('codfisc' => array('link' => $this->_registry->router->link($this->_instance_name, 'view')))
     *         @endcode
     *     - @b add_params_url (array): parametri aggiuntivi da passare ai link delle operazioni sui record
     *     - @b add_buttons (array): bottoni aggiuntivi da anteporre a quelli di modifica ed eliminazione, nel formato array(array('label'=>\Gino\icon('group'), 'link'=>indirizzo, 'param_id'=>'ref'))
     *       - @a label (string), nome del bottone
     *       - @a link (string), indirizzo del collegamento
     *       - @a param_id (string), nome del parametro identificativo da aggiungere all'indirizzo (default: id[=valore_id])
     *     - @b deny_ordered (boolean): disabilita l'ordinamento dei record negli elenchi (default false)
     *     // Parametri per l'esportazione dei dati
     *     - @b basic_export (boolean): attiva il collegamento per l'esportazione base dei dati (default false); vengono esportati soltanto i campi visualizzati nell'elenco
     *     - @b filename_export (string): nome del file di esportazione (default items-export.csv)
     *     - @b export (integer): parametro GET passato dal metodo @a backOffice per indicare la richiesta del file di esportazione
     *     - @b advanced_export (boolean): esportazione avanzata dei dati (default false)
     * @return lista record paginata e ordinabile
     * 
     * ##Descrizione
     * Impostando nel costruttore l'opzione @a edit_allow vengono mostrati soltanto i record con i valori id indicati nell'opzione.
     * 
     * ##Esempio di filtri aggiuntivi
     * Segue un esempio di filtri aggiuntivi. I metodi filterDateFrom e filterDateTo sono stati creati in una classe che estende Gino.AdminTable.
     * 
     * @code
     * 'filter_add' => array(
     *   array(
	 *     'field' => 'invoiced', 
	 *     'name' => 'date_from', 
	 *     'label' => _("Dal"),
	 *     'input' => 'date',
	 *     'filter' => 'filterDateFrom'
	 *   ),
	 *   array(
	 *     'field' => 'invoiced',
	 *     'name' => 'date_to',
	 *     'label' => _("Al"),
	 *     'input' => 'date',
	 *     'filter' => 'filterDateTo'
	 *   )
	 * )
	 * @endcode
     */
    public function adminList($model, $options_view=array()) {

		$db = Db::instance();
		$model_structure = $model->getStructure();
		$model_table = $model->getTable();
		
		// some options
		$this->_filter_fields = gOpt('filter_fields', $options_view, array());
		$this->_filter_join = gOpt('filter_join', $options_view, array());
		$this->_filter_add = gOpt('filter_add', $options_view, array());
		$this->_list_display = gOpt('list_display', $options_view, array());
		$this->_list_remove = gOpt('list_remove', $options_view, array('instance'));
		$this->_ifp = gOpt('items_for_page', $options_view, 20);
		
		$list_display_options = gOpt('list_display_options', $options_view, array());
		$list_title = gOpt('list_title', $options_view, ucfirst($model->getModelLabel()));
		$list_description = gOpt('list_description', $options_view, "<p>"._("Lista record registrati")."</p>");
		$list_where = gOpt('list_where', $options_view, array());
		$link_fields = gOpt('link_fields', $options_view, array());
		$addParamsUrl = gOpt('add_params_url', $options_view, array());
		$add_buttons = gOpt('add_buttons', $options_view, array());
		$deny_ordered = gOpt('deny_ordered', $options_view, false);
		$basic_export = gOpt('basic_export', $options_view, false);
		$filename_export = gOpt('filename_export', $options_view, 'items-export.csv');
		$export = gOpt('export', $options_view, null);
		$advanced_export = gOpt('advanced_export', $options_view, false);
		
        // for @compatibility with older versions
        if(array_key_exists('view_export', $options_view) and is_bool($options_view['view_export'])) {
        	$basic_export = $options_view['view_export'];
        }
        
        // fields to be shown
        $fields_loop = array();
        if($this->_list_display) {
            foreach($this->_list_display as $fname) {
                if(is_array($fname)) {
                    $fields_loop[$fname['member']] = array(
                        'member' => $fname['member'],
                        'label' => $fname['label']
                    );
                }
                else {
                    $fields_loop[$fname] = $model_structure[$fname];
                }
            }
        }
        else {
            $fields_loop = $model_structure;
            if(count($this->_list_remove))
            {
                foreach($this->_list_remove AS $value)
                    unset($fields_loop[$value]);
            }
        }

        $order = urldecode(cleanVar($this->_request->GET, 'order', 'string', ''));
        if(!$order) $order = 'id DESC';
        // get order field and direction
        preg_match("#^([^ ,]*)\s?((ASC)|(DESC))?.*$#", $order, $matches);
        $field_order = isset($matches[1]) && $matches[1] ? $matches[1] : '';
        $order_dir = isset($matches[2]) && $matches[2] ? $matches[2] : '';

        // filter form
        $tot_ff = count($this->_filter_fields);
        if($tot_ff) $this->setSessionSearch($model);

        $tot_ff_join = count($this->_filter_join);
        if($tot_ff_join) $this->setSessionSearchAdd($model, $this->_filter_join);

        $tot_ff_add = count($this->_filter_add);
        if($tot_ff_add) $this->setSessionSearchAdd($model, $this->_filter_add);

        // managing instance
        $query_where = array();
        if(array_key_exists('instance', $model_structure)) {
            $query_where[] = "instance='".$this->_controller->getInstance()."'";
        }

        // prepare query
        $query_selection = $model_table.".id";	// 2017-03-09: $db->distinct($model_table.".id")
        $query_table = array($model_table);
        
        if(is_array($this->_edit_allow)) {
        	if(count($this->_edit_allow)) {
        		$query_where[] = "id IN (".implode(",", $this->_edit_allow).")";
        	}
        	// see nothing
        	else {
        		$query_where[] = "id = 0";
        	}
        }
        if(count($list_where)) {
            $query_where = array_merge($query_where, $list_where);
        }
        $query_where_no_filters = implode(' AND ', $query_where);
        // filters
        if($tot_ff) {
            $this->addWhereClauses($query_where, $model);
        }
        // order
        $query_order = $this->adminListOrder($order_dir, $model_table, $field_order);
        
        $tot_records_no_filters_result = $db->select("COUNT(id) as tot", $query_table, $query_where_no_filters);
        $tot_records_no_filters = $tot_records_no_filters_result[0]['tot'];

        $tot_records_result = $db->select("COUNT(id) as tot", $query_table, implode(' AND ', $query_where));
        $tot_records = $tot_records_result[0]['tot'];

        $paginator = Loader::load('Paginator', array($tot_records, $this->_ifp));

        $limit = $export ? null: $paginator->limitQuery();

        $records = $db->select($query_selection, $query_table, implode(' AND ', $query_where), array('order'=>$query_order, 'limit'=>$limit, 'debug'=>false));
        if(!$records) $records = array();

        // Header
        $heads = array();
        $export_header = array();

        foreach($fields_loop as $field_name => $field_obj) {

            if($this->permission($options_view, $field_name))
            {
                if(is_array($field_obj)) {
                	$label = $field_obj['label'];
                }
                else {
                    $model_label = $model_structure[$field_name]->getLabel();
                    $label = is_array($model_label) ? $model_label[0] : $model_label;
                }
                $export_header[] = $label;
                
                if($deny_ordered) {
                	$can_be_ordered = false;
                }
                else {
                	if(is_object($field_obj)) {
                		$build_obj = $model->build($field_obj);
                		$can_be_ordered = $build_obj->canBeOrdered();
                	}
                	else {
                		$can_be_ordered = false;
                	}
                }

                if(!is_array($field_obj) and $can_be_ordered) {

                    $ord = $order == $field_name." ASC" ? $field_name." DESC" : $field_name." ASC";
                    if($order == $field_name." ASC") {
                        $jsover = "$(this).getNext('.fa').removeClass('fa-arrow-circle-up').addClass('fa-arrow-circle-down')";
                        $jsout = "$(this).getNext('.fa').removeClass('fa-arrow-circle-down').addClass('fa-arrow-circle-up')";
                        $css_class = "fa-arrow-circle-up";
                    }
                    elseif($order == $field_name." DESC") {
                        $jsover = "$(this).getNext('.fa').removeClass('fa-arrow-circle-down').addClass('fa-arrow-circle-up')";
                        $jsout = "$(this).getNext('.fa').removeClass('fa-arrow-circle-up').addClass('fa-arrow-circle-down')";
                        $css_class = "fa-arrow-circle-down";
                    }
                    else {
                        $js = '';
                        $jsover = "$(this).getNext('.fa').removeClass('invisible')";
                        $jsout = "$(this).getNext('.fa').addClass('invisible')";
                        $a_style = "visibility:hidden";
                        $css_class = 'fa-arrow-circle-up invisible';
                    }

                    $add_params = $addParamsUrl;
                    $add_params['order'] = $ord;
                    $link = $this->editUrl($add_params, array('start'));
                    $head_t = "<a href=\"".$link."\" onmouseover=\"".$jsover."\" onmouseout=\"".$jsout."\" onclick=\"$(this).setProperty('onmouseout', '')\">".$label."</a>";
                    $heads[] = $head_t." <span class=\"fa $css_class\"></div>";
                }
                else {
                    $heads[] = $label;
                }
            }
        }
        if($export) {
        	$items[] = $export_header;
        }
        $heads[] = array('text'=>'', 'class'=>'noborder nobkg');

        // Records data
        $rows = array();
        foreach($records as $r) {

            $record_model = new $model($r['id'], $this->_controller);
            
            $row = array();
            $export_row = array();
            foreach($fields_loop as $field_name => $field_obj) {

                if($this->permission($options_view, $field_name))
                {
                    // Set list display options
                	$opt_maxchars = 0;
                	
                	if(is_array($list_display_options) and count($list_display_options)) {
                		if(array_key_exists($field_name, $list_display_options)) {
                			
                			$opt_field = $list_display_options[$field_name];
                			if(array_key_exists('maxchars', $opt_field)) {
                				$opt_maxchars = (int) $opt_field['maxchars'];
                			}
                		}
                		elseif(
                			is_array($field_obj) and 
                			array_key_exists('member', $field_obj) and 
                			array_key_exists($field_obj['member'], $list_display_options)) {
                			
                			$opt_field = $list_display_options[$field_obj['member']];
                			if(array_key_exists('maxchars', $opt_field)) {
                				$opt_maxchars = (int) $opt_field['maxchars'];
                			}
                		}
                	}
                	// /Set
                	
                	if(is_array($field_obj)) {
                        $member = $field_obj['member'];
                    	$record_value = $record_model->$member();
                    }
                    else {
                        $record_value = $record_model->shows($field_obj);	// value provided by Gino.Build::printValue()
                    }
                    
                    // shortens a string
                    if($opt_maxchars > 0) {
                    	
                    	if(strlen($record_value) >= $opt_maxchars) {
                    		$shorten = $this->shortenString($record_value, $opt_maxchars);
                    		$record_value = "<span class=\"link\" title=\"$record_value\" onclick=\"alert('$record_value');\">$shorten</span>";
                    	}
                    }

                    $export_row[] = $record_value;
                    $record_value = htmlChars($record_value);

                    if(isset($link_fields[$field_name]) && $link_fields[$field_name])
                    {
                        $link_field = $link_fields[$field_name]['link'];
                        $link_field_param = array_key_exists('param_id', $link_fields[$field_name]) ? $link_fields[$field_name]['param_id'] : 'id';

                        $link_field = $link_field.'&'.$link_field_param."=".$r['id'];

                        $record_value = "<a href=\"".$link_field."\">$record_value</a>";
                    }

                    $row[] = $record_value;
                }
            }

            $links = array();

            if(count($add_buttons))
            {
                foreach($add_buttons AS $value)
                {
                    if(is_array($value))
                    {
                        $label_button = array_key_exists('label', $value) ? $value['label'] : null;
                        $link_button = array_key_exists('link', $value) ? $value['link'] : null;
                        $param_id_button = array_key_exists('param_id', $value) ? $value['param_id'] : 'id';

                        if($label_button && $link_button && $param_id_button)
                        {
                            $link_button = strpos($link_button, '?') === FALSE ? $link_button.'?'.$param_id_button."=".$r['id'] : $link_button.'&'.$param_id_button."=".$r['id'];
                            $links[] = "<a href=\"$link_button\">$label_button</a>";
                        }
                    }
                }
            }

            $add_params_edit = array('edit'=>1, 'id'=>$r['id']);
            $add_params_delete = array('delete'=>1, 'id'=>$r['id']);
            if(count($addParamsUrl))
            {
                foreach($addParamsUrl AS $key=>$value)
                {
                    $add_params_edit[$key] = $value;
                    $add_params_delete[$key] = $value;
                }
            }
            
            if(($this->_edit_deny != 'all' && !in_array($r['id'], $this->_edit_deny))
            		OR (is_array($this->_edit_allow) && in_array($r['id'], $this->_edit_allow))) {
                $links[] = "<a href=\"".$this->editUrl($add_params_edit)."\">".\Gino\icon('modify', array('scale' => 1))."</a>";
            }
            if($this->_delete_deny != 'all' && !in_array($r['id'], $this->_delete_deny)) {
                $links[] = "<a href=\"javascript: if(confirm('".jsVar(sprintf(_("Sicuro di voler eliminare \"%s\"?"), $record_model))."')) location.href='".$this->editUrl($add_params_delete)."';\">".\Gino\icon('delete', array('scale' => 1))."</a>";
            }
            $buttons = array(
                array('text' => implode(' &#160; ', $links), 'class' => 'nowrap')
            ); 

            if($export) {
            	$items[] = $export_row;
            }
            $rows[] = array_merge($row, $buttons);
        }

        if($export)
        {
        	require_once(CLASSES_DIR.OS.'class.Export.php');
        	$obj_export = new Export();
        	$obj_export->setData($items);
        	return $obj_export->exportData($filename_export);
        }

        if($tot_ff) {
            $caption = sprintf(_('Risultati %s di %s'), $tot_records, $tot_records_no_filters);
        }
        else {
            $caption = '';
        }

        $this->_view->setViewTpl('table');
        $this->_view->assign('class', 'table table-striped table-hover');
        $this->_view->assign('caption', $caption);
        $this->_view->assign('heads', $heads);
        $this->_view->assign('rows', $rows);

        $table = $this->_view->render();

        if($this->_allow_insertion) {
            $link_insert = "<a href=\"".$this->editUrl(array('insert'=>1))."\">".\Gino\icon('insert', array('scale' => 2))."</a>";
        }
        else {
            $link_insert = "";
        }
        
        if($basic_export) {
        	$link_export = "<a href=\"".$this->editUrl(array('export'=>1))."\">".\Gino\icon('export', array('scale' => 2))."</a>";
        }
        else {
        	$link_export = null;
        }
        
        $this->_registry->addJs(SITE_JS."/admin.js");

        $this->_view->setViewTpl('admin_table_list');
        $this->_view->assign('title', $list_title);
        $this->_view->assign('description', $list_description);
        
        if($advanced_export)
        {
        	// @see formGetData()
        	$this->_view->assign('link_modal', $this->editUrl(array(
        		'action' => 'getdata',
        		'where' => base64_encode(implode(' AND ', $query_where)),
        		'model' => base64_encode(get_class($model))
        	)));
        	$this->_view->assign('model_name', get_class($model));
        }
        else {
        	$this->_view->assign('link_modal', null);
        	$this->_view->assign('model_name', null);
        }
        
        $this->_view->assign('link_insert', $link_insert);
        $this->_view->assign('link_export', $link_export);
        $this->_view->assign('search_icon', \Gino\icon('search', array('scale' => 2)));
        $this->_view->assign('table', $table);
        $this->_view->assign('tot_records', $tot_records);
        $this->_view->assign('form_filters_title', _("Filtri"));
        $this->_view->assign('form_filters', $tot_ff ? $this->formFilters($model, $options_view) : null);
        $this->_view->assign('pagination', $paginator->pagination());
        
        return $this->_view->render();
    }
    
    /**
     * @brief Accorcia una stringa a un numero massimo di caratteri
     * @param string $string stringa da accorciare
     * @param integer $maxchars numero massimo di caratteri
     * @return string
     */
    private function shortenString($string, $maxchars) {
    	
    	if(!$maxchars) {
    		return $string;
    	}
    	
    	$n_start = (int) $maxchars/2;
    	$n_end = (int) $maxchars/4;
    	$shorten = substr($string, 0, $n_start). " ... " . substr($string, -$n_end);
    	
    	return $shorten;
    }

    /**
     * @brief Imposta le variabili di sessione usate per filtrare i record nella lista amministrativa
     * 
     * @param \Gino\Model $model istanza di Gino.Model
     * @return void
     */
    protected function setSessionSearch($model) {

        $model_structure = $model->getStructure();
        $class_name = get_class($model);

        foreach($this->_filter_fields as $fname) {

            if(!isset($this->_session->{$class_name.'_'.$fname.'_filter'})) {
                $this->_session->{$class_name.'_'.$fname.'_filter'} = null;
            }
        }

        if(isset($this->_request->POST['ats_submit'])) {

            foreach($this->_filter_fields as $fname) {
                if(isset($this->_request->POST[$fname]) && $this->_request->POST[$fname] !== '') {
                    
                	$build = $model->build($model_structure[$fname]);
                	$this->_session->{$class_name.'_'.$fname.'_filter'} = $build->cleanFilter($this->_request->POST[$fname], array("escape"=>false));
                }
                else {
                    $this->_session->{$class_name.'_'.$fname.'_filter'} = null;
                }
            }
        }
    }

    /**
     * @brief Setta le variabili di sessione usate per filtrare i record nella lista amministrativa (riferimento ai filtri non automatici)
     * 
     * @param \Gino\Model $model istanza di Gino.Model
     * @param array $filters elenco dei filtri
     * @return void
     */
    protected function setSessionSearchAdd($model, $filters) {

        $class_name = get_class($model);

        foreach($filters as $array) {

            if(is_array($array) && array_key_exists('name', $array))
            {
                $fname = $array['name'];

                if(!isset($this->_session->{$class_name.'_'.$fname.'_filter'})) {
                    $this->_session->{$class_name.'_'.$fname.'_filter'} = null;
                }
            }
        }

        if(isset($this->_request->POST['ats_submit'])) {

            foreach($filters as $array) {

                if(is_array($array) and array_key_exists('name', $array))
                {
                    $fname = $array['name'];

                    if(isset($this->_request->POST[$fname]) and $this->_request->POST[$fname] !== '') {
                        $this->_session->{$class_name.'_'.$fname.'_filter'} = $this->clean($fname, $array);
                    }
                    else {
                        $this->_session->{$class_name.'_'.$fname.'_filter'} = null;
                    }
                }
            }
        }
    }

    /**
     * @brief Aggiunge le condizioni where usate per filtrare i record nella admin list all'argomento $query_where passato per reference
     *
     * @see self::addWhereJoin()
     * @see self::addWhereExtra()
     * @param array $query_where reference all'array di where clauses già impostate
     * @param \Gino\Model $model istanza di Gino.Model
     * @return void
     */
    protected function addWhereClauses(&$query_where, $model) {

        $model_structure = $model->getStructure();
        $class_name = get_class($model);
        
        $model_table = $model->getTable();

        foreach($this->_filter_fields as $fname) {
            if(isset($this->_session->{$class_name.'_'.$fname.'_filter'})) {

                // Filtri aggiuntivi associati ai campi automatici
                if(count($this->_filter_join))
                {
                    $where_join = $this->addWhereJoin($model, $class_name, $fname);
                    if(!is_null($where_join)) {
                        $query_where[] = $where_join;
                    }
                    else {
                        $build = $model->build($model_structure[$fname]);
                    	$query_where[] = $build->filterWhereClause($this->_session->{$class_name.'_'.$fname.'_filter'});
                    }
                }
                else {
                	$build = $model->build($model_structure[$fname]);
                	$query_where[] = $build->filterWhereClause($this->_session->{$class_name.'_'.$fname.'_filter'});
                }
            }
        }

        // Filtri aggiuntivi non associati ai campi automatici
        if(count($this->_filter_add))
        {
            $where_add = $this->addWhereExtra($class_name);

            if(count($where_add))
            {
                foreach($where_add AS $value)
                {
                    if(!is_null($value)) $query_where[] = $value;
                }
            }
        }
    }

    /**
     * @brief Elementi che concorrono a determinare le condizioni di ricerca dei campi automatici
     *
     * Ci può essere una solo campo input di tipo join.
     *
     * @param object $model modello
     * @param string $class_name nome della classe
     * @param string $fname nome del campo della tabella al quale associare le condizioni aggiuntive
     * @return array di condizioni o null
     */
    private function addWhereJoin($model, $class_name, $fname) {

    	$model_structure = $model->getStructure();
    	
    	foreach($this->_filter_join AS $array)
        {
            $field = gOpt('field', $array, null);

            if(($field && $field == $fname))
            {
                $ff_name = $array['name'];
                $ff_where_clause = array();

                if(isset($this->_session->{$class_name.'_'.$ff_name.'_filter'}))
                {
                    $ff_data = $array['data'];
                    $ff_value = $this->_session->{$class_name.'_'.$ff_name.'_filter'};

                    if(array_key_exists('where_clause', $array))
                    {
                        $ff_where_clause_key = $array['where_clause'];
                        $ff_where_clause_value = array_key_exists($ff_value, $ff_data) ? $ff_data[$ff_value] : null;

                        $ff_where_clause = array($ff_where_clause_key=>$ff_where_clause_value);
                    }
                }

                $build = $model->build($model_structure[$fname]);
                
                return $build->filterWhereClause($this->_session->{$class_name.'_'.$fname.'_filter'}, $ff_where_clause);
            }
        }

        return null;
    }

    /**
     * @brief Definizione delle condizioni di ricerca aggiuntive a quelle sui campi automatici
     * 
     * La condizione da inserire nella query di ricerca viene definita nel metodo indicato come valore della chiave @a filter. Il metodo deve essere creato di volta in volta.
     * 
     * @param string $class_name nome della classe
     * @return array di condizioni
     */
    private function addWhereExtra($class_name) {

        $where = array();

        foreach($this->_filter_add AS $array)
        {
            $ff_name = $array['name'];

            if(isset($this->_session->{$class_name.'_'.$ff_name.'_filter'}))
            {
                $ff_value = $this->_session->{$class_name.'_'.$ff_name.'_filter'};
            }
            else
            {
                $ff_value = null;
            }
            $ff_filter = $array['filter'];

            if($ff_filter) $where[] = $this->{$ff_filter}($ff_value);
        }

        return $where;
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
     * @brief Form per filtraggio record
     * 
     * @see self::permission()
     * @see self::formFiltersAdd()
     * @see Gino.Build::formFilter()
     * @param \Gino\Model $model istanza di Gino.Model
     * @param array $options autorizzazioni alla visualizzazione dei singoli campi
     * @return form html
     */
    protected function formFilters($model, $options) {

        $model_structure = $model->getStructure();
        $class_name = get_class($model);

        $gform = new Form();

        $form = $gform->open($this->editUrl(array(), array('start')), false, '', array(
        	'form_id' => 'atbl_filter_form', 
        	'validation' => false
        ));

        foreach($this->_filter_fields as $fname) {

            if($this->permission($options, $fname))
            {
            	$field = $model_structure[$fname];
            	
            	$field_label = $field->getLabel();
                if(is_array($field_label)) {
                    $field->setLabel($field_label[0]);
                }
                
                $build = $model->build($field);
                $build->setValue($this->_session->{$class_name.'_'.$fname.'_filter'});
                
                $form .= $build->formFilter(array('default'=>null));

                $form .= $this->formFiltersAdd($this->_filter_join, $fname, $class_name);
                $form .= $this->formFiltersAdd($this->_filter_add, $fname, $class_name);
            }
        }

        $onclick = "onclick=\"$$('#atbl_filter_form input, #atbl_filter_form select').each(function(el) {
            if(el.get('type')==='text') el.value='';
            else if(el.get('type')==='radio') el.removeProperty('checked');
            else if(el.get('tag')=='select') el.getChildren('option').removeProperty('selected');
        });\"";

        $input_reset = Input::input('ats_reset', 'button', _("tutti"), array("classField"=>"generic", "js"=>$onclick));
        $form .= Input::input_label('ats_submit', 'submit', _("filtra"), '', array("classField"=>"submit", "text_add"=>' '.$input_reset));
        $form .= $gform->close();

        return $form;
    }

    /**
     * @brief Input form dei filtri aggiuntivi
     * 
     * @param array $filters elenco dei filtri; ogni filtro a sua volta è un array i cui elementi permettono di definire l'input form
     *   le chiavi dell'array filtro sono le seguenti
     *   - @a field (string): nome del campo che precede l'input form aggiuntivo nel form di ricerca
     *   - @a name (string): nome dell'input
     *   - @a label (string): nome della label
     *   - @a data (array): elementi che compongono gli input form radio e select
     *   - @a default (string): valore di default
     *   - @a input (string): tipo di input form, valori disponibili: radio (default), select, date, text
     *   - @a filter (string): nome del metodo da richiamare per ricavare la condizione per la query; il metodo dovrà essere creato in una classe che estende @a AdminTable()
     *   inoltre ogni filtro contiene le opzioni da passare al metodo clean
     *   - @a value_type (string): tipo di dato (default string)
     *   - @a method (array): default $this->_request->POST
     *   - @a escape (boolean): default true
     * @param string $fname nome del campo della tabella al quale far seguire gli eventuali filtri aggiuntivi
     * @param string $class_name nome della classe
     * @return elementi del form in html
     */
    private function formFiltersAdd($filters, $fname, $class_name) {

        $form = '';

        if(count($filters))
        {
            foreach($filters AS $array)
            {
                $field = gOpt('field', $array, null);

                if(($field && $field == $fname))
                {
                    $ff_name = $array['name'];
                    $ff_value = $this->_session->{$class_name.'_'.$ff_name.'_filter'};
                    $ff_label = gOpt('label', $array, '');
                    $ff_data = gOpt('data', $array, array());
                    $ff_default = gOpt('default', $array, '');
                    $ff_input = gOpt('input', $array, 'radio');

                    if($ff_input == 'radio')
                    {
                        $form .= Input::radio_label($ff_name, $ff_value, $ff_data, $ff_default, $ff_label, array('required'=>false));
                    }
                    elseif($ff_input == 'select')
                    {
                        $form .= Input::select($ff_name, $ff_value, $ff_data, $ff_label, array('required'=>false));
                    }
                    elseif($ff_input == 'date')
                    {
                    	$form .= Input::input_date($ff_name, $ff_value, $ff_label, array('required'=>false));
                    }
                    else
                    {
                        $form .= Input::input_label($ff_name, 'text', $ff_value, $ff_label, array('required'=>false));
                    }
                }
            }
        }
        return $form;
    }

    /**
     * @brief Costruisce il percorso per il reindirizzamento
     *
     * @param array $add_params elenco parametri da aggiungere al path (Gino.Http.Request::path) (formato chiave=>valore)
     * @param array $remove_params elenco parametri da rimuovere dal path (Gino.Http.Request::path)
     * @return url ricostruito
     */
    protected function editUrl($add_params = array(), $remove_params = array()) {

        return $this->_registry->router->transformPathQueryString($add_params, $remove_params);
     }

    /**
     * @brief Ripulisce l'input di un form di ricerca
     * 
     * @param string $name nome dell'input form
     * @param array $options array associativo di opzioni
     *   - @b value_type (string)
     *   - @b method (array)
     *   - @b escape (boolean)
     * @return valore ripulito
     */
    private function clean($name, $options=null) {

        $value_type = isset($options['value_type']) ? $options['value_type'] : 'string';
        $method = isset($options['method']) ? $options['method'] : $this->_request->POST;
        $escape = gOpt('escape', $options, true);

        return cleanVar($method, $name, $value_type, null, array('escape'=>$escape));
    }
    
    /**
     * @brief Form per l'esportazione avanzata dei record
     *
     * @param array $options_view opzioni della vista (comprese le autorizzazioni a visualizzare singoli campi)
     * @return \Gino\Http\Response
     */
    protected function formGetData($options_view=array()) {
    	
    	$action = \Gino\cleanVar($this->_request->GET, 'action', 'string');
    	$where = \Gino\cleanVar($this->_request->GET, 'where', 'string');	// value base64_encode
    	$model = \Gino\cleanVar($this->_request->GET, 'model', 'string');	// value base64_encode
    	
    	if($action != 'getdata') {
    		return new \Gino\Http\Response(null);
    	}
    	
    	$model_name = base64_decode($model);
    	
    	$c = $model_name::$columns;
    	
    	$checkbox = '';
    	$hidden = '';
    	$checked = false;
    	
    	if(count($c)) {
    
    		$hidden .= Input::hidden('action', $action);
    		$hidden .= Input::hidden('where', $where);
    		$hidden .= Input::hidden('model', $model);
    
    		foreach ($c AS $field_name => $field_object) {
    			
    			// rimuovere i campi ManyToManyThroughField dall'esportazione
    			if(!is_a($field_object, '\Gino\ManyToManyThroughField')) {
    				$checkbox .= Input::checkbox('fields[]', $checked, $field_name, array()).' '.$field_object->getLabel()."<br />";
    			}
    		}
    	}
    	
    	$this->_view->setViewTpl('admin_table_export');
    	$this->_view->assign('form_action', $this->editUrl());
    	$this->_view->assign('hidden', $hidden);
    	$this->_view->assign('checkbox', $checkbox);
    
    	return new \Gino\Http\Response($this->_view->render());
    }
    
    /**
     * @brief Esportazione dei dati
     * @param array $options_view opzioni della vista (comprese le autorizzazioni a visualizzare singoli campi)
     * @return \Gino\Http\ResponseFile
     */
    protected function actionGetData($options_view=array()) {
    	
    	$action = cleanVar($this->_request->POST, 'action', 'string');
    	$where = cleanVar($this->_request->POST, 'where', 'string');	// value base64_encode
    	$model = cleanVar($this->_request->POST, 'model', 'string');	// value base64_encode
    	
    	$link_return = $this->editUrl(array(), array('action', 'where', 'model'));
    	
    	if($action != 'getdata') {
    		return Error::errorMessage(array('error' => _("Operazione errata")), $link_return);
    	}
    	 
    	if(!isset($this->_request->POST['fields'])) {
    		return Error::errorMessage(array('error' => _("Selezionare almeno un campo dall'elenco")), $link_return);
    	}
    	 
    	$where = base64_decode($where);
    	$model_class = base64_decode($model);
    	 
    	$checkbox = $this->_request->POST['fields'];
    	$values = clean_array($checkbox, array('asforminput' => true, 'datatype' => 'string'));
    	
    	if(count($values) == 0) {
    		$values = array("*");
    	}
    	
    	$model_obj = new $model_class(null, $this->_controller);
    	
    	$items = $this->getData($model_obj, $values, $where, $options_view);
    	
    	$filename_export = date("Y-m-d").'-'.get_name_class($model_class).'.csv';
    	
    	require_once(CLASSES_DIR.OS.'class.Export.php');
    	$obj_export = new Export();
    	$obj_export->setData($items);
    	return $obj_export->exportData($filename_export);
    }
    
    /**
     * @brief Ricava i dati dei record da esportare
     * @param object $model
     * @param array $fields
     * @param string $where
     * @param array $options_view
     * @return array
     */
    private function getData($model, $fields, $where, $options_view) {
    	
    	$db = Db::instance();
    	// Struttura dei campi del modello (fieldname=>object Field)
    	$model_structure = $model->getStructure();
    	$model_table = $model->getTable();
    	
    	// fields to be shown
    	$fields_loop = array();
    	$fields_m2m = array();
    	if($fields) {
    		foreach($fields as $fname) {
    			if(is_array($fname)) {
    				$fields_loop[$fname['member']] = array(
    					'member' => $fname['member'],
    					'label' => $fname['label']
    				);
    			}
    			else {
    				if(is_a($model_structure[$fname], '\Gino\ManyToManyField')) {
    					$fields_m2m[] = $fname;
    				}
    				$fields_loop[$fname] = $model_structure[$fname];
    			}
    		}
    	}
    	else {
    		$fields_loop = $model_structure;
    	}
    	
    	// Removes m2m fields in the construction of query selection
    	$fields_without_m2m = array_diff($fields, $fields_m2m);
    	
    	$query_selection = implode(', ', $fields_without_m2m);
    	if(!array_key_exists('id', $fields_without_m2m)) {
    		$query_selection = 'id, '.$query_selection;
    	}
    	
    	$records = $db->select($query_selection, $model_table, $where, array());
    	if(!$records) $records = array();
    	
    	$heads = array();
    	$rows = array();
    	
    	foreach($fields_loop as $field_name=>$field_obj) {
    
    		if($this->permission($options_view, $field_name))
    		{
    			if(is_array($field_obj)) {
    				$label = $field_obj['label'];
    			}
    			else {
    				$model_label = $model_structure[$field_name]->getLabel();
    				$label = is_array($model_label) ? $model_label[0] : $model_label;
    			}
    			$heads[] = $label;
    		}
    	}
    	$rows[] = $heads;
    	
    	// Data
    	foreach($records as $r) {
    
    		$record_model = new $model($r['id'], $this->_controller);
    
    		$row = array();
    		foreach($fields_loop as $field_name=>$field_obj) {
    			
    			if($this->permission($options_view, $field_name))
    			{
    				if(is_array($field_obj)) {
    					$member = $field_obj['member'];
    					$record_value = $record_model->$member();
    				}
    				else {
    					$record_value = $record_model->shows($field_obj);	// @see Gino.Build::printValue()
    				}
    
    				$row[] = $record_value;
    			}
    		}
    		$rows[] = $row;
    	}
    	
    	return $rows;
    }
}
