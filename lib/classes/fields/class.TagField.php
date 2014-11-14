<?php
/**
 * @file class.TagField.php
 * @brief Contiene la classe TagField per la gestione di campi per inserimento tag
 *
 * @copyright 2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

/**
 * @brief Classe per la gestione di campi per inserimento tag
 *
 * @copyright 2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class TagField extends Field {

    private $_model_controller_class,
            $_model_controller_instance;

    /**
     * @brief Costruttore
     * @description Opzioni ulteriori al parent: 
     *              - model_controller_class: nome classe del controller
     *              - model_controller_instance: id istanza del controller
     * @see Field::__construct
     */
    function __construct($options) {
        parent::__construct($options);

        $this->_model_controller_class = $options['model_controller_class'];
        $this->_model_controller_instance = $options['model_controller_instance'];
    }

    /**
     * @brief Widget html per il form
     * @param Form $form istanza del form
     * @param array $options opzioni
     * @see Field::formElement
     * @return widget
     */
    public function formElement($form, $options) {
        // moocomplete
        $registry = registry::instance();
        $registry->addJs(SITE_JS.'/MooComplete.js');
        $registry->addCss(CSS_WWW.'/MooComplete.css');

        // all tags
        $tags = GTag::getAllTags();
        $js_tags_list = "['".implode("','", $tags)."']";

        $text_add = "<span class=\"fa fa-cloud link\" onclick=\"var win = new gino.layerWindow({overlay: false, title: '".jsVar(_('Tag cloud'))."', html: '".jsVar($this->tagCloud())."'}); win.display();\"></span>";
        $field = $form->cinput($this->_name, 'text', $this->_value, $this->_label, array('id' => $this->_name, 'text_add' => $text_add));
        $field .= "<script>";
        // moocomplete script
        $field .= "window.addEvent('load', function() {
            var tag_input = new MooComplete('".$this->_name."', {
                list: $js_tags_list, // elements to use to suggest.
                mode: 'tag', // suggestion mode (tag | text)
                size: 8 // number of elements to suggest
            });
        });\n";
        // clound functionality
        $field .= "var addTag = function(el) {
            var tag = el.get('text');
            var field = $('".$this->_name."');
            if(field.value.substr(field.value.length - 1) == ',' || field.value == '') {
                field.value = field.value + tag;
            }
            else {
                field.value = field.value + ',' + tag;
            }
        }";
        $field .= "</script>";

        return $field;
    }

    /**
     * @brief Ripulisce l'input e registra un listener per salvare i tag quando il modello è stato correttamente salvato
     * @see Field::clean
     * @return valore ripulito
     */
    public function clean($options=null) {
        $event_dispatcher = EventDispatcher::instance();
        $event_dispatcher->listenEmitter($this->_model, 'post_save', array($this, 'save'));

        return parent::clean($options);
    }

    /**
     * @brief Salva i tag nelle tabelle dei tag e quella di associazione ai contenuti
     * @param string $event_name nome evento
     * @param array $param array associativo. La chiave model ha il modello appena salvato
     * @return void
     */
    public function save($event_name, $params) {
        GTag::saveContentTags($this->_model_controller_class, $this->_model_controller_instance, get_class($this->_model), $this->_model->id, $this->_model->tags);
    }

    /**
     * @brief Tag cloud
     * @return tag cloud
     */
    public function tagCloud() {
        $db = db::instance();
        $histogram = GTag::getTagsHistogram();

        $buffer = '<p>';
        foreach($histogram as $tag=>$freq) {
            $buffer .= "<span class=\"link\" onclick=\"addTag(this)\" style=\"font-size: ".(1 + (0.2 * $freq - 0.2))."em\">".$tag."</span> ";
        }
        $buffer .= "</p>";

        return $buffer;
    }



}
