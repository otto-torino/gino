<?php
/**
 * @file class.email.php
 * @brief Contiene la classe email
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

/**
 * @brief Libreria per la gestione di email personalizzate
 * 
 * Vedi la classe user
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 * 
 * Per poter utilizzare questa libreria occorre:
 * 1. includere la classe
 * 2. creare una tabella
 * @code
 * CREATE TABLE IF NOT EXISTS `[RIFERIMENTO-TABELLA]_email` (
 * `id` int(11) NOT NULL AUTO_INCREMENT,
 * `ref_function` smallint(2) NOT NULL,
 * `description` varchar(255) NOT NULL,
 * `subject` varchar(200) NOT NULL,
 * `text` text NOT NULL,
 * PRIMARY KEY (`id`),
 * UNIQUE KEY `ref_function` (`ref_function`)
 * ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
 * @endcode
 * 3. creare i record delle email personalizzate
 * 4. definire per ogni email personalizzata un metodo subjectEmail_[valoreID] e un metodo textEmail_[valoreID]
 */
class email extends pub {

	private $_class, $_class_prefix;
	private $_tbl_email;
	private $_instance;
	private $_title;
	private $_action;
	
	function __construct($class, $instance){
		
		parent::__construct();
		
		$this->_title = _("Personalizzazione email");
		
		$this->setData($instance, $class);
		
		$this->_action = cleanVar($_REQUEST, 'action', 'string', '');
	}
	
	private function setData($instance, $class) {
		
		$this->_instance = $instance;
		$this->_instanceName = $this->_db->getFieldFromId($this->_tbl_module, 'name', 'id', $instance);

		if($this->_instance && empty($this->_instanceName)) exit(error::syserrorMessage("options", "setData", "Istanza di ".$class." non trovata", __LINE__));

		if($class) $this->_class = $class;
		else exit(error::syserrorMessage("email", "setData", "Classe ".$class." inesistente", __LINE__));

		if(!$this->_instance) $this->_instanceName = $this->_class; 		
		
		$this->_class_prefix = $this->field_class('tbl_name', $this->_class);
		$this->_tbl_email = $this->_class_prefix.'_email';

		$this->_return_link = method_exists($class, "manageDoc")? $this->_instanceName."-manageDoc": $this->_instanceName."-manage".ucfirst($class);

	}
	
	private function field_class($field, $class_name){
		
		$query = "SELECT ma.$field FROM ".$this->_tbl_module_app." AS ma, ".$this->_tbl_module." AS m WHERE m.class='$class_name' AND m.type='class' AND m.class=ma.name";
		$a = $this->_db->selectquery($query);
		if(sizeof($a) > 0)
		{
			foreach ($a AS $b)
			{
				$value = $b[$field];
			}
		}
		else
		{
			$query = "SELECT $field FROM ".$this->_tbl_module_app." WHERE name='$class_name' AND type='class'";
			$a = $this->_db->selectquery($query);
			if(sizeof($a) > 0)
			{
				foreach ($a AS $b)
				{
					$value = $b[$field];
				}
			}
		}
		return $value;
	}

	public function manageDoc(){

		if($this->_action == 'save') {$this->actionEmail();exit;}

		$id = cleanVar($_GET, 'id', 'int', '');

		$GINO = "<div class=\"vertical_1\">\n";
		$GINO .= $this->listEmail($id);
		$GINO .= "</div>\n";

		$GINO .= "<div class=\"vertical_2\">\n";
		$GINO .= in_array($this->_action, array($this->_act_modify, $this->_act_insert))?$this->formEmail($id):$this->infoEmail();
		$GINO .= "</div>\n";

		$GINO .= "<div class=\"null\"></div>";

		return $GINO;
	}

	private function infoEmail(){
		
		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'h1', 'headerLabel'=>_("Informazioni")));
		$buffer = "<p>"._("E' possibile personalizzare l'oggetto e il testo del messaggio. Se l'oggetto non viene personalizzato verrà visualizzato quello predefinito.")."</p>";
		$buffer .= "<p>"._("Nella preview i testi personalizzati sono sottolineati in arancione.")."</p>";

		$htmlsection->content = $buffer;

		return $htmlsection->render();
	}

	private function listEmail($select_doc){

		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'h1', 'headerLabel'=>_("Email")));

		$query = "SELECT * FROM ".$this->_tbl_email."";
		$a = $this->_db->selectquery($query);
		if(sizeof($a) > 0)
		{
			$htmlList = new htmlList(array("numItems"=>sizeof($a), "separator"=>true));
			$GINO = $htmlList->start();

			foreach ($a AS $b)
			{
				$id = $b['id'];
				$description = htmlChars($b['description']);
				$subject = htmlCharsText($b['subject']);
				$text = htmlCharsText($b['text']);

				$selected = $id == $select_doc?true:false;				
				
				$GINO .= "<script>\n";
				$GINO .= "window.addEvent('domready', function() {tog_".$id." = new Fx.Reveal('vw_$id');})";
				$GINO .= "</script>\n";
				$link_view = "<span onclick=\"tog_$id.toggle()\" class=\"link\">".pub::icon('view')."</span>";
				$link_modify = "<a href=\"$this->_home?evt[$this->_return_link]&block=email&id=$id&amp;action=".$this->_act_modify."\">".pub::icon('modify')."</a>";	
				$GINO .= $htmlList->item("$description", array($link_view, $link_modify), $selected, false);
				$GINO .= "<li id=\"vw_$id\" style=\"display:none;\">".$this->previewEmail($id)."</li>";
				$GINO .= $htmlList->listLine();
			}
			$GINO .= $htmlList->end();
		}

		$htmlsection->content = $GINO;

		return $htmlsection->render();
	}

	private function formEmail($id){

		$gform = new Form('gform', 'post', true);
		$gform->load('dataform');

		$query = "SELECT * FROM ".$this->_tbl_email." WHERE id='$id'";
		$a = $this->_db->selectquery($query);
		if(sizeof($a) > 0) {
			foreach ($a AS $b) {
				$id = $b['id'];
				$description = htmlInput($b['description']);
				$subject = htmlInput($b['subject']);
				$text = htmlInput($b['text']);
			}
		}
		$title = _("Modifica contenuti");
		$submit = _("modifica");

		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'h1', 'headerLabel'=>$title));
		$required = 'description';
		$buffer = $gform->form($this->_home."?evt[$this->_return_link]&block=email&action=save", '', $required);
		$buffer .= $gform->hidden('id', $id);
		$buffer .= $gform->cinput('description', 'text', $description, _("Descrizione"), array("required"=>true, "size"=>45, "maxlength"=>255));
		$buffer .= $gform->cinput('subject', 'text', $subject, _("Oggetto"), array("size"=>45, "maxlength"=>200));
		$buffer .= $gform->ctextarea('text', $text, _("Testo"), array("cols"=>45, "rows"=>7));
		$buffer .= $gform->cinput('submit_action', 'submit', $submit, '', array("classField"=>"submit"));
		$buffer .= $gform->cform();

		$htmlsection->content = $buffer;

		return $htmlsection->render();
	}

	public function actionEmail(){

		$id = cleanVar($_POST, 'id', 'int', '');
		$description = cleanVar($_POST, 'description', 'string', '');
		$subject = cleanVar($_POST, 'subject', 'string', '');
		$text = cleanVar($_POST, 'text', 'string', '');

		$gform = new Form('gform', 'post', true);
		$gform->save('dataform');
		$req_error = $gform->arequired();

		$link_error = $this->_home."?evt[$this->_return_link]&block=email&action=$this->_act_modify&id=$id";

		if($req_error > 0) 
			exit(error::errorMessage(array('error'=>1), $link_error));

		$query = "UPDATE ".$this->_tbl_email." SET description='$description', subject='$subject', text='$text' WHERE id='$id'";
		$result = $this->_db->actionquery($query);

		EvtHandler::HttpCall($this->_home, $this->_return_link, "block=email");
	}

	/**
	 * Invio di una email
	 * 
	 * @param string $to indirizzo di destinazione
	 * @param integer $id codice dello schema email associato
	 * @param string $data stringa contenente i riferimenti da inserire nel testo dell'email
	 * @return email
	 */
	public function schemaSendEmail($to, $id, $data){
		
		$class = new $this->_class;
		
		// Contenuti personalizzati
		$contents = $this->contentsEmail($id, 'send');
		
		$subject_function = 'subjectEmail_'.$id;
		$message_function = 'textEmail_'.$id;
		
		$subject = $class->$subject_function();
		$text = $class->$message_function($data);
		
		// Contents
		if(empty($contents[0])) $subject_all = $subject; else $subject_all = $contents[0];
		
		if(empty($contents[1])) $break = ''; else $break = "\n\n";
		$text_all = $text.$break.$contents[1].$this->emailPolicy();
		// End

		$object = $subject_all;
		$message = $text_all;
		$from = "From: ".$this->_email_from;
		mail($to, $object, $message, $from);
	}
	
	private function previewEmail($id){

		// Contenuti personalizzati
		$contents = $this->contentsEmail($id, 'view');

		$subject_function = 'subjectEmail_'.$id;
		$message_function = 'textEmail_'.$id;

		$subject = call_user_func(array($this->_class, $subject_function), $this->_instance);
		$text = call_user_func(array($this->_class, $message_function), $this->_instance);
		
		// Contents
		if(empty($contents[0])) $subject_all = $subject; else $subject_all = "<span style=\"background-color:#ffcc00\">".$contents[0]."</span>";
		
		if(empty($contents[1])) $break = ''; else $break = "<br /><br />";
		$text_all = $text.$break."<span style=\"background-color:#ffcc00\">".$contents[1].'</span><br />'.htmlCharsText($this->emailPolicy());
		// End
		
		$GINO = '';
		$GINO .= "<div><b>"._("Oggetto")."</b></div><p>".$subject_all."</p>";
		$GINO .= "<div><b>"._("Testo")."</b></div><p>".$text_all."</p>";

		return $GINO;
	}
	
	private function contentsEmail($id, $type){

		$query = "SELECT subject, text FROM ".$this->_tbl_email." WHERE id='$id'";
		$a = $this->_db->selectquery($query);
		if(sizeof($a) > 0) {
			$subject_db = $a[0]['subject'];
			$text_db = $a[0]['text'];
		}
		else { $subject_db = ''; $text_db = '';}

		if($type == 'send') {
			if(!empty($subject_db)) $subject_db = ' '.$subject_db;
			if(!empty($text_db)) $text_db = $text_db."\n";
		}
		elseif ($type == 'view') {
			if(!empty($subject_db)) $subject_db = ' '.htmlCharsText($subject_db);
			if(!empty($text_db)) $text_db = htmlCharsText($text_db).'<br />';
		}

		return array($subject_db, $text_db);
	}
}
?>
