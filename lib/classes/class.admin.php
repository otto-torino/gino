<?php
/**
 * @file class.admin.php
 * @brief Contiene la classe admin
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

/**
 * @brief Libreria per la gestione dei permessi sulle funzionalità del modulo
 * 
 * Gestisce l'associazione degli utenti alle funzionalità del modulo
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class admin {

	private $_title;
	private $_class, $_class_prefix, $instance;
	private $_group_admin;
	
	private $_tbl_group;
	private $_tbl_guser;
	
	private $_action;
	
	function __construct($class, $instance){
		
		parent::__construct();
		
		$this->setData($instance, $class);

		$this->_title = _("Permessi");
		
		$this->_action = cleanVar($_REQUEST, 'action', 'string', '');

		$this->_group_admin = $this->_access->adminGroup($this->_class);
	}
	
	private function setData($instance, $class) {
		
		$this->_instance = $instance;
		$this->_instanceName = $this->_db->getFieldFromId($this->_tbl_module, 'name', 'id', $instance);

		if($this->_instance && empty($this->_instanceName)) exit(error::syserrorMessage("options", "setData", "Istanza di ".$class." non trovata", __LINE__));

		if($class) $this->_class = $class;
		else exit(error::syserrorMessage("admin", "setData", "Classe ".$class." inesistente", __LINE__));

		if(!$this->_instance) $this->_instanceName = $this->_class; 		
		
		$this->_class_prefix = $this->field_class('tbl_name', $this->_class);
		$this->_tbl_group = $this->_class_prefix.'_grp';
		$this->_tbl_guser = $this->_class_prefix.'_usr';

		$this->_return_link = method_exists($class, "manageDoc")? $this->_instanceName."-manageDoc": $this->_instanceName."-manage".ucfirst($class);
	}
	
	/**
	 * Ricava il valore di un campo della tabella sys_module_app
	 * 
	 * @param string $field nome del campo
	 * @param string $class_name nome della classe
	 * @return string
	 */
	private function field_class($field, $class_name){
		
		$records = $this->_db->select("ma.$field", $this->_tbl_module_app." AS ma, ".$this->_tbl_module." AS m", "m.class='$class_name' AND m.type='class' AND m.class=ma.name");
		if(count($records))
		{
			foreach($records AS $r)
			{
				$value = $r[$field];
			}
		}
		else
		{
			$records = $this->_db->select($field, $this->_tbl_module_app, "name='$class_name' AND type='class'");
			if(count($records))
			{
				foreach($records AS $r)
				{
					$value = $r[$field];
				}
			}
		}
		
		return $value;
	}
	
	/**
	 * Interfaccia per la gestione dei gruppi e degli utenti nei gruppi
	 * 
	 * @return string
	 */
	public function manageDoc(){

		if($this->_action == 'save') {$this->actionUser();exit;}

		// Variables
		$id = cleanVar($_GET, 'id', 'int', '');
		$block = cleanVar($_GET, 'block', 'string', '');
		$func = cleanVar($_GET, 'func', 'string', '');
		// end

		if($func=='adduser') {echo $this->ajaxAddNoAdminUser();exit;}
		$select_doc = $id;
		
		if($id == $this->_group_admin)	// Administration Group
		{
			if($this->_access->AccessVerifyRoleIDIf($this->_access_admin))
				$form = $this->formUser($id, $this->_action);
			else
				$form = $this->formUserView($id, $this->_action);
		}
		elseif($id) $form = $this->formUser($id, $this->_action);
		else $form = $this->infoDoc();

		$GINO = "<div class=\"section_admin\">\n";

		$GINO .= "<div class=\"vertical_1\">\n";
		$GINO .= $this->listGroup($select_doc);
		$GINO .= "</div>\n";

		$GINO .= "<div class=\"vertical_2\">\n";
		$GINO .= $form;
		$GINO .= "</div>\n";

		$GINO .= "<div class=\"null\"></div>";

		$GINO .= "</div>\n";

		return $GINO;
	}
	
	private function infoDoc(){
		
		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'h1', 'headerLabel'=>_("Informazioni")));
		$buffer = "<p>"._("Gli amministratori di sistema vengono inseriti automaticamente nel gruppo 'responsabili'.")."</p>";
		$buffer .= "<p>"._("Il gruppo 'responsabili' può svolgere tutte le mansioni previste per gli altri gruppi.")."</p>";
		$level = $this->_db->getFieldFromId($this->_tbl_user_role, 'name', 'role_id', $this->_access_user);
		$buffer .= "<p>"._("Gli utenti di tutti i gruppi devono avere almeno un livello di accesso")." '$level'.</p>";

		$htmlsection->content = $buffer;

		return $htmlsection->render();
	}
	
	/**
	 * Elenco dei gruppi
	 * 
	 * @param integer $select_doc valore ID del gruppo selezionato
	 * @return string
	 */
	private function listGroup($select_doc){
		
		$link_base = $this->_home."?evt[".$this->_return_link."]&amp;block=permissions";
		
		if(method_exists($this->_class, 'manageDoc')) $function = 'manageDoc';
		else $function = 'manage'.ucfirst($this->_class);
		
		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'h1', 'headerLabel'=>$this->_title));

		$query = "SELECT * FROM ".$this->_tbl_group." ORDER BY id, name ASC";
		$a = $this->_db->selectquery($query);
		if(sizeof($a) > 0)
		{
			$htmlList = new htmlList(array("numItems"=>sizeof($a), "separator"=>true));
			$GINO = $htmlList->start();
			foreach($a AS $b)
			{
				$id = htmlChars($b['id']);
				$name = htmlChars($b['name']);
				$description = htmlChars($b['description'], '', array('newline'=>true));
				$no_admin = htmlChars($b['no_admin']);
				
				if($no_admin == 'yes') $text = " (senza funzionalità amministrative)"; else $text = '';
				
				$link_user = " <a href=\"$link_base&amp;id=$id\">".$this->icon('group', '')."</a>";
				$selected = $id == $select_doc?true:false;				
				
				$label = $name.$text.($description?"<br/><span style=\"font-weight:normal;font-style:italic;\">".$description."</span>":'');
				$GINO .= $htmlList->item($label, array($link_user), $selected, true);

			}
			$GINO .= $htmlList->end();
		}
				
		$htmlsection->content = $GINO;

		return $htmlsection->render();
	}
	
	/**
	 * Elenco degli utenti amministratori dell'applicazione
	 * 
	 * @return array
	 */
	public function listUserAdmin(){
		
		$users =array();
		$query = "SELECT user_id FROM ".$this->_tbl_user." WHERE role<='$this->_access_admin'";
		$a = $this->_db->selectquery($query);
		if(sizeof($a) > 0)
		{
			foreach($a AS $b)
			{
				$users[] = $b['user_id'];
			}
		}
		return $users;
	}
	
	private function formUser($group, $action){
	
		$gform = new Form('gform', 'post', true);
		$gform->load('dataform');
		
		// Utenti del Gruppo
		$guser = $this->listUserGroup($group);
		$group_name = $this->_db->getFieldFromId($this->_tbl_group, 'name', 'id', $group);

		$title = _("Utenti amministratori");
		$submit = _("modifica");
		$required = '';
		
		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'h1', 'headerLabel'=>$title));
		
		$GINO = '';
		
		$concat = $this->_db->concat(array("firstname", "' '", "lastname"));
		$query = $this->_db->query("user_id, $concat AS name", $this->_tbl_user, "role<='".$this->_access_admin."' AND valid='yes'", array('order'=>"lastname ASC, firstname ASC"));
		$a = $this->_db->selectquery($query);
		if(sizeof($a) > 0)
		{
			foreach ($a AS $b)
			{
				$name = htmlChars($b['name']);
				$GINO .= "<p>$name</p>";
			}
		}
		else $GINO .= _("non risultano utenti registrati.");
		
		$htmlsection->content = $GINO;

		$buffer = $htmlsection->render();

		// Utenti con accesso alle funzionalità della classe
		
		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'h1', 'headerLabel'=>_("Gruppo")." '$group_name' - "._("Utenti validi")));

		$GINO .= $gform->form($this->_home."?evt[$this->_return_link]&block=permissions&action=save", '', $required);
		$GINO .= $gform->hidden('id', $group);

		$guser_q = implode(',', $guser);
		if($guser_q) $where_gusr = " AND user_id IN ($guser_q)";
		else $where_gusr = '';

		$query = $this->_db->query("user_id, $concat AS name", $this->_tbl_user, "role<='".$this->_access_user."' AND role>'".$this->_access_admin."' AND valid='yes' $where_gusr", array('order'=>"lastname ASC, firstname ASC"));
		$a = $this->_db->selectquery($query);
		if(sizeof($a) > 0 && $guser_q)
		{
			$GINO .= $gform->multipleCheckbox("ul[]", $guser, $query, array(_("Utenti presenti"), _("deselezionare gli utenti che si intendono rimuovere")), array("table"=>$this->_tbl_user, "field"=>$concat, "idName"=>"user_id"));
			$GINO .= $gform->cinput('submit_remove', 'submit', $submit, '', array("classField"=>"submit"));
			$GINO .= "<p class=\"line\"></p>\n";
		}
		
		$onkeyup = "onkeyup=\"ajaxRequest('post', '".$this->_home."?pt[$this->_return_link]&block=permissions&func=adduser', 'username='+$(this).getProperty('value')+'&guser_q=$guser_q&id=$group', 'av_users', {'load':'av_users', 'cache':true})\"";
		$GINO .= $gform->cinput('user', 'text', '', _("Cerca utente"), array("size"=>10, "maxlength"=>10, "js"=>$onkeyup));
		
		$GINO .= $gform->cell($this->ajaxAddNoAdminUser($guser_q), array("id"=>"av_users"));

		$GINO .= $gform->cform();
		
		$htmlsection->content = $GINO;

		$buffer .= $htmlsection->render();

		return $buffer;
	}
	
	/**
	 * Elenco degli utenti che possono essere associati ai gruppi a seguito di una ricerca utente
	 * 
	 * Utenti con accesso all'area amministrativa e che non siano amministratori dell'applicazione
	 * 
	 * @param string $guser_q elenco utenti da non mostrare (separati da virgola)
	 * @return string
	 */
	public function ajaxAddNoAdminUser($guser_q='') {

		$GINO = '';

		$gform = new Form('gform', 'post', false);

		if(!$guser_q) $guser_q = cleanVar($_POST, 'guser_q', 'string', '');
		$username = cleanVar($_POST, 'username', 'string', '');

		$where_usr = $username ? " AND (firstname LIKE '%$username%' OR lastname LIKE '%$username%')" : '';

		$guser = explode(',',$guser_q);
		if($guser_q) $where_gusr = " AND user_id NOT IN ($guser_q)"; else $where_gusr = '';
		$concat = $this->_db->concat(array("firstname", "' '", "lastname"));
		
		$query = $this->_db->query("user_id, $concat AS name", $this->_tbl_user, "role<='".$this->_access_user."' AND role>'".$this->_access_admin."' AND valid='yes' $where_usr $where_gusr", array('order'=>"lastname ASC, firstname ASC"));
		$a = $this->_db->selectquery($query);
		if(sizeof($a) > 0)
		{
			$GINO = $gform->startTable();
			$GINO .= $gform->multipleCheckbox("um[]", $guser, $query, _("Aggiungi utenti"), array("table"=>$this->_tbl_user, "field"=>$concat, "idName"=>"user_id"));
			$GINO .= $gform->cinput('submit_add', 'submit', _("aggiungi"), '', array("classField"=>"submit"));
			$GINO .= $gform->endTable();
		}

		return $GINO;
	}

	/**
	 * Associazione degli utenti ai gruppi
	 * 
	 * @see listUserGroup()
	 * @see sqlcode::admin_actionUser()
	 */
	public function actionUser(){

		$gform = new Form('gform', 'post', false);
		$gform->save('dataform');
		$req_error = $gform->arequired();
		
		$id = cleanVar($_POST, 'id', 'int', '');
		$submit_remove = cleanVar($_POST, 'submit_remove', 'string', '');
		$submit_add = cleanVar($_POST, 'submit_add', 'string', '');
		$ul = cleanVar($_POST, 'ul', 'array', '');
		$um = cleanVar($_POST, 'um', 'array', '');

		$link_error = $this->_home."?evt[$this->_return_link]&block=permissions&id=$id";
		$link = "id=$id&block=permissions";
		
		if(!$id)
			exit(error::errorMessage(array('error'=>9), $link_error));
		
		$guser = $this->listUserGroup($id);	// utenti preesistenti
		$control = 0;

		if($submit_remove) {
			
			include_once(LIB_DIR.OS."sqlcode.php");
			$obj = new sqlcode();
			$call = get_class().'_actionUser';
			$query_delete = $obj->$call($this->_tbl_guser, $this->_tbl_user, $id, $this->_access_user, $this->_access_admin, $this->_instance);
			
			//$query_delete = "DELETE FROM gu USING ".$this->_tbl_guser." AS gu INNER JOIN ".$this->_tbl_user." AS u WHERE gu.group_id='$id' AND u.user_id=gu.user_id AND u.role<='".$this->_access_user."' AND u.role>'".$this->_access_admin."' AND gu.instance='$this->_instance'";
			$result_delete = $this->_db->actionquery($query_delete);

			if($result_delete) {
				if(sizeof($ul) > 0) {
					foreach($ul AS $value) {
						if(!empty($value))
						{
							$query_insert = "INSERT INTO ".$this->_tbl_guser." (instance, group_id, user_id) VALUES ('$this->_instance', $id, $value)";
							$result_insert = $this->_db->actionquery($query_insert);
							if(!$result_insert) $control++;
						}
					}
				}
			}
		}
		elseif($submit_add) {
		
			if(sizeof($um) > 0) {
				foreach($um AS $value) {
					if(!empty($value))
					{
						$query_insert = "INSERT INTO ".$this->_tbl_guser." (instance, group_id, user_id) VALUES ('$this->_instance', $id, $value)";
						$result_insert = $this->_db->actionquery($query_insert);
						if(!$result_insert) $control++;
					}
				}
			}
		}

		// Ripristino situazione precedente
		if($control > 0)
		{
			$query_delete = "DELETE FROM ".$this->_tbl_guser." WHERE group_id='$id' AND instance='$this->_instance'";
			$result_delete = $this->_db->actionquery($query_delete);
			if($result_delete)
			{
				foreach($guser AS $value)
				{
					if(!empty($value))
					{
						$query_insert = "INSERT INTO ".$this->_tbl_guser." (instance, group_id, user_id) VALUES ('$this->_instance', $id, $value)";
						$result_insert = $this->_db->actionquery($query_insert);
					}
				}
			}
		}
		EvtHandler::HttpCall($this->_home, $this->_return_link, $link);
	}
	
	/**
	 * Visualizza gli utenti di un gruppo
	 * 
	 * @param integer $group valore ID del gruppo
	 * @param string $action
	 * @return string
	 */
	private function formUserView($group, $action){
	
		$guser = $this->listUserGroup($group);
		
		$title_form = _("utenti del gruppo");
		
		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'h1', 'headerLabel'=>$title_form));

		$GINO = "";
		if(sizeof($guser) > 0)
		{
			foreach ($guser AS $value)
			{
				$query = "SELECT firstname, lastname, valid
				FROM ".$this->_tbl_user."
				WHERE user_id='$value' ORDER BY lastname ASC, firstname ASC";
				$a = $this->_db->selectquery($query);
				if(sizeof($a) > 0)
				{
					foreach($a AS $b)
					{
						$firstname = htmlChars($b['firstname']);
						$lastname = htmlChars($b['lastname']);
						$valid = $b['valid'];
						
						$valid == 'no' ? $text = '['._("utente disabilitato").']' : $text = '';
						
						$GINO .= "<p>$firstname $lastname $text</p>";
					}
				}
			}
		}
		else $GINO .= _("non risultano utenti registrati.");
		
		$htmlsection->content = $GINO;
		
		return $htmlsection->render();
	}

	/**
	 * Elenco utenti di un gruppo
	 * 
	 * @param integer $group valore ID del gruppo
	 * @return array
	 */
	public function listUserGroup($group){
		
		$user = array();
		$query_group = "SELECT user_id FROM ".$this->_tbl_guser." WHERE group_id='$group' AND instance='$this->_instance'";
		$a = $this->_db->selectquery($query_group);
		if(sizeof($a) > 0)
		{
			foreach($a AS $b)
			{
				$user[] = $b['user_id'];
			}
		}
		return $user;
	}
}
?>
