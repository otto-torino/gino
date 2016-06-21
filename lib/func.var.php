<?php
/**
 * @file func.var.php
 * @brief Racchiude le librerie per il trattamento di stringhe e dei valori da e per un input/database
 *
 * @copyright 2005-2016 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

/**
 * @brief Converte l'encoding di un valore preso da un campo di un database (non UTF-8) nella codifica UTF-8
 *
 * @param string $value valore da convertire
 * @return valore convertito
 */
function convertToHtml($value)
{
    $value = mb_detect_encoding($value, mb_detect_order(), true) === 'UTF-8' ? $value : mb_convert_encoding($value, 'UTF-8');
    return $value;
}

/**
 * @brief Converte l'encoding di un valore da html (es UTF-8) a un encoding valido per il database
 *
 * @param string $value valore da convertire
 * @param string $character_set set di caratteri del database
 *   - @a CP1252, per SQL Server
 * @return valore convertito
 */
function convertToDatabase($value, $character_set=null)
{
    if(!is_null($character_set))
        $value = mb_convert_encoding($value, $character_set, mb_detect_encoding($value, mb_detect_order(), true));

    return $value;
}

/**
 * @brief Rimuove gli attributi javascript insicuri nei tag html
 *
 * @see remove_attributes()
 * @param string $text testo
 * @param boolean $strip_js rimuove gli attributi javascript (default true)
 * @param boolean $strip_attributes rimuove alcune proprietà quando risultano vuote (default true)
 * @return testo ripulito
 */
function strip_tags_attributes($text, $strip_js=true, $strip_attributes=true)
{
    if($strip_js)
    {
    	$js_attributes = array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onbounce', 'oncellchange', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavaible', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragdrop', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterupdate', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onlayoutcomplete', 'onlosecapture', 'onmoveout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onunload');
    	$text = preg_replace('/\s(' . implode('|', $js_attributes) . ').*?([\s\>])/', '\\2', preg_replace('/<(.*?)>/ie', "'<' . preg_replace(array('/javascript:[^\"\']*/i', '/(" . implode('|', $js_attributes) . ")[ \\t\\n]*=[ \\t\\n]*[\"\'][^\"\']*[\"\']/i', '/\s+/'), array('', '', ' '), stripslashes('\\1')) . '>'", $text));
    }
	if($strip_attributes) {
		$text = str_replace(" class=\"\"", '', $text);
	}

    return $text;
}

/**
 * @brief Rimuove i tag indicati
 * @description Funziona in modo opposto alla funzione strip_tags() con la quale è possibile indicare i tag da preservare.
 * 
 * @param string $text testo
 * @param string $tags stringa con i tag da rimuovere, ad esempio "<a><p><quote>"
 * @param boolean $stripContent rimuove anche il testo contenuto tra l'apertura e la chiusura del tag (default false)
 * @return stringa ripulita
 */
function strip_selected_tags($text, $tags='', $stripContent=false)
{
    preg_match_all("/<([^>]+)>/i", $tags, $allTags, PREG_PATTERN_ORDER);
    foreach ($allTags[1] as $tag){
        if($stripContent){
            $text = preg_replace("/<".$tag."[^>]*>.*<\/".$tag.">/iU", "", $text);
        }
        $text = preg_replace("/<\/?".$tag."[^>]*>/iU", "", $text);
    }
    return $text;
}

/**
 * @brief Rimuove i tag di tipo embedded (unitamente al testo incluso)
 *
 * @param string $text testo
 * @return string
 */
function strip_embedded_tags($text)
{
    $text = preg_replace(
        array(
            '@<head[^>]*?>.*?</head>@siu',
            '@<style[^>]*?>.*?</style>@siu',
            '@<script[^>]*?.*?</script>@siu',
            '@<object[^>]*?.*?</object>@siu',
            '@<embed[^>]*?.*?</embed>@siu',
            '@<applet[^>]*?.*?</applet>@siu',
            '@<noframes[^>]*?.*?</noframes>@siu',
            '@<noscript[^>]*?.*?</noscript>@siu',
            '@<noembed[^>]*?.*?</noembed>@siu'
        ),
        array(
            ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' '
        ),
        $text);

    return $text;
}

// 1. from HTML Form to DB

/**
 * @brief Clean plain text
 * @description Rimuove tutti i tag html
 * 
 * @param string $value value taken from the request ($_POST[input_name])
 * @param array $options array associativo di opzioni
 *   - @b escape (boolean): aggiunge le sequenze di escape ai caratteri speciali in una stringa per l'uso in una istruzione SQL (@see Gino.Db::escapeString())
 * @return string or null
 */
function clean_text($value, $options=array()) {
	
	$escape = gOpt('escape', $options, true);
	
	if($value === null) {
		return null;
	}
	
	$value = trim($value);
	
	settype($value, 'string');
	
	$value = strip_tags($value);
	
	if($escape) {
		$db = Db::instance();
		$value = $db->escapeString($value);
	}
	
	return $value;
}

/**
 * @brief Clean html
 * @description Se viene impostata l'opzione @a strip_tags, vengono rimossi tutti i tag html dal testo a parte quelli presenti nell'opzione.
 * 
 * @param string $value value taken from the request ($_POST[input_name])
 * @param array $options array associativo di opzioni
 *   - @b escape (boolean): aggiunge le sequenze di escape ai caratteri speciali in una stringa per l'uso in una istruzione SQL (@see Gino.Db::escapeString()); default true
 *   - @b strip_tags (string): elenco dei tag da rimuovere (ad esempio '<p><a><quote>')
 *   - @b strip_embedded (boolean): rimuove tutti i tag html di tipo embedded (default false)
 *   - @b allowable_tags (string): elenco dei tag da non rimuovere (ad esempio '<p><a>')
 * @return string or null
 */
function clean_html($value, $options=array()) {

	$escape = gOpt('escape', $options, true);
	$strip_tags = gOpt('strip_tags', $options, null);
	$strip_embedded = gOpt('strip_embedded', $options, false);
	$allowable_tags = gOpt('allowable_tags', $options, null);
	
	if($value === null) {
		return null;
	}
	
	$value = trim($value);
	
	settype($value, 'string');
	
	if($allowable_tags) {
		$value = strip_tags($value, $allowable_tags);
	}
	else {
		if($strip_tags) {
			$value = strip_selected_tags($value, $strip_tags, false);
		}
		
		if($strip_embedded)
		{
			$value = strip_embedded_tags($value);
			$value = strip_tags_attributes($value);
		}
	}
	
	$value = html_entity_decode($value, ENT_QUOTES, 'UTF-8');
	
	if($escape) {
		$db = Db::instance();
		$value = $db->escapeString($value);
	}
	
	return $value;
}

/**
 * @brief Clean boolean
 * 
 * @param int (0|1) $value value taken from the request ($_POST[input_name])
 * @return NULL or bool
 */
function clean_bool($value) {

	if($value === null) {
		return null;
	}
	settype($value, 'bool');

	return $value;
}

/**
 * @brief Clean integer
 * 
 * @param int $value value taken from the request ($_POST[input_name])
 * @return NULL or integer
 */
function clean_int($value) {

	if(is_null($value) or $value == '') {
		return null;
	}
	settype($value, 'int');
	
	return $value;
}

/**
 * @brief Clean float
 * 
 * @param float $value value taken from the request ($_POST[input_name])
 * @return NULL or float
 */
function clean_float($value) {

	if(is_null($value) or $value == '') {
		return null;
	}
	
	//$larr = localeconv();
	//$value = str_replace($larr['decimal_point'], '.', $value);
	$value = str_replace(',', '.', $value);
	
	settype($value, 'float');

	return $value;
}

/**
 * @brief Clean date
 * 
 * @see clean_text()
 * @param string $value value taken from the request ($_POST[input_name])
 * @param array $options array associativo di opzioni
 *   - opzioni del metodo Gino.clean_text()
 *   - @b typeofdate (string): tipo di data; valori validi: date (default), datetime
 *   - @b separator (string): separatore utilizzato nella data (default /)
 * @return NULL or string
 */
function clean_date($value, $options=array()) {

	$typeofdate = gOpt('typeofdate', $options, 'date');
	$separator = gOpt('separator', $options, '/');
	
	$value = clean_text($value, $options);
	
	if($typeofdate == 'date')
	{
		$value = dateToDbDate($value, $separator);
	}
	elseif($typeofdate == 'datetime')
	{
		$spilt = explode(" ", $value);
		$date = dateToDbDate($spilt[0], $separator);
		
		if(isset($split[1])) {
			$time = timeToDbTime($split[1]);
		} else {
			$time = '00:00:00';
		}
		
		$value = $date.' '.$time;
	}
	else
	{
		return null;
	}

	return $value;
}

/**
 * @brief Clean time
 * 
 * @see clean_text()
 * @param string $value value taken from the request ($_POST[input_name])
 * @param array $options array associativo di opzioni
 *   - opzioni del metodo Gino.clean_text()
 * @return NULL or string
 */
function clean_time($value, $options=array()) {

	$value = clean_text($value, $options);
	
	if($value === null) {
		return null;
	} else {
		return timeToDbTime($value);
	}
}

/**
 * @brief Clean email
 * 
 * @see clean_text()
 * @param string $value value taken from the request ($_POST[input_name])
 * @param array $options array associativo di opzioni
 *   - opzioni del metodo Gino.clean_text()
 * @return NULL or string
 */
function clean_email($value, $options=array()) {

	$value = clean_text($value, $options);
	
	if(is_null($value))
	{
		return null;
	}
	elseif(is_string($value))
	{
		/*$check = \Gino\checkEmail($value, true);
		 if(!$check) {
		 	throw new \Exception(_("Formato dell'email non valido"));
		 }
		 return $value;*/
		
		$value = \filter_var($value, FILTER_VALIDATE_EMAIL);
		
		if($value === false) {
			throw new \Exception(_("Formato dell'email non valido"));
		}
		else {
			return $value;
		}
	}
	else {
		throw new \Exception(_("Valore non valido per la funzione clean_email"));
	}
}

/**
 * @brief Clean array
 *
 * @param array $value value taken from the request ($_POST[input_name])
 * @param array $options array associativo di opzioni
 *   - opzioni del metodo clean_text()
 *   - @b datatype (string): tipo di dato degli elementi dell'array; valori validi: int (default), string, float, bool
 *   - @b asforminput (boolean): indica se ritornare gli elementi in un array (default true) o separati da virgola in formato stringa
 * @return array, string or null
 */
function clean_array($value, $options=array()) {

	$datatype = gOpt('datatype', $options, 'int');
	$asforminput = gOpt('asforminput', $options, true);
	
	if($value === null) {
		return null;
	}
	
	if(is_array($value))
	{
		$items = array();
		foreach($value AS $item)
		{
			if($datatype == 'int') {
				$item = clean_int($item);
			} elseif($datatype == 'string') {
				$item = clean_text($item, $options);
			} elseif($datatype == 'float') {
				$item = clean_float($item);
			} elseif($datatype == 'bool') {
				$item = clean_bool($item);
			} else {
				$item = null;
			}
			
			$items[] = $item;
		}
		
		if($asforminput) {
			return $items;
		} else {
			return implode(',', $items);
		}
	}
	else {
		return null;
	}
}

/**
 * @brief Modifica il valore presente in un campo del form per inserirlo nel database
 * @description È stato mantenuto per mantenere la compatibilità con le versioni precedenti
 * 
 * @param array $method parametri della request ($_GET, $_POST, $_REQUEST)
 * @param string $name nome della variabile
 * @param string $type tipo di variabile (bool,int,float,string,array)
 * @param string $strip_tags stringa con i tag da rimuovere, ad esempio "<a><p><quote>"
 * @param array $options array associativo di opzioni
 * @return testo ripulito
 */
function cleanVar($method, $name, $type, $strip_tags = '', $options = array())
{
	$options['strip_tags'] = $strip_tags;
	
	if(array_key_exists($name, $method)) {
		$value = $method[$name];
	}
	else {
		$value = null;
	}
	
	if($type == 'int')
	{
		return clean_int($value);
	}
	elseif($type == 'string')
	{
		return clean_text($value, $options);
	}
	elseif($type == 'array')
	{
		return clean_array($value, $options);
	}
	elseif($type == 'bool')
	{
		return clean_bool($value);
	}
	elseif($type == 'float')
	{
		return clean_float($value);
	}
	else
	{
		return null;
	}
}

/**
 * @brief Conversione dei dati Unicode $_GET/$_POST (generati dalla funzione javascript escape()) in UTF8 per il processo server-side
 *
 * @param string $value
 * @return stringa decodificata
 */
function utf8_urldecode($value) {

    $value = preg_replace("/%u([0-9a-f]{3,4})/i","&#x\\1;", urldecode($value));
    $value = html_entity_decode($value, ENT_QUOTES, 'UTF-8');	// prev version: ENT_QUOTES => null

    return $value;
}

/**
 * @brief Modifica il valore presente in un campo testo del form per inserirlo nel database
 * @description Testo di tipo "codice", nel quale non viene rimosso il codice html
 * 
 * @param string $method metodo utilizzato (GET, POST, REQUEST)
 * @param string $name nome della variabile
 * @param array $options
 *   array associativo di opzioni
 *   - @b width (string): sovrascrive la larghezza di visualizzazione di una immagine
 *   - @b height (string): sovrascrive l'altezza di visualizzazione di una immagine
 * @return testo modificato
 */
function codeToDB($method, $name, $options=array()){

	if(isset($method[$name]) AND !empty($method[$name]))
	{
		$value = $method[$name];
		if(array_key_exists('width', $options)) {
			$value = preg_replace("#width=\"(.*?)\"#", "width=\"".$options['width']."\"", $value);
		}
		if(array_key_exists('height', $options)){
			$value = preg_replace("#height=\"(.*?)\"#", "height=\"".$options['height']."\"", $value);
		}
		
		$db = Db::instance();
		$value = $db->escapeString($value);
	}
	else $value = null;

	return $value;
}

// End 1

// 2. from DB to HTML

/**
 * @brief Modifica il valore di un campo di tipo testo per visualizzarlo in HTML
 *
 * @param string $string testo
 * @param string $id codice che raggruppa un insieme di immagini da visualizzare con le librerie slimbox
 * @param array $options
 *   array associativo di opzioni
 *   - @b newline (boolean): inserisce dei tag BR prima di ogni nuova linea in una stringa (sostituisce i caratteri newline)
 * @return stringa modificata
 */
function htmlChars($string, $id='', $options=array())
{
    $newline = array_key_exists('newline', $options) ? $options['newline'] : false;

    $string = convertToHtml($string);

    $string = trim($string);
    $string = stripslashes($string);

    $string = str_replace ('&euro;', '€', $string);
    $string = str_replace ('&bull;', '•', $string);
    $string = str_replace ('&', '&amp;', $string);	// CSS2
    $string = str_replace ('\'', '&#039;', $string);

    if($newline) {
    	$string = nl2br($string);
    }

    if($id) $string = slimboxReplace($string, $id);
    return $string;
}

/**
 * @brief Modifica il valore di un campo testuale di tipo "codice" per visualizzarlo in HTML
 * @description Testo di tipo "codice", ovvero inserito con la funzione @a codeToDB(). Crea un blocco che racchiude il codice.
 *
 * @see codeToDB()
 * @param string $string
 * @return testo modificato
 */
function preCodeParser($string) {

    $final_string = '';
    $reg_expr = "/(.*?)((\[\/?precode\])|$)/is";
    preg_match_all($reg_expr, $string, $lines, PREG_SET_ORDER);

    $open_tag = "/\[precode\]/i";
    $close_tag = "/\[\/precode\]/i";
    foreach($lines as $line) {
        if(preg_match($open_tag, $line[2])) {  //line conteins an open tag
            $part = $line[1]."<pre class=\"code\">";
        }
        elseif(preg_match($close_tag, $line[2])) {
            $part = htmlspecialchars($line[1])."</pre>";
            $part = preg_replace("#\[br\]#", "<br/>", $part);
        }
        elseif(!empty($line[1])) $part = $line[1];
        else $part = '';

        $final_string .= $part;
    }

    return $final_string;
}

/**
 * @brief Modifica il valore di un campo testuale di tipo "codice" per visualizzarlo in HTML
 * @description Testo di tipo "codice", ovvero inserito con la funzione @a codeToDB(). Crea un blocco che racchiude il codice e ne evidenzia le righe.
 *
 * @see codeToDB()
 * @see preCodeParser()
 * @param string $string
 * @param string $id codice che raggruppa un insieme di immagini da visualizzare con le librerie slimbox
 * @return testo modificato
 */
function codeParser($string, $id='') {

    $string = trim($string);

    $string = str_replace ('&euro;', '€', $string);

    if($id) $string = slimboxReplace($string, $id);

    $string = preCodeParser($string);

    $final_string = '';
    $reg_expr = "/(.*?)((\[\/?code\])|$)/is";
    preg_match_all($reg_expr, $string, $lines, PREG_SET_ORDER);

    $open_tag = "/\[code\]/i";
    $close_tag = "/\[\/code\]/i";
    foreach($lines as $line) {
        if(preg_match($open_tag, $line[2])) {  //line conteins an open tag
            $part = $line[1]."<div class=\"codelist\"><ol>";
        }
        elseif(preg_match($close_tag, $line[2])) {
            $text = htmlspecialchars($line[1]);
            $text_a = explode("\n", $text);
            $part = '';
            $odd=true;
            foreach($text_a as $t) {
                $li_class = ($odd)?"li_odd":"li_even";
                $t = preg_replace("/ /", "&nbsp;", $t);
                $t = preg_replace("/\t/", "&nbsp;&nbsp;&nbsp;&nbsp;", $t);
                $part .= "<li class=\"$li_class\">$t</li>";
                $odd = !$odd;
            }
            $part .= "</ol></div>";
        }
        elseif(!empty($line[1])) $part = $line[1];
        else $part = '';
        $part = preg_replace("/:/", "&#58;", $part);
        $part = nl2br($part);
        $final_string .= $part;
    }

    return $final_string;
}

/**
 * @brief Modifica il valore di un campo di tipo testo per attivare le librerie slimbox
 * 
 * @param string $string testo
 * @param string $id codice che raggruppa un insieme di immagini da visualizzare con le librerie slimbox
 * @return testo modificato
 */
function slimboxReplace($string, $id) {

    $rel = "lightbox-$id";

    $pattern = "/(<img)[^(\/>)]+(class=\"[\d\w\s\-]*lightbox[\d\w\s\-]*\"){1}[^(\/>)]+(src=\")([^\"]+)[^(\/>)]+\/>/i";
    $replacement = "<a rel=\"".$rel."\" href=\"$4\" >$0</a>";

    $pattern2 = "/(<img)[^(\/>)]+(src=\")(^\"]+)[^(\/>)]+(class=\"[\d\w\s\-]*lightbox[\d\w\s\-]*\"){1}[^(\/>)]+\/>/i";
    $replacement2 = "<a rel=\"".$rel."\" href=\"$3\" >$0</a>";

    $string = preg_replace_callback($pattern, function ($matches) use ($rel) {
    	$dirname = dirname($matches[4]);
    	$filename = urlencode(basename($matches[4]));
    	return "<a rel=\"".$rel."\" href=\"".$dirname.'/'.$filename."\" >".$matches[0]."</a>";
    }, $string);
    
    $string = preg_replace_callback($pattern2, function ($matches) use ($rel) {
    	$dirname = dirname($matches[3]);
    	$filename = urlencode(basename($matches[3]));
    	return "<a rel=\"".$rel."\" href=\"".$dirname.'/'.$filename."\" >".$matches[0]."</a>";
    }, $string);

    return $string;
}

/**
 * @brief Mostra il valore di un campo di tipo testo formattato come "solo testo"
 * @description Inserisce dei tag BR prima di ogni nuova linea in una stringa (sostituisce i caratteri di fine riga \\r\\n)
 *
 * @param string $string
 * @return testo modificato
 */
function htmlCharsText($string)
{
    $string = convertToHtml($string);

    $string = trim($string);
    // CSS2
    $string = str_replace ('&', '&amp;', $string);
    $string = str_replace ('\'', '&#039;', $string);
    $string = stripslashes($string);
    $string = nl2br($string);

    return $string;
}
// End 2

// 3. from DB to Form

/**
 * @brief Modifica il valore di un campo di tipo testo per visualizzarlo in un input form
 *
 * @param string $string
 * @return testo modificato
 */
function htmlInput($string)
{
	if(is_null($string) or empty($string)) {
    	return null;
    }

    $string = convertToHtml($string);

    $string = trim($string);
    $string = stripslashes($string);
    //$string = replaceChar($string);
    $string = htmlspecialchars($string);

    return $string;
}

/**
 * @brief Modifica il valore di un campo di tipo testo per visualizzarlo in un input form di tipo "solo testo"
 *
 * @param string $string
 * @return testo modificato
 */
function codeInput($string) {

    $string = preg_replace("/:/", "&#58;", $string);
    return $string;
}

// End 3

/**
 * @brief Racchiude il testo tra virgolette singole
 *
 * La funzione viene utilizzata ad esempio per racchiudere i campi nelle email e nelle esportazioni di file 
 *
 * @param string $string
 * @return testo racchiuso tra virgolette
 */
function enclosedField($string){

    $string = '"'.$string.'"';
    return $string;
}

/**
 * @brief Escape testo che deve essere passato come variabile javascript
 *
 * @param string $string
 * @param boolean $newline mantiene gli 'a capo' (default false)
 * @return testo escaped
 */
function jsVar($string, $newline=false)
{
    if($newline)
    {
        $string = str_replace("\n",'\\n',$string);
        $string = str_replace("\r",'\\r',$string);
        $string = str_replace("\t",'\\t',$string);
    }
    else
    {
        $string = str_replace("\n",'',$string);
        $string = str_replace("\r",'',$string);
        $string = str_replace("\t",'',$string);
    }

    $string = str_replace("'","\'",$string);
    $string = str_replace("&#039;",'\\\'',$string);
    $string = str_replace("\"","\'",$string);

    return $string;
}

/**
 * @brief Escape testo che deve essere racchiuso in attributi html
 *
 * @param string $string
 * @return testo escaped
 */
function attributeVar($string)
{
  $string = str_replace("\n",'',$string);
  $string = str_replace("\r",'',$string);
  $string = str_replace("\t",'',$string);
  $string = str_replace("&#039;",'\\\'',$string);
  $string = str_replace("\"","\'",$string);

  return $string;
}

/**
 * @brief Converte le entities HTML, ma non i tag
 *
 * @param string $string
 * @return testo convertito
 */
function htmlToEntities($string){

    $string = str_replace ('\'','&#039;', $string);
    $string = str_replace("€","&#8364;",$string);
    $string = str_replace("$","&#36;",$string);
    $string = str_replace("©","&#169;",$string);
    $string = str_replace("®","&#174;",$string);

    $string = str_replace("à","&#224;",$string);
    $string = str_replace("è","&#232;",$string);
    $string = str_replace("ò","&#242;",$string);
    $string = str_replace("ù","&#249;",$string);
    $string = str_replace("°","&#176;",$string);

    return $string;
}

/**
 * @brief Codifica i parametri url
 *
 * @param string $params parametri url
 * @return stringa codificata
 */
function encode_params($params){

    if(!empty($params))
    {
        $params = preg_replace('/=/', ':', $params);
        $params = preg_replace('/&/', ';;', $params);
    }
    return $params;
}

/**
 * @brief Decodifica i parametri url
 *
 * @param string $params parametri url
 * @return stringa decodificata
 */
function decode_params($params){

    if(!empty($params))
    {
        $params = preg_replace('/:/', '=', $params);
        $params = preg_replace('/;;/', '&', $params);
    }
    return $params;
}
