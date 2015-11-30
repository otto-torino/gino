<?php
/**
 * @file class.Lang.php
 * @brief Contiene la classe definizione ed implementazione della classe Gino.App.Language.Lang
 *
 * @copyright 2005-2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

namespace Gino\App\Language;

use \Gino\Registry;

/**
 * @brief Classe di tipo Gino.Model che rappresenta una lingua installata sul sistema
 *
 * @copyright 2005-2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class Lang extends \Gino\Model {

    public static $table = "language";
    public static $columns;

    /**
     * @brief Costruttore
     * @param int $id id lingua
     * @return istanza di Gino.App.Language.Lang
     */
    function __construct($id = null) {

        $this->_model_label = _('Lingua');

        $this->_tbl_data = self::$table;
        
        parent::__construct($id);
    }

    /**
     * @brief Rappresentazione a stringa dell'oggetto
     * @return nome lingua
     */
    function __toString() {
        return $this->language;
    }

    /**
     * @brief Codice completo lingua es it_IT
     * @return codice lingua
     */
    public function code() {
        return $this->language_code.'_'.$this->country_code;
    }
    
    /**
     * Struttura dei campi della tabella di un modello
     * 
     * @return array
     */
    public static function columns() {
    
    	$columns['id'] = new \Gino\IntegerField(array(
    		'name'=>'id',
    		'primary_key'=>true,
    		'auto_increment'=>true,
    	));
    	$columns['label'] = new \Gino\CharField(array(
    		'name'=>'label',
    		'label' => _('etichetta'),
    		'required'=>true,
    		'max_lenght'=>10,
    		'trnsl' => false
    	));
    	$columns['language'] = new \Gino\CharField(array(
    		'name'=>'language',
    		'label' =>  _('lingua'),
    		'required'=>true,
    		'max_lenght'=>50,
    		'trnsl' => false
    	));
    	$columns['language_code'] = new \Gino\EnumField(array(
    		'name'=>'language_code',
    		'label' => _('codice lingua'),
    		'widget'=>'select',
    		'required'=>true,
    		'max_lenght'=>5,
    		'choice'=>self::languageCodes(),
    	));
    	$columns['country_code'] = new \Gino\EnumField(array(
    		'name'=>'country_code',
    		'label' => _('codice stato'),
    		'widget'=>'select',
    		'required'=>true,
    		'max_lenght'=>5,
    		'choice'=>self::countryCodes(),
    	));
    	$columns['active'] = new \Gino\BooleanField(array(
            'name'=>'active',
    		'label' => _('attiva'),
            'required'=>true,
            'default'=>0,
        ));
    	return $columns;
    }

    /**
     * @brief Lingua di default
     *
     * @description La lingua di default viene impostata nelle Impostazioni di sistema @ref Gino.App.Sysconf
     * @return string (codice della lingua)
     */
    public static function getMainLang() {

        $registry = Registry::instance();
        $db = $registry->db;
        
        $rows = $db->select('language_code, country_code', self::$table, "id='".$registry->sysconf->dft_language."'");	// prima della nuova branch: $registry->sysconf->dft_language->id
        if($rows && count($rows))
        {
            return $rows[0]['language_code'] . '_' . $rows[0]['country_code'];
        }
        else return null;
    }

    /**
     * @brief Codici lingua
     *
     * @return array di codici lingua codice => lingua
     */
    private static function languageCodes(){

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
     * @brief Codici stato
     *
     * @return array di codici stato codice => stato
     */
    private static function countryCodes(){

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
}

Lang::$columns=Lang::columns();
