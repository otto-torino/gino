<?php
/**
 * @file class.FileField.php
 * @brief Contiene la definizione ed implementazione della classe Gino.FileField
 *
 * @copyright 2005-2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

loader::import('class/fields', '\Gino\Field');

/**
 * @brief Campo di tipo FILE
 *
 * @copyright 2005-2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class FileField extends Field {

	/**
	 * Proprietà dei campi specifiche del modello
	 */
	protected $_extensions, $_path, $_add_path, $_prefix, $_check_type, $_types_allowed, $_max_file_size;
	
    /**
     * @brief Costruttore
     * 
     * @see Gino.Field::__construct()
     * @param array $options array associativo di opzioni del campo del database
     *   - opzioni generali definite come proprietà nella classe Field()
     *   - opzioni specifiche del tipo di campo
     *     - @b extensions (array): estensioni lecite di file
     *     - @b path (mixed)
     *       - string, percorso assoluto fino a prima del valore del record ID
     *       @code
     *       $controller = new auth();
     *       'path' => $controller->getBasePath(),
     *       @endcode
     *       - array
     *       @code
     *       'path' => array('\Gino\App\Attachment\AttachmentItem', 'getPath'),
     *       @endcode
     *     - @b add_path (string): parte del percorso assoluto dal parametro @a path fino a prima del file
     *     - @b prefix (string)
     *     - @b check_type (boolean)
     *     - @b types_allowed(array)
     *     - @b max_file_size (integer)
     */
    function __construct($options) {

        $this->_default_widget = 'file';
        parent::__construct($options);

        $this->_value_type = null;
        
        $this->_extensions = isset($options['extensions']) ? $options['extensions'] : array('txt','xml','html','htm','doc','xls','zip','pdf');
        $this->_path = isset($options['path']) && $options['path'] ? $options['path'] : '';
        $this->_add_path = isset($options['add_path']) && $options['add_path'] ? $options['add_path'] : '';
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
    }
    
	/**
     * @see Gino.Field::getProperties()
     */
    public function getProperties() {
    
    	$prop = parent::getProperties();
    
    	$prop['extensions'] = $this->_extensions;
    	$prop['path'] = $this->_path;
    	$prop['add_path'] = $this->_add_path;
    	$prop['prefix'] = $this->_prefix;
    	$prop['check_type'] = $this->_check_type;
    	$prop['filesize_field'] = $this->_filesize_field;
    	$prop['types_allowed'] = $this->_types_allowed;
    	$prop['max_file_size'] = $this->_max_file_size;
    
    	return $prop;
    }

    /**
     * @see Gino.Field::getValue()
     * @return null or string
     */
    public function getValue($value) {
    	 
    	if(is_null($value)) {
    		return null;
    	}
    	elseif(is_string($value)) {
    		return $value;
    	}
    	else throw new \Exception(_("Valore non valido"));
    }
}
