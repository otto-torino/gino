<?php
/**
 * @file class.FileBuild.php
 * @brief Contiene la definizione ed implementazione della classe Gino.FileBuild
 *
 * @copyright 2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

loader::import('class/build', '\Gino\Build');

/**
 * @brief Campo di tipo FILE
 *
 * @copyright 2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class FileBuild extends Build {

	/**
	 * Proprietà dei campi specifiche del modello
	 */
	protected $_extensions, $_path, $_add_path, $_prefix, $_check_type, $_filesize_field, $_types_allowed, $_max_file_size;
	
    /**
     * Percorso assoluto della directory del file
     * @var string
     */
    protected $_directory;

    /**
     * Controllo sulla eliminazione del file
     * @var boolean
     */
    protected $_delete_file;

    /**
     * @brief Costruttore
     * 
     * @see Gino.Build::__construct()
     * @param array $options array associativo di opzioni del campo del database
     *   - opzioni generali definite come proprietà nella classe Build()
     */
    function __construct($options=array()) {

        parent::__construct($options);
        
        $this->_extensions = $options['extensions'];
        $this->_path = $options['path'];
        $this->_add_path = $options['add_path'];
        $this->_prefix = $options['prefix'];
        $this->_check_type = $options['check_type'];
        $this->_filesize_field = $options['filesize_field'];
        $this->_types_allowed = $options['types_allowed'];
        $this->_max_file_size =$options['max_file_size'];

        $this->_directory = $this->pathToFile();
        $this->_delete_file = false;
    }

    /**
     * @brief Getter della proprietà extensions (estensioni accettate)
     * @return proprietà extensions
     */
    public function getExtensions() {

        return $this->_extensions;
    }

    /**
     * @brief Setter della proprietà extensions
     * @param array $value
     * @return void
     */
    public function setExtensions($value) {

        $this->_extensions = $value;
    }

    /**
     * @brief Getter della proprietà path_abs (percorso assoluto)
     * @return proprietà path_abs
     */
    public function getPath() {

        return $this->_path;
    }

    /**
     * @brief Setter della proprietà path
     * @param mixed $value
     *   - string
     *   - array, array(object controller, string method_name, array method_params)
     * @return void
     */
    public function setPath($value) {

    	if(is_array($value) && count($value))
    	{
    		$controller = $value[0];
    		$method = $value[1];
    		$params = isset($value[2]) ? $value[2] : null;
    		
    		$call = array($value[0], $value[1]);
    		
    		if($params)
    			$path = call_user_func_array($call, $params);
    		else
    			$path = call_user_func($call);
    	}
    	else {
    		$path = $value;
    	}
    	
    	$this->_path = $path;
    }

    /**
     * @brief Getter della proprietà path_add
     * @return proprietà path_add
     */
    public function getAddPath() {

        return $this->_add_path;
    }

    /**
     * @brief Setter della proprietà path_add
     * @param string $value
     * @return void
     */
    public function setAddPath($value) {

        $this->_add_path = $value;
    }

    /**
     * @brief Getter della proprietà prefix
     * @return proprietà prefix
     */
    public function getPrefix() {

        return $this->_prefix;
    }

    /**
     * @brief Setter della proprietà prefix
     * @param string $value
     * @return void
     */
    public function setPrefix($value) {

        $this->_prefix = $value;
    }

    /**
     * @brief Getter della proprietà check_type (controllare o meno il mime type)
     * @return proprietà check_type
     */
    public function getCheckType() {

        return $this->_check_type;
    }

    /**
     * @brief Setter della proprietà check_type
     * @param bool $value
     * @return void
     */
    public function setCheckType($value) {

        $this->_check_type = $value;
    }

    /**
     * @brief Getter della proprietà types_allowed (mime types consentiti)
     * @return proprietà types_allowed
     */
    public function getTypesAllowed() {

        return $this->_types_allowed;
    }

    /**
     * @brief Setter della proprietà types_allowed
     * @param array $value
     * @return void
     */
    public function setTypesAllowed($value) {

        $this->_types_allowed = $value;
    }

    /**
     * @brief Getter della proprietà max_file_size
     * @return proprietà max_file_size
     */
    public function getMaxFileSize() {

        return $this->_max_file_size;
    }

    /**
     * @brief Setter della proprietà max_file_size
     * @param int $value
     * @return void
     */
    public function setMaxFileSize($value) {

        $this->_max_file_size = $value;
    }

    /**
     * @brief Getter della proprietà directory
     * @return proprietà direcotry
     */
    public function getDirectory() {

        return $this->_directory;
    }

    /**
     * @brief Setter della proprietà direcotry
     * @param string $value
     * @return void
     */
    public function setDirectory($value) {

        $this->_directory = $value;
    }
    
    /**
     * Verifica se il file presente è da eliminare
     * 
     * @param string $input_file
     * @param boolean $check_delete
     * @return boolean
     */
    private function checkDeleteFile($input_file, $check_delete) {
    	
    	$existing_values = $this->_model->getRecordValues();
    	
    	$existing_file = $existing_values ? $existing_values[$this->_field_object->getName()] : null;
    	$delete = (($input_file && $existing_file) || $check_delete) ? TRUE : FALSE;
    	
    	return $delete;
    }

    /**
     * @brief Salva il file uploadato
     * @param string $filename nome file
     * @param resource $filename_tmp file temporaneo
     * @return bool (true) or array (error)
     */
    protected function saveFile($filename, $filename_tmp) {

        if(!is_dir($this->_directory)) {
        	if(!@mkdir($this->_directory, 0755, TRUE))
        		return array('error'=>32);
        }

        $upload = move_uploaded_file($filename_tmp, $this->_directory.$filename) ? TRUE : FALSE;
        if(!$upload) { 
            return array('error'=>16);
        }

        if($this->_filesize_field) {
            $this->_model->{$this->_filesize_field} = $_FILES[$this->_name]['size'];
        };

        return TRUE;
    }

    /**
     * @brief Eliminazione diretta del file
     * @return bool (true) or array (error)
     */
    public function delete() {

    	$existing_values = $this->_model->getRecordValues();
    	$existing_file = $existing_values ? $existing_values[$this->_field_object->getName()] : null;
    	
    	if($existing_file && is_file($this->_directory.$existing_file)) {
            if(!@unlink($this->_directory.$existing_file)) {
                return array('error'=>17);
            }
        }

        return TRUE;
    }

    /**
     * @brief Ricostruisce il percorso a un file
     * 
     * @param array $options
     *   array associativo di opzioni
     *   - @b type (string): tipo di percorso
     *     - @a abs: assoluto
     *     - @a rel: relativo
     *   - @b thumb_file (boolean): file thumbnail
     *   - @b complete (boolean): percorso completo col nome del file
     * @return percorso
     */
    protected function pathToFile($options=array()) {

        $type = array_key_exists('type', $options) ? $options['type'] : 'abs';
        $complete = array_key_exists('complete', $options) ? $options['complete'] : false;
        $thumb_file = array_key_exists('thumb_file', $options) ? $options['thumb_file'] : false;
		
        $filename = $thumb_file ? $this->_prefix_thumb.$this->_value : $this->_value;
        $directory = $this->_path.$this->_add_path;
        $directory = $this->conformPath($directory);

        if($complete)
            $directory = $directory.$filename;

        if($type == 'rel')
            $directory = relativePath($directory);

        return $directory;
    }

    /**
     * @brief Imposta il separatore di directory come ultimo carattere
     *
     * @param string $directory nome della directory
     * @return path directory
     */
    private function conformPath($directory){

        $directory = (substr($directory, -1) != OS && $directory != '') ? $directory.OS : $directory;
        return $directory;
    }

    /**
     * @brief Sostituisce nel nome di un file i caratteri diversi da [a-zA-Z0-9_.-] con il carattere underscore (_)
     * 
     * Se il nome del file è presente lo salva aggiungendogli un numero progressivo
     * 
     * @param string $filename nome del file
     * @param string $prefix prefisso da aggiungere al nome del file
     * @param array $options
     *   array associativo di opzioni in aggiunta a quelle del metodo clean()
     *   - @b add_index (boolean)
     *     - true, aggiunge un numero progressivo al nome del file, ad esempio da foo.1.txt a foo.1.2.txt
     *     - false (default), incrementa il numero senza aggiungerlo, ad esempio da foo.1.txt a foo.2.txt
     * @return nome file
     */
    private function checkFilename($filename, $prefix, $options=null) {

        $add_index = gOpt('add_index', $options, false);

        $filename = preg_replace("#[^a-zA-Z0-9_\.-]#", "_", $filename);
        $filename = $prefix.$filename;

        $files = is_dir($this->_directory) ? scandir($this->_directory) : array();

        if($add_index)
        {
            $i=1;
            while(in_array($filename, $files))
            {
                $filename = substr($filename, 0, strrpos($filename, '.')+1).$i.substr($filename, strrpos($filename, '.'));
                $i++;
            }
        }
        else
        {
            while(in_array($filename, $files))
            {
                $info = pathinfo($filename);
                $file =  basename($filename, '.'.$info['extension']);

                if(preg_match('#([.]+)+#', $file))
                {
                    $prefix = substr($file, 0, strrpos($file, '.')+1);

                    $i = substr($file, strrpos($file, '.')+1);

                    if(preg_match('#(^[0-9]+$)#', $i))
                    {
                        (int) $i;
                        $i++;
                    }
                    else
                    {
                        if($i) $prefix .= $i.'.';
                        $i=1;
                    }
                }
                else
                {
                    $prefix = $file.'.';
                    $i=1;
                }

                $filename = $prefix.$i.'.'.$info['extension'];
            }
        }

        return $filename;
    }
    
    /**
     * @see Gino.Build::formFilter()
     */
    public function formFilter($options = array()) {
    	
    	$options['widget'] = 'text';
    	
    	return parent::formFilter($options);
    }
    
    /**
     * @see Gino.Build::filterWhereClause()
     */
    public function filterWhereClause($value) {
    
    	return $this->_table.".".$this->_name." LIKE '%".$value."%'";
    }
    
    /**
     * @see Gino.Build::cleanFilter()
     */
    public function cleanFilter($options)
    {
    	$request = \Gino\Http\Request::instance();
    	$escape = gOpt('escape', $options, TRUE);
    
    	return cleanVar($request->POST, $this->_name, 'string', null, array('escape'=>$escape));
    }
    
    /**
     * @see Gino.Build::formElement()
     */
    public function formElement($mform, $options=array()) {
    
    	if($this->_value != '' and (!isset($options['preview']) or $options['preview']))
    	{
    		$options['preview'] = TRUE;
    		$options['previewSrc'] = $this->pathToFile(array('type' => 'rel', 'complete' => TRUE));
    	}
    	if(!isset($options['extensions'])) $options['extensions'] = $this->_extensions;
    
    	return parent::formElement($mform, $options);
    }
    
    /**
     * @see Gino.Build::clean()
     * @description Effettua l'upload del file
     * 
     * @return string or Exception
     */
    public function clean($request_value, $options=null) {
    	
    	$request = \Gino\Http\Request::instance();
    	
    	if($request_value) {
    		$request_value = $this->checkFilename($request_value, $this->_prefix, $options);
    	}
    	
    	$check_name = isset($options['check_del_file_name']) ? $options['check_del_file_name'] : "check_del_".$this->_name;
    	$check_delete = $request->checkPOSTKey($check_name, 'ok');
    	$upload = $request_value ? TRUE : FALSE;
    	
    	$this->_delete_file = $this->checkDeleteFile($request_value, $check_delete);
    	
    	if($upload) {
    		$filename = (string) $request_value;
    	}
    	elseif($this->_delete_file) {
    		$filename = '';
    	}
    	else {
    		$filename = $this->_value;
    	}
    	
    	if($this->_delete_file) {
    		$this->delete();
    	}
    	
    	$existing_values = $this->_model->getRecordValues();
    	$existing_file = $existing_values ? $existing_values[$this->_field_object->getName()] : null;
    	
    	$code_messages = \Gino\Error::codeMessages();
    	
    	if($existing_file && $filename == $existing_file)
    	{
    		return $filename;
    	}
    	elseif($filename)
    	{
    		$filename_size = $request->FILES[$this->_name]['size'];
    		$filename_tmp = $request->FILES[$this->_name]['tmp_name'];
    		
    		if(!$filename_size) {
    			throw new \Exception(_("Empty filename"));
    		}
    		
    		if($this->_max_file_size && $filename_size > $this->_max_file_size) {
    			throw new \Exception($code_messages[33]);
    		}
    		
    		$finfo = finfo_open(FILEINFO_MIME_TYPE);
    		$mime = finfo_file($finfo, $filename_tmp);
    		finfo_close($finfo);
    		if(!\Gino\extension($filename, $this->_extensions) ||
    		preg_match('#%00#', $filename) ||
    		($this->_check_type && !in_array($mime, $this->_types_allowed))) {
    			throw new \Exception($code_messages[3]);
    		}
    		
    		// Save File
    		$save = $this->saveFile($filename, $filename_tmp);
    		
    		if($save === true) {
    			return $filename;
    		}
    		elseif(is_array($save)) {
    			$code = $save['error'];
    			throw new \Exception($code_messages[$code]);
    		}
    		else {
    			throw new \Exception(_("Errore nel salvataggio del file"));
    		}
    	}
    	else return '';
    }
}
