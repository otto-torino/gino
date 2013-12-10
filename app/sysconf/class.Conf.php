<?php

class Conf extends Model {

  function __construct() {

    $this->_tbl_data = TBL_SYS_CONF;
    $this->_fields_label = array(
      'multi_language' => array(_('gestione multilingua'), _("Gestione di un sito multi-lingua. Le lingue attive e quella principale sono da settarsi nel modulo Lingue del sistema.")),
      'dft_language' => array(_('lingua di default'), _("Lingua dei contenuti del sito nel caso in cui la gestione multilingua sia disattivata.")),
      'log_access' => array(_('log accessi'), _("Log di tutti gli accessi all'area riservata del sito da parte degli utenti.")),
      'head_description' => array(_('contenuto meta tag description'), _("Descrizione di base che compare nei meta tag e letta dai motori di ricerca. Molti moduli sovrascrivono questo testo.")),
      'head_keywords' => array(_('contenuto meta tag keywords'), _("Parole chiave che rappresentano i contenuti del sito, lette dai motori di ricerca. Molti moduli sovrascrivono questo testo.")),
      'head_title' => array(_('contenuto meta tag title'), _("Titolo del sito visualizzato nella finestra/scheda dei Browser. Molti moduli sovrascrivono questo testo.")),
      'google_analytics' => array(_('codice google analytics'), _("Codice da inserire per utilizzare il sistema di statistiche google analytics")),
      'captcha_public' => array(_('chiave pubblica reCAPTCHA'), _("Chiave pubblica per l'utilizzo del sistema captcha reCAPTCHA. Se non inserita il sistema utilizzerà il sistema di prevenzione di default.")),
      'captcha_private' => array(_('chiave privata reCAPTCHA'), _("Chiave privata per l'utilizzo del sistema captcha reCAPTCHA. Se non inserita il sistema utilizzerà il sistema di prevenzione di default.")),
      'email_admin' => _('email amministratore sito'),
      'email_from_app' => array(_('email invio automatico comunicazioni'), _("Indirizzo e-mail utilizzato per inviare comunicazioni automatiche da parte del sistema")),
      'mobile' => array(_('gestione mobile'), _("Attiva il riconoscimento di dispositivi mobili. E' necessario configurare correttamente i template e le skin per una corretta visualizzazione.")),
      'password_crypt' => array(_('metodo di criptazione delle password'), _('se si modifica l\'impostazione è necessario risalvare tutte le password utenti per aggiornarle secondo la nuova impostazione.')),
      'enable_cache' => array(_('abilitazione cache'), _("Abilita le funzionalità di caching su file dei contenuti e dati dei singoli moduli e delle skin")),
      'robots' => array(_('contenuto file robots'), _("Il file robots.txt viene utilizzato per fornire indicazioni riguardo all'indicizzazione dei contenuti del sito nei motori di ricerca"))
    );

    parent::__construct(1);

  }

  /**
   * Rappresentazione testuale del modello 
   *
   * @return string
   */
  function __toString() {

    return _('Configurazione Gino');
  }

  /**
   * Sovrascrive la struttura di default
   * 
   * @see propertyObject::structure()
   * @param integer $id
   * @return array
   */
  public function structure($id) {

    $structure = parent::structure($id);

    $structure['multi_language'] = new BooleanField(array(
      'name'=>'multi_language', 
      'model'=>$this,
      'required'=>true,
      'enum'=>array(1 => _('si'), 0 => _('no')), 
      'default'=>0,
    ));

    $structure['dft_language'] = new ForeignKeyField(array(
      'name'=>'dft_language', 
      'model'=>$this,
      'required'=>true,
      'lenght'=>3, 
      'foreign'=>'Lang', 
      'foreign_order'=>'language', 
    ));

    $structure['log_access'] = new BooleanField(array(
      'name'=>'log_access', 
      'model'=>$this,
      'required'=>true,
      'enum'=>array(1 => _('si'), 0 => _('no')), 
      'default'=>0,
    ));

    $structure['email_admin'] = new EmailField(array(
      'name'=>'email_admin', 
      'model'=>$this,
      'required'=>true,
    ));

    $structure['email_from_app'] = new EmailField(array(
      'name'=>'email_from_app', 
      'model'=>$this,
      'required'=>true,
    ));

    $structure['mobile'] = new BooleanField(array(
      'name'=>'mobile', 
      'model'=>$this,
      'required'=>true,
      'enum'=>array(1 => _('si'), 0 => _('no')), 
      'default'=>0,
    ));

    $structure['password_crypt'] = new EnumField(array(
      'name'=>'password_crypt', 
      'model'=>$this,
      'required'=>true,
      'enum'=>array('none' => _('nessuno'), 'sha1' => _('sha1'), 'md5' => _('md5')), 
      'default'=>'md5',
    ));

    $structure['enable_cache'] = new BooleanField(array(
      'name'=>'enable_cache', 
      'model'=>$this,
      'required'=>true,
      'enum'=>array(1 => _('si'), 0 => _('no')), 
      'default'=>0,
    ));

    return $structure;

  }


}
