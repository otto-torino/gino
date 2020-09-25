<?php
/**
 * @file class.ModelTools.php
 * @brief Contiene gli strumenti del modello Gino.Model
 */
namespace Gino;


class ModelTools {

    /**
     * Controller
     * @var object
     */
    private $_controller;

    /**
     * Modello
     * @var object
     */
    private $_model;

    /**
     * Costruttore
     *
     * @param object $model
     * @param object $controller
     * @return void
     */
    function __construct($model, $controller) {

        $this->_model = $model;
        $this->_controller = $controller;
    }
    
    /**
     * @brief Url dell'interfaccia di dettaglio del record
     *
     * @param array $options
     * @return string
     */
    public function objectUrl($options=[]) {
        
        $interface = \Gino\gOpt('interface', $options, 'detail');
        $ref = \Gino\gOpt('ref', $options, 'slug');
        
        return $this->_controller->link($this->_controller->getInstanceName(), $interface, ['id' => $this->_model->$ref]);
    }
    
    /**
     * @brief Url assoluto dell'interfaccia di dettaglio del record
     *
     * @param array $options
     * @return string
     */
    public function objectAbsoluteUrl($options=[]) {
        
        $interface = \Gino\gOpt('interface', $options, 'detail');
        $ref = \Gino\gOpt('ref', $options, 'slug');
        
        $request = \Gino\Http\Request::instance();
        return $request->root_absolute_url.$this->_controller->link($this->_controller->getInstanceName(), $interface, ['id' => $this->_model->$ref]);
    }
    
    /**
     * @brief Elenco tag con link all'elenco dei post correlati
     * @description I tag sono salvati nel campo @a tags di tipo Gino.TagField
     *
     * @see Gino.GTag::viewTags
     * @param array $options opzioni di Gino.GTag::viewTags
     * @return string
     */
    public function showTags($options=[]) {
        
        return \Gino\GTag::viewTags($this->_controller, $this->_model->tags, $options);
    }
    
    /**
     * @brief Condizione in una select query per trovare i record associati a un determinato tag
     * 
     * @see Gino.GTag::whereCondition
     * @param \Gino\Controller $controller
     * @param string $tag valore del tag da ricercare
     * @return string
     */
    public static function tagQueryCondition($controller, $tag) {
        
        return \Gino\GTag::whereCondition($controller, $tag);
    }
    
    /**
     * @brief Imposta la condizione della query
     *
     * @param object $controller
     * @param array $options associativo di opzioni
     * @return string
     */
    /*public static function queryConditions($controller, $options=[]) {
        
        $where = ["instance='".$controller->getInstance()."'"];
        // add other conditions
        $where = implode(' AND ', $where);
        
        return $where;
    }*/
    
    /**
     * @brief Restituisce il numero di oggetti selezionati
     *
     * @param object $controller istanza del controller
     * @param string $table nome della tabella del modello
     * @param array $options array associativo di opzioni
     * @return integer
     */
    /*public static function objectCount($controller, $table, $options = []) {
        
        $db = \Gino\Db::instance();
        
        $where = self::queryConditions($controller, $options);
        
        return $db->getNumRecords($table, $where);
    }*/
    
    /**
     * @brief Path relativo all'immagine di un campo Gino.ImageField
     *
     * @param array $options array associativo di opzioni
     * @return string
     */
    /*public function imagePath($options = []) {
        
        $fieldname = \Gino\gOpt('fieldname', $options, 'image');
        $dir = \Gino\gOpt('dir', $options, null);
        
        $path = $this->_controller->getBasePath().'/';
        if($dir) {
            $path .= $dir.'/';
        }
        $path .= $this->_model->$fieldname;
        
        return $path;
    }*/
    
    /**
     * @brief Path relativo a un file allegato
     * 
     * @param array $options array associativo di opzioni
     * @return string
     */
    public function filePath($options = []) {
        
        $fieldname = \Gino\gOpt('fieldname', $options, 'attachment');
        
        return $this->_controller->getBasePath().'/attachment/'.$this->_model->$fieldname;
    }
    
    /**
     * @brief Path relativo al download di un file
     * @return string
     */
    public function fileDownloadUrl() {
        
        return $this->_controller->link($this->_controller->getInstanceName(), 'download', array('id' => $this->_model->id));
    }
    
    /**
     * @brief Dimensioni immagine
     * 
     * @param array $options array associativo di opzioni
     * @return array ('width' => WIDTH, 'height' => HEIGHT)
     */
    public function imageSize($options = []) {
        
        list($width, $height, $type, $attr) = getimagesize(\Gino\absolutePath($this->imagePath($options)));
        return array('width' => $width, 'height' => $height);
    }
    
    /**
     * @brief Elenco delle categorie associate a un record
     * @description Le categorie sono salvate nel campo @a categories di tipo Gino.ManyToManyField
     * 
     * @param string $modelname nome del modello categoria
     * @return array di oggetti Category
     */
    public function categoriesName($modelname) {
        
        $items = array();
        if(count($this->_model->categories)) {
            foreach ($this->_model->categories AS $ctg_id) {
                
                $items[] = new $modelname($ctg_id, $this->_controller);
            }
        }
        return $items;
    }
    
    /**
     * @brief Elenco delle categorie associate a un record. Ogni categoria è un collegamento a una pagina con i suoi record associati.
     * @description Le categorie sono salvate nel campo @a categories di tipo Gino.ManyToManyField
     * 
     * @param string $modelname nome del modello categoria
     * @param array $options array associativo di opzioni
     * @return array
     */
    public function categoriesLink($modelname) {
        
        $interface = \Gino\gOpt('interface', $options, 'archive');
        
        $items = array();
        if(count($this->_model->categories)) {
            foreach ($this->_model->categories AS $ctg_id) {
                
                $ctg = new $modelname($ctg_id, $this->_controller);
                
                $url = $this->_controller->link($this->_controller->getInstanceName(), $interface, array('ctg' => $ctg->slug));
                $link = "<a href=\"$url\">".\Gino\htmlChars($ctg->name)."</a>";
                $items[] = $link;
            }
        }
        return $items;
    }
}
