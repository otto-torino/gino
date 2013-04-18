<?php
/**
 * \file class.pageEntry.php
 * Contiene la definizione ed implementazione della classe pageEntry.
 * 
 * @version 0.1
 * @copyright 2012 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */

/**
 * \ingroup page
 * Classe tipo model che rappresenta una pagina.
 *
 * @version 0.1
 * @copyright 2012 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */
class pageEntry extends propertyObject {

	private $_controller;

	protected static $_extension_img = array('jpg', 'jpeg', 'png');
	public static $tbl_entry = 'page_entry';
	public static $tbl_entry_tag = 'page_entry_tag';

	/**
	 * Costruttore
	 * 
	 * @param integer $id valore ID del record
	 * @param object $instance istanza del controller
	 */
	function __construct($id, $instance) {

		$this->_controller = $instance;
		$this->_tbl_data = self::$tbl_entry;

		$this->_fields_label = array(
			'category_id'=>_("Categoria"), 
			'author'=>_('Autore'),
			'creation_date'=>_('Data creazione'),
			'last_edit_date'=>_('Data ultima modifica'),
			'title'=>_("Titolo"),
			'slug'=>array(_("Slug"), _('utilizzato per creare un permalink alla risorsa')),
			'image'=>_('Immagine'),
			'text'=>_('Testo'),
			'tags'=>_('Tag'),
			'enable_comments'=>_('abilita commenti'),
			'published'=>_('Pubblicato'),
			'read'=>_('Visualizzazioni'), 
			'tpl_code'=>array(_("Template"), _("sovrascrive il template di default"))
		);

		parent::__construct($id);

		$this->_model_label = $this->id ? $this->title : '';
	}

	/**
	 * Rappresentazione testuale del modello 
	 * 
	 * @return string
	 */
	function __toString() {
		
		return $this->_model_label;
	}

	/**
	 * Sovrascrive la struttura di default
	 * 
	 * @see propertyObject::structure()
	 * @param integer $id
	 * @return array
	 */
	public function structure($id) {
		
		$structure = parent::structure($id);
		
		//$category = new category($this->_controller);

		$structure['category_id'] = new foreignKeyField(array(
			'name'=>'category_id', 
			'required'=>true,
			'label'=>$this->_fields_label['category_id'], 
			'lenght'=>3, 
			//'enum_data'=>$category->inputTreeArray(array('table'=>'page_category')), 
			'fkey_table'=>pageCategory::$_tbl_item, 
			'fkey_field'=>'name', 
			'fkey_order'=>'name', 
			'value'=>$this->category_id, 
			'table'=>$this->_tbl_data
		));
		
		$structure['published'] = new booleanField(array(
			'name'=>'published', 
			'required'=>true,
			'label'=>$this->_fields_label['published'], 
			'enum'=>array(1 => _('si'), 0 => _('no')), 
			'default'=>0,
			'value'=>$this->published, 
			'table'=>$this->_tbl_data 
		));

		$structure['enable_comments'] = new booleanField(array(
			'name'=>'enable_comments', 
			'required'=>true,
			'label'=>$this->_fields_label['enable_comments'], 
			'enum'=>array(1 => _('si'), 0 => _('no')), 
			'default'=>0, 
			'value'=>$this->enable_comments, 
			'table'=>$this->_tbl_data
		));

		$structure['creation_date'] = new datetimeField(array(
			'name'=>'creation_date', 
			'required'=>true,
			'label'=>$this->_fields_label['creation_date'], 
			'auto_now'=>false, 
			'auto_now_add'=>true, 
			'value'=>$this->creation_date 
		));

		$structure['last_edit_date'] = new datetimeField(array(
			'name'=>'last_edit_date', 
			'required'=>true,
			'label'=>$this->_fields_label['last_edit_date'], 
			'auto_now'=>true, 
			'auto_now_add'=>true, 
			'value'=>$this->last_edit_date 
		));

		$base_path = $this->_controller->getBasePath();
		$add_path = $this->_controller->getAddPath($this->id);

		$structure['image'] = new imageField(array(
			'name'=>'image', 
			'value'=>$this->image, 
			'label'=>$this->_fields_label['image'], 
			'lenght'=>100, 
			'extensions'=>self::$_extension_img, 
			'resize'=>false, 
			'path'=>$base_path, 
			'add_path'=>$add_path
		));

		return $structure;
	}

	/**
	 * Restituisce l'istanza pageEntry a partire dallo slug/ID fornito 
	 * 
	 * @param mixed $slug lo slug oppure il valore ID della pagina
	 * @param object $controller istanza del controller
	 * @access public
	 * @return istanza di pageEntry
	 */
	public static function getFromSlug($slug, $controller) {
	
		$res = null;

		$db = db::instance();
		
		if(preg_match('#^[0-9]$#', $slug))
		{
			$res = new pageEntry($slug, $controller);
		}
		else
		{
			$rows = $db->select('id', self::$tbl_entry, "slug='$slug'", null, array(0, 1));
			if(count($rows)) {
				$res = new pageEntry($rows[0]['id'], $controller);
			}
		}

		return $res;
	}

	/**
	 * Lista di valori ID di tag associati ad almeno una pagina 
	 * 
	 * @return array di id di tag
	 */
	public static function getAssociatedTags() {

		$res = array();

		$db = db::instance();
		$rows = $db->select('tag', self::$tbl_entry_tag, "entry IN (SELECT id FROM ".self::$tbl_entry." WHERE published='1')", null, null);
		if($rows and count($rows)) {
			foreach($rows as $row) {
				$res[] = $row['tag'];
			}
		}

		return $res;
	}

	/**
	 * Lista di tag con frequenza di associazione 
	 * 
	 * @return array associativo di tag id=>frequenza
	 */
	public static function getTagFrequency() {

		$res = array();

		$db = db::instance();
		$query = "SELECT tag, COUNT(entry) AS f FROM ".self::$tbl_entry_tag." WHERE entry IN (SELECT id FROM ".self::$tbl_entry." WHERE published='1') GROUP BY tag ORDER BY tag";
		$rows = $db->selectquery($query);
		if($rows and count($rows)) {
			foreach($rows as $row) {
				$res[$row['tag']] = $row['f'];
			}
		}

		return $res;
	}

	/**
	 * Tag @ref pageTag associati al post 
	 * 
	 * @return array di oggetti pageTag
	 */
	public function getTagObjects() {
		
		$res = array();
		$db = db::instance();
		$rows = $db->select('tag', self::$tbl_entry_tag, "entry='".$this->id."'", 'tag', null);
		if($rows and count($rows)) {
			foreach($rows as $row) {
				$res[] = new pageTag($row['tag'], $this->_controller);
			}
		}

		return $res;
	}

	/**
	 * Restituisce oggetti di tipo @ref pageEntry 
	 * 
	 * @param object $controller istanza del controller 
	 * @param array $options array associativo di opzioni
	 *   - @b published (boolean)
	 *   - @b tag (integer)
	 *   - @b category (integer)
	 *   - @b order (string)
	 *   - @b limit (string)
	 * @return array di istanze di tipo pageEntry
	 */
	public static function get($controller, $options = null) {

		$res = array();

		$published = gOpt('published', $options, true);
		$tag = gOpt('tag', $options, null);
		$category = gOpt('category', $options, null);
		$order = gOpt('order', $options, 'creation_date');
		$limit = gOpt('limit', $options, null);

		$db = db::instance();
		$selection = 'id';
		$table = self::$tbl_entry;
		$where_arr = array();
		if($published) {
			$where_arr[] = "published='1'";
		}
		if($category) {
			$where_arr[] = "category_id='$category'";
		}
		if($tag) {
			$where_arr[] = "id IN (SELECT entry FROM ".self::$tbl_entry_tag." WHERE tag='".$tag."')";
		}
		$where = implode(' AND ', $where_arr);

		$rows = $db->select($selection, $table, $where, $order, $limit);
		if(count($rows)) {
			foreach($rows as $row) {
				$res[] = new pageEntry($row['id'], $controller);
			}
		}

		return $res;
	}

	/**
	 * Restituisce il numero di oggetti pageEntry selezionati 
	 * 
	 * @param array $options array associativo di opzioni
	 *   - @b published (boolean)
	 *   - @b tag (integer)
	 *   - @b category (integer)
	 * @return numero di post
	 */
	public static function getCount($options = null) {

		$res = 0;

		$published = gOpt('published', $options, true);
		$tag = gOpt('tag', $options, null);
		$category = gOpt('category', $options, null);

		$db = db::instance();
		$selection = 'COUNT(id) AS tot';
		$table = self::$tbl_entry;
		$where_arr = array();
		if($published) {
			$where_arr[] = "published='1'";
		}
		if($category) {
			$where_arr[] = "category_id='$category'";
		}
		if($tag) {
			$where_arr[] = "id IN (SELECT entry FROM ".self::$tbl_entry_tag." WHERE tag='".$tag."')";
		}
		$where = implode(' AND ', $where_arr);

		$rows = $db->select($selection, $table, $where, null, null);
		if($rows and count($rows)) {
			$res = $rows[0]['tot'];
		}

		return $res;
	}

	/**
	 * Salva i tag associati al post 
	 * 
	 * @param mixed $tags array di id di tag
	 * @return il risultato dell'operazione
	 */
	public function saveTags($tags) {

		$db = db::instance();

		if(!count($tags)) {
			return true;
		}

		$query = "DELETE FROM ".self::$tbl_entry_tag." WHERE entry='".$this->id."'";
		$res = $db->actionquery($query);

		$inserts = array();
		foreach($tags as $tag) {
			$inserts[] = "('".$this->id."', '".$tag."')";
		}

		$query = "INSERT INTO ".self::$tbl_entry_tag." (entry, tag) VALUES ".implode(',', $inserts);
		return $db->actionquery($query);

	}

	/**
	 * Elimina l'oggetto 
	 * 
	 * @return il risultato dell'operazione
	 */
	public function delete() {

		// delete tags
		$db = db::instance();
		$query = "DELETE FROM ".self::$tbl_entry_tag." WHERE entry='".$this->id."'";

		$db->actionquery($query);

		blogComment::deleteFromEntry($this->_controller, $this->id);

		return parent::delete();

	}

	/**
	 * Path relativo dell'immagine associata 
	 * 
	 * @param object $controller istanza del controller
	 * @return path relativo dell'immagine
	 */
	public function imgPath($controller) {

		return $controller->getBasePath().'/'.$this->image;
	}
}

?>
