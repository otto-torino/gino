<?php
/**
 * @file class.Modal.php
 * @brief Contiene la definizione ed implementazione della classe Gino.Modal
 * 
 * @copyright 2018 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

/**
 * @brief Classe per l'utilizzo delle modali
 * @description 
 * 
 * @copyright 2018 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 * 
 * #MODO D'USO
 * 
 * ##Case 1: il contenuto viene caricato dinamicamente recuperando i dati da una sorgente esterna
 * (Gino.AdminTable::adminList -> opt advanced_export -> view admin_table_list)
 * 
 * 1. impostare il link per l'apertura della modale
 * @code
 * <span class="icon fa fa-download fa-2x <?= $trigger_modal ?> link"></span>
 * @endcode
 * 
 * 2. inserire la renderizzazione della modale e lo script per caircare dinamicamente i contenuti
 * @code
 * <?= $render_modal ?>
 * <?= $script_modal ?>
 * @endcode
 * 
 * Le variabili possono essere così definite:
 * @code
 * $modal = new Modal(['modal_id' => "myModal", 'modal_title_id' => 'myModalTitle']);
 * $modal->setModalTrigger('newClassName'); // if you want overwrite the default name
 * $this->_view->assign('trigger_modal', $modal->getModalTrigger());
 * $this->_view->assign('script_modal', $modal->loadDinamycData($link_modal));
 * $this->_view->assign('render_modal', $modal->render('Modal Title', null));
 * @endcode
 * 
 * ##Case 2: la modale è già definita nella pagina (trigger link e modale)
 * (Gino.Input::input_file -> preview image)
 * 
 * @code
 * $modal = new \Gino\Modal([
 *   'modal_id' => $name.'ModalCenter',
 *   'modal_title_id' => $name.'ModalCenterTitle',
 * ]);
 * $value_link = $modal->trigger($label, ['tagname' => 'button', 'class' => 'btn btn-secondary btn-sm']);
 * $value_link .= $modal->render(_("Media"), "HTML data");
 * @endcode
 * 
 */
class Modal {

    protected $_modal_id;
    protected $_modal_title_id;
    protected $_modal_trigger;
    protected $_popup_trigger;
    
    function __construct($options=[]) {
        
        $this->_modal_id = gOpt('modal_id', $options, 'exampleModalCenter');
        $this->_modal_title_id = gOpt('modal_title_id', $options, 'exampleModalCenterTitle');
        
        $this->_modal_trigger = "openModal";
        $this->_popup_trigger = "openPopup";
    }
    
    /**
     * @brief Imposta il nome della classe che innesta con l'onclick l'apertura della modale
     * @param string $value
     */
    public function setModalTrigger($value) {
        
        $this->_modal_trigger = $value;
    }
    
    /**
     * @brief Riporta il nome della classe che innesta con l'onclick l'apertura della modale
     * @return string
     */
    public function getModalTrigger() {
        
        return $this->_modal_trigger;
    }
    
    /**
     * @brief Imposta il nome della classe che innesta con l'onclick l'apertura del popup
     * @param string $value
     */
    public function setPopupTrigger($value) {
        
        $this->_popup_trigger = $value;
    }
    
    /**
     * @brief Riporta il nome della classe che innesta con l'onclick l'apertura del popup
     * @return string
     */
    public function getPopupTrigger() {
        
        return $this->_popup_trigger;
    }
    
    /**
     * @brief Link/button for trigger the modal
     * @param string $label
     * @param array $options
     *   - @b class (string)
     *   - @b tagname (string): nome del tag; valori disponibili @a button (default), @a span
     * @return string
     */
    public function trigger($label, $options=[]) {
        
        $class = gOpt('class', $options, null);
        $tagname = gOpt('tagname', $options, 'button');
        
        if($tagname == 'button') {
            $tag = 'button';
            if(!$class) {
                $class = 'btn btn-primary';
            }
            $add = "type=\"button\"";
        }
        elseif ($tagname == 'span') {
            $tag = 'span';
            $add = null;
        }
        
        $buffer = "<".$tag." $add class=\"$class\"
    	data-toggle=\"modal\" data-target=\"#".$this->_modal_id."\">";
        $buffer .= $label."</".$tag.">";
        
        return $buffer;
    }
    
    /**
     * @brief Script che permette di visualizzare la modale recuperando i contenuti in modo dinamico da un indirizzo web
     * @param string $url
     * @return string
     */
    public function loadDinamycData($url) {
        
        $trigger = '.'.$this->_modal_trigger;
        
        $buffer = "
<script>
(function($) {
	$('".$trigger."').on('click',function(){
        $('.modal-body').load('".$url."',function(){
            $('#".$this->_modal_id."').modal({show:true});
        });
    });
})(jQuery);
</script>";
        return $buffer;
    }
    
    /**
     * @brief Renderizza la modale
     *
     * @param string $title
     * @param string $body
     * @param array $options
     *   - @b vertically_centered (boolean): add .modal-dialog-centered to .modal-dialog to vertically center the modal
     *   - @b close_button (boolean)
     *   - @b save_button (boolean)
     * @return string
     */
    public function render($title, $body, $options=[]) {
        
        $vertically_centered = gOpt('vertically_centered', $options, true);
        $close_button = gOpt('close_button', $options, true);
        $save_button = gOpt('save_button', $options, false);
        
        $view = new View(null, 'modal');
        $dict = [
            'vertically_centered' => $vertically_centered,
            'modal_id' => $this->_modal_id,
            'modal_title_id' => $this->_modal_title_id,
            'title' => $title,
            'body' => $body,
            'close_button' => $close_button,
            'save_button' => $save_button,
        ];
        
        return $view->render($dict);
    }
    
    /**
     * @brief Script che apre un popup recuperando i contenuti in modo dinamico da un indirizzo web
     * @description Dynamic Bootstrap Modal with Different URL
     * 
     * @param string $url
     * @return string
     */
    public function scriptOpenPopup($url) {
        
        $trigger = '.'.$this->_popup_trigger;
        
        $buffer = "
<script>
(function($) {
	$(document).ready(function(){
        $('".$trigger."').on('click',function(){
            var dataURL = $(this).attr('data-href');
            $('.modal-body').load(dataURL,function(){
                $('#".$this->_modal_id."').modal({show:true});
            });
        });
    });
})(jQuery);
</script>";
        return $buffer;
    }
    
    /**
     * @brief Link che apre un popup
     * @description In data-href attribute, the URL needs to be specified, from where you want to load the content.
     * 
     * @param string $url
     * @param string $string
     * @return string
     */
    public function linkOpenPopup($url, $string) {
        
        return "<a href=\"javascript:void(0);\" data-href=\"$url\" class=\"".$this->_popup_trigger."\">$string</a>";
    }
    
    /**
     * @brief Renderizza il popup
     * 
     * @param string $title
     * @param string $body
     * @param array $options
     * @return string
     */
    public function renderOpenPopup($title, $body, $options=[]) {
        
        $view = new View(null, 'modal_popup');
        $dict = [
            'modal_id' => $this->_modal_id,
            'title' => $title,
            'body' => $body,
        ];
        
        return $view->render($dict);
    }
}
