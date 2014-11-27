<?php
/**
 * @file class_language.php
 * @brief Contiene la classe definizione ed implementazione della classe Gino.App.Language.language
 * 
 * @copyright 2005-2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

/**
 * @namespace Gino.App.Language
 * @description Namespace dell'applicazione Language
 */
namespace Gino\App\Language;

use \Gino\Http\Response;
use \Gino\View;
use \Gino\Document;

require_once('class.Lang.php');

/**
 * @brief Classe di tipo Gino.Controller per la gestione delle lingue disponibili per le traduzioni
 *
 * @copyright 2005-2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class language extends \Gino\Controller {

    const FLAG_PREFIX = "flag_";
    const FLAG_SUFFIX = ".gif";

    private $_options;
    public $_optionsLabels;
    private $_title;
    private $_flag_language;

    function __construct(){

        parent::__construct();

        $this->_title = \Gino\htmlChars($this->setOption('title', true));
        $this->_flag_language = $this->setOption('opt_flag');

        $this->_options = \Gino\Loader::load('Options', array($this));
        $this->_optionsLabels = array("title"=>_("Titolo"), "opt_flag"=>_("Bandiere come etichette"));

        $this->_view_dir = dirname(__FILE__).OS.'views';
    }

    /**
     * @brief Elenco dei metodi che possono essere richiamati dal menu e dal template
     * @return array, elenco metodi nella forma nome_metodo => array(label => string, permissions => array())
     */
    public static function outputFunctions() {

        $list = array(
            "choiceLanguage" => array("label"=>_("Scelta lingua"), "permissions"=>array()),
        );

        return $list;
    }

    /**
     * @brief Vista scelta lingua (includibile in template)
     * @description Se l'impostazione multilingua è FALSE ritorna una risposta di contenuto vuoto
     *
     * @return html
     */
    public function choiceLanguage(){

        $GINO = $this->_registry->addCss($this->_class_www.'/language.css');

        if($this->_registry->sysconf->multi_language) {

            $codes = explode('_', $this->_registry->request->session->lng);
            $lng_support = (bool) $this->_db->getNumRecords(TBL_LANGUAGE, "active='1' AND language_code='".$codes[0]."' AND country_code='".$codes[1]."'");

            $languages = Lang::objects(null, array('where' => "active='1'", 'order' => 'language'));
            $view = new \Gino\View($this->_view_dir, 'choice_language');
            $dict = array(
                'languages' => $languages,
                'flag' => $this->_flag_language,
                'flag_prefix' => self::FLAG_PREFIX,
                'flag_suffix' => self::FLAG_SUFFIX,
                'lng_support' => $lng_support,
                'lng' => $this->_registry->request->session->lng,
                'lng_dft' => $this->_registry->request->session->lngDft,
                'router' => $this->_registry->router
            );

            return $view->render($dict);

        }

        return '';
    }

    /**
     * @brief Interfaccia di amministrazione modulo
     * @param \Gino\Http\Request $request istanza di Gino.Http.Request
     * @return Gino.Http.Response
     */
    public function manageLanguage(\Gino\Http\Request $request) {

        $this->requirePerm('can_admin');

        $block = \Gino\cleanVar($request->GET, 'block', 'string', null);

        $link_options = "<a href=\"".$this->_home."?evt[$this->_class_name-manageLanguage]&block=options\">"._("Opzioni")."</a>";
        $link_dft = "<a href=\"".$this->_home."?evt[".$this->_class_name."-manageLanguage]\">"._("Gestione")."</a>";
        $sel_link = $link_dft;

        if($block=='options') {
            $backend = $this->manageOptions();
            $sel_link = $link_options;
        }
        else {
            $backend = $this->manageLang($request);
        }

        if(is_a($backend, '\Gino\Http\Response')) {
        	return $backend;
        }

        $dict = array(
            'title' => _('Lingue di sistema'),
            'links' => array($link_options, $link_dft),
            'selected_link' => $sel_link,
            'content' => $backend
        );

        $view = new View();
        $view->setViewTpl('tab');

        $document = new Document($view->render($dict));
        return $document();
    }

    /**
     * @brief Interfaccia di amministrazione lingue installate nel sistema
     * @param \Gino\Http\Request $request istanza di Gino.Http.Request
     * @return html, interfaccia
     */
    private function manageLang(\Gino\Http\Request $request) {

        $info = "<p>"._("Elenco di tutte le lingue supportate dal sistema, attivare quelle desiderate.</p>");
        $info .= "<p>"._("L'inserimento dei contenuti e la visualizzazione in assenza di traduzioni avviene nella lingua di default impostata nella sezione 'Impostazioni di sistema'.")."</p>\n";

        $opts = array(
            'list_description' => $info
        );

        $admin_table = \Gino\Loader::load('AdminTable', array($this));

        return $admin_table->backoffice('Lang', $opts);
    }

}
