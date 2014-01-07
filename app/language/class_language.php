<?php
/**
 * @file class_language.php
 * @brief Contiene la classe language
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

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
class language extends AbstractEvtClass{

	protected $_instance, $_instanceName;

	private $_options;
	public $_optionsLabels;
	private $_title;

	private static $tbl_translation = "language_translation";
	
	private $_flag_language;
	private $_flag_prefix;
	private $_flag_suffix;
	private $_language_codes, $_country_codes;
	
	private $_input_field, $_textarea_field, $_fckeditor_field;
	 
	function __construct(){
		
		parent::__construct();

		$this->_instance = 0;
		$this->_instanceName = $this->_className;

		$this->setAccess();

		$this->_title = htmlChars($this->setOption('title', true));
		$this->_flag_language = $this->setOption('opt_flag');
		$this->_flag_prefix = "flag_";
		$this->_flag_suffix = ".gif";

		$this->_options = new options($this->_className, $this->_instance);
		$this->_optionsLabels = array("title"=>_("Titolo"), "opt_flag"=>_("Bandiere come etichette"));
		
		$this->_input_field = 'input';
  		$this->_textarea_field = 'textarea';
  		$this->_fckeditor_field = 'fckeditor';
		
		$this->_language_codes = $this->langCodes();
		$this->_country_codes = $this->countryCodes();
	}
	
	/**
	 * Definizione dei permessi di visualizzazione aggiuntivi a quello base
	 * 
	 * @see AbstractEvtClass::permission()
	 */
	public static function permission(){

		$access_2 = _("Permessi di amministrazione");
		$access_3 = '';
		return array($access_2, $access_3);
	}

	/**
	 * Elenco dei metodi che possono essere richiamati dal menu e dal template
	 * 
	 * @return array
	 */
	public static function outputFunctions() {

		$list = array(
			"choiceLanguage" => array("label"=>_("Scelta lingua"), "role"=>'1'),
		);

		return $list;
	}

	/**
	 * Codici lingua
	 * 
	 * @return array
	 */
	private function langCodes(){

		return array(
		'aa'=>'Afar',
		'ab'=>'Abkhazian',
		'ae'=>'Avestan',
		'af'=>'Afrikaans',
		'am'=>'Amharic',
		'ar'=>'Arabic',
		'as'=>'Assamese',
		'ay'=>'Aymara',
		'az'=>'Azerbaijani',
		'ba'=>'Bashkir',
		'be'=>'Byelorussian; Belarusian',
		'bg'=>'Bulgarian',
		'bh'=>'Bihari',
		'bi'=>'Bislama',
		'bn'=>'Bengali; Bangla',
		'bo'=>'Tibetan',
		'br'=>'Breton',
		'bs'=>'Bosnian',
		'ca'=>'Catalan',
		'ce'=>'Chechen',
		'ch'=>'Chamorro',
		'co'=>'Corsican',
		'cs'=>'Czech',
		'cu'=>'Church Slavic',
		'cv'=>'Chuvash',
		'cy'=>'Welsh',
		'da'=>'Danish',
		'de'=>'German',
		'dz'=>'Dzongkha; Bhutani',
		'el'=>'Greek',
		'en'=>'English',
		'eo'=>'Esperanto',
		'es'=>'Spanish',
		'et'=>'Estonian',
		'eu'=>'Basque',
		'fa'=>'Persian',
		'fi'=>'Finnish',
		'fj'=>'Fijian; Fiji',
		'fo'=>'Faroese',
		'fr'=>'French',
		'fy'=>'Frisian',
		'ga'=>'Irish',
		'gd'=>'Scots; Gaelic',
		'gl'=>'Gallegan; Galician',
		'gn'=>'Guarani',
		'gu'=>'Gujarati',
		'gv'=>'Manx',
		'ha'=>'Hausa (?)',
		'he'=>'Hebrew (formerly iw)',
		'hi'=>'Hindi',
		'ho'=>'Hiri Motu',
		'hr'=>'Croatian',
		'hu'=>'Hungarian',
		'hy'=>'Armenian',
		'hz'=>'Herero',
		'ia'=>'Interlingua',
		'id'=>'Indonesian (formerly in)',
		'ie'=>'Interlingue',
		'ik'=>'Inupiak',
		'io'=>'Ido',
		'is'=>'Icelandic',
		'it'=>'Italian',
		'iu'=>'Inuktitut',
		'ja'=>'Japanese',
		'jv'=>'Javanese',
		'ka'=>'Georgian',
		'ki'=>'Kikuyu',
		'kj'=>'Kuanyama',
		'kk'=>'Kazakh',
		'kl'=>'Kalaallisut; Greenlandic',
		'km'=>'Khmer; Cambodian',
		'kn'=>'Kannada',
		'ko'=>'Korean',
		'ks'=>'Kashmiri',
		'ku'=>'Kurdish',
		'kv'=>'Komi',
		'kw'=>'Cornish',
		'ky'=>'Kirghiz',
		'la'=>'Latin',
		'lb'=>'Letzeburgesch',
		'ln'=>'Lingala',
		'lo'=>'Lao; Laotian',
		'lt'=>'Lithuanian',
		'lv'=>'Latvian; Lettish',
		'mg'=>'Malagasy',
		'mh'=>'Marshall',
		'mi'=>'Maori',
		'mk'=>'Macedonian',
		'ml'=>'Malayalam',
		'mn'=>'Mongolian',
		'mo'=>'Moldavian',
		'mr'=>'Marathi',
		'ms'=>'Malay',
		'mt'=>'Maltese',
		'my'=>'Burmese',
		'na'=>'Nauru',
		'nb'=>'Norwegian Bokmål',
		'nd'=>'Ndebele, North',
		'ne'=>'Nepali',
		'ng'=>'Ndonga',
		'nl'=>'Dutch',
		'nn'=>'Norwegian Nynorsk',
		'no'=>'Norwegian',
		'nr'=>'Ndebele, South',
		'nv'=>'Navajo',
		'ny'=>'Chichewa; Nyanja',
		'oc'=>'Occitan; Provençal',
		'om'=>'(Afan) Oromo',
		'or'=>'Oriya',
		'os'=>'Ossetian; Ossetic',
		'pa'=>'Panjabi; Punjabi',
		'pi'=>'Pali',
		'pl'=>'Polish',
		'ps'=>'Pashto, Pushto',
		'pt'=>'Portuguese',
		'qu'=>'Quechua',
		'rm'=>'Rhaeto-Romance',
		'rn'=>'Rundi; Kirundi',
		'ro'=>'Romanian',
		'ru'=>'Russian',
		'rw'=>'Kinyarwanda',
		'sa'=>'Sanskrit',
		'sc'=>'Sardinian',
		'sd'=>'Sindhi',
		'se'=>'Northern Sami',
		'sg'=>'Sango; Sangro',
		'si'=>'Sinhalese',
		'sk'=>'Slovak',
		'sl'=>'Slovenian',
		'sm'=>'Samoan',
		'sn'=>'Shona',
		'so'=>'Somali',
		'sq'=>'Albanian',
		'sr'=>'Serbian',
		'ss'=>'Swati; Siswati',
		'st'=>'Sesotho; Sotho, Southern',
		'su'=>'Sundanese',
		'sv'=>'Swedish',
		'sw'=>'Swahili',
		'ta'=>'Tamil',
		'te'=>'Telugu',
		'tg'=>'Tajik',
		'th'=>'Thai',
		'ti'=>'Tigrinya',
		'tk'=>'Turkmen',
		'tl'=>'Tagalog',
		'tn'=>'Tswana; Setswana',
		'to'=>'Tonga (?)',
		'tr'=>'Turkish',
		'ts'=>'Tsonga',
		'tt'=>'Tatar',
		'tw'=>'Twi',
		'ty'=>'Tahitian',
		'ug'=>'Uighur',
		'uk'=>'Ukrainian',
		'ur'=>'Urdu',
		'uz'=>'Uzbek',
		'vi'=>'Vietnamese',
		'vo'=>'Volap@"{u}k; Volapuk',
		'wa'=>'Walloon',
		'wo'=>'Wolof',
		'xh'=>'Xhosa',
		'yi'=>'Yiddish (formerly ji)',
		'yo'=>'Yoruba',
		'za'=>'Zhuang',
		'zh'=>'Chinese',
		'zu'=>'Zulu'
		);
	}

	/**
	 * Codici stato
	 * 
	 * @return array
	 */
	private function countryCodes(){

		return array(
		'AD'=>'Andorra',
		'AE'=>'United Arab Emirates',
		'AF'=>'Afghanistan',
		'AG'=>'Antigua and Barbuda',
		'AI'=>'Anguilla',
		'AL'=>'Albania',
		'AM'=>'Armenia',
		'AN'=>'Netherlands Antilles',
		'AO'=>'Angola',
		'AQ'=>'Antarctica',
		'AR'=>'Argentina',
		'AS'=>'Samoa (American)',
		'AT'=>'Austria',
		'AU'=>'Australia',
		'AW'=>'Aruba',
		'AZ'=>'Azerbaijan',
		'BA'=>'Bosnia and Herzegovina',
		'BB'=>'Barbados',
		'BD'=>'Bangladesh',
		'BE'=>'Belgium',
		'BF'=>'Burkina Faso',
		'BG'=>'Bulgaria',
		'BH'=>'Bahrain',
		'BI'=>'Burundi',
		'BJ'=>'Benin',
		'BM'=>'Bermuda',
		'BN'=>'Brunei',
		'BO'=>'Bolivia',
		'BR'=>'Brazil',
		'BS'=>'Bahamas',
		'BT'=>'Bhutan',
		'BV'=>'Bouvet Island',
		'BW'=>'Botswana',
		'BY'=>'Belarus',
		'BZ'=>'Belize',
		'CA'=>'Canada',
		'CC'=>'Cocos (Keeling) Islands',
		'CD'=>'Congo (DemRep.)',
		'CF'=>'Central African Rep.',
		'CG'=>'Congo (Rep.)',
		'CH'=>'Switzerland',
		'CI'=>'Cote d\'Ivoire',
		'CK'=>'Cook Islands',
		'CL'=>'Chile',
		'CM'=>'Cameroon',
		'CN'=>'China',
		'CO'=>'Colombia',
		'CR'=>'Costa Rica',
		'CU'=>'Cuba',
		'CV'=>'Cape Verde',
		'CX'=>'hristmas Island',
		'CY'=>'Cyprus',
		'CZ'=>'Czech Republic',
		'DE'=>'Germany',
		'DJ'=>'Djibouti',
		'DK'=>'Denmark',
		'DM'=>'Dominica',
		'DO'=>'Dominican Republic',
		'DZ'=>'Algeria',
		'EC'=>'Ecuador',
		'EE'=>'Estonia',
		'EG'=>'Egypt',
		'EH'=>'Western Sahara',
		'ER'=>'Eritrea',
		'ES'=>'Spain',
		'ET'=>'Ethiopia',
		'FI'=>'Finland',
		'FJ'=>'Fiji',
		'FK'=>'Falkland Islands',
		'FM'=>'Micronesia',
		'FO'=>'Faeroe Islands',
		'FR'=>'France',
		'GA'=>'Gabon',
		'GB'=>'Britain (UK)',
		'GD'=>'Grenada',
		'GE'=>'Georgia',
		'GF'=>'French Guiana',
		'GH'=>'Ghana',
		'GI'=>'Gibraltar',
		'GL'=>'Greenland',
		'GM'=>'Gambia',
		'GN'=>'Guinea',
		'GP'=>'Guadeloupe',
		'GQ'=>'Equatorial Guinea',
		'GR'=>'Greece',
		'GS'=>'South Georgia and the South Sandwich Islands',
		'GT'=>'Guatemala',
		'GU'=>'Guam',
		'GW'=>'Guinea-Bissau',
		'GY'=>'Guyana',
		'HK'=>'Hong Kong',
		'HM'=>'Heard Island and McDonald Islands',
		'HN'=>'Honduras',
		'HR'=>'Croatia',
		'HT'=>'Haiti',
		'HU'=>'Hungary',
		'ID'=>'Indonesia',
		'IE'=>'Ireland',
		'IL'=>'Israel',
		'IN'=>'India',
		'IO'=>'British Indian Ocean Territory',
		'IQ'=>'Iraq',
		'IR'=>'Iran',
		'IS'=>'Iceland',
		'IT'=>'Italy',
		'JM'=>'Jamaica',
		'JO'=>'Jordan',
		'JP'=>'Japan',
		'KE'=>'Kenya',
		'KG'=>'Kyrgyzstan',
		'KH'=>'Cambodia',
		'KI'=>'Kiribati',
		'KM'=>'Comoros',
		'KN'=>'St Kitts and Nevis',
		'KP'=>'Korea (North)',
		'KR'=>'Korea (South)',
		'KW'=>'Kuwait',
		'KY'=>'Cayman Islands',
		'KZ'=>'Kazakhstan',
		'LA'=>'Laos',
		'LB'=>'Lebanon',
		'LC'=>'St Lucia',
		'LI'=>'Liechtenstein',
		'LK'=>'Sri Lanka',
		'LR'=>'Liberia',
		'LS'=>'Lesotho',
		'LT'=>'Lithuania',
		'LU'=>'Luxembourg',
		'LV'=>'Latvia',
		'LY'=>'Libya',
		'MA'=>'Morocco',
		'MC'=>'Monaco',
		'MD'=>'Moldova',
		'MG'=>'Madagascar',
		'MH'=>'Marshall Islands',
		'MK'=>'Macedonia',
		'ML'=>'Mali',
		'MM'=>'Myanmar (Burma)',
		'MN'=>'Mongolia',
		'MO'=>'Macao',
		'MP'=>'Northern Mariana Islands',
		'MQ'=>'Martinique',
		'MR'=>'Mauritania',
		'MS'=>'Montserrat',
		'MT'=>'Malta',
		'MU'=>'Mauritius',
		'MV'=>'Maldives',
		'MW'=>'Malawi',
		'MX'=>'Mexico',
		'MY'=>'Malaysia',
		'MZ'=>'Mozambique',
		'NA'=>'Namibia',
		'NC'=>'New Caledonia',
		'NE'=>'Niger',
		'NF'=>'Norfolk Island',
		'NG'=>'Nigeria',
		'NI'=>'Nicaragua',
		'NL'=>'Netherlands',
		'NO'=>'Norway',
		'NP'=>'Nepal',
		'NR'=>'Nauru',
		'NU'=>'Niue',
		'NZ'=>'New Zealand',
		'OM'=>'Oman',
		'PA'=>'Panama',
		'PE'=>'Peru',
		'PF'=>'French Polynesia',
		'PG'=>'Papua New Guinea',
		'PH'=>'Philippines',
		'PK'=>'Pakistan',
		'PL'=>'Poland',
		'PM'=>'St Pierre and Miquelon',
		'PN'=>'Pitcairn',
		'PR'=>'Puerto Rico',
		'PS'=>'Palestine',
		'PT'=>'Portugal',
		'PW'=>'Palau',
		'PY'=>'Paraguay',
		'QA'=>'Qatar',
		'RE'=>'Reunion',
		'RO'=>'Romania',
		'RU'=>'Russia',
		'RW'=>'Rwanda',
		'SA'=>'Saudi Arabia',
		'SB'=>'Solomon Islands',
		'SC'=>'Seychelles',
		'SD'=>'Sudan',
		'SE'=>'Sweden',
		'SG'=>'Singapore',
		'SH'=>'St Helena',
		'SI'=>'Slovenia',
		'SJ'=>'Svalbard and Jan Mayen',
		'SK'=>'Slovakia',
		'SL'=>'Sierra Leone',
		'SM'=>'San Marino',
		'SN'=>'Senegal',
		'SO'=>'Somalia',
		'SR'=>'Suriname',
		'ST'=>'Sao Tome and Principe',
		'SV'=>'El Salvador',
		'SY'=>'Syria',
		'SZ'=>'Swaziland',
		'TC'=>'Turks and Caicos Is',
		'TD'=>'Chad',
		'TF'=>'French Southern and Antarctic Lands',
		'TG'=>'Togo',
		'TH'=>'Thailand',
		'TJ'=>'Tajikistan',
		'TK'=>'Tokelau',
		'TM'=>'Turkmenistan',
		'TN'=>'Tunisia',
		'TO'=>'Tonga',
		'TP'=>'East Timor',
		'TR'=>'Turkey',
		'TT'=>'Trinidad and Tobago',
		'TV'=>'Tuvalu',
		'TW'=>'Taiwan',
		'TZ'=>'Tanzania',
		'UA'=>'Ukraine',
		'UG'=>'Uganda',
		'UM'=>'US minor outlying islands',
		'US'=>'United States',
		'UY'=>'Uruguay',
		'UZ'=>'Uzbekistan',
		'VA'=>'Vatican City',
		'VC'=>'St Vincent',
		'VE'=>'Venezuela',
		'VG'=>'Virgin Islands (UK)',
		'VI'=>'Virgin Islands (US)',
		'VN'=>'Vietnam',
		'VU'=>'Vanuatu',
		'WF'=>'Wallis and Futuna',
		'WS'=>'Samoa (Western)',
		'YE'=>'Yemen',
		'YT'=>'Mayotte',
		'YU'=>'Yugoslavia',
		'ZA'=>'South Africa',
		'ZM'=>'Zambia',
		'ZW'=>'Zimbabwe'
		);
	}
	
	/**
	 * Box di scelta lingua
	 * 
	 * @param boolean $p attiva un tag DIV con ID language
	 * @return string
	 */
	public function choiceLanguage($p=true){

		$GINO = $this->scriptAsset("language.css", "languageCSS", 'css');
		
		if($this->_multi_language == 'yes') {
			if($p) {
				$GINO .= "<section id=\"section_language\">\n";
				$GINO .= '<h1 class="hidden">' . $this->_title . '</h1>';
			}
			
			$query_i = "SELECT label FROM ".$this->_tbl_language." WHERE active='yes' AND code='".$this->_lng_nav."' ORDER BY language";
			$a_i = $this->_db->selectquery($query_i);
			$lngSupport = sizeof($a_i)>0 ? true:false;

			$query = "SELECT label, code FROM ".$this->_tbl_language." WHERE active='yes' ORDER BY language";
			$a = $this->_db->selectquery($query);
			if(sizeof($a) > 0)
			{
				$list = '';
				foreach($a AS $b) {
					if($this->_flag_language) {
						$language = "<img src=\"".$this->_img_www.'/'.$this->_flag_prefix.htmlChars($b['label']).$this->_flag_suffix."\" />";
						$space = " ";
					}
					else {
						$language = htmlChars($b['label']);
						$space = "| ";
					}

					if(($lngSupport && $b['code'] == $this->_lng_nav) || (!$lngSupport && $b['code']== $this->_lng_dft))
						$list .= "$space $language \n";
					else
						$list .= "$space <a href=\"".$this->_home."?lng=$b[code]\">$language</a> \n";
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
	
	/**
	 * Elenco delle lingue dell'applicazione
	 * 
	 * @param string $code codice lingua
	 * @return string
	 */
	private function listLanguage($code){

		$link_insert = "<a href=\"".$this->_home."?evt[".$this->_className."-manageLanguage]&amp;action=".$this->_act_insert."\">".$this->icon('insert', _("nuova lingua"))."</a>";

		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'h1', 'headerLabel'=>$this->_title, 'headerLinks'=>$link_insert));
		
		$query = "SELECT label, language, code, main, active FROM ".$this->_tbl_language." ORDER BY language";
		$a = $this->_db->selectquery($query);
		if(sizeof($a) > 0)
		{
			$htmlList = new htmlList(array("numItems"=>sizeof($a), "separator"=>true));
			$GINO  = $htmlList->start();
			
			foreach($a AS $b)
			{
				if($b['main'] == 'yes') $main = _(" (principale)"); else $main = '';
				if($b['active'] == 'no') $active = _("(non attiva)"); else $active = '';
				
				$label = htmlChars($b['label']);
				$language = htmlChars($b['language']);
				$code_lng = $b['code'];
				
				$link_modify = "<a href=\"".$this->_home."?evt[".$this->_className."-manageLanguage]&amp;code=$b[code]&amp;action=".$this->_act_modify."\">".pub::icon('modify')."</a>";
			
				$selected = ($code==$code_lng)?true:false;				

				$GINO .= $htmlList->item($language.' - '.$code_lng.$main." ".$active, $link_modify, $selected, true);
			}
			$GINO .= $htmlList->end();
		}
		
		$htmlsection->content = $GINO;

		return $htmlsection->render();
	}
	
	/**
	 * Interfaccia amministrazione per la gestione delle lingue
	 * 
	 * @see $_access_2
	 * @return string
	 */
	public function manageLanguage(){

		$this->accessType($this->_access_2);
		
		$block = cleanVar($_GET, 'block', 'string', '');

		$htmltab = new htmlTab(array("linkPosition"=>'right', "title"=>$this->_title));	
		$link_options = "<a href=\"".$this->_home."?evt[$this->_className-manageLanguage]&block=options\">"._("Opzioni")."</a>";
		$link_dft = "<a href=\"".$this->_home."?evt[".$this->_className."-manageLanguage]\">"._("Gestione")."</a>";
		$sel_link = $link_dft;

		if($block=='options') {
			$GINO = sysfunc::manageOptions(null, $this->_className);
			$sel_link = $link_options;
		}
		else {
			$code = cleanVar($_GET, 'code', 'string', '');
			$action = cleanVar($_GET, 'action', 'string', '');
		
			$form = '';
		
			if($action == $this->_act_insert OR $action == $this->_act_modify)
			{
				$form .= $this->formLanguage($code);
			}
			else 
			{
				$form .= $this->info();
			}
		
			$GINO = "<div class=\"vertical_1\">\n";
			$GINO .= $this->listLanguage($code);
			$GINO .= "</div>\n";
		
			$GINO .= "<div class=\"vertical_2\">\n";
			$GINO .= $form;
			$GINO .= "</div>\n";
		
			$GINO .= "<div class=\"null\"></div>";
		}	

		$htmltab->navigationLinks = array($link_options, $link_dft);
		$htmltab->selectedLink = $sel_link;
		$htmltab->htmlContent = $GINO;
		return $htmltab->render();
	}
	
	private function info(){
		
		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'h1', 'headerLabel'=>_("Informazioni")));
		$buffer = "<p>"._("L'inserimento dei contenuti avviene nella lingua principale.")."</p>\n";
		$buffer .= "<p>"._("In assenza di traduzioni vengono visualizzati sempre i contenuti nella lingua principale.")."</p>\n";
		
		$htmlsection->content = $buffer;

		return $htmlsection->render();
	}

	/**
	 * Form di inserimento e modifica di una lingua
	 * 
	 * @param string $code codice lingua
	 * @return string
	 */
	private function formLanguage($code){
		
		$gform = new Form('gform', 'post', true);
		$gform->load('dataform');
		
		if(!empty($code))
		{
			$query = "SELECT label, language, main, active FROM ".$this->_tbl_language." WHERE code='$code'";
			$a = $this->_db->selectquery($query);
			if(sizeof($a) > 0)
			{
				foreach($a AS $b)
				{
					$label = htmlInput($b['label']);
					$language = htmlInput($b['language']);
					$main = htmlInput($b['main']);
					$active = htmlInput($b['active']);
				}
				$arrayCode = explode('_', $code);
				$code1 = $arrayCode[0];
				$code2 = $arrayCode[1];
			}
			
			$title = _("Modifica")." '$language'";
			$submit = _("modifica");
		}
		else	// insert new language
		{
			$label = $gform->retvar('label', '');
			$language = $gform->retvar('lang', '');
			$main = $gform->retvar('main', '');
			$active = $gform->retvar('active', '');
			$code1 = $gform->retvar('lang_codes', '');
			$code2 = $gform->retvar('country_codes', '');
			
			$title = _("Nuova lingua");
			$submit = _("inserisci");
		}
		$required = 'lang,label,lang_codes,country_codes,main,active';
		
		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'h1', 'headerLabel'=>$title));

		$required = '';
		$GINO = $gform->form($this->_home."?evt[".$this->_className."-actionLanguage]", '', $required);
		$GINO .= $gform->hidden('main2', 'main2');
		$GINO .= $gform->hidden('code', $code);

		$GINO .= $gform->cinput('lang', 'text', $language, _("Nome"), array("required"=>true, "size"=>40, "maxlength"=>200));
		$GINO .= $gform->cinput('label', 'text', $label, _("Etichetta"), array("required"=>true, "size"=>40, "maxlength"=>200));
		$GINO .= $gform->cselect('lang_codes', $code1, $this->_language_codes, _("Lingua"), array("required"=>true));
		$GINO .= $gform->cselect('country_codes', $code2, $this->_country_codes, _("Stato"), array("required"=>true));
		$GINO .= $gform->cradio('main', $main, array("yes"=>_("si"),"no"=>_("no")), 'no', _("Principale"), array("required"=>true));
		$GINO .= $gform->cradio('active', $active, array("yes"=>_("si"),"no"=>_("no")), 'no', _("Attiva"), array("required"=>true));
		$GINO .= $gform->cinput('submit_action', 'submit', $submit, '', array("classField"=>"submit"));
		$GINO .= $gform->cform();

		$htmlsection->content = $GINO;

		return $htmlsection->render();
	}
	
	/**
	 * Inserimento e modifica di una lingua
	 * 
	 * @see $_access_2
	 */
	public function actionLanguage(){
	
		$this->accessType($this->_access_2);
		
		$gform = new Form('gform', 'post', true);
		$gform->save('dataform');
		$req_error = $gform->arequired();
		
		$code = cleanVar($_POST, 'code', 'string', '');
		$label = cleanVar($_POST, 'label', 'string', '');
		$language = cleanVar($_POST, 'lang', 'string', '');
		$lang_codes = cleanVar($_POST, 'lang_codes', 'string', '');
		$country_codes = cleanVar($_POST, 'country_codes', 'string', '');
		$main = cleanVar($_POST, 'main', 'string', '');
		$main2 = cleanVar($_POST, 'main2', 'string', '');
		$active = cleanVar($_POST, 'active', 'string', '');
		
		$link_error = $this->_home."?evt[$this->_className-manageLanguage]&code=$code";

		if($req_error > 0) 
			exit(error::errorMessage(array('error'=>1), $link_error));
		
		$inputCode = $lang_codes."_".$country_codes;
		
		// check existence
		$query = "SELECT code FROM ".$this->_tbl_language." WHERE code!='$code'";
		$a = $this->_db->selectquery($query);
		if(sizeof($a) > 0)
		{
			$count = 0;
			foreach($a AS $b)
			{
				if($b['code'] == $inputCode)
				$count++;
			}
			if($count > 0)
				exit(error::errorMessage(array('error'=>12), $link_error));
		}
		
		if(!empty($code))
		{
			if($main == 'yes' AND $main2 == 'no')
			{
				$query = "UPDATE ".$this->_tbl_language." SET main='no' WHERE main='yes'";
				$this->_db->actionquery($query);
			}
			
			$query = "UPDATE ".$this->_tbl_language." SET label='$label', language='$language', code='$inputCode', main='$main', active='$active' WHERE code='$code'";
			$this->_db->actionquery($query);
		}
		else	// insert
		{
			$query = "INSERT INTO ".$this->_tbl_language." (label, language, code, main, active) VALUES ('$label', '$language', '$inputCode', 'no', '$active')";
			$this->_db->actionquery($query);
		}
		
		EvtHandler::HttpCall($this->_home, $this->_className.'-manageLanguage', '');
	}
	
	/**
	 * Interfaccia che apre o chiude il form per l'inserimento e la modifica delle traduzioni
	 * 
	 * Viene richiamato nei metodi della classe Form: cinput(), ctextarea(), fcktextarea()
	 * 
	 * @see formTranslation()
	 * @see gino-min.js
	 * @see $_access_user
	 * @param string $type tipologia di input (input, textarea, fckeditor)
	 * @param string $tbl nome della tabella con il campo da tradurre
	 * @param string $field nome del campo con il testo da tradurre
	 * @param integer $id_value valore dell'ID del record di riferimento per la traduzione
	 * @param integer $width lunghezza del tag input o numero di colonne (textarea)
	 * @param string $fck_toolbar nome della toolbar dell'editor html
	 * @return string
	 */
	public function formFieldTranslation($type, $tbl, $field, $id_value, $width, $fck_toolbar='') {
	 	
		$this->accessType($this->_access_user);

	 	if(empty($id_name)) $id_name = 'id';

	 	$GINO = "<div class=\"form_translation\">\n";
	 	
	 	$query = "SELECT label, code FROM ".$this->_tbl_language." WHERE main='no' AND active='yes'";
	 	$a = $this->_db->selectquery($query);
		if(sizeof($a) > 0)
		{
			$first = true;
			foreach($a AS $b) {
				$label = htmlChars($b['label']);
				$code = htmlChars($b['code']);
				$GINO .= "<span class=\"trnsl_lng link\" onclick=\"prepareTrlForm('$code', $(this), '$tbl', '$field', '$type', '$id_value', '$width', '$fck_toolbar', '".$this->_home."')\">".$label."</span> &#160;";
				$first = false; 
			}	
			$GINO .= " &nbsp; <span id=\"".$tbl.$field."\"></span>\n";
		}
		
	 	$GINO .= "</div>\n";
	 	
	 	return $GINO;
	}
	 
	/**
	 * Form per l'inserimento e la modifica delle traduzioni
	 * 
	 * @see $_access_user
	 * @return print
	 * 
	 * Il metodo viene richiamato da una request ajax avviata dalla funzione javascript prepareTrlForm().
	 */
	public function formTranslation() {
	 	
		$this->accessType($this->_access_user);

	 	$lng_code = cleanVar($_POST, 'lng_code', 'string', '');
	 	$tbl = cleanVar($_POST, 'tbl', 'string', '');
	 	$field = cleanVar($_POST, 'field', 'string', '');
	 	$type = cleanVar($_POST, 'type', 'string', '');
	 	$id_value = cleanVar($_POST, 'id_value', 'int', '');
	 	$width = cleanVar($_POST, 'width', 'string', '');
	 	$fck_toolbar = cleanVar($_POST, 'fck_toolbar', 'string', '');
	 	
	 	$myform = new Form('gform', 'post', true);
	 	
	 	$query = "SELECT text FROM ".$this->_tbl_translation." WHERE tbl_id_value='$id_value' AND tbl='$tbl' AND field='$field' AND language='$lng_code'";
	 	$a = $this->_db->selectquery($query);
	 	if(sizeof($a) > 0)
		{
			foreach($a AS $b) {
				if($type == $this->_input_field || $type== $this->_textarea_field) $text = htmlInput($b['text']);
				elseif($type == $this->_fckeditor_field) $text = htmlInputEditor($b['text']);
			}
			$action = $this->_act_modify;
		}
		else {
			$text = '';
			$action = $this->_act_insert;
		}
	 	
	 	$GINO = "<div style=\"margin-top:10px;\">";
	 	
		$url = $this->_home."?evt[".$this->_className."-actionTranslation]";
		$onclick = "ajaxRequest('post', '".$url."', 'type=$type&tbl=$tbl&field=$field&id_value=$id_value&text='+$('trnsl_".$field."').getProperty('value')+'&lng_code=$lng_code&action=$action', '".$tbl.$field."', {'script':true})";
	 	
	 	if($type == $this->_input_field) {
	 		$GINO .= $myform->input('trnsl_'.$field, 'text', $text, array("size"=>$width, "id"=>'trnsl_'.$field));
	 	}
	 	elseif($type == $this->_textarea_field) {
			$GINO .= $myform->textarea('trnsl_'.$field, $text, array("cols"=>$width, "rows"=>4, "id"=>'trnsl_'.$field));
	 	}
	 	elseif($type == $this->_fckeditor_field) {
			$onclick = "ajaxRequest('post', '".$url."', 'type=$type&tbl=$tbl&field=$field&id_value=$id_value&text='+escape(CKEDITOR.instances['trnsl_".$field."'].getData())+'&lng_code=$lng_code&action=$action', '".$tbl.$field."', {'script':true})";
			$GINO .= $myform->textarea('trnsl_'.$field, $text, array("cols"=>40, "rows"=>4, "id"=>'trnsl_'.$field));
	 	}
	 	$onclick = "onclick=\"$onclick\"";
		$GINO .= "<p>".$myform->input('submit', 'button', _("applica"), array("classField"=>"submit", "js"=>$onclick))."</p>";

		$GINO .= "</div>";

	 	echo $GINO;
	 	exit();
	 }
	 
	/**
	 * Inserimento e la modifica delle traduzioni
	 * 
	 * @see $_access_2
	 */
	public function actionTranslation() {
	 	
	 	$this->accessType($this->_access_2);
	 	
		$action = cleanVar($_POST, 'action', 'string', '');
		$type = cleanVar($_POST, 'type', 'string', '');
		if($type == $this->_input_field || $type == $this->_textarea_field) $text = cleanVar($_POST, 'text', 'string', '');
		elseif($type == $this->_fckeditor_field) {
			$text = cleanVarEditor($_POST, 'text', '');
			$text = preg_replace("/%u([0-9a-f]{3,4})/i","&#x\\1;",urldecode($text));
			$text = html_entity_decode($text,null,'UTF-8');
		}
	 	$lng_code = cleanVar($_POST, 'lng_code', 'string', '');
	 	$tbl = cleanVar($_POST, 'tbl', 'string', '');
	 	$field = cleanVar($_POST, 'field', 'string', '');
	 	$id_value = cleanVar($_POST, 'id_value', 'int', '');
	 	
	 	if($action == $this->_act_insert) {
	 		$query = "INSERT INTO ".$this->_tbl_translation." (tbl_id_value, tbl, field, language, text) VALUES ($id_value, '$tbl', '$field', '$lng_code', '$text')";
	 	}
	 	elseif($action == $this->_act_modify) {
	 		$query = "UPDATE ".$this->_tbl_translation." SET text='$text' WHERE tbl_id_value='$id_value' AND tbl='$tbl' AND field='$field' AND language='$lng_code'";
	 	}
	 	$this->_db->actionquery($query);
	 	
	 	echo "<script>
			$('trnsl_container').dispose();
			$('".$tbl.$field."').getParent().getChildren('span[class~=trnsl_lng_sel]').removeClass('trnsl_lng_sel');
			CKEDITOR.remove(CKEDITOR.instances['trnsl_".$field."']);
		</script>";
	 	exit();
	}
	
	/**
	 * Elimina una traduzione
	 * 
	 * @param string $tbl nome della tabella con il campo da tradurre
	 * @param integer $tbl_id valore dell'ID del record di riferimento per la traduzione
	 * @return boolean
	 */
	public static function deleteTranslations($tbl, $tbl_id) {
	 	
		$db = db::instance();
		$query = $tbl_id == 'all'
			? "DELETE FROM ".self::$tbl_translation." WHERE tbl='$tbl'"
			: "DELETE FROM ".self::$tbl_translation." WHERE tbl='$tbl' AND tbl_id_value='$tbl_id'";
		$result = $db->actionquery($query);
	 	if($result) return true;
	 	else return false;
	}
	
	/**
	 * Visualizza tutte le traduzioni di un elemento
	 * 
	 * Viene richiamato nei metodi della classe Form: cinput(), ctextarea(), fcktextarea()
	 * 
	 * @see $_access_user
	 * @param string $tbl nome della tabella con il campo da tradurre
	 * @param string $field nome del campo con il testo da tradurre
	 * @param integer $id_value valore dell'ID del record di riferimento per la traduzione
	 * @return string
	 */
	public function viewFieldTranslation($tbl, $field, $id_value) {
	 	
		$this->accessType($this->_access_user);

		$text = '';
	 	
	 	$query = "SELECT text, language FROM ".$this->_tbl_translation." WHERE tbl_id_value='$id_value' AND tbl='$tbl' AND field='$field' ORDER BY language";
	 	$a = $this->_db->selectquery($query);
	 	if(sizeof($a) > 0)
		{
			foreach($a AS $b) {
				$text_field = htmlChars($b['text']);
				$language = htmlChars($b['language']);
				$text .= $this->_db->getFieldFromId($this->_tbl_language, 'label', 'code', $language).": ".$text_field."<br/>";
			}
		}
	 	
	 	return $text;
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
