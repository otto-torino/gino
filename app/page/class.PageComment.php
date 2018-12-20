<?php
/**
 * @file class.PageComment.php
 * Contiene la definizione ed implementazione della classe Gino.App.Page.PageComment
 *
 * @copyright 2013-2018 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */

namespace Gino\App\Page;

/**
 * @brief Classe tipo Gino.Model che rappresenta un commento ad una pagina
 *
 * @copyright 2013-2018 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */
class PageComment extends \Gino\Model {

    public static $table = 'page_comment';
    public static $table_users = TBL_USER;
    public static $columns;

    /**
     * @brief Costruttore
     * 
     * @param integer $id valore ID del record
     * @return void
     */
    function __construct($id) {

        $this->_controller = new page();
        $this->_tbl_data = self::$table;

        parent::__construct($id);

        $this->_model_label = _("Commento");
    }

    /**
     * @brief Rappresentazione testuale dell'oggetto
     * @return string, data e autore
     */
    function __toString() {

        return (string) $this->id ? $this->datetime.'-'.$this->author : '';
    }
    
    protected static function properties($model, $controller) {
    	
    	$items = array();
    	
    	$items['reply'] = array(
    		'foreign_where'=>'entry=\''.$model->entry.'\'',
    	);
    	
    	return $items;
    }
    
    /**
     * Struttura dei campi della tabella di un modello
     *
     * @return array
     */
    public static function columns() {
    
    	$columns['id'] = new \Gino\IntegerField(array(
    		'name' => 'id',
    		'primary_key' => true,
    		'auto_increment' => true,
    	));
    	$columns['entry'] = new \Gino\ForeignKeyField(array(
    		'name' => 'entry',
    		'label' => _('Pagina'),
            'required' => true,
    		'foreign' => '\Gino\App\Page\PageEntry',
    		'foreign_order' => 'last_edit_date',
    	));
    	$columns['datetime'] = new \Gino\DatetimeField(array(
    		'name' => 'datetime',
    		'label' => _('Data'),
    		'required' => true,
    		'auto_now' => false,
    		'auto_now_add' => true,
    	));
    	$columns['author'] = new \Gino\CharField(array(
    		'name' => 'author',
    		'label' => _("Autore"),
    		'required' => true,
    		'max_lenght' => 200,
    		'trnsl' => false
    	));
    	$columns['email'] = new \Gino\EmailField(array(
    		'name' => 'email',
    		'label' => _("Email"),
    		'required' => true,
    		'max_lenght' => 200,
    	));
    	$columns['web'] = new \Gino\CharField(array(
    		'name' => 'web',
    		'label' => _("Sito web"),
    		'required' => false,
    		'max_lenght' => 200,
    		'trnsl' => false
    	));
    	$columns['text'] = new \Gino\TextField(array(
    		'name' => 'text',
    	    'label' => [_('Testo'), _('Non è consentito l\'utilizzo di alcun tag html')],
    		'required' => true
    	));
    	$columns['notification'] = new \Gino\BooleanField(array(
    		'name' => 'notification',
    		'label' => _('Notifica altri commenti'),
    		'required' => true,
    	));
    	$columns['reply'] = new \Gino\ForeignKeyField(array(
    		'name'=>'reply',
    		'label'=>_('Risposta'),
    		'required'=>false,
    		'foreign'=>'\Gino\App\Page\PageComment',
    		'foreign_where'=>null,
    		'foreign_order'=>'datetime',
    	));
		$columns['published'] = new \Gino\BooleanField(array(
            'name' => 'published', 
            'label' => _('Pubblicato'),
            'required' => true,
        ));

        return $columns;
    }

    /**
     * @brief Numero totale di commenti per la pagina
     * 
     * @param integer $entry_id identificativo della pagina
     * @return integer
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
     * @return boolean, risultato dell'operazione
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
    public function save($options=array()) {

        $db_object = new PageComment($this->_p['id']);

        if(!$db_object->published && $this->_p['published']) {
            $notify = TRUE;
        }
        else {
            $notify = FALSE;
        }

        $result = parent::save($options);

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
            $author_email = $db->getFieldFromId(self::$table_users, 'email', 'id', $entry->author);
            $author_name = $db->getFieldFromId(self::$table_users, $concat, 'id', $entry->author);
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

PageComment::$columns=PageComment::columns();
