<?php
/**
 * @file class.Item.php
 * @brief Contiene la definizione ed implementazione della classe Gino.App.Post.Item
 */

namespace Gino\App\Post;

use \Gino\ManyToManyField;
use \Gino\BooleanField;
use \Gino\TagField;
use \Gino\DatetimeField;
use \Gino\ImageField;
use \Gino\FileField;
use \Gino\SlugField;
use \Gino\Db;
use \Gino\GTag;

/**
 * \ingroup post
 * @brief Classe tipo Gino.Model che rappresenta un singolo post
 */
class Item extends \Gino\Model {

    public static $table = 'post_item';
    public static $table_ctgs = 'post_item_category';
    public static $columns;
    
    protected static $_extension_img = array('jpg', 'jpeg', 'png');
    protected static $_extension_attachment = array('pdf', 'doc', 'xdoc', 'odt', 'xls', 'csv', 'txt');

    /**
     * @brief Costruttore
     * 
     * @param integer $id valore ID del record
     * @param object $controller
     */
    function __construct($id, $controller) {

        $this->_controller = $controller;
        $this->_tbl_data = self::$table;

        parent::__construct($id);

        $this->_model_label = _('Post');
    }

    /**
     * @brief Rappresentazione a stringa dell'oggetto
     * @return string, titolo post
     */
    function __toString() {
        return (string) $this->title;
    }
    
    /**
     * @see Gino.Model::properties()
     */
    protected static function properties($model, $controller) {
    	
    	$instance = $controller->getInstance();
    	$base_path = $controller->getBaseAbsPath();
    	
    	$property['tags'] = array(
			'model_controller_instance' => $instance,
		);
    	$property['img'] = array(
    		'path' => $base_path.OS.'img',
    		'width' => $controller->getImageWidth()
    	);
    	$property['attachment'] = array(
    		'path' => $base_path.OS.'attachment'
    	);
    	$property['categories'] = array(
    		'm2m_where' => 'instance=\''.$instance.'\'',
    		'm2m_controller' => $controller,
    		'add_related_url' => $controller->linkAdmin(array(), 'block=ctg&insert=1')
    	);
    	
    	return $property;
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
			'max_lenght'=>11,
		));
		$columns['instance'] = new \Gino\IntegerField(array(
			'name'=>'instance',
			'required'=>true,
			'max_lenght'=>11,
		));
		$columns['insertion_date'] = new \Gino\DatetimeField(array(
			'name' => 'insertion_date',
			'label' => _('Data inserimento'),
			'required' => true,
			'auto_now' => FALSE,
			'auto_now_add' => TRUE
		));
		$columns['last_edit_date'] = new \Gino\DatetimeField(array(
			'name' => 'last_edit_date',
			'label' => _('Data ultima modifica'),
			'required'=>true,
			'auto_now' => TRUE,
			'auto_now_add' => TRUE
		));
		$columns['date'] = new \Gino\DateField(array(
			'name' => 'date',
			'label' => _('Data'),
			'required' => TRUE,
		));
		$columns['title'] = new \Gino\CharField(array(
			'name'=>'title',
			'label'=>_("Titolo"),
			'required'=>true,
			'max_lenght'=>200,
		));
		$columns['slug'] = new \Gino\SlugField(array(
			'name' => 'slug',
			'unique_key' => true,
			'label' => array(_("Slug"), _('utilizzato per creare un permalink alla risorsa')),
			'required' => true,
			'max_lenght' => 200,
			'autofill' => array('date', 'title'),
		));
		$columns['text'] = new \Gino\TextField(array(
			'name' => 'text',
			'label' => _("Testo"),
			'required' => false
		));
		$columns['tags'] = new \Gino\TagField(array(
			'name' => 'tags',
			'label' => array(_('Tag'), _("elenco separato da virgola")),
			'required' => false,
			'max_lenght' => 255,
			'model_controller_class' => 'post',
			'model_controller_instance' => null,
		));
		$columns['img'] = new \Gino\ImageField(array(
			'name' => 'img',
			'label' => _('Immagine'),
			'extensions' => self::$_extension_img,
			'path' => null,
			'resize' => TRUE,
			'thumb' => FALSE,
			'width' => null
		));
		$columns['attachment'] = new \Gino\FileField(array(
			'name' => 'attachment',
			'label' => _('Allegato'),
			'extensions' => self::$_extension_attachment,
			'path' => null,
			'check_type' => FALSE
		));
		$columns['private'] = new \Gino\BooleanField(array(
			'name' => 'private',
			'label' => array(_('Privata'), _('i post privati sono visualizzabili solamente dagli utenti che hanno il permesso \'visualizzazione post privati\'')),
			'required' => true,
			'default' => 0
		));
		$columns['social'] = new \Gino\BooleanField(array(
			'name' => 'social',
			'label' => _('Condivisione social networks'),
			'required' => true,
			'default' => 0
		));
		$columns['slideshow'] = new \Gino\BooleanField(array(
			'name' => 'slideshow',
			'label' => _('Post da mostrare nello slideshow'),
			'required' => true,
			'default' => 0
		));
		$columns['published'] = new \Gino\BooleanField(array(
			'name' => 'published',
			'label' => _('Pubblicata'),
			'required' => true,
			'default' => 0
		));
        $columns['categories'] = new \Gino\ManyToManyField(array(
            'name' => 'categories',
            'label' => _("Categorie"),
            'm2m' => '\Gino\App\Post\Category',
            'm2m_where' => null,
            'm2m_controller' => null,
            'join_table' => self::$table_ctgs,
            'add_related' => TRUE,
            'add_related_url' => null
        ));

        return $columns;
    }

    /**
     * @brief Lista di oggetti categoria associati al post
     * @return array di istanze di Gino.App.Post.Category
     */
    public function objCategories() {

        $res = array();
        foreach($this->categories as $ctgid) {
            $res[] = new Category($ctgid, $this->_controller);
        }

        return $res;
    }

    /**
     * @brief Restituisce il numero di record che soddisfano le condizioni date
     *
     * @param \Gino\App\Post\post $controller istanza del controller Gino.App.Post.post
     * @param array $options opzioni per la definizione delle condizioni della query (@see setConditionWhere())
     * @return integer
     */
    public static function getCount($controller, $options = null) {

        $db = Db::instance();
        
        $where = self::setConditionWhere($controller, $options);
        
        return $db->getNumRecords(self::$table, $where);
    }
    
    /**
     * @brief Imposta le condizioni di ricerca dei record
     * @param object $controller istanza del controller Gino.App.Post.post
     * @param array $options array associativo di opzioni
     *   - @b private (boolean)
     *   - @b published (boolean)
     *   - @b slideshow (boolean)
     *   - @b ctg (integer)
     *   - @b tag (string)
     *   - @b text (string): fa riferimento ai campi title e text
     *   - @b date_from (string): data di inizio intervallo di ricerca
     *   - @b date_to (string): data di fine intervallo di ricerca
     *   - @b remove_id (array): elenco valori id da non selezionare
     * @return string
     */
    public static function setConditionWhere($controller, $options = null) {
    	
    	$private = \Gino\gOpt('private', $options, FALSE);
    	$published = \Gino\gOpt('published', $options, TRUE);
    	$slideshow = \Gino\gOpt('slideshow', $options, False);
    	$ctg = \Gino\gOpt('ctg', $options, null);
    	$tag = \Gino\gOpt('tag', $options, null);
    	$text = \Gino\gOpt('text', $options, null);
    	$date_from = \Gino\gOpt('date_from', $options, null);
    	$date_to = \Gino\gOpt('date_to', $options, null);
    	$remove_id = \Gino\gOpt('remove_id', $options, null);
    	
    	$where = array("instance='".$controller->getInstance()."'");
    	
    	if(is_bool($private)) {
    		$where[] = "private = '".(int) $private."'";
    	}
    	if(is_bool($published)) {
    		$where[] = "published = '".(int) $published."'";
    	}
    	if(is_bool($slideshow)) {
    	    $where[] = "slideshow = '".(int) $slideshow."'";
    	}
    	if($ctg) {
    		$where[] = "id IN (SELECT article_id FROM ".self::$table_ctgs." WHERE category_id='".$ctg."')";
    	}
    	if($tag) {
    		$where[] = \Gino\GTag::whereCondition($controller, $tag);
    	}
    	if($text) {
    		$where[] = "(title LIKE '%".$text."%' OR text LIKE '%".$text."%')";
    	}
    	if($date_from) {
    		$where[] = "date >= '".\Gino\dateToDbDate($date_from)."'";
    	}
    	if($date_to) {
    		$where[] = "date <= '".\Gino\dateToDbDate($date_to)."'";
    	}
    	if(is_array($remove_id) && count($remove_id)) {
    		$where[] = "id NOT IN (".implode(',', $remove_id).")";
    	}
    	
    	return implode(' AND ', $where);
    }

    /**
     * @brief Url relativo al dettaglio del post
     * @return string
     */
    public function getUrl() {

        return $this->_controller->link($this->_controller->getInstanceName(), 'detail', array('id' => $this->slug));
    }

    /**
     * @brief Path relativo dell'immagine associata
     * @return string, path relativo dell'immagine
     */
    public function getImgPath() {

        return $this->_controller->getBasePath().'/img/'.$this->img;
    }

    /**
     * @brief Path relativo dell'allegato
     * @return string
     */
    public function getAttachmentPath() {

        return $this->_controller->getBasePath().'/attachment/'.$this->attachment;
    }

    /**
     * @brief Path relativo al download dell'allegato
     * @return string, path relativo
     */
    public function attachmentDownloadUrl() {

        return $this->_controller->link($this->_controller->getInstanceName(), 'download', array('id' => $this->id));
    }
    
    /**
     * Dimensioni immagine
     * @return array ('width' => WIDTH, 'height' => HEIGHT)
     */
    public function getSize() {
    	
    	list($width, $height, $type, $attr) = getimagesize(\Gino\absolutePath($this->getImgPath()));
    	return array('width' => $width, 'height' => $height);
    }
    
    /**
     * @brief Elenco delle categorie associate a un record
     * 
     * @return \Gino\App\Post\Category[]
     */
    public function getCategoriesName() {
    	
    	$items = array();
    	if(count($this->categories)) {
    		foreach ($this->categories AS $ctg_id) {
    			
    		    $items[] = new Category($ctg_id, $this->_controller);
    		}
    	}
    	return $items;
    }
    
    /**
     * @brief Elenco delle categorie associate a un record; ogni categoria è un collegamento a una pagina che mostra i record
     * di quella categoria
     *
     * @return array
     */
    public function getCategoriesLink() {
        
        $items = array();
        if(count($this->categories)) {
            foreach ($this->categories AS $ctg_id) {
                
                $ctg = new Category($ctg_id, $this->_controller);
                
                $url = $this->_controller->link($this->_controller->getInstanceName(), 'archive', array('ctg' => $ctg->slug));
                $link = "<a href=\"$url\">".\Gino\htmlChars($ctg->name)."</a>";
                $items[] = $link;
            }
        }
        return $items;
    }
    
    /**
     * @brief Data in formato iso 8601
     *
     * @return string, data iso 8601
     */
    public function dateIso() {
    	
        $datetime = new \Datetime($this->date);
        return $datetime->format('c');
    }
    
    /**
     * @see Gino.Model::delete()
     */
    public function delete() {
    	
    	\Gino\GTag::deleteTaggedItem($this->_controller->getClassName(), $this->_controller->getInstance(), get_name_class($this), $this->id);
    	
    	return parent::delete();
    }
}

Item::$columns=Item::columns();
