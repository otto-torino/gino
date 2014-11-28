<?php
/**
 * @file class.GraphicsItem.php
 * @brief Contiene la definizione ed implementazione della classe Gino.App.Graphics.GraphicsItem
 *
 * @copyright 2005-2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

namespace Gino\App\Graphics;

/**
 * @brief Classe di tipo Gino.Model che rappresenta un header/footer
 *
 * @copyright 2005-2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class GraphicsItem extends \Gino\Model {

    public static $table = "sys_graphics";
    private static $_extension_img = array("jpg", "png", "gif");

    /**
     * @brief Costruttore
     * @return istanza di Gino.App.Graphics.GraphicsItem
     */
    function __construct($id) {

        $this->_model_label = _('Elemento grafico');

        $this->_fields_label = array(
            'name' => _('nome'),
            'description' => _('descrizione'),
            'type' => array(_('tipo'), _('scegliere se mostrare l\'immagine caricata o includere il codice html')),
            'image' => _('immagine'),
            'html' => _('codice html'),
        );

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

    /*
     * @brief Sovrascrive la struttura di default
     *
     * @see Gino.Model::structure()
     * @param integer $id
     * @return array, struttura
     */
    public function structure($id) {

        $structure = parent::structure($id);

        $structure['type'] = new \Gino\EnumField(array(
            'name'=>'type',
            'model'=>$this,
            'widget'=>'select',
            'required'=>true,
            'enum'=>array(1 => _('immagine'), 2 => _('codice')),
        ));

        $base_path = GRAPHICS_DIR;

        $structure['image'] = new \Gino\ImageField(array(
            'name'=>'image',
            'model'=>$this,
            'lenght'=>100,
            'extensions'=>self::$_extension_img,
            'resize'=>FALSE,
            'preview'=>TRUE,
            'path'=>$base_path
        ));

        return $structure;

    }

}
