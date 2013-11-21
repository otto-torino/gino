<?php
/**
 * @file class.makeList.php
 * @brief Contiene la classe makeList
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

/**
 * @brief Gestisce un elenco di elementi
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 * 
 * La libreria deve essere inclusa all'inizio del file della classe che la deve utilizzare:
 * @code
 * require_once(CLASSES_DIR.OS.'class.makeList.php');
 * @endcode
 * 
 * I filtri di ricerca aggiuntivi devono essere implementati estendendo la classe e creando il o i metodi indicati nell'opzione @filter dell'opzione @a filter_add. \n
 * I metodi hanno come proprietà il valore in arrivo dal campo di ricerca associato ed eventualmente un valore che funge da riferimento nella query di ricerca (opzione @a where_value dell'opzione @a filter_add). \n
 * Se l'opzione @a where_value non è indicata, il suo valore viene impostato a null. \n
 * Esempio:
 * @code
 * protected function filterWhereInscript($value, $where_value) {
 *   //
 * }
 * @endcode
 */
class makeList {

	protected $_controller;
	protected $_db, $session, $_form;
	protected $_view;
	
	/**
	 * Filtri per la ricerca relativi alla tabella principale (corrispondono ai nomi dei campi della tabella)
	 * 
	 * @var array
	 */
	protected $_filter_fields;
	
	/**
	 * Filtri aggiuntivi associati ai campi della tabella principale (concorrono alla definizione delle loro condizioni)
	 * 
	 * @var array
	 */
	protected $_filter_add;
	
	protected $_list_display, $_list_remove;
	protected $_ifp;
	
	/**
	 * Costruttore
	 * 
	 * @param object $instance oggetto dell'istanza
	 * @param array $opts
	 *   array associativo di opzioni
	 *   - @b view_folder (string): percorso della directory contenente la vista da caricare
	 */
	function __construct($instance, $opts = array()) {

		$this->_controller = $instance;
		$this->_db = db::instance();
		$this->session = session::instance();
		
		$view_folder = gOpt('view_folder', $opts, null);
		
		$this->_form = new Form('', '', '');
		$this->_view = new view($view_folder);
		
		$this->_instance_name = $this->_controller->getInstance();
	}
	
	/**
	 * Elenco dei campi che compaiono nel SELECT
	 * 
	 * @param string $table nome della tabella dei campi selezionati
	 * @return array
	 */
	protected function selectFields($table) {
		
		$selection = array();
		if($table)
		{
			if((substr($table, -1) != '.')) $table = $table.'.';
		}
		
		if(count($this->_list_display))
		{
			foreach($this->_list_display AS $key=>$options)
			{
				$selection[] = $table.$key;
			}
		}
		return $selection;
	}

	/**
	 * Lista dei record
	 * 
	 * @see setSessionSearch()
	 * @see setSessionSearchAdd()
	 * @see addTable()
	 * @see addWhereClauses()
	 * @see headsList()
	 * @see condition()
	 * @param object $model
	 * @param array $options_view
	 *   array associativo di opzioni
	 *   - @b filter_fields (array): campi sui quali applicare il filtro per la ricerca automatica
	 *   - @b filter_add (array): contiene le proprietà degli input form che vengono aggiunti come filtro per la ricerca automatica
	 *     - @a field (string): nome del campo che precede l'input form aggiuntivo nel form di ricerca
	 *     - @a name (string): nome dell'input
	 *     - @a label (string): nome della label
	 *     - @a data (array): elementi che compongono gli input form radio e select
	 *     - @a input (string): tipo di input form, valori validi: radio (default), select
	 *     - @a where_value (string)
	 *     - @a table_add (mixed)
	 *       string, elenco delle tabelle da aggiungere al FROM (separate da virgola), indipendentemente dai valori della ricerca
	 *       array, elenco delle tabella nel formato: {nome_tabella}=>{valore della ricerca in riferimento al quale caricare la tabella nel FROM}
	 *     - @a filter (string): nome del metodo da richiamare per la condizione aggiuntiva; il metodo dovrà essere creato in una classe che estenda @a makeList() \n
	 *     Esempio:
	 *     @code
	 *     'filter_add'=>array(
	 *       array(
	 *         'field'=>'description', 
	 *         'label'=>_("Utenti iscritti"), 
	 *         'name'=>'inscript', 
	 *         'data'=>array('no'=>_("no"), 'yes'=>_("si")), 
	 *         'where_value'=>$course_id, 
	 *         'table_add'=>array('nome_tabella_da_aggiungere'=>'yes'), 
	 *         'filter'=>'filterWhereInscript'
	 *       )
	 *     )
	 *     @endcode
	 *   - @b list_display (array): campi mostrati nella lista (se vuoto mostra tutti), nel formato field_name=>array(options_key=>options_value)
	 *     - @a label (string): intestazione del campo
	 *     - @a ordered (boolean): attivare l'ordinamento (default: true)
	 *     - @a view (boolean): visualizzare l'intestazione (default: true)
	 *   - @b list_remove (array): campi da non mostrare nella lista (default: instance)
	 *   - @b items_for_page (integer): numero di record per pagina
	 *   - @b list_title (string): titolo
	 *   - @b list_description (string): descrizione sotto il titolo (informazioni aggiuntive)
	 *   - @b query_table (array)
	 *   - @b query_where (array)
	 *   - @b table (string): nome/alias della tabella da utilizzare come prefisso dei campi nella query
	 *   - @b field_search (array): indica in che modo effettuare la ricerca per un dato campo, esempio: array(field1=>'equal', field2=>'like'[,...])
	 *   - @b link_delete (boolean): attivare il link di eliminazione di un record (default: false)
	 *   - @b options_delete (array): opzioni dell'eliminazione di un record
	 *     - @a field (string): nome del campo di riferimento (default: id)
	 *     - @a text (string): testo del javascript di eliminazione
	 *   - @b add_params_url (array): parametri aggiuntivi da passare ai link delle operazioni sui record, nel formato array(key=>value[,])
	 *   - @b input_first (boolean): riserva la prima colonna a un campo input (default true)
	 *   - @b tr_class (string): classe css del tag TR utilizzato per evidenziare alcuni record
	 * @return string
	 * 
	 * @example Per mettere in evidenza alcuni record con la classe di css @a tr_class si può inserire nel ciclo: 
	 * @code
	 * foreach($this->_list_display AS $key=>$options_field) { ... }
	 * @endcode 
	 * una condizione simile:
	 * @code
	 * if($key == 'id' && $reference_id && $this->condition($r[$key], $reference_id))
	 * {
	 *   $row['evidence'] = true;
	 * }
	 * @endcode
	 */
	public function printList($options=array()) {
		
		$this->_filter_fields = gOpt('filter_fields', $options, array());
		$this->_filter_add = gOpt('filter_add', $options, array());
		$this->_list_display = gOpt('list_display', $options, array());
		$this->_ifp = gOpt('items_for_page', $options, 20);
		$list_title = gOpt('list_title', $options, '');
		$list_description = gOpt('list_description', $options, '');
		$query_table = gOpt('query_table', $options, array());
		$query_where = gOpt('query_where', $options, array());
		$table = gOpt('table', $options, '');
		$field_search = gOpt('field_search', $options, array());
		$link_delete = gOpt('link_delete', $options, false);
		$options_delete = gOpt('options_delete', $options, array());
		$add_params_url = gOpt('add_params_url', $options, array());
		$input_first = gOpt('input_first', $options, false);
		$tr_class = gOpt('tr_class', $options, null);
		
		if($table) $table = $table.'.';
		
		$query_selection_tot = "COUNT(".$table."id) as tot";
		$query_selection = $this->selectFields($table);
		
		// ordinamento
		$order = cleanVar($_GET, 'order', 'string', '');
		if(!$order) $order = 'id DESC';
		// get order field and direction
		preg_match("#^([^ ,]*)\s?((ASC)|(DESC))?.*$#", $order, $matches);
		$field_order = isset($matches[1]) && $matches[1] ? $matches[1] : '';
		$order_dir = isset($matches[2]) && $matches[2] ? $matches[2] : '';

		// filter form
		$tot_ff = count($this->_filter_fields);
		if($tot_ff) $this->setSessionSearch($this->_instance_name);
		
		$tot_ff_add = count($this->_filter_add);
		if($tot_ff_add) $this->setSessionSearchAdd($this->_instance_name, $this->_filter_add);

		$query_table = $this->addTable($query_table);

		// filters
		if($tot_ff) {
			$this->addWhereClauses($query_where, $this->_instance_name, array('table'=>$table, 'field_search'=>$field_search));
		}
		// order
		$query_order = $table.$field_order." ".$order_dir;
		
		$tot_records_result = $this->_db->select($query_selection_tot, $query_table, implode(' AND ', $query_where));
		$tot_records = $tot_records_result[0]['tot'];

		$pagelist = new PageList($this->_ifp, $tot_records, 'array');
		$limit = array($pagelist->start(), $pagelist->rangeNumber);
		
		$records = $this->_db->select($query_selection, $query_table, implode(' AND ', $query_where), array('order'=>$query_order, 'limit'=>$limit));
		if(!$records) $records = array();
		
		$heads = $this->headsList($this->_list_display, $order, $add_params_url, $input_first);
		$rows = array();
		
		if(count($records))
		{
			$form = $this->form($options);
			$form_start = $form['start'];
			$form_end = $form['end'];
			
			foreach($records as $r)
			{
				$row = array();
				if($input_first) $row[] = '';
				
				foreach($this->_list_display AS $key=>$options_field)
				{
					$field_view = array_key_exists('view', $options_field) ? $options_field['view'] : true;
					if($field_view)
					{
						$value = $this->condition($key, $r[$key]);
						
						if(!is_null($value))
							$row[] = $value;
						else
							$row[] = htmlChars($r[$key]);
					}
				}
				
				$links = array();
				
				if($link_delete)
				{
					$field_delete = array_key_exists('field', $options_delete) ? $options_delete['field'] : 'id';
					$text_delete = array_key_exists('text', $options_delete) ? $options_delete['text'] : _("Sei sicuro di voler eliminare il riferimento?");
					$links[] = $this->linkDelete($r[$field_delete], $field_delete, $text_delete, $add_params_url);
				}
				$buttons = array(
					array('text' => implode(' ', $links), 'class' => 'no_border no_bkg')
				);
				
				$rows[] = array_merge($row, $buttons);
			}
		}
		else
		{
			$form_start = null;
			$form_end = null;
		}
		
		$this->_view->setViewTpl('table');
		$this->_view->assign('form_start', $form_start);
		$this->_view->assign('form_end', $form_end);
		$this->_view->assign('class', 'generic');
		$this->_view->assign('caption', '');
		$this->_view->assign('tr_class', $tr_class);
		$this->_view->assign('heads', $heads);
		$this->_view->assign('rows', $rows);

		$table = $this->_view->render();

		$this->_view->setViewTpl('admin_table_list');
		$this->_view->assign('title', $list_title);
		$this->_view->assign('description', $list_description);
		$this->_view->assign('link_insert', '');
		$this->_view->assign('table', $table);
		$this->_view->assign('tot_records', $tot_records);
		$this->_view->assign('form_filters_title', _("Filtri"));
		$this->_view->assign('form_filters', $tot_ff ? $this->formFilters($this->_instance_name) : null);
		$this->_view->assign('pnavigation', $pagelist->listReferenceGINO($_SERVER['REQUEST_URI'], false, '', '', '', false, null, null, array('add_no_permalink'=>true)));
		$this->_view->assign('psummary', $pagelist->reassumedPrint());

		return $this->_view->render();
	}
	
	/**
	 * Condizioni da verificare con le celle della tabella per (eventualmente) personalizzare l'output
	 * 
	 * Se il metodo ritorna un valore nullo, la cella di tabella mostrerà il valore come da database.
	 * 
	 * @param string $name nome del campo
	 * @param string $value valore del campo
	 * @return mixed
	 * 
	 * Esempio:
	 * @code
	 * if($name == 'valid')
	 * {
	 *   $print = $value ? _("si") : _("no");	// $value is boolean
	 * }
	 * else $print = null;
	 * 
	 * return $print;
	 * @endcode
	 */
	protected function condition($name, $value) {
		
		return null;
	}
	
	/**
	 * Permette di gestire un form associato alla lista dei record
	 * 
	 * @param array $options array associativo di opzioni in arrivo dal metodo prinList()
	 * @return array('start', 'end')
	 * 
	 * Per creare un form occorre istanziare la classe e sovrascrivere il metodo. \n
	 * Esempio:
	 * @code
	 * $gform = new Form('name_form', 'post', false);
	 * $form_start = $gform->form($form_action, false, '');
	 * $form_start .= $gform->hidden('rid', $reference);
	 * $form_start .= $gform->hidden('order', $order);
	 * $form_start .= $gform->hidden('start', $pagelist->start());
	 * 
	 * $form_end = $gform->cinput('submit', 'submit', _("iscrivi"), '', array("classField"=>'submit'));
	 * $form_end .= $gform->cform();
	 * 
	 * return array('start'=>$form_start, 'end'=>$form_end);
	 * @endcode
	 */
	protected function form($options=array()) {
		
		return array('start'=>null, 'end'=>null);
	}
	
	/**
	 * Indirizzo per l'eliminazione di un elemento
	 * 
	 * @param mixed $reference
	 * @param string $field
	 * @param string $text
	 * @param array $add_params_url
	 * @return string
	 * 
	 * Esempio per la sovrascrittura del testo:
	 * @code
	 * htmlspecialchars(sprintf(_("Sicuro di voler eliminare l'iscrizione di \"%s\"?"), $record_model), ENT_QUOTES)."')
	 * @endcode
	 */
	protected function linkDelete($reference, $field, $text, $add_params_url) {
		
		$add_params_delete = array('delete'=>1, $field=>$reference);
		if(count($add_params_url))
		{
			foreach($add_params_url AS $key=>$value)
			{
				$add_params_delete[$key] = $value;
			}
		}
		
		$links = "<a href=\"javascript: if(confirm('".$text."')) location.href='".$this->editUrl($add_params_delete)."';\">".pub::icon('delete')."</a>";
		return $links;
	}

	/**
	 * Setta le variabili di sessione usate per filtrare i record nella lista amministrativa
	 * 
	 * @see clean()
	 * @param string $instance_name
	 * @return void
	 */
	protected function setSessionSearch($instance_name) {

		foreach($this->_filter_fields as $fname) {

			if(!isset($this->session->{$instance_name.'_'.$fname.'_filter'})) {
				$this->session->{$instance_name.'_'.$fname.'_filter'} = null;
			}
		}

		if(isset($_POST['ats_submit'])) {

			foreach($this->_filter_fields as $fname) {

				if(isset($_POST[$fname]) && $_POST[$fname] !== '') {
					$this->session->{$instance_name.'_'.$fname.'_filter'} = $this->clean($fname, array("escape"=>false));
				}
				else {
					$this->session->{$instance_name.'_'.$fname.'_filter'} = null;
				}
			}
		}
	}
	
	/**
	 * Setta le variabili di sessione usate per filtrare i record nella lista amministrativa (riferimento a tabelle aggiuntive)
	 * 
	 * @see clean()
	 * @param string $instance_name
	 * @param array $filters elenco dei filtri
	 * @return void
	 */
	protected function setSessionSearchAdd($instance_name, $filters) {

		foreach($filters as $array) {

			if(is_array($array) && array_key_exists('name', $array))
			{
				$fname = $array['name'];
				
				if(!isset($this->session->{$instance_name.'_'.$fname.'_filter'})) {
					$this->session->{$instance_name.'_'.$fname.'_filter'} = null;
				}
			}
		}

		if(isset($_POST['ats_submit'])) {

			foreach($filters as $array) {
				
				if(is_array($array) && array_key_exists('name', $array))
				{
					$fname = $array['name'];
					
					if(isset($_POST[$fname]) && $_POST[$fname] !== '') {
						$this->session->{$instance_name.'_'.$fname.'_filter'} = $this->clean($fname, $array);
					}
					else {
						$this->session->{$instance_name.'_'.$fname.'_filter'} = null;
					}
				}
			}
		}
	}
	
	/**
	 * Permette di aggiungere tabelle all'elenco delle tabelle nel FROM della query
	 * 
	 * @param array $tables elenco tabelle presenti nell'opzione @a query_table (vedi printList())
	 * @return array
	 */
	protected function addTable($tables) {
		
		foreach($this->_filter_add AS $array)
		{
			$ff_name = $array['name'];
			
			if(isset($this->session->{$this->_instance_name.'_'.$ff_name.'_filter'}))
			{
				$ff_value = $this->session->{$this->_instance_name.'_'.$ff_name.'_filter'};
				
				if(array_key_exists('table_add', $array) && !is_null($ff_value))
				{
					$table_add = $array['table_add'];
					if(is_string($table_add))
					{
						$tables[] = $table_add;
					}
					elseif(is_array($table_add))
					{
						foreach ($table_add AS $key=>$value)
						{
							if($value == $ff_value)
								$tables[] = $key;
						}
					}
				}
			}
		}
		
		return $tables;
	}
	
	protected function clean($name, $options=null) {
		
		return cleanVar($_POST, $name, 'string', null, $options);
	}
	
	/**
	 * Setta la condizione where usata per filtrare i record nell'elenco
	 * 
	 * @see addWhereExtra()
	 * @param array $query_where
	 * @param string $instance_name
	 * @param array $options
	 *   array associativo di opzioni
	 *   - @b table (string): nome/alias della tabella da utilizzare come prefisso dei campi nella query
	 *   - @b field_search (array): indica in che modo effettuare la ricerca per un dato campo, esempio: array(field1=>'equal', field2=>'like'[,...])
	 *     valori validi:
	 *     - @a equal
	 *     - @a like (default)
	 * @return string (the where clause)
	 * 
	 * Le condizioni legate ai filtri aggiuntivi vengono caricate soltanto se risultano non nulle.
	 */
	protected function addWhereClauses(&$query_where, $instance_name, $options=array()) {

		$table = array_key_exists('table', $options) ? $options['table'] : null;
		$field_search = array_key_exists('field_search', $options) ? $options['field_search'] : array();
		
		foreach($this->_filter_fields as $fname) {
			if(isset($this->session->{$instance_name.'_'.$fname.'_filter'})) {
				
				$field_name = $table.$fname;
				$field_value = $this->session->{$instance_name.'_'.$fname.'_filter'};
				
				if(array_key_exists($fname, $field_search) && $field_search[$fname] == 'equal')
				{
					$query_where[] = "$field_name='$field_value'";
				}
				else
				{
					if(preg_match("#^\"([^\"]*)\"$#", $field_value, $matches))
						$condition = "='".$matches[1]."'";
					elseif(preg_match("#^\"([^\"]*)$#", $field_value, $matches))
						$condition = " LIKE '".$matches[1]."%'";
					else
						$condition = " LIKE '%".$field_value."%'";
					
					$query_where[] = $field_name.$condition;
				}
			}
		}

		// Filtri aggiuntivi non associati ai campi "regolari"
		if(count($this->_filter_add))
		{
			$where_add = $this->addWhereExtra($instance_name);
			
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
	 * Definizione delle condizioni di ricerca aggiuntive a quelle sui campi della tabella di riferimento
	 * 
	 * @param string $instance_name nome dell'istanza
	 * @return array
	 * 
	 * Esempio di filtro di ricerca da implementare:
	 * @code
	 * protected function filterWhereInscript($value, $where_value) {
	 *   
	 *   $where = null;
	 *   
	 *   if(!is_null($value))
	 *   {
	 *     if($value == 'yes')
	 *     {
	 *       $where = 'user.id=join_user.user_id AND join_user.ref_id='$where_value'";
	 *     }
	 *     elseif($value == 'no')
	 *     {
	 *       $where = "user.id NOT IN (SELECT user_id FROM join_user WHERE ref_id='$where_value')";
	 *     }
	 *   }
	 *   return $where;
	 * }
	 * @endcode
	 */
	private function addWhereExtra($instance_name) {
		
		$where = array();
		
		foreach($this->_filter_add AS $array)
		{
			$ff_name = $array['name'];
			
			if(isset($this->session->{$instance_name.'_'.$ff_name.'_filter'}))
			{
				$ff_value = $this->session->{$instance_name.'_'.$ff_name.'_filter'};
			}
			else
			{
				$ff_value = null;
			}
			$ff_where_value = array_key_exists('where_value', $array) ? $array['where_value'] : null;
			$ff_filter = $array['filter'];
			
			if($ff_filter) $where[] = $this->{$ff_filter}($ff_value, $ff_where_value);
		}
		
		return $where;
	}

	/**
	 * Form per filtraggio record
	 * 
	 * @see editUrl()
	 * @see formFiltersAdd()
	 * @param string $instance_name nome dell'istanza
	 * @return il form
	 */
	protected function formFilters($instance_name) {

		$gform = new Form('atbl_filter_form', 'post', false);

		$form = $gform->form($this->editUrl(array(), array('start')), false, '');

		foreach($this->_filter_fields as $fname) {
			
			$field_value = $this->session->{$instance_name.'_'.$fname.'_filter'};
			$field_label = array_key_exists('label', $this->_list_display[$fname]) ? $this->_list_display[$fname]['label'] : ucfirst($fname);
			
			$form .= $gform->cinput($fname, 'text', $field_value, $field_label, array('required'=>false));
			
			$form .= $this->formFiltersAdd($this->_filter_add, $fname, $instance_name, $gform);
		}
		
		$onclick = "onclick=\"$$('#atbl_filter_form input, #atbl_filter_form select').each(function(el) {
			if(el.get('type')==='text') el.value='';
			else if(el.get('type')==='radio') el.removeProperty('checked');
			else if(el.get('tag')=='select') el.getChildren('option').removeProperty('selected');
		});\"";

		$input_reset = $gform->input('ats_reset', 'button', _("tutti"), array("classField"=>"generic", "js"=>$onclick));
		$form .= $gform->cinput('ats_submit', 'submit', _("filtra"), '', array("classField"=>"submit", "text_add"=>' '.$input_reset));
		$form .= $gform->cform();

		return $form;
	}
	
	/**
	 * Input form dei filtri aggiuntivi
	 * 
	 * @param array $filters elenco dei filtri
	 * @param string $fname nome del campo della tabella al quale far seguire gli eventuali filtri aggiuntivi
	 * @param string $instance_name nome dell'istanza
	 * @param object $gform
	 * @return string
	 */
	private function formFiltersAdd($filters, $fname, $instance_name, $gform) {
		
		$form = '';
		
		if(count($filters))
		{
			foreach($filters AS $array)
			{
				$field = gOpt('field', $array, null);
				
				if(($field && $field == $fname))
				{
					$ff_name = $array['name'];
					$ff_value = $this->session->{$instance_name.'_'.$ff_name.'_filter'};
					$ff_label = gOpt('label', $array, '');
					$ff_data = gOpt('data', $array, array());
					$ff_input = gOpt('input', $array, 'radio');
					
					if($ff_input == 'radio')
					{
						$form .= $gform->cradio($ff_name, $ff_value, $ff_data, '', $ff_label, array('required'=>false));
					}
					elseif($ff_input == 'select')
					{
						$form .= $gform->cselect($ff_name, $ff_value, $ff_data, $ff_label, array('required'=>false));
					}
					else
					{
						$form .= $gform->cinput($ff_name, 'text', $ff_value, $ff_label, array('required'=>false));
					}
				}
			}
		}
		return $form;
	}

	/**
	 * Costruisce il percorso per il reindirizzamento
	 * 
	 * @param array $add_params elenco parametri da aggiungere alla REQUEST_URI (formato chiave=>valore)
	 * @param array $remove_params elenco parametri da rimuovere dalla REQUEST_URI
	 * @return string
	 */
	public function editUrl($add_params, $remove_params=null) {

		$url = $_SERVER['REQUEST_URI'];

		if($remove_params) {
			foreach($remove_params as $key) {
				$url = preg_replace("#&?".preg_quote($key)."=[^&]*#", '', $url);
			}
		}
		
		if($add_params) {
			$add_url = '';
			foreach($add_params as $key=>$value) {
				$url = preg_replace("#&".preg_quote($key)."=[^&]*#", '', $url);
				$add_url .= '&'.$key.'='.$value;
			}

			if(preg_match("#\?#", $url)) {
				$url =  $url.$add_url;		
			}
			else {
				$url = $url."?".substr($add_url, 1);
			}
		}

		return $url;
	}
	
	/**
	 * Intestazione della tabella
	 * 
	 * @param array $fields elenco dei campi da mostrare nell'intestazione della lista, nel formato: nome_campo=>array_opzioni
	 *   opzioni previste:
	 *   - @b label (string): label del campo
	 *   - @b ordered (boolean): campo ordinabile (default: true)
	 *   - @b view (boolean): mostra la label (default: true)
	 * @param mixed $order parametro di ordinamento
	 * @param array $add_params parametri aggiuntivi
	 * @return array
	 */
	protected function headsList($fields, $order, $add_params=array(), $input_first) {
		
		$heads = array();
		
		if(count($fields) == 0) return $heads;
		
		if($input_first) $heads[] = '';
		
		foreach($fields as $field_name=>$options) {

			$label = array_key_exists('label', $options) ? $options['label'] : ucfirst($field_name);
			$ordered = array_key_exists('ordered', $options) ? $options['ordered'] : true;
			$view = array_key_exists('view', $options) ? $options['view'] : true;
			
			if($view && $ordered) {

				$ord = $order == $field_name." ASC" ? $field_name." DESC" : $field_name." ASC";
				if($order == $field_name." ASC") {
					$jsover = "$(this).getNext('.arrow').removeClass('arrow_up').addClass('arrow_down')";
					$jsout = "$(this).getNext('.arrow').removeClass('arrow_down').addClass('arrow_up')";
					$css_class = "arrow_up";
				}
				elseif($order == $field_name." DESC") {
					$jsover = "$(this).getNext('.arrow').removeClass('arrow_down').addClass('arrow_up')";
					$jsout = "$(this).getNext('.arrow').removeClass('arrow_up').addClass('arrow_down')";
					$css_class = "arrow_down";
				}
				else {
					$js = '';
					$jsover = "$(this).getNext('.arrow').addClass('arrow_up')";
					$jsout = "$(this).getNext('.arrow').removeClass('arrow_up')";
					$a_style = "visibility:hidden";
					$css_class = '';
				}

				$add_params['order'] = $ord;
				$link = $this->editUrl($add_params, array('start'));
				$head_t = "<a href=\"".$link."\" onmouseover=\"".$jsover."\" onmouseout=\"".$jsout."\" onclick=\"$(this).setProperty('onmouseout', '')\">".$label."</a>";
				$heads[] = $head_t." <div style=\"margin-right: 5px;top:3px;\" class=\"right arrow $css_class\"></div>";
			}
			elseif($view && !$ordered) {
				$heads[] = $label;
			}
		}
		$heads[] = array('text'=>'', 'class'=>'no_border no_bkg');
		
		return $heads;
	}
}
?>
