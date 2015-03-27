<?php
/**
 * @file class.RegistrationProfile.php
 * Contiene la definizione ed implementazione della classe Gino.App.Auth.RegistrationProfile.
 * 
 * @copyright 2015 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */
namespace Gino\App\Auth;

/**
 * @brief Classe tipo Gino.Model che rappresenta un profilo di registrazione utenti
 *
 * @copyright 2015 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */
class RegistrationProfile extends \Gino\Model {

    const MODULE_TYPE_SYS = 1;
    const MODULE_TYPE_INS = 2;

    public static $table = TBL_REGISTRATION_PROFILE;
    public static $table_groups = TBL_REGISTRATION_PROFILE_GROUP;

    /**
     * @brief Costruttore
     *
     * @param integer $id valore ID del record
     * @return istanza di Gino.App.Auth.RegistrationProfile
     */
    function __construct($id) {

        $this->_fields_label = array(
            'description' => _('Descrizione'),
            'title' => _('Titolo'),
            'text' => _('Testo'),
            'terms' => _('Informativa termini e condizioni di utilizzo/privacy'),
            'auto_enable' => array(_('Attivazione automatica'), _('Se falso l\'utente deve essere attivato da un altro utente di sistema')),
            'add_information' => array(_('Informazioni aggiuntive'), _('Richiesta di informazioni aggiuntive gestite da un\'altra applicazione')),
            'add_information_module_type' => _('Tipologia applicazione'),
            'add_information_module_id' => _('Id applicazione'),
            'groups' => _('Gruppi associati all\'utenza'),
        );

        $this->_model_label = _('Profilo di Registrazione');
        $this->_tbl_data = self::$table;
        parent::__construct($id);
    }

    /**
     * @brief Rappresentazione a stringa dell'oggetto
     * @return descrizione profilo
     */
    function __toString()
    {
        return $this->description;
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

        $structure['auto_enable'] = new \Gino\BooleanField(array(
            'name'=>'auto_enable', 
            'model'=>$this,
            'required'=>true,
            'enum'=>array(1 => _('si'), 0 => _('no')), 
            'default'=>0,
        ));

        $structure['groups'] = new \Gino\ManyToManyField(array(
            'name'=>'groups',
            'model'=>$this,
            'required'=>FALSE,
            'm2m'=>'\Gino\App\Auth\Group',
            'm2m_where'=>null,
            'm2m_order'=>'name ASC',
            'join_table'=>self::$table_groups,
            'add_related' => TRUE,
            'add_related_url' => $this->_registry->router->link('auth', 'manageAuth', array(), "block=group&insert=1")
        ));

        $structure['add_information'] = new \Gino\BooleanField(array(
            'name'=>'add_information', 
            'model'=>$this,
            'required'=>true,
            'enum'=>array(1 => _('si'), 0 => _('no')), 
            'default'=>0,
        ));

        $structure['add_information_module_type'] = new \Gino\EnumField(array(
            'name'=>'add_information_module_type', 
            'model'=>$this,
            'widget'=>'select',
            'lenght'=>4, 
            'enum'=>array(self::MODULE_TYPE_SYS => _('modulo di sistema'), self::MODULE_TYPE_INS => _('modulo istanza')),
        ));

        return $structure;
    }

    /**
     * @brief Url pagina di registrazione profilo
     *
     * @return url
     */
    public function getUrl()
    {
        return $this->_registry->router->link('auth', 'registration', array('id' => $this->id));
    }

    /**
     * Istanza del modulo utilizzato per gestire informazioni aggiuntive
     *
     * @return istanza modulo, object
     */
    public function informationApp()
    {
        if(!$this->add_information) return FALSE;

        if(!$this->add_information_module_type or !$this->add_information_module_id) {
            throw new \Exception(_('Tipo e/o id del modulo che deve completare le informazioni mancanti per la registrazione.'));
        }

        if($this->add_information_module_type == self::MODULE_TYPE_SYS) {
            $module = new \Gino\App\SysClass\ModuleApp($this->add_information_module_id);
            if(!$module->id) {
                throw new \Exception(sprintf(_('Il modulo che richiede informazioni aggiuntive alla registrazione è sconosciuto (id: %s).'), $this->add_information_module_id));
            }
            $class = $module->classNameNs();
            $app = new $class();
        }
        else {
            $module = new \Gino\App\Module\ModuleInstance($this->add_information_module_id);
            if(!$module->id) {
                throw new \Exception(sprintf(_('Il modulo che richiede informazioni aggiuntive alla registrazione è sconosciuto (id: %s).'), $this->add_information_module_id));
            }
            $class = $module->classNameNs();
            $app = new $class($module->id);
        }
        // check module's method existance
        if(!method_exists($app, 'formAuthRegistration') or !method_exists($app, 'actionAuthRegistration')) {
            throw new \Exception(sprintf(_('Il modulo %s che richiede informazioni aggiuntive alla registrazione non implementa i metodi necessari (formAuthRegistration, actionAuthRegistration).'), $module->label));
        }

        return $app;
    }

}
