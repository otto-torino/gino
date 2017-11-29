<?php
/**
 * APPUNTI PER LA GESTIONE DELLE STRINGHE
 * ---------------
 * 
 * ###iconv()
 * iconv() converte la stringa codificata nel parametro stringa in in_charset nella stringa codificata in out_charset. Restituisce la stringa convertita o FALSE, se fallisce.
 * @code
 * string iconv (string $in_charset , string $out_charset , string $str)
 * @endcode
 * dove @a in_charset è l'input charset, @a out_charset l'output charset e @a str la stringa che deve essere convertita.
 * 
 * Se si aggiunge la stringa //TRANSLIT a out_charset viene attivata la traslitterazione. 
 * Ciò significa che quando un carattere non può essere rappresentato nel charset di destinazione, può essere approssimato attraverso uno o più caratteri simili alla vista. \n
 * Se si aggiunge la stringa //IGNORE, i caratteri che non possono essere rappresentati nel charset di destinazione vengono scartati silenziosamente.
 * In caso contrario, la stringa (str) viene tagliata dal primo carattere illegale e viene generato un E_NOTICE.
 * 
 * Il parametro //IGNORE vale soltanto per la codifica di output. Ciò significa che se (e solo se) l'ingresso è codificato correttamente, 
 * iconv può (e solo allora può) cambiarlo a un'altra codifica. \n
 * Se poi la codifica di uscita non può codificare un codice-punto che è disponibile nella codifica di ingresso, allora il codice-punto sarà rifiutato. \n
 * //IGNORE è solo un flag che indica come trattare la mancanza dei codice-punti nella codifica di output.
 * 
 * @see http://php.net/manual/en/function.iconv.php
 * The main problem here is that when your string contains illegal UTF-8 characters, there is no really straight forward way to handle those. \n
 * iconv() simply (and silently!) terminates the string when encountering the problematic characters (also if using //IGNORE), returning a clipped string.
 * The output character set (the second parameter) should be different from the input character set (first param).
 * If they are the same, then if there are illegal UTF-8 characters in the string, iconv will reject them as being illegal according to the input character set.
 * 
 * ###htmlentities()
 * htmlentities() crea problemi con l'impostazione del charset 'UTF-8' quando il database ha come collation @a latin1. \n
 * Nel caso di problemi (ad esempio la creazione di un file pdf costituito unicamente da una pagina bianca) 
 * verificare che tutti i dati in arrivo dal database vengano gestiti attraverso l'interfaccia di gestione delle stringhe plugin_mpdf::text().
 */

/**
 * @file func.mpdf.php
 * @brief Racchiude le librerie per il trattamento di stringhe e dei valori da database in pdf
 *
 * @copyright 2005-2017 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

/**
 * Formattazione del testo da html a pdf
 * 
 * @param string $string
 * @return string
 */
function htmlToPdf($string) {
	
	$string = htmlentities($string, ENT_QUOTES, 'UTF-8');
	
	return htmlspecialchars_decode($string);
}

/**
 * Gestione delle stringhe salvate in campi trattati con tag input (char, varchar)
 * 
 * @param string $string
 * @return string
 */
function pdfChars($string, $openclose=false) {
    
	$string = convertToHtml($string);
	
	$string = trim($string);
	$string = stripslashes($string);

	$string = str_replace ('&euro;', '€', $string);
	$string = str_replace ('&', '&amp;', $string);
	$string = str_replace ('\'', '&#039;', $string);
	$string = preg_replace("/:/", "&#58;", $string);
	
	if($openclose)
	{
		$string = str_replace("<", "&lt;", $string);
		$string = str_replace(">", "&gt;", $string);
	}
	
	return $string;
}

/**
 * Gestione delle stringhe salvate in campi trattati con tag textarea (text)
 * 
 * @param string $string
 * @return string
 */
function pdfChars_Textarea($string){
    
	$string = trim($string);
	$string = stripslashes($string);
	$string = convertToHtml($string);
	
	$string = str_replace ('&euro;', '€', $string);
	$string = str_replace ('&bull;', '•', $string);
	$string = htmlentities($string, ENT_QUOTES, 'UTF-8');
	
	$string = nl2br($string);
	
	return $string;
}

/**
 * Gestione delle stringhe salvate in campi trattati con l'editor CKEditor (text)
 * 
 * @param string $string
 * @return string
 */
function pdfTextChars($string) {
    
	$string = trim($string);
	$string = stripslashes($string);
	$string = convertToHtml($string);
	
	// Eliminare i commenti HTML
	$string = preg_replace("#(\n*)#", "", $string);
	$string = preg_replace("#<!--(.*)-->#", "", $string);
	
	//$string = preg_replace("#<br />#", "\n", $string);

	$string = str_replace ('&euro;', '€', $string);
	$string = str_replace ('&bull;', '•', $string);
	//$string = str_replace ('&', '&amp;', $string);
	//$string = str_replace ('\'', '&#039;', $string);
	//$string = preg_replace("/:/", "&#58;", $string);
	//$string = str_replace(':', "&#58;", $string);
	
	// conversione in entities
	$string = htmlentities($string, ENT_QUOTES, 'UTF-8');
	// riconversione di alcune entities
	$string = preg_replace('#&lt;([a-zA-Z]+)&gt;#', "<$1>", $string);	// <p>
	$string = preg_replace('#&lt;/([a-zA-Z]+)&gt;#', "</$1>", $string);	// </p>
	//$string = preg_replace('#/&gt;#', '/>', $string);					// />
	$string = preg_replace("#&lt;([a-zA-Z]+)[\s]+[\w]*/&gt;#", "<$1 />", $string);	// <br />
	$string = preg_replace("#&lt;([a-zA-Z]+)[\s]+(id|class|lang)=&quot;[\w\.\-]*&quot;[\s]*&gt;#", "<$1>", $string);	// <span id="...">
	
	// per risolvere i problemi nel riconoscere la fine del tag 'b' quando è in prossimità di un 'br'
	$string = preg_replace("#><br />#", ">\n<br />", $string);
	$string = preg_replace("#<br />\n(<[a-zA-Z]+>)#", "$1\n<br />", $string);
	
	// problema quando non sono tag:
	//$string = str_replace('&lt;', "<", $string);
	//$string = str_replace('&gt;', ">", $string);
	
	//$string = preg_replace("#><br />#", "> <br />", $string);
	//$string = preg_replace("#<ul>#", "<ul style=\"margin:-20px;padding:0;\">", $string);
	
	return $string;
}

/**
 * @brief Adatta il testo per il pdf
 * 
 * @param string $str
 * @return string
 */
function pdfHtmlToEntities($str) {
	
	//$txt = normalize_special_characters($str);
	$txt = replaceChar($str);
	$txt = htmlentities($txt, ENT_QUOTES, 'UTF-8');
	$txt = str_replace('&euro;', chr(128), $txt);
	$txt = html_entity_decode($txt);
	
	return $txt;
}

/**
 * Normalizza alcuni caratteri speciali
 * 
 * @param string $str
 * @param boolean $unwanted indica se convertire i caratteri alfabetici accentati in caratteri non accentati (default false)
 * @return string
 */
function normalize_special_characters($str, $unwanted=false) {
    
	# Quotes cleanup
	$str = str_replace(chr(ord("`")), "'", $str);
	$str = str_replace(chr(ord("´")), "'", $str);
	$str = str_replace(chr(ord("„")), ",", $str);
	$str = str_replace(chr(ord("`")), "'", $str);
	$str = str_replace(chr(ord("´")), "'", $str);
	$str = str_replace(chr(ord("“")), "\"", $str);
	$str = str_replace(chr(ord("”")), "\"", $str);
	$str = str_replace(chr(ord("´")), "'", $str);

	$unwanted_array = array('Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E', 'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y');
	if($unwanted)
    	$str = strtr($str, $unwanted_array);

	# Bullets, dashes, and trademarks
	$str = str_replace(chr(149), "&#8226;", $str);	# bullet •
	$str = str_replace(chr(150), "&ndash;", $str);	# en dash
	$str = str_replace(chr(151), "&mdash;", $str);	# em dash
	$str = str_replace(chr(153), "&#8482;", $str);	# trademark
	$str = str_replace(chr(169), "&copy;", $str);	# copyright mark
	$str = str_replace(chr(174), "&reg;", $str);	# registration mark

	return $str;
}

/**
 * @brief Sostituzione di alcuni caratteri (generalmente inseriti con word)
 *
 * @param string $text
 * @return string, stringa ripulita
 */
function replaceChar($text) {
    
    $find = array("’", "‘", "`");
    $text = str_replace($find, "'", $text);
    $find = array("“", "”");
    $text = str_replace($find, "\"", $text);
    $text = str_replace("…", "...", $text);
    $text = str_replace("–", "-", $text);
    
    return $text;
}

function htmlButTags($str) {
    
	// Take all the html entities
	$caracteres = get_html_translation_table(HTML_ENTITIES);
	// Find out the "tags" entities
	$remover = get_html_translation_table(HTML_SPECIALCHARS);
	// Spit out the tags entities from the original table
	$caracteres = array_diff($caracteres, $remover);
	// Translate the string....
	$str = strtr($str, $caracteres);
	
	// now amps
	$str = preg_replace("/&(?![A-Za-z]{0,4}\w{2,3};|#[0-9]{2,3};)/","&amp;" , $str);
	
	return $str;
}

function keephtml($string) {
    
	$res = htmlentities($string);
	$res = str_replace("&lt;","<",$res);
	$res = str_replace("&gt;",">",$res);
	$res = str_replace("&quot;",'"',$res);
	$res = str_replace("&amp;",'&',$res);
	return $res;
}
?>