<?php
/**
 * @file class.CodeMirror.php
 * @brief Contiene la definizione ed implementazione della classe Gino.CodeMirror
 * 
 * @copyright 2018 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

/**
 * @brief Classe per l'utilizzo di input per la scrittura di codice
 * @description 
 * 
 * @copyright 2018 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 * 
 * #MODO D'USO
 * 
 * 1. Istanziare la libreria passandogli eventualmente il valore identificativo del codemirror
 * @code
 * $codemirror = \Gino\Loader::load('CodeMirror', array(['type' => 'view', 'mirror_id' => null]));
 * @endcode
 * 
 * 2. Costruire il textarea nel ModelForm
 * @code
 * $buffer = $mform->view(
 *   [
 *     ...
 *     'addCell' => [
 *       'last_cell' => [
 *         'name' => 'code', 
 *         'field' => $codemirror->inputText('code', $code, ['label' => _("Codice PHP")])
 *       ]
 *     ]
 *   ],
 *   [ ... ]
 * );
 * @endcode
 * oppure costruirlo direttamente
 * @code
 * $buffer = $codemirror->inputText('code', $code, ['label' => _("Codice PHP")]);
 * @endcode
 * 
 * 3. dopo aver costruito l'input richiamare lo script
 * @code
 * $buffer .= $codemirror->renderScript();
 * @endcode
 * 
 */
class CodeMirror {

    /**
     * @brief Valore identificativo del textarea
     * @var string
     */
    protected $_code_mirror_id;
    
    /**
     * @brief Opzioni dello script
     * @var string
     */
    private $_script_options;
    
    /**
     * @brief Costruttore
     * @param array $options
     *   - @b type (string): tipologia di codice; valori validi @a css, @a view (default)
     *   - @b mirror_id (string): valore identificativo del textarea
     */
    function __construct($options=[]) {
        
        $type = gOpt('type', $options, 'view');
        $mirror_id = gOpt('mirror_id', $options, null);
        
        if(!$mirror_id) {
            $this->_code_mirror_id = 'codemirror';
        }
        
        $this->_script_options = $this->addResources($type);
    }
    
    public function setCodeMirrorId($value) {
        
        if(is_string($value)) {
            $this->_code_mirror_id = $value;
        }
    }
    
    public function getCodeMirrorId() {
        
        return $this->_code_mirror_id;
    }
    
    /**
     * @brief Carica le risorse e definisce le opzioni
     * @param string $type tipologia di codice; valori validi @a css, @a view (default)
     * @return string
     */
    private function addResources(string $type) {
        
        $registry = Registry::instance();
        $registry->addJs(SITE_JS."/CodeMirror/codemirror.js");
        $registry->addCss(CSS_WWW."/codemirror.css");
        
        if($type == 'css') {
            $registry->addJs(SITE_JS."/CodeMirror/css.js");
            $options = "{
                lineNumbers: true,
                matchBrackets: true,
                indentUnit: 4,
                indentWithTabs: true,
                enterMode: \"keep\",
                tabMode: \"shift\"
            }";
        }
        elseif($type == 'view') {
            $registry->addJs(SITE_JS."/CodeMirror/htmlmixed.js");
            $registry->addJs(SITE_JS."/CodeMirror/matchbrackets.js");
            $registry->addJs(SITE_JS."/CodeMirror/css.js");
            $registry->addJs(SITE_JS."/CodeMirror/xml.js");
            $registry->addJs(SITE_JS."/CodeMirror/clike.js");
            $registry->addJs(SITE_JS."/CodeMirror/php.js");
            $options = "{
                lineNumbers: true,
                matchBrackets: true,
                mode: \"application/x-httpd-php\",
                indentUnit: 4,
                indentWithTabs: true,
                enterMode: \"keep\",
                tabMode: \"shift\"
            }";
        }
        else {
            $options = "{}";
        }
        
        return $options;
    }
    
    /**
     * @brief Textarea
     * @param string name nome dell'input textarea
     * @param mixed $value
     * @param array $options array associativo delle opzioni di Gino.Input::textarea_label() e Gino.Input::textarea();
     *   in particolare possono interessare @a additional_class e @a classField.
     *   In aggiunta ci sono le seguenti opzioni:
     *   - @b label (mixed): label del textarea;
     *     se è presente viene richiamato Gino.Input::textarea_label(), in caso contrario viane richiamato Gino.Input::textarea()
     * @return string
     */
    public function inputText($name, $value, $options=[]) {
        
        $label = gOpt('label', $options, null);
        
        $options['id'] = $this->_code_mirror_id;
        
        if($label) {
            $input = \Gino\Input::textarea_label($name, $value, $label, $options);
        }
        else {
            $input = \Gino\Input::textarea($name, $value, $options);
        }
        return $input;
    }
    
    /**
     * @brief Script che associa CodeMirror al textarea
     * @return string
     */
    public function renderScript() {
        
        return "<script>var myCodeMirror = CodeMirror.fromTextArea(document.getElementById('".$this->_code_mirror_id."'), ".$this->_script_options.");</script>";
    }
}
