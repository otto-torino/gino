<?php
/**
 * @file class_graphics.php
 * @brief Contiene la definizione ed implementazione della classe Gino.App.Graphics.graphics
 *
 * @copyright 2005-2018 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
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

require_once 'class.GraphicsItem.php';

/**
 * @brief Classe di tipo Gino.Controller per la gestione personalizzata degli header e footer del sistema
 *
 * Sono disponibili un header e un footer personalizzabili ed utilizzabili nella composizione del layout.
 * Un header/footer può essere di due tipologie:
 *   - grafica, prevede il caricamento di una immagine
 *   - codice, prevede l'inserimento di codice html
 *
 * @copyright 2005-2018 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class graphics extends \Gino\Controller {

    private $_title;

    /**
     * @brief Costruttore
     * @return void, istanza di Gino.App.Graphics.graphics
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
            "printHeader" => array("label"=>_("Header"), "permissions"=>array()),
        	"printFooter" => array("label"=>_("Footer"), "permissions"=>array()),
        );

        return $list;
    }
    
    public function printHeader() {
    	return $this->render(1);
    }
    
    public function printFooter() {
    	return $this->render(2);
    }
    
    private function isHeader($id) {
    	if($id == 1) {
    		return true;
    	}
    	else {
    		return false;
    	}
    }

    /**
     * @brief Stampa l'header/footer
     *
     * @param integer $id valore ID del record
     * @return string, html
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
        elseif($graphics_item->type == 2) {
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

        $link_dft = ['link' => $this->linkAdmin(), 'label' => _('Gestione')];
        $link_views = ['link' => $this->linkAdmin(array(), 'block=frontend'), 'label' => _('Frontend')];
        $sel_link = $link_dft;

        $block = \Gino\cleanVar($request->GET, 'block', 'string', '');

        if($block == 'frontend') {
            $backend = $this->manageFrontend();
            $sel_link = $link_views;
        }
        else {
            $admin_table = \Gino\Loader::load('AdminTable', array($this, array('delete_deny' => 'all', 'allow_insertion' => false)));
            
            $backend = $admin_table->backOffice(
            	'GraphicsItem',
            	array(
            		'list_display' => array('id', 'description', array('member'=>'getTypeName', 'label'=>_('Tipo')), 'image'),
                	'list_description' => _("L'header e il footer possono essere definiti come immagine (che deve essere caricata) oppure come codice html.")
            	),
            	array(
            		'removeFields' => array('name')
            	),
            	array(
            		'html' => array(
            			'typeoftext' => 'html',
            			'trnsl' => false,
            			'cols' => 40,
            			'rows' => 10
            		)
            	)
            );
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
        $view->setViewTpl('tabs');

        $document = new Document($view->render($dict));
        return $document();
    }

}
