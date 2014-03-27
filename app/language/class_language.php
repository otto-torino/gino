<?php
/**
 * @file class_language.php
 * @brief Contiene la classe language
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

require_once('class.Lang.php');

/**
 * @brief Libreria per la gestione delle lingue disponibili per le traduzioni
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 * 
 * Notes.
 * 
 * The public function of this class shows the menu where to select the language of navigation.
 * The administration part regards with the insertion and monification of languages.
 * 
 * The public view privilege and the administrative privilege are setted in the DB, and editable by the user in the sysClass class administration
 */
class language extends Controller {

	protected $_instance, $_instance_name;

	private $_options;
	public $_optionsLabels;
	private $_title;

	private static $tbl_translation = "language_translation";
	
	private $_flag_language;
	private $_flag_prefix;
	private $_flag_suffix;
	private $_language_codes, $_country_codes;
	
	function __construct(){
		
		parent::__construct();

		$this->_instance = 0;
		$this->_instance_name = $this->_class_name;

		//$this->setAccess();

		$this->_title = htmlChars($this->setOption('title', true));
		$this->_flag_language = $this->setOption('opt_flag');
		$this->_flag_prefix = "flag_";
		$this->_flag_suffix = ".gif";

		$this->_options = loader::load('Options', array($this->_class_name, $this->_instance));
		$this->_optionsLabels = array("title"=>_("Titolo"), "opt_flag"=>_("Bandiere come etichette"));
		
	}
	
	/**
	 * Elenco dei metodi che possono essere richiamati dal menu e dal template
	 * 
	 * @return array
	 */
	public static function outputFunctions() {

		$list = array(
			"choiceLanguage" => array("label"=>_("Scelta lingua"), "permissions"=>array()),
		);

		return $list;
	}

	/**
	 * Box di scelta lingua
	 * 
	 * @param boolean $p attiva un tag DIV con ID language
	 * @return string
	 */
	public function choiceLanguage($p=true){

		$GINO = $this->_registry->addCss($this->_class_www.'/language.css');
		if($this->_registry->sysconf->multi_language) {
			if($p) {
				$GINO .= "<section id=\"section_language\">\n";
				$GINO .= '<h1 class="hidden">' . $this->_title . '</h1>';
			}
      $codes = explode('_', $this->_registry->session->lng);
			$query_i = "SELECT label FROM ".TBL_LANGUAGE." WHERE active='1' AND language_code='".$codes[0]."' AND country_code='".$codes[1]."' ORDER BY language";
			$a_i = $this->_db->selectquery($query_i);
			$lngSupport = sizeof($a_i)>0 ? true:false;

			$query = "SELECT id, label, country_code, language_code FROM ".TBL_LANGUAGE." WHERE active='1' ORDER BY language";
			$a = $this->_db->selectquery($query);
			if(sizeof($a) > 0)
			{
				$list = '';
        foreach($a AS $b) {
          $lng = new Lang($b['id']);
					if($this->_flag_language) {
						$language = "<img src=\"".SITE_IMG.'/'.$this->_flag_prefix.htmlChars($b['label']).$this->_flag_suffix."\" />";
						$space = " ";
					}
					else {
						$language = htmlChars($b['label']);
						$space = "| ";
					}

					if(($lngSupport && $lng->code() == $this->_registry->session->lng) || (!$lngSupport && $lng->code()== $this->_registry->session->lngDft))
						$list .= "$space <span>$language</span> \n";
					else
						$list .= "$space <a href=\"".$this->_home."?lng=".$lng->code()."\">$language</a> \n";
				}
				$list = substr_replace($list, '', 0, 2);
				$GINO .= $list;
			}

			if($p) {
				$GINO .= "</section>\n";
			}

			return $GINO;
		}
	}
	
  public function manageLanguage() {

    $this->requirePerm('can_admin');

    $block = cleanVar($_GET, 'block', 'string', null);

    $link_options = "<a href=\"".$this->_home."?evt[$this->_class_name-manageLanguage]&block=options\">"._("Opzioni")."</a>";
    $link_dft = "<a href=\"".$this->_home."?evt[".$this->_class_name."-manageLanguage]\">"._("Gestione")."</a>";
    $sel_link = $link_dft;

    if($block=='options') {
      $content = $this->manageOptions();
      $sel_link = $link_options;
    }
    else {
      $content = $this->manageLang();
    }

    $dict = array(
      'title' => _('Lingue di sistema'),
      'links' => array($link_options, $link_dft),
      'selected_link' => $sel_link,
      'content' => $content
    );

    $view = new view();
    $view->setViewTpl('tab');

    return $view->render($dict);

  }

  private function manageLang() {

    $info = "<p>"._("Elenco di tutte le lingue supportate dal sistema, attivare quelle desiderate.</p>");
    $info .= "<p>"._("Una sola lingua può essere principale, ed è in quella lingua che avviene l'inserimento dei contenuti e la visualizzazione in assenza di traduzioni.")."</p>\n";

    $opts = array(
      'list_description' => $info
    );

    $admin_table = loader::load('AdminTable', array(
      $this
    ));

    if(isset($_POST['id'])) {
      if($_POST['main']) {
        Lang::resetMain();
      }
    }

    return $admin_table->backoffice('Lang', $opts);

  }
	
	/**
	 * Sostituisce un campo input con un campo editor
	 * 
	 * @see Form::editorHtml()
	 * @return string
	 * 
	 * Il metodo viene richiamato come callback di una request ajax (su formTranslation()) avviata dalla funzione javascript prepareTrlForm(). \n
	 * Se il campo input è di tipo editor, il metodo sovrascrive il campo input creato da formTranslation().
	 */
	public function replaceTextarea() {
	 
		$gform = new Form('gform', 'post', true);

		$type = cleanVar($_POST, 'type', 'string', '');
		
		if($type == $this->_fckeditor_field)
		{
	 		$field = cleanVar($_POST, 'field', 'string', '');
			$width = cleanVar($_POST, 'width', 'string', '');
	 		$fck_toolbar = cleanVar($_POST, 'fck_toolbar', 'string', '');

			return $gform->editorHtml('trnsl_'.$field, null, $fck_toolbar, $width, null, true);
		} else return null;
	}
}
?>
