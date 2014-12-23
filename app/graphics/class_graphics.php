<?php
/**
 * @file class_graphics.php
 * @brief Contiene la definizione ed implementazione della classe Gino.App.Graphics.graphics
 *
 * @copyright 2005-2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

/**
 * @namespace Gino.App.Graphics
 * @description Namespace dell'applicazione Graphics, che gestisce in maniera semplificata header e footer
 */
namespace Gino\App\Graphics;

use \Gino\View;
use \Gino\Document;

require_once('class.GraphicsItem.php');

/**
 * @brief Classe di tipo Gino.Controller per la gestione personalizzata degli header e footer del sistema
 *
 * Sono disponibili 5 header e 5 footer completamente personalizzabili ed utilizzabili nella composizione del layout.
 * Un header/footer può essere di due tipologie:
 *   - grafica, prevede il caricamento di una immagine
 *   - codice, prevede l'inserimento di codice html
 *
 * @copyright 2005-2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class graphics extends \Gino\Controller {

    private $_title;

    /**
     * @brief Costruttore
     * @return istanza di Gino.App.Graphics.graphics
     */
    function __construct(){

        parent::__construct();

        $this->_title = _("Layout - header/footer");
    }
    /**
     * @brief Restituisce alcune proprietà della classe
     * @return array associativo contenente le viste
     */
    public static function getClassElements() {
        return array(
            'views' => array(
                'render.php' => _('Stampa l\'header o il footer')
            )
        );
    }

    /**
     * @brief Elenco dei metodi che possono essere richiamati dal menu e dal template
     * @return array, elenco metodi nella forma nome_metodo => array(label => string, permissions => array())
     */
    public static function outputFunctions() {

        $list = array(
            "printHeaderPublic" => array("label"=>_("Header Pubblico"), "permissions"=>array()),
            "printHeaderPrivate" => array("label"=>_("Header Privato"), "permissions"=>array()),
            "printHeaderAdmin" => array("label"=>_("Header Amministrazione"), "permissions"=>array()),
            "printHeaderMobile" => array("label"=>_("Header Dispositivo Mobile"), "permissions"=>array()),
            "printHeaderAdhoc" => array("label"=>_("Header Adhoc"), "permissions"=>array()),
            "printFooterPublic" => array("label"=>_("Footer Pubblico"), "permissions"=>array()),
            "printFooterPrivate" => array("label"=>_("Footer Privato"), "permissions"=>array()),
            "printFooterAdmin" => array("label"=>_("Footer Amministrazione"), "permissions"=>array()),
            "printFooterMobile" => array("label"=>_("Footer Dispositivo Mobile"), "permissions"=>array()),
            "printFooterAdhoc" => array("label"=>_("Footer Adhoc"), "permissions"=>array())
        );

        return $list;
    }

    /**
     * @brief Dice se l'oggetto rappresenta un header o no
     * @param int $id
     * @return bool
     */
    private function isHeader($id) {

        return !!($id < 6);
    }

    /**
     * @brief Header con valore ID 1
     *
     * @return html, header
     */
    public function printHeaderPublic() {
        return $this->render(1);
    }

    /**
     * @brief Header con valore ID 2
     *
     * @return html, header
     */
    public function printHeaderPrivate() {
        return $this->render(2);
    }

    /**
     * @brief Header con valore ID 3
     *
     * @return html, header
     */
    public function printHeaderAdmin() {
        return $this->render(3);
    }

    /**
     * @brief Header con valore ID 4
     *
     * @return html, header
     */
    public function printHeaderMobile() {
        return $this->render(4);
    }

    /**
     * @brief Header con valore ID 5
     *
     * @return html, header
     */
    public function printHeaderAdhoc() {
        return $this->render(5);
    }

    /**
     * @brief Header con valore ID 6
     *
     * @return html, header
     */
    public function printFooterPublic() {
        return $this->render('6');
    }

    /**
     * @brief Footer con valore ID 7
     *
     * @return html, footer
     */
    public function printFooterPrivate() {
        return $this->render('7');
    }

    /**
     * @brief Footer con valore ID 8
     *
     * @return html, footer
     */
    public function printFooterAdmin() {
        return $this->render('8');
    }

    /**
     * @brief Footer con valore ID 9
     *
     * @return html, footer
     */
    public function printFooterMobile() {
        return $this->render('9');
    }

    /**
     * @brief Footer con valore ID 10
     *
     * @return html, footer
     */
    public function printFooterAdhoc() {
        return $this->render('10');
    }

    /**
     * @brief Stampa l'header/footer
     *
     * @param integer $id valore ID del record
     * @return html
     */
    private function render($id) {

        if(!$id) return '';

        $graphics_item = new GraphicsItem($id);

        if($graphics_item->type == 1 && $graphics_item->image) 
        {
            $src = SITE_GRAPHICS."/".$graphics_item->image;
            if($this->isHeader($id)) {
                $buffer = "<a href=\"".HOME_FILE."\"><img src=\"$src\" alt=\""._("header")."\" /></a>\n";
            }
            else{
                $buffer = "<img src=\"$src\" alt=\""._("footer")."\" />\n";
            }
        }
        elseif($graphics_item->type==2) {
            $buffer = $graphics_item->html;
        }

        $view = new \Gino\View($this->_view_dir);
        $view->setViewTpl('render');
        $dict = array(
            'id' => "site_".($this->isHeader($id) ? "header" : "footer"),
            'content' => $buffer,
            'type' => $graphics_item->type,
            'header' => $this->isHeader($id),
            'img_path' => $graphics_item->image ? SITE_GRAPHICS."/$graphics_item->image" : null,
            'code' => $graphics_item->html
        );

        return $view->render($dict);
    }

    /**
     * @brief Interfaccia di amministrazione modulo
     * @param \Gino\Http\Request $request istanza di Gino.Http.Request
     * @return \Gino\Http\Response
     */
    public function manageGraphics(\Gino\Http\Request $request) {

        $this->requirePerm('can_admin');

        $link_dft = sprintf('<a href="%s">%s</a>', $this->linkAdmin(), _('Gestione'));
        $link_views = sprintf('<a href="%s">%s</a>', $this->linkAdmin(array(), 'block=frontend'), _('Frontend'));
        $sel_link = $link_dft;

        $block = \Gino\cleanVar($request->GET, 'block', 'string', '');

        if($block == 'frontend') {
            $backend = $this->manageFrontend();
            $sel_link = $link_views;
        }
        else {
            $info = "<p>"._("Elenco di tutte le lingue supportate dal sistema, attivare quelle desiderate.</p>");
            $info .= "<p>"._("Una sola lingua può essere principale, ed è in quella lingua che avviene l'inserimento dei contenuti e la visualizzazione in assenza di traduzioni.")."</p>\n";

            $opts = array(
                'list_display' => array('id', 'description', 'type', 'image'),
                'list_description' => $info
            );

            $opts_form = array(
                'removeFields' => array('name')
            );

            $admin_table = \Gino\Loader::load('AdminTable', array(
                $this,
                array(
                    'allow_insertion' => FALSE,
                    'delete_deny' => 'all',
                )
            ));

            $backend = $admin_table->backoffice('GraphicsItem', $opts, $opts_form);
        }

        if(is_a($backend, '\Gino\Http\Response')) {
            return $backend;
        }

        $dict = array(
            'title' => _('Header & Footer'),
            'links' => array($link_views, $link_dft),
            'selected_link' => $sel_link,
            'content' => $backend
        );

        $view = new View();
        $view->setViewTpl('tab');

        $document = new Document($view->render($dict));
        return $document();
    }

}
