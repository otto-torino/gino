<?php
/**
 * @file class.PageEntry.php
 * Contiene la definizione ed implementazione della classe Gino.App.Page.PageEntry.
 * 
 * @copyright 2013-2014 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */
namespace Gino\App\Page;

/**
 * @brief Classe tipo Gino.Model che rappresenta una pagina
 *
 * @copyright 2013-2014 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */
class PageEntry extends \Gino\Model {

    protected static $_extension_img = array('jpg', 'jpeg', 'png');
    public static $table = 'page_entry';

    /**
     * Costruttore
     * 
     * @param integer $id valore ID del record
     * @param object $instance istanza del controller
     */
    function __construct($id) {

        $this->_controller = new page();
        $this->_tbl_data = self::$table;

        $this->_fields_label = array(
            'category_id'=>_("Categoria"), 
            'author'=>_('Autore'),
            'creation_date'=>_('Inserimento'),
            'last_edit_date'=>_('Ultima modifica'),
            'title'=>_("Titolo"),
            'slug'=>array(_("Slug"), _('utilizzato per creare un permalink alla risorsa')),
            'image'=>_('Immagine'),
            'url_image'=>array(_("Collegamento sull'immagine"), _("indirizzo URL")),
            'text'=>_('Testo'),
            'tags'=>array(_('Tag'), _("elenco separato da virgola")),
            'enable_comments'=>_('Abilita commenti'),
            'published'=>_('Pubblicato'), 
            'social'=>_('Condivisioni social'),
            'private'=>array(_("Privata"), _("pagina visualizzabile da utenti con il relativo permesso")),
            'users'=>array(_("Utenti che possono visualizzare la pagina"), _("sovrascrive l'impostazione precedente")),  
            'read'=>_('Visualizzazioni'), 
            'tpl_code'=>array(_("Template pagina intera"), _("richiamato da URL (sovrascrive il template di default)")."<br />".page::explanationTemplate()), 
            'box_tpl_code'=>array(_("Template box"), _("richiamato nel template del layout (sovrascrive il template di default)"))
        );

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
     * @brief Sovrascrive la struttura di default
     *
     * @see Gino.Model::structure()
     * @param integer $id
     * @return array, struttura
     */
    public function structure($id) {

        $structure = parent::structure($id);

        $structure['category_id'] = new \Gino\ForeignKeyField(array(
            'name'=>'category_id',
            'model'=>$this,
            'required'=>false,
            'foreign'=>'\Gino\App\Page\PageCategory',
            'foreign_order'=>'name ASC',
            'add_related' => true,
            'add_related_url' => $this->_controller->linkAdmin(array(), "block=ctg&insert=1")
        ));

        $structure['slug'] = new \Gino\SlugField(array(
            'name'=>'slug',
            'model'=>$this,
            'required'=>true,
            'autofill'=>'title',
        ));

        $structure['published'] = new \Gino\BooleanField(array(
            'name'=>'published',
            'model'=>$this,
            'required'=>true,
            'enum'=>array(1 => _('si'), 0 => _('no')),
            'default'=>0,
        ));

        $structure['social'] = new \Gino\BooleanField(array(
            'name'=>'social',
            'model'=>$this,
            'required'=>true,
            'enum'=>array(1 => _('si'), 0 => _('no')),
            'default'=>0,
        ));

        $structure['private'] = new \Gino\BooleanField(array(
            'name'=>'private',
            'model'=>$this,
            'required'=>true,
            'enum'=>array(1 => _('si'), 0 => _('no')),
            'default'=>0,
        ));

        $structure['users'] = new \Gino\ManyToManyInlineField(array(
            'name'=>'users',
            'model'=>$this,
            'm2m'=>'\Gino\App\Auth\User',
            'm2m_where'=>"active='1'", 
            'm2m_order'=>"lastname ASC, firstname",
        ));

        $structure['enable_comments'] = new \Gino\BooleanField(array(
            'name'=>'enable_comments',
            'model'=>$this,
            'required'=>true,
            'enum'=>array(1 => _('si'), 0 => _('no')), 
            'default'=>0,
        ));

        $structure['creation_date'] = new \Gino\DatetimeField(array(
            'name'=>'creation_date',
            'model'=>$this,
            'required'=>true,
            'auto_now'=>false,
            'auto_now_add'=>true,
        ));

        $structure['last_edit_date'] = new \Gino\DatetimeField(array(
            'name'=>'last_edit_date', 
            'model'=>$this,
            'required'=>true,
            'auto_now'=>true,
            'auto_now_add'=>true,
        ));

        $base_path = $this->_controller->getBasePath();
        $add_path = $this->_controller->getAddPath($this->id);

        $structure['image'] = new \Gino\ImageField(array(
            'name'=>'image',
            'model'=>$this,
            'lenght'=>100,
            'extensions'=>self::$_extension_img,
            'resize'=>false,
            'path'=>$base_path,
            'add_path'=>$add_path
        ));

        $structure['tags'] = new \Gino\TagField(array(
            'name' => 'tags',
            'model' => $this,
            'model_controller_class' => 'page',
            'model_controller_instance' => 0
        ));

        return $structure;
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
     * @brief Salva il modello
     * @description Sovrascrive il metodo di Gino.Model per salvare l'autore della pagina
     * @see Gino.Model::save()
     * @return risultato operazione, bool
     */
    public function save()
    {
        $session = \Gino\session::instance();
        $this->author = $session->user_id;

        return parent::save();
    }


    /**
     * @brief Elimina l'oggetto
     * @description Sovrascrive il metodo di Gino.Model per eliminare i commenti
     * @see Gino.Model::delete()
     * @return il risultato dell'operazione, bool
     */
    public function delete() {

        PageComment::deleteFromEntry($this->id);
        
        GTag::deleteTaggedItem($this->_controller->getClassName(), $this->_controller->getInstance(), get_name_class($this), $this->id);

        return parent::delete();
    }

    /**
     * @brief Path relativo dell'immagine associata 
     * @return path relativo dell'immagine
     */
    public function imgPath() {

        return $this->_controller->getBasePath('rel').$this->id.'/'.$this->image;
    }
}
