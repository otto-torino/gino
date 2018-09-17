<?php
/**
 * @file class.TagBuild.php
 * @brief Contiene la definizione ed implementazione della classe Gino.TagBuild
 *
 * @copyright 2015-2018 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

/**
 * @brief Gestisce i campi per inserimento tag
 *
 * @copyright 2015-2018 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class TagBuild extends Build {

    /**
	 * Proprietà dei campi specifiche del tipo di campo
	 */
	protected $_model_controller_class, $_model_controller_instance;

    /**
     * @brief Costruttore
     *
     * @see Gino.Build::__construct()
     * @param array $options array associativo di opzioni del campo del database
     *   - opzioni generali definite come proprietà nella classe Build()
     */
    function __construct($options) {

        parent::__construct($options);

        $this->_model_controller_class = $options['model_controller_class'];
        $this->_model_controller_instance = $options['model_controller_instance'];
    }

    /**
     * @see Gino.Build::formElement()
     */
    public function formElement($mform, $options=array()) {
        
    	return TagInput::input($this->_name, $this->_value, $this->_label);
    }

    /**
     * @see Gino.Build::clean()
     * @description Ripulisce l'input e registra un listener per salvare i tag quando il modello è stato correttamente salvato
     * 
     * @param array $options array associativo di opzioni
     *   - opzioni della funzione Gino.clean_text()
     * @return string
     */
    public function clean($request_value, $options=null) {
        
    	$event_dispatcher = EventDispatcher::instance();
        $event_dispatcher->listenEmitter($this->_model, 'post_save', array($this, 'save'));

        return clean_text($request_value, $options);
    }

    /**
     * @brief Salva i tag nelle tabelle dei tag e quella di associazione ai contenuti
     * @param string $event_name nome evento
     * @param array $param array associativo. La chiave model ha il modello appena salvato
     * @return void
     */
    public function save($event_name, $params) {
        GTag::saveContentTags($this->_model_controller_class, $this->_model_controller_instance, get_name_class($this->_model), $this->_model->id, $this->_model->tags);
    }

    /**
     * @brief Tag cloud
     * @description Le frequenze vengono conformate a un valore massimo in modo da non avere stringhe con dimensioni troppo grandi. 
     * Il riferimento viene preso sul valore di massima frequenza.
     * 
     * @param array $options array associativo di opzioni di \Gino\GTag::getTagsHistogram()
     * @return string
     */
    public static function tagCloud($options=[]) {
        
        $db = Db::instance();
        $histogram = GTag::getTagsHistogram($options);
        
        $max_freq = max($histogram);
        $max_value = 50;
        
        $buffer = '<p>';
        foreach($histogram as $tag => $freq) {
            
            if($max_freq > $max_value) {
                $freq = ($freq*$max_value)/$max_freq;
            }
            
            $font_size = 1 + (0.05 * $freq - 0.2);
            $font_size = preg_replace("#,#", '.', $font_size);
            $buffer .= "<span class=\"link\" onclick=\"addTag(this)\" style=\"font-size: ".$font_size."em\">".$tag."</span> ";
        }
        $buffer .= "</p>";
        
        return $buffer;
    }
}
