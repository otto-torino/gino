<?php
/**
 * @file class.RegistrationRequest.php
 * Contiene la definizione ed implementazione della classe Gino.App.Auth.RegistrationRequest.
 * 
 * @copyright 2015 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */
namespace Gino\App\Auth;

/**
 * @brief Classe tipo Gino.Model che rappresenta una richiesta di registrazione
 *
 * @copyright 2015 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */
class RegistrationRequest extends \Gino\Model {

    public static $table = TBL_REGISTRATION_REQUEST;
    public static $columns;

    /**
     * @brief Costruttore
     *
     * @param integer $id valore ID del record
     * @return void, istanza di Gino.App.Auth.RegistrationProfile
     */
    function __construct($id) {

        $this->_model_label = _('Richiesta di Registrazione');
        $this->_tbl_data = self::$table;
        parent::__construct($id);
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
     	$columns['registration_profile'] = new \Gino\ForeignKeyField(array(
            'name' => 'registration_profile',
            'label' => _('Profilo registrazione'),
            'required' => TRUE,
            'foreign' => '\Gino\App\Auth\RegistrationProfile',
            'foreign_order' => 'id ASC',
        ));
        $columns['date'] = new \Gino\DatetimeField(array(
            'name' => 'date',
            'label' => _('Data'),
            'required' => TRUE,
            'auto_now' => FALSE,
            'auto_now_add' => TRUE,
        ));
        $columns['code'] = new \Gino\CharField(array(
        	'name'=>'code',
        	'label'=>_('Codice'),
        	'required'=>true,
        	'max_lenght'=>32,
        ));
        $columns['firstname'] = new \Gino\CharField(array(
        	'name'=>'firstname',
        	'label'=>_('Nome'),
        	'required'=>true,
        	'max_lenght'=>255,
        ));
        $columns['lastname'] = new \Gino\CharField(array(
        	'name'=>'lastname',
        	'label'=>_('Cognome'),
        	'required'=>true,
        	'max_lenght'=>255,
        ));
        $columns['username'] = new \Gino\CharField(array(
        	'name'=>'username',
        	'label'=>_('Username'),
        	'required'=>true,
        	'max_lenght'=>50,
        ));
        $columns['password'] = new \Gino\CharField(array(
        	'name'=>'password',
        	'label'=>_('Password'),
        	'required'=>true,
        	'widget'=>'password', 
        	'max_lenght'=>100,
        ));
        $columns['email'] = new \Gino\EmailField(array(
            'name'=>'email',
            'label'=>_('Email'),
            'required'=>TRUE,
        	'max_lenght'=>128,
        ));
        $columns['confirmed'] = new \Gino\BooleanField(array(
            'name'=>'confirmed',
            'label'=>_('Confermato'),
            'required'=>TRUE,
            'default'=>0,
        ));
        $columns['user'] = new \Gino\ForeignKeyField(array(
            'name' => 'user',
            'label' => _('Utente'),
            'required' => false,
            'foreign' => '\Gino\App\Auth\User',
            'foreign_order' => 'lastname, firstname ASC',
        ));

        return $columns;
     }

    /**
     * Nome utente o link per sua creazione e attivazione
     *
     * @return string
     */
    public function getOrActivateUser()
    {
        if($this->user) {
            $user = new User($this->user);
            return '<a href="'.$this->_registry->router->link('auth', 'manageAuth', array(), array('edit' => 1, 'id' => $user->id)).'">' . sprintf('%s %s', \Gino\htmlChars($user->firstname), \Gino\htmlChars($user->lastname)) . '</a>';
        }
        else {
            return '<a href="'.$this->_registry->router->link('auth', 'activateRegistrationUser', array(), array('id' => $this->id)).'">' . _('crea ed attiva utente') . '</a>';
        }
    }

}

RegistrationRequest::$columns=RegistrationRequest::columns();