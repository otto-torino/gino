<?php
/**
 * @file class.PhpModule.php
 * @brief Contiene la definizione ed implementazione della classe Gino.App.PhpModuleView.PhpModule
 * 
 * @copyright 2005-2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino\App\PhpModuleView;

use \Gino\Http\Redirect;

/**
 * @brief Classe di tipo Gino.Model che rappresenta un outpu scritto in codice php
 * 
 * @copyright 2005-2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class PhpModule extends \Gino\Model {

    protected $_tbl_data;
    public static $table = 'php_module';
    private $_interface;

    /**
     * Costruttore
     * 
     * @param integer $id valore ID dell'istanza
     * @param string $interface nome dell'istanza
     * @return void
     */
    function __construct($id, $interface) {

        $this->_tbl_data = self::$table;

        parent::__construct($id);

        $this->_interface = $interface;
    }

    /**
     * @brief Struttura dati
     * @see Gino.Model::structure()
     * @param int $id id record
     * @return array, struttura
     */
    public function structure($id) {

        parent::structure(null);
        if($id)
        {
            $res = $this->_db->select('*', $this->_tbl_data, "instance='$id'");
            if($res && count($res)) $this->_p = $res[0];
        }
        else $this->_p = array('id'=>null, 'instance'=>null, 'content'=>null);
    }

    /**
     * @brief Setter della proprietà instance
     * @param int $value
     * @return TRUE
     */
    public function setInstance($value) {

        if($this->_p['instance']!=$value && !in_array('instance', $this->_chgP)) $this->_chgP[] = 'instance';
        $this->_p['instance'] = $value;

        return TRUE;
    }

    /**
     * @brief Setter della proprietà content (codice php)
     * @param string $value
     * @return TRUE
     */
    public function setContent($value) {

        if($this->_p['content']!=$value && !in_array('content', $this->_chgP)) $this->_chgP[] = 'content';
        $this->_p['content'] = $value;

        return TRUE;
    }

    /**
     * @brief Form di inserimento e modifica del codice php
     * @return html, form
     */
    public function formPhpModule() {

        $this->_registry->addJs(SITE_JS."/CodeMirror/codemirror.js");
        $this->_registry->addCss(CSS_WWW."/codemirror.css");
        $this->_registry->addJs(SITE_JS."/CodeMirror/htmlmixed.js");
        $this->_registry->addJs(SITE_JS."/CodeMirror/matchbrackets.js");
        $this->_registry->addJs(SITE_JS."/CodeMirror/css.js");
        $this->_registry->addJs(SITE_JS."/CodeMirror/xml.js");
        $this->_registry->addJs(SITE_JS."/CodeMirror/clike.js");
        $this->_registry->addJs(SITE_JS."/CodeMirror/php.js");

        $options = "{
            lineNumbers: true,
            matchBrackets: true,
            mode: \"application/x-httpd-php\",
            indentUnit: 4,
            indentWithTabs: true,
            enterMode: \"keep\",
            tabMode: \"shift\"
        }";

        $gform = \Gino\Loader::load('Form', array('gform', 'post', true));
        $gform->load('dataform');

        $required = 'content';

        $buffer = $gform->open($this->_registry->router->link($this->_interface, 'manageDoc', array(), array('action' => 'save')), '', $required, array("generateToken"=>true));

        $content = $this->content? $this->content:"\$buffer = '';";
        $buffer .= $gform->ctextarea('content', htmlspecialchars($gform->retvar('content',$content)), array(_("Codice"), _("Il codice php deve ritornare tutto l'output immagazzinato dentro la variabile <b>\$buffer</b>, la quale <b>non</b> deve essere reinizializzata. Attenzione a <b>non stampare</b> direttamente variabili con <b>echo</b> o <b>print()</b>, perchè in questo caso i contenuti verrebbero stampati al di fuori del layout.<br/>Le funzioni di esecuzione di programmi sono disabilitate.")), array("id"=>"codemirror", "required"=> true, "other"=>"style=\"width: 95%\"", "rows"=>30));

        $buffer .= $gform->cinput('submit_action', 'submit', _("salva"), '', array("classField"=>"submit"));
        $buffer .= $gform->close();

        $buffer .= "<script>var myCodeMirror = CodeMirror.fromTextArea(document.getElementById('codemirror'), $options);</script>";

        $view =   new \Gino\View(null, 'section');
        $dict = array(
        'title' => _('Modifica codice'),
        'class' => 'admin',
        'content' => $buffer
        );
        return $view->render($dict);
    }

    /**
     * @brief Processa il form di modifica/inserimento codice php
     * @see self::formPhpModule()
     * @param \Gino\Http\Request $request istanza di Gino.Http.Request
     * @return \Gino\Http\Redirect
     */
    public function actionPhpModule(\Gino\Http\Request $request) {

        $gform = \Gino\Loader::load('Form', array('gform', 'post', false, array("verifyToken"=>true)));
        $gform->save('dataform');
        $req_error = $gform->arequired();

        $content = $this->_db->escapeString(htmlspecialchars_decode($request->POST['content']));
        $link_error = $this->_registry->router->link($this->_interface, 'manageDoc', array(), array('action' => 'modify'));

        if($req_error > 0) {
            return error::errorMessage(array('error'=>1), $link_error);
        }

        $this->content = $content;
        $this->save();

        return new Redirect($this->_registry->router->link($this->_interface, 'manageDoc'));
    }
}
