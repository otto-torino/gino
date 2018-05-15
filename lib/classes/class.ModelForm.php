<?php
/**
 * @file class.ModelForm.php
 * @brief Contiene la definizione ed implementazione della classe Gino.ModelForm
 *
 * @copyright 2015-2016 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

\Gino\Loader::import('class/exceptions', array('\Gino\Exception\Exception403'));
\Gino\Loader::import('class', array('\Gino\Form'));

/**
 * @brief Classe per la creazione ed il salvataggio dati di un form
 *
 * Fornisce gli strumenti per generare gli elementi del form e per gestire l'upload di file
 *
 * @copyright 2015-2016 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class ModelForm extends Form {
	
	/**
	 * @brief Modello
	 * @var object
	 */
	protected $_model;
	
	/**
	 * @brief Elenco dei campi (completi di struttura) da mostrare come input
	 * @var array
	 */
	protected $_fields;
	
	/**
     * @brief Costruttore
     * 
     * @param object $model
     * @param array $options
     *   array associativo di opzioni
     *   - opzioni del costruttore della classe Gino.Form
     *   - @b fields (array): elenco dei campi da mostrare come input
     */
    function __construct($model, $options=array()){

    	parent::__construct($options);
    	
    	$fields = gOpt('fields', $options, array());
    	
    	$this->_model = $model;
    	$this->_fields = $this->get($fields);
    }
    
    public function getModel() {
    	return $this->_model;
    }
    
    /**
     * Interfaccia per il metodo di renderizzazione del form
     * 
     * @see Gino.Form::render()
     * @param array $options_form opzioni del form e del layout
     * @param array $options_field opzioni dei campi
     * @return form html
     */
    public function view($options_form=array(), $options_field=array()) {
    	
    	return $this->render($this->_model, array('fields'=>$this->_fields, 'options_form'=>$options_form, 'options_field'=>$options_field));
    }
    
    /**
     * @brief Recupera la struttura dei campi
     * @description Nel caso in cui non vengano indicati dei campi vengono presi tutti i campi del modello
     * 
     * @param array $fields elenco dei campi da mostrare come input
     * @return array(string field => object build)
     */
    public function get($fields=array()) {
    	
    	$structure = array();
    	
    	$selection = is_array($fields) && count($fields) ? true : false;
    	
    	$columns = $this->_model->getStructure();
    	
    	foreach($columns as $field=>$object) {
    	
    		if(($selection && in_array($field, $fields)) or !$selection) {
    			
    			if($field == 'instance')
    			{
    				$object->setWidget(null);
    				$object->setRequired(false);
    			}
    			
    			$build = $this->_model->build($object);
    			
    			$structure[$field] = $build;
    		}
    	}
    	
    	return $structure;
    }
    
    /**
     * @brief Valore della traduzione di un campo
     * 
     * @param string $table nome della tabella con il campo da tradurre
     * @param mixed $field nome o nomi dei campi da recuperare
     *   - string: nome del campo con il testo da tradurre
     *   - array: nomi dei campi da concatenare
     * @param mixed $ref_value valore del campo di riferimento
     * @param string $ref_id nome del campo di riferimento (generalmente id)
     * @return integer|string
     */
    public static function translationValue($table, $field, $ref_value, $ref_id) {
    	
    	$session = Session::instance();
    	$translation = new translation($session->lng, $session->lngDft);
    	
    	$trnsl_value = $translation->selectTXT($table, $field, $ref_value, $ref_id);
    	return $trnsl_value;
    }
    
    /**
     * @brief Salvataggio dei dati a seguito del submit di un form di inserimento/modifica
     * @description Per attivare l'importazione dei file utilizzare l'opzione @a import_file e sovrascrivere il metodo readFile().
     * 
     * @see Gino.Field::retrieveValue()
     * @see Gino.Build::clean()
     * @see Gino.Model::save()
     * @param array $options array associativo di opzioni
     *   - opzioni per il recupero dei dati dal form
     *   - opzioni per selezionare gli elementi da recuperare dal form
     *     - @b removeFields (array): elenco dei campi non presenti nel form
     *     - @b viewFields (array): elenco dei campi presenti nel form
     *   - opzioni per l'importazione di un file
     *     - @b import_file (array): attivare l'importazione di un file
     *     - @a field_import (string): nome del campo del file di importazione
     *     - @a field_verify (array): valori da verificare nel processo di importazione, nel formato array(nome_campo=>valore[, ])
     *     - @a field_log (string): nome del campo del file di log
     *     - @a dump (boolean): per eseguire il dump della tabella prima di importare il file
     *     - @a dump_path (string): percorso del file di dump
     * @param array $options_field opzioni per formattare i valori dei campi da salvare nel database
     * @return bool, risultato operazione
     */
    public function save($options=array(), $options_field=array()) {
    
    	// Opzioni per selezionare gli elementi da recuperare dal form
    	$removeFields = array_key_exists('removeFields', $options) ? $options['removeFields'] : null;
    	$viewFields = array_key_exists('viewFields', $options) ? $options['viewFields'] : null;
    	
    	// Opzioni per l'ìmportazione di un file
    	$import = false;
    	if(isset($options['import_file']) && is_array($options['import_file']))
    	{
    		$field_import = array_key_exists('field_import', $options['import_file']) ? $options['import_file']['field_import'] : null;
    		$field_verify = array_key_exists('field_verify', $options['import_file']) ? $options['import_file']['field_verify'] : array();
    		$field_log = array_key_exists('field_log', $options['import_file']) ? $options['import_file']['field_log'] : null;
    		$dump = array_key_exists('dump', $options['import_file']) ? $options['import_file']['dump'] : false;
    		$dump_path = array_key_exists('dump_path', $options['import_file']) ? $options['import_file']['dump_path'] : null;
    
    		if($field_import) $import = TRUE;
    	}
    	
    	$this->saveSession();
    	$req_error = $this->checkRequired();
    
    	if($req_error > 0) {
    		return array('error'=>1);
    	}
    	
    	$controller = $this->_model->getController();
    	$m2mt = array();
    	$builds = array();
    
    	foreach($this->_model->getStructure() as $field=>$object) {
    
    		if($this->permission($options, $field) && (
    			($removeFields && !in_array($field, $removeFields)) ||
    			($viewFields && in_array($field, $viewFields)) ||
    			(!$viewFields && !$removeFields)
    		))
    		{
    			if(isset($options_field[$field])) {
    				$opt_element = $options_field[$field];
    			}
    			else {
    				$opt_element = array();
    			}
    			
    			if($field == 'instance' && is_null($this->_model->instance))
    			{
    				$this->_model->instance = $controller->getInstance();
    			}
    			elseif(is_a($object, '\Gino\ManyToManyThroughField'))
    			{
    				$m2mt[] = array(
    					'field' => $field,
    					'object' => $object,
    				);
    			}
    			else
    			{
    				$retrieve_value = $object->retrieveValue($field);
    				
    				$build = $this->_model->build($object);
    				$opt_element['model_id'] = $this->_model->id;
    				
    				try {
    					$value = $build->clean($retrieve_value, $opt_element);
    				} catch (\Gino\Exception\ValidationError $e) {
    					return array('error' => $e->getMessage());
    				}
    				
    				// imposta il valore; @see Gino.Model::__set()
    				$this->_model->{$field} = $value;
    
    				if($import)
    				{
    					if($field == $field_import)
    						$path_to_file = $object->getPath();
    				}
    			}
    		}
    	}
    
    	if($import)
    	{
    		$result = $this->readFile($this->_model, $path_to_file, array('field_verify'=>$field_verify, 'dump'=>$dump, 'dump_path'=>$dump_path));
    		if($field_log) {
    			$this->_model->{$field_log} = $result;
    		}
    	}
    
    	$result = $this->_model->save();
    	
    	// error
    	if(is_array($result)) {
    		return $result;
    	}
    
    	foreach($m2mt as $data) {
    		$result = $this->m2mThroughAction($data['field'], $data['object'], $this->_model, $options);
    		 
    		// error
    		if(is_array($result)) {
    			return $result;
    		}
    	}
    	
    	return $result;
    }
    
    /**
     * @brief Salvataggio dei campi Gino.ManyToManyThroughField
     *
     * @description Il salvataggio di questi tipi di campi avviene in automatico utilizzando
     *              la class Gino.AdminTable. Non è gestito dalla classe Gino.Model.
     *
     * @param string $m2m_name nome del campo ManytoManyThroughField
     * @param \Gino\ManyToManyThroughField $m2m_object istanza della classe di tipo Gino.Field che rappresenta il campo
     * @param \Gino\Model $model istanza del model cui appartiene il campo
     * @param $options array associativo di opzioni (@see self::save)
     * @return bool, risultato operazione
     */
    private function m2mThroughAction($m2m_name, $m2m_object, $model, $options=array()) {
    
    	$removeFields = $m2m_object->getRemoveFields();
    	
    	$controller = $model->getController();
    	$build = $model->build($m2m_object);
    	$m2m_class = $build->getM2m();
    
    	$indexes = cleanVar($this->_request->POST, 'm2mt_'.$m2m_name.'_ids', 'array', '');
    
    	if(!is_array($indexes)) $indexes = array();
    
    	$check_ids = array();
    	$m2m_m2m = array();
    	$object_names = array();
    
    	foreach($indexes as $index) {
    
    		$id = cleanVar($this->_request->POST, 'm2mt_'.$m2m_name.'_id_'.$index, 'int', '');
    
    		// oggetto pronto per edit or insert
    		$m2m_model = new $m2m_class($id, $build->getController());
    
    		foreach($m2m_model->getStructure() as $field=>$object) {
    
    			if(!isset($object_names[$field])) {
    				$object_names[$field] = $object->getName();
    			}
    			
    			if($this->permission($options, $field) && (($removeFields && !in_array($field, $removeFields)) || (!$removeFields)))
    			{
    				$opt_element = array('check_del_file_name' => 'm2mt_'.$m2m_name.'_check_del_'.$object_names[$field].'_'.$index);
    
    				if(isset($options_element[$field])) {
    					$opt_element = array_merge($opt_element, $options_element[$field]);
    				}
    
    				if($field == 'instance' && is_null($m2m_model->instance))
    				{
    					$m2m_model->instance = $controller->getInstance();
    				}
    				elseif(is_a($object, '\Gino\ManyToManyThroughField'))
    				{
    					$this->m2mThroughAction($field, $object, $m2m_model);
    				}
    				else
    				{
    					$m2m_build = $m2m_model->build($object);
    					$m2m_build->setName('m2mt_'.$m2m_name.'_'.$object_names[$field].'_'.$index);
    					
    					$m2m_retrieve_value = $object->retrieveValue($m2m_build->getName());
    					$value = $m2m_build->clean($m2m_retrieve_value, $opt_element);
    					$m2m_model->{$field} = $value;
    					
    					if(isset($import) and $import)
    					{
    						if($field == $field_import) {
    							$path_to_file = $object->getPath();
    						}
    					}
    				}
    			}
    		}
    		
    		$m2m_model->{$build->getModelTableId()} = $model->id;
    		$m2m_model->save();
    		$check_ids[] = $m2m_model->id;
    	}
    
    	// eliminazione di tutti gli m2mt che non ci sono più
    	$db = Db::instance();
    	$where = count($check_ids) ? $build->getModelTableId()."='".$model->id."' AND id NOT IN (".implode(',', $check_ids).")" : $build->getModelTableId()."='".$model->id."'";
    	$objs = $m2m_class::objects($build->getController(), array('where' => $where));
    
    	if($objs and count($objs)) {
    		foreach($objs as $obj) {
    			$obj->delete();
    		}
    	}
    
    	// aggiornamento del modello di modo che le modifiche agli m2mt si riflettano immediatamente sul modello di appartenenza
    	$model->refreshModel();
    
    	return TRUE;
    }
    
    /**
     * @brief Legge il file e ne importa il contenuto
     *
     * @todo Implementare la funzionalità di importazione del file
     * @param \Gino\Model $model oggetto del modello
     * @param string $path_to_file
     * @param array $options
     *   array associativo di opzioni
     *   - @b verify_items (array): valori da verificare nel processo di importazione, nel formato array(nome_campo=>valore[, ])
     *   - @b dump (boolean): effettua il dump della tabella prima dell'importazione
     *   - @b dump_path (string): percorso del file di dump
     * @return string (log dell'importazione)
     */
    protected function readFile($model, $path_to_file, $options=array()) {
    
    	return null;
    }
    
    /**
     * @brief Restore di un file
     * @todo Implementare la funzionalità di restore di un file
     * @see Gino.Db::restore()
     * @param string $table nome della tabella
     * @param string $filename nome del file da importare
     * @param array $options arra
     * @return boolean
     */
    protected function restore($table, $filename, $options=array()) {
    
    	$db = Db::instance();
    	return $db->restore($table, $filename, $options);
    }
    
    /**
     * @brief Dump di una tabella
     *
     * @see Gino.Db::dump()
     * @param string $table
     * @param string $filename nome del file completo di percorso
     * @param array $options array associativo di opzioni
     * @return string (nome del file di dump)
     */
    protected function dump($table, $filename, $options=array()) {
    
    	$db = Db::instance();
    	return $db->dump($table, $filename, $options);
    }
}
