<?php
/**
 * @file class_statistics.php
 * @brief Contiene la classe statistics
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

/**
 * @brief Statistiche degli accessi all'area privata
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class statistics extends Controller {
	
	private $_title;
	
	private $_options;
	public $_optionsLabels;

	private $_group_1;
	
	private $_tbl_log_access;
	private $_stat_access, $_stat_doc, $_stat_reg;
	private $_sites;

	private $_block;
		
	function __construct() {
		
		parent::__construct();

		// options
		$this->_title = htmlChars($this->setOption('title', true));

		$this->_options = new options($this->_className, $this->_instance);
		$this->_optionsLabels = array("title"=>_("Titolo"));
		
		$this->_tbl_log_access = "sys_log_access";
		
		$this->_stat_access = 'login';
		$this->_stat_doc = 'doc';
		$this->_stat_reg = 'reg';

		$this->_block = cleanVar($_REQUEST, 'block', 'string', '');
	}
	
	/**
	 * Interfaccia alla visualizzazione delle statistiche
	 * 
	 * @see $_access_admin
	 * @return string
	 */
	public function manageStatistics() {
		
		$this->accessType($this->_access_admin);
		
		$htmltab = new htmlTab(array("linkPosition"=>'right', "title"=>$this->_title));	
		
		$link_options = "<a href=\"".$this->_home."?evt[$this->_className-manageStatistics]&block=options\">"._("Opzioni")."</a>";
		$link_dft = "<a href=\"".$this->_home."?evt[".$this->_className."-manageStatistics]\">"._("Gestione")."</a>";
		$sel_link = $link_dft;

		if($this->_block == 'options') {$GINO = sysfunc::manageOptions(null, $this->_className); $sel_link = $link_options;}
		else {
			$stat = cleanVar($_GET, 'stat', 'string', '');
			$right_col = $stat==$this->_stat_access?$this->accessStatistics():$this->infoDoc();

			$GINO = "<div class=\"vertical_1\">\n";
			$GINO .= $this->listStats($stat);
			$GINO .= "</div>\n";

			$GINO .= "<div class=\"vertical_2\">\n";
			$GINO .= $right_col;
			$GINO .= "</div>\n";

			$GINO .= "<div class=\"null\"></div>";
		}
		$htmltab->navigationLinks = array($link_options, $link_dft);
		$htmltab->selectedLink = $sel_link;
		$htmltab->htmlContent = $GINO;
		return $htmltab->render();
	}
	
	/**
	 * Elenco delle tipologie di statistica
	 * 
	 * @param string $stat tipo di statistica
	 * @return string
	 */
	private function listStats($stat) {

		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'header', 'headerLabel'=>_("Elenco")));

		$label = _("Accessi area privata");
		$link = "<a href=\"".$this->_home."?evt[".$this->_className."-manageStatistics]&amp;stat=".$this->_stat_access."\">".pub::icon('view')."</a>\n";
		$htmlList = new htmlList(array("numItems"=>1, "separator"=>false));
		$GINO = $htmlList->start();
	
		$selected = ($stat == $this->_stat_access)?true:false;				
		$GINO .= $htmlList->item($label, $link, $selected);
		$GINO .= $htmlList->end();		
		
		$htmlsection->content = $GINO;

		return $htmlsection->render();
	}
	
	/**
	 * Intestazione delle statistiche sugli accessi all'area privata
	 * 
	 * @return string
	 */
	private function accessStatistics() {
	
		$link_export = "<a href=\"$this->_home?evt[$this->_className-export]\">".pub::icon('export')."</a>";
		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'h1', 'headerLabel'=>_("Accessi utenti registrati"), "headerLinks"=>$link_export));
		$htmlsection->content = $this->accessStat();

		return $htmlsection->render();
	}
	
	/**
	 * Statistiche sugli accessi all'area privata
	 * 
	 * @see $_access_admin
	 * @return string
	 */
	public function accessStat() {
		
		$this->accessType($this->_access_admin);

		$GINO = "<table class=\"generic\">\n";
		$GINO .= "<tr>\n";
		$GINO .= "<th>"._("Utente")."</th><th>"._("Accessi totali")."</th><th>"._("Ultimo accesso")."</th>\n";
		$GINO .= "</tr>\n";
		
		$query = "SELECT user_id, CONCAT(lastname, ' ', firstname) AS name FROM ".$this->_tbl_user." ORDER BY lastname";
		$a = $this->_db->selectquery($query);
		if(sizeof($a) > 0)
		{
			$odd = true;
			foreach($a AS $b)
			{
				$tr_class = ($odd)?"trOdd":"trEven";
				$GINO .= "<tr class=\"$tr_class\">\n";
				$user_id = $b['user_id'];
				$user_name = htmlChars($b['name']);
			
				$query2 = "SELECT id, date FROM ".$this->_tbl_log_access." WHERE user_id='$user_id' ORDER BY date DESC";
				$a2 = $this->_db->selectquery($query2);
				if(sizeof($a2)>0) {
					$last_access = $a2[0]['date'];
					$last_access_array = explode(' ', $last_access);
					$last_access_date = dbDateToDate($last_access_array[0], '/');
					$last_access_time = $last_access_array[1];
					$tot_access = sizeof($a2);
				}
				else {$last_access_date = null; $last_access_time = null; $tot_access = null;}
				
				$GINO .= "<td>$user_name</td><td>$tot_access</td><td>$last_access_date $last_access_time</td>\n";
				$GINO .= "</tr>\n";
				
				$odd = !$odd;
			}
		}
		$GINO .= "</table>\n";
				
		return $GINO;
	}
	
	private function infoDoc(){

		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'h1', 'headerLabel'=>_("Informazioni")));
		$log_access = $this->_db->getFieldFromId($this->_tbl_sysconf,'log_access','id',1);
		$GINO = '';
		if($log_access == 'no') 
			$GINO .= "<p>"._("Attenzione, attualmente il log degli accessi è disattivato. Modificare il settaggio dalle Impostazioni di sistema.")."</p>\n";

		$GINO .= "<p>";
		$GINO .= "<b>"._("Accessi area privata")."</b><br/>";
		$GINO .= _("Resoconto degli accessi al sistema (sito principale e sito secondario) da parte degli utenti registrati, con numero totale di accessi, data e ora dell'ultimo accesso effettuato.");
		$GINO .= "</p>\n";

		$htmlsection->content = $GINO;

		return $htmlsection->render();
	}

	/**
	 * Esportazione delle statistiche sugli accessi all'area privata
	 * 
	 * @see $_access_admin
	 * @return file
	 */
	public function export() {
	
		$this->accessType($this->_access_admin);

		require_once(CLASSES_DIR.OS.'class.export.php');

		$data = array();
		$data[0] = array(_("id"), _("cognome"), _("nome"), _("data"), _("ora"));

		$query = "SELECT id, user_id, date FROM ".$this->_tbl_log_access." ORDER BY date DESC";
		$a = $this->_db->selectquery($query);
		if(sizeof($a)>0) {
			foreach($a as $b) {
				$data[] = array($b['id'], $this->_db->getFieldFromId($this->_tbl_user, 'lastname', 'user_id', $b['user_id']), $this->_db->getFieldFromId($this->_tbl_user, 'firstname', 'user_id', $b['user_id']), dbDatetimeToDate($b['date'], "/"), dbDatetimeToTime($b['date']));
			}
		}

		$export = new export();
		$export->setData($data);
		$export->setOrder('date DESC');

		$filename = "log_access_".date("YmdHis").".csv";
		ob_clean();
		$export->exportData($filename, 'csv');
	}
}
?>
