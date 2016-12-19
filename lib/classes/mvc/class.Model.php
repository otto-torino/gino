<?php
/**
 * @file class.Model.php
 * @brief Contiene la definizione ed implementazione della classe Gino.Model
 *
 * @copyright 2014-2016 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

/**
 * @brief Classe astratta che definisce un modello, cioè un oggetto che rappresenta una tabella su database
 *
 * @description La classe permette di descrivere la struttura dei dati del modello. 
 * Sono supportati molti tipi di dati (@see lib/classes/fields/*), compresi le relazioni molti a molti, comprensivi, nel caso, di campi aggiuntivi. 
 * La classe gestisce il salvataggio del modello su db e l'eliminazione, controllando, se specificato, che le constraint siano rispettate. 
 * Sono presenti metodi di generico utilizzo quali un selettore di oggetti, un selettore attraverso slug.
 *
 * Le proprietà su DB possono essere lette attraverso il metodo __get, ma possono anche essere protette costruendo una funzione get personalizzata all'interno della classe. \n
 * Le proprietà su DB possono essere impostate attraverso il metodo __set; in aggiunta possono essere definiti setter specifici definendo dei metodi @a setFieldname. \n
 *
 * La classe figlia che istanzia il parent passa il valore ID del record dell'oggetto direttamente nel costruttore:
 * @code
 * parent::__construct($id);
 * @endcode
 *
 * ##Criteri di costruzione di un modello/tabella per la definizione della struttura
 * Le tabelle che si riferiscono alle applicazioni possono essere gestite in modo automatico attraverso la classe @a Gino.AdminTable. \n
 * Ognuna di queste tabelle viene definita in un modello che estende la classe @a Gino.Model, e in particolare il modello definisce i propri campi nel metodo statico columns(). 
 * In questo metodo vengono definiti tutti i campi del modello utilizzando le opzioni generali dei campi (@see Gino.Field::__construct()) e quelle specifiche del tipo di campo.
 * 
 * Le tabelle devono essere coerenti con la definizione del modello per cui, ad esempio, i campi obbligatori devono essere 'not null' e gli eventuali valori di default 
 * devono essere indicati anche nel campo della tabella.
 *
 * @copyright 2014-2016 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
 abstract class Model {

    protected $_registry, $_request, $_db;
    
    /**
     * Nome della tabella del modello
     * @var string
     */
    protected $_tbl_data;
    
    /**
     * Label del modello
     * @var string
     */
    protected $_model_label;

    /**
     * Controller
     * @var object
     */
    protected $_controller;

    /**
     * Oggetto della localizzazione
     * @var object
     */
    protected $_locale;

    /**
     * Array contenente i valori di un record
     * @var array(field_name => field_value)
     */
    protected $_p = array();

    /**
     * Contiene le informazioni sui vincoli della tabella del modello
     * @var array
     */
    protected $_is_constraint = array();
    
    /**
     * Controllo dei vincoli della tabella del modello
     * @var boolean
     */
    protected $_check_is_constraint = true;

    protected $_lng_dft, $_lng_nav;
    
    /**
     * Struttura dei campi del modello
     * @var array
     */
    private $_structure;
    
    /**
     * Traduzioni
     * @var object
     */
    private $_trd;

    /**
     * Costruttore
     *
     * @param integer $id valore ID del record dell'oggetto
     * @return istanza di Gino.Model
     */
    function __construct($id = null) {

        $this->_registry = Registry::instance();
        $this->_request = \Gino\Http\Request::instance();
        
        $session = Session::instance();
        $this->_db = $this->_registry->db;
        
        $this->_lng_dft = $session->lngDft;
        $this->_lng_nav = $session->lng;
        
        $this->_structure = $this->getStructure();
        
        $this->fetchColumns($id);

        $this->_trd = new Translation($this->_lng_nav, $this->_lng_dft);
    }

    /**
     * @brief Rappresentazione a stringa dell'oggetto
     * @description Sovrascrivere questo metodo nella classe figlia per restituire un valore parlante
     * @return id
     */
    public function __toString() {

        return $this->id;
    }

    /**
     * @brief Etichetta del campo
     * @param string $field nome campo
     * @return etichetta
     */
	public function fieldLabel($field) {

		return isset($this->_fields_label[$field]) ? $this->_fields_label[$field] : $field;
	}

     /**
     * @brief Setter per la variabile di controllo del check constraint
     * @param bool $check
     * @return void
     */
    protected function setCheckIsConstraint($check) {
        $this->_check_is_constraint = (bool) $check;
    }

    /**
     * @brief Setter per la proprietà che contiene le informazioni per il check dei constraint
     * @description Esempio:
     *              @code
     *              $is_constraint = array(
     *                  'MyModelClass'=>'field_name',
     *                  'MyModelClassWithInstanceController'=>array('field' => 'field_name', 'controller' => new mycontroller())
     *              );
     *              @endcode
     * @param array $is_constraint
     * @return void
     */
    public function setIsConstraint($is_constraint) {
        $this->_is_constraint = $is_constraint;
    }

    /**
     * @brief Metodo richiamato ogni volta che qualcuno prova a ottenere una proprietà dell'oggetto non definita
     *
     * L'output è il metodo get specifico per questa proprietà (se esiste), altrimenti è la proprietà. \n
     * Per i campi su tabella principale la proprietà ritornata è uguale al valore salvato sul db. \n
     * Per i m2m la proprietà è uguale ad un array con gli id dei modelli correlati. \n
     * Per i m2mt la proprietà è uguale ad un array con gli id dei modelli correlati. \n
     * 
     * @param string $pName nome della proprietà
     * @return valore proprietà
     */
    public function __get($pName) {
    	
    	$null = null;
        
    	if(!array_key_exists($pName, $this->_p)) return $null;
        elseif(method_exists($this, 'get'.$pName)) return $this->{'get'.$pName}();
        elseif(array_key_exists($pName, $this->_p)) return $this->_p[$pName];
        else return $null;
    }

    /**
     * @brief Metodo richiamato ogni volta che qualcuno prova a impostare una proprietà dell'oggetto non definita ($this->{fieldname})
     * 
     * @param string $pName nome della proprietà
     * @param mixed $pValue valore da impostare
     * @return void
     */
    public function __set($pName, $pValue) {

    	$class = get_class($this);
    	
    	if(array_key_exists($pName, $class::$columns))
    	{
    		$obj = $class::$columns[$pName];
    		$this->_p[$pName] = $obj->valueToDb($pValue);
    	}
    	else throw new \Exception(sprintf(_("Il campo %s non è presente"), $pName));
    }

    /**
     * @brief Eliminazione di tutti i record legati all'istanza del controller passato come argomento
     * @param mixed $controller istanza del controller
     * @return TRUE
     */
    public static function deleteInstance($controller) {

        $db = db::instance();

        $rows = $db->select('id', static::$table, "instance='".$controller->getInstance()."'");
        if($rows and count($rows)) {
            foreach($rows as $row) {
                $obj = new static($row['id'], $controller);
                $obj->delete();
            }
        }
        return TRUE;
    }

    /**
     * @brief Ritorna l'oggetto ManyToMany Through Model
     * 
     * @param string $m2mt_field nome del campo m2mt
     * @param int $id id del record
     * @return oggetto
     */
    public function m2mtObject($m2mt_field, $id) {
        
		$field_obj = $this->_structure[$m2mt_field];
		
		$build = $this->build($field_obj);
		$class = $build->getM2m();
		
		return new $class($id, $build->getController());
    }

    /**
     * @brief Array associativo id => rappresentazione a stringa a partire da array di oggetti
     * @param array $objects
     * @return array associativo id=>stringa
     */
    public static function getSelectOptionsFromObjects($objects) {
        $res = array();
        foreach($objects as $obj) {
            $res[$obj->id] = (string) $obj;
        }
        return $res;
    }

    /**
     * @brief Recupera le proprietà con la traduzione
     * @param string $pName nome proprietà
     * @return string traduzione
     */
    public function ml($pName) {
        return ($this->_trd->selectTXT($this->_tbl_data, $pName, $this->_p['id']));
    }

    /**
     * @brief Struttura dei campi del modello
     * @description Un array associativo che contiene tutti i campi come chiavi e le relative classi di tipo @ref Field come valore
     * @return struttura dati
     */
    public function getStructure() {
        
    	$class = get_class($this);
    	
    	if(!is_array($class::$columns)) {
    		throw new \Exception(sprintf(_("Non sono stati definiti nel modello i campi della tabella %s"), $this->_tbl_data));
    	}
    	
    	return $class::$columns;
    }
    
    /**
     * Valori di un record
     * 
     * @param string $id valore id del record
     * @return multitype:array,null
     */
    public function getRecordValues() {
    	
    	if(!$this->id) return null;
    	
    	$res = $this->_db->select("*", $this->_tbl_data, "id='".$this->id."'");
    	if(count($res))
    		return $res[0];
    	else
    		return null;
    }

   /**
    * @brief Metodo generico statico per ricavare oggetti
    * @param mixed $controller istanza del controller
    * @param array $options array associativo di opzioni:
    *   - @b where: where clause
    *   - @b order: ordinamento
    *   - @b limit: limite risultati
    *   - @b debug: stampa la query
    * @return array di oggeti ricavati
    */
    public static function objects($controller = null, $options = array()) {

        $where = isset($options['where']) ? $options['where'] : null;
        $order = isset($options['order']) ? $options['order'] : null;
        $limit = isset($options['limit']) ? $options['limit'] : null;
        $debug = isset($options['debug']) ? $options['debug'] : false;

        $res = array();
        $db = Db::instance();
        $rows = $db->select('id', static::$table, $where, array('order'=>$order, 'limit'=>$limit, 'debug' => $debug));
        if($rows and count($rows)) {
            foreach($rows as $row) {
                $res[] = $controller ? new static($row['id'], $controller) : new static($row['id']);
            }
        }

        return $res;
    }

    /**
     * @brief Recupera l'oggetto a partire dallo slug
     * @param string $slug slug
     * @param mixed $controller istanza del controller.
     * @return oggetto che matcha lo slug dato
     */
    public static function getFromSlug($slug, $controller = null)
    {
        $db = Db::instance();
        $rows = $db->select('id', static::$table, "slug='".$slug."'");
        if($rows and count($rows)) {
            if($controller) {
                return new static($rows[0]['id'], $controller);
            }
            else {
                return new static($rows[0]['id']);
            }
        }
        return null;
    }

    /**
     * @brief Controller del modello
     * @return istanza del controller o null se non esiste istanza
     */
    public function getController() {
        return $this->_controller ? $this->_controller : null;
    }

    /**
     * @brief Salva il modello su db
     * @description Salva sia i campi della tabella sia i campi m2m. I campi m2mt devono essere salvati manualmente,
     *              la classe @ref AdminTable lo fa in maniera automatica.
     *              Quando il salvataggio avviene con successo viene emesso un segnale 'post_save' da parte del modello.
     * 
     * @param array $options
     *   array associativo di opzioni
     *   - @b only_update (mixed): nomi dei campi da aggiornare
     *     - string, nomi dei campi separati da virgola
     *     - array, elenco dei nomi dei campi
     *   - @b no_update (array): elenco dei campi da non impostare in una istruzione di update; di default vengono aggiunti i campi 'id', 'instance'
     * @return il risultato dell'operazione o errori
     */
    public function save($options=array()) {

    	$only_update = array_key_exists('only_update', $options) ? $options['only_update'] : null;
    	$no_update = array_key_exists('no_update', $options) && is_array($options['no_update']) ? $options['no_update'] : array();
    	
    	$event_dispatcher = EventDispatcher::instance();

		$class = get_class($this);
		$columns = $class::$columns;
		$m2m = array();
		
		if($this->_p['id']) {
            
			if($only_update && is_string($only_update)) {
				$only_update = explode(',', $only_update);
			}
			
			// Set_options
			$no_update_fields = array('id', 'instance');
			
			if(count($only_update)) {
				$no_update = array();
			}
			elseif(count($no_update)) {
				
				foreach($no_update_fields AS $value)
				{
					if(!in_array($value, $no_update)) {
						$no_update[] = $value;
					}
				}
			}
			else {
				$no_update = $no_update_fields;
			}
			// /Set_options
			
			$fields = array();
			
			foreach($this->_p as $pName=>$pValue)
			{
				if((count($only_update) && in_array($pName, $only_update)) or (count($no_update) && !in_array($pName, $no_update)))
				{
					if(!array_key_exists($pName, $columns)) {
						throw new \Exception(sprintf(_("The field name \"%s\" does not exist"), $pName));
					}
					
					$field_obj = $columns[$pName];
					
					if(!$this->checkM2m($field_obj))
					{
						if(is_object($pValue))
						{
							$fields[$pName] = $pValue->id;
						}
						else
						{
							$fields[$pName] = $field_obj->valueToDb($pValue);
						}
					}
					else $m2m[$pName] = $pValue;
				}
			}
			
			$result = $this->_db->update($fields, $this->_tbl_data, "id='{$this->_p['id']}'", DEBUG_ACTION_QUERY);
			if(DEBUG_ACTION_QUERY) {
				exit();
			}
		}
		else
		{
        	$fields = array();
        	
        	foreach($this->_p as $pName=>$pValue)
			{
				if(!array_key_exists($pName, $columns)) {
					throw new \Exception(_("The field name does not exist"));
				}
				$field_obj = $columns[$pName];
				
				if(!$this->checkM2m($field_obj))
				{
					if(is_object($pValue))
					{
						$fields[$pName] = $pValue->id;
					}
					else
					{
						// viene impostato il valore di default se il campo è obbligatorio e il valore è nullo
						$default = $field_obj->getDefault();
						$required = $field_obj->getRequired();
						$value_to_db = $field_obj->valueToDb($pValue);
						
						if($required === true && is_null($value_to_db)) {
							$fields[$pName] = $default;
						}
						else {
							$fields[$pName] = $value_to_db;
						}
					}
				}
				else $m2m[$pName] = $pValue;
			}
            
			$result = $this->_db->insert($fields, $this->_tbl_data, DEBUG_ACTION_QUERY);
			if(DEBUG_ACTION_QUERY) {
				exit();
			}
		}
		
        if(!$result) {
            throw new \Exception(_("Salvataggio non riuscito"));
        }

        if(!$this->_p['id']) $this->_p['id'] = $this->_db->getlastid($this->_tbl_data);

        if(count($m2m)) {
        	$result = $this->savem2m($m2m);
        }

        $event_dispatcher->emit($this, 'post_save', array('model' => $this));

        return $result;
    }

    /**
     * @brief Salvataggio dei campi ManyToMany
     * 
     * @param array $m2m campi m2m del modello (field_name => (array) join_table_id_values)
     * @return true
     */
    public function savem2m($m2m) {
        
    	$class = get_class($this);
    	$columns = $class::$columns;
    	
    	foreach($m2m as $pName=>$pValue) {
    		
    		$field_obj = $columns[$pName];
    		
    		if(is_a($field_obj, '\Gino\ManyToManyField')) {
    			
    			$build = $this->build($field_obj);
    			$this->_db->delete($build->getJoinTable(), $build->getJoinTableId()."='".$this->id."'");
    			
    			if(is_array($pValue))
    			{
    				foreach($pValue as $fid) {
    					$this->_db->insert(array(
    							$build->getJoinTableId() => $this->id,
    							$build->getJoinTableM2mId() => $fid
    						), $build->getJoinTable()
    					);
    				}
    			}
    		}
    	}
    	
        return true;
    }

    /**
     * @brief Elimina le proprietà su db del modello e le traduzioni
     * @return risultato dell'operazione, bool
     */
    public function deleteDbData() {
        
        $this->_registry->trd->deleteTranslations($this->_tbl_data, $this->_p['id']);
        $result = $this->_db->delete($this->_tbl_data, "id='{$this->_p['id']}'");
        return $result;
    }

    /**
     * @brief Elimina l'oggetto
     * @description Elimina i dati su db, le traduzioni, e le associazioni m2m e m2mt
     *              Controlla che non ci siano regole di constraint che impediscano l'eliminazione, in caso
     *              ce ne fossero di non rispettate ritorna un elenco di regole che impediscono l'eliminazione.
     * @return bool (true) or array (error)
     */
    public function delete() {

        // check constraints
		if($this->_check_is_constraint and count($this->_is_constraint)) {
			$res = $this->checkIsConstraint();
			if($res !== true) {
				return array("error" => $this->isConstraintError($res));
			}
		}

        $this->deletem2m();
        $this->deletem2mthrough();

        $this->deletem2m();
        $this->deletem2mthrough();

        foreach($this->_structure as $field) {
            
        	$build = $this->build($field);
        	
        	if(method_exists($build, 'delete')) {
        		$result = $build->delete();
        		if($result !== TRUE) {
        			$class_type = preg_replace("#Build$#", '', get_name_class($build));
        			return array('error' => sprintf(_("Si è verificato un errore nel processo di eliminazione di elementi correlati al campo '%s' (%s)."), $build->getName(), $class_type));
        		}
        	}
        	
        	if(method_exists($build, 'delete')) {
                $result = $build->delete();
                if($result !== TRUE) {
                    return array('error' => _("Si è verificato un errore nel processo di eliminazione di elementi correlati a una tipologia di campo."));
                }
            }
        }
        
        $result = $this->deleteDbData();
        if($result !== TRUE) {
        	return array("error" => 37);
        }

        return TRUE;
    }

    /**
     * @brief Errore conseguente ad una violazione delle constraint in eliminazione
     * @param array $res array delle regole contraint violate
     * @return html errore
     */
    protected function isConstraintError($res) {
        $html = "<p>"._("Il record che si intende eliminare compare come riferimento nei seguenti oggetti").":</p>";
        $html .= "<ul>";
        foreach($res as $model => $records) {
            $html .= "<li>".$model."</li>";
            $html .= "<ul>";
            foreach($records as $record) {
                $html .= "<li>".$record."</li>";
            }
            $html .= "</ul>";
        }
        $html .= "</ul>";

        return $html;
    }

    /**
     * @brief Controllo delle regole constraint
     * @return true oppure lista delle regole violate
     */
    protected function checkIsConstraint() {

        $res = array();
        $db = db::instance();

        foreach($this->_is_constraint as $model=>$prop) {

            // model of instantiable module
            if(is_array($prop)) {
                $field = $prop['field'];
                $controller = $prop['controller'];
            }
            else {
                $field = $prop;
                $controller = null;
            }

            // does an object exists?
            $an_objs = $model::objects($controller, array('limit' => array(0,1)));
            $an_obj = count($an_objs) ? $an_objs[0] : null;
            $model_label = $an_obj ? $an_obj->getModelLabel() : '';
            if($an_obj and $an_obj->id) {
                
                // m2m
                if(isset($an_obj->_structure[$field]) and is_a($an_obj->_structure[$field], 'ManyToManyField')) {
                    
                	$field_obj = $an_obj->_structure[$field];
                    $build = $this->buid($field_obj);
                	
                    $table = $build->getJoinTable();
                    $id_string = $build->getJoinTableId();
                    $m2m_id_string = $build->getJoinTableM2mId();
                    
                    $rows = $db->select($id_string, $table, $m2m_id_string."='".$this->id."'");
                    if($rows and count($rows)) {
                        if(!isset($res[$model_label])) {
                            $res[$model_label] = array();
                        }
                        foreach($rows as $row) {
                            $obj = $controller ? new $model($row[$id_string], $controller) : new $model($row[$id_string]);
                            $res[$model_label][] = (string) $obj . ' - '._('m2m:').' '.$obj->fieldLabel($field);
                        }
                    }
                }
            }
            $objs = $model::objects($controller, array('where' => $field."='".$this->id."'"));
            if($objs and count($objs)) {
                if(!isset($res[$model_label])) {
                    $res[$model_label] = array();
                }
                foreach($objs as $obj) {
                    $res[$model_label][] = (string) $obj.' - '._('campo:').' '.$obj->fieldLabel($field);
                }
            }
        }

        return count($res) ? $res : TRUE;
    }

    /**
     * @brief Elimina le associazioni m2m
     * @return risultato dell'operazione, bool
     */
	public function deletem2m() {
        
		$result = true;
		
		foreach($this->_structure as $field => $obj) {
        	
        	if(is_a($obj, '\Gino\ManyToManyField')) {
            	
        		$build = $this->build($obj);
                $result = $result and $this->_db->delete($build->getJoinTable(), $build->getJoinTableId()."='".$this->id."'");
        	}
        }
        return $result;
    }

    /**
     * @brief Elimina le associazioni m2mt
     * @return risultato dell'operazione, bool
     */
    public function deletem2mthrough() {
        
    	$result = true;
        foreach($this->_structure as $field => $obj) {
        	
        	if(is_a($obj, '\Gino\ManyToManyThroughField')) {
        		$result = $result and $this->deletem2mthroughField($field);
            }
        }
        return $result;
    }

    /**
     * @brief Elimina le associazioni di un campo m2mt
     * 
     * @param string $field_name nome campo
     * @return risultato dell'operazione, bool
     */
    public function deletem2mthroughField($field_name) {
        
    	$field_obj = $this->_structure[$field_name];
        
    	if(!is_a($field_obj, '\Gino\ManyToManyThroughField')) {
    		throw new \Exception(_("Il tipo di campo non è corretto"));
    	}
    	
    	$build = $this->build($field_obj);
        $class = $build->getM2m();
        
        $ids = $build->getValue();
        if(count($ids))
        {
        	foreach($ids AS $id)
        	{
        		$m2m_obj = new $class($id, $this->getController());
        		$res = $m2m_obj->delete();
        		if(is_array($res)) {
        			return $res;
        		}
        	}
        }
        return true;
    }

    /**
     * @brief Etichetta del modello
     * @return label
     */
    public function getModelLabel() {
        return $this->_model_label;
    }

    /**
     * @brief Tabella principale dei dati
     * @return nome tabella
     */
    public function getTable() {
        return $this->_tbl_data;
    }
    
    /**
     * Verifica se il tipo di campo di un modello è un oggetto ManyToMany
     * 
     * @param object $field oggetto del tipo di campo
     * @return boolean
     */
    private function checkM2m($field_obj) {
    	
    	if(is_a($field_obj, '\Gino\ManyToManyField') 
    	or is_a($field_obj, '\Gino\ManyToManyThroughField')) {
    		return true;
    	}
    	else {
    		return false;
    	}
    }
    
    /**
     * Verifica se il tipo di campo di un modello è un oggetto file
     *
     * @param object $field oggetto del tipo di campo
     * @return boolean
     */
    public function checkFile($field_obj) {
    	 
    	if(is_a($field_obj, '\Gino\FileField')
    	or is_a($field_obj, '\Gino\ImageField')) {
    		return true;
    	}
    	else {
    		return false;
    	}
    }
    
    /**
     * Recupera l'oggetto del tipo di campo di un modello
     * 
     * @param string $field_name nome del campo
     * @return object or null
     */
    private function getFieldObject($field_name) {
    	
		$class = get_class($this);
		
		if(array_key_exists($field_name, $class::$columns)) {
			return $class::$columns[$field_name];
		}
		else {
			return null;
		}
    }
	
	/**
	 * Verifica se il modello di un campo ManyToManyThroughField ha dei campi di tipo File
	 * 
	 * @param string $name nome del campo ManyToManyThroughField
	 * @param integer $id valore id del modello che contiene il campo ManyToManyThroughField
	 * @return boolean
	 */
	public function checkM2mtFileField($name, $id) {
	
		$m2mt_model = $this->m2mtObject($name, $id);
		$columns = $m2mt_model::$columns;
	
		foreach ($columns AS $field_name => $field_obj) {
			
			if($this->checkFile($field_obj)) {
				return true;
			}
		}
		return false;
	}
    
    /**
     * Struttura dei campi della tabella di un modello
     *
     * @return array
     *
     * @description Il formato degli elementi dell'array è il seguente:
     * @code
     * field_name = new \Gino\{Type}Field(array(
     *   'name' => string,
     *   'label' => string,
     *   'primary_key' => bool,
     *   'unique_key' => bool,
     *   'auto_increment' => bool,
     *   'default' => mixed,
     *   'max_lenght' => integer,
     *   'required' => boolean,
     *   'int_digits' => integer,
     *   'decimal_digits' => integer,
     * ));
     * @endcode
     */
    public static function columns() {
    
    	return array();
    }
    
    /**
     * @brief Racchiude tutte le proprietà di un modello
     * @description Recupera le proprietà del campo dipendenti dai valori del record e imposta le opzioni: model, field_object, value, table. 
     * 
     * @param object $field_obj oggetto della classe del tipo di campo
     * @return array
     */
    public function getProperties($field_obj) {
    	
    	$field_name = $field_obj->getName();
    	$controller = $this->getController();
    	
    	$prop = $this->properties($this, $controller);
    	
    	$prop_base = array(
    		'model' => $this,
    		'field_object' => $field_obj, 
    		'value' => $field_obj->valueFromDb($this->$field_name),
    		'table' => $this->getTable()
    	);
    	
    	if(array_key_exists($field_name, $prop)) {
    		$prop_model = $prop[$field_name];
    		
    		if(!is_array($prop_model)) {
    			throw new \Exception(_("Le proprietà specifiche di un campo del modello devono essere definite in un array"));
    		}
    		
    		return array_merge($prop_base, $prop_model);
    	}
    	else return $prop_base;
    }
    
    /**
     * Proprietà specifiche di un modello dipendenti dai valori del record (ad esempio dal valore id)
     * 
     * @param object $model
     * @param object $controller
     * @return array
     */
    protected static function properties($model, $controller) {
    	
    	return array();
    }
    
    /**
     * Classe Build del campo di tabella
     * 
     * @description Le eventuali proprietà del modello dipendenti dai valori del record sovrascrivono le proprietà del campo
     * @param object $field_obj oggetto della classe del tipo di campo
     * @return object
     */
    public function build($field_obj) {
    	
    	if(!is_object($field_obj))
    		throw new \Exception(_("Gino.Model::build() expects an object"));
    	
    	// model properties
    	$prop_model = $this->getProperties($field_obj);
    	
    	// field properties
    	$prop_column = $field_obj->getProperties();
    	
    	$field_obj_class = get_class($field_obj);
    	$class = preg_replace("#^(.+)(Field)$#", '$1Build', $field_obj_class);
    	
    	return new $class(array_merge($prop_column, $prop_model));
    }
    
    /**
     * Valore da mostrare in output
     * 
     * @see Gino.Build::printValue()
     * @param object $field_obj oggetto della classe del tipo di campo
     * @return mixed
     */
    public function shows($field_obj) {
    	
    	$obj = $this->build($field_obj);
    	$value = $obj->printValue();
    	
    	return $value;
    }
    
    /**
     * @brief Recupera i valori del record e li carica nella proprietà _p
     * @description Il valore dei campi di tipo ManyToMany è un array che racchiude i valori id dei record della tabella di join associata al modello
     * 
     * @param integer $id valore id del record
     * @throws \Exception
     */
	public function fetchColumns($id) {
		
		$row = $this->_db->select('*', $this->_tbl_data, "id='$id'", array('debug'=>false));
		
		$class = get_class($this);
		
		if(!is_array($class::$columns)) {
			throw new \Exception(sprintf(_("Non sono stati definiti nel modello i campi della tabella %s"), $this->_tbl_data));
		}
		
		foreach($class::$columns as $field_name=>$field_obj) {
			
			if($row && count($row)) {
				
				if($this->checkM2m($field_obj)) {
					$build = $this->build($field_obj);
					$this->_p[$field_name] = $build->getValue();
				}
				else {
					$this->_p[$field_name] = $field_obj->valueFromDb($row[0][$field_name]);
				}
			}
			else {
				$this->_p[$field_name] = null;
			}
		}
	}
	
	/**
     * @brief Refresh del modello (da chiamare manualmente)
     * 
     * @description Quando ad esempio si modificano gli m2mt e si vogliono vederne gli effetti prima del ricaricamento pagina. \n
     * Modificando gli m2mt, questi vengono aggiornati sul db, ma il modello che ha tali m2mt continua a referenziare i vecchi, questo perché il salvataggio
     * viene gestito da Gino.ModelForm e non da modello stesso. 
     * Per fare in modo che le modifiche agli m2mt si riflettano immediatamente sul modello di appartenenza questo metodo viene richiamato da Gino.ModelForm. 
     * Allo stesso modo richiamarlo manualmente se la modifica agli m2mt viene fatta in modo diverso dall'uso di Gino.ModelForm::save().
     *
     * @see Gino.ModelForm::m2mthroughAction()
     * @return void
     */
    public function refreshModel() {
    	
    	$this->fetchColumns($this->id);
    }
}
