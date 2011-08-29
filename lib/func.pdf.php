<?php
/*
	HTML entities conversion (but not tags conversion)
*/

function pdfChars($string, $openclose=false)
{
	$string = trim($string);
	$string = stripslashes($string);
	//$string = utf8_encode($string);	// DB latin1

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

// Textarea (descriptions)
function pdfChars_Textarea($string)
{
	$string = trim($string);
	$string = stripslashes($string);
	//$string = utf8_encode($string);	// DB latin1
	
	$string = str_replace ('&euro;', '€', $string);
	$string = str_replace ('&bull;', '•', $string);
	$string = htmlentities($string, ENT_QUOTES, 'UTF-8');
	
	$string = nl2br($string);
	
	return $string;
}

// CKEditor: Paste As plain text
function pdfTextChars($string)
{
	$string = trim($string);
	$string = stripslashes($string);
	//$string = utf8_encode($string);	// -> DB latin1
	
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
	
	$string = htmlentities($string, ENT_QUOTES, 'UTF-8');	// tutte entities
	$string = preg_replace('#&lt;([a-zA-Z]+)&gt;#', "<$1>", $string);	// <p>
	$string = preg_replace('#&lt;/([a-zA-Z]+)&gt;#', "</$1>", $string);	// </p>
	//$string = preg_replace('#/&gt;#', '/>', $string);					// />
	$string = preg_replace("#&lt;([a-zA-Z]+)[\s]+[\w]*/&gt;#", "<$1 />", $string);	// <br />
	$string = preg_replace("#&lt;([a-zA-Z]+)[\s]+(id|class|lang)=&quot;[\w\.\-]*&quot;[\s]*&gt;#", "<$1>", $string);	// <span id="...">
	
	// Per risolvere i problemi nel riconoscere la fine del tag B quando è in prossimità di un BR
	$string = preg_replace("#><br />#", ">\n<br />", $string);
	$string = preg_replace("#<br />\n(<[a-zA-Z]+>)#", "$1\n<br />", $string);
	
	// problema quando non sono tag:
	//$string = str_replace('&lt;', "<", $string);
	//$string = str_replace('&gt;', ">", $string);
	
	//$string = preg_replace("#><br />#", "> <br />", $string);
	//$string = preg_replace("#<ul>#", "<ul style=\"margin:-20px;padding:0;\">", $string);
	
	return $string;
}

function pdfHtmlToEntities($str){
	
	//$txt = normalize_special_characters($str);
	$txt = replaceChar($str);
	$txt = htmlentities($txt, ENT_QUOTES, 'UTF-8');
	$txt = str_replace('&euro;', chr(128), $txt);
	$txt = html_entity_decode($txt);
	
	return $txt;
}

function normalize_special_characters($str, $unwanted=false)
{
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
	$str = str_replace(chr(169), "&copy;", $str);		# copyright mark
	$str = str_replace(chr(174), "&reg;", $str);		# registration mark

	return $str;
}

function htmlButTags($str)
{
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

function keephtml($string)
{
	$res = htmlentities($string);
	$res = str_replace("&lt;","<",$res);
	$res = str_replace("&gt;",">",$res);
	$res = str_replace("&quot;",'"',$res);
	$res = str_replace("&amp;",'&',$res);
	return $res;
}
?>