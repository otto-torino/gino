<?php
/**
 * @file class.Conf.php
 * @brief Contiene la definizione ed implementazione della classe Gino.App.Sysconf.Conf
 *
 * @copyright 2013-2016 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

namespace Gino\App\Sysconf;

/**
 * @brief Classe di tipo Gino.Model che gestisce le impostazioni del sistema
 *
 * Le impostazioni sono salvate in una riga di tabella corrispondente ad ID 1
 *
 * @copyright 2013-2016 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class Conf extends \Gino\Model {

    public static $table = "sys_conf";
    public static $columns;
    
    /**
     * @brief Costruttore
     * @return void
     */
    function __construct() {

        $this->_tbl_data = TBL_SYS_CONF;
        
        parent::__construct(1);
    }

    /**
     * @brief Rappresentazione a stringa dell'oggetto
     * @return string
     */
     function __toString() {

        return _('Configurazione gino');
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
		$columns['multi_language'] = new \Gino\BooleanField(array(
			'name' => 'multi_language',
			'label' => array(_('gestione multilingua'), _("Gestione di un sito multi-lingua. Le lingue attive e quella principale sono da settarsi nel modulo Lingue del sistema.")),
			'required' => true,
			'default' => 0,
		));
		$columns['dft_language'] = new \Gino\ForeignKeyField(array(
			'name' => 'dft_language',
			'label' => array(_('lingua di default'), _("Lingua dei contenuti del sito nel caso in cui la gestione multilingua sia disattivata.")),
			'required' => true,
			'max_lenght' => 3,
			'foreign' => '\Gino\App\Language\Lang',
			'foreign_order' => 'language',
		));
		$columns['log_access'] = new \Gino\BooleanField(array(
			'name' => 'log_access',
			'label' => array(_('log accessi'), _("Log di tutti gli accessi all'area riservata del sito da parte degli utenti.")),
			'required' => true,
			'default' => 0,
		));
		$columns['head_description'] = new \Gino\TextField(array(
			'name' => 'head_description',
			'label' => array(_('contenuto meta tag description'), _("Descrizione di base che compare nei meta tag e letta dai motori di ricerca. Molti moduli sovrascrivono questo testo.")),
			'required' => true
		));
		$columns['head_keywords'] = new \Gino\CharField(array(
			'name' => 'head_keywords',
			'label' => array(_('contenuto meta tag keywords'), _("Parole chiave che rappresentano i contenuti del sito, lette dai motori di ricerca. Molti moduli sovrascrivono questo testo.")),
            'required'=>false,
    		'max_lenght'=>255,
		));
		$columns['head_title'] = new \Gino\CharField(array(
			'name' => 'head_title',
			'label' => array(_('contenuto meta tag title'), _("Titolo del sito visualizzato nella finestra/scheda dei Browser. Molti moduli sovrascrivono questo testo.")),
            'required' => true,
    		'max_lenght' => 255,
		));
		$columns['google_analytics'] = new \Gino\CharField(array(
			'name' => 'google_analytics',
			'label' => array(_('codice google analytics'), _("Codice da inserire per utilizzare il sistema di statistiche google analytics")),
            'required' => false,
    		'max_lenght' => 20,
		));
		$columns['captcha_public'] = new \Gino\CharField(array(
			'name'=>'captcha_public',
			'label' => array(_('chiave pubblica reCAPTCHA'), _("Chiave pubblica per l'utilizzo del sistema captcha reCAPTCHA. Se non inserita il sistema utilizzerà il sistema di prevenzione di default.")),
			'required'=>false,
    		'max_lenght'=>64,
		));
		$columns['captcha_private'] = new \Gino\CharField(array(
			'name' => 'captcha_private',
			'label' => array(_('chiave privata reCAPTCHA'), _("Chiave privata per l'utilizzo del sistema captcha reCAPTCHA. Se non inserita il sistema utilizzerà il sistema di prevenzione di default.")),
			'required' => false
		));
		$columns['sharethis_public_key'] = new \Gino\CharField(array(
			'name' => 'sharethis_public_key',
			'label' => array(_('chiave pubblica ShareThis'), _("Chiave pubblica per l'utilizzo del servizio di sharing ShareThis. Se non inserita il sistema utilizzerà il sistema di sharing di default.")),
			'required' => false,
    		'max_lenght' => 64,
		));
		$columns['disqus_shortname'] = new \Gino\CharField(array(
			'name' => 'disqus_shortname',
			'label' => array(_('Disqus shortname'), _("Shortname per l'integrazione del sistema di commenti Disqus.")),
			'required' => false,
    		'max_lenght' => 64,
		));
		$columns['email_admin'] = new \Gino\EmailField(array(
			'name' => 'email_admin',
			'label' => _('email amministratore sito'),
            'required'=>true,
    		'max_lenght'=>128,
		));
		$columns['email_from_app'] = new \Gino\EmailField(array(
			'name' => 'email_from_app',
			'label' => array(_('email invio automatico comunicazioni'), _("Indirizzo e-mail utilizzato per inviare comunicazioni automatiche da parte del sistema")),
            'required' => true,
    		'max_lenght' => 100,
		));
		$columns['mobile'] = new \Gino\BooleanField(array(
			'name' => 'mobile',
			'label' => array(_('gestione mobile'), _("Attiva il riconoscimento di dispositivi mobili. E' necessario configurare correttamente i template e le skin per una corretta visualizzazione.")),
            'required' => true,
			'default' => 0,
		));
		$columns['password_crypt'] = new \Gino\EnumField(array(
			'name'=>'password_crypt',
			'label' => array(_('metodo di criptazione delle password'), _('se si modifica l\'impostazione è necessario risalvare tutte le password utenti per aggiornarle secondo la nuova impostazione.')),
            'required' => true,
			'choice' => array('none' => _('nessuno'), 'sha1' => _('sha1'), 'md5' => _('md5')),
			'value_type' => 'string', 
			'default' => 'md5',
		));
		$columns['enable_cache'] = new \Gino\BooleanField(array(
			'name' => 'enable_cache',
			'label' => array(_('abilitazione cache'), _("Abilita le funzionalità di caching su file dei contenuti e dati dei singoli moduli e delle skin")),
            'required' => true,
			'default' => 0,
		));
		$columns['query_cache'] = new \Gino\BooleanField(array(
			'name' => 'query_cache',
			'label' => array(_('abilitazione cache delle query'), _("Per la tipologia di cache query da utilizzare modificare le impostazioni nel file configuration.php")), 
        	'required' => true,
			'default' => 0,
		));
		$columns['query_cache_time'] = new \Gino\IntegerField(array(
			'name' => 'query_cache_time',
			'label' => array(_('tempo di durata della cache query'), _("Tempo in secondi")),
			'required' => false
		));
		return $columns;
	}
}

Conf::$columns=Conf::columns();

