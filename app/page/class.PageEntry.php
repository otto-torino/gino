<?php
/**
 * @file class.PageEntry.php
 * Contiene la definizione ed implementazione della classe Gino.App.Page.PageEntry.
 * 
 * @copyright 2013-2015 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */
namespace Gino\App\Page;

use Gino\GTag;

/**
 * @brief Classe tipo Gino.Model che rappresenta una pagina
 *
 * @copyright 2013-2015 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */
class PageEntry extends \Gino\Model {

    public static $table = 'page_entry';
    public static $columns;
    
    protected static $_extension_img = array('jpg', 'jpeg', 'png');

    /**
     * Costruttore
     * 
     * @param integer $id valore ID del record
     * @param object $instance istanza del controller
     */
    function __construct($id) {

        $this->_tbl_data = self::$table;
        
        $this->_controller = new page();
        
        parent::__construct($id);
        
        $this->_model_label = _("Pagina");
    }

    /**
     * @brief Rappresentazione a stringa dell'oggetto
     * @return titolo
     */
    function __toString() {

        return (string) $this->title;
    }
    
    /**
     * @see Gino.Model::properties()
     */
    protected static function properties($model, $controller) {
    	
    	$base_path = $controller->getBasePath();
        $add_path = $controller->getAddPath($model->id);
    	
        $property['image'] = array(
    		'path'=>$base_path,
    		'add_path'=>$add_path
    	);
    	
    	return $property;
    }

    /**
     * Struttura dei campi della tabella di un modello
     *
     * @return array
     */
    public static function columns() {

        $controller = new page();
        
        $columns['id'] = new \Gino\IntegerField(array(
			'name'=>'id',
			'primary_key'=>true,
			'auto_increment'=>true,
        	'max_lenght'=>11,
		));
        $columns['category_id'] = new \Gino\ForeignKeyField(array(
            'name'=>'category_id',
            'label'=>_("Categoria"),
            'required'=>false,
        	'foreign'=>'\Gino\App\Page\PageCategory',
        	'foreign_order'=>'name ASC',
        	'add_related' => true,
        	'add_related_url' => $controller->linkAdmin(array(), "block=ctg&insert=1"),
        ));
		$columns['author'] = new \Gino\ForeignKeyField(array(
			'name' => 'author',
			'label' => _("Autore"),
			'required' => true,
			'foreign' => '\Gino\App\Auth\User',
			'foreign_order' => 'lastname ASC, firstname ASC',
			'add_related' => false,
		));
		$columns['creation_date'] = new \Gino\DatetimeField(array(
			'name' => 'creation_date',
			'label' => _('Inserimento'),
			'required' => true,
			'auto_now' => false,
			'auto_now_add' => true,
		));
		$columns['last_edit_date'] = new \Gino\DatetimeField(array(
			'name'=>'last_edit_date',
			'label'=>_('Ultima modifica'),
			'required'=>true,
			'auto_now'=>true,
			'auto_now_add'=>true,
		));
		$columns['title'] = new \Gino\CharField(array(
			'name'=>'title',
			'label'=>_("Titolo"),
			'required'=>true,
			'max_lenght'=>200,
		));
        $columns['slug'] = new \Gino\SlugField(array(
            'name'=>'slug',
        	'unique_key'=>true,
            'label'=>array(_("Slug"), _('utilizzato per creare un permalink alla risorsa')),
            'required'=>true,
        	'max_lenght'=>200,
        	'autofill'=>'title',
        ));
        $columns['image'] = new \Gino\ImageField(array(
        	'name'=>'image',
        	'label'=>_("Immagine"),
        	'max_lenght'=>100,
        	'extensions'=>self::$_extension_img,
        	'resize'=>false,
        	'path'=>null,
        	'add_path'=>null
        ));
        $columns['url_image'] = new \Gino\CharField(array(
        	'name'=>'url_image',
        	'label'=>array(_("Collegamento sull'immagine"), _("indirizzo URL")),
        	'required'=>false,
        	'max_lenght'=>200,
        ));
        $columns['text'] = new \Gino\TextField(array(
        	'name' => 'text',
        	'label' => _("Testo"),
        	'required' => true
        ));
        $columns['tags'] = new \Gino\TagField(array(
        	'name' => 'tags',
        	'label' => array(_('Tag'), _("elenco separato da virgola")),
        	'required' => false,
        	'max_lenght' => 255,
        	'model_controller_class' => 'page',
        	'model_controller_instance' => 0,
        ));
        $columns['enable_comments'] = new \Gino\BooleanField(array(
        	'name'=>'enable_comments',
        	'label'=>_('Abilita commenti'),
        	'required'=>true,
        ));
		$columns['published'] = new \Gino\BooleanField(array(
            'name'=>'published',
            'label'=>_('Pubblicato'),
            'required'=>true,
        ));
		$columns['social'] = new \Gino\BooleanField(array(
            'name'=>'social',
            'label'=>_('Condivisioni social'),
            'required'=>true,
        ));
        $columns['private'] = new \Gino\BooleanField(array(
        	'name'=>'private',
        	'label'=>array(_("Privata"), _("pagina visualizzabile da utenti con il relativo permesso")),
        	'required'=>true,
        ));
        $columns['users'] = new \Gino\MulticheckField(array(
			'name' => 'users', 
        	'label' => array(_("Utenti che possono visualizzare la pagina"), _("sovrascrive l'impostazione precedente")),
        	'required' => false,
        	'max_lenght' => 255,
			'refmodel' => '\Gino\App\Auth\User', 
			'refmodel_where' => "active='1'", 
			'refmodel_order' => "lastname ASC, firstname", 
		));
        $columns['read'] = new \Gino\IntegerField(array(
        	'name'=>'read',
        	'label'=>_('Visualizzazioni'),
        	'required'=>true,
        	'default'=>0,
        ));
        $columns['tpl_code'] = new \Gino\TextField(array(
        	'name'=>'tpl_code',
        	'label' => array(_("Template pagina intera"), _("richiamato da URL (sovrascrive il template di default)")."<br />".page::explanationTemplate()),
        	'required'=>false
        ));
        $columns['box_tpl_code'] = new \Gino\TextField(array(
        	'name'=>'box_tpl_code',
        	'label' => array(_("Template box"), _("richiamato nel template del layout (sovrascrive il template di default)")),
        	'required'=>false
        ));

        return $columns;
    }

    /**
     * @brief Restituisce l'istanza Gino.App.Page.PageEntry a partire dallo slug/ID fornito
     *
     * @param mixed $slug lo slug oppure il valore ID della pagina
     * @param null $controller per compatibilità con il metodo Gino.Model::getFromSlug
     * @return istanza di Gino.App.Page.PageEntry
     */
    public static function getFromSlug($slug, $controller = null) {

        $res = null;

        $db = \Gino\db::instance();

        if(preg_match('#^[0-9]+$#', $slug))
        {
            $res = new PageEntry($slug);
        }
        else
        {
            $rows = $db->select('id', self::$table, "slug='$slug'", array('limit'=>array(0, 1)));
            if(count($rows)) {
                $res = new PageEntry($rows[0]['id']);
            }
        }

        return $res;
    }

    /**
     * @brief Url relativo pagina
     * @return url
     */
    public function getUrl() {

        return $this->_registry->router->link('page', 'view', array('id'=>$this->slug));
    }

    /**
     * @brief Definisce le condizioni di accesso a una pagina integrando le condizioni del WHERE
     *
     * @param integer $access_user valore ID dell'utente
     * @param boolean $access_private indica se l'utente appartiene al gruppo "utenti pagine private"
     * @return where clause
     */
    private static function accessWhere($access_user, $access_private) {

        $where = '';

        if($access_user == 0)    // condizione di non autenticazione
        {
            $where = "(users IS NULL OR users='') AND private='0'";
        }
        elseif($access_user && is_int($access_user))
        {
            // condizione campo users non vuoto
            $w1 = "users IS NOT NULL AND users!='' AND users REGEXP '[[:<:]]".$access_user."[[:>:]]'";

            // condizione campo users vuoto, da abbinare all'accesso privato se impostato 
            $w2 = "users IS NULL OR users=''";

            if(is_bool($access_private))
            {
                if(!$access_private)
                {
                    $w3 = "private='0'";

                    $where = "(($w1) OR ($w2 AND $w3)";
                }
                else
                {
                    $where = "(($w1) OR ($w2)";
                }
            }
            else $where = "(($w1) OR ($w2)";
        }
        elseif(is_bool($access_private)) {
            if(!$access_private) {
                $where = "private='0'";
            }
        }

        return $where;
    }

    /**
     * @brief Restituisce oggetti di tipo @ref Gino.App.Page.PageEntry 
     * 
     * @see accessWhere()
     * @param object $controller istanza del controller 
     * @param array $options array associativo di opzioni
     *   - @b published (boolean)
     *   - @b tag (integer)
     *   - @b category (integer)
     *   - @b order (string)
     *   - @b limit (string)
     *   - @b access_user (integer): valore ID dell'utente in sessione (per l'accesso limitato a specifici utenti)
     *   - @b access_private (boolean): identifica se l'utente in sessione appartiene al gruppo che può accedere alle pagine private
     * @return array di istanze di tipo Gino.App.Page.PageEntry
     */
    public static function get($options = null) {

        $res = array();

        $published = \Gino\gOpt('published', $options, true);
        $tag = \Gino\gOpt('tag', $options, null);
        $category = \Gino\gOpt('category', $options, null);
        $order = \Gino\gOpt('order', $options, 'creation_date');
        $limit = \Gino\gOpt('limit', $options, null);
        $where_opt = \Gino\gOpt('where', $options, null);

        $access_user = \Gino\gOpt('access_user', $options, null);
        $access_private = \Gino\gOpt('access_private', $options, null);

        $db = \Gino\db::instance();
        $selection = 'id';
        $table = self::$table;
        $where_arr = array();
        if($published) {
            $where_arr[] = "published='1'";
        }
        if($category) {
            $where_arr[] = "category_id='$category'";
        }
        if($tag) {
            $where_arr[] = "id IN (SELECT entry FROM ".self::$table_tag." WHERE tag='".$tag."')";
        }

        $where = implode(' AND ', $where_arr);
        if($where_opt) {
            $where = implode(' AND ', array($where_opt));
        }

        $where_add = self::accessWhere($access_user, $access_private);

        if($where && $where_add)
            $where = $where." AND ".$where_add;
        elseif(!$where && $where_add)
            $where = $where_add;

        $rows = $db->select($selection, $table, $where, array('order'=>$order, 'limit'=>$limit));
        if(count($rows)) {
            foreach($rows as $row) {
                $res[] = new PageEntry($row['id']);
            }
        }

        return $res;
    }

    /**
     * @brief Restituisce il numero di oggetti Gino.App.Page.PageEntry selezionati 
     * 
     * @param array $options array associativo di opzioni
     *   - @b published (boolean)
     *   - @b tag (integer)
     *   - @b category (integer)
     *   - @b access_user (integer): valore ID dell'utente in sessione (per l'accesso limitato a specifici utenti)
     *   - @b access_private (boolean): identifica se l'utente in sessione appartiene al gruppo che può accedere alle pagine private
     * @return numero di pagine
     */
    public static function getCount($options = null) {

        $res = 0;

        $published = \Gino\gOpt('published', $options, true);
        $tag = \Gino\gOpt('tag', $options, null);
        $category = \Gino\gOpt('category', $options, null);

        $access_user = \Gino\gOpt('access_user', $options, null);
        $access_private = \Gino\gOpt('access_private', $options, null);

        $db = \Gino\db::instance();
        $selection = 'COUNT(id) AS tot';
        $table = self::$table;
        $where_arr = array();
        if($published) {
            $where_arr[] = "published='1'";
        }
        if($category) {
            $where_arr[] = "category_id='$category'";
        }
        if($tag) {
            $where_arr[] = "id IN (SELECT entry FROM ".self::$table_tag." WHERE tag='".$tag."')";
        }

        $where = implode(' AND ', $where_arr);
        $where_add = self::accessWhere($access_user, $access_private);

        if($where && $where_add)
            $where = $where." AND ".$where_add;
        elseif(!$where && $where_add)
            $where = $where_add;

        $rows = $db->select($selection, $table, $where);
        if($rows and count($rows)) {
            $res = $rows[0]['tot'];
        }

        return $res;
    }
    
    /**
     * @see Gino.Model::save()
     * @description Sovrascrive il metodo di Gino.Model per salvare l'autore della pagina
     */
    public function save($options=array())
    {
        $session = \Gino\session::instance();
        
        $this->author = $session->user_id;
        
        return parent::save($options);
    }

    /**
     * @see Gino.Model::delete()
     */
    public function delete() {

        PageComment::deleteFromEntry($this->id);
        
        \Gino\GTag::deleteTaggedItem($this->_controller->getClassName(), $this->_controller->getInstance(), get_name_class($this), $this->id);
        
        $result = parent::delete();
        
        if($result) $this->deleteDir();
        
        return $result;
    }

    /**
     * @brief Path relativo dell'immagine associata 
     * @return path relativo dell'immagine
     */
    public function imgPath() {

        return $this->_controller->getBasePath('rel').$this->id.'/'.$this->image;
    }
    
    /**
     * Elimina la directory della pagina
     * 
     * @throws \Exception
     * @return boolean
     */
    private function deleteDir() {
    	
    	$dirname = $this->_controller->getBasePath().$this->id;
    	if(is_dir($dirname)) {
    		if(!rmdir($dirname))
    			throw new \Exception(sprintf(_("La directory %s non è stata eliminata"), $dirname));
    	}
    	return true;
    }
}

PageEntry::$columns=PageEntry::columns();