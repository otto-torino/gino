<?php
/**
 * @file class.pageComment.php
 * Contiene la definizione ed implementazione della classe Gino.App.Page.PageComment
 *
 * @copyright 2013-2014 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */

namespace Gino\App\Page;

/**
 * @brief Classe tipo Gino.Model che rappresenta un commento ad una pagina
 *
 * @copyright 2013-2014 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */
class PageComment extends \Gino\Model {

    public static $table = 'page_comment';

    /**
     * @brief Costruttore
     * 
     * @param integer $id valore ID del record
     * @return istanza di Gino.App.Page.PageComment
     */
    function __construct($id) {

        $this->_controller = new page();
        $this->_tbl_data = self::$table;

        $this->_fields_label = array(
            'entry'=>_('Post'),
            'datetime'=>_('Data'),
            'author'=>_("Autore"),
            'email'=>_('Email'),
            'web'=>_("Sito web"),
            'notification'=>_('Notifica altri commenti'),
            'reply'=>_('Risposta'),
            'published'=>_('Pubblicato'),
        );

        parent::__construct($id);

        $this->_model_label = _("Commento");
    }

    /**
     * @brief Rappresentazione testuale dell'oggetto
     * @return data, autore
     */
    function __toString() {

        return (string) $this->id ? $this->datetime.'-'.$this->author : '';
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

        $structure['published'] = new \Gino\BooleanField(array(
            'name'=>'published', 
            'model'=>$this,
            'required'=>true,
            'enum'=>array(1 => _('si'), 0 => _('no')), 
            'default'=>0,
        ));

        $structure['notification'] = new \Gino\BooleanField(array(
            'name'=>'notification', 
            'model'=>$this,
            'required'=>true,
            'enum'=>array(1 => _('si'), 0 => _('no')), 
            'default'=>0, 
        ));

        $structure['datetime'] = new \Gino\DatetimeField(array(
            'name'=>'datetime', 
            'model'=>$this,
            'required'=>true,
            'auto_now'=>false, 
            'auto_now_add'=>true, 
        ));

        $structure['entry'] = new \Gino\ForeignKeyField(array(
            'name'=>'entry', 
            'model'=>$this,
            'lenght'=>255, 
            'foreign'=>'\Gino\App\Page\PageEntry', 
            'foreign_order'=>'last_edit_date',
        ));

        $structure['reply'] = new \Gino\ForeignKeyField(array(
            'name'=>'reply', 
            'model'=>$this,
            'lenght'=>255, 
            'foreign'=>'\Gino\App\Page\PageComment', 
            'foreign_where'=>'entry=\''.$this->entry.'\'', 
            'foreign_order'=>'datetime',
        ));

        return $structure;
    }

    /**
     * @brief Numero totale di commenti per la pagina
     * 
     * @param integer $entry_id identificativo della pagina
     * @return numero di commenti
     */
    public static function getCountFromEntry($entry_id) {

        $res = 0;
        $db = \Gino\Db::instance();
        $rows = $db->select('COUNT(id) AS tot', self::$table, "entry='".$entry_id."' AND published='1'");
        if($rows and count($rows)) {
            $res = $rows[0]['tot'];
        }

        return $res;
    }

    /**
     * @brief Restituisce oggetti di tipo @ref Gino.App.Page.PageComment legati ad una pagina
     *
     * @param array $options array associativo di opzioni
     *   array associativo di opzioni
     *   - @b controller (object): istanza del controller
     *   - @b entry_id (integer): identificativo della pagina
     * @return array di istanze di tipo Gino.App.Page.PageComment
     */
    public static function get($options = null) {

        $res = array();

        $controller = \Gino\gOpt('controller', $options, null);
        $entry_id = \Gino\gOpt('entry_id', $options, null);

        if(!$controller || !$entry_id)
            return $res;

        $published = \Gino\gOpt('published', $options, true);
        $reply = \Gino\gOpt('reply', $options, null);
        $order = \Gino\gOpt('order', $options, 'creation_date');
        $limit = \Gino\gOpt('limit', $options, null);

        $db = \Gino\db::instance();
        $selection = 'id';
        $table = self::$table;
        $where_arr = array("entry='".$entry_id."'");
        if($published) {
            $where_arr[] = "published='1'";
        }
        if(!is_null($reply)) {
            $where_arr[] = "reply='".$reply."'";
        }
        $where = implode(' AND ', $where_arr);

        $rows = $db->select($selection, $table, $where, array('order'=>$order, 'limit'=>$limit));
        if(count($rows)) {
            foreach($rows as $row) {
                $res[] = new PageComment(array('entry_id'=>$row['id'], 'controller'=>$controller));
            }
        }

        return $res;
    }

    /**
     * @brief Albero dei commenti ad una pagina 
     * 
     * @param integer $entry_id identificativo della pagina
     * @param integer $reply identificativo del commento al quale risponde 
     * @return array di array associativi in ordine ad albero. id=>id commento, recursion=>indentazione
     */
    public static function getTree($entry_id, $reply = 0, $tree = array(), $recursion = 0) {

        $db = \Gino\Db::instance();

        if(!$reply) {
            $reply_q = "(reply='0' OR reply IS NULL)";
        }
        else {
            $reply_q = "reply='".$reply."'";
        }

        $child_rows = $db->select("id", self::$table, "$reply_q AND published='1' AND entry='".$entry_id."'", array('order'=>"datetime DESC"));

        foreach($child_rows as $row) {
            $tree[] = array(
                'id'=>$row['id'],
                'recursion'=>$recursion
            );
            $tree = self::getTree($entry_id, $row['id'], $tree, $recursion + 1);
        }

        return $tree;
    }

    /**
     * @brief Eliminazione commenti legati ad una pagina 
     * 
     * @param int $entry_id identificativo della pagina
     * @return risultato dell'operazione, bool
     */
    public static function deleteFromEntry($entry_id) {

        $db = \Gino\Db::instance();
        return $db->delete(self::$table, "entry='".$entry_id."'");
    }

    /**
     * @brief Salva i cambiamenti fatti sull'oggetto modificando o inserendo un nuovo record su DB
     *
     * Invia email di notifica dell'avvenuta pubblicazione di un commento all'autore del post 
     * se l'opzione comment_notification è attiva, ed agli utenti che commentando hanno scelto
     * di essere notificati nel caso fossero stati postati altri commenti
     *
     * @see Gino.Model::save()
     * @return boolean
     */
    public function save() {

        $db_object = new PageComment($this->_p['id']);

        if(!$db_object->published && $this->_p['published']) {
            $notify = TRUE;
        }
        else {
            $notify = FALSE;
        }

        $result = parent::save();

        if($notify) {
            $this->notifyComment();
        }

        return $result;
    }

    /**
     * @brief Notifica gli utenti riguardo l'aggiunta di un commento
     * @return TRUE
     */
    private function notifyComment() {

        $db = \Gino\Db::instance();

        $entry = new pageEntry($this->entry, $this->_controller);

        $plink = new \Gino\Link();    
        $link = $this->_registry->router->link('page', 'view', array('id' => $entry->slug), array(), array('abs' => TRUE)).'#comment'.$this->id;

        $email_from_app = $this->_registry->sysconf->email_from_app;

        // notify other commentors
        $rows = $db->select('DISTINCT(email), author, id', self::$table, "entry='".$this->entry."' AND published='1' AND notification='1'");
        if($rows and count($rows)) {
            foreach($rows as $row) {
                $email = $row['email'];
                if($email != $this->email) {
                    $subject = sprintf(_("Notifica nuovo commento alla pagina \"%s\""), $entry->title);
                    $object = sprintf("%s è stato inserito un nuovo commento da %s, clicca su link seguente (o copia ed incolla nella barra degli indirizzi) per visualizzarlo\r\n%s", $row['author'], $this->author, $link);
                    $from = "From: ".$email_from_app;

                    \mail($email, $subject, $object, $from);
                }
            }
        }

        // notify author
        if($this->_controller->commentNotification()) {

            $concat = $db->concat(array("firstname", "' '", "lastname"));
            $author_email = $db->getFieldFromId('user_app', 'email', 'user_id', $entry->author);
            $author_name = $db->getFieldFromId('user_app', $concat, 'user_id', $entry->author);
            if($author_email) {
                $subject = sprintf(_("Nuovo commento al post \"%s\""), $entry->title);
                $object = sprintf("%s è stato inserito un nuovo commento da %s, clicca su link seguente (o copia ed incolla nella barra degli indirizzi) per visualizzarlo\r\n%s", $author_name, $this->author, $link);
                $from = "From: ".$email_from_app;
                \mail($author_email, $subject, $object, $from);
            }
        }

        return TRUE;
    }
}