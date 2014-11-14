<?php
/**
 * @file func.var.php
 * @brief Racchiude le librerie per il trattamento dei valori da e per un input/database
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

/**
 * Converte l'encoding di un valore preso da un campo di un database (non UTF-8) nella codifica UTF-8
 * 
 * @param string $value valore da convertire
 * @return string
 */
function convertToHtml($value)
{
	$value = mb_detect_encoding($value, mb_detect_order(), true) === 'UTF-8' ? $value : mb_convert_encoding($value, 'UTF-8');
	
	return $value;
}

/**
 * Converte l'encoding di un valore da html (es UTF-8) a un encoding valido per il database 
 * 
 * @param string $value valore da convertire
 * @param string $character_set set di caratteri del database
 *   - @a CP1252, per SQL Server
 * @return string
 */
function convertToDatabase($value, $character_set=null)
{
	if(!is_null($character_set))
		$value = mb_convert_encoding($value, $character_set, mb_detect_encoding($value, mb_detect_order(), true));
	
	return $value;
}

/**
 * Funzione di sostituzione per i caratteri "strani" di word
 * 
 * @param string $text
 * @return string
 */
function replaceChar($text)
{
	$find = array("’", "‘", "`");
	$text = str_replace($find, "'", $text);
	$find = array("“", "”");
	$text = str_replace($find, "\"", $text);
	$text = str_replace("…", "...", $text);
	$text = str_replace("–", "-", $text);

	return $text;
}

/**
 * Rimuove gli attributi javascript ed html nei tag
 * 
 * @see remove_attributes()
 * @param string $text testo
 * @param boolean $strip_js rimuove gli attributi javascript
 * @param boolean $strip_attributes rimuove alcuni attributi html
 * @return string
 */
function strip_tags_attributes($text, $strip_js, $strip_attributes)
{
	if($strip_js)
	{
		$js_attributes = array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavaible', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragdrop', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterupdate', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmoveout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');
		
		$text = preg_replace('/\s(' . implode('|', $js_attributes) . ').*?([\s\>])/', '\\2', preg_replace('/<(.*?)>/ie', "'<' . preg_replace(array('/javascript:[^\"\']*/i', '/(" . implode('|', $js_attributes) . ")[ \\t\\n]*=[ \\t\\n]*[\"\'][^\"\']*[\"\']/i', '/\s+/'), array('', '', ' '), stripslashes('\\1')) . '>'", $text));
	}
	
	if($strip_attributes) $text = remove_attributes($text);
	
	return $text;
}

/**
 * Rimuove gli attributi html nei tag
 * 
 * @param string $text testo
 * @return string
 */
function remove_attributes($text)
{
	// rimuove 'style'
	//$text = preg_replace("'\\s(style)=\"(.*?)\"'i", '', $text);
	
	//$strip_attrib = "/(font\-size|color|font\-family|line\-height|text\-indent):\\s(\\d+(\\x2E\\d+\\w+|\\W)|\\w+)(;|)(\\s|)/i";
	//$text = preg_replace($strip_attrib, '', $text);
	
	// rimuove 'class' quando non assume un valore
	$text = str_replace(" class=\"\"", '', $text);
	
	return $text;
}

/**
 * Rimuove i tag indicati
 * 
 * Simile a strip_tags(), ma mentre strip_tags rimuove tutti i tag, questa funzione li preserva
 * 
 * @param string $text testo
 * @param string $tags stringa con i tag da rimuovere, ad esempio "<a><p><quote>"
 * @param boolean $stripContent rimuove anche il testo contenuto tra l'apertura e la chiusura del tag
 * @return string
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
 * Rimuove script, stili e altro testo indesiderato (e invisibile) tra i tag 
 * 
 * @param string $text testo
 * @return string
 */
function strip_invisible_tags($text)
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

/**
 * Sostituisce le entities con il carattere corrispondente
 * 
 * Questa funzione viene utilizzata per "ripulire" il testo che arriva dall'editor html (CK Editor)
 * 
 * @param string $text testo
 * @return string
 */
function stripAll($text)
{
	//$text = html_entity_decode($text);
	$text = str_replace("&#8364;","€",$text);
	$text = str_replace("&#36;","$",$text);
	$text = str_replace("&#169;","©",$text);
	$text = str_replace("&#174;","®",$text);
	$text = str_replace("&#176;","°",$text);
	$text = str_replace("&#224;","à",$text);
	$text = str_replace("&#232;","è",$text);
	$text = str_replace("&#242;","ò",$text);
	$text = str_replace("&#249;","ù",$text);
	$text = str_replace("&#160;"," ",$text);
	$text = str_replace("&#224;","à",$text);
	$text = str_replace("&#176;","°",$text);
	$text = str_replace("&#38;","&",$text);
	$text = str_replace("&#8212;","-",$text);
	$text = str_replace("&ndash;","-",$text);
	$find = array ("&#39;","&lsquo;","&rsquo;");
	$text = str_replace($find,"'",$text);
	$text = str_replace("&sbquo;",",",$text);
	$find = array("&ldquo;","&rdquo;","&bdquo;","&quot;","&#8220;","&#34;");
	$text = str_replace($find, "\"", $text);
	$text = str_replace("&hellip;","...",$text);
	
	return $text;
}

/**
 * Sostituisce alcuni tag utilizzati dall'editor html con i tag standard
 * 
 * Questa funzione viene utilizzata per "ripulire" il testo che arriva dall'editor html (CK Editor)
 * 
 * @param string $text testo
 * @return string
 */
function stripEditor($text)
{
	$text = str_replace ('<strong>', '<b>', $text);
	$text = str_replace ('</strong>', '</b>', $text);
	$text = str_replace ('<em>', '<i>', $text);
	$text = str_replace ('</em>', '</i>', $text);
	
	// Link
	$search = array('target="_top"', 'target="_self"', 'target="_parent"');
	$text = str_replace ($search, '', $text);
	$text = str_replace ('target="_blank"', 'rel="external"', $text);
	// End
	return $text;
}

/*
	Input/Output DB Values
*/

// 1. from HTML Form to DB

/**
 * Modifica il valore presente in un campo del form per inserirlo nel database
 * 
 * Imposta il tipo del testo
 * 
 * @see clean_sequence()
 * @param string $method metodo utilizzato (GET, POST, REQUEST)
 * @param string $name nome della variabile
 * @param string $type tipo di variabile (bool,int,float,string,array,object,null)
 * @param string $strip_tags stringa con i tag da rimuovere, ad esempio "<a><p><quote>"
 * @param array $options array associativo di opzioni
 * @return mixed
 */
function cleanVar($method, $name, $type, $strip_tags, $options=array())
{
	if(isset($method[$name]) AND $method[$name] !== '')
	{
		$value = $method[$name];
		
		if($type == 'array')
		{
			$n_array = array();
			foreach($value AS $element)
			{
				$n_array[] = clean_sequence($element, $strip_tags, $options);
			}
			$value = $n_array;
		}
		else
		{
			$value = clean_sequence($value, $strip_tags, $options);
		}
	}
	else
		$value = null;
	
	if(isset($type))
	{
		if($type == 'float')
		{
			$larr = localeconv();
			/*
			$search = array(
				$larr['decimal_point'],			// Decimal point character
				$larr['mon_decimal_point'],		// Monetary decimal point character
				$larr['thousands_sep'],			// Thousands separator
				$larr['mon_thousands_sep'],		// Monetary thousands separator
				$larr['currency_symbol'],		// Local currency symbol (i.e. $)
				$larr['int_curr_symbol']		// International currency symbol (i.e. USD)
			);
			$replace = array('.', '.', '', '', '', '');
			$value = str_replace($search, $replace, $value);
			*/
      if($value !== null) {
        $value = str_replace(',', '.', $value);
			  $value = floatval($value);
			  $value = str_replace($larr['decimal_point'], '.', $value);
      }
		}
		elseif($value !== null) settype($value, $type);
	}
	
	return $value;
}

/**
 * Esegue una serie di operazioni sul testo per renderlo compatibile con gino e col database
 * 
 * @param mixed $text testo
 * @param string $strip_tags stringa con i tag da rimuovere, ad esempio "<a><p><quote>"
 * @param array $options
 *   array associativo di opzioni
 *   - @b escape (boolean)
 * @return mixed
 */
function clean_sequence($text, $strip_tags, $options){
	
	$escape = gOpt('escape', $options, true);
	
	$text = trim($text);
	if(get_magic_quotes_gpc()) $text = stripslashes($text);	// magic_quotes_gpc = On
	
	// Strip
	if(isset($strip_tags) && !empty($strip_tags)) $text = strip_selected_tags($text, $strip_tags, false);
	$text = strip_invisible_tags($text);
	$text = strip_tags_attributes($text, true, true);
	
	$text = replaceChar($text);
	$text = str_replace ('€', '&euro;', $text);	// con DB ISO-8859-1
	
	if($escape)
	{
		$db = db::instance();
		$text = $db->escapeString($text);
	}
	
	return $text;
}

/**
 * Modifica il valore presente in un campo del form di tipo editor html per inserirlo nel database
 * 
 * @param string $method metodo utilizzato (GET, POST, REQUEST)
 * @param string $name nome della variabile
 * @param string $strip_tags stringa con i tag da rimuovere, ad esempio "<a><p><quote>"
 * @return string
 */
function cleanVarEditor($method, $name, $strip_tags)
{
	if(isset($method[$name]) AND !empty($method[$name]))
	{
		$value = $method[$name];
		
		settype($value, 'string');
		
		$value = trim($value);
		if(get_magic_quotes_gpc()) $value = stripslashes($value);	// magic_quotes_gpc = On
		
		$value = stripEditor($value);
		
		if(isset($strip_tags) && !empty($strip_tags)) $value = strip_selected_tags($value, $strip_tags, false);
		$value = strip_tags_attributes($value, true, true);
		$value = html_entity_decode($value, null, 'UTF-8');

		$value = replaceChar($value);
		$value = stripAll($value);
		
		$db = db::instance();
		$value = $db->escapeString($value);
	}
	else
	{
		$value = '';
	}
	return $value;
}

/**
 * Conversione dei dati Unicode $_GET/$_POST, generati dalla funzione javascript escape(), in UTF8 per il processo server-side
 * 
 * @param string $str
 * @return string
 */
function utf8_urldecode($str) {
	
	$str = preg_replace("/%u([0-9a-f]{3,4})/i","&#x\\1;", urldecode($str));
	$str = html_entity_decode($str, null, 'UTF-8');
	
	return $str;
}

/**
 * Prepara il testo da inserire nel database senza rimuovere il codice html
 * 
 * @param string $text testo
 * @return string
 */
function codeDb($text) {	
	
	if(get_magic_quotes_gpc()) $text = stripslashes($text);	// magic_quotes_gpc = On
	$text = str_replace ('€', '&euro;', $text);	// con DB ISO-8859-1
	
	$db = db::instance();
	$text = $db->escapeString($text);
	
	return $text;
}

/**
 * Modifica il valore presente in un campo testo del form per inserirlo nel database
 * 
 * Si tratta di testo di tipo "codice", nel quale non viene rimosso il codice html
 * 
 * @see codeDB()
 * @param string $method metodo utilizzato (GET, POST, REQUEST)
 * @param string $name nome della variabile
 * @param array $options
 *   array associativo di opzioni
 *   - @b width (string): sovrascrive la larghezza di visualizzazione di una immagine
 *   - @b height (string): sovrascrive l'altezza di visualizzazione di una immagine
 * @return string
 */
function codeToDB($method, $name, $options=array()){
	
	if(isset($method[$name]) AND !empty($method[$name]))
	{
		$value = $method[$name];
		
		if(array_key_exists('width', $options))
			$value = preg_replace("#width=\"(.*?)\"#", "width=\"".$options['width']."\"", $value);
		if(array_key_exists('height', $options))
			$value = preg_replace("#height=\"(.*?)\"#", "height=\"".$options['height']."\"", $value);
		
		$value = codeDB($value);
	}
	else $value = null;
	
	return $value;
}

// End 1

// 2. from DB to HTML

/**
 * Modifica il valore di un campo testo del database per visualizzarlo in HTML
 * 
 * @param string $string testo
 * @param string $id codice che raggruppa un insieme di immagini da visualizzare con le librerie slimbox
 * @param array $options
 *   array associativo di opzioni
 *   - @b newline (boolean): inserisce dei tag BR prima di ogni nuova linea in una stringa (sostituisce i \r\n)
 */
function htmlChars($string, $id='', $options=array())
{
	$newline = array_key_exists('newline', $options) ? $options['newline'] : false;
	
	$string = convertToHtml($string);
	
	$string = trim($string);
	$string = stripslashes($string);
	//$string = utf8_encode($string);

	$string = str_replace ('&euro;', '€', $string);
	$string = str_replace ('&bull;', '•', $string);
	$string = str_replace ('&', '&amp;', $string);	// CSS2
	$string = str_replace ('\'', '&#039;', $string);
	$string = preg_replace("/:/", "&#58;", $string);

	if($newline)
		$string = nl2br($string);
	
	if($id) $string = slimboxReplace($string, $id);
	return $string;
}

/**
 * Modifica il valore di un campo testo del database per visualizzarlo in HTML
 * 
 * Si tratta di testo di tipo "codice", ovvero inserito con la funzione @a codeToDB().
 * Crea un blocco che racchiude il codice.
 * 
 * @see codeToDB()
 * @param string $string
 * @return string
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
 * Modifica il valore di un campo testo del database per visualizzarlo in HTML
 * 
 * Si tratta di testo di tipo "codice", ovvero inserito con la funzione @a codeToDB().
 * Crea un blocco che racchiude il codice e ne evidenzia le righe.
 * 
 * @see codeToDB()
 * @see preCodeParser()
 * @param string $string
 * @param string $id codice che raggruppa un insieme di immagini da visualizzare con le librerie slimbox
 * @return string
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
 * Modifica il valore di un campo testo del database per attivare le librerie slimbox
 * 
 * @param string $string testo
 * @param string $id codice che raggruppa un insieme di immagini da visualizzare con le librerie slimbox
 * @return string
 */
function slimboxReplace($string, $id) {

	$rel = "lightbox-$id";

	$pattern = "/(<img)[^(\/>)]+(class=\"lightbox\"){1}[^(\/>)]+(src=\")([^\"]+)[^(\/>)]+\/>/i";
	$replacement = "<a rel=\"".$rel."\" href=\"$4\" >$0</a>";
	
	$pattern2 = "/(<img)[^(\/>)]+(src=\")([^\"]+)[^(\/>)]+(class=\"lightbox\"){1}[^(\/>)]+\/>/i";
	$replacement2 = "<a rel=\"".$rel."\" href=\"$3\" >$0</a>";

	$string = preg_replace($pattern, $replacement, $string);
	$string = preg_replace($pattern2, $replacement2, $string);

	return $string;
}

/**
 * Mostra il valore di un campo testo del database formattato come "solo testo"
 * 
 * Inserisce dei tag BR prima di ogni nuova linea in una stringa (sostituisce i \r\n)
 * 
 * @param string $string
 * @return string
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
 * Modifica il valore di un campo testo del database per visualizzarlo in un input form
 * 
 * @param string $string
 * @return string
 */
function htmlInput($string)
{
	if(is_null($string))
		return null;
	
	$string = convertToHtml($string);
	
	$string = trim($string);
	$string = stripslashes($string);
	$string = replaceChar($string);
	$string = htmlspecialchars($string);
	//$string = preg_replace("/:/", "&#58;", $string);
	
	return $string;
}

/**
 * Modifica il valore di un campo testo del database per visualizzarlo in un input form di tipo editor html
 * 
 * @param string $string
 * @return string
 */
function htmlInputEditor($string)
{
	$string = convertToHtml($string);
	
	$string = trim($string);
	$string = stripslashes($string);
	$string = str_replace ('rel="external"', 'target="_blank"', $string);
	return $string;
}

/**
 * Modifica il valore di un campo testo del database per visualizzarlo in un input form di tipo "solo testo"
 * 
 * @param string $string
 * @return string
 */
function codeInput($string) {

	$string = preg_replace("/:/", "&#58;", $string);
	return $string;
}

// End 3

/**
 * Racchiude il testo tra virgolette singole
 * 
 * La funzione viene utilizzata ad esempio per racchiudere i campi nelle email e nelle esportazioni di file 
 * 
 * @param string $string
 * @return string
 */
function enclosedField($string){
	
	$string = '"'.$string.'"';
	return $string;
}

/**
 * Da utilizzare per il testo che deve essere racchiuso in variabili javascript
 * 
 * @param string $string
 * @param boolean $newline mantiene gli 'a capo' (default false)
 * @return string
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
 * Da utilizzare per il testo che deve essere racchiuso in attributi html
 * 
 * @param string $string
 * @return string
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
 * Converte le entities HTML, ma non i tag
 * 
 * @param string $string
 * @return string
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
	//$string = str_replace("-","&#8212;",$string);
	
	return $string;
}

?>
