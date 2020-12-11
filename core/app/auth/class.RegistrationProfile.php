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

use Gino\Registry;
use Gino\IntegerField;

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
    public static $columns;

    /**
     * @brief Costruttore
     *
     * @param integer $id valore ID del record
     * @return void, istanza di Gino.App.Auth.RegistrationProfile
     */
    function __construct($id) {

        $this->_model_label = _('Profilo di Registrazione');
        $this->_tbl_data = self::$table;
        parent::__construct($id);
    }

    /**
     * @brief Rappresentazione a stringa dell'oggetto
     * @return string, descrizione profilo
     */
    function __toString() {
        
    	return $this->description;
    }

    /**
      * Struttura dei campi della tabella di un modello
      *
      * @return array
      */
     public static function columns() {
     
     	$registry = Registry::instance();
     	
     	$columns['id'] = new \Gino\IntegerField(array(
     		'name'=>'id',
     		'primary_key'=>true,
     		'auto_increment'=>true,
     	));
     	$columns['description'] = new \Gino\CharField(array(
     		'name'=>'description',
     		'label'=>_('Descrizione'),
     		'required'=>true,
     		'max_lenght'=>255,
     	));
     	$columns['title'] = new \Gino\CharField(array(
     		'name'=>'title',
     		'label'=>_('Titolo'),
     		'required'=>false,
     		'max_lenght'=>255,
     	));
     	$columns['text'] = new \Gino\TextField(array(
     		'name'=>'text',
     		'label' => _("Testo"),
     		'required'=>false
     	));
     	$columns['terms'] = new \Gino\TextField(array(
     		'name'=>'terms',
     		'label' => _('Informativa termini e condizioni di utilizzo/privacy'),
     		'required'=>false
     	));
     	$columns['auto_enable'] = new \Gino\BooleanField(array(
        	'name'=>'auto_enable', 
     		'label'=>array(_('Attivazione automatica'), _('Se falso l\'utente deve essere attivato da un altro utente di sistema')),
        	'required'=>true,
        	'default'=>0,
        ));
		$columns['add_information'] = new \Gino\BooleanField(array(
        	'name'=>'add_information', 
        	'label'=>array(_('Informazioni aggiuntive'), _('Richiesta di informazioni aggiuntive gestite da un\'altra applicazione')),
        	'required'=>false,
        	'default'=>0,
        ));
		$columns['add_information_module_type'] = new \Gino\EnumField(array(
        	'name'=>'add_information_module_type', 
        	'label'=>_('Tipologia applicazione'),
        	'widget'=>'select',
        	'max_lenght'=>1, 
        	'choice'=>array(self::MODULE_TYPE_SYS => _('modulo di sistema'), self::MODULE_TYPE_INS => _('modulo istanza')),
        ));
		$columns['add_information_module_id'] = new \Gino\CharField(array(
			'name'=>'add_information_module_id',
			'label'=>_('Id applicazione'),
			'max_lenght'=>11,
			'required'=>false
		));
        $columns['groups'] = new \Gino\ManyToManyField(array(
        	'name'=>'groups',
        	'label'=>_('Gruppi associati all\'utenza'),
        	'required'=>FALSE,
        	'm2m'=>'\Gino\App\Auth\Group',
        	'm2m_where'=>null,
        	'm2m_order'=>'name ASC',
        	'join_table'=>self::$table_groups,
        	'add_related' => TRUE,
        	'add_related_url' => $registry->router->link('auth', 'manageAuth', array(), "block=group&insert=1")
        ));

        return $columns;
    }

    /**
     * @brief Url pagina di registrazione profilo
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->_registry->router->link('auth', 'registration', array('id' => $this->id));
    }

    /**
     * Istanza del modulo utilizzato per gestire informazioni aggiuntive
     *
     * @return object, istanza modulo
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

RegistrationProfile::$columns=RegistrationProfile::columns();