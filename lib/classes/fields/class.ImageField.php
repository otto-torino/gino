<?php
/**
 * @file class.ImageField.php
 * @brief Contiene la definizione ed implementazione della classe Gino.ImageField
 *
 * @copyright 2005-2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

loader::import('class/fields', array('\Gino\Field', '\Gino\FileField'));

/**
 * @brief Campo di tipo IMMAGINE
 *
 * Tipologie di input associabili: input file
 *
 * @copyright 2005-2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 * 
 * ##CREAZIONE THUMBNAIL
 * Il thumbnail dell'immagine viene generato automaticamente se è abilitata l'opzione @a resize (default true), e se al contempo 
 * l'opzione @a thumb è pari a true (default) e l'opzione @a prefix_thumb non è nulla (default thumb_).
 */
class ImageField extends FileField {

	/**
	 * Proprietà dei campi specifiche del modello
	 */
	protected $_resize, $_thumb, $_prefix_file, $_prefix_thumb, $_width, $_height, $_thumb_width, $_thumb_height;
	
    /**
     * @brief Costruttore
     *
     * @see Gino.Field::__construct()
     * @param array $options array associativo di opzioni del campo del database
     *   - opzioni generali definite come proprietà nella classe Field()
     *   - opzioni generali definite come proprietà nella classe FileField()
     *   - opzioni specifiche del tipo di campo
     *     - @b resize (boolean)
     *     - @b thumb (boolean)
     *     - @b prefix_file (string)
     *     - @b prefix_thumb (string)
     *     - @b width (integer)
     *     - @b height (integer)
     *     - @b thumb_width (integer)
     *     - @b thumb_height (integer)
     */
    function __construct($options) {

        $this->_default_widget = 'image';
        parent::__construct($options);
        
        $this->_extensions = isset($options['extensions']) ? $options['extensions'] : array("jpg, png");
        $this->_types_allowed = isset($options['types_allowed']) ? $options['types_allowed'] : array(
        	"image/jpeg",
        	"image/gif",
        	"image/png"
        );
        
        $this->_resize = isset($options['resize']) ? $options['resize'] : true;
        $this->_thumb = isset($options['thumb']) ? $options['thumb'] : true;
        $this->_prefix_file = isset($options['prefix_file']) ? $options['prefix_file'] : '';
        $this->_prefix_thumb = isset($options['prefix_thumb']) ? $options['prefix_thumb'] : 'thumb_';
        $this->_width = isset($options['width']) ? $options['width'] : 800;
        $this->_height = isset($options['height']) ? $options['height'] : null;
        $this->_thumb_width = isset($options['thumb_width']) ? $options['thumb_width'] : 200;
        $this->_thumb_height = isset($options['thumb_height']) ? $options['thumb_height'] : null;
    }
    
    /**
     * @see Gino.Field::getProperties()
     */
    public function getProperties() {
    
    	$prop = parent::getProperties();
    
    	$prop['extensions'] = $this->_extensions;
    	$prop['types_allowed'] = $this->_types_allowed;
    	
    	$prop['resize'] = $this->_resize;
    	$prop['thumb'] = $this->_thumb;
    	$prop['prefix_file'] = $this->_prefix_file;
    	$prop['prefix_thumb'] = $this->_prefix_thumb;
    	$prop['width'] = $this->_width;
    	$prop['height'] = $this->_height;
    	$prop['thumb_width'] = $this->_thumb_width;
    	$prop['thumb_height'] = $this->_thumb_height;
    
    	return $prop;
    }
}
