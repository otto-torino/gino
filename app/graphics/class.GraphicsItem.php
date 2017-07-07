<?php
/**
 * @file class.GraphicsItem.php
 * @brief Contiene la definizione ed implementazione della classe Gino.App.Graphics.GraphicsItem
 *
 * @copyright 2005-2017 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

namespace Gino\App\Graphics;

/**
 * @brief Classe di tipo Gino.Model che rappresenta un header/footer
 *
 * @copyright 2005-2017 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class GraphicsItem extends \Gino\Model {

    public static $table = "sys_graphics";
    public static $columns;
    
    private static $_extension_img = array("jpg", "png", "gif");

    /**
     * @brief Costruttore
     * @return istanza di Gino.App.Graphics.GraphicsItem
     */
    function __construct($id) {

        $this->_model_label = _('Elemento grafico');

        $this->_tbl_data = self::$table;
        parent::__construct($id);
    }

    /**
     * @brief Rappresentazione a stringa dell'oggetto
     * @return descrizione
     */
    function __toString() {
        return (string) $this->description;
    }
    
    /**
     * Struttura dei campi della tabella di un modello
     *
     * @return array
     */
    public static function columns() {
    	
    	$columns['id'] = new \Gino\IntegerField(array(
			'name'=>'id',
			'primary_key'=>true,
			'auto_increment'=>true,
		));
    	$columns['name'] = new \Gino\CharField(array(
    		'name'=>'name',
    		'label'=>_("Nome"),
    		'required'=>true,
    		'max_lenght'=>50,
    		'table'=>self::$table
    	));
    	$columns['description'] = new \Gino\CharField(array(
    		'name'=>'description',
    		'label' => _("Descrizione"),
    		'required'=>true,
    		'max_lenght'=>100,
    	));
		$columns['type'] = new \Gino\EnumField(array(
    		'name' => 'type',
    		'label' => array(_('Tipo'), _('scegliere se mostrare l\'immagine caricata o includere il codice html')),
    		'widget' => 'select',
    		'required' => true,
    		'choice' => array(1 => _('immagine'), 2 => _('codice')),
    	));
    	
    	$columns['image'] = new \Gino\ImageField(array(
    		'name' => 'image',
    		'label' => _('Immagine'),
    		'max_lenght' => 100,
    		'extensions' => self::$_extension_img,
    		'resize' => FALSE,
    		'preview' => TRUE,
    		'path' => GRAPHICS_DIR
    	));
    	$columns['html'] = new \Gino\TextField(array(
    		'name' => 'html',
    		'label' => _("Codice html"),
    		'required' => false
    	));
    	
    	return $columns;
    }
    
    public function getTypeName() {
    	
    	if($this->type == 1) {
    		return _('immagine');
    	}
    	elseif($this->type == 2) {
    		return _('codice');
    	}
    	else {
    		return null;
    	}
    }
}

GraphicsItem::$columns=GraphicsItem::columns();
