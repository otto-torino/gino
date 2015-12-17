<?php
/**
 * @file class_sysconf.php
 * @brief Contiene la definizione ed implementazione della classe Gino.App.Sysconf.sysconf
 *
 * @copyright 2013-2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

/**
 * @namespace Gino.App.Sysconf
 * @description Namespace dell'applicazione Sysconf, che gestisce le impostazioni di sistema
 */
namespace Gino\App\Sysconf;

use \Gino\View;
use \Gino\Document;
use \Gino\Http\Redirect;
use \Gino\Http\Request;

require_once('class.Conf.php');

/**
 * @brief Classe di tipo Gino.Controller per la gestione delle principali impostazioni di sistema
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
 *   - Abilita la cache query
 *   - Codice google analytics (es. UA-1234567-1)
 *   - Chiave pubblica reCAPTCHA
 *   - Chiave privata reCAPTCHA
 *   - Chiave pubblica ShareThis
 *   - Shortname Disqus
 *   - Email amministratore di sistema
 *   - Email invio automatico comunicazioni
 *   - Ottimizzazione per dispositivi mobili (Palmari, Iphone)
 *   - Contenuto file robots.txt
 *
 * @copyright 2013-2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class sysconf extends \Gino\Controller {

   /**
    * @brief Costruttore
    * @return istanza di Gino.App.Sysconf.sysconf
    */
    function __construct(){
        parent::__construct();
    }

    /**
     * @brief Interfaccia di amministrazione modulo
     * @param \Gino\Http\Request $request istanza di Gino.Http.Request
     * @return Gino.Http.Response
     */
    public function manageSysconf(\Gino\Http\Request $request) {

        $this->requirePerm('can_admin');

        $conf = new Conf(1);
		$mform = \Gino\Loader::load('ModelForm', array($conf));
        
        if(isset($request->POST['empty_cache'])) {
            \Gino\deleteFileDir(CACHE_DIR, FALSE);
            return new Redirect($this->linkAdmin());
        }
        elseif(isset($request->POST['id'])) {
            
        	// VERIFICARE
        	
        	$result = $mform->save();	// $mform->save($options_form, $options_field);
        	
        	$robots = filter_input(INPUT_POST, 'robots');
            if($fp = @fopen(SITE_ROOT.OS."robots.txt", "wb")) {
                fwrite($fp, $robots);
                fclose($fp);
            }
            return new Redirect($this->linkAdmin());
        }
        elseif($request->checkGETKey('trnsl', '1')) {
            return $this->_trd->manageTranslation($request);
        }
        else {
            
        	$form = $mform->view(
        		array(
        			'addCell' => array(
                    	'google_analytics' => array(
                        	'name' => 'robots',
                        	'field' => \Gino\Input::textarea_label('robots', is_readable(SITE_ROOT.OS.'robots.txt') ? file_get_contents(SITE_ROOT.OS.'robots.txt') : "", $conf->fieldLabel('robots'))
            			),
                    	'enable_cache' => array(
                        	'name' => 'empty_cache',
                        	'field' => \Gino\Input::input_label('empty_cache', 'submit', _('svuota'), _('svuota la cache'), null)
                    	)
                	),
                	'fieldsets' => array(
                    	_('Lingua') => array('id', 'multi_language', 'dft_language'),
                    	_('Log') => array('log_access'),
                    	_('Password') => array('password_crypt'),
                    	_('E-mail') => array('email_admin', 'email_from_app'),
                    	_('Mobile') => array('mobile'),
                    	_('Cache') => array('enable_cache', 'empty_cache', 'query_cache', 'query_cache_time'),
                    	_('Meta') => array('head_title', 'head_description', 'head_keywords'),
                   		_('Robots') => array('robots'),
                    	_('Servizi') => array('google_analytics', 'captcha_public', 'captcha_private', 'sharethis_public_key', 'disqus_shortname')
                	)
        		),
        		array(
        			'head_title' => array('size' => 50),
        			'head_description' => array('cols' => 50, 'rows' => 4),
        			'head_keywords' => array('size' => 50),
        			'query_cache_time' => array('size' => 4),
        			'google_analytics' => array('trnsl' => FALSE),
        			'captcha_public' => array('trnsl' => FALSE),
        			'captcha_private' => array('trnsl' => FALSE),
        			'sharethis_public_key' => array('trnsl' => FALSE),
        			'disqus_shortname' => array('trnsl' => FALSE),
        		)
        	);
        	
        	$content = "<p class=\"backoffice-info\">"._("Configurazione del sistema.")."</p>";
            $content .= $form;
        }

        $dict = array(
            'title' => _('Impostazioni di sistema'),
            'class' => 'admin',
            'content' => $content
        );

        $view = new View();
        $view->setViewTpl('section');

        $document = new Document($view->render($dict));
        return $document();
    }

}
