<?php
/**
 * @file class.View.php
 * @brief Contiene la definizione ed implementazione della classe Gino.View.
 *
 * @copyright 2013-2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

/**
 * @brief Gestisce le viste, impostando il template e ritornando l'output
 * 
 * @copyright 2013-2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class View {

    /**
     * oggetto che contiene il context della view 
     */
    protected $_data;

    /**
     * istanze del registry 
     */
    protected $_registry;

    /**
     * percorso alla cartella contenente le view specifiche del modulo
     */
    protected $_view_folder;

    /**
     * percorso alla cartella che contine le view di sistema
     */
    protected $_dft_view_folder;

    /**
     * path alla view correntemente in uso
     */
    protected $_view_tpl;

    /**
     * @brief Costruttore
     * @param string $view_folder path alla directory in cui cercare il template, default null.
     *                            Se ha valore null il template viene cercato all'interno della directory dei template generali di gino.
     * @param string $tpl nome template, default null
     * @return istanza di Gino.View
     */
    function __construct($view_folder = null, $tpl = null) {

        $this->_data = new \stdClass();
        $this->_registry = registry::instance();
        $this->_view_folder = $view_folder;
        $this->_dft_view_folder = VIEWS_DIR;

        if($tpl) {
            $this->setViewTpl($tpl);
        }
    }

    /**
     * @brief Setta il template della view
     *
     * Cerca il template nella directory che contiene le view specifiche e se non lo trova prosegue la ricerca nella directory delle view di sistema 
     *
     * @param string $view_name il nome del template
     * @param array $opts
     * @return void o Exception se il template non viene trovato
     */
    public function setViewTpl($view_name, $opts = null) {

        if(!is_null($this->_view_folder) && is_readable($this->_view_folder.OS.$view_name.".php")) {
            $this->_view_tpl = $this->_view_folder.OS.$view_name.".php";
        }
        elseif(is_readable($this->_dft_view_folder.OS.$view_name.".php")) {
            $this->_view_tpl = $this->_dft_view_folder.OS.$view_name.".php";
        }
        else {
            throw new \Exception(sprintf(_("Impossibile caricare la vista %s"), $view_name));
        }
    }

    /**
     * @brief Aggiunge variabili al context
     *
     * @param string $name il nome della variabile da utilizzare nella vista 
     * @param mixed $value il valore della variabile
     * @return void
     */
    public function assign($name, $value) {
        $this->_data->$name = $value;
    }

    /**
     * @brief Ritorna l'output generato dalla vista
     * @param array data dizionario delle variabili da passare
     * @return l'output della vista
     */
    public function render($data = null) {

        $buffer = '';

        if($data) {
            foreach($data as $k => $v) $$k = $v;
        }
        else {
            foreach($this->_data as $k=>$v) $$k=$v;
        }

        ob_start();
        include($this->_view_tpl);
        $buffer .= ob_get_contents();
        ob_clean();

        return $buffer;
    }
}
