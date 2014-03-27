<?php
/**
 * @file class_sysconf.php
 * @brief Contiene la classe sysconf
 * 
 * @copyright 2013 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

require_once('class.Conf.php');

/**
 * @brief Gestione delle principali impostazioni di sistema
 * 
 * @copyright 2013 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 * 
 * Le impostazioni di sistema sono:
 *   - Gestione lingue
 *   - Lingua di default (se la gestione lingue è disattivata)
 *   - Log degli accessi
 *   - Metodo criptazione password
 *   - Descrizione sito
 *   - Parole chiave sito
 *   - Titolo sito
 *   - Abilita la cache di contenuti e dati
 *   - Codice google analytics (es. UA-1234567-1)
 *   - Chiave pubblica reCAPTCHA
 *   - Chiave privata reCAPTCHA
 *   - Chiave pubblica ShareThis
 *   - Email amministratore di sistema
 *   - Email invio automatico comunicazioni
 *   - Ottimizzazione per dispositivi mobili (Palmari, Iphone)
 *   - Contenuto file robots.txt
 */
class sysconf extends Controller {

  private $_title;

  function __construct(){

    parent::__construct();

    $this->_instance = 0;
    $this->_instanceName = $this->_class_name;

    $this->_title = _("Impostazioni");

    $this->_permissions = array('can_admin');

  }

  public function manageSysconf() {

    $this->requirePerm('can_admin');

    $opts = array();

    $admin_table = loader::load('AdminTable', array(
      $this,
      $opts
    ));

    $conf = new Conf(1);

    $myform = new Form(null, null, null);

    if(isset($_POST['empty_cache'])) {
      $this->_registry->pub->deleteFileDir(CACHE_DIR, false);
      $this->_registry->plink->redirect($this->_class_name, 'manageSysconf');
    }
    elseif(isset($_POST['id'])) {
      $result = $admin_table->modelAction($conf);
      $robots = filter_input(INPUT_POST, 'robots');
      if($fp = @fopen(SITE_ROOT.OS."robots.txt", "wb")) {
        fwrite($fp, $robots);
        fclose($fp);
      }
      $this->_registry->plink->redirect($this->_class_name, 'manageSysconf');
    }
    elseif(isset($_GET['trnsl']) and $_GET['trnsl'] == '1') {
      if(isset($_GET['save']) and $_GET['save'] == '1') {
        $this->_trd->actionTranslation();
      }
      else {
        $this->_trd->formTranslation();
      }
    }
    else {
      $content = "<p class=\"backoffice-info\">"._("Configurazione del sistema.")."</p>";
      $content .= $admin_table->modelForm($conf, array(
        'addCell' => array(
          'google_analytics' => array(
            'name' => 'robots',
            'field' => $myform->ctextarea('robots', is_readable(SITE_ROOT.OS.'robots.txt') ? file_get_contents(SITE_ROOT.OS.'robots.txt') : "", $conf->fieldLabel('robots'))
          ),
          'enable_cache' => array(
            'name' => 'empty_cache',
            'field' => $myform->cinput('empty_cache', 'submit', _('svuota'), _('svuota la cache'), null)
          )
        ),
        'fieldsets' => array(
          _('Lingua') => array('id', 'multi_language', 'dft_language'),
          _('Log') => array('log_access'),
          _('Password') => array('password_crypt'),
          _('E-mail') => array('email_admin', 'email_from_app'),
          _('Mobile') => array('mobile'),
          _('Cache') => array('enable_cache', 'empty_cache'),
          _('Meta') => array('head_title', 'head_description', 'head_keywords'),
          _('Robots') => array('robots'),
          _('Servizi') => array('google_analytics', 'captcha_public', 'captcha_private', 'sharethis_public_key', 'disqus_shortname')
        )
      ), array(
        'google_analytics' => array('trnsl' => false),
        'captcha_public' => array('trnsl' => false),
        'captcha_private' => array('trnsl' => false),
        'sharethis_public_key' => array('trnsl' => false),
        'disqus_shortname' => array('trnsl' => false),
      ));
    }

    $dict = array(
      'title' => _('Impostazioni di sistema'),
      'class' => 'admin',
      'content' => $content
    );

    $view = new view();
    $view->setViewTpl('section');
    return $view->render($dict);

  }

}
?>
