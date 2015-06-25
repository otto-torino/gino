<?php
/**
 * @file class.FileField.php
 * @brief Contiene la definizione ed implementazione della classe Gino.FileField
 *
 * @copyright 2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

loader::import('class/fields', '\Gino\FieldBuild');

/**
 * @brief Campo di tipo FILE
 *
 * @copyright 2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class FileFieldBuild extends FieldBuild {

    /**
     * Percorso assoluto della directory del file
     * 
     * @var string
     */
    protected $_directory;

    /**
     * Controllo sulla eliminazione del file
     * 
     * @var boolean
     */
    protected $_delete_file;

    /**
     * Proprietà dei campi specifiche del tipo di campo
     */
    protected $_extensions, $_path_abs, $_path_add, $_prefix, $_check_type, $_types_allowed, $_max_file_size;

    /**
     * @brief Costruttore
     * 
     * @see Gino.FieldBuild::__construct()
     * @param array $options array associativo di opzioni del campo del database
     *   - opzioni generali definite come proprietà nella classe FieldBuild()
     *   - @b extensions (array): estensioni lecite di file
     *   - @b path (mixed)
     *     - string, percorso assoluto fino a prima del valore del record ID
     *     @code
     *     $controller = new auth();
     *     'path' => $controller->getBasePath(),
     *     @endcode
     *     - array
     *     @code
     *     'path' => array('\Gino\App\Attachment\AttachmentItem', 'getPath'),
     *     @endcode
     *   - @b add_path (string): parte del percorso assoluto dal parametro @a path fino a prima del file
     *   - @b prefix (string)
     *   - @b check_type (boolean)
     *   - @b types_allowed(array)
     *   - @b max_file_size (integer)
     */
    function __construct($options=array()) {

        parent::__construct($options);
        
        $this->_delete_file = false;

        $this->_extensions = isset($options['extensions']) ? $options['extensions'] : array('txt','xml','html','htm','doc','xls','zip','pdf');
        $this->_path_abs = isset($options['path']) ? $this->setPath($options['path']) : '';
        $this->_path_add = isset($options['add_path']) ? $options['add_path'] : '';
        $this->_prefix = isset($options['prefix']) ? $options['prefix'] : '';
        $this->_check_type = isset($options['check_type']) ? $options['check_type'] : false;
        $this->_filesize_field = isset($options['filesize_field']) ? $options['filesize_field'] : false;
        $this->_types_allowed = isset($options['types_allowed']) ? $options['types_allowed'] : array(
            "text/plain",
            "text/html",
            "text/xml",
            "video/mpeg",
            "audio/midi",
            "application/pdf",
            "application/x-compressed",
            "application/x-zip-compressed",
            "application/zip",
            "multipart/x-zip",
            "application/vnd.ms-excel",
            "application/msword",
            "application/x-msdos-program",
            "application/octet-stream"
        );
        $this->_max_file_size = isset($options['max_file_size']) ? $options['max_file_size'] : null;

        $this->_directory = $this->pathToFile();
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

        return $this->_path_abs;
    }

    /**
     * @brief Setter della proprietà enum
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
    	
    	$this->_path_abs = $path;
    }

    /**
     * @brief Getter della proprietà path_add
     * @return proprietà path_add
     */
    public function getAddPath() {

        return $this->_path_add;
    }

    /**
     * @brief Setter della proprietà path_add
     * @param string $value
     * @return void
     */
    public function setAddPath($value) {

        $this->_path_add = $value;
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
     * @brief Ripulisce un input per l'inserimento in database
     * @see Gino.Field::clean()
     */
    public function clean($options=null) {

        $request = \Gino\Http\Request::instance();
        if(isset($request->FILES[$this->_name]['name']) AND $request->FILES[$this->_name]['name'] != '')
        {
            $filename = $request->FILES[$this->_name]['name'];
            $filename = $this->checkFilename($filename, $this->_prefix, $options);
        }
        else $filename = '';

        $check_name = isset($options['check_del_file_name']) ? $options['check_del_file_name'] : "check_del_".$this->_name;
        $check_delete = $request->checkPOSTKey($check_name, 'ok');
        $delete = (($filename && $this->_value) || $check_delete) ? TRUE : FALSE;
        $upload = $filename ? TRUE : FALSE;

        $this->_delete_file = $delete;

        if($upload) $file = $filename;
        elseif($delete) $file = '';
        else $file = $this->_value;

        return $file;
    }

    /**
     * @brief Salva il file uploadato
     * @param string $filename nome file
     * @param resource $filename_tmp file temporaneo
     * @return risultato operazione, bool
     */
    protected function saveFile($filename, $filename_tmp) {

        if(!is_dir($this->_directory))
            if(!@mkdir($this->_directory, 0755, TRUE))
                return array('error'=>32);

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
     * @return True o errore
     */
    public function delete() {

        if(is_file($this->_directory.$this->_value)) {
            if(!@unlink($this->_directory.$this->_value)) {
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
        $directory = $this->_path_abs.$this->_path_add;
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
     * @brief Elemento del form nei filtri
     * @return elemento form (label + input)
     */
    public function formFilter(\Gino\Form $form, $options = array())
    {
    	return $form->cinput($this->_name, 'text', $this->_value, $this->_label, array());
    }
    
    /**
     * @brief Definisce la condizione WHERE per il campo
     *
     * @param mixed $value
     * @return where clause
     */
    public function filterWhereClause($value) {
    
    	return $this->_table.".".$this->_name." LIKE '%".$value."%'";
    }
    
    /**
     * Clean valore input da filtri
     * @return valore ripulito
     */
    public function cleanFilter($options)
    {
    	$request = \Gino\Http\Request::instance();
    	$escape = gOpt('escape', $options, TRUE);
    
    	return cleanVar($request->POST, $this->_name, 'string', null, array('escape'=>$escape));
    }
    
    /**
     * @brief Widget html per il form
     * @param \Gino\Form $form istanza di Gino.Form
     * @param array $options opzioni
     * @see Gino.Field::formElement()
     * @return widget html
     */
    public function formElement(\Gino\Form $form, $options) {
    
    	if($this->_value != '' and (!isset($options['preview']) or $options['preview']))
    	{
    		$options['preview'] = TRUE;
    		$options['previewSrc'] = $this->pathToFile(array('type' => 'rel', 'complete' => TRUE));
    	}
    	if(!isset($options['extensions'])) $options['extensions'] = $this->_extensions;
    
    	return parent::formElement($form, $options);
    }
    
    //////
    public function validate() {
    	
    	///// -> managefile()
    }
    
    public function manageFile($value) {
    
    	if(is_null($value)) {
    		return null;
    	}
    	
    	$filename = (string) $value;
    	 
    	$request = \Gino\Http\Request::instance();
    	 
    	if($this->_delete_file) {
    		$this->delete();
    	}
    	
    	if($filename == $this->_value)    // file preesistente
    	{
    		return $filename;
    	}
    	elseif($filename)
    	{
    		$filename_size = $request->FILES[$this->_name]['size'];
    		$filename_tmp = $request->FILES[$this->_name]['tmp_name'];
    		 
    		if($this->_max_file_size && $filename_size > $this->_max_file_size) {
    			return array('error'=>33);
    		}
    		 
    		$finfo = finfo_open(FILEINFO_MIME_TYPE);
    		$mime = finfo_file($finfo, $filename_tmp);
    		finfo_close($finfo);
    		if(!\Gino\extension($filename, $this->_extensions) ||
    				preg_match('#%00#', $filename) ||
    				($this->_check_type && !in_array($mime, $this->_types_allowed))) {
    					return array('error'=>03);
    				}
    				 
    				if($this->saveFile($filename, $filename_tmp))
    					return $filename;
    				else
    					throw new \Exception(_("Errore nel salvataggio del file"));
    	}
    	else return '';
    }
}
