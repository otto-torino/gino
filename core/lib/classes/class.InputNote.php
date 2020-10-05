<?php
/**
 * @file class.InputNote.php
 * @brief Contiene la definizione ed implementazione della classe Gino.InputNote
 *
 * @copyright 2019 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 */
namespace Gino;

/**
 * @brief Classe per la gestione delle note a campo di un input form
 * 
 * @copyright 2019 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 */
class InputNote {

	private $_view;
	
	/**
	 * @brief Valore id del @a collapse
	 * @var string
	 */
	private $_collapse_id;
	
	/**
	 * @brief Button Label
	 * @var string
	 */
	private $_button_label;
	
	/**
	 * @brief Annotazioni
	 * @var string
	 */
	private $_note;
	
	/**
     * @brief Costruttore
     * 
     * @param array $options
     *   array associativo di opzioni
     *   - @b collapse_id (string)
     *   - @b button_label (string)
     *   - @b note (string)
     * @return void
     */
    function __construct($options=[]){

        $this->_view = VIEWS_DIR.OS.'inputs';
        
        $collapse_id = gOpt('collapse_id', $options, 'gino');
        $this->_note = gOpt('note', $options, null);
        $this->_button_label = gOpt('button_label', $options, _("note del campo"));
        
        $this->setNameReference($collapse_id);
    }
    
    private function setNameReference($name) {
        $this->_collapse_id = 'collapse'.$name;
    }
    
    /**
     * @brief Renderizza le note
     * @return string
     */
    public function render() {
        
        $view = new View($this->_view, 'footnote');
        
        $dict = [
            'note' => $this->_note,
            'collapse_id' => $this->_collapse_id,
            'button_label' => _("note del campo")
        ];
        return $view->render($dict);
    }
}
