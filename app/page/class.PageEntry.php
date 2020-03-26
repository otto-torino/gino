<?php
/**
 * @file class.PageEntry.php
 * Contiene la definizione ed implementazione della classe Gino.App.Page.PageEntry.
 * 
 * @copyright 2013-2020 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */
namespace Gino\App\Page;

use \Gino\GTag;

/**
 * @brief Classe tipo Gino.Model che rappresenta una pagina
 *
 * @copyright 2013-2020 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
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
     * @return string, titolo
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
        	'default' => 0
        ));
		$columns['published'] = new \Gino\BooleanField(array(
            'name'=>'published',
            'label'=>_('Pubblicato'),
            'required'=>true,
        	'default' => 0
        ));
		$columns['social'] = new \Gino\BooleanField(array(
            'name'=>'social',
            'label'=>_('Condivisioni social'),
            'required'=>true,
        	'default' => 0
        ));
        $columns['private'] = new \Gino\BooleanField(array(
        	'name'=>'private',
        	'label'=>array(_("Privata"), _("pagina visualizzabile da utenti con il relativo permesso")),
        	'required'=>true,
        	'default' => 0
        ));
        /*
        $ids = \Gino\App\Auth\User::getUsersWithDefinedPermissions(array('can_view_private'), $controller);
        if(count($ids)) {
        	$where_mf = "active='1' AND id IN (".implode(',', $ids).")";
        }
        else {
        	$where_mf = "id=NULL";
        }*/
        
        $columns['users'] = new \Gino\MulticheckField(array(
        	'name' => 'users',
        	'label' => _("Utenti che possono visualizzare la pagina"),
        	'max_lenght' => 255,
        	'refmodel' => '\Gino\App\Auth\User',
        	'refmodel_where' => "active='1'",
        	'refmodel_order' => "lastname ASC, firstname",
        ));
        $columns['view_last_edit_date'] = new \Gino\BooleanField(array(
        	'name' => 'view_last_edit_date',
        	'label' => _("Visualizzare la data di aggiornamento della pagina"),
        	'required' => true,
        	'default' => 0
        ));
        
        $ids = \Gino\App\Auth\User::getUsersWithDefinedPermissions(array('can_edit_single_page'), $controller);
        if(count($ids)) {
        	$where_mf = "active='1' AND id IN (".implode(',', $ids).")";
        }
        else {
        	$where_mf = "id=NULL";
        }
        
        $columns['users_edit'] = new \Gino\MulticheckField(array(
        	'name' => 'users_edit',
        	'label' => array(_("Utenti che possono editare la pagina"), _("elenco degli utenti associati al permesso di redazione dei contenuti di singole pagine")),
        	'max_lenght' => 255,
        	'refmodel' => '\Gino\App\Auth\User',
        	'refmodel_where' => $where_mf,
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
        	'label' => array(_("Template pagina intera"), _("richiamato da URL (sovrascrive il template di default)")),
        	'required' => false,
            'footnote' => page::explanationTemplate()
        ));
        $columns['box_tpl_code'] = new \Gino\TextField(array(
        	'name'=>'box_tpl_code',
        	'label' => array(_("Template box"), _("richiamato nel template del layout (sovrascrive il template di default)")),
        	'required' => false
        ));

        return $columns;
    }

    /**
     * @brief Restituisce l'istanza Gino.App.Page.PageEntry a partire dallo slug/ID fornito
     *
     * @param mixed $slug lo slug oppure il valore ID della pagina
     * @param null $controller per compatibilità con il metodo Gino.Model::getFromSlug
     * @return Gino.App.Page.PageEntry
     */
    public static function getFromSlug($slug, $controller = null) {

        $res = null;

        $db = \Gino\Db::instance();

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
     * @brief Elenco delle pagine che possono essere modificate da un utente
     * 
     * @desc Gli utenti devono essere associati al permesso can_edit_single_page
     * @param integer $user_id
     * @return array(pages id)
     */
    public static function getEditEntry($user_id) {
    	
    	$db = \Gino\Db::instance();
    	
    	$items = array();
    	
    	$rows = $db->select('id', self::$table, "users_edit REGEXP '[[:<:]]".$user_id."[[:>:]]'");
    	if(count($rows))
    	{
    		foreach ($rows AS $row)
    		{
    			$items[] = (int) $row['id'];
    		}
    	}
    	return $items;
    }

    /**
     * @brief Url relativo pagina
     * @return string
     */
    public function getUrl() {

        return $this->_registry->router->link('page', 'view', array('id'=>$this->slug));
    }

    /**
     * @brief Definisce le condizioni di accesso alle pagine private
     *
     * @param integer $user_id valore ID dell'utente
     * @param boolean $view_private indica se l'utente ha associato il permesso @a can_view_private
     * @return string, where clause
     */
    private static function viewPrivatePages($user_id, $view_private) {

        $where = '';

        if($user_id == 0)    // condizione di non autenticazione
        {
            $where = "(users IS NULL OR users='') AND private='0'";
        }
        elseif($user_id && is_int($user_id))
        {
            // condizione campo users non vuoto
            $w1 = "users IS NOT NULL AND users!='' AND users REGEXP '[[:<:]]".$user_id."[[:>:]]'";

            // condizione campo users vuoto, da abbinare all'accesso privato se impostato 
            $w2 = "users IS NULL OR users=''";

            if(is_bool($view_private))
            {
                if(!$view_private)
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
        elseif(is_bool($view_private)) {
            if(!$view_private) {
                $where = "private='0'";
            }
        }

        return $where;
    }

    /**
     * @brief Restituisce oggetti di tipo @ref Gino.App.Page.PageEntry 
     * 
     * @param array $options array associativo di opzioni
     *   - @b published (boolean)
     *   - @b tag (integer)
     *   - @b category (integer)
     *   - @b user_id (integer): valore ID dell'utente in sessione (per l'accesso limitato a specifici utenti)
     *   - @b view_private (boolean): identifica se l'utente in sessione ha il permesso @a can_view_privat
     *   - @b where (string): condizioni personalizzate
     *   - @b order (string)
     *   - @b limit (string)
     *   - @b debug (boolean): default false
     * @return array di istanze di tipo Gino.App.Page.PageEntry
     */
    public static function get($options = null) {

        $res = array();

        $published = \Gino\gOpt('published', $options, true);
        $tag = \Gino\gOpt('tag', $options, null);
        $category = \Gino\gOpt('category', $options, null);
        $user_id = \Gino\gOpt('user_id', $options, null);
        $view_private = \Gino\gOpt('view_private', $options, null);
        $where_opt = \Gino\gOpt('where', $options, null);
        
        $order = \Gino\gOpt('order', $options, 'creation_date');
        $limit = \Gino\gOpt('limit', $options, null);
        $debug = \Gino\gOpt('debug', $options, false);

        $db = \Gino\Db::instance();
         
        $where = self::setConditionWhere(array(
        	'published' => $published,
        	'category' => $category,
        	'tag' => $tag,
        	'user_id' => $user_id,
        	'view_private' => $view_private,
        	'custom' => $where_opt,
        ));
        
        $rows = $db->select('id', self::$table, $where, array('order' => $order, 'limit' => $limit, 'debug' => $debug));
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
     *   - @b user_id (integer): valore ID dell'utente in sessione (per l'accesso limitato a specifici utenti)
     *   - @b view_private (boolean): identifica se l'utente in sessione ha il permesso @a can_view_private
     * @return integer, numero di pagine
     */
    public static function getCount($options = null) {

        $res = 0;

        $published = \Gino\gOpt('published', $options, true);
        $tag = \Gino\gOpt('tag', $options, null);
        $category = \Gino\gOpt('category', $options, null);
		$user_id = \Gino\gOpt('user_id', $options, null);
        $view_private = \Gino\gOpt('view_private', $options, null);

        $db = \Gino\Db::instance();
        
        $where = self::setConditionWhere(array(
        	'published' => $published,
        	'category' => $category,
        	'tag' => $tag, 
        	'user_id' => $user_id,
        	'view_private' => $view_private,
        ));

        $rows = $db->select('COUNT(id) AS tot', self::$table, $where);
        if($rows and count($rows)) {
            $res = $rows[0]['tot'];
        }

        return $res;
    }
    
    /**
     * @brief Imposta le condizioni di ricerca dei record
     * 
     * @param array $options array associativo di opzioni
     *   - @b published (boolean)
     *   - @b tag (integer)
     *   - @b category (integer)
     *   - @b text (string): fa riferimento ai campi title e text
     *   - @b user_id (integer): valore ID dell'utente in sessione (per l'accesso limitato a specifici utenti)
     *   - @b view_private (boolean): identifica se l'utente in sessione ha il permesso @a can_view_private
     *   - @b custom (string): condizioni personalizzate
     * @return string
     */
    public static function setConditionWhere($options = null) {

    	$published = \Gino\gOpt('published', $options, true);
    	$tag = \Gino\gOpt('tag', $options, null);
    	$category = \Gino\gOpt('category', $options, null);
    	$text = \Gino\gOpt('text', $options, null);
    	$user_id = \Gino\gOpt('user_id', $options, null);
        $view_private = \Gino\gOpt('view_private', $options, null);
        $custom = \Gino\gOpt('custom', $options, null);
    	
    	$controller = new page();
    	$where = array();
    	
    	if($published) {
    		$where[] = "published='1'";
    	}
    	if($category) {
    		$where[] = "category_id='$category'";
    	}
    	if($tag) {
    		$where[] = \Gino\GTag::whereCondition($controller, $tag);
    	}
    	if($text) {
    		$where[] = "(title LIKE '%".$text."%' OR text LIKE '%".$text."%')";
    	}
    	
    	if($custom) {
    		$conditions = $custom;
    	}
    	else {
    		$conditions = implode(' AND ', $where);
    	}
    	
    	$condition_private = self::viewPrivatePages($user_id, $view_private);
    	
    	if($condition_private && $conditions) {
    		$conditions = $conditions." AND ".$condition_private;
    	}
    	
    	return $conditions;
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
     * @return string, percorso relativo dell'immagine
     */
    public function imgPath() {

        return $this->_controller->getBasePath('rel').$this->id.'/'.$this->image;
    }
    
    /**
     * @brief Elimina la directory della pagina
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
    
    /**
     * {@inheritDoc}
     * @see \Gino\Model::displayItem()
     */
    public function displayItem() {
        
        if($this->published == 1) {
            return true;
        }
        else {
            return false;
        }
    }
}

PageEntry::$columns=PageEntry::columns();