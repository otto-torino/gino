<?php
/**
 * @file class.DirectoryBuild.php
 * @brief Contiene la definizione ed implementazione della classe Gino.DirectoryBuild
 *
 * @copyright 2015-2016 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

/**
 * @brief Gestisce i campi di tipo DIRECTORY
 *
 * @copyright 2015-2016 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class DirectoryBuild extends Build {

    /**
     * Proprietà dei campi specifiche del tipo di campo
     */
    protected $_path, $_prefix, $_default_name;

    /**
     * @brief Costruttore
     *
     * @see Gino.Build::__construct()
     * @param array $options array associativo di opzioni del campo del database
     *   - opzioni generali definite come proprietà nella classe Build()
     */
    function __construct($options) {

        parent::__construct($options);

        $this->_path = $options['path'];
        $this->_prefix = $options['prefix'];
        $this->_default_name = $options['default_name'];
    }

    /**
     * @brief Getter della proprietà path
     * @return proprietà path
     */
    public function getPath() {

        return $this->_path;
    }

    /**
     * @brief Setter della proprietà path
     * @param string $value
     * @return void
     */
    public function setPath($value) {

        $this->_path = $value;
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
     * @brief Nome di default della directory
     * 
     * @see Gino.clean_text()
     * @param array $options
     * @return nome directory o null
     */
    private function defaultName($options){

		$request = \Gino\Http\Request::instance();
		if($this->_default_name)
		{
			$field = array_key_exists('field', $this->_default_name) ? $this->_default_name['field'] : 'id';
			$maxlentgh = array_key_exists('maxlentgh', $this->_default_name) ? $this->_default_name['maxlentgh'] : 15;

			$request = Request::instance();
			$value = clean_text($request->method->$field, $options);

			$name_dir = substr($value, 0, $maxlentgh);
			$name_dir = preg_replace("#[^a-zA-Z0-9_\.-]#", "_", $name_dir);
			return $name_dir;
		}
		else return null;
    }

    /**
     * @brief Sostituisce nel nome di una directory i caratteri diversi da [a-zA-Z0-9_.-] con il carattere underscore (_)
     * 
     * Se il nome della directory è presente lo salva aggiungendogli un numero progressivo
     * 
     * @param string $name_dir nome della directory
     * @return nome directory 'friendly'
     */
    private function checkName($name_dir) {

        $name_dir = preg_replace("#[^a-zA-Z0-9_\.-]#", "_", $name_dir);
        $name_dir = $this->_prefix.$name_dir;

        $files = scandir($this->_path);
        $i=1;

        while(in_array($name_dir, $files)) { $name_dir = substr($name_dir, 0, strrpos($name_dir, '.')+1).$i.substr($name_dir, strrpos($name_dir, '.')); $i++; }

        return $name_dir;
    }
    
    /**
     * @see Gino.Build::clean()
     * @description Crea la directory se non esiste
     * 
     * @see defaultName()
     * @param array $options array associativo di opzioni
     *   - opzioni della funzione Gino.clean_text()
     * @return string
     */
    public function clean($request_value, $options=null) {
    
    	$value = clean_text($request_value, $options);
    	
    	if(!$value) {
    		$value = $this->defaultName($options);
    	}
    	
    	if($value != $this->_value) {
    		$value = $this->checkName($value);
    	}
    	
    	$existing_values = $this->_model->getRecordValues();
    	$existing_dir = $existing_values ? $existing_values[$this->getName()] : null;
    	
    	if($value == $existing_dir)
        {
            return $value;
        }
        elseif($value)
        {
            if(!$this->_model->id)
            {
                if(!mkdir($this->_path.$value)) {
                	throw new \Gino\Exception\ValidationError(Error::codeMessages(32));
                }
            }
            else
            {
                if(!$existing_dir)
                {
                    if(!mkdir($this->_path.$value)) {
                        throw new \Gino\Exception\ValdationError(Error::codeMessages(32));
                    }
                }
                else
                {
                    if(!rename($this->_path.$existing_dir, $this->_path.$value)) {
                        throw new \Gino\Exception\ValidationError(Error::codeMessages(32));
                    }
                }
            }

            return $value;
        }
        else return null;
    }

    /**
     * @brief Eliminazione della directory
     * @return TRUE o errore
     */
    public function delete() {

        if($this->_value and is_dir($this->_path.$this->_value)) {
            if(!\Gino\deleteFileDir($this->_path.$this->_value))
                return array('error'=>_("errore nella eliminazione della directory"));
        }

        return TRUE;
    }
}
