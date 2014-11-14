<?php
/**
 * @file class_graphics.php
 * @brief Contiene la classe graphics
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino\App\Graphics;

require_once('class.GraphicsItem.php');

/**
 * @brief Gestione personalizzata degli header e footer del sistema
 * 
 * Sono disponibili 5 header e 5 footer completamente personalizzabili ed utilizzabili nella composizione del layout.
 * Un header/footer può essere di due tipologie:
 *   - grafica, prevede il caricamento di una immagine
 *   - codice, prevede l'inserimento di codice html
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class graphics extends \Gino\Controller {

	private $_title;
	private $_tbl_graphics;
	private $_block;
	
	function __construct(){
		
		parent::__construct();

		$this->_title = _("Layout - header/footer");
		$this->_tbl_graphics = 'sys_graphics';
		
		$this->_block = \Gino\cleanVar($_REQUEST, 'block', 'string', '');
	}

  public static function getClassElements() {
    return array(
      'views' => array(
        'render.php' => _('Stampa l\'header o il footer')
      )
    );
  }
	
	/**
	 * Elenco dei metodi che possono essere richiamati dal menu e dal template
	 * 
	 * @return array
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

	private function isHeader($id) {
		
		return $id < 6 ? true : false;
	}

	/**
	 * Interfaccia all'header con valore ID 1
	 * 
	 * @see $_access_base
	 * @return string
	 */
	public function printHeaderPublic() {
		return $this->render(1);
	}
	
	/**
	 * Interfaccia all'header con valore ID 2
	 * 
	 * @see $_access_base
	 * @return string
	 */
	public function printHeaderPrivate() {
		return $this->render(2);
	}
	
	/**
	 * Interfaccia all'header con valore ID 3
	 * 
	 * @see $_access_base
	 * @return string
	 */
	public function printHeaderAdmin() {
		return $this->render(3);
	}
	
	/**
	 * Interfaccia all'header con valore ID 4
	 * 
	 * @see $_access_base
	 * @return string
	 */
	public function printHeaderMobile() {
		return $this->render(4);
	}

	/**
	 * Interfaccia all'header con valore ID 5
	 * 
	 * @see $_access_base
	 * @return string
	 */
	public function printHeaderAdhoc() {
		return $this->render(5);
	}

	/**
	 * Interfaccia al footer con valore ID 6
	 * 
	 * @see $_access_base
	 * @return string
	 */
	public function printFooterPublic() {
		return $this->render('6');
	}
	
	/**
	 * Interfaccia al footer con valore ID 7
	 * 
	 * @see $_access_base
	 * @return string
	 */
	public function printFooterPrivate() {
		return $this->render('7');
	}
	
	/**
	 * Interfaccia al footer con valore ID 8
	 * 
	 * @see $_access_base
	 * @return string
	 */
	public function printFooterAdmin() {
		return $this->render('8');
	}

	/**
	 * Interfaccia al footer con valore ID 9
	 * 
	 * @see $_access_base
	 * @return string
	 */
	public function printFooterMobile() {
		return $this->render('9');
	}

	/**
	 * Interfaccia al footer con valore ID 10
	 * 
	 * @see $_access_base
	 * @return string
	 */
	public function printFooterAdhoc() {
		return $this->render('10');
	}

	/**
	 * Prepara l'header/footer
	 * 
	 * @param integer $id valore ID del record
	 * @return string
	 */
	private function render($id) {
	
		if(!$id) return '';
		
		$graphics_item = new GraphicsItem($id);

		if($graphics_item->type==1 && $graphics_item->image) 
		{
			$src = SITE_GRAPHICS."/$graphics_item->image";
			if($this->isHeader($id)) {
				$buffer = "<a href=\"".$this->_home."\"><img src=\"$src\" alt=\""._("header")."\" /></a>\n";
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

  public function manageGraphics() {

    $this->requirePerm('can_admin');

    $link_dft = "<a href=\"".$this->_home."?evt[".$this->_class_name."-manageGraphics]\">"._("Gestione")."</a>";
    $link_views = "<a href=\"".$this->_home."?evt[".$this->_class_name."-manageGraphics]&block=frontend\">"._("Frontend")."</a>";
    $sel_link = $link_dft;

    if($this->_block == 'frontend') {
      $buffer = $this->manageFrontend();
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
          'allow_insertion' => false,
          'delete_deny' => 'all',
        )
      ));

      $buffer = $admin_table->backoffice('GraphicsItem', $opts, $opts_form);
    }

    $dict = array(
      'title' => _('Header & Footer'),
      'links' => array($link_views, $link_dft),
      'selected_link' => $sel_link,
      'content' => $buffer
    );

    $view = new \Gino\View();
    $view->setViewTpl('tab');

    return $view->render($dict);

  }

}
