<?php
/**
 * @file class_page.php
 * @brief Contiene la classe page
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

/**
 * @brief Libreria dedicata alla gestione delle pagine dell'applicazione
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 * 
 * I contenuti non testuali delle pagine sono strutturati in directory secondo lo schema:
 *   - contents/
 *   - page/
 *   - [module_id]/
 *   - [block_id]/
 *   - [file]
 */
class page extends AbstractEvtClass{

	protected $_instance, $_instanceName;
	
	private $_title, $_block_title, $_block_chars, $_read_all, $_block_media;
	private $_options;
	public $_optionsLabels;

	private $_access_module;
	private $_group_1, $_group_2;
	
	private $_tbl_item, $_tbl_content, $_tbl_content_add, $_tbl_content_file, $_tbl_content_layout;
	
	private $_extension_content1, $_extension_content2, $_extension_content3, $_extension_attach, $_extension_include;
	private $_text_multifile;
	private $_max_item_list;
	private $_image_width;
	private $_fck_toolbar, $_fck_width, $_fck_height;
	
	private $_layout_text, $_layout_img, $_layout_img_text, $_layout_text_img;
	private $_layout_link_file, $_layout_single_include, $_layout_single_code;
	
	private $_block_page, $_block_content, $_block_lng, $_block_module;
	private $_js_var_form;

	private $_action, $block;
	
	function __construct(){
		
		parent::__construct();

		$this->_instance = 0;
		$this->_instanceName = $this->_className;

		$this->_access_module = $this->_access_admin;
		$this->setAccess();
		$this->setGroups();

		// options
		$this->_title = htmlChars($this->setOption('title', true));
		$this->_block_title = $this->setOption('block_title');
		$this->_block_chars = $this->setOption('block_chars');
		$this->_read_all = $this->setOption('read_all');
		$this->_block_media = $this->setOption('block_media');
	
		$this->_options = new options($this->_className, $this->_instance);
		$this->_optionsLabels = array(
			"title"=>_("Titolo"), 
			"block_title"=>_("Visualizza titolo in modalità blocco"), 
			"block_chars"=>array(_("Numero di caratteri in modalità blocco"), _("attivo solo se si setta a 'si' l'opzione successiva")),
			"read_all"=>array(_("Link 'Leggi tutto' in modalità blocco"), _("visualizza solo il numero di caratteri indicato nell'opzione precedente ed inserisce un link alla pagina completa")),
			"block_media"=>_("Visualizza immagini in modalità blocco")
		);

		$this->_tbl_item = $this->_tbl_page;
		$this->_tbl_content = 'page_block';
		$this->_tbl_content_add = 'page_block_add';
		$this->_tbl_content_file = 'page_block_file';
		$this->_tbl_content_layout = 'page_layout';
		
		$this->_extension_content1 = array('gif','jpg','png');	// for media content
		$this->_extension_content2 = array('swf');
		$this->_extension_content3 = array('mp4','webm','ogv','mov');
		$this->_extension_attach = array('rtf','txt','pdf','doc');	// for attach file content
		$this->_extension_include = array('html','htm','txt');	// for single page (include file)
		$this->_text_multifile = false;
		
		$this->_image_width = 100;	// preview
		$this->_fck_toolbar = 'Full';
		$this->_fck_width = '98%';
		$this->_fck_height = '300';
		
		$this->_layout_text = 1; $this->_layout_img = 2; $this->_layout_img_text = 3; $this->_layout_text_img = 4;
		$this->_layout_link_file = 5; $this->_layout_single_include = 8; $this->_layout_single_code = 9;
		
		$this->_block_page = 'page';
		$this->_block_content = 'content';
		$this->_block_lng = 'language';
		$this->_block_module = 'master';
		
		$this->_js_var_form = cleanVar($_GET, 'var1', 'string', '');
		
		$this->_action = cleanVar($_REQUEST, 'action', 'string', '');
		$this->_block = cleanVar($_REQUEST, 'block', 'string', '');
	}
	
	/**
	 * Gruppi per accedere alle funzionalità del modulo
	 * 
	 * @b _group_1: redazione
	 * @b _group_2: redazione contenuti
	 */
	private function setGroups(){
		
		// Redazione
		$this->_group_1 = array($this->_list_group[0], $this->_list_group[1]);
		// Redazione contenuti
		$this->_group_2 = array($this->_list_group[0], $this->_list_group[1], $this->_list_group[2]);
	}
	
	/**
	 * Controlla se un utente può accedere ai contenuti di una pagina
	 * 
	 * @see access::AccessVerifyPage()
	 * @see access::AccessVerifyPageIf()
	 * @param integer $page_id valore ID della pagina
	 * @param boolean $block blocco di pagina o pagina completa
	 * @return boolean o redirect
	 */
	private function accessPage($page_id, $block=false){
		
		$module = $this->_db->getFieldFromId($this->_tbl_item, 'module', 'item_id', $page_id);
		
		if(empty($module)) EvtHandler::HttpCall($this->_home, $this->_className.'-notExistPage', '');
		
		if($block)
			return $this->_access->AccessVerifyPageIf($module);
		else
			$this->_access->AccessVerifyPage($module);
	}
	
	/**
	 * Stampa l'errore di pagina non disponibile
	 * 
	 * @return string
	 */
	public function notExistPage(){
		
		$data = $this->notExistPageData();
		echo $data;
	}
	
	/**
	 * Controlla se un utente possiede un ruolo valido per accedere a una pagina
	 *  
	 * @param integer $id valore ID della pagina
	 * @return boolean
	 */
	public function checkReadPermission($id) {
		
		$query = "SELECT role1 FROM ".TBL_MODULE." WHERE id='".$this->_db->getFieldFromId($this->_tbl_page, 'module', 'item_id', $id)."'";
		$a = $this->_db->selectquery($query);
		$role = (sizeof($a) > 0)? $a[0]['role1']:0;
		
		return ($this->_session_role <= $role)? true:false;
	}

	private function notExistPageData(){
	
		$GINO = "<div class=\"area\">\n";
		$GINO .= "<div class=\"error\">"._("La pagina richiesta non è disponibile.")."</div>";
		$GINO .= "</div>\n";
		
		return $GINO;
	}
	
	/**
	 * Avvia il downolad il un file allegato (in un blocco)
	 * 
	 * @return void
	 */
	public function downloader(){
		
		$doc_id = cleanVar($_GET, 'id', 'int', '');
		
		if(!empty($doc_id))
		{
			$query = "SELECT item, filename FROM ".$this->_tbl_content." WHERE content_id='$doc_id'";
			$a = $this->_db->selectquery($query);
			if(sizeof($a) > 0)
			{
				foreach($a AS $b)
				{
					$item = $b['item'];
					$filename = htmlInput($b['filename']);
					
					$directory = $this->pathBlockDir($doc_id, 'abs');
					$full_path = $directory.$filename;
					
					download($full_path);
					exit();
				}
			}
			else exit();
		}
		else exit();
	}
	
	private function nameLayout($id){
		
		$name = htmlChars($this->_trd->selectTXT($this->_tbl_content_layout, 'name', $id));
		
		return $name;
	}
	
	private function typePage($item_id){
		
		$query = "SELECT layout FROM ".$this->_tbl_content." WHERE item='$item_id' AND order_list='1'";
		$a = $this->_db->selectquery($query);
		if(sizeof($a) > 0)
		{
			foreach($a AS $b)
			{
				$layout = htmlChars($b['layout']);
			}
		}
		else $layout = '';
		
		return $layout;
	}
	
	private function namePageDir($page_id){
	
		$query = "SELECT m.directory FROM ".$this->_tbl_module." AS m, ".$this->_tbl_item." AS i
		WHERE i.item_id='$page_id' AND i.module=m.id";
		$a = $this->_db->selectquery($query);
		if(sizeof($a) > 0)
		{
			foreach($a AS $b)
			{
				$directory = $b["directory"];
			}
		}
		else $directory = '';
		
		return $directory;
	}
	
	/**
	 * Percorso alla directory dei contenuti
	 *
	 * @param integer $content_id valore ID del blocco
	 * @param string $type tipo di percorso da esportare, assoluto (abs) o relativo (rel)
	 * @return string
	 */
	private function pathBlockDir($content_id, $type){
	
		$page_id = $this->_db->getFieldFromId($this->_tbl_content, 'item', 'content_id', $content_id);
		$page_dir = $this->namePageDir($page_id);
		
		if($type == 'abs')
		{
			$directory = $this->_data_dir.$this->_os.$page_dir.$this->_os.$content_id.$this->_os;
		}
		elseif($type == 'rel')
		{
			$directory = $this->_data_www.'/'.$page_dir.'/'.$content_id.'/';
		}
		
		return $directory;
	}
	
	/**
	 * Caricamento delle varibili nel registro
	 * 
	 * @param integer $id valore ID della pagina
	 */
	private function addRegistry($id) {

		$reg_title = htmlChars(pub::variable('head_title'))." - ".htmlChars($this->_trd->selectTXT($this->_tbl_item, 'title', $id, 'item_id'));
		
		$query = "SELECT content_id, layout, img, link, filename FROM ".$this->_tbl_content." WHERE item='$id' AND text!='' ORDER BY order_list LIMIT 0,1";
		$a = $this->_db->selectquery($query);
		if(sizeof($a) > 0)
		{
			$content_id = htmlChars($a[0]['content_id']);
			$content_text = htmlChars($this->_trd->selectTXT($this->_tbl_content, 'text', $content_id, 'content_id'), $content_id);
			$reg_description = cutHtmlText($content_text, 500, '...', true, false, true);
		}
		else 
			$reg_description = htmlChars($this->_trd->selectTXT($this->_tbl_item, 'subtitle', $id, 'item_id'));
		
		$reg_image_src = is_file(SITE_ROOT.OS."img".OS."logo.jpg") ? $this->_url_root.SITE_WWW."/img/logo.jpg" : null;
		
		$registry = registry::instance();
		$registry->description = $reg_description;
		$registry->addMeta(array('name'=>'title', 'content'=>$reg_title));
		$registry->addHeadLink(array('rel'=>'image_src', 'href'=>$reg_image_src));
	}

	/**
	 * Interfaccia per visualizzare la pagina come blocco
	 * 
	 * @see viewItem()
	 * @param integer $item_id valore ID della pagina
	 * @return string
	 */
	public function blockItem($item_id=null){

		if(!$item_id) $item_id = cleanVar($_GET, 'id', 'int', '');
		
		if($this->accessPage($item_id, true))
			return $this->viewItem($item_id, 'block');
		else
			return null;
	}
	
	/**
	 * Interfaccia per visualizzare la pagina completa
	 * 
	 * @see addRegistry()
	 * @see viewItem()
	 * @param integer $item_id valore ID della pagina
	 * @return string
	 */
	public function displayItem($item_id=null){

		if(!$item_id) $item_id = cleanVar($_GET, 'id', 'int', '');
		
		$this->accessPage($item_id);
		
		$this->addRegistry($item_id);
		return $this->viewItem($item_id, 'page');
	}
	
	/**
	 * Visualizzazione pagina
	 * 
	 * @see selectMedia()
	 * @param integer $item_id valore ID della pagina
	 * @param string $style modo di visualizzazione
	 *   - @a block: come blocco
	 *   - @a page: come pagina completa
	 * @return string
	 */
	private function viewItem($item_id=null, $style=null){
	
		$buffer = '';	
		$social = $this->_db->getFieldFromId($this->_tbl_item, 'social', 'item_id', $item_id);
		$caching = $this->_db->getFieldFromId($this->_tbl_item, 'cache', 'item_id', $item_id);

		$cache = new outputCache($buffer, $caching ? true : false);
		if($cache->start('page', $item_id.$this->_lng_nav, $caching)) {
			$GINO = '';

			$style_par = cleanVar($_GET, 'par_style', 'string', '');
			$id_par = cleanVar($_GET, 'par_id', 'int', '');

			if(!empty($style_par)) $style = $style_par;	// Page Gallery
			if(!empty($id_par)) $item_id = $id_par;
		
			if(!empty($item_id))
			{
				$query = "SELECT view_title FROM ".$this->_tbl_item." WHERE item_id='$item_id'";
				$a = $this->_db->selectquery($query);
				if(sizeof($a) > 0)
					foreach($a AS $b)
						$view = $b['view_title'];
			
				if(!empty($id_par)) $view = 'no';
			
				$title = htmlChars($this->_trd->selectTXT($this->_tbl_item, 'title', $item_id, 'item_id'));
				$text = htmlChars($this->_trd->selectTXT($this->_tbl_item, 'subtitle', $item_id, 'item_id'));

				$htmlsection = new htmlSection(array('id'=>"page_".$item_id,'class'=>'public', 'headerTag'=>'header'));
				if(($view=='yes' && $style=='page')||($view=='yes' && $style=='block' && $this->_block_title)) $htmlsection->headerLabel = $title;
			
				$GINO = '';
				if((($view=='yes' && $style=='page')||($view=='yes' && $style=='block' && $this->_block_title)) && !empty($text))
					$GINO .= "<div class=\"subtitle\">$text</div>\n";
				
				$query = "SELECT content_id, layout, img, link, filename FROM ".$this->_tbl_content." WHERE item='$item_id' ORDER BY order_list";
				$a = $this->_db->selectquery($query);
				if(sizeof($a) > 0)
				{
					foreach($a AS $b)
					{
						$content_id = htmlChars($b['content_id']);
						$layout = htmlChars($b['layout']);
						$img = htmlChars($b['img']);
						$link = htmlChars($b['link']);
						$filename = htmlChars($b['filename']);
					
						$content_text = htmlChars($this->_trd->selectTXT($this->_tbl_content, 'text', $content_id, 'content_id'), $content_id);
						$directory1 = $this->pathBlockDir($content_id, 'rel');
						$directory2 = $this->pathBlockDir($content_id, 'abs');
					
						$content = '';
						if(sizeof($a) == 1 AND $layout == $this->_layout_single_include)
						{
							$include_file = $this->_trd->selectTXT($this->_tbl_content, 'text', $content_id, 'content_id');
							$path_file = $directory2.$include_file;
						
							if(file_exists($path_file))
							{
								$fp = @fopen($path_file, 'r');
							
								if(!$fp) $GINO .= _("impossibile aprire il file.");
								else
								{
									$line = '';
									while(!feof($fp))
									{
										$line .= fgets($fp);
									}
									@fclose($fp);
									$content .= $line;
								}
							}
						}
						elseif(sizeof($a) == 1 AND $layout == $this->_layout_single_code)
						{
							$content .= $content_text;
						}
						elseif($layout == $this->_layout_text)
						{
							if(!empty($content_text)) $content .= $content_text;
						}
						elseif($layout == $this->_layout_img)
						{
							if(!empty($img))
							{
								$content .= $this->selectMedia($content_id, $img, $directory1, $link);
							}
						}
						elseif($layout == $this->_layout_img_text)
						{
							if(!empty($img))
							{
								$content .= "<div class=\"layout_page1\">";
								$content .= $this->selectMedia($content_id, $img, $directory1, $link);
								$content .= "</div>\n";
							}
						
							if(!empty($content_text)) $content .= "<div class=\"layou_page_text\">".$content_text."</div>";
							$content .= "<div class=\"null\"></div>";
						}
						elseif($layout == $this->_layout_text_img)
						{
							if(!empty($img))
							{
								$content .= "<div class=\"layout_page2\">";
								$content .= $this->selectMedia($content_id, $img, $directory1, $link);
								$content .= "</div>\n";
							}
							
							if(!empty($content_text)) $content .= "<div class=\"layout_page_text\" style=\"text-align:justify\">".$content_text."</div>";
							$content .= "<div class=\"null\"></div>";
						}
						elseif($layout == $this->_layout_link_file)
						{
							if(!empty($content_text)) $content .= $content_text;
						
							if(!empty($filename))
							{
								$content .= "<div class=\"layout_page3\">";
								$content .= "<a href=\"".$this->_home."?evt[".$this->_className."-downloader]&amp;id=$content_id\">$filename</a>";
								$content .= "</div>\n";
							}
						}

						if($style=='block' && $this->_read_all && $this->_block_chars) {
							$ending = "<br/><a href=\"$this->_home?evt[page-displayItem]&id=$item_id\">"._("leggi tutto")."</a>";
							$content = cutHtmlText($content, $this->_block_chars, $ending, false, false, !$this->_block_media);
							$content .= "<div class=\"null\"></div>";
						}
						elseif($style=='block' && !$this->_block_media) {
							$rexp = "/<img\s*[^>]*\/>/is";
							$content = preg_replace($rexp, "", $content);
						}
						$GINO .= $content;
					}
					if($social=='yes') {
						$GINO .= shareAll("all", $this->_url_root.SITE_WWW."/".$this->_plink->aLink($this->_instanceName, 'displayItem', array("id"=>$item_id)), $title);
					}
				}
				else
				{
					$GINO .= "<p>"._("elementi ancora da inserire.")."</p>\n";
				}
			}
		
			$htmlsection->content = $GINO;
		
			$GINO = $htmlsection->render();

			$cache->stop($GINO);
		}

		return $buffer;
	}
	
	/**
	 * Mostra l'indirizzo di una pagina
	 * 
	 * @return string
	 */
	public function textLink(){
	
		$code = cleanVar($_GET, 'code', 'int', '');
		
		$path = $this->_plink->aLink('page', 'displayItem', "id=$code");
		$path_html = "&#60;a href=\"".$path."\"&#62;"._("<b>testo da sostituire</b>")."&#60;/a&#62;";
		
		$GINO = "<p>"._("Per creare un link a questa pagina utilizzare il codice seguente:")."</p>\n";
		
		$GINO .= $path_html;
		
		return $GINO;
	}
	
	private function infoPage(){
		
		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'h1', 'headerLabel'=>_("Informazioni")));
		$GINO = '';
		if($this->_multi_language == 'yes')
			$GINO .= "<p>"._("L'inserimento dei contenuti avviene nella lingua principale.")."</p>";
		
		$GINO .= "<p>"._("Una volta creata la pagina è possibile inserirla a menu.")."</p>";
		
		$htmlsection->content = $GINO;

		return $htmlsection->render();
	}
	
	/**
	 * Controlla se una pagina possiede delle sottopagine
	 * 
	 * @param integer $page valore ID della pagina
	 * @return boolean
	 */
	private function expandTree($page){
	
		$query = "SELECT item_id FROM ".$this->_tbl_item." WHERE parent='$page'";
		$a = $this->_db->selectquery($query);
		if(sizeof($a) > 0) return true; else return false;
	}
	
	/**
	 * Intestazione della pagina
	 * 
	 * Viene mostrata al posto del form se l'utente non è associato al gruppo @a _group_1
	 * 
	 * @param integer $id valore ID della pagina
	 * @return string
	 */
	private function textPage($id){
	
		$title = htmlChars($this->_trd->selectTXT($this->_tbl_item, 'title', $id, 'item_id'));
		$text = htmlChars($this->_trd->selectTXT($this->_tbl_item, 'text', $id, 'item_id'));

		$GINO = "<div class=\"area\">\n";		
		$GINO .= "<div class=\"area_title\">"._("intestazione")."</div>\n";
		$GINO .= "<p>"._("titolo").": $title</p>\n";
		$GINO .= "<p>"._("sottotitolo").": $text</p>\n";
		
		$GINO .= "</div>\n";
		
		return $GINO;
	}
	
	/**
	 * Interfaccia amministrativa per la gestione delle pagine
	 * 
	 * @return string
	 */
	public function managePage(){
	
		$this->accessGroup('ALL');
		
		$htmltab = new htmlTab(array("linkPosition"=>'right', "title"=>_("Pagine")));	
		$link_admin = "<a href=\"".$this->_home."?evt[$this->_instanceName-managePage]&block=permissions\">"._("Permessi")."</a>";
		$link_options = "<a href=\"".$this->_home."?evt[$this->_instanceName-managePage]&block=options\">"._("Opzioni")."</a>";
		$link_dft = "<a href=\"".$this->_home."?evt[".$this->_instanceName."-managePage]\">"._("Gestione")."</a>";
		$sel_link = $link_dft;

		// Variables
		$id = cleanVar($_GET, 'id', 'int', '');
		$cnt = cleanVar($_GET, 'cnt', 'int', '');
		$ref = cleanVar($_GET, 'ref', 'string', '');
		$action = cleanVar($_GET, 'action', 'string', '');
		$block = cleanVar($_GET, 'block', 'string', '');
		// End
		
		$form = '';
		
		if($this->_block == 'permissions' && $this->_access->AccessVerifyGroupIf($this->_className, $this->_instance, '', '')) {
			$GINO = sysfunc::managePermissions($this->_instance, $this->_className);		
			$sel_link = $link_admin;
		}
		elseif($this->_block == 'options') {
			$GINO = sysfunc::manageOptions($this->_instance, $this->_className);		
			$sel_link = $link_options;
		}
		else {

			if($block == $this->_block_module AND $this->_access->AccessVerifyRoleIDIf($this->_access_module))
			{
				if($action == $this->_act_delete)
				{
					$form .= $this->formDeleteModule($id, $action, $ref);
				}
				else
				{
					$form .= $this->formModule($id, $ref);
				}
			
				$select_page = $id;
			}
			elseif($block == $this->_block_content)
			{
			
				if($action == $this->_act_insert_first OR $action == $this->_act_insert_before  OR $action == $this->_act_insert_after)
				{
					$form .= $this->insertContent($id, $cnt, $action, $ref);
				}
				elseif($action == $this->_act_insert_single OR $action == $this->_act_modify_single)
				{
					$form .= $this->singleContent($id, $cnt, $action, $ref);
				}
				elseif($action == $this->_act_modify)
				{
					$form .= $this->formContent($id, $cnt, $action, $ref);
				}
				$select_page = $id;
			}
			else
			{
				if($action == $this->_act_modify OR $action == $this->_act_insert OR $action == $this->_act_delete)
				{
					if($this->_access->AccessVerifyGroupIf($this->_className, $this->_instance, $this->_user_group, $this->_group_1))
					{
						if($action == $this->_act_insert || $action == $this->_act_modify)
						{
							$form .= $this->formPage($id, $ref);
						}
						elseif($action == $this->_act_delete)
						{
							$form .= $this->formDeletePage($id, $action, $ref);
						}
					}
					else
					{
						$form .= $this->textPage($id);
					}
					
					if($action == $this->_act_modify)
					{
						$form .= $this->formBlockPage($id, $action, $ref);
					}
				
					$select_page = $id;
				}
				else
				{
					$form = $this->infoPage();
					$select_page = '';
				}
			}
		
			$GINO = $this->scriptAsset("page.js", "pageJS", 'js');
			$GINO .= "<div class=\"vertical_1\">\n";
			$GINO .= $this->listTree($ref, $select_page);
			$GINO .= "</div>\n";
		
			$GINO .= "<div class=\"vertical_2\">\n";
			$GINO .= $form;
			$GINO .= "</div>\n";
			
			$GINO .= "<div class=\"null\"></div>";
		}

		if($this->_access->AccessVerifyGroupIf($this->_className, $this->_instance, '', '')) $links_array = array($link_admin, $link_options, $link_dft);
		else $links_array = array($link_options, $link_dft);

		$htmltab->navigationLinks = $links_array;
		$htmltab->selectedLink = $sel_link;
		$htmltab->htmlContent = $GINO;
		return $htmltab->render();	
	}
	
	/**
	 * Albero delle pagine a partire dai moduli-pagina
	 * 
	 * @see expandTree()
	 * @see tree()
	 * @see textLink()
	 * @param string $reference posizione della pagina selezionata nella struttura delle pagine (percorso completo)
	 * @param integer $select_page pagina selezionata
	 * @return string
	 */
	private function listTree($reference, $select_page){
	
		$page = explode("_", $reference);
		
		// first page (module)
		if(sizeof($page) > 0) $first_page = $page[0]; else $first_page = '';
		
		if($this->_access->AccessVerifyRoleIDIf($this->_access_module)) {
			$link_module = "<a href=\"".$this->_home."?evt[".$this->_className."-managePage]&amp;block=".$this->_block_module."&amp;action=".$this->_act_insert."\">".$this->icon('insert', _("nuova pagina principale"))."</a>";
		}
		else $link_module = '';
		
		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'header', 'headerLabel'=>_("Albero pagine"), 'headerLinks'=>$link_module));

		$query = "SELECT i.item_id FROM ".$this->_tbl_module." AS m, ".$this->_tbl_item." AS i
		WHERE m.type='".$this->_module_type[0]."' AND i.parent='0' AND i.module=m.id";
		$list_item = $this->_trd->listItemOrdered($query, 'item_id', $this->_tbl_item, 'title', 'asc');
		
		if(sizeof($list_item) > 0)
		{
			$htmlList = new htmlList(array("numItems"=>sizeof($list_item), "separator"=>true));
			$GINO = $htmlList->start();
			
			foreach($list_item AS $key=>$value)
			{
				$selected = (!empty($first_page) AND $first_page == $key)? true:false;
				
				$title = htmlChars($this->_trd->selectTXT($this->_tbl_item, 'title', $key, 'item_id'));
				
				$itemLabel = $this->expandTree($key)
					? "<a href=\"".$this->_home."?evt[".$this->_className."-managePage]&amp;id=$key&amp;ref=$key\">$title</a>"
					: $title;
				
				// Info
				if($this->_access->AccessVerifyGroupIf($this->_className, $this->_instance, '', ''))
				{
					$query = "SELECT m.role1, m.directory FROM ".$this->_tbl_module." AS m, ".$this->_tbl_page." AS p
					WHERE p.item_id='$key' AND p.parent='0' AND p.module=m.id";
					$a = $this->_db->selectquery($query);
					if(sizeof($a) > 0) {
						$role1 = htmlChars($this->_db->getFieldFromId($this->_tbl_user_role, 'name', 'role_id', $a[0]['role1']));
						$directory = $a[0]['directory'];
					}
					
					$itemLabel .= "<br /><span class=\"little\">ID: $key - "._("permessi").": $role1 <br/>"._("cartella").": contents/page/$directory</span>";
				}
				// End
				
				$lnk_module = " <a href=\"".$this->_home."?evt[".$this->_className."-managePage]&amp;id=$key&amp;ref=$key&amp;action=".$this->_act_modify."&amp;block=".$this->_block_module."\">".$this->icon('config', _("opzioni"))."</a>";
				$lnk_modify = " <a href=\"".$this->_home."?evt[".$this->_className."-managePage]&amp;id=$key&amp;ref=$key&amp;action=".$this->_act_modify."\">".$this->icon('modify', _("modifica"))."</a>";
				$lnk_insert = " <a href=\"".$this->_home."?evt[".$this->_className."-managePage]&amp;id=$key&amp;ref=$key&amp;action=".$this->_act_insert."\">".$this->icon('insert', _("nuova sottopagina"))."</a>";
				$lnk_content = " <a href=\"".$this->_home."?evt[".$this->_className."-managePage]&amp;id=$key&amp;ref=$key&amp;action=&amp;block=".$this->_block_content."\">".$this->icon('content', '')."</a>";
				
				$url = $this->_home."?pt[".$this->_className."-textLink]&amp;code=$key";
				$lnk_link = " <a onclick=\"window.myWin = new layerWindow({'title':'"._("Link alla risorsa")."', 'url':'$url', 'bodyId':'link$key', 'width':500});window.myWin.display($(this));\">".$this->icon('link', '')."</a>";
				
				if($this->_access->AccessVerifyGroupIf($this->_className, $this->_instance, $this->_user_group, $this->_group_1))
					$links = $this->_access->AccessVerifyRoleIDIf($this->_access_module)
						? array($lnk_link, $lnk_module, $lnk_modify, $lnk_insert)
						: array($lnk_link, $lnk_modify, $lnk_insert);
				elseif($this->_access->AccessVerifyGroupIf($this->_className, $this->_user_group, $this->_group_2))
					$links = $this->_access->AccessVerifyRoleIDIf($this->_access_module)
						? array($lnk_link, $lnk_module, $lnk_content)
						: array($lnk_link, $lnk_content);
				
				$itemContent = (!empty($first_page) AND $first_page == $key)? $this->tree($first_page, $page, $select_page):null;
				$GINO .= $htmlList->item($itemLabel, $links, $selected, true, $itemContent);
			}
			$GINO .= $htmlList->end();
		}
		else
		{
			$GINO = "<div class=\"message\">"._("non risultano pagine registrate.")."</div>\n";
		}
		
		$htmlsection->content = $GINO;
		
		return $htmlsection->render();
	}
	
	/**
	 * Percorso di una pagina dal modulo-pagina alla pagina selezionata
	 * 
	 * Ogni ciclo mostra le sottopagine di una pagina nel percorso
	 * 
	 * @see expandTree()
	 * @see textLink()
	 * @param string $link_page posizione di una pagina compresa nel percorso tra il modulo-pagina e la pagina selezionata
	 * @param array $page elenco delle pagine dal punto in cui ci si trova nell'albero alla pagina selezionata
	 * @param integer $select_page pagina selezionata
	 * @return string
	 */
	private function tree($link_page, $page, $select_page){
	
		// PAGES
		
		// tolgo il primo elemento
		$parent_page = array_shift($page);
		
		// riferimento per il percorso
		if(sizeof($page) > 0) $next_page = $page[0]; else $next_page = '';
		
		// livello di profondità
		$level = sizeof(explode('_', $link_page));
		// END
		
		$GINO = '';
		
		$query = "SELECT item_id FROM ".$this->_tbl_item." WHERE parent='$parent_page'";
		
		$ist_item = $this->_trd->listItemOrdered($query, 'item_id', $this->_tbl_item, 'title', 'asc');
		
		if(sizeof($ist_item) > 0)
		{
			$htmlList = new htmlList(array("class"=>"admin inside", "numItems"=>sizeof($ist_item), "separator"=>true));
			$GINO = $htmlList->start();

			foreach($ist_item AS $key=>$value)
			{
				$title = htmlChars($this->_trd->selectTXT($this->_tbl_item, 'title', $key, 'item_id'));
				
				$new_link_page = $link_page.'_'.$key;
				
				$selected = $select_page == $key ? true:false;	
				
				$sign = "<img src=\"".SITE_IMG."/list_mini.gif\" alt=\">\" style=\"position:relative;bottom:3px;\"/>&#160;";
				$itemLabel = $sign." ";
				$itemLabel .= $this->expandTree($key)
					? "<a href=\"".$this->_home."?evt[".$this->_className."-managePage]&amp;id=$key&amp;ref=$new_link_page\">$title</a>"
					: $title;
				
				$lnk_modify = " <a href=\"".$this->_home."?evt[".$this->_className."-managePage]&amp;id=$key&amp;ref=$link_page&amp;action=".$this->_act_modify."\">".$this->icon('modify', '')."</a>";
				$lnk_insert = " <a href=\"".$this->_home."?evt[".$this->_className."-managePage]&amp;id=$key&amp;ref=$new_link_page&amp;action=".$this->_act_insert."\">".$this->icon('insert', _("nuova sottopagina"))."</a>";
				$lnk_content = " <a href=\"".$this->_home."?evt[".$this->_className."-managePage]&amp;id=$key&amp;ref=$link_page&amp;action=&amp;block=".$this->_block_content."\">".$this->icon('content', '')."</a>";
				
				$url = $this->_home."?pt[$this->_className-textLink]&amp;code=$key";
				$lnk_link = " <a onclick=\"window.myWin = new layerWindow({'title':'"._("Link alla risorsa")."', 'url':'$url', 'bodyId':'link$key', 'width':500});window.myWin.display($(this));\">".$this->icon('link', '')."</a>";
				
				if($this->_access->AccessVerifyGroupIf($this->_className, $this->_instance, $this->_user_group, $this->_group_1))
				{
					$links = array($lnk_link, $lnk_modify, $lnk_insert);
				}
				elseif($this->_access->AccessVerifyGroupIf($this->_className, $this->_instance, $this->_user_group, $this->_group_2))
				{
					$links = array($lnk_link, $lnk_content);
				}
				else $links = array();

				$itemContent = $next_page == $key? $this->tree($new_link_page, $page, $select_page):null;
				$GINO .= $htmlList->item($itemLabel, $links, $selected, true, $itemContent);

			}
			$GINO .= $htmlList->end();
		}
		return $GINO;
	}
	
	/**
	 * Form di inserimento e modifica di una pagina
	 * 
	 * Titoli e caratteristiche della pagina.
	 * Per l'inserimento di una nuova pagina principale rifarsi al metodo formModule().
	 * 
	 * @param integer $id valore ID della pagina
	 * @param string $reference valori ID delle pagine che precedono nell'albero la pagina selezionata
	 * @return string
	 */
	private function formPage($id, $reference){
	
		$gform = new Form('gform', 'post', true, array("trnsl_table"=>$this->_tbl_item, "trnsl_id"=>$id));
		$gform->load('dataform');
		
		$title_page = htmlChars($this->_trd->selectTXT($this->_tbl_item, 'title', $id, 'item_id'));
		
		if(!empty($id) AND $this->_action == $this->_act_modify)
		{
			$query = "SELECT title, subtitle, view_title, social, cache FROM ".$this->_tbl_item." WHERE item_id='$id'";
			$a = $this->_db->selectquery($query);
			if(sizeof($a) > 0)
			{
				$view = $a[0]['view_title'];
				$title = htmlInput($a[0]['title']);
				$text = htmlInput($a[0]['subtitle']);
				$cache = htmlInput($a[0]['cache']);
				$social = htmlInput($a[0]['social']);
			}	
			
			$submit = _("modifica");
			
			// Links
			$title_text = _("Modifica")." '$title_page'";
			$link = "id=$id&amp;ref=$reference&amp;action=".$this->_act_delete;
			
			if($id != $reference AND !empty($reference))
			{
				$link_delete = "<a href=\"".$this->_home."?evt[".$this->_className."-managePage]&amp;$link\">".$this->icon('delete', '')."</a>";
			}
			elseif($this->_access->AccessVerifyRoleIDIf($this->_access_module))
			{
				$link_delete = "<a href=\"".$this->_home."?evt[".$this->_className."-managePage]&amp;$link&amp;block=".$this->_block_module."\">".$this->icon('delete', '')."</a>";
			}
			$url = $this->_home."?evt[".$this->_className."-displayItem]&amp;id=$id";
			$html = $this->displayItem($id);
			$link_view = " <div style=\"display:none;\" id=\"preview_page\">$html</div><span class=\"link\" onclick=\"window.myWin = new layerWindow({'title':'"._("Preview pagina")."', 'htmlNode':$('preview_page'), 'bodyId':'prew_page$id', 'width':800});window.myWin.display();\">".$this->icon('view', '')."</span>";
			$links = array($link_delete, $link_view, $this->_link_return);
		}
		else
		{
			$cache = $gform->retvar('cache', '');
			$social = $gform->retvar('social', '');
			$view = $gform->retvar('view', 'yes');
			$title = $gform->retvar('title', '');
			$text = $gform->retvar('text', '');
			$submit = _("inserisci");
			
			// Links
			$title_text = _("Nuova pagina in")." '$title_page'";
			$links = null;
		}
		
		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'h1', 'headerLabel'=>$title_text, 'headerLinks'=>$links));

		$required = 'title';
		$GINO = $gform->form($this->_home."?evt[".$this->_className."-actionPage]", '', $required);
		$GINO .= $gform->hidden('id', $id);
		$GINO .= $gform->hidden('action', $this->_action);
		$GINO .= $gform->hidden('ref', $reference);

		$GINO .= $gform->cinput('title', 'text', $title, _("Titolo"), array("required"=>true, "size"=>40, "maxlength"=>200, "trnsl"=>true, "field"=>"title"));
		$GINO .= $gform->ctextarea('text', $text, _("Sottotitolo"), array("cols"=>45, "rows"=>2, "trnsl"=>true, "field"=>"subtitle"));
		$GINO .= $gform->cradio('view', $view, array("yes"=>_("si"),"no"=>_("no")), 'no', _("Visibili nella pagina"), array("required"=>true));
		$GINO .= $gform->cradio('social', $social, array("yes"=>_("si"),"no"=>_("no")), 'no', _("Attiva condivisione social networks"), array("required"=>true));
		$GINO .= $gform->cinput('cache', 'cache', $cache, array(_("Tempo di caching dei contenuti (s)"), _("Se non si vogliono tenere in cache o non si è sicuri del significato lasciare vuoto o settare a 0")), array("required"=>false, "size"=>40, "maxlength"=>200, "trnsl"=>true, "field"=>"title"));
		
		$GINO .= $gform->cinput('submit_action', 'submit', $submit, '', array("classField"=>"submit"));
		$GINO .= $gform->cform();
		
		$htmlsection->content = $GINO;
		
		return $htmlsection->render();
	}
	
	/**
	 * Inserimento e modifica di una pagina
	 * 
	 * @see $_group_1
	 */
	public function actionPage(){
	
		$this->accessGroup($this->_group_1);
		
		$gform = new Form('gform', 'post', true);
		$gform->save('dataform');
		$req_error = $gform->arequired();
		
		$id = cleanVar($_POST, 'id', 'int', '');
		$view = cleanVar($_POST, 'view', 'string', '');
		$title = cleanVar($_POST, 'title', 'string', '');
		$text = cleanVar($_POST, 'text', 'string', '');
		$cache = cleanVar($_POST, 'cache', 'int', '');
		$social = cleanVar($_POST, 'social', 'string', '');

		$reference = cleanVar($_POST, 'ref', 'string', '');
		
		$link_error = $this->_home."?evt[$this->_className-managePage]&id=$id&ref=$reference&action=$this->_action";
		
		if($req_error > 0) 
			exit(error::errorMessage(array('error'=>1), $link_error));
		
		if(!empty($title))
		{
			if($this->_action == $this->_act_modify)
			{
				$query = "UPDATE ".$this->_tbl_item." SET title='$title', subtitle='$text', view_title='$view', social='$social', cache='$cache' WHERE item_id='$id'";
				$this->_db->actionquery($query);
				
				EvtHandler::HttpCall($this->_home, $this->_className.'-managePage', "id=$id&ref=$reference&action=$this->_action");
			}
			elseif($this->_action == $this->_act_insert)
			{
				$page = explode("_", $reference);
				
				if(sizeof($page) > 0)
				{
					$ref_page_module = $page[0];
					$ref_page_parent = array_pop($page);
					
					$query = "SELECT module FROM ".$this->_tbl_item." WHERE item_id='$ref_page_module' AND parent='0'";
					$a = $this->_db->selectquery($query);
					if(sizeof($a) > 0)
					{
						foreach($a AS $b)
						{
							$ref_module = $b["module"];
						}
					}
					else
					{
						exit(error::errorMessage(array('error'=>9), $link_error));
					}
				}
				else
				{
					exit(error::errorMessage(array('error'=>9), $link_error));
				}
				
				$date = date("Y-m-d H:i:s");
				
				$query = "INSERT INTO ".$this->_tbl_item." (title, subtitle, module, parent, date, view_title, social, cache)
				VALUES ('$title', '$text', $ref_module, $ref_page_parent, '$date', '$view', '$social', '$cache')";
				$result = $this->_db->actionquery($query);
				$last_id_item = $this->_db->getlastid($this->_tbl_item);
				
				EvtHandler::HttpCall($this->_home, $this->_className.'-managePage', "id=$last_id_item&ref=$reference&action=".$this->_act_modify);
			}
		}
		else	// redirection error
		{
			if($action == $this->_act_insert)
			{
				$prev_page = end(explode("_", $reference));
				exit(error::errorMessage(array('error'=>1), $this->_home."?evt[$this->_className-managePage]&id=$prev_page&ref=$reference&action=$this->_action"));
			}
			elseif($action == $this->_act_modify)
			{
				exit(error::errorMessage(array('error'=>1), $this->_home."?evt[$this->_className-managePage]&id=$id&ref=$reference&action=$this->_action"));
			}
			else
			{
				exit(error::errorMessage(array('error'=>1), $this->_home."?evt[$this->_className-managePage]"));
			}
		}
	}
	
	/**
	 * Form di eliminazione di una pagina
	 * 
	 * @param integer $id valore ID della pagina
	 * @param string $action azione da eseguire
	 * @param string $reference valori ID delle pagine che precedono nell'albero la pagina selezionata
	 * @return string
	 */
	private function formDeletePage($id, $action, $reference){
		
		$gform = new Form('gform', 'post', false);
		
		$title_page = htmlChars($this->_trd->selectTXT($this->_tbl_item, 'title', $id, 'item_id'));
		
		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'h1', 'headerLabel'=>_("Elimina")." '$title_page'"));

		$required = '';
		$GINO = $gform->form($this->_home."?evt[".$this->_className."-actionDeletePage]", '', $required);
		$GINO .= $gform->hidden('id', $id);
		$GINO .= $gform->hidden('ref', $reference);
		$GINO .= $gform->hidden('action', $action);

		$GINO .= $gform->cinput('delete_action', 'submit', _("elimina"), array(_("Attenzione!"), _("l'eliminazione è definitiva e comporta l'eliminazione delle pagine che seguono nell'albero")), array("classField"=>"submit"));
		$GINO .= $gform->cform();
		
		$htmlsection->content = $GINO;
		
		return $htmlsection->render();
	}
	
	/**
	 * Elimina la pagina selezionata e le pagine che la seguono nell'albero
	 * 
	 * Con le pagine vengono eliminati anche tutti contenuti
	 * 
	 * @param integer $id valore ID della pagina
	 * @return void
	 */
	private function deleteTree($id){
		
		// per evitare di eliminare la pagina principale (modulo)
		$query = "SELECT parent FROM ".$this->_tbl_item." WHERE item_id='$id'";
		$a = $this->_db->selectquery($query);
		if(sizeof($a) > 0)
		{
			// Contenuti
			$query_content = "SELECT content_id FROM ".$this->_tbl_content." WHERE item='$id'";
			$c = $this->_db->selectquery($query_content);
			if(sizeof($c) > 0)
			{
				foreach($c AS $d)
				{
					$content_id = $d['content_id'];
					language::deleteTranslations($this->_tbl_content, $content_id);
					
					// aggiunte e file
					$query = "DELETE FROM ".$this->_tbl_content_add." WHERE content_id='$content_id'";
					$this->_db->actionquery($query);
					
					$query = "DELETE FROM ".$this->_tbl_content_file." WHERE content_id='$content_id'";
					$this->_db->actionquery($query);
					
					// Eliminazione File e Directory
					$directory = $this->pathBlockDir($content_id, 'abs');
					$this->deleteFileDir($directory, true);
				}
			}
			
			$query_delete = "DELETE FROM ".$this->_tbl_content." WHERE item='$id'";
			$this->_db->actionquery($query_delete);
			// End
			
			language::deleteTranslations($this->_tbl_item, $id);
			
			$query_delete = "DELETE FROM ".$this->_tbl_item." WHERE item_id='$id'";
			$this->_db->actionquery($query_delete);
			
			// Search Sub Page
			$query_next = "SELECT item_id FROM ".$this->_tbl_item." WHERE parent='$id'";
			$c = $this->_db->selectquery($query_next);
			if(sizeof($c) > 0)
			{
				foreach($c AS $d)
				{
					$this->deleteTree($d['item_id'], $directory);
				}
			}
		}
	}
	
	/**
	 * Eliminazione di una pagina
	 * 
	 * Non è possibile eliminare le pagine principali (modulo-pagina)
	 * 
	 * @see $_group_1
	 * @see deleteTree()
	 */
	public function actionDeletePage(){
	
		$this->accessGroup($this->_group_1);
		
		$id = cleanVar($_POST, 'id', 'int', '');
		$action = cleanVar($_POST, 'action', 'string', '');
		$reference = cleanVar($_POST, 'ref', 'string', '');
		
		$prev_page = end(explode("_", $reference));
		
		$link = "id=$prev_page&ref=$reference";
		$link_error = $this->_home."?evt[$this->_className-managePage]&id=$id&ref=$reference&action=$action";
		$redirect = $this->_className.'-managePage';
		
		if(empty($reference))	// Master Page
			exit(error::errorMessage(array('error'=>9), $link_error));
		
		if(!empty($id) AND $action == $this->_act_delete)
		{
			$this->deleteTree($id);
			
			EvtHandler::HttpCall($this->_home, $redirect, $link);
		}
		else
			exit(error::errorMessage(array('error'=>9), $link_error));
	}
	
	/**
	 * Visualizzazione schematica dei blocchi di una pagina con la possibilità di operare su di essi delle azioni e di ordinarli 
	 * 
	 * @see selectContent()
	 * @see orderContent()
	 * @see selectMedia()
	 * @param integer $id valore ID della pagina
	 * @param string $action azione da eseguire
	 * @param string $reference valori ID delle pagine che precedono nell'albero la pagina selezionata
	 * @return string
	 */
	private function formBlockPage($id, $action, $reference){
		
		$query = "SELECT content_id, layout, text, img, link, filename, order_list FROM ".$this->_tbl_content." WHERE item='$id' ORDER BY order_list";
		$a = $this->_db->selectquery($query);
		if(sizeof($a) > 0)
		{
			$GINO = '';
			foreach($a AS $b)
			{
				$content_id = htmlChars(($b['content_id']));
				$layout = htmlChars(($b['layout']));
				$img = htmlChars($b['img']);
				$link = htmlChars($b['link']);
				$filename = htmlChars($b['filename']);
				$order_list = htmlInput($b['order_list']);
				
				$text = htmlChars($this->_trd->selectTXT($this->_tbl_content, 'text', $content_id, 'content_id'));
				
				$s_title = ($layout != $this->_layout_single_code AND $layout != $this->_layout_single_include)
					? $this->orderContent($id, $content_id, $order_list, sizeof($a), $reference)
					: null;
				$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'h1', 'headerLabel'=>$s_title));
				$htmlsection->headerLinks = $this->selectContent($id, $content_id, $reference);
				
				$buffer = "<b>"._("layout").'</b>: '.$this->nameLayout($layout);
				
				$directory = $this->pathBlockDir($content_id, 'rel');
				if(!empty($img) AND ($layout == $this->_layout_text_img || $layout == $this->_layout_img_text || $layout == $this->_layout_img))
				{
					if($layout == $this->_layout_text_img) $css = 'dd_dx';
					elseif($layout == $this->_layout_img_text) $css = 'dd_sx';
					else $css = '';
					
					$buffer .= "<div class=\"$css\">\n";
					$buffer .= $this->selectMedia($content_id, $img, $directory, '', array('preview'=>true, 'width'=>$this->_image_width));
					
					if(!empty($link)) $buffer .= "<br />link: <a href=\"$link\">$link</a>";
					
					$buffer .= "</div>\n";
				}
				
				if(!empty($text) && $layout==$this->_layout_single_include) $buffer .= "<br /><b>"._("Nome file: ")."</b>".$directory.$text;
				elseif(!empty($text)) $buffer .= "<br />".$text;
				if(!empty($filename)) $buffer .= "<p>"._("file abbinato").": $filename</p>";
				$htmlsection->content = $buffer;
				$GINO .= $htmlsection->render();
			}
		}
		else
		{
			$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'h1', 'headerLabel'=>_("Contenuti")));
			$htmlsection->headerLinks = $this->selectContent($id, '', $reference);
			$htmlsection->content = "<p>"._("Nessun contenuto inserito")."</p>";
			$GINO = $htmlsection->render();
		}
		
		return $GINO;
	}
	
	/**
	 * Scelta del tipo di azione da intraprendere in merito a un blocco di pagina
	 * 
	 * @see chosenContent()
	 * @param integer $item_id valore ID della pagina
	 * @param integer $content_id valore ID del blocco
	 * @param string $reference valori ID delle pagine che precedono nell'albero la pagina selezionata
	 * @return string
	 */
	private function selectContent($item_id, $content_id, $reference){
	
		$gform = new Form('gform', 'post', false, array("tblLayout"=>false));

		$GINO = $gform->form($this->_home."?evt[".$this->_className."-chosenContent]", '', '');
		$GINO .= $gform->hidden('item_id', $item_id);
		$GINO .= $gform->hidden('ref', $reference);

		if(!empty($content_id))
		{
			$GINO .= $gform->hidden('content_id', $content_id);
			
			$GINO .= "<select name=\"action\">\n";
			$GINO .= "<option value=\"\">"._("scegli un'azione")."</option>\n";
			$GINO .= "<option value=\"".$this->_act_delete."\">"._("elimina")."</option>\n";
			
			$layout = $this->typePage($item_id);
			if($layout != $this->_layout_single_code AND $layout != $this->_layout_single_include)
			{
				$GINO .= "<option value=\"".$this->_act_modify."\">"._("modifica")."</option>\n";
				$GINO .= "<option value=\"".$this->_act_insert_before."\">"._("inserisci prima")."</option>\n";
				$GINO .= "<option value=\"".$this->_act_insert_after."\">"._("inserisci dopo")."</option>\n";
			}
			else
			{
				$GINO .= "<option value=\"".$this->_act_modify_single."\">"._("modifica")."</option>\n";
			}
			$GINO .= "</select>";
		}
		else
		{
			$GINO .= "<select name=\"action\">\n";
			$GINO .= "<option value=\"\">"._("scegli un'azione")."</option>\n";
			$GINO .= "<option value=\"".$this->_act_insert_first."\">"._("inserisci da editor")."</option>\n";
			$GINO .= "<option value=\"".$this->_act_insert_single."\">"._("inserisci da html")."</option>\n";
			$GINO .= "</select>";
		}
		
		$GINO .= "&nbsp;\n";

		$GINO .= $gform->input('submit_action', 'submit', _("procedi"), array("js"=>"onclick=\"if($(this).getPrevious('select').getProperty('value')=='') {alert('"._("seleziona un\' azione")."');return false;}\"", "classField"=>"generic"));
		$GINO .= $gform->cform();
		
		return $GINO;
	}
	
	/**
	 * Elimina un blocco di pagina
	 * 
	 * @see pub::deleteFileDir()
	 * @param integer $content_id valore ID del blocco
	 * @param integer $item_id valore ID della pagina
	 * @return void
	 */
	private function deleteBlock($content_id, $item_id){
		
		$directory = $this->pathBlockDir($content_id, 'abs');
		
		$query = "DELETE FROM ".$this->_tbl_content." WHERE content_id='$content_id'";
		$result = $this->_db->actionquery($query);
		if($result)
		{
			language::deleteTranslations($this->_tbl_content, $content_id);
			
			// rigenerazione ordine
			$query = "SELECT content_id FROM ".$this->_tbl_content." WHERE item='$item_id' ORDER BY order_list ASC";
			$a = $this->_db->selectquery($query);
			if(sizeof($a) > 0)
			{
				$count = 1;
				foreach($a AS $b)
				{
					$cnt = $b['content_id'];
					$q = "UPDATE ".$this->_tbl_content." SET order_list=$count WHERE content_id='$cnt'";
					$this->_db->actionquery($q);
					$count++;
				}
			}
			
			// aggiunte e file
			$query = "DELETE FROM ".$this->_tbl_content_add." WHERE content_id='$content_id'";
			$this->_db->actionquery($query);
			
			$query = "DELETE FROM ".$this->_tbl_content_file." WHERE reference='$content_id'";
			$this->_db->actionquery($query);
			
			// eliminazione File e Directory
			$this->deleteFileDir($directory, true);
		}
	}
	
	/**
	 * Redirige al metodo @a managePage() in base al tipo di azione intrapreso sul blocco della pagina
	 * 
	 * L'azione di eliminazione del blocco viene eseguita all'interno del metodo
	 * 
	 * @see deleteBlock()
	 * @return redirect
	 */
	public function chosenContent(){
		
		$this->accessGroup($this->_group_1);
		
		$item_id = cleanVar($_POST, 'item_id', 'int', '');
		$content_id = cleanVar($_POST, 'content_id', 'int', '');
		$reference = cleanVar($_POST, 'ref', 'string', '');
		$action = cleanVar($_POST, 'action', 'string', '');
		
		$ref_page = "id=$item_id&ref=$reference&action=$action&block=".$this->_block_content."";
		$ref_page_delete = "id=$item_id&ref=$reference&action=".$this->_act_modify."";
		
		if(!empty($content_id))
		{
			if($action == $this->_act_delete)
			{
				// Eliminazione Record
				$query = "SELECT content_id FROM ".$this->_tbl_content." WHERE content_id='$content_id'";
				$a = $this->_db->selectquery($query);
				if(sizeof($a) > 0) $result = true; else $result = false;
				
				if($result) $this->deleteBlock($content_id, $item_id);
				
				EvtHandler::HttpCall($this->_home, $this->_className.'-managePage', $ref_page_delete);
			}
			elseif($action == $this->_act_modify OR $action == $this->_act_modify_single)
			{
				EvtHandler::HttpCall($this->_home,  $this->_className.'-managePage', "$ref_page&cnt=$content_id");
			}
			elseif($action == $this->_act_insert_before OR $action == $this->_act_insert_after)
			{
				EvtHandler::HttpCall($this->_home,  $this->_className.'-managePage', "$ref_page&cnt=$content_id");
			}
		}
		elseif(!empty($item_id) AND ($action == $this->_act_insert_first OR $action == $this->_act_insert_single))
		{
			EvtHandler::HttpCall($this->_home,  $this->_className.'-managePage', $ref_page);
		}

		EvtHandler::HttpCall($this->_home, $this->_className.'-managePage', $ref_page);
	}
	
	private function insertContent($item_id, $content_id, $action, $reference){
	
		$GINO = '';
		
		if(!empty($content_id) AND !empty($action))
		{
			$query = "SELECT i.item_id, c.order_list
			FROM ".$this->_tbl_item." AS i, ".$this->_tbl_content." AS c
			WHERE c.item=i.item_id AND c.content_id='$content_id'";
			$a = $this->_db->selectquery($query);
			if(sizeof($a) > 0)
			{
				foreach($a AS $b)
				{
					$item_id = $b["item_id"];
					$order = $b["order_list"];
				}
			}
			
			if($action == $this->_act_insert_before)
			{
				$order_new = $order;
			}
			elseif($action == $this->_act_insert_after)
			{
				$order_new = $order + 1;
			}

			$GINO .= $this->formContent($item_id, $content_id, $action, $reference, $order_new);
		}
		elseif(!empty($item_id) AND $action == $this->_act_insert_first)
		{
			$order_new = 1;
			
			$GINO .= $this->formContent($item_id, '', $action, $reference, $order_new);
		}

		return $GINO;
	}
	
	/**
	 * Form di inserimento e modifica di un blocco di pagina del tipo con inclusione HTML
	 * 
	 * Un blocco di pagina generato con l'opzione "inserisci da html" è unico per la pagina
	 * 
	 * @param integer $item_id valore ID della pagina
	 * @param integer $content_id valore ID del blocco
	 * @param string $action azione da eseguire
	 * @param string $reference valori ID delle pagine che precedono nell'albero la pagina selezionata
	 * @return string
	 */
	private function singleContent($item_id, $content_id, $action, $reference){
	
		$gform = new Form('gform', 'post', true);
		$gform->load('dataform');
		
		$GINO = '';
		
		if(!empty($content_id) AND $action == $this->_act_modify_single)
		{
			$layout = $this->typePage($item_id);
			
			$text = htmlInput($this->_db->getFieldFromId($this->_tbl_content, 'text', 'content_id', $content_id));

			$title = _("modifica contenuto");
			$submit = _("modifica");
			
			if($layout == $this->_layout_single_code)
			{
				$text1 = $text;	// codice html
				$text2 = '';	// nome del file
			}
			elseif($layout == $this->_layout_single_include)
			{
				$text1 = '';
				$text2 = $text;
			}
			else
			{
				$text1 = '';
				$text2 = '';
			}
		}
		elseif($action == $this->_act_insert_single)
		{
			$layout = '';
			$text1 = '';
			$text2 = '';
			$title = _("nuovo contenuto");
			$submit = _("inserisci");
		}
		
		$page_title = $this->_db->getFieldFromId($this->_tbl_page, 'title', 'item_id', $item_id);
		
		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'h1', 'headerLabel'=>"$page_title - $title"));
		
		$required = '';
		$GINO .= $gform->form($this->_home."?evt[".$this->_className."-actionSingleContent]", true, $required);
		$GINO .= $gform->hidden('item_id', $item_id);
		$GINO .= $gform->hidden('content_id', $content_id);
		$GINO .= $gform->hidden('action', $action);
		$GINO .= $gform->hidden('ref', $reference);

		// Layout
		$array = array();
		$query = "SELECT id AS fkey FROM ".$this->_tbl_content_layout."
		WHERE id='".$this->_layout_single_code."' OR id='".$this->_layout_single_include."'";
		$a = $this->_db->selectquery($query);
		if(sizeof($a) > 0)
		{
			foreach ($a AS $b)
			{
				$key = $b['fkey'];
				$value = $this->_trd->selectTXT($this->_tbl_content_layout, 'name', $key);
				$array[$key] = $value;
			}
		}
		$GINO .= $gform->cradio('layout', $layout, $array, $this->_layout_single_code, _("Layout"), array("required"=>true));
		
		// Include File
		$GINO .= $gform->cfile('file1', $text2, _("File"), array("extensions"=>$this->_extension_include, "del_check"=>true));
		
		// Code HTML
		 $GINO .= $gform->ctextarea('text_code', $text1, _("Codice html"), array("cols"=>48, "rows"=>20, "trnsl"=>"true", "field"=>"text", "trnsl_table"=>$this->_tbl_content, "trnsl_id"=>$content_id));
		
		$GINO .= $gform->cinput('submit_action', 'submit', $submit, '', array("classField"=>"submit"));
		$GINO .= $gform->cform();
		
		$htmlsection->content = $GINO;
		
		return $htmlsection->render();
	}
	
	/**
	 * Form di inserimento e modifica di un blocco di pagina
	 * 
	 * @see optionMedia()
	 * @param integer $item_id valore ID della pagina
	 * @param integer $content_id valore ID del blocco
	 * @param string $action azione da eseguire
	 * @param string $reference valori ID delle pagine che precedono nell'albero la pagina selezionata
	 * @param integer $order_new posizione nella sequenza dei blocchi
	 * @return string
	 */
	private function formContent($item_id, $content_id, $action, $reference, $order_new=''){
	
		$gform = new Form('jump', 'post', true, array("trnsl_table"=>$this->_tbl_content, "trnsl_id"=>$content_id));
		$gform->load('cdataform');
		
		$GINO = '';
		
		if(!empty($content_id) AND $action == $this->_act_modify)
		{
			$query = "SELECT c.layout, c.text, c.img, c.link, c.filename, c.order_list
			FROM ".$this->_tbl_item." AS i, ".$this->_tbl_content." AS c
			WHERE content_id='$content_id' AND c.item=i.item_id";
			$a = $this->_db->selectquery($query);
			if(sizeof($a) > 0)
			{
				foreach($a AS $b)
				{
					// Layout
					if(!empty($this->_js_var_form)) $layout = $this->_js_var_form;
					else $layout = htmlInput($b["layout"]);
					
					$text = htmlInputEditor($b["text"]);
					$filename = htmlInput($b["img"]);
					$filename2 = htmlInput($b["filename"]);
					$order = $b["order_list"];
				}
				
				$title = _("blocco").' '.$order;
				$submit = _("modifica");
				
				if(!empty($filename))
				{
					$directory = $this->pathBlockDir($content_id, 'rel');
					$www_file = $directory.$filename;
				}
				else $www_file = '';
				
				$type_media = $this->typeMedia($filename);
			}
		}
		else
		{
			// Layout: onChange prima di passare da actionContent()
			if(empty($this->_js_var_form)) $layout = $gform->retvar('var1', $this->_layout_text);
			else $layout = $this->_js_var_form;
			
			$filename = '';
			$filename2 = '';
			$text = $gform->retvar('ctext', '');
			
			$title = _("nuovo blocco");
			$submit = _("inserisci");
			$www_file = '';
			$type_media = '';
		}
		
		$jslink = $this->_home."?evt[".$this->_className."-managePage]&id=$item_id&ref=$reference&action=$action&block=".$this->_block_content."&cnt=$content_id";
		// End
		
		$page_title = $this->_db->getFieldFromId($this->_tbl_page, 'title', 'item_id', $item_id);
		
		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'h1', 'headerLabel'=>"$page_title - $title"));
		
		$GINO = "<a name=\"a1\"></a>";
		
		$required = '';
		$GINO .= $gform->form($this->_home."?evt[".$this->_className."-actionContent]", true, $required);
		$GINO .= $gform->hidden('item_id', $item_id);
		$GINO .= $gform->hidden('content_id', $content_id);
		$GINO .= $gform->hidden('order', $order_new);
		$GINO .= $gform->hidden('action', $action);
		$GINO .= $gform->hidden('ref', $reference);
		$GINO .= $gform->hidden('old_file1', $filename);
		$GINO .= $gform->hidden('old_file2', $filename2);
		$GINO .= $gform->hidden('jslink', $jslink);

		// Layout
		$array = array();
		$query = "SELECT id AS fkey FROM ".$this->_tbl_content_layout."
		WHERE id!='".$this->_layout_single_code."' AND id!='".$this->_layout_single_include."'";
		$a = $this->_db->selectquery($query);
		if(sizeof($a) > 0)
		{
			foreach ($a AS $b)
			{
				$key = $b['fkey'];
				$value = $this->_trd->selectTXT($this->_tbl_content_layout, 'name', $key);
				$array[$key] = $value;
			}
		}
		$expl = _("Layout semplificato. Per una gestione personalizzata delle immagini usare i relativi strumenti dell'editor di testo.");
		$GINO .= $gform->cradio('var1', $layout, $array, '', array(_("Layout"), $expl), array("required"=>true, "js"=>"onclick=\"jump_radio()\"", "aspect"=>"v"));
		
		// Media
		if($layout == $this->_layout_img OR $layout == $this->_layout_img_text OR $layout == $this->_layout_text_img)
		{
			// Scelta Media
			$js = "onchange=\"ajaxRequest('post', '{$this->_home}?pt[{$this->_className}-optionMedia]', 'rid=$content_id&action=$action&opt='+$(this).value, 'option_media')\"";
			$array = array_combine($this->_type_media, $this->_type_media_value);
			$GINO .= $gform->cselect('media', $type_media, $array, _("Tipologia di media"), array('js'=>$js));
			
			$GINO .= $gform->cell($this->optionMedia($content_id, $type_media, $action), array("id"=>"option_media"));
		}
		
		// File attach
		if($layout == $this->_layout_link_file)
		{
			$GINO .= $gform->cfile('file2', $filename2, _("File allegato"), array("extensions"=>$this->_extension_attach, "del_check"=>true));
		}
		
		// Text
		if($layout == $this->_layout_text OR $layout == $this->_layout_img_text OR $layout == $this->_layout_text_img OR $layout == $this->_layout_link_file)
		{
			$GINO .= $gform->fcktextarea('ctext', $text, _("Testo"), array("notes"=>true, "img_preview"=>true, "fck_toolbar"=>$this->_fck_toolbar,"trnsl"=>true, "field"=>"text"));
		}

		$GINO .= $gform->cinput('submit_action', 'submit', $submit, '', array("classField"=>"submit"));
		$GINO .= $gform->cform();

		$htmlsection->content = $GINO;
		
		return $htmlsection->render();
	}
	
	/**
	 * Elementi multimediali di un blocco della pagina (Form)
	 * 
	 * Mostra campi diversi in base al valore del parametro $type_media
	 * 
	 * @param integer $content_id valore ID del blocco
	 * @param string $type_media tipologia di media (vedi pub::_type_media)
	 * @param string $action azione da eseguire
	 * @return string
	 */
	public function optionMedia($content_id='', $type_media='', $action='') {
	
		if(!empty($content_id) AND !empty($action) AND !empty($type_media))
		{
			$id = $content_id;
		}
		else
		{
			$id = cleanVar($_POST, 'rid', 'int', '');
			$type_media = cleanVar($_POST, 'opt', 'string', '');
			$action = cleanVar($_POST, 'action', 'string', '');
		}
		
		if($action != $this->_act_modify) $id = '';
		
		$gform = new Form('jump', 'post', false);	// -> formContent()
		$gform->load('cdataform');
		
		$GINO = $gform->startTable();
		
		if(!empty($id))
		{
			$query = "SELECT link, img FROM ".$this->_tbl_content." WHERE content_id='$id'";
			$a = $this->_db->selectquery($query);
			if(sizeof($a) > 0)
			{
				foreach($a AS $b)
				{
					$link = htmlInput($b['link']);
					$filename = htmlInput($b['img']);
				}
			}
			
			$query = "SELECT media_width, media_height, media_alt_text, filename1, filename2, filename3 FROM ".$this->_tbl_content_add."
			WHERE content_id='$id'";
			$a = $this->_db->selectquery($query);
			if(sizeof($a) > 0)
			{
				foreach($a AS $b)
				{
					$width = htmlInput($b['media_width']);
					$height = htmlInput($b['media_height']);
					$alt_text = htmlInput($b['media_alt_text']);
					$file1 = htmlInput($b['filename1']);
					$file2 = htmlInput($b['filename2']);
					$file3 = htmlInput($b['filename3']);
				}
			}
			else
			{
				$width = '';
				$height = '';
				$alt_text = '';
				$file1 = $file2 = $file3 = '';
			}
		}
		else
		{
			$link = '';
			$filename = '';
			$width = '';
			$height = '';
			$alt_text = '';
			$file1 = $file2 = $file3 = '';
		}
		
		if($type_media == $this->_type_media[0])
		{
			$page_id = $this->_db->getFieldFromId($this->_tbl_content, 'item', 'content_id', $id);
			$page_dir = $this->namePageDir($page_id);
			$img_view = $this->_data_www."/".$page_dir."/".$content_id."/".$filename;
			$GINO .= $gform->cfile('file1', $filename, _("Media"), array("preview"=>true, "previewSrc"=>$img_view, "extensions"=>$this->_extension_content1, "del_check"=>true));
			$GINO .= $gform->cinput('link', 'text', $link, _("Link associato"), array("size"=>40, "maxlength"=>100));
			$GINO .= $gform->cinput('alt', 'text', $alt_text, _("Testo alternativo"), array("size"=>40, "maxlength"=>100));
		}
		elseif($type_media == $this->_type_media[1] || $type_media == $this->_type_media[2])
		{
			if($type_media == $this->_type_media[1])
			{
				$label = _("Media");
				$ext = $this->_extension_content2;
			}
			else
			{
				$label = _("Media video");
				$ext = $this->_extension_content3;
				$GINO .= $gform->noinput('', _("Il video con estensione mov viene considerato il video di fallback"));
				$GINO .= $gform->hidden('old_fileadd1', $file1);
				$GINO .= $gform->hidden('old_fileadd2', $file2);
				$GINO .= $gform->hidden('old_fileadd3', $file3);
			}
			$GINO .= $gform->cfile('file1', $filename, $label, array("preview"=>false, "extensions"=>$ext, "del_check"=>true));
			
			if($type_media == $this->_type_media[2])
			{
				$GINO .= $gform->cfile('fileadd1', $file1, $label, array("preview"=>false, "extensions"=>$ext, "del_check"=>true));
				$GINO .= $gform->cfile('fileadd2', $file2, $label, array("preview"=>false, "extensions"=>$ext, "del_check"=>true));
				$GINO .= $gform->cfile('fileadd3', $file3, $label, array("preview"=>false, "extensions"=>$ext, "del_check"=>true));
			}
			$GINO .= $gform->cinput('width', 'text', $width, _("Larghezza"), array("size"=>4, "maxlength"=>4, "pattern"=>"^\d{0,4}$", "hint"=>"inserire un numero intero con non più di 4 cifre", "text_add"=>" px"));
			$GINO .= $gform->cinput('height', 'text', $height, _("Altezza"), array("size"=>4, "maxlength"=>4, "pattern"=>"^\d{0,4}$", "hint"=>"inserire un numero intero con non più di 4 cifre", "text_add"=>" px"));
		}
		$GINO .= $gform->endTable();
		
		return $GINO;
	}
	
	/**
	 * Elimina i contenuti multimediali associati alla tabella page_block_file
	 * 
	 * @see $_group_2
	 * @see pub::deleteFile()
	 */
	public function actionDeleteMedia() {
	
		$this->accessGroup($this->_group_2);
		
		$fid = cleanVar($_GET, 'fid', 'int', '');
		$action = cleanVar($_GET, 'action', 'string', '');
		
		$GINO = '';
		
		if(!empty($fid) AND $action == $this->_act_delete)
		{
			$query = "SELECT reference, filename FROM ".$this->_tbl_content_file." WHERE id='$fid'";
			$a = $this->_db->selectquery($query);
			if(sizeof($a) > 0)
			{
				foreach($a AS $b)
				{
					$content_id = $b['reference'];
					$filename = $b['filename'];
					
					$directory = $this->pathBlockDir($content_id, 'abs');
					
					$result = $this->deleteFile($directory.$filename, $this->_home, '', '');
					if($result)
					{
						$query_delete = "DELETE FROM ".$this->_tbl_content_file." WHERE id='$fid'";
						$result_query = $this->_db->actionquery($query_delete);
						
						if(!$result_query)
						$GINO .= "<p>"._("errore nella query di eliminazione del file")." '$filename'</p>";
					}
					else
					{
						$GINO .= "<p>"._("non è stato possibile eliminare il file")." '$filename'</p>";
					}
				}
			}
		}
		
		return $GINO;
	}
	
	/**
	 * Inserimento e modifica dei blocchi di pagina
	 * 
	 * @see $_group_2
	 * @see actionInsertContentFirst()
	 * @see actionInsertContent()
	 * @see actionModifyContent()
	 */
	public function actionContent(){
	
	$this->accessGroup($this->_group_2);
		
		$gform = new Form('jump', 'post', false);
		$gform->save('cdataform');
		
		$item_id = cleanVar($_POST, 'item_id', 'int', '');
		$content_id = cleanVar($_POST, 'content_id', 'int', '');
		$layout = cleanVar($_POST, 'var1', 'int', '');
		$link = cleanVar($_POST, 'link', 'string', '');
		$text = cleanVarEditor($_POST, 'ctext', '');
		$order = cleanVar($_POST, 'order', 'int', '');
		$action = cleanVar($_POST, 'action', 'string', '');
		$reference = cleanVar($_POST, 'ref', 'string', '');
		$old_file1 = cleanVar($_POST, 'old_file1', 'string', '');
		$old_file2 = cleanVar($_POST, 'old_file2', 'string', '');
		
		// Added Input
		$media = cleanVar($_POST, 'media', 'string', '');
		$alt = cleanVar($_POST, 'alt', 'string', '');
		$width = cleanVar($_POST, 'width', 'int', '');
		$height = cleanVar($_POST, 'height', 'int', '');
		// End
		
		$filename_name = isset($_FILES['file1'])?$_FILES['file1']['name']:null;
		$filename2_name = isset($_FILES['file2'])?$_FILES['file2']['name']:null;
		
		$ref_page = "id=$item_id&ref=$reference&action=".$this->_act_modify;
		$ref_page_error = "id=$item_id&ref=$reference&action=$action&block=".$this->_block_content."&cnt=$content_id&var1=$layout";
		$redirect = $this->_className.'-managePage';
		$link_error = $this->_home."?evt[$redirect]&$ref_page_error";
		
		if((empty($filename_name) AND (empty($old_file1) AND empty($link))) AND empty($filename2_name) AND empty($text))
			exit(error::errorMessage(array('error'=>2), $link_error));
		
		if(empty($layout)) $layout = $this->defaultLayout();
		
		$cid = $action == $this->_act_modify ? $content_id : $this->_db->autoIncValue($this->_tbl_content);
		
		// Directory
		$page_dir = $this->namePageDir($item_id);
		$path_dir = $this->_data_dir.$this->_os.$page_dir.$this->_os.$cid.$this->_os;
		if(!is_dir($path_dir))
			if(!@mkdir($path_dir)) exit(error::errorMessage(array('error'=>32), $link_error));
		
		// Action
		if($action == $this->_act_insert_first)
		{
			$result = $this->actionInsertContentFirst($cid, $item_id, $link, $layout, $text, $order);
		}
		elseif($action == $this->_act_insert_before OR $action == $this->_act_insert_after)
		{
			$result = $this->actionInsertContent($cid, $item_id, $link, $layout, $text, $order);
		}
		elseif($action == $this->_act_modify)
		{
			$result = $this->actionModifyContent($cid, $link, $layout, $text);
		}
		
		if($result)
		{
			// Added Input
			if($media == $this->_type_media[0] || $media == $this->_type_media[1] || $media == $this->_type_media[2])
			{
				$query = "SELECT content_id FROM ".$this->_tbl_content_add." WHERE content_id='$cid'";
				$a = $this->_db->selectquery($query);
				if(sizeof($a) > 0)
				{
					if($media == $this->_type_media[0])
					{
						$set = "media_alt_text='$alt'";
					}
					elseif($media == $this->_type_media[1] || $media == $this->_type_media[2])
					{
						$set = "media_width=$width, media_height=$height";
					}
					$query_action = "UPDATE ".$this->_tbl_content_add." SET $set WHERE content_id='$cid'";
				}
				else
				{
					$query_action = "INSERT INTO ".$this->_tbl_content_add." (content_id, media_width, media_height, media_alt_text)
					VALUES ($cid, $width, $height, '$alt')";
				}
				$this->_db->actionquery($query_action);
				
				if($media == $this->_type_media[0])
				{
					$ext = $this->_extension_content1;
					$mimetype = array("image/jpeg","image/gif","image/png");
				}
				elseif($media == $this->_type_media[1])
				{
					$ext = $this->_extension_content2;
					$mimetype = array("application/x-shockwave-flash");
				}
				elseif($media == $this->_type_media[2])
				{
					$ext = $this->_extension_content3;
					$mimetype = array("video/quicktime", "video/ogg", "video/mp4", "video/webm");
				}
				
				$options = array('types_allowed'=>$mimetype);
				$gform->manageFile('file1', $old_file1, false, $ext, $path_dir, $link_error, $this->_tbl_content, 'img', 'content_id', $cid, $options);
				$gform->manageFile('file2', $old_file2, false, $this->_extension_attach, $path_dir, $link_error, $this->_tbl_content, 'filename', 'content_id', $cid, array('check_type'=>false));
				
				if($media == $this->_type_media[2])
				{
					$old_fileadd1 = cleanVar($_POST, 'old_fileadd1', 'string', '');
					$old_fileadd2 = cleanVar($_POST, 'old_fileadd2', 'string', '');
					$old_fileadd3 = cleanVar($_POST, 'old_fileadd3', 'string', '');
					
					$gform->manageFile('fileadd1', $old_fileadd1, false, $ext, $path_dir, $link_error, $this->_tbl_content_add, 'filename1', 'content_id', $cid, $options);
					$gform->manageFile('fileadd2', $old_fileadd2, false, $ext, $path_dir, $link_error, $this->_tbl_content_add, 'filename2', 'content_id', $cid, $options);
					$gform->manageFile('fileadd3', $old_fileadd3, false, $ext, $path_dir, $link_error, $this->_tbl_content_add, 'filename3', 'content_id', $cid, $options);
				}
			}
			
			EvtHandler::HttpCall($this->_home, $redirect, $ref_page);
		}
		else
		{
			if($action != $this->_act_modify)
				rmdir($path_dir);
			exit(error::errorMessage(array('error'=>9), $link_error));
		}
	}
	
	private function resultSearchFileName($file_new, $file_old, $directory){
		
		$listFile = searchNameFile($directory);
		$count = 0;
		if(sizeof($listFile) > 0)
		{
			foreach($listFile AS $value)
			{
				if(!empty($file_old))
				{
					if($file_new == $value AND $file_old != $value) $count++;
				}
				elseif($file_new == $value) $count++;
			}
		}
		
		return $count;
	}
	
	/**
	 * Inserimento e modifica di un blocco di pagina del tipo con inclusione HTML
	 * 
	 * Un blocco di pagina generato con l'opzione "inserisci da html" è unico per la pagina
	 * 
	 * @see $_group_2
	 */
	public function actionSingleContent(){
	
		$this->accessGroup($this->_group_2);
		
		$gform = new Form('gform', 'post', false);
		$gform->save('dataform');

		$item_id = cleanVar($_POST, 'item_id', 'int', '');
		$content_id = cleanVar($_POST, 'content_id', 'int', '');
		$layout = cleanVar($_POST, 'layout', 'int', '');
		$text_code = $layout == $this->_layout_single_code ? mysql_real_escape_string($_POST['text_code']) : cleanVar($_POST, 'text_code', 'string', '');
		$action = cleanVar($_POST, 'action', 'string', '');
		$reference = cleanVar($_POST, 'ref', 'string', '');
		$check_del_file1 = cleanVar($_POST, 'check_del_file1', 'string', '');
		
		if(isset($_FILES['file1']['name']))
		{
			$file_name = $_FILES['file1']['name'];
			$file_size = $_FILES['file1']['size'];
			$file_tmp = $_FILES['file1']['tmp_name'];
		}
		else
		{
			$file_name = '';
		}
		
		$ref_page_error = "id=$item_id&ref=$reference&action=$action&block=".$this->_block_content."&cnt=$content_id";
		$ref_page = "id=$item_id&ref=$reference&action=".$this->_act_modify."";
		$redirect = $this->_className.'-managePage';
		$link_error = $this->_home."?evt[$redirect]&$ref_page_error";
		
		/*
			Controlli
		*/
		
		if(empty($layout) OR (empty($text_code) AND empty($file_name) && $check_del_file1!='ok'))
			exit(error::errorMessage(array('error'=>2), $link_error));
		
		if($layout != $this->_layout_single_code AND $layout != $this->_layout_single_include)
			exit(error::errorMessage(array('error'=>9), $link_error));
		
		// Old file
		if($action == $this->_act_modify_single)
		{
			$query = "SELECT text FROM ".$this->_tbl_content." WHERE content_id='$content_id'";
			$a = $this->_db->selectquery($query);
			if(sizeof($a) > 0)
			{
				foreach($a AS $b)
				{
					$old_file = $b['text'];
				}
			}
			else $old_file = '';
		}
		else $old_file = '';
		
		// Content ID
		if($action == $this->_act_modify_single)
		{
			$cid = $content_id;
		}
		else	// Next id
		{
			$cid = $this->_db->autoIncValue($this->_tbl_content);
		}
		
		// Directory
		$page_dir = $this->namePageDir($item_id);
		$path_dir = $this->_data_dir.$this->_os.$page_dir.$this->_os.$cid.$this->_os;
		
		// A) Inclusione File
		if($layout == $this->_layout_single_include && empty($file_name) && $check_del_file1!='ok')
			exit(error::errorMessage(array('error'=>2), $link_error));
		
		// B) Codice
		if($layout == $this->_layout_single_code AND empty($text_code))
			exit(error::errorMessage(array('error'=>2), $link_error));
		// End Controlli
		
		$order = 1;
		if($layout == $this->_layout_single_include) $text = $file_name;
		elseif($layout == $this->_layout_single_code) $text = $text_code;
		
		if($action == $this->_act_insert_single)
		{
			$query = "INSERT INTO ".$this->_tbl_content." (content_id, item, layout, text, img, order_list)
			VALUES ($cid, $item_id, $layout, '$text', '', $order)";
			$result = $this->_db->actionquery($query);
			$del_old_file = false;
		}
		elseif($action == $this->_act_modify_single)
		{
			// Per verificare un passaggio da 'include file' a 'code html'
			$query = "SELECT layout FROM ".$this->_tbl_content." WHERE content_id='$content_id'";
			$a = $this->_db->selectquery($query);
			if(sizeof($a) > 0)
			{
				foreach($a AS $b)
				{
					$old_layout = $b['layout'];
				}
			}
			// End
			
			if($old_layout == $this->_layout_single_include AND $layout == $this->_layout_single_code)
			{
				$del_old_file = true;
			}
			else $del_old_file = false;
			// End
			
			$query = "UPDATE ".$this->_tbl_content." SET layout=$layout, text='$text' WHERE content_id='$content_id'";
			$result = $this->_db->actionquery($query);
		}
		
		if($result)
		{
			if(!is_dir($path_dir))
				if(!@mkdir($path_dir))
					exit(error::errorMessage(array('error'=>32), $link_error));
			
			if($layout == $this->_layout_single_include)
			{
				$gform->manageFile('file1', $old_file, false, $this->_extension_include, $path_dir, $link_error, $this->_tbl_content, 'text', 'content_id', $cid, array());
			}
			elseif($layout == $this->_layout_single_code)
			{
				if($del_old_file)
				{
					@unlink($path_dir.$old_file);
				}
			}
			
			EvtHandler::HttpCall($this->_home, $redirect, $ref_page);
		}
		else
			exit(error::errorMessage(array('error'=>9), $link_error));
	}
	
	// insert after or before another block
	private function actionInsertContent($new_id, $item_id, $link, $layout, $text, $order){
		
		// modify sequences
		$query = "SELECT content_id, order_list FROM ".$this->_tbl_content." WHERE item='$item_id' AND order_list>='$order'";
		$a = $this->_db->selectquery($query);
		if(sizeof($a) > 0)
		{
			foreach($a AS $b)
			{
				$order_new = $b["order_list"] + 1;
				$query_modify = "UPDATE ".$this->_tbl_content." SET order_list=$order_new WHERE content_id='$b[content_id]'";
				$this->_db->actionquery($query_modify);
			}
		}
		// end
		
		$query = "INSERT INTO ".$this->_tbl_content." (content_id, item, layout, text, link, order_list)
		VALUES ($new_id, $item_id, $layout, '$text', '$link', $order)";
		$result = $this->_db->actionquery($query);
		
		return $result;
	}
	
	private function actionInsertContentFirst($new_id, $item_id, $link, $layout, $text, $order){
		
		$query = "INSERT INTO ".$this->_tbl_content." (content_id, item, layout, text, link, order_list)
		VALUES ($new_id, $item_id, $layout, '$text', '$link', $order)";
		$result = $this->_db->actionquery($query);
		
		return $result;
	}
	
	private function actionModifyContent($content_id, $link, $layout, $text){
	
		// Per non sovrascrivere i campi quando si cambia il layout
		if($layout == $this->_layout_text) $set = '';
		elseif($layout == $this->_layout_text_img OR $layout == $this->_layout_img_text OR $layout == $this->_layout_img) $set = ", link='$link'";
		elseif($layout == $this->_layout_link_file) $set = "";
		// End
		
		$query = "UPDATE ".$this->_tbl_content." SET layout=$layout, text='$text' $set WHERE content_id='$content_id'";
		$result = $this->_db->actionquery($query);
		
		return $result;
	}
	
	/**
	 * Form per l'ordinamento dei blocchi di pagina
	 * 
	 * @param integer $item_id valore ID della pagina
	 * @param integer $content_id valore ID del blocco
	 * @param integer $order_old posizione attuale del blocco
	 * @param integer $content_tot numero di blocchi della pagina
	 * @param string $reference valori ID delle pagine che precedono nell'albero la pagina selezionata
	 * @return string
	 */
	private function orderContent($item_id, $content_id, $order_old, $content_tot, $reference){
	
		$gform = new Form('orderform', 'post', true, array("tblLayout"=>false));
		$gform->load('orderdataform');

		$GINO = $gform->form($this->_home."?evt[".$this->_className."-changeOrder]", '', '');
		$GINO .= $gform->hidden('order_old', $order_old);
		$GINO .= $gform->hidden('number_tot', $content_tot);
		$GINO .= $gform->hidden('item_id', $item_id);
		$GINO .= $gform->hidden('change_id', $content_id);
		$GINO .= $gform->hidden('ref', $reference);

		$GINO .= _("Blocco")."&nbsp;";

		$GINO .= $gform->input('order_new', 'text', $order_old, array("required"=>true, "size"=>2, "maxlength"=>2))."&nbsp;";
		$GINO .= $gform->input('submit_action', 'submit', _("ordina"), array("classField"=>"submit"));

		$GINO .= $gform->cform();
		
		return $GINO;
	}
	
	/**
	 * Ordinamento dei blocchi di pagina
	 * 
	 * @see $_group_2
	 */
	public function changeOrder(){
	
		$this->accessGroup($this->_group_2);
		
		$gform = new Form('gform', 'post', true);
		$gform->save('dataform');
		
		$order_new = cleanVar($_POST, 'order_new', 'int', '');	// nuova numerazione
		$order_old = cleanVar($_POST, 'order_old', 'int', '');	// vecchia numerazione
		$number_tot = cleanVar($_POST, 'number_tot', 'int', '');	// contenuti totali
		
		$item_id = cleanVar($_POST, 'item_id', 'int', '');
		$change_id = cleanVar($_POST, 'change_id', 'int', '');
		$reference = cleanVar($_POST, 'ref', 'string', '');
		
		$ref_page = "id=$item_id&ref=$reference&action=".$this->_act_modify."";
		
		if(!empty($order_new) AND !empty($order_old) AND !empty($change_id) AND !empty($number_tot))
		{
			if($order_new > $number_tot) $order_new = $number_tot;
			
			$orderNumberTemporary = $number_tot + 2;
			
			// vecchio numero minore del nuovo
			if($order_old < $order_new)
			{
				// spostamento item da modificare
				$query = "UPDATE ".$this->_tbl_content." SET order_list=$orderNumberTemporary WHERE content_id='$change_id'";
				$this->_db->actionquery($query);
				
				// selezione item del blocco precedente il nuovo numero
				$query = "SELECT content_id FROM ".$this->_tbl_content." WHERE item='$item_id' AND order_list<='$order_new' ORDER BY order_list";
				$a = $this->_db->selectquery($query);
				if(sizeof($a) > 0)
				{
					$count = 1;	// inizio contatore
					foreach($a AS $b)
					{
						// spostamento item blocco precedente
						$query = "UPDATE ".$this->_tbl_content." SET order_list=$count WHERE content_id='$b[content_id]'";
						$this->_db->actionquery($query);
						$count++;
					}
				}
				
				// posizionamento item da modificare
				$query = "UPDATE ".$this->_tbl_content." SET order_list=$order_new WHERE content_id='$change_id'";
				$this->_db->actionquery($query);
			}
			// vecchio numero maggiore del nuovo
			elseif($order_old > $order_new)
			{
				// spostamento item da modificare
				$query = "UPDATE ".$this->_tbl_content." SET order_list=$orderNumberTemporary WHERE content_id='$change_id'";
				$this->_db->actionquery($query);
				
				// selezione item del blocco successivo il nuovo numero
				$query = "SELECT content_id FROM ".$this->_tbl_content." WHERE item='$item_id' AND order_list>='$order_new' ORDER BY order_list";
				$a = $this->_db->selectquery($query);
				if(sizeof($a) > 0)
				{
					$count = $order_new + 1;	// inizio contatore
					foreach($a AS $b)
					{
						// spostamento item blocco precedente
						$query = "UPDATE ".$this->_tbl_content." SET order_list=$count WHERE content_id='$b[content_id]'";
						$this->_db->actionquery($query);
						$count++;
					}
				}
				
				// posizionamento item da modificare
				$query = "UPDATE ".$this->_tbl_content." SET order_list=$order_new WHERE content_id='$change_id'";
				$this->_db->actionquery($query);
			}
			else
			{
				EvtHandler::HttpCall($this->_home, $this->_className.'-managePage', $ref_page);
			}
		}
		
		EvtHandler::HttpCall($this->_home, $this->_className.'-managePage', $ref_page);
	}
	
	/**
	 * Visualizzazione degli elementi multimediali dei blocchi di pagina
	 * 
	 * @param integer $content_id valore ID del blocco
	 * @param string $filename nome del file multimediale (campo @a img della tabella page_block)
	 * @param string $dirname percorso relativo del file multimediale
	 * @param string $link collegamento associato a un file multimediale (campo @a link della tabella page_block)
	 * @param array $options
	 *   array associativo di opzioni
	 *   - @b preview (boolean): attivare di un preview dell'immagine
	 *   - @b width (string): larghezza dell'immagine di preview
	 * @return string
	 */
	private function selectMedia($content_id, $filename, $dirname, $link, $options=array()){
		
		$preview = array_key_exists('preview', $options) ? $options['preview'] : false;
		$preview_width = array_key_exists('width', $options) ? $options['width'] : $this->_image_width;
		
		$path = $dirname.$filename;
		$type_media = $this->typeMedia($filename);
		
		$query = "SELECT media_width, media_height, media_alt_text, filename1, filename2, filename3 FROM ".$this->_tbl_content_add." WHERE content_id='$content_id'";
		$a = $this->_db->selectquery($query);
		if(sizeof($a) > 0)
		{
			foreach($a AS $b)
			{
				$width = htmlChars($b['media_width']);
				$height = htmlChars($b['media_height']);
				$alt = htmlChars($b['media_alt_text']);
				$fileadd1 = $b['filename1'];
				$fileadd2 = $b['filename2'];
				$fileadd3 = $b['filename3'];
			}
		}
		else
		{
			$width = '';
			$height = '';
			$alt = '';
			$fileadd1 = $fileadd2 = $fileadd3 = '';
		}
		
		$media = '';
		
		if($type_media == $this->_type_media[0])
		{
			if(!empty($alt)) $alt_text = $alt; else $alt_text = _("media pagina");
			$params = $preview ? "width=\"$preview_width\"" : '';
			
			$media .= "<img src=\"$path\" alt=\"$alt_text\" $params />";
		}
		elseif($type_media == $this->_type_media[1])
		{
			$pos = strrpos($filename, '.');
			$object_id = substr($filename, 0, $pos);
			
			$media .= "<object classid=\"clsid:d27cdb6e-ae6d-11cf-96b8-444553540000\" codebase=\"http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0\" width=\"$width\" height=\"$height\" id=\"$object_id\" align=\"middle\">\n";
			$media .= "<param name=\"allowScriptAccess\" value=\"sameDomain\" />\n";
			$media .= "<param name=\"allowFullScreen\" value=\"false\" />\n";
			$media .= "<param name=\"movie\" value=\"$path\" />\n";
			$media .= "<param name=\"quality\" value=\"high\" />\n";
			$media .= "<param name=\"bgcolor\" value=\"#ffffff\" />\n";
			$media .= "<embed src=\"$path\" quality=\"high\" bgcolor=\"#ffffff\" width=\"$width\" height=\"$height\" name=\"$object_id\" align=\"middle\" wmode=\"transparent\" allowScriptAccess=\"sameDomain\" allowFullScreen=\"false\" type=\"application/x-shockwave-flash\" pluginspage=\"http://www.macromedia.com/go/getflashplayer\" />\n";
			$media .= "</object>\n";
		}
		elseif($type_media == $this->_type_media[2])
		{
			if($preview)
			{
				$media .= $filename;
				if($fileadd1) $media .= "<br />".$fileadd1;
				if($fileadd2) $media .= "<br />".$fileadd2;
				if($fileadd3) $media .= "<br />".$fileadd3;
			}
			else
			{
				$a_video = array();
				if($filename)
				{
					$ext = $this->extensionFile($filename);
					$a_video[$ext] = $filename;
				}
				if($fileadd1)
				{
					$ext = $this->extensionFile($fileadd1);
					$a_video[$ext] = $fileadd1;
				}
				if($fileadd2)
				{
					$ext = $this->extensionFile($fileadd2);
					$a_video[$ext] = $fileadd2;
				}
				if($fileadd3)
				{
					$ext = $this->extensionFile($fileadd3);
					$a_video[$ext] = $fileadd3;
				}
				
				$width = $width ? "width=\"$width\"" : '';
				$height = $height ? "height=\"$height\"" : '';
				
				$media .= "<video $width $height preload controls>";
				
				if(array_key_exists('mp4', $a_video))
					$media .= "<source src=\"".$dirname.$a_video['mp4']."\" type='video/mp4; codecs=\"avc1.42E01E, mp4a.40.2\"' />";
				
				if(array_key_exists('webm', $a_video))
					$media .= "<source src=\"".$dirname.$a_video['webm']."\" type='video/webm; codecs=\"vp8, vorbis\"' />";
				
				if(array_key_exists('ogv', $a_video))
					$media .= "<source src=\"".$dirname.$a_video['ogv']."\" type='video/ogg; codecs=\"theora, vorbis\"' />";
				
				if(array_key_exists('mov', $a_video))
				{
					$media = "<object classid=\"clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B\" codebase=\"http://www.apple.com/qtactivex/qtplugin.cab\" $height $width>\n";
					$media .= "<param name=\"src\" value=\"".$dirname.$a_video['mov']."\" />\n";
					$media .= "<param name=\"autoplay\" value=\"true\" />\n";
					$media .= "<param name=\"controller\" value=\"false\" />\n";
					$media .= "<embed pluginspage=\"http://www.apple.com/quicktime/download/\" src=\"".$dirname.$a_video['mov']."\" type=\"video/quicktime\" controller=\"true\" autoplay=\"true\" $height $width />\n";
					$media .= "</object>\n";
				}
				$media .= "</video>";
			}
		}
		
		if(!empty($link)) $GINO = "<a href=\"$link\">$media</a>"; else $GINO = $media;
		
		return $GINO;
	}
	
	/*
		MASTER PAGES (MODULE)
	*/
	
	/**
	 * Form di inserimento di una nuova pagina principale (modulo-pagina) e di modifica delle opzioni di una pagina principale
	 * 
	 * @see $_access_module
	 * @param integer $id valore ID della pagina
	 * @param string $reference corrisponde al valore del parametro $i
	 * @return string
	 */
	private function formModule($id, $reference){

		$gform = new Form('gform', 'post', true, array("trnsl_table"=>$this->_tbl_module, "trnsl_id"=>$id));
		$gform->load('dataform');
		
		if($this->_action == $this->_act_modify)
		{
			$module = $this->_db->getFieldFromId($this->_tbl_page, 'module', 'item_id', $id);
			$title_page = $this->_db->getFieldFromId($this->_tbl_page, 'title', 'item_id', $id);
			
			$query = "SELECT label, role1 FROM ".$this->_tbl_module." WHERE id='$module'";
			$a = $this->_db->selectquery($query);
			if(sizeof($a) > 0)
			{
				foreach($a AS $b)
				{
					$label = htmlInput($b['label']);
					$role1 = htmlInput($b['role1']);
				}
				$title = _("Opzioni")." '$title_page'";
				$submit = _("modifica");
			}
		}
		else
		{
			$module = '';
			$label = $gform->retvar('label', '');
			$role1 = $gform->retvar('role1', $this->_min_role);

			$title = _("Nuova pagina principale");
			$submit = _("inserisci");
		}

		$required = 'label';

		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'h1', 'headerLabel'=>$title));

		$required = '';
		$GINO = $gform->form($this->_home."?evt[".$this->_className."-actionModule]", '', $required);
		$GINO .= $gform->hidden('id', $id);
		$GINO .= $gform->hidden('mdl', $module);
		$GINO .= $gform->hidden('action', $this->_action);
		$GINO .= $gform->hidden('ref', $reference);

		$name = $this->_action==$this->_act_modify? _("Etichetta"):_("Nome");	
		
		$GINO .= $gform->cinput('label', 'text', $label, $name, array("required"=>true, "size"=>30, "maxlength"=>100, "trnsl"=>true, "field"=>"label"));
		
		$role_list = $this->_access->listRole();

		$GINO .= $gform->cradio('role1', $role1, $role_list, '', _("Permessi di visualizzazione"), array("required"=>true, "aspect"=>"v"));
		
		$GINO .= $gform->cinput('submit', 'submit', $submit, '', array("classField"=>"submit"));
		$GINO .= $gform->cform();

		$htmlsection->content = $GINO;
		
		return $htmlsection->render();
	}
	
	/**
	 * Inserimento di una nuova pagina principale e di modifica delle opzioni di una pagina principale
	 * 
	 * @see $_access_module
	 */
	public function actionModule(){

		$this->accessType($this->_access_module);

		$gform = new Form('gform', 'post', false);
		$gform->save('dataform');
		$req_error = $gform->arequired();
		
		$id = cleanVar($_POST, 'id', 'int', '');	// Page ID
		$mdl = cleanVar($_POST, 'mdl', 'int', '');	// Module ID
		$action = cleanVar($_POST, 'action', 'string', '');
		$ref = cleanVar($_POST, 'ref', 'int', '');
		
		$label = cleanVar($_POST, 'label', 'string', '');
		$role1 = cleanVar($_POST, 'role1', 'int', '');
		
		$link = "id=$id&ref=$ref&action=$action&block=".$this->_block_module;
		$redirect = $this->_className.'-managePage';
		$link_error = $this->_home."?evt[$redirect]&$link";
		
		if($req_error > 0 || empty($label) || empty($role1) || ($this->_action == $this->_act_modify AND empty($id))) 
			exit(error::errorMessage(array('error'=>1), $link_error));
		
		$type = $this->_module_type[0];
		$public = 'yes';
		$masquerade = 'no';
		$role_group = 0;
		$role2 = $this->_min_role;
		$role3 = $this->_min_role;
		
		if($action == $this->_act_modify)
		{
			$query = "UPDATE ".$this->_tbl_module." SET label='$label', role1=$role1 WHERE id='$mdl'";
			$result = $this->_db->actionquery($query);
		}
		elseif($action == $this->_act_insert)
		{
			// Next Module ID
			$mdl = $this->_db->autoIncValue($this->_tbl_module);
			
			// Create Directory
			$directory = $mdl;	// directory name
			$path_directory = $this->_data_dir.$this->_os.$directory;
			
			if(!@mkdir($path_directory, 0755))
				exit(error::errorMessage(array('error'=>32), $link_error));
			// End
			
			$query_module = "INSERT INTO ".$this->_tbl_module."
			(id, label, name, type, role1, role2, role3, directory, masquerade, role_group)
			VALUES
			($mdl, '$label', '', '$type', $role1, $role2, $role3, '$directory', '$masquerade', $role_group)";
			$result_module = $this->_db->actionquery($query_module);

			if($result_module)
			{
				// Position
				$query_position = "INSERT INTO ".$this->_tbl_position." (code) VALUES ($mdl)";
				$this->_db->actionquery($query_position);

				// Page
				$date = date("Y-m-d H:i:s");

				$query_page = "INSERT INTO ".$this->_tbl_page." (module, parent, date, title, subtitle)
				VALUES ($mdl, 0, '$date', '$label', '')";
				$result_page = $this->_db->actionquery($query_page);
			}
			else 
			{
				@rmdir($path_directory);
				exit(error::errorMessage(array('error'=>9), $link_error));
			}
		}
		
		if (($action == $this->_act_modify AND $result) OR $action == $this->_act_insert AND $result_page) 
			EvtHandler::HttpCall($this->_home, $redirect, $link);
		else 
			exit(error::errorMessage(array('error'=>9), $link_error));
	}
	
	/**
	 * Form di eliminazione di una pagina principale
	 * 
	 * @see $_access_module
	 * @param integer $id valore ID della pagina
	 * @param string $action azione da eseguire
	 * @param string $reference corrisponde al valore del parametro $i
	 * @return string
	 */
	private function formDeleteModule($id, $action, $reference){

		$gform = new Form('gform', 'post', false);
		$gform->load('dataform');

		$module = $this->_db->getFieldFromId($this->_tbl_page, 'module', 'item_id', $id);
		$title = $this->_db->getFieldFromId($this->_tbl_page, 'title', 'item_id', $id);
		
		$GINO = '';

		$query = "SELECT label FROM ".$this->_tbl_module." WHERE id='$module' AND type='".$this->_module_type[0]."'";
		$a = $this->_db->selectquery($query);
		if(sizeof($a) > 0)
		{
			foreach($a AS $b)
			{
				$label = htmlChars($b['label']);
			}

			$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'h1', 'headerLabel'=>_("Elimina pagina principale")." '$title'"));

			$required = '';
			$GINO .= $gform->form($this->_home."?evt[".$this->_className."-actionDeleteModule]", '', $required);
			$GINO .= $gform->hidden('id', $id);
			$GINO .= $gform->hidden('mdl', $module);
			$GINO .= $gform->hidden('block', $this->_block_module);
			$GINO .= $gform->hidden('action', $this->_act_delete);
			$GINO .= $gform->hidden('ref', $reference);

			$GINO .= $gform->cinput('delete_action', 'submit', _("elimina"), array(_("Attenzione!"), _("l'eliminazione è definitiva e comporta l'eliminazione delle pagine che seguono nell'albero")), array("classField"=>"submit"));
			$GINO .= $gform->cform();
		}

		$htmlsection->content = $GINO;
		
		return $htmlsection->render();
	}

	/**
	 * Eliminazione di una pagina principale
	 * 
	 * @see $_access_module
	 */
	public function actionDeleteModule(){

		$this->accessType($this->_access_module);

		$id = cleanVar($_POST, 'id', 'int', '');
		$mdl = cleanVar($_POST, 'mdl', 'int', '');
		$block = cleanVar($_POST, 'block', 'string', '');
		$action = cleanVar($_POST, 'action', 'string', '');
		$ref = cleanVar($_POST, 'ref', 'int', '');

		$link = '';
		$redirect = $this->_className.'-managePage';
		$link_error = $this->_home."?evt[$redirect]&id=$id&ref=$ref&action=".$this->_act_delete."&block=".$this->_block_module;

		if($action == $this->_act_delete AND $block == $this->_block_module)
		{
			if(!empty($id) AND !empty($mdl))
			{
				$query = "SELECT directory FROM ".$this->_tbl_module." WHERE id='$mdl'";
				$a = $this->_db->selectquery($query);
				if(sizeof($a) > 0)
				{
					foreach($a AS $b)
					{
						$directory = htmlInput($b['directory']);
					}
				}
				else
					exit(error::errorMessage(array('error'=>9), $link_error));

				// 1. Module
				$query_module = "DELETE FROM ".$this->_tbl_module." WHERE id='$mdl'";
				$this->_db->actionquery($query_module);

				// 2. Directory
				$path_directory = $this->_data_dir.$this->_os.$directory;
				$this->deleteFileDir($path_directory, true);
				
				// 3. Position
				$query_position = "DELETE FROM ".$this->_tbl_position." WHERE code='$mdl'";
				$this->_db->actionquery($query_position);

				// 4. Pages
				$query_page = "SELECT item_id FROM ".$this->_tbl_page." WHERE module='$mdl'";
				$a = $this->_db->selectquery($query_page);
				if(sizeof($a) > 0)
				{
					foreach($a AS $b)
					{
						$page = $b['item_id'];
						
						language::deleteTranslations($this->_tbl_page, $page);
						
						$query_content = "SELECT content_id FROM ".$this->_tbl_page_block." WHERE item='$page'";
						$c = $this->_db->selectquery($query_content);
						if(sizeof($c) > 0)
						{
							foreach($c AS $d)
							{
								$content = $d['content_id'];
								language::deleteTranslations($this->_tbl_page_block, $content);
							}
						}
						
						$query_content = "DELETE FROM ".$this->_tbl_page_block." WHERE item='$page'";
						$this->_db->actionquery($query_content);
					}
				}

				$query_page = "DELETE FROM ".$this->_tbl_page." WHERE module='$mdl'";
				$this->_db->actionquery($query_page);
				
				EvtHandler::HttpCall($this->_home, $redirect, $link);
			}
			else
				exit(error::errorMessage(array('error'=>1), $link_error));
		}

		exit(error::errorMessage(array('error'=>9), $link_error));
	}
	
	/**
	 * Layout di default di un blocco pagina
	 * 
	 * @return integer
	 */
	private function defaultLayout(){
	
		$default = '';
		
		$query = "SELECT id FROM ".$this->_tbl_content_layout." WHERE default_value='yes'";
		$a = $this->_db->selectquery($query);
		if(sizeof($a) > 0)
		{
			foreach($a AS $b)
			{
				$default = $b["id"];
			}
		}
		
		return $default;
	}
	
	/**
	 * Definizione dei parametri da utilizzare per il modulo "Ricerca nel sito"
	 * 
	 * Il modulo "Ricerca nel sito" richiama questo metodo per ottenere informazioni riguardo alla tabella, campi, pesi ecc... per effettuare la ricerca dei contenuti
	 * 
	 * @see searchSite::results()
	 * @return array array associativo contenente i parametri per la ricerca
	 */
	public function searchSite() {
	
		return array("table"=>"page AS p, page_block AS pb", "selected_fields"=>array("p.item_id", array("highlight"=>true, "field"=>"p.title"), array("highlight"=>true, "field"=>"p.subtitle"), array("highlight"=>true, "field"=>"pb.text")), "required_clauses"=>array("p.item_id"=>array("field"=>true, "value"=>"pb.item")), "weight_clauses"=>array("p.title"=>array("weight"=>3), "p.subtitle"=>array("weight"=>2), "pb.text"=>array("weight"=>1)));
	}

	/**
	 * Definisce la presentazione del singolo item trovato a seguito di ricerca (modulo "Ricerca nel sito")
	 * 
	 * @see searchSite::results()
	 * @param array array associativo contenente i risultati della ricerca
	 * @return string
	 */
	public function searchSiteResult($results) {
	
		$buffer = "<div><a href=\"$this->_home?evt[$this->_className-displayItem]&id=".$results['p.item_id']."\">";
		$buffer .= $results['p.title'] ? htmlChars($results['p.title']) : htmlChars($this->_db->getFieldFromId($this->_tbl_page, 'title', 'item_id', $results['p.item_id']));
		$buffer .= "</a></div>";
		if($results['pb.text']) $buffer .= "<div class=\"search_text_result\">...".htmlChars($results['pb.text'])."...</div>";
		return $buffer;
	}
}
?>
