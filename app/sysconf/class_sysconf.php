<?php
/**
 * @file class_sysconf.php
 * @brief Contiene la classe sysconf
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

/**
 * @brief Gestione delle principali impostazioni di sistema
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 * 
 * Le impostazioni di sistema sono:
 *   - Livello base autenticazione
 *   - Livello autenticazione amministratore
 *   - Gestione lingue
 *   - Lingua di default (se la gestione lingue è disattivata)
 *   - Log degli accessi
 *   - Metodo criptazione password
 *   - Caricamento metodi richiamati da url
 *   - Descrizione sito
 *   - Parole chiave sito
 *   - Titolo sito
 *   - Abilita la cache di contenuti e dati
 *   - Codice google analytics (es. UA-1234567-1)
 *   - Chiave pubblica reCAPTCHA
 *   - Chiave privata reCAPTCHA
 *   - Email amministratore di sistema
 *   - Email invio automatico comunicazioni
 *   - Ottimizzazione per dispositivi mobili (Palmari, Iphone)
 *   - Contenuto file robots.txt
 *   - Permalink
 */
class sysconf extends AbstractEvtClass{

	private $_title;
	
	function __construct(){

		parent::__construct();

		$this->_instance = 0;
		$this->_instanceName = $this->_className;

		$this->_title = _("Impostazioni");
	}
	
	/**
	 * Interfaccia per la gestione delle impostazioni
	 * 
	 * Visualizzazione e form di modifica
	 * 
	 * @return string
	 */
	public function manageSysconf() {

		$this->accessType($this->_access_admin);
		
		$htmltab = new htmlTab(array("linkPosition"=>'right', "title"=>_("Impostazioni di sistema")));
		$link_dft = "<a href=\"".$this->_home."?evt[".$this->_className."-manageSysconf]\">"._("Gestione")."</a>";
		$sel_link = $link_dft;

		$action = cleanVar($_GET, 'act', 'string', '');
		
		if($action != $this->_act_modify)
			$link = "<a href=\"".$this->_home."?evt[".$this->_className."-manageSysconf]&act=".$this->_act_modify."\"><input type=\"submit\" class=\"submit\" value=\""._("modifica")."\" name=\"submit_modify_sysconf\" /></a>";
		
		$htmlsection = new htmlSection();

		$GINO = $this->scriptAsset("sysconf.css", "sysconf.CSS", 'css');
			
		$query = "SELECT * FROM ".$this->_tbl_sysconf." WHERE id='1'";
		$a = $this->_db->selectquery($query);
		if(sizeof($a) > 0)
		{			
			foreach($a AS $b)
			{
				if($action == $this->_act_modify) {
					foreach($b as $k=>$v) $$k = htmlInput($v);

				}
				else 
				{
					$function = 'htmlChars';
					$text_empty = "<span class=\"evidence\">"._("da inserire")."</span>";
					
					foreach($b as $k=>$v) $$k = htmlChars($v);
					
					if(empty($multi_language)) $multi_language = _("no");
					if(empty($dft_language)) $dft_language = $text_empty;
					if(empty($precharge_mdl_url)) $precharge_mdl_url = _("si");
					if($log_access == 'yes') $log_access = _("si");
					if(empty($password_crypt) OR $password_crypt == 'none') $password_crypt = _("no");
					if(empty($head_description)) $head_description = $text_empty;
					if(empty($head_keywords)) $head_keywords = $text_empty;
					if(empty($head_title)) $head_title = $text_empty;
					if(empty($google_analytics)) $google_analytics = $text_empty;
					if(empty($captcha_public)) $captcha_public = $text_empty;
					if(empty($captcha_private)) $captcha_private = $text_empty;
					if(empty($email_name)) $email_name = $text_empty;
					if(empty($email_admin)) $email_admin = $text_empty;
					if(empty($email_from_app)) $email_from_app = $text_empty;
					if(empty($permalinks)) $permalinks = _("si");
				}
				$robots = is_readable(SITE_ROOT.OS.'robots.txt') ? file_get_contents(SITE_ROOT.OS.'robots.txt') : "";
				
				$t_user_role = _("Livello base autenticazione");
				$t_admin_role = _("Livello autenticazione amministratore");
				$t_multi_language = _("Gestione lingue");
				$t_dft_language = _("Lingua di default (se la gestione lingue è disattivata)");
				$t_precharge_mdl_url = _("Caricamento metodi richiamati da url");
				$t_log_access = _("Log degli accessi");
				$t_password_crypt = _("Metodo criptazione password");
				$t_head_description = _("Descrizione sito");
				$t_head_keywords =_("Parole chiave sito");
				$t_head_title = _("Titolo sito");
				$t_google_analytics = _("Codice google analytics")." (es. UA-1234567-1)";
				$t_captcha_public = _("Chiave pubblica reCAPTCHA");
				$t_captcha_private = _("Chiave privata reCAPTCHA");
				$t_email_admin = _("Email amministratore di sistema");
				$t_email_from_app = _("Email invio automatico comunicazioni");
				$t_enable_cache = _("Abilita la cache di contenuti e dati");
				$t_robots = _("Contenuto file robots.txt");
				
				$t_mobile = _("Ottimizzazione per dispositivi mobili (Palmari, Iphone)");
				$t_permalinks = _("Permalink");
			}
		}
		
		if($action == $this->_act_modify)
		{
			$this->_gform = new Form('gform', 'post', true, array("trnsl_id"=>1, "trnsl_table"=>$this->_tbl_sysconf));
			$this->_gform->load('dataform', true);
			
			if($this->_session_role == $this->_max_role) $required = 'user_role,admin_role,multi_language,dft_language,password_crypt';
			else $required = '';
			
			$submit = _("modifica");
			
			$GINO = $this->_gform->form($this->_home."?evt[".$this->_className."-actionSysconf]", '', $required, array("generateToken"=>true));
			
			if($this->_session_role == $this->_max_role)
			{
				$query = "SELECT role_id, name FROM ".$this->_tbl_user_role." ORDER BY role_id ASC";
				$GINO .= $this->_gform->cselect('user_role', $user_role, $query , array($t_user_role, _("Livello base per il quale è prevista la registrazione")),
				       array("required"=>true, "noFirst"=>true));
				$GINO .= $this->_gform->cselect('admin_role', $admin_role, $query , $t_admin_role,
				       array("required"=>true, "noFirst"=>true));
				$GINO .= $this->_gform->cradio('multi_language', $multi_language, array('yes'=>_("si"), 'no'=>_("no")), 'no', $t_multi_language);
				$query = "SELECT code, language FROM ".$this->_tbl_language." ORDER BY language ASC";
				$GINO .= $this->_gform->cselect('dft_language', $dft_language, $query , $t_dft_language,
				       array("required"=>true, "noFirst"=>true));
				$GINO .= $this->_gform->cselect('password_crypt', $password_crypt, array('none'=>_("no"), 'md5'=>'md5', 'sha1'=>'sha1') , $t_password_crypt,
				       array("required"=>true, "noFirst"=>true));

				$GINO .= $this->_gform->cradio('log_access', $log_access, array('yes'=>_("si"), 'no'=>_("no")), 'no', array($t_log_access, _("Log degli accessi all'area privata")));
				$GINO .= $this->_gform->cradio('precharge_mdl_url', $precharge_mdl_url, array('yes'=>_("si"), 'no'=>_("no")), 'yes', array($t_precharge_mdl_url, _("Importante: lasciare a 'sì' se insicuri. <br/> L'opzione permette di caricare i metodi richiamati da url prima della composizione del layout tramite template e quindi di eseguire azioni anche in assenza del segnaposto nel template.")));
			}
			$GINO .= $this->_gform->ctextarea('head_description', $head_description, array($t_head_description, _("Per motori di ricerca")),
				array("cols"=>50, "rows"=>4, "trnsl"=>true, "field"=>"head_description"));
			$GINO .= $this->_gform->ctextarea('head_keywords', $head_keywords, array($t_head_keywords, _("Per motori di ricerca")),
				array("cols"=>50, "rows"=>4, "trnsl"=>true, "field"=>"head_keywords"));
			$GINO .= $this->_gform->cinput('head_title', 'text', $head_title, $t_head_title, array("size"=>52, "maxlength"=>200, "trnsl"=>true, "field"=>"head_title"));
			$GINO .= $this->_gform->cradio('enable_cache', $enable_cache, array(1=>_("si"), 0=>_("no")), 0, $t_enable_cache);
			$GINO .= $this->_gform->cinput('google_analytics', 'text', $google_analytics, $t_google_analytics, array("size"=>52, "maxlength"=>200));
			$GINO .= $this->_gform->cinput('captcha_public', 'text', $captcha_public, array($t_captcha_public, _("Se non vengono inserite le chiavi per l'utilizzo di reCAPTCHA verrà utilizzato il sistema di default.")), array("size"=>52, "maxlength"=>200));
			$GINO .= $this->_gform->cinput('captcha_private', 'text', $captcha_private, $t_captcha_private, array("size"=>52, "maxlength"=>200));
			$GINO .= $this->_gform->cinput('email_admin', 'text', $email_admin, $t_email_admin, array("size"=>52, "maxlength"=>200));
			$GINO .= $this->_gform->cinput('email_from_app', 'text', $email_from_app, $t_email_from_app, array("size"=>52, "maxlength"=>200));
			$GINO .= $this->_gform->cradio('mobile', $mobile, array('yes'=>_("si"), 'no'=>_("no")), 'no', array($t_mobile, _("Per una corretta visualizzazione del sito sui dispositivi mobili occorre personalizzare i template e il CSS.")));
			$GINO .= $this->_gform->ctextarea('robots', $robots, $t_robots, array("cols"=>70, "rows"=>10));
			$GINO .= $this->_gform->cradio('permalinks', $permalinks, array('yes'=>_("si"), 'no'=>_("no")), 'yes', array($t_permalinks, _("Se si disattivano i permalink impostare 'RewriteEngine' a 'off' nel file .htaccess.")));

			$GINO .= $this->_gform->cinput('submit_modify_sysconf', 'submit', $submit, '', array('classField'=>'submit'));
		
			$GINO .= $this->_gform->cform();
		}
		else
		{
			$GINO .= "<table class=\"viewConf\">";
			
			$GINO .= "<tr><td class=\"confLabel_tooltipfull\" title=\""._("Livello base di autenticazione::Livello di utenza a partire dal quale è prevista la registrazione al sito.")."\">$t_user_role</td>";
			$GINO .= "<td class=\"confValue\">".$this->_db->getFieldFromId($this->_tbl_user_role, 'name', 'role_id', $user_role)."</td></tr>";

			$GINO .= "<tr><td class=\"confLabel_tooltipfull\" title=\""._("Livello autenticazione amministratore::Livello di utenza a partire dal quale sono attive le funzionalità amministrative complete.")."\">$t_admin_role</td>";
			$GINO .= "<td class=\"confValue\">".$this->_db->getFieldFromId($this->_tbl_user_role, 'name', 'role_id', $admin_role)."</td></tr>";

			$GINO .= "<tr><td class=\"confLabel_tooltipfull\" title=\""._("Gestione lingue::Gestione di un sito multi-lingua. Le lingue attive e quella principale sono da settarsi nel modulo Lingue del sistema.")."\">$t_multi_language</td>";
			$GINO .= "<td class=\"confValue\">".$multi_language."</td></tr>";

			$GINO .= "<tr><td class=\"confLabel_tooltipfull\" title=\""._("Lingua di default::Lingua dei contenuti del sito nel caso in cui la gestione multilingua sia disattivata.")."\">$t_dft_language</td>";
			$GINO .= "<td class=\"confValue\">".$dft_language."</td></tr>";

			$GINO .= "<tr><td class=\"confLabel_tooltipfull\" title=\""._("Log degli accessi::Log di tutti gli accessi all'area riservata del sito da parte degli utenti.")."\">$t_log_access</td>";
			$GINO .= "<td class=\"confValue\">".$log_access."</td></tr>";

			$GINO .= "<tr><td class=\"confLabel_tooltipfull\" title=\""._("Metodo di criptazione della password::Algoritmo crittografico di hashing utilizzato per le password degli utenti.")."\">$t_password_crypt</td>";
			$GINO .= "<td class=\"confValue\">".$password_crypt."</td></tr>";
			
			$GINO .= "<tr><td class=\"confLabel_tooltipfull\" title=\""._("Caricamento dei metodi richiamati da url::Caricamento ed esecuzione del metodo invocato tramite URL prima della composizione del layout tramite template.")."\">$t_precharge_mdl_url</td>";
			$GINO .= "<td class=\"confValue\">".$precharge_mdl_url."</td></tr>";
			
			$GINO .= "<tr><td class=\"confLabel_tooltipfull\" title=\""._("Descrizione del sito::Descrizione che compare nei meta tag e letta dai motori di ricerca.")."\">$t_head_description</td>";
			$GINO .= "<td class=\"confValue\">".$head_description."</td></tr>";

			$GINO .= "<tr><td class=\"confLabel_tooltipfull\" title=\""._("Parole chiave::Parole chiave che rappresentano i contenuti del sito, lette dai motori di ricerca.")."\">$t_head_keywords</td>";
			$GINO .= "<td class=\"confValue\">".$head_keywords."</td></tr>";

			$GINO .= "<tr><td class=\"confLabel_tooltipfull\" title=\""._("Titolo sito::Titolo del sito visualizzato nella finestra/scheda dei Browser")."\">$t_head_title</td>";
			$GINO .= "<td class=\"confValue\" >".$head_title."</td></tr>";
			
			$GINO .= "<tr><td class=\"confLabel_tooltipfull\" title=\""._("Abilita cache::Abilita le funzionalità di caching su file dei contenuti e dati dei singoli moduli e delle skin")."\">$t_enable_cache</td>";
			$GINO .= "<td class=\"confValue\" >";
			if($enable_cache) {
				$GINO .= "<form method=\"post\" action=\"$this->_home?evt[$this->_className-emptyCache]\">"._("si")." &#160; <input type=\"submit\" class=\"submit\" name=\"empty_cache\" value=\""._("svuota")."\" /></form>";
			}
			else $GINO .= _("no");
			$GINO .= "</td></tr>";

			$GINO .= "<tr><td class=\"confLabel_tooltipfull\" title=\""._("Codice google analytics::Codice da inserire per utilizzare il sistema di statistiche google analytics")."\">$t_google_analytics</td>";
			$GINO .= "<td class=\"confValue\">".$google_analytics."</td></tr>";

			$GINO .= "<tr><td class=\"confLabel_tooltipfull\" title=\""._("Chiave pubblica reCAPTCHA::Chiave pubblica per l'utilizzo del sistema captcha reCAPTCHA. Se non inserita il sistema utilizzerà il sistema di prevenzione di default.")."\">$t_captcha_public</td>";
			$GINO .= "<td class=\"confValue\">".$captcha_public."</td></tr>";

			$GINO .= "<tr><td class=\"confLabel_tooltipfull\" title=\""._("Chiave privata reCAPTCHA::Chiave privata per l'utilizzo del sistema captcha reCAPTCHA. Se non inserita il sistema utilizzerà il sistema di prevenzione di default.")."\">$t_captcha_private</td>";
			$GINO .= "<td class=\"confValue\">".$captcha_private."</td></tr>";
			$GINO .= "<tr><td class=\"confLabel_tooltipfull\" title=\""._("E-mail dell'utente amministratore di sistema::Indirizzo e-mail dell'amministratore del sito.")."\">$t_email_admin</td>";
			$GINO .= "<td class=\"confValue\">".$email_admin."</td></tr>";

			$GINO .= "<tr><td class=\"confLabel_tooltipfull\" title=\""._("E-mail invio automatico comunicazioni::Indirizzo e-mail utilizzato per inviare comunicazioni automatiche da parte del sistema")."\">$t_email_from_app</td>";
			$GINO .= "<td class=\"confValue\">".$email_from_app."</td></tr>";

			$GINO .= "<tr><td class=\"confLabel_tooltipfull\" title=\""._("Ottimizzazione per dispositivi mobili::Attiva il riconoscimento di dispositivi mobili. E' necessario configurare correttamente i template e le skin per una corretta visualizzazione.")."\">$t_mobile</td>";
			$GINO .= "<td class=\"confValue\" >".$mobile."</td></tr>";
			
			$GINO .= "<tr><td class=\"confLabel_tooltipfull\" title=\""._("Contenuto del file robots.txt::Il file robots.txt viene utilizzato per fornire indicazioni riguardo all'indicizzazione dei contenuti del sito nei motori di ricerca")."\">$t_robots</td>";
			$GINO .= "<td class=\"confValue\" >".cutHtmlText(nl2br($robots), '20', '...', false, true, true, null)."</td></tr>";
			
			$GINO .= "<tr><td class=\"confLabel_tooltipfull\" title=\""._("Permalink::Attiva i permalink. Se si disattivano i permalink impostare 'RewriteEngine' a 'off' nel file .htaccess.")."\">$t_permalinks</td>";
			$GINO .= "<td class=\"confValue\" >".$permalinks."</td></tr>";
			
			$GINO .= "<tr><td></td>";
			$GINO .= "<td class=\"confValue\" >".$link."</td></tr>";

			$GINO .= "</table>";
		}
		
		$htmlsection->content = $GINO;

		$buffer = $htmlsection->render();
		
		$htmltab->navigationLinks = array($link_dft);
		$htmltab->selectedLink = $sel_link;
		$htmltab->htmlContent = $buffer;
		return $htmltab->render();
	}
	
	public function emptyCache() {
		
		$this->deleteFileDir(CACHE_DIR, false);

		header("Location: $this->_home?evt[$this->_className-manageSysconf]");
	}

	/**
	 * Modifica le impostazioni si sistema
	 */
	public function actionSysconf() {
	
		$this->accessType($this->_access_admin);
		
		$this->_gform = new Form('gform','post', false, array("verifyToken"=>true));
		$this->_gform->save('dataform');
		$req_error = $this->_gform->arequired();
		
		$user_role = cleanVar($_POST, 'user_role', 'int', '');
		$admin_role = cleanVar($_POST, 'admin_role', 'int', '');
		$multi_language = cleanVar($_POST, 'multi_language', 'string', '');
		$dft_language = cleanVar($_POST, 'dft_language', 'string', '');
		$log_access = cleanVar($_POST, 'log_access', 'string', '');
		$password_crypt = cleanVar($_POST, 'password_crypt', 'string', '');
		$precharge_mdl_url = cleanVar($_POST, 'precharge_mdl_url', 'string', '');
		$head_description = cleanVar($_POST, 'head_description', 'string', '');
		$head_keywords = cleanVar($_POST, 'head_keywords', 'string', '');
		$head_title = cleanVar($_POST, 'head_title', 'string', '');
		$google_analytics = cleanVar($_POST, 'google_analytics', 'string', '');
		$captcha_public = cleanVar($_POST, 'captcha_public', 'string', '');
		$captcha_private = cleanVar($_POST, 'captcha_private', 'string', '');
		$email_admin = cleanVar($_POST, 'email_admin', 'string', '');
		$email_from_app = cleanVar($_POST, 'email_from_app', 'string', '');
		$mobile = cleanVar($_POST, 'mobile', 'string', '');
		$enable_cache = cleanVar($_POST, 'enable_cache', 'int', '');
		$robots = $_POST['robots'];
		$permalinks = cleanVar($_POST, 'permalinks', 'string', '');
		
		$link_error = $this->_home."?evt[$this->_className-manageSysconf]&act=".$this->_act_modify;
		$link = '';
		
		if($req_error > 0) 
			exit(error::errorMessage(array('error'=>1), $link_error));
		
		if($this->_session_role == $this->_max_role)
		$query_plus = "user_role=$user_role, admin_role=$admin_role, multi_language='$multi_language', dft_language='$dft_language', log_access='$log_access', password_crypt='$password_crypt', precharge_mdl_url='$precharge_mdl_url',";
		else $query_plus = '';
		
		$query = "UPDATE ".$this->_tbl_sysconf." SET $query_plus head_description='$head_description', head_keywords='$head_keywords', head_title='$head_title', enable_cache='$enable_cache', google_analytics='$google_analytics', captcha_public='$captcha_public', captcha_private='$captcha_private', email_admin='$email_admin', email_from_app='$email_from_app', mobile='$mobile', permalinks='$permalinks' WHERE id='1'";
		$result = $this->_db->actionquery($query);
		
		if($fp = @fopen(SITE_ROOT.OS."robots.txt", "wb")) {
			fwrite($fp, $robots);
			fclose($fp);
		}
		
		EvtHandler::HttpCall($this->_home, $this->_className.'-manageSysconf', $link);
	}
}
?>
