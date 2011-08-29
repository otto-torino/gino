<?php
/*================================================================================
Gino - a generic CMS framework
Copyright (C) 2005  Otto Srl - written by Marco Guidotti

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

For additional information: <opensource@otto.to.it>
================================================================================*/

require_once(CLASSES_DIR.OS."class.account.php");
require_once(CLASSES_DIR.OS."class.email.php");

class user extends AbstractEvtClass{

	protected $_instance, $_instanceName;
	private $_account;
	
	private $_title;
	
	private $_properties, $_options;
	public $_optionsLabels;

	private $_group_1;

	private $_report_dir;

	private $_extension_media;
	private $_user_for_page;
	private $_lng_tbl_nation;

	public static $width_img = 200;
	public static $width_thumb = 50;
	public static $prefix_img = 'img_';
	public static $prefix_thumb = 'thumb_';
	
	private $_user_view, $_email_mod, $_info_media, $_username_email;
	private $_aut_validation, $_aut_publication, $_aut_registration;
	private $_user_other;
	
	public $_other_field1, $_other_field2, $_other_field3;
	private $_label_field1, $_label_field2, $_label_field3;

	private $_length_pwd_min, $_length_pwd_max, $_length_pwd, $_pwd_number, $_pwd_automatic;
	private $_action, $_block;

	function __construct(){

		parent::__construct();

		$this->_instance = 0;
		$this->_instanceName = $this->_className;

		$this->setAccess();
		$this->setGroups();
		
		$this->_action = cleanVar($_REQUEST, 'action', 'string', '');
		$this->_block = cleanVar($_REQUEST, 'block', 'string', '');

		// options
		$this->_title = htmlChars($this->setOption('title', true));
		$this->_user_other = $this->setOption('more_info');
		$this->_info_media = $this->setOption('media_info');
		$this->_user_view = $this->setOption('user_card_view');
		$this->_aut_validation = $this->setOption('aut_valid');
		$this->setUserForPage($this->setOption('users_for_page'));
		$this->_aut_registration = $this->setOption('aut_registration');
		$this->_email_mod = $this->setOption('mod_email');
		$this->_username_email = $this->setOption('username_email');
		$this->_pwd_automatic = $this->setOption('aut_pwd');
		$this->_length_pwd = $this->setOption('pwd_length');
		$this->_length_pwd_min = $this->setOption('pwd_min_length');
		$this->_length_pwd_max = $this->setOption('pwd_max_length');
		$this->_pwd_number = $this->setOption('pwd_number');
		
		$this->_aut_publication = false;	// pub='yes|no' nell'inserimento di un nuovo utente

		// the second paramether will be the class instance
		$this->_options = new options($this->_className, $this->_instance);
		$this->_optionsLabels = array(
		"title"=>_("Titolo"),
		"more_info"=>_("Informazioni aggiuntive utenti"),
		"media_info"=>_("Informazioni multimediali utenti"),
		"user_card_view"=>_("Schede utenti visibili"),
		"aut_valid"=>_("Utenti attivi automaticamente"),
		"users_for_page"=>_("Utenti per pagina"),
		"aut_registration"=>_("Registrazione autonoma"),
		"mod_email"=>_("Personalizzazione email di conferma"),
		"username_email"=>_("Utilizzo email come username"),
		"aut_pwd"=>_("Generazione automatica password"),
		"pwd_length"=>_("Caratteri della password automatica"),
		"pwd_min_length"=>_("Minimo caratteri password"),
		"pwd_max_length"=>_("Massimo caratteri password"),
		"pwd_number"=>_("Caratteri numerici password")
		);

		$this->_report_dir = $this->_data_dir;

		$this->_extension_media = array('jpg', 'png');
		$this->_lng_tbl_nation = $this->_lng_nav;

		// Class account
		$this->_account = new account($this, $this->_className);
		$this->_properties = array(
			'a'=>'',
			'b'=>'',
			'c'=>''
		);
		// End
		
		$this->setAddUserData();
	}
	
	public function __get($property_name){
		
		return isset($this->_properties[$property_name]) ? $this->_properties[$property_name]:null;
	}
	
	/*
	public function __set($property_name, $value){
		
		$this->_properties[$property_name] = $value;
	}
	*/
	
	private function setGroups(){

		// Assistenti
		$this->_group_1 = array($this->_list_group[0], $this->_list_group[1]);
	}

	public static function permission(){

		$access_2 = _("Permessi di registrazione tramite form utente");// _("accesso form di registrazione utenti")
		$access_3 = _("Permessi di modifica dati personali");
		return array($access_2, $access_3);
	}

	private function getUserForPage() {
		return $this->_user_for_page;
	}

	private function setUserForPage($req_var) {

		if($req_var) $this->_user_for_page = $req_var;
		else $this->_user_for_page = 20;
	}

	private function setAddUserData(){

		/*
		Default value:
		empty => not visible
		yes|no => visible
		*/
		$this->_other_field1 = '';	// yes|no
		$this->_other_field2 = '';
		$this->_other_field3 = '';

		$this->_label_field1 = '';	// example: _("Azienda")
		$this->_label_field2 = '';
		$this->_label_field3 = '';
	}

	/*
	 * Funzioni che possono essere richiamate da menu e messe all'interno del template;
	 * array ("function" => array("label"=>"description", "role"=>"privileges"))
	 */
	public static function outputFunctions() {
	
		$list = array(
			"blockList" => array("label"=>_("Elenco utenti"), "role"=>'1'),
			"viewList" => array("label"=>_("Elenco utenti e schede informazioni"), "role"=>'1'),
			"userCard" => array("label"=>_("Scheda utente connesso"), "role"=>'3'),
			"registration" => array("label"=>_("Form di registrazione autonoma"), "role"=>'2'),
			"personal" => array("label"=>_("Modifica dati personali"), "role"=>'3')
		);

		return $list;
	
	}

	public function blockList(){

		$this->accessType($this->_access_base);

		$GINO = '';

		$query = "SELECT user_id, firstname, lastname FROM ".$this->_tbl_user." WHERE pub='yes' ORDER BY lastname ASC, firstname ASC";
		$a = $this->_db->selectquery($query);
		if(sizeof($a) > 0)
		{
			$GINO .= "<div class=\"lista\">\n";
			$GINO .= "<dl>\n";
			foreach($a AS $b)
			{
				$id = htmlChars($b['user_id']);
				$firstname = htmlChars($b['firstname']);
				$lastname = htmlChars($b['lastname']);

				$GINO .= "<dt><a href=\"".$this->_home."?evt[".$this->_className."-viewList]&amp;id=$id\">$firstname $lastname</a></dt>\n";
				$GINO .= "<dd></dd>\n";
			}
			$GINO .= "</dl>\n";
			$GINO .= "</div>\n";

			$GINO .= "<p class=\"link\"><a href=\"".$this->_home."?evt[".$this->_className."-viewList]\">"._("elenco completo")."</a></p>\n";
		}

		return $GINO;
	}

	public function viewList(){

		$this->accessType($this->_access_base);
		
		if(!$this->_user_view) exit();

		return $this->viewListData();
	}

	private function viewListData(){

		$id = cleanVar($_GET, 'id', 'int', '');

		$GINO = "<div class=\"vertical_1\">\n";

		$htmlsection = new htmlSection(array('class'=>'public', 'headerTag'=>'header', 'headerLabel'=>$this->_title));

		$query = "SELECT user_id, firstname, lastname FROM ".$this->_tbl_user." WHERE pub='yes'
		ORDER BY lastname ASC, firstname ASC";
		$a = $this->_db->selectquery($query);
		if(sizeof($a) > 0)
		{
			$htmlList = new htmlList(array("numItems"=>sizeof($a), "separator"=>true));
			$list = $htmlList->start();
			foreach($a AS $b)
			{
				$user_id = htmlChars($b['user_id']);
				$firstname = htmlChars($b['firstname']);
				$lastname = htmlChars($b['lastname']);

				$selected = $id==$user_id?true:false;
				$item_label = "<a href=\"".$this->_home."?evt[".$this->_className."-viewList]&amp;id=$user_id\">$firstname $lastname</a>\n";
				$list .= $htmlList->item($item_label, null, $selected, true);
			}
			$list .= $htmlList->end();

		}
		$htmlsection->content = $list;
		$GINO .= $htmlsection->render();
		$GINO .= "</div>\n";	// End vertical_1

		$GINO .= "<div class=\"vertical_2\">\n";
		$GINO .= $id? $this->cardUser($id):$this->infoUser();
		$GINO .= "</div>\n";

		$GINO .= "<div class=\"null\"></div>";

		return $GINO;
	}

	private function infoUser(){

		$htmlsection = new htmlSection(array('class'=>'public', 'headerTag'=>'header', 'headerLabel'=>_("Informazioni")));

		$GINO = "<p>"._("...")."</p>\n";

		$htmlsection->content = $GINO;

		return $htmlsection->render();
	}

	private function cardUser($id){

		$query = "SELECT firstname, lastname, text, photo FROM ".$this->_tbl_user." WHERE user_id=$id AND pub='yes'";
		$a = $this->_db->selectquery($query);
		if(sizeof($a) > 0)
		{
			$htmlsection = new htmlSection(array('class'=>'public', 'headerTag'=>'header'));

			foreach($a AS $b)
			{
				$firstname = htmlChars($b['firstname']);
				$lastname = htmlChars($b['lastname']);
				$text = htmlChars($b['text']);
				$photo = htmlChars($b['photo']);
				$htmlsection->headerLabel = "$firstname $lastname";

				$GINO = '';
				if(!empty($photo))
				{
					$file = $this->_data_www."/".self::$prefix_img."$photo";
					$GINO .= "<img src=\"$file\" alt=\""._("immagine")."\"/>";
				}

				if(!empty($text))
				{
					$GINO .= "<p>$text</p>";
				}
			}
			$htmlsection->content = $GINO;
		
			return $htmlsection->render();
		}

		return '';
	}

	public function userCard() {
		
		$this->accessType($this->_access_3);

		$htmlsection = new htmlSection(array('class'=>'public', 'headerTag'=>'header'));

		$query = "SELECT * FROM ".$this->_tbl_user." WHERE user_id=".$this->_session_user." AND pub='yes'";
		$a = $this->_db->selectquery($query);
		if(sizeof($a) > 0)
		{

			foreach($a AS $b)
			{
				$firstname = htmlChars($b['firstname']);
				$lastname = htmlChars($b['lastname']);
				$company = htmlChars($b['company']);
				$phone = htmlChars($b['phone']);
				$fax = htmlChars($b['fax']);
				$email = htmlChars($b['email']);
				$address = htmlChars($b['address']);
				$cap = htmlChars($b['cap']);
				$city = htmlChars($b['city']);
				$nation = htmlChars($b['nation']);
				$text = htmlChars($b['text']);
				$photo = htmlChars($b['photo']);
				$date = htmlChars($b['date']);

				$htmlsection->headerLabel = _("Scheda utente");

				$GINO = $this->scriptAsset("user.css", "userCSS", 'css');
				$GINO .= "<table class=\"userCard\">";
				$GINO .= "<tr><th style=\"width:".(self::$width_thumb+10)."px\"><div style=\"text-align:center\">";
				if(!empty($photo)) {
					$file = $this->_data_www."/".self::$prefix_thumb."$photo";
					$GINO .= "<img src=\"$file\" alt=\""._("immagine")."\"/>";
				}
				$GINO .= "<p><b>$firstname $lastname</b></p>";
				$GINO .= "<p>Registrato il ".dbDatetimeToDate($date, "/")."</b></p>";
				$GINO .= "</div></th>";
				$GINO .= "<td>";
				if($company) $GINO .= "<p><b>"._("Ragione sociale")."</b>: ".$company."</p>";
				if($phone) $GINO .= "<p><b>"._("Telefono")."</b>: ".$phone."</p>";
				if($fax) $GINO .= "<p><b>"._("Fax")."</b>: ".$fax."</p>";
				if($email) $GINO .= "<p><b>"._("Email")."</b>: ".$email."</p>";
				if($address) $GINO .= "<p><b>"._("Indirizzo")."</b>: ".$address."</p>";
				if($cap) $GINO .= "<p><b>"._("CAP")."</b>: ".$cap."</p>";
				if($city) $GINO .= "<p><b>"._("Città")."</b>: ".$city."</p>";
				if($nation) $GINO .= "<p><b>"._("Nazione")."</b>: ".$this->_db->getFieldFromId($this->_tbl_nation, $this->_lng_nav, 'id', $nation)."</p>";
				if($text) $GINO .= "<p><b>"._("Informazioni")."</b>: ".$text."</p>";
				$GINO .= "</td></tr>";
				$GINO .= "</table>";

				$GINO .= "<p><a href=\"$this->_home?evt[$this->_className-personal]\">"._("Modifica password o dati personali")."</a></p>";
			}
			$htmlsection->content = $GINO;
		
			return $htmlsection->render();
		}

		return '';

	}

	public function manageUser(){

		$this->accessGroup('ALL');

		$htmltab = new htmlTab(array("linkPosition"=>'right', "title"=>$this->_title));	
		$link_admin = "<a href=\"".$this->_home."?evt[$this->_className-manageUser]&block=permissions\">"._("Permessi")."</a>";
		$link_options = "<a href=\"".$this->_home."?evt[$this->_className-manageUser]&block=options\">"._("Opzioni")."</a>";
		$link_email = "<a href=\"".$this->_home."?evt[$this->_className-manageUser]&block=email\">"._("Email")."</a>";
		$link_dft = "<a href=\"$this->_home?evt[$this->_className-manageUser]\">"._("Gestione")."</a>";
		$sel_link = $link_dft;

		$GINO = $this->scriptAsset("user.css", "userCSS", 'css');

		if($this->_block == 'options') {$GINO .= sysfunc::manageOptions(null, $this->_className); $sel_link = $link_options;}
		elseif($this->_block == 'permissions' && $this->_access->AccessVerifyGroupIf($this->_className, $this->_instance, '', '')) {
			$GINO .= sysfunc::managePermissions(null, $this->_className); 
			$sel_link = $link_admin;
		}
		elseif($this->_block == 'email' && $this->_access->AccessVerifyGroupIf($this->_className, $this->_instance, '', '') && $this->_email_mod) {
			$GINO .= sysfunc::manageEmail(null, $this->_className); 
			$sel_link = $link_email;
		}
		elseif($this->_action == $this->_act_insert) $GINO .= $this->newUser();
		elseif($this->_action == $this->_act_modify || $this->_action==$this->_act_delete) $GINO .= $this->modifyUser();
		else $GINO .= $this->listUser();
		
		if($this->_access->AccessVerifyGroupIf($this->_className, $this->_instance, '', ''))
			$links_array = $this->_email_mod 
				? array($link_email, $link_admin, $link_options, $link_dft)
				: array($link_admin, $link_options, $link_dft);
		else $links_array = array($link_options, $link_dft);

		$htmltab->navigationLinks = $links_array;
		$htmltab->selectedLink = $sel_link;
		$htmltab->htmlContent = $GINO;
		return $htmltab->render();
	}

	private function selectBar(){

		$search = cleanVar($_GET, 's', 'int', '');
		if(!empty($search)) $var = "&amp;s=$search"; else $var = '';
		
		$list = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'z');

		$bar = '';
		foreach($list AS $value)
		{
			$bar .= "| <a href=\"".$this->_home."?evt[".$this->_className."-manageUser]&amp;char=$value".$var."\">$value</a> ";
		}
		$bar .= "|";
		$bar .= "&nbsp;<a href=\"".$this->_home."?evt[".$this->_className."-manageUser]\">"._("tutti")."</a> |";

		return "<p class=\"center\">$bar</p>";
	}
	
	private function listUser(){
		
		$link_insert = "<a href=\"".$this->_home."?evt[".$this->_className."-manageUser]&action=$this->_act_insert\">".$this->icon('insert', '')."</a>";
		$link_export = $this->exportFile();
		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'header', 'headerLabel'=>_("Elenco utenti di sistema")));
		$htmlsection->headerLinks = array($link_export, $link_insert);

		$char = cleanVar($_GET, 'char', 'string', '');
		$search = cleanVar($_GET, 's', 'int', '');

		// Search Options
		if(empty($search) OR $search == 1)
		{
			$order = "name ASC";
			
			if(!empty($char)) $where = "WHERE lastname LIKE '$char%'";
			else $where = '';
		}
		elseif($search == 2)
		{
			$order = "company ASC, name ASC";
			
			if(!empty($char)) $where = "WHERE company LIKE '$char%' AND company!=''";
			else $where = "WHERE company!=''";
		}
		// End
		
		$GINO = $this->textNewUser();
		$GINO .= $this->selectBar()."\n";

		$queryTotUsers = "SELECT user_id FROM ".$this->_tbl_user." $where";
		$this->_list = new PageList($this->_user_for_page, $queryTotUsers, 'query');

		$concat = $this->_db->concat(array("lastname", "' '", "firstname"));
		$limit = $this->_db->limit($this->_list->rangeNumber, $this->_list->start());
		$query = "SELECT user_id, $concat AS name, company, email, phone, pub, role, valid
		FROM ".$this->_tbl_user." $where ORDER BY $order $limit";
		$a = $this->_db->selectquery($query);
		if(sizeof($a) > 0)
		{
			$href_search = $this->_home."?evt[".$this->_className."-manageUser]&amp;";
			
			$this->_gform = new Form('ginoform', 'post', false);

			$GINO .= $this->scriptAsset("user.js", "userJS", 'js');
			$GINO .= "<table class=\"userTableList\" summary=\""._("elenco utenti con accesso al sistema")."\">\n";
			$GINO .= "<tr>\n";
			$GINO .= "<th".($search==1 || empty($search)?" class=\"thOrder\"":"").">";
			$GINO .= "<span class=\"tooltip\" title=\""._("ordina per nome utente")."\"><a href=\"".$href_search."s=1\">"._('Utente')."</a></span>";
			$GINO .= "</th>\n";
			$GINO .= "<th".($search==2?" class=\"thOrder\"":"").">";
			$GINO .= "<span class=\"tooltip\" title=\""._("ordina per azienda")."\"><a href=\"".$href_search."s=2\">"._('Azienda')."</a></span>";
			$GINO .= "</th>\n";
			$GINO .= "<th>"._('Email')."</th>\n";
			
			if($this->_access->AccessVerifyGroupIf($this->_className, $this->_instance, '', ''))
			$GINO .= "<th scope=\"col\">"._('Ruolo')."</th>\n";
			
			if($this->_user_other)
			{
				if(!empty($this->_other_field1)) $GINO .= "<th scope=\"col\">".$this->_label_field1."</th>\n";
				if(!empty($this->_other_field2)) $GINO .= "<th scope=\"col\">".$this->_label_field2."</th>\n";
				if(!empty($this->_other_field3)) $GINO .= "<th scope=\"col\">".$this->_label_field3."</th>\n";
			}
			else
			{
				$GINO .= "<th scope=\"col\">"._('Telefono')."</th>\n";
			}

			if($this->_user_view) $GINO .= "<th scope=\"col\" class=\"tooltip\" title=\"visualizza scheda utente\">"._("Scheda")."</th>\n";

			$GINO .= "<th scope=\"col\">"._('Attivo')."</th>\n";
			$GINO .= "<th class=\"noBorder\" scope=\"col\"></th>\n";
			$GINO .= "</tr>\n";
			$odd = true;
			foreach($a AS $b)
			{
				$user = $b['user_id'];
				$name = htmlChars($b['name']);
				$company = htmlChars($b['company']);
				$email = htmlChars($b['email']);
				$role = htmlChars($b['role']);
				$phone = htmlChars($b['phone']);
				$public = htmlChars($b['pub']);
				$valid = htmlInput($b['valid']);

				$tr_class = ($odd)? "trOdd":"trEven";
				
				$GINO .= "<tr class=\"$tr_class\">\n";
				
				$GINO .= $this->_gform->hidden('user', $user);

				$GINO .= "<td scope=\"row\"><a href=\"".$this->_home."?evt[".$this->_className."-manageUser]&amp;user=$user&amp;action=$this->_act_modify&start=".$this->_list->start()."\">$name</a></td>\n";
				$GINO .= "<td>$company</td>\n";
				$GINO .= "<td>$email</td>\n";
				
				if($this->_access->AccessVerifyGroupIf($this->_className, $this->_instance, '', ''))
				$GINO .= "<td>".$this->_db->getFieldFromId($this->_tbl_user_role, 'name', 'role_id', $role)."</td>\n";
								
				if($this->_user_other)
				{
					
					$query2 = "SELECT * FROM ".$this->_tbl_user_add." WHERE user_id='$user'";
					$c = $this->_db->selectquery($query2);
					if(sizeof($c) > 0)
					{
						foreach ($c AS $d)
						{
							$field1 = htmlChars($d['field1']);
							$field2 = htmlChars($d['field2']);
							$field3 = htmlChars($d['field3']);

							if(!empty($this->_other_field1))
							{
								if(!empty($field1))
								{
									/*
									$array = array('yes'=>_("si"), 'no'=>_("no"));
									$GINO .= "<td class=\"special\">";
									$GINO .= $this->_gform->label('field1', '', '', '').$this->_gform->clabel();
									$GINO .= $this->_gform->bradio('field1', $field1, 'array', $array, $this->_other_field1, 'h', '');
									$GINO .= "</td>\n";
									*/
									$GINO .= $this->inputValue($this->_gform, "field1", 'yes', $field1, '', '', array("id"=>"field1_$user"));
								}
								else
								{
									$GINO .= "<td class=\"\"></td>\n";
								}
							}

							if(!empty($this->_other_field2))
							{
								if(!empty($field2))
								{
									$GINO .= $this->inputValue($this->_gform, "field2", 'yes', $field2, '', '', array("id"=>"field2_$user"));
								}
								else
								{
									$GINO .= "<td class=\"\"></td>\n";
								}
							}

							if(!empty($this->_other_field3))
							{
								if(!empty($field3))
								{
									$GINO .= $this->inputValue($this->_gform, "field3", 'yes', $field3, '', '', array("id"=>"field3_$user"));
								}
								else
								{
									$GINO .= "<td class=\"\"></td>\n";
								}
							}
						}
					}
					else
					{
						if(!empty($this->_other_field1))
						{
							$GINO .= $this->inputValue($this->_gform, "field1", 'yes', '', '', '', array("id"=>"field1_$user"));
						}
						if(!empty($this->_other_field2))
						{
							$GINO .= $this->inputValue($this->_gform, "field2", 'yes', '', '', '', array("id"=>"field2_$user"));
						}
						if(!empty($this->_other_field3))
						{
							$GINO .= $this->inputValue($this->_gform, "field3", 'yes', '', '', '', array("id"=>"field3_$user"));
						}
					}
				}
				else
				{
					$GINO .= "<td>$phone</td>\n";
				}

				if($this->_user_view)
				{
					/*
					$GINO .= "<td>";
					$GINO .= $this->_gform->label('public', '', '', '').$this->_gform->clabel();
					$GINO .= $this->_gform->bradio('public', $public, 'array', $array, 'no', 'h', '');
					$GINO .= "</td>";
					*/
					$GINO .= $this->inputValue($this->_gform, "public", 'yes', $public, '', '', array("id"=>"public_$user"));
				}

				//if($this->_session_role < $role) $other = ''; else $other = 'disabled';
				if($this->_access->AccessVerifyRoleIDIf($role)) $other = array("id"=>"valid_$user"); else $other = array("disabled"=>true, "id"=>"valid_$user");
				
				$GINO .= $this->inputValue($this->_gform, "valid", 'yes', $valid, '', '', $other);
				
				$GINO .= "<td class=\"tdIcon\">";
				$onclick = "onclick=\"changeValid('$this->_home?pt[$this->_className-changeValid]', '$user')\"";
				$GINO .= $this->_gform->input('submit_valid', 'submit', _("modifica"), array("classField"=>"submit", "js"=>$onclick));
				$GINO .= " <span id=\"changeValidResult$user\"></span>";
				$GINO .= "</td>";

				$GINO .= "</tr>\n";
				
				$odd = !$odd;
			}
			$GINO .= "</table>\n";

			$GINO .= $this->_list->listReferenceGINO("evt[".$this->_className."-manageUser]&char=$char");
		}
		else
		{
			$GINO .= "<p>"._("non risultano utenti")."</p>\n";
		}
		
		$htmlsection->content = $GINO;
		return $htmlsection->render();
	}
	
	private function inputValue($object, $name, $value, $dbvalue, $label, $required, $other){
		
		$GINO = "<td class=\"\">";
				
		$checked = $value==$dbvalue ? true:false;
		$GINO .= $object->checkbox($name, $checked, $value, $other);
		
		$GINO .= "</td>\n";
		
		return $GINO;
	}
	
	private function formUser($user, $action){

		$GINO = '';

		$start = cleanVar($_GET, 'start', 'int', '');

		$gform = new Form('gform', 'post', true);
		$gform->load('dataform');

		if(!empty($user) AND $action == $this->_act_modify)
		{
			$query = "SELECT firstname, lastname, company, phone, fax, email, username, address, cap, city, nation, text, photo, pub, role, date, valid FROM ".$this->_tbl_user." WHERE user_id='$user'";
			$a = $this->_db->selectquery($query);
			if(sizeof($a) > 0)
			{
				foreach($a AS $b)
					foreach($b as $k=>$v) $$k = htmlInput($v);

				$submit = _("modifica");
			}
		}
		else
		{
			$user = '';
			$firstname = $gform->retvar('firstname', '');
			$lastname = $gform->retvar('lastname', '');
			$company = $gform->retvar('company', '');
			$phone = $gform->retvar('phone', '');
			$fax = $gform->retvar('fax', '');
			$email = $gform->retvar('email', '');
			$username = $gform->retvar('username', '');
			$address = $gform->retvar('address', '');
			$cap = $gform->retvar('cap', '');
			$city = $gform->retvar('city', '');
			$nation = $gform->retvar('nation', '');
			$text = $gform->retvar('text', '');
			$photo = '';
			$role = $gform->retvar('role', $this->_access->default_role);

			$submit = _("inserisci");
		}

		if(empty($cap)) $cap = '';
		
		// Required
		if($this->_access->AccessVerifyGroupIf($this->_className, $this->_instance, $this->_user_group, $this->_group_1) AND
		$this->_session_role < $role)
		{
			$req_role = ',role';
		}
		else $req_role = '';

		if($action == $this->_act_insert)
		{
			if(!$this->_pwd_automatic) $req_pwd = ',pwd,pwd2'; else $req_pwd = '';

			if($this->_username_email) $required = "firstname,lastname,email,email2".$req_pwd.$req_role;
			else $required = "username,firstname,lastname,email".$req_pwd.$req_role;
		}
		else
		{
			if($this->_username_email) $req_email = ''; else $req_email = ',email';

			$required = "firstname,lastname".$req_email.$req_role;
		}
		// End
		
		// Text Character Password
		$pwd_min = $this->_length_pwd_min;
		$pwd_max = $this->_length_pwd_max;
		$pwd_number = $this->_pwd_number;
		
		if($action == $this->_act_insert AND !$this->_pwd_automatic)
		{
			if(!empty($pwd_number))
			{
				$text_number = ", "._("almeno $pwd_number numerici").'.';
			}
			else $text_number = '.';
			
		}
		// End

		$GINO .= $gform->form($this->_home."?evt[".$this->_className."-actionUser]", true, $required);
		$GINO .= $gform->hidden('user', $user);
		$GINO .= $gform->hidden('action', $action);
		$GINO .= $gform->hidden('start', $start);
		$GINO .= $gform->hidden('old_file', $photo);
		if($action == $this->_act_insert)
		{
			if(!$this->_pwd_automatic)
			{
				$text_number = (!empty($pwd_number))? ", "._("almeno $pwd_number numerici").'.':'.';
				$GINO .= $gform->cinput('pwd', 'password', '', array(_("Password"), _("Deve contenere minimo $pwd_min, massimo $pwd_max caratteri").$text_number ), array("required"=>true, "size"=>20, "maxlength"=>$pwd_max));
				$GINO .= $gform->cinput('pwd2', 'password', '', _("Verifica password"), array("required"=>true, "size"=>20, "maxlength"=>$pwd_max));
			}
			else
				$GINO .= $gform->noinput(_("Password"), _("Generata in automatico"));
			
			if(!$this->_username_email)
			{
				$url = "$this->_home?pt[user-actionCheckUsername]";
				$onclick = "onclick=\"ajaxRequest('post', '$url', 'username='+$('username').getProperty('value'), 'check')\"";
				$check = "<div id=\"check\" style=\"color:#ff0000;\"></div>\n";

				$GINO .= $gform->cinput('username', 'text', $username, _("Username"), array("id"=>"username", "required"=>true, "size"=>40, "maxlength"=>50));
				$GINO .= $gform->cinput('check_username', 'button', _("controlla"), _("Disponibilità username"), array('js'=>$onclick, "text_add"=>$check));
			}
		}

		$GINO .= $gform->cinput('company', 'text', $company, _("Ragione sociale"), array("size"=>40, "maxlength"=>100));
		$GINO .= $gform->cinput('firstname', 'text', $firstname, _("Nome"), array("required"=>true, "size"=>40, "maxlength"=>50));
		$GINO .= $gform->cinput('lastname', 'text', $lastname, _("Cognome"), array("required"=>true, "size"=>40, "maxlength"=>50));

		if($this->_username_email)
		{
			if($action == $this->_act_insert) {
				$GINO .= $gform->cinput('email', 'text', $email, _("Email"), array("required"=>true, "size"=>40, "maxlength"=>100));
				$GINO .= $gform->cinput('email2', 'text', '', _("Controllo email"), array("required"=>true, "size"=>40, "maxlength"=>100, "other"=>"autocomplete=\"off\""));
			}
			else 
				$GINO .= $gform->cinput('emailview', 'text', $email, _("Email"), array("required"=>true, "size"=>40, "maxlength"=>100, "other"=>"style=\"color:#666666\"", "readonly"=>true));
		}
		else
		{
			$GINO .= $gform->cinput('email', 'text', $email, _("Email"), array("required"=>true, "size"=>40, "maxlength"=>100));
		}
		
		if($this->_access->AccessVerifyGroupIf($this->_className, $this->_instance, $this->_user_group, $this->_group_1))
		{
			if($this->_session_role == $this->_max_role) $where = "";
			else $where = "WHERE role_id > '".$this->_session_role."'";
			$query = "SELECT role_id, name FROM ".$this->_tbl_user_role." $where ORDER BY role_id";
			
			if($this->_session_role >= $role)
			{
				$GINO .= $gform->hidden('role', $role);
				$GINO .= $gform->cinput('roleview', 'text', $this->_db->getFieldFromId($this->_tbl_user_role, 'name', 'role_id', $role), _("Livello di accesso"), array("required"=>true, "size"=>40, "maxlength"=>100, "other"=>"style=\"color:#666666\"", "readonly"=>true));
			}
			else $GINO .= $gform->cselect('role', $role, $query, _("Livello di accesso"), array("required"=>true));
		}

		$GINO .= $gform->cinput('address', 'text', $address, _("Indirizzo"), array("size"=>40, "maxlength"=>200));
		$GINO .= $gform->cinput('cap', 'text', $cap, _("Cap"), array("size"=>5, "maxlength"=>5));
		$GINO .= $gform->cinput('city', 'text', $city, _("Città"), array("size"=>40, "maxlength"=>50));

		$query = "SELECT id, ".$this->_lng_tbl_nation." FROM ".$this->_tbl_nation." ORDER BY ".$this->_lng_tbl_nation." ASC";
		$GINO .= $gform->cselect('nation', $nation, $query, _("Nazione"), array("required"=>false));

		$GINO .= $gform->cinput('phone', 'text', $phone, _("Telefono"), array("size"=>30, "maxlength"=>30));
		$GINO .= $gform->cinput('fax', 'text', $fax, _("Fax"), array("size"=>30, "maxlength"=>30));

		if($this->_info_media)
		{
			$GINO .= $gform->ctextarea('text', $text, _("Informazioni generali"), array("cols"=>50, "rows"=>7));
			$GINO .= $gform->cfile('photo', $photo, _("Fotografia"), array("extensions"=>$this->_extension_media, "del_check"=>true));
		}

		$GINO .= $gform->cinput('submit_action', 'submit', $submit, '', array("classField"=>"submit"));

		$GINO .= $gform->cform();

		return $GINO;
	}

	public function modifyUser(){

		$this->accessGroup('ALL');

		$GINO = '';

		$user = cleanVar($_GET, 'user', 'int', '');
		$action = cleanVar($_GET, 'action', 'string', '');

		if(empty($user))
			EvtHandler::HttpCall($this->_home, $this->_className.'-manageUser', "error=09");
		else
			$user_role = $this->_db->getFieldFromId($this->_tbl_user, 'role', 'user_id', $user);

		if(empty($action)) $action = $this->_act_modify;

		if(
		$action == $this->_act_modify AND
		$this->_access->AccessVerifyRoleIDIf($user_role) AND	// $user_role >= _session_role
		$this->_access->AccessVerifyGroupIf($this->_className, $this->_instance, '', ''))
		{
			$link_del = "<a href=\"".$this->_home."?evt[".$this->_className."-manageUser]&amp;user=$user&amp;action=".$this->_act_delete."\">".$this->icon('delete', '')."</a>";
		}
		else $link_del = '';

		$link_return = $this->_link_return;
		// End

		// Title
		$query = "SELECT username, date FROM ".$this->_tbl_user." WHERE user_id='$user'";
		$a = $this->_db->selectquery($query);
		if(sizeof($a) > 0)
		{
			foreach($a AS $b)
			{
				$username = htmlChars($b['username']);
				$date = htmlChars($b['date']);
			}
		}

		if($action == $this->_act_modify) $title = _("Modifica");
		elseif($action == $this->_act_delete) $title = _("Elimina");
		// End

		$title_section = "$title '$username' "._("registrato il")." '".dbDatetimeToDate($date, '/')."' "._("alle ")."'".dbDatetimeToTime($date)."'";
		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'h1', 'headerLabel'=>$title_section, 'headerLinks'=>array($link_del, $link_return)));

		if($action == $this->_act_modify)
		{
			if($this->_session_role < $user_role OR $user == $this->_session_user)
				$GINO = $this->formPwd($user, 'h', 'manageUser', $action);

			$GINO .= $this->formUser($user, $action);
		}
		elseif ($action == $this->_act_delete AND $this->_access->AccessVerifyGroupIf($this->_className, $this->_instance, '', ''))
		{
			$GINO = $this->formDeleteUser($user, $action);
		}

		$htmlsection->content = $GINO;

		return $htmlsection->render();
	}

	public function actionUser(){

		$this->accessGroup('ALL');

		$gform = new Form('gform', 'post', true);
		$gform->save('dataform');
		$req_error = $gform->arequired();

		$user = cleanVar($_POST, 'user', 'int', '');
		$action = cleanVar($_POST, 'action', 'string', '');

		$username = cleanVar($_POST, 'username', 'string', '');
		$pwd = cleanVar($_POST, 'pwd', 'string', '');
		$pwd2 = cleanVar($_POST, 'pwd2', 'string', '');
		$company = cleanVar($_POST, 'company', 'string', '');
		$firstname = cleanVar($_POST, 'firstname', 'string', '');
		$lastname = cleanVar($_POST, 'lastname', 'string', '');
		$email = cleanVar($_POST, 'email', 'string', '');
		$email2 = cleanVar($_POST, 'email2', 'string', '');
		$role = cleanVar($_POST, 'role', 'int', '');

		$address = cleanVar($_POST, 'address', 'string', '');
		$cap = cleanVar($_POST, 'cap', 'int', '');
		$city = cleanVar($_POST, 'city', 'string', '');
		$nation = cleanVar($_POST, 'nation', 'int', '');
		$phone = cleanVar($_POST, 'phone', 'string', '');
		$fax = cleanVar($_POST, 'fax', 'string', '');
		$text = cleanVar($_POST, 'text', 'string', '');

		$file_name = $_FILES['photo']['name'];
		$file_size = $_FILES['photo']['size'];
		$file_tmp = $_FILES['photo']['tmp_name'];

		$old_file = cleanVar($_POST, 'old_file', 'string', '');
		$check_del_file = cleanVar($_POST, 'check_del_file', 'string', '');

		$date = date("Y-m-d H:i:s");
		$directory = $this->_data_dir.$this->_os;

		if($action == $this->_act_insert)
		{
			$link = '';
			$link_error = $this->_home."?evt[".$this->_className."-manageUser]";
			$redirect = $this->_className.'-manageUser';
		}
		elseif($action == $this->_act_modify)
		{
			$link = "user=$user&action=$action";
			$link_error = $this->_home."?evt[".$this->_className."-manageUser]&user=$user&action=$action";
			$redirect = $this->_className.'-manageUser';
			$redirect_error = $this->_className.'-manageUser';
		}
		else
		{
			$link = '';
			$link_error = $this->_home."?evt[".$this->_className."-manageUser]";
			$redirect = $this->_className.'-manageUser';
			$redirect_error = $this->_className.'-manageUser';
		}

		// Controllo degli inserimenti

		if($req_error > 0) 
			exit(error::errorMessage(array('error'=>1), $link_error));

		if($this->_session_role != $this->_max_role AND $role < $this->_session_role)
			exit(error::errorMessage(array('error'=>9), $link_error));

		// Email
		if(!empty($email) AND !email_control($email))
			exit(error::errorMessage(array('error'=>7), $link_error));

		if($action == $this->_act_insert)
		{
			if($this->_username_email AND $email != $email2)
				exit(error::errorMessage(array('error'=>25), $link_error));

			$query = "SELECT email FROM ".$this->_tbl_user."";
			if($this->valueExist($query, 'email', $email))
				exit(error::errorMessage(array('error'=>20), $link_error));
		}

		if($action == $this->_act_insert)
		{
			// Password
			if(!$this->_pwd_automatic)
			{
				if(!$this->_account->verifyPassword($pwd))
					exit(error::errorMessage(array('error'=>19), $link_error));

				if($pwd != $pwd2)
					exit(error::errorMessage(array('error'=>6), $link_error));
			}
			else
			{
				$pwd = $this->_account->generatePwd();
			}

			$password = $this->cryptMethod($pwd, $this->_crypt);
			// End Password

			if($this->_username_email)
			{
				$username = $email;
			}
			else
			{
				$query = "SELECT username FROM ".$this->_tbl_user."";
				if($this->valueExist($query, 'username', $username))
					exit(error::errorMessage(array('error'=>8), $link_error));
			}

			if(empty($role)) $role = $this->_min_role;

			$validation = ($this->_aut_validation)? "yes":"no";
			$publication= ($this->_aut_publication)? "yes":"no";

			$query = "INSERT INTO ".$this->_tbl_user." (
			firstname, lastname, company, phone, fax, email, username, userpwd,
			address, cap, city, nation, text, pub, role, date, valid, privacy
			) VALUES (
			'$firstname', '$lastname', '$company', '$phone', '$fax', '$email', '$username', '$password',
			'$address', $cap, '$city', $nation, '$text',
			'".$publication."', '$role', '$date', '".$validation."', 'yes'
			)";
			$result = $this->_db->actionquery($query);

			if($result)
			{
				$user_id = $this->_db->getlastid($this->_tbl_user);
				$session = session_id();
				$link_add = "u=$username&p=$pwd&sid=$session";

				if($this->_user_other)
				{
					$query2 = "INSERT INTO ".$this->_tbl_user_add." VALUES ($user_id, '".$this->_other_field1."', '".$this->_other_field2."', '".$this->_other_field3."')";
					$result2 = $this->_db->actionquery($query2);
				}
			}
		}
		elseif($action == $this->_act_modify AND !empty($user))
		{
			if(!empty($email))  $email_field = "email='$email',"; else $email_field = '';
			if(empty($role)) $role_field = ''; else $role_field = ", role='$role'";

			$query = "UPDATE ".$this->_tbl_user." SET
			firstname='$firstname', lastname='$lastname', company='$company',
			phone='$phone', fax='$fax', ".$email_field."
			address='$address', cap=$cap, city='$city', nation=$nation,
			text='$text'".$role_field."
			WHERE user_id='$user'";
			$result = $this->_db->actionquery($query);
		}
		
		$userid = ($action==$this->_act_insert)? $this->_db->getlastid($this->_tbl_user):$user;
		$gform->manageFile('photo', $old_file, true, $this->_extension_media, $directory, $link_error, $this->_tbl_user, 'photo', 'user_id', $userid,
			array("prefix_file"=>self::$prefix_img, "prefix_thumb"=>self::$prefix_thumb, "width"=>self::$width_img, "thumb_width"=>self::$width_thumb));
		
		if($action == $this->_act_insert) {
			if(!empty($link)) $link .= '&'.$link_add; else $link = $link_add;
			EvtHandler::HttpCall($this->_home, $redirect, $link);
		}
		
		EvtHandler::HttpCall($this->_home, "$this->_className-manageUser", "start=$start");

	}

	private function formDeleteUser($user, $action){

		$gform = new Form('gform', 'post', false);
		
		$GINO = $gform->form($this->_home."?evt[".$this->_className."-actionDeleteUser]", '', '');
		$GINO .= $gform->hidden('id', $user);
		$GINO .= $gform->hidden('action', $action);

		$GINO .= $gform->cinput('delete_action', 'submit', _("elimina"), _("ATTENZIONE: l'eliminazione è definitiva."), array("classField"=>"submit"));

		$GINO .= $gform->cform();

		return $GINO;
	}

	public function actionDeleteUser(){

		$this->accessGroup('');

		$id = cleanVar($_POST, 'id', 'int', '');
		$action = cleanVar($_POST, 'action', 'string', '');

		$user_role = $this->_db->getFieldFromId($this->_tbl_user, 'role', 'user_id', $id);
		$this->_access->AccessVerifyRoleID($user_role);

		$link = '';
		$link_error = "user=$id&action=$action";

		$redirect_ok = $this->_className.'-manageUser';
		$redirect_ko = $this->_className.'-modifyUser';

		if(!empty($id) AND $action == $this->_act_delete)
		{
			$query = "SELECT photo FROM ".$this->_tbl_user." WHERE user_id='$id'";
			$a = $this->_db->selectquery($query);
			if(sizeof($a) > 0)
			{
				foreach($a AS $b)
				{
					$img = htmlInput($b['photo']);
					$path_to_file = $this->_data_dir.$this->_os.self::$prefix_img.$img;
					$this->deleteFile($path_to_file, $this->_home, $redirect_ko, $link_error);
					$path_to_thumb = $this->_data_dir.$this->_os.self::$prefix_thumb.$img;
					$this->deleteFile($path_to_thumb, $this->_home, $redirect_ko, $link_error);
				}
			}

			$query_delete = "DELETE FROM ".$this->_tbl_user." WHERE user_id='$id'";
			$result = $this->_db->actionquery($query_delete);
			if($result)
			{
				if($this->_user_other)
				{
					$query_delete = "DELETE FROM ".$this->_tbl_user_add." WHERE user_id='$id'";
					$this->_db->actionquery($query_delete);
				}

				EvtHandler::HttpCall($this->_home, $redirect_ok, $link);
			}
		}

		exit(error::errorMessage(array('error'=>9), $this->_home."?evt[$redirect_ko]&$link_error"));
	}

	/**
	 * Change User Password
	 *
	 * @param integer $user		user id
	 * @param string $format	h: horizontal | v: vertical
	 * @param string $function	function name (for return redirect)
	 * @param string $action
	 * @return string
	 */
	private function formPwd($user, $format, $function, $action){

		$GINO = "<div class=\"section\">\n";
		
		// Text Character Password
		$pwd_min = $this->_length_pwd_min;
		$pwd_max = $this->_length_pwd_max;
		$pwd_number = $this->_pwd_number;
		$text_number = (!empty($pwd_number))? ", "._("almeno $pwd_number numerici").'.':'.';
				
		// End
		
		$gform = new Form('pwdform', 'post', true);
		$gform->load('pwdform');

		$required = 'pwd,pwd2';
		$GINO .= $gform->form($this->_home."?evt[".$this->_className."-changePwd]", '', $required);
		$GINO .= $gform->hidden('user', $user);
		$GINO .= $gform->hidden('action', $action);
		$GINO .= $gform->hidden('function', $function);

		$GINO .= $gform->cinput('pwd', 'password', '', array(_("Password"), _("Deve contenere minimo $pwd_min, massimo $pwd_max caratteri").$text_number), array("required"=>true, "size"=>40, "maxlength"=>$pwd_max));
		$GINO .= $gform->cinput('pwd2', 'password', '', _("Verifica password"), array("required"=>true, "size"=>40, "maxlength"=>$pwd_max, "other"=>"autocomplete=\"off\""));

		$GINO .= $gform->cinput('submit_action', 'submit', _("modifica"), '', array("classField"=>"submit"));

		$GINO .= $gform->cform();
		$GINO .= "</div>\n";
		$GINO .= "<p class=\"line\"></p>";

		return $GINO;
	}

	public function changePwd(){

		$this->accessType($this->_access_3);

		$user = cleanVar($_POST, 'user', 'int', '');
		$action = cleanVar($_POST, 'action', 'string', '');
		$function = cleanVar($_POST, 'function', 'string', '');
		$pwd = cleanVar($_POST, 'pwd', 'string', '');
		$pwd2 = cleanVar($_POST, 'pwd2', 'string', '');

		$gform = new Form('pwdform', 'post', true);
		$gform->save('pwdform');
		$req_error = $gform->arequired();

		$link_b = $this->_home."?evt[".$this->_className."-$function]";
		if($action == $this->_act_modify AND $function != 'personal')
		{
			$link = "user=$user&action=$action";
			$link_error = $link_b."&$link";
		}
		else
		{
			$link = '';
			$link_error = $link_b;
		}

		$redirect = $this->_className.'-'.$function;

		if($req_error > 0) 
			exit(error::errorMessage(array('error'=>1), $link_error));

		if(empty($user))
			exit(error::errorMessage(array('error'=>9), $link_error));

		$user_role = $this->_db->getFieldFromId($this->_tbl_user, 'role', 'user_id', $user);
		if($this->_session_role >= $user_role AND $user != $this->_session_user)
			exit(error::errorMessage(array('error'=>31), $link_error));

		if(!$this->_account->verifyPassword($pwd))
			exit(error::errorMessage(array('error'=>19), $link_error));

		if($pwd != $pwd2)
			exit(error::errorMessage(array('error'=>6), $link_error));

		$password = $this->cryptMethod($pwd, $this->_crypt);

		$query = "UPDATE ".$this->_tbl_user." SET userpwd='$password' WHERE user_id='$user'";
		$this->_db->actionquery($query);

		EvtHandler::HttpCall($this->_home, $redirect, $link);
	}

	private function formValid($user){

		$GINO = '';

		$query = "SELECT valid FROM ".$this->_tbl_user." WHERE user_id='$user'";
		$a = $this->_db->selectquery($query);
		if(sizeof($a) > 0)
		{
			foreach($a AS $b)
			{
				$valid = $b['valid'];
				if($b['valid'] == 'yes') { $checked1 = 'checked'; $checked2 = ''; }
				else { $checked1 = ''; $checked2 = 'checked'; }

				$GINO .= "<form action=\"".$this->_home."?evt[".$this->_className."-changeValid]\" method=\"post\">\n";
				$GINO .= "<div class=\"form\">\n";

				$GINO .= "<input type=\"hidden\" id=\"user\" name=\"user\" value=\"$user\" />\n";
				$GINO .= "<input type=\"radio\" id=\"valid\" name=\"valid\" value=\"yes\" $checked1 />yes
				<input type=\"radio\" id=\"valid\" name=\"valid\" value=\"no\" $checked2 />no\n";
				$GINO .= "<label><input type=\"submit\" id=\"submit_valid\" name=\"submit_valid\" value=\""._("modifica")."\" /></label>\n";

				$GINO .= "</div>\n";
				$GINO .= "</form>\n";
			}
		}

		return $GINO;
	}

	public function changeValid(){

		$this->accessGroup('ALL');

		$this->_gform = new Form('gform', 'post', false);

		$user = cleanVar($_POST, 'user', 'int', '');
		$public = cleanVar($_POST, 'public', 'string', '');
		$valid = cleanVar($_POST, 'valid', 'string', '');
		
		$user_role = $this->_db->getFieldFromId($this->_tbl_user, 'role', 'user_id', $user);
		if($this->_access->AccessVerifyRoleIDIf($user_role))
		{
			$valid_value = $valid ? $valid : "no";
			
			if(sizeof($valid) > 0) $set_valid = ", valid='$valid_value'";
			else 
				return "request error:"._("Campi obbligatori mancanti");
		}
		else $set_valid = '';

		if($this->_user_view AND sizeof($public) > 0)
		{
			$set_public = $public ? $public:'no';
		}
		else $set_public = ($this->_aut_publication)?"yes":"no";

		if($this->_user_other) $this->changeOther();

		$query = "UPDATE ".$this->_tbl_user." SET pub='$set_public' $set_valid
		WHERE user_id='$user'";
		$this->_db->actionquery($query);

		return _("OK");
	}

	public function changeOther(){

		$this->accessGroup('ALL');

		$user = cleanVar($_POST, 'user', 'int', '');
		$field1 = cleanVar($_POST, 'field1', 'string', '');
		$field2 = cleanVar($_POST, 'field2', 'string', '');
		$field3 = cleanVar($_POST, 'field3', 'string', '');

		$link = "user=$user";
		$link_error = "user=$user&";
		$redirect = $this->_className.'-manageUser';

		$value1 = $field1 ? $field1 : "no";
		$value2 = $field2 ? $field2 : "no";
		$value3 = $field3 ? $field3 : "no";
		
		$query = "SELECT user_id FROM ".$this->_tbl_user_add." WHERE user_id='$user'";
		$a = $this->_db->selectquery($query);
		if(sizeof($a) > 0)
		{
			$query = "UPDATE ".$this->_tbl_user_add." SET field1='$value1', field2='$value2', field3='$value3'
			WHERE user_id='$user'";
			$result = $this->_db->actionquery($query);
		}
		else
		{
			$query = "INSERT INTO ".$this->_tbl_user_add." VALUES ('$user', '$value1', '$value2', '$value3')";
			$result = $this->_db->actionquery($query);
		}

		if(!$result)
			return "request error:"._("Impossibile eseguire l'operazione richiesta, contattare l'amministratore del sistema");
		else return true;

	}

	private function textNewUser(){

		$username = cleanVar($_GET, 'u', 'string', '');
		$password = cleanVar($_GET, 'p', 'string', '');
		$session = cleanVar($_GET, 'sid', 'string', '');

		$GINO = '';

		if(!empty($username) AND !empty($password) AND !empty($session))
		{
			$session_now = session_id();
			if($session_now == $session)
			{
				$GINO .= "<div>";
				$GINO .= "<p><span class=\"subtitle\">"._("Il nuovo utente è stato creato con i seguenti parametri").":</span></p>";
				$GINO .= "<p>"._("Username").": $username</p>";
				$GINO .= "<p>"._("Password").": $password</p>";
				$GINO .= "</div>\n";
			}
		}

		return $GINO;
	}

	public function newUser(){

		$this->accessGroup('ALL');

		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'h1', 'headerLabel'=>_("Nuovo utente"), 'headerLinks'=>$this->_link_return));
		$htmlsection->content = $this->formUser('', $this->_act_insert);

		return $htmlsection->render();
	}

	/*
		EMAIL personalizzate
	*/
	
	public static function subjectEmail_1($instance){

		$title = pub::variable('head_title');
		return _("Nuova registrazione").' - '.$title;
	}

	public static function textEmail_1($instance, $var=''){

		if(!empty($var))
		{
			$array_data = explode(';;', $var);
			$name = $array_data[0];
			$surname = $array_data[1];
			$user = $array_data[2];
			$password = $array_data[3];
			$link = $array_data[4];
		}
		elseif (empty($name) AND empty($surname) AND empty($user) AND empty($password) AND empty($link))
		{
			$name = '[name]';
			$surname = '[surname]';
			$user = '[username]';
			$password = '[password]';
			$link = '[link]';
		}
		
		$text = _("Gentile").' '.$name." ".$surname.", "._("ti ringraziamo per esserti registrato.")."\n"._("Ti ricordiamo i dati per l'accesso:")."\n"._("username: ").$user."\npassword: ".$password."\n"._("Per completare la registrazione non ti rimane che cliccare il link che segue.")."\n\nhttp://".$_SERVER['HTTP_HOST'].SITE_WWW.'/'.$link;

		return $text;
	}
	
	public static function subjectEmail_2($instance){

		$title = pub::variable('head_title');
		return _("Nuova registrazione").' - '.$title;
	}

	public static function textEmail_2($instance, $var=''){

		if(!empty($var))
		{
			$array_data = explode(';;', $var);
			$name = $array_data[0];
			$surname = $array_data[1];
			$user = $array_data[2];
			$password = $array_data[3];
			$link = $array_data[4];
		}
		elseif (empty($name) AND empty($surname) AND empty($user) AND empty($password) AND empty($link))
		{
			$name = '[name]';
			$surname = '[surname]';
			$user = '[username]';
			$password = '[password]';
			$link = '[link]';
		}
		
		$text = _("Gentile").' '.$name." ".$surname.", "._("ti ringraziamo per esserti registrato.")."\n"._("Ti ricordiamo i dati per l'accesso:")."\n"._("username: ").$user."\npassword: ".$password."\n";

		return $text;
	}

	/*
		REGISTRAZIONE AUTOMATICA NUOVI UTENTI
	*/

	public function registration() {

		$this->accessType($this->_access_2);

		return $this->_account->formAccount();

	}

	public function actionRegistration() {

		$this->accessType($this->_access_2);

		$this->_account->actionAccount('registrationMsg', '', $this->_home."?evt[$this->_className-registration]");
	}

	public function registrationMsg() {

		$this->accessType($this->_access_2);

		$htmlsection = new htmlSection(array('id'=>"news_".$this->_instanceName,'class'=>'public', 'headerTag'=>'header', 'headerLabel'=>_("Registrazione")));

		$GINO = "<p>";
		$GINO .= _("La procedura di registrazione è stata completata.");
		
		if(!$this->_aut_validation)
		{
			$GINO .= "<br />"._("All'indirizzo di posta elettronica che avete indicato è stata inviata una email con la quale potrete provvedere alla conferma della registrazione.");
		}
		else 
		{
			$GINO .= "<br />"._("All'indirizzo di posta elettronica che avete indicato è stata inviata una email di conferma della registrazione. L'account è attivo da questo momento.");
		}
		
		$GINO .= "</p>";

		$GINO .= "<p>"._("Torna alla ")."<b><a href=\"$this->_home\">"._("home")."</a></b>.</p>";
		
		$htmlsection->content = $GINO;

		return $htmlsection->render();
	}
	
	public function confirmRegistration(){
		
		$this->accessType($this->_access_2);

		return $this->confirmRegistrationData();

	}
	
	private function confirmRegistrationData() {

		$htmlsection = new htmlSection(array('class'=>'public', 'headerTag'=>'header', 'headerLabel'=>_("Conferma registrazione")));

		$result = $this->_account->confirmRegistration();
		
		if($result)
			$GINO = "<p>"._("La registrazione è avvenuta con successo.")."</p>\n";
		else
			$GINO = "<p>"._("La conferma della registrazione ha avuto esito negativo, contattare l'amministratore del sistema.").' '.$this->_email_send."</p>";

		$htmlsection->content = $GINO;

		return $htmlsection->render();
	}

	/*
		Modifica dai personali
	*/
	public function personal() {

		$this->accessType($this->_access_3);

		if(!empty($this->_session_user))
			return $this->_account->formAccount($this->_act_modify);
		
	}

	public function actionPersonal(){
		
		$this->accessType($this->_access_3);
		
		$this->_account->actionAccount('', '');
	}
	// End

	public function actionCheckUsername() {

		$username = cleanVar($_POST, 'username', 'string', '');

		if(empty($username)) {echo "<span style=\"font-weight:bold\">"._("Inserire uno username!")."</span>"; exit();}

		$query = "SELECT user_id FROM ".$this->_tbl_user." WHERE username='$username'";
		$a = $this->_db->selectquery($query);
		if(sizeof($a) > 0)
		{
			echo "<span style=\"font-weight:bold\">"._("Username non disponibile!")."</span>";
			exit();
		}
		else
		{
			echo "<span style=\"font-weight:bold\">"._("Username disponibile!")."</span>";
			exit();
		}
	}

	/*
		Export File
	*/
	private function exportFile(){
		
		return "<a href=\"".$this->_home."?evt[".$this->_className."-export]\">".$this->icon('export', '')."</a>";
	}
	
	public function export(){
		
		$this->accessGroup('ALL');

		$date = date("Y-m-d_H-i-s");
		$report_file = "users.csv";
		
		$output = "COGNOME,NOME,AZIENDA,TELEFONO,FAX,EMAIL,INDIRIZZO,CAP,CITTA,NAZIONE,ATTIVO";
		
		if(!empty($this->_label_field1) OR !empty($this->_label_field2) OR !empty($this->_label_field3))
		{
			if(!empty($this->_label_field1)) $name1 = strtoupper($this->_label_field1); else $name1 = 'FIELD1';
			if(!empty($this->_label_field2)) $name2 = strtoupper($this->_label_field2); else $name2 = 'FIELD2';
			if(!empty($this->_label_field3)) $name3 = strtoupper($this->_label_field3); else $name3 = 'FIELD3';
			
			$output .= ",$name1,$name2,$name3\r\n";
			
			$query = "SELECT DISTINCT(u.user_id), u.firstname, u.lastname, u.company, u.phone, u.fax, u.email, u.address, u.cap, u.city, u.nation, u.valid, a.field1, a.field2, a.field3
			FROM ".$this->_tbl_user." AS u, ".$this->_tbl_user_add." AS a
			WHERE u.user_id=a.user_id ORDER BY u.lastname ASC, u.firstname ASC";
		}
		else
		{
			$output .= "\r\n";
			
			$query = "SELECT DISTINCT(user_id), firstname, lastname, company, phone, fax, email, address, cap, city, nation, valid
			FROM ".$this->_tbl_user." ORDER BY lastname ASC, firstname ASC";
		}
		$a = $this->_db->selectquery($query);
		if(sizeof($a) > 0)
		{
			foreach($a AS $b)
			{
        		$firstname = utf8_encode($b['firstname']);
        		$lastname = utf8_encode($b['lastname']);
        		$company = utf8_encode($b['company']);
        		$phone = $b['phone'];
        		$fax = $b['fax'];
        		$email = utf8_encode($b['email']);
        		$address = enclosedField(utf8_encode($b['address']));
        		$cap = $b['cap'];
        		$city = enclosedField(utf8_encode($b['city']));
        		$valid = $b['valid'];
        		$nation = utf8_encode($this->_db->getFieldFromId($this->_tbl_nation, $this->_lng_tbl_nation, 'id', $b['nation']));
        		$nation = enclosedField($nation);
        		
        		if(!empty($this->_label_field1) OR !empty($this->_label_field2) OR !empty($this->_label_field3))
        		{
        			$field1 = $b['field1'];
        			$field2 = $b['field2'];
        			$field3 = $b['field3'];
        			$output .= "$lastname,$firstname,$company,$phone,$fax,$email,$address,$cap,$city,$nation,$valid,$field1,$field2,$field3\r\n";
        		}
        		else
        		{
        			$output .= "$lastname,$firstname,$company,$phone,$fax,$email,$address,$cap,$city,$nation,$valid\r\n";
        		}
			}
		}

		header("Content-type: application/csv \r \n");
		header("Content-Disposition: inline; filename=$report_file");
		echo $output;
		exit();
	}
	
}

?>
