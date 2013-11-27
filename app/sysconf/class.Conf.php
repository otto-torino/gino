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
      'required'=>true,
      'label'=>$this->_fields_label['multi_language'], 
      'enum'=>array(1 => _('si'), 0 => _('no')), 
      'default'=>0,
      'value'=>$this->multi_language, 
      'table'=>$this->_tbl_data
    ));

    $structure['dft_language'] = new ForeignKeyField(array(
      'name'=>'dft_language', 
      'required'=>true,
      'label'=>$this->_fields_label['dft_language'], 
      'lenght'=>3, 
      'fkey_table'=>TBL_LANGUAGE, 
      'fkey_field'=>'language', 
      'fkey_order'=>'language', 
      'value'=>$this->dft_language, 
      'table'=>$this->_tbl_data
    ));

    $structure['log_access'] = new BooleanField(array(
      'name'=>'log_access', 
      'required'=>true,
      'label'=>$this->_fields_label['log_access'], 
      'enum'=>array(1 => _('si'), 0 => _('no')), 
      'default'=>0,
      'value'=>$this->log_access, 
      'table'=>$this->_tbl_data
    ));

    $structure['email_admin'] = new EmailField(array(
      'name'=>'email_admin', 
      'required'=>true,
      'label'=>$this->_fields_label['email_admin'], 
      'value'=>$this->email_admin, 
    ));

    $structure['email_from_app'] = new EmailField(array(
      'name'=>'email_from_app', 
      'required'=>true,
      'label'=>$this->_fields_label['email_from_app'], 
      'value'=>$this->email_from_app, 
    ));

    $structure['mobile'] = new BooleanField(array(
      'name'=>'mobile', 
      'required'=>true,
      'label'=>$this->_fields_label['mobile'], 
      'enum'=>array(1 => _('si'), 0 => _('no')), 
      'default'=>0,
      'value'=>$this->mobile, 
      'table'=>$this->_tbl_data
    ));

    $structure['password_crypt'] = new EnumField(array(
      'name'=>'password_crypt', 
      'required'=>true,
      'label'=>$this->_fields_label['password_crypt'], 
      'enum'=>array('none' => _('nessuno'), 'sha1' => _('sha1'), 'md5' => _('md5')), 
      'default'=>'md5',
      'value'=>$this->password_crypt, 
      'table'=>$this->_tbl_data
    ));

    $structure['enable_cache'] = new BooleanField(array(
      'name'=>'enable_cache', 
      'required'=>true,
      'label'=>$this->_fields_label['enable_cache'], 
      'enum'=>array(1 => _('si'), 0 => _('no')), 
      'default'=>0,
      'value'=>$this->enable_cache, 
      'table'=>$this->_tbl_data
    ));

    return $structure;

  }


}
