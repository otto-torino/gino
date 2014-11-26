<?php
/**
 * @file class.Model.php
 * @brief Contiene la definizione ed implementazione della classe Gino.Model
 *
 * @copyright 2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

/**
 * @brief Classe astratta che definisce un modello, cioè un oggetto che rappresenta una tabella su database
 *
 * @description La classe permette di descrivere la struttura dei dati del modello. Sono supportati molti tipi di dati, compresi le relazioni molti a molti, comprensive se il caso di campi aggiuntivi.
 *              La classe gestisce il salvataggio del modello su db e l'eliminazione, controllando, se specificato, che le constraint siano rispettate.
 *              Sono presenti metodi di generico utilizzo quali un selettore di oggetti, un selettore attraverso slug.
 *
 *              Le proprietà su DB possono essere lette attraverso la funzione __get, ma possono anche essere protette costruendo una funzione get personalizzata all'interno della classe. \n
 *              Le proprietà su DB possono essere impostate attraverso il metodo __set, possono essere definiti setter specifici definendo dei metodi setFieldname \n
 *
 *              La classe figlia che istanzia il parent passa il valore ID del record dell'oggetto direttamente nel costruttore:
 *              @code
 *              parent::__construct($id);
 *              @endcode
 *
 *              ##Criteri di costruzione di una tabella per la definizione della struttura
 *              Le tabelle che si riferiscono alle applicazioni possono essere gestite in modo automatico attraverso la classe @a adminTable. \n
 *              I modelli delle tabelle estendono la classe @a Model che ne ricava la struttura. Ne deriva che le tabelle devono essere costruite seguendo specifici criteri:
 *                - i campi obbligatori devono essere 'not null'
 *                - un campo auto-increment viene gestito automaticamente come input di tipo hidden
 *                - definire gli eventuali valori di default (soprattutto nei campi enumerazione)
 *
 *              ##Ulteriori elementi che contribuiscono alla definizione della struttura
 *              Le label dei campi devono essere definite nel modello nella proprietà @a $_fields_label. Una label non definita prende il nome del campo. \n
 *              Esempio:
 *              @code
 *              $this->_fields_label = array(
 *                'ctg'=>_("Categoria"),
 *                'name'=>_("Titolo"),
 *                'private'=>array(_("Tipologia"), _("privato: visibile solo dal relativo gruppo"))
 *              );
 *              @endcode
 *
 * @copyright 2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
 abstract class Model {

    protected $_registry,
               $_request,
               $_db;
    protected $_tbl_data;
    protected $_model_label;

    /**
     * Struttura della tabella
     * @var array
     */
    protected $_structure;

    protected $_controller;

    /**
     * Oggetto della localizzazione
     * @var object
     */
    protected $_locale;

    //protected $_main_class;

    /**
     * Intestazioni dei campi del database nel form
     * @var array
     */
    protected $_fields_label=array();
    protected $_p, $_chgP = array();
    protected $_m2m = array(), $_m2mt = array();

    protected $_is_constraint = array();
    protected $_check_is_constraint = true;

    protected $_lng_dft, $_lng_nav;
    private $_trd;

    /**
     * Costruttore
     *
     * @param integer $id valore ID del record dell'oggetto
     * @return istanza di Gino.Model
     */
    function __construct($id = null) {

        $this->_registry = registry::instance();
        $session = Session::instance();
        $this->_db = $this->_registry->db;
        $this->_lng_dft = $session->lngDft;
        $this->_lng_nav = $session->lng;
        $this->_structure = $this->structure($id);
        $this->_p['instance'] = null;

        $this->_trd = new translation($this->_lng_nav, $this->_lng_dft);
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
        $this->_is_consraint = $is_constraint;
    }

    /**
     * @brief Metodo richiamato ogni volta che qualcuno prova a ottenere una proprietà dell'oggetto non definita
     *
     * L'output è il metodo get specifico per questa proprietà (se esiste), altrimenti è la proprietà
     * Per i campi su tabella principale la proprietà ritornata è uguale al valore slavato sul db.
     * Per i m2m la proprietà è uguale ad un array con gli id dei modelli correlati.
     * Per i m2mt la proprietà è uguale ad un array con gli id dei modelli correlati.
     * @param string $pName
     * @return valore proprietà
     */
     public function &__get($pName) {
        $null = null;
        if(!array_key_exists($pName, $this->_p) and !array_key_exists($pName, $this->_m2m) and !array_key_exists($pName, $this->_m2mt)) return $null;
        elseif(method_exists($this, 'get'.$pName)) return $this->{'get'.$pName}();
        elseif(array_key_exists($pName, $this->_p)) return $this->_p[$pName];
        elseif(array_key_exists($pName, $this->_m2m)) return $this->_m2m[$pName];
        elseif(array_key_exists($pName, $this->_m2mt)) return $this->_m2mt[$pName];
        else return $null;
    }

    /**
     * @brief Metodo richiamato ogni volta che qualcuno prova a impostare una proprietà dell'oggetto non definita
     *
     * L'output è il metodo set specifico per questa proprietà (se esiste), altrimenti la proprietà è impostata leggendo l'array POST e il tipo stringa
     *
     * @param string $pName nome della proprietà
     * @param mixed $pValue valore da settare
     * @return void
     */
    public function __set($pName, $pValue) {

        if(!array_key_exists($pName, $this->_p) and !array_key_exists($pName, $this->_m2m)) return null;
        elseif(method_exists($this, 'set'.$pName)) return $this->{'set'.$pName}($pValue);
        elseif(array_key_exists($pName, $this->_p)) {
            if($this->_p[$pName] !== $pValue && !in_array($pName, $this->_chgP)) $this->_chgP[] = $pName;
            $this->_p[$pName] = $pValue;
        }
        elseif(array_key_exists($pName, $this->_m2m)) {
            $this->_m2m[$pName] = $pValue;
        }
        elseif(array_key_exists($pName, $this->_structure) and get_class($this->_structure[$pName]) == 'ManyToManyThroughField') {
            $this->_m2mt[$pName] = $pValue;
        }
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
     * @brief Aggiunge un m2m al modello
     * @param string $field nome campo m2m
     * @param array $value lista di id correlati
     * @return void
     */
    public function addm2m($field, $value) {
        $this->_m2m[$field] = $value;
    }

    /**
     * @brief Aggiunge un m2m through al modello
     * @param string $field nome campo m2m
     * @param array $value lista di id correlati
     * @return void
     */
    public function addm2mthrough($field, $value) {
        $this->_m2mt[$field] = $value;
    }

    /**
     * @brief Ritorna l'oggetto m2m through model
     * @param string $m2mt_field nome del campo m2mt
     * @param int $id id del record
     * @return oggetto
     */
    public function m2mtObject($m2mt_field, $id) {
        $field_obj = $this->_structure[$m2mt_field];
        $class = $field_obj->getM2m();
        return new $class($id, $field_obj->getController());
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
     * @brief Struttura dati
     * @description Un array associativo che contiene tutti i campi come chiavi e le relative classi di tipo @ref Field come valore
     * @return struttura dati
     */
    public function getStructure() {
        return $this->_structure;
    }

   /**
    * @brief Metodo generico statico per ricavare oggetti
    * @param mixed $controller istanza del controller
    * @param array $options array associativo di opzioni:
    *                       - where: where clause
    *                       - order: ordinamento
    *                       - limit: limite risultati
    * @return array di oggeti ricavati
    */
    public static function objects($controller = null, $options = array()) {

        $where = isset($options['where']) ? $options['where'] : null;
        $order = isset($options['order']) ? $options['order'] : null;
        $limit = isset($options['limit']) ? $options['limit'] : null;

        $res = array();
        $db = db::instance();
        $rows = $db->select('id', static::$table, $where, array('order'=>$order, 'limit'=>$limit, 'debug' => false));
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
        $db = db::instance();
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
     * @description Salva si i campi della tabella sia i m2m. I m2mt devono essere salvati manualmente,
     *              la classe @ref AdminTable lo fa in maniera automatica.
     *              Quando il salvataggio avviene con successo viene emesso un segnale 'post_save' da parte
     *              del modello.
     * @return il risultato dell'operazione o errori
     */
    public function save() {

        $event_dispatcher = EventDispatcher::instance();

        $result = true;

        if($this->_p['id']) {
            if(sizeof($this->_chgP)) {
                $fields = array();
                foreach($this->_chgP as $pName) $fields[$pName] = $this->_p[$pName];
                $result = $this->_db->update($fields, $this->_tbl_data, "id='{$this->_p['id']}'");
            }
        }
        else {
            if(sizeof($this->_chgP)) {
                $fields = array();
                foreach($this->_chgP as $pName) 
                {
                    if(!($pName == 'id' and $this->id === null))
                        $fields[$pName] = $this->_p[$pName];
                }
                $result = $this->_db->insert($fields, $this->_tbl_data);
            }
        }

        if(!$result) {
            return array('error'=>9);
        }

        if(!$this->_p['id']) $this->_p['id'] = $this->_db->getlastid($this->_tbl_data);

        $result = $this->savem2m();

        $event_dispatcher->emit($this, 'post_save', array('model' => $this));

        return $result;
    }

    /**
     * @brief Salvataggio dei m2m
     * @return TRUE
     */
    public function savem2m() {
        foreach($this->_m2m as $field => $values) {
            $obj = $this->_structure[$field];
            if(is_a($obj, '\Gino\ManyToManyField')) {
                $this->_db->delete($obj->getJoinTable(), $obj->getJoinTableId()."='".$this->id."'");
                foreach($values as $fid) {
                    $this->_db->insert(array(
                        $obj->getJoinTableId() => $this->id,
                        $obj->getJoinTableM2mId() => $fid
                        ), $obj->getJoinTable()
                    );
                }
            }
        }
        return TRUE;
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
     * @return risultato dell'operazione (bool) o un errore
     */
    public function delete() {

        // check constraints
        if($this->_check_is_constraint and count($this->_is_constraint)) {
          $res = $this->checkIsConstraint();
          if($res !== true) {
            return array("error"=>$this->isConstraintError($res));
          }
        }

        $this->deletem2m();
        $this->deletem2mthrough();

        $result = $this->deleteDbData();
        if($result !== TRUE) {
            return array("error"=>37);
        }

        foreach($this->_structure as $field) {
            if(method_exists($field, 'delete')) {
                $result = $field->delete();
                if($result !== TRUE) {
                    return $result;
                }
            }
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
                    $table = $field_obj->getJoinTable();
                    $id_string = $field_obj->getJoinTableId();
                    $m2m_id_string = $field_obj->getJoinTableM2mId();
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
            if(is_a($obj, 'ManyToManyField')) {
                $result = $result and $this->_db->delete($obj->getJoinTable(), $obj->getJoinTableId()."='".$this->id."'");
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
            if(is_a($obj, 'ManyToManyThroughField')) {
                $result = $result and $this->deletem2mthroughField($field);
            }
        }
        return $result;
    }

    /**
     * @brief Elimina lòe associazioni di un campo m2mt
     * @param string $field_name nome campo
     * @return risultato dell'operazione, bool
     */
    public function deletem2mthroughField($field_name) {
        $obj = $this->_structure[$field_name];
        $class = $obj->getM2m();
        foreach($this->_m2mt[$field_name] as $id) {
            $m2m_obj = new $class($id, $obj->getController());
            return $m2m_obj->delete();
        }

        return TRUE;
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
     * @brief Struttura dei campi di una tabella del database
     * 
     * Gli elementi della struttura possono essere sovrascritti all'interno del metodo structure() della classe che estende model. \n
     * 
     * Ogni elemento viene associato a una classe del tipo di dato e le vengono passate le specifiche del campo. \n
     * Esistono classi che corrispondono al tipo di dato e classi specifiche, per poter associare le quali è necessario sovrascrivere il campo nel metodo structure(). \n
     * Classi specifiche per particolati tipi di dato sono foreignKeyField, imageField, fileField, hiddenField.
     * 
     * @see DbManager::getTableStructure()
     * @see dataCache::get()
     * @see dataCache::save()
     * Esempio di riscrittura del metodo structure():
     * @code
     * public function structure($id) {
     *   
     *   $structure = parent::structure($id);
     *   
     *   $structure['ctg'] = new foreignKeyField(array(
     *     'name'=>'ctg', 
     *     'model'=>$this, 
     *     'foreign'=>'MyModel', 
     *     'foreign_order'=>'name', 
     *     'foreign_controller'=>$this->_controller
     *   ));
     *   
     *   $base_path = $this->_controller->getBasePath(); // example -> /contents/events/eventsInterface/
     *   $add_path_image = $this->id ? $this->_controller->getAddPath($this->id, 'image') : '';	// example -> id/img/
     *   
     *   $structure['image'] = new imageField(array(
     *     'name'=>'image', 
     *     'model'=>$this, 
     *     'extensions'=>self::$extension_media, 
     *     'path'=>$base_path, 
     *     'add_path'=>$add_path_image, 
     *     'resize'=>true, 
     *     'check_type'=>false, 
     *     'width'=>$this->_controller->getImageWidth(),
     *     'thumb_width'=>$this->_controller->getImageThumbWidth()
     *   ));
     * }
     * @endcode
     *
     * @param integer $id valore ID del record di riferimento
     * @return array
     *
     */
    public function structure($id) {

        if(!$this->_tbl_data) {
            throw new \Exception('La tabella _tbl_data del modello non è definita');
        }

        loader::import('class', array('\Gino\Cache'));
        if($id)
        {
            $records = $this->_db->select('*', $this->_tbl_data, "id='$id'");
            if($records and count($records)) {
                $this->_p = $records[0];
            }
            else {
                error::warning(sprintf(_('Oggetto %s, id %d inesistente.'), get_class($this), $id));
                $this->_p = array();
                $id = null;
            }
        }

        $cache = new \Gino\DataCache();
        if(!$fieldsTable = $cache->get('table_structure', $this->_tbl_data, 3600)) {
            $fieldsTable = $this->_db->getTableStructure($this->_tbl_data);
            $cache->save($fieldsTable);
        }

        $structure = array();
        if(sizeof($fieldsTable) > 0)
        {
            $primary_key = $fieldsTable['primary_key'];
            $fields = $fieldsTable['fields'];
            $keys = $fieldsTable['keys']; // array delle chiavi uniche

            foreach($fields AS $key=>$value)
            {
                if(!$id) $this->_p[$key] = null;

                $type = $value['type'];
                $maxLenght = $value['max_length'];
                $numberIntDigits = $value['n_int'];
                $numberDecimalDigits = $value['n_precision'];
                $order = $value['order'];
                $default = $value['default'];
                $null = $value['null'];
                $extra = $value['extra'];
                $enum = $value['enum'];

                $pkey = $key == $primary_key ? TRUE : FALSE;
                $ukey = in_array($key, $keys) ? TRUE : FALSE;
                $auto_increment = $extra == 'auto_increment' ? TRUE : FALSE;

                $dataType = $this->dataType($type);

                // Valori di un campo enumerazione
                if($enum)
                {
                    $array = explode(',', $enum);
                    $array_clean = array();
                    foreach($array AS $evalue)
                    {
                        preg_match("#\'([0-9a-zA-Z-_,.']+)\'#", $evalue, $matches);
                        if(isset($matches[1]))
                            $array_clean[$matches[1]] = $matches[1];
                    }
                    $enum = $array_clean;
                }

                $label = array_key_exists($key, $this->_fields_label) ? $this->_fields_label[$key] : ucfirst($key);

                $options_field = array(
                    'name'=>$key,
                    'model'=>$this,
                    'lenght'=>$maxLenght,
                    'primary_key'=>$pkey,
                    'unique_key'=>$ukey,
                    'auto_increment'=>$auto_increment, 
                    'type'=>$type, 
                    'int_digits'=>$numberIntDigits, 
                    'decimal_digits'=>$numberDecimalDigits, 
                    'order'=>$order, 
                    'default'=>$default, 
                    'required'=>$null=='NO' ? TRUE : FALSE, 
                    'extra'=>$extra, 
                    'enum'=>$enum, 
                    'label'=>$label, 
                );

                $structure[$key] = loader::load('fields/'.$dataType, array($options_field));
            }
        }

        return $structure;
    }

    /**
     * @brief Update della struttura da chiamare manualmente
     *
     * Quando ad esempio si modificano gli m2mt e si vogliono vederne gli effetti prima del ricaricamento pagina
     * Modificando gli m2mt, questi vengono aggiornati sul db, ma il modello che ha tali m2mt continua a referenziare i vecchi, questo perché il salvataggio
     * viene gestito da AdminTable e non da modello stesso che quindi ne è quasi all'oscuro. Ora questo metodo viene anche chiamato da AdminTable e quindi
     * le modifiche si riflettono immediatamente anche sul modello. Chiamarlo manualmente se la modifica agli m2mt viene fatta in modo diverso dall'uso del
     * metodo modelAction di Gino.AdminTable
     *
     * @return void
     */
    public function updateStructure() {
        $this->_structure = $this->structure($this->id);
    }

    /**
     * @brief Uniforma il tipo di dato di un campo definito dal metodo Gino.DbManager::getTableStructure()
     * @description ritorna il nome della classe che gestisce il modello del tipo di campo
     * @param string $type tipo di dato
     * @return tipo di dato
     */
    private function dataType($type) {

        if($type == 'tinyint' || $type == 'smallint' || $type == 'int' || $type == 'mediumint' || $type == 'bigint')
        {
            $dataType = 'integer';
        }
        elseif($type == 'float' || $type == 'double' || $type == 'decimal' || $type == 'numeric')
        {
            $dataType = 'float';
        }
        elseif($type == 'mediumtext' || $type == 'longtext')
        {
            $dataType = 'text';
        }
        elseif($type == 'varchar')
        {
            $dataType = 'char';
        }
        else
        {
            $dataType = $type;
        }

        $dataType = ucfirst($dataType).'Field';

        return $dataType;
    }
}
