<?php
namespace Gino\App\Graphics;

class GraphicsItem extends \Gino\Model {

  public static $table = "sys_graphics";
  private static $_extension_img = array("jpg", "png", "gif");

  function __construct($id) {

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

  function __toString() {
    return $this->description;
  }

  public function getModelLabel() {
      return _('Elemento grafico');
  }

  /*
   * Sovrascrive la struttura di default
   * 
   * @see propertyObject::structure()
   * @param integer $id
   * @return array
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
      'resize'=>false, 
      'preview'=>true, 
      'path'=>$base_path 
    ));

    return $structure;

  }

}
