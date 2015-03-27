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

    /**
     * @brief Costruttore
     *
     * @param integer $id valore ID del record
     * @return istanza di Gino.App.Auth.RegistrationProfile
     */
    function __construct($id) {

        $this->_fields_label = array(
            'registration_profile' => _('Profilo registrazione'),
            'date' => _('Data'),
            'code' => _('Codice'),
            'firstname' => _('Nome'),
            'lastname' => _('Cognome'),
            'username' => _('Username'),
            'password' => _('Password'),
            'email' => _('Email'),
            'confirmed' => _('Confermato'),
            'user' => _('Utente'),
        );

        $this->_model_label = _('Richiesta di Registrazione');
        $this->_tbl_data = self::$table;
        parent::__construct($id);
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

        $structure['registration_profile'] = new \Gino\ForeignKeyField(array(
            'name' => 'registration_profile',
            'model' => $this,
            'required' => TRUE,
            'foreign' => '\Gino\App\Auth\RegistrationProfile',
            'foreign_order' => 'id ASC',
        ));

        $structure['date'] = new \Gino\DatetimeField(array(
            'name' => 'date',
            'model' => $this,
            'required' => TRUE,
            'auto_now' => FALSE,
            'auto_now_add' => TRUE,
        ));

        $structure['email'] = new \Gino\EmailField(array(
            'name'=>'email',
            'model'=>$this,
            'required'=>TRUE,
        ));

        $structure['confirmed'] = new \Gino\BooleanField(array(
            'name'=>'confirmed',
            'model'=>$this,
            'required'=>TRUE,
            'enum'=>array(1 => _('si'), 0 => _('no')),
            'default'=>0,
        ));

        $structure['user'] = new \Gino\ForeignKeyField(array(
            'name' => 'user',
            'model' => $this,
            'required' => TRUE,
            'foreign' => '\Gino\App\Auth\User',
            'foreign_order' => 'lastname, firstname ASC',
        ));

        return $structure;

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
