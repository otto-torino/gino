<?php
/*================================================================================
    Gino - a generic CMS framework
    Copyright (C) 2005  Otto Srl - written by Marco Guidotti

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

   For additional information: <opensource@otto.to.it>
================================================================================*/
/*
CONTENTS OF FUNCTIONS
*/

function mssql_escape_string($string_to_escape)
{
	$replaced_string = str_replace("'","''",$string_to_escape);
	$replaced_string = str_replace("\0","[NULL]",$replaced_string);
	
	return $replaced_string;
}

/*
	Replace
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

function replaceChar2($text)
{
	$find = array("", "", "`");
	$text = str_replace($find, "'", $text);
	$find = array("", "");
	$text = str_replace($find, "\"", $text);
	$text = str_replace("…", "...", $text);
	$text = str_replace("", "-", $text);
	$text = str_replace("", "€", $text);
	return $text;
}

// End Replace

/*
	Strip
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

function remove_attributes($text)
{
	// rimuove 'style'
	//$text = preg_replace("'\\s(style)=\"(.*?)\"'i", '', $text);
	
	// => problemi
	$strip_attrib = 
"/(font\-size|color|font\-family|line\-height|text\-indent):\\s(\\d+(\\x2E\\d+\\w+|\\W)|\\w+)(;|)(\\s|)/i";
	//$text = preg_replace($strip_attrib, '', $text);
	
	// rimuove 'class' quando non assume un valore
	$text = str_replace(" class=\"\"", '', $text);
	
	return $text;
}

/*
// strip selected tags => VERIFICARE
function _strip_selected_tags_($text, $tags = array())
{
	$args = func_get_args();
	$text = array_shift($args);
	$tags = func_num_args() > 2 ? array_diff($args, array($text)) : (array)$tags;
	foreach ($tags as $tag){
		while(preg_match('/<'.$tag.'(|\W[^>]*)>(.*)<\/'. $tag .'>/iusU', $text, $found)){
			$text = str_replace($found[0],$found[2],$text);
		}
	}
	return preg_replace('/(<('.implode('|',$tags).')(|\W.*)\/>)/iusU', '', $text);
}
*/

/**
* strip_selected_tags ( string str [, string strip_tags[, strip_content flag]] )
* ---------------------------------------------------------------------
* Like strip_tags() but inverse; the strip_tags tags will be stripped, not kept.
* strip_tags: string with tags to strip, ex: "<a><p><quote>" etc.
* strip_content flag: TRUE will also strip everything between open and closed tag
*/
function strip_selected_tags($str, $tags = '', $stripContent = false)
{
	preg_match_all("/<([^>]+)>/i", $tags, $allTags, PREG_PATTERN_ORDER);
	foreach ($allTags[1] as $tag){
		if($stripContent){
			$str = preg_replace("/<".$tag."[^>]*>.*<\/".$tag.">/iU", "", $str);
		}
		$str = preg_replace("/<\/?".$tag."[^>]*>/iU", "", $str);
	}
	return $str;
}

function strip_invisible_tags($text)
{
	// This function will remove scripts, styles, and other unwanted
	// invisible text between tags.
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

function stripAll($text)	// FCK Editor
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

function stripEditor($text)	// FCKEditor
{
	// FCKEditor Tag
	$text = str_replace ('<strong>', '<b>', $text);
	$text = str_replace ('</strong>', '</b>', $text);
	$text = str_replace ('<em>', '<i>', $text);
	$text = str_replace ('</em>', '</i>', $text);
	// End
	
	// Link
	$search = array('target="_top"', 'target="_self"', 'target="_parent"');
	$text = str_replace ($search, '', $text);
	$text = str_replace ('target="_blank"', 'rel="external"', $text);
	// End
	return $text;
}

// End Strip

/*
	Input/Output DB Values
*/

// 1. from HTML Form to DB

/**
* cleanVar ( string str [, string settype[, string strip_tags]] )
* ---------------------------------------------------------------------
* method: $_GET, $_POST, $_REQUEST
* name: variable name
* settype: string with variable type: bool|int|float|string|array|object|null
* strip_tags: string with tags to strip, ex: "<a><p><quote>" etc.
*/
function cleanVar($method, $name, $type, $strip_tags)
{
	if(isset($method[$name]) AND !empty($method[$name]))
	{
		$value = $method[$name];
		
		if($type == 'array')
		{
			$n_array = array();
			foreach($value AS $element)
			{
				$n_array[] = clean_sequence($element, $strip_tags);
			}
			$value = $n_array;
		}
		else
		{
			$value = clean_sequence($value, $strip_tags);
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
			$value = str_replace(',', '.', $value);
			$value = floatval($value);
			$value = str_replace($larr['decimal_point'], '.', $value);
		}
		else settype($value, $type);
	}

	return $value;
}

function clean_sequence($text, $strip_tags){
	
	$text = trim($text);
	if(get_magic_quotes_gpc()) $text = stripslashes($text);	// magic_quotes_gpc = On
	
	// Strip
	if(isset($strip_tags) && !empty($strip_tags)) $text = strip_selected_tags($text, $strip_tags, false);
	$text = strip_invisible_tags($text);
	$text = strip_tags_attributes($text, true, true);
	
	// Replace
	$text = replaceChar($text);
	
	$text = str_replace ('€', '&euro;', $text);	// con DB ISO-8859-1
	$text = mysql_real_escape_string($text);
	
	return $text;
}

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
		
		$value = mysql_real_escape_string($value);
	}
	else
	{
		$value = '';
	}
	return $value;
}

function codeDb($text) {
	if(get_magic_quotes_gpc()) $text = stripslashes($text);	// magic_quotes_gpc = On
	$text = str_replace ('€', '&euro;', $text);	// con DB ISO-8859-1
	return mysql_real_escape_string($text);
}

// Classe multimedia
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
function htmlChars($string, $id='', $options=array())
{
	$newline = array_key_exists('newline', $options) ? $options['newline'] : false;
	
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

// only text (\n)
function htmlCharsText($string)
{
	$string = trim($string);
	// CSS2
	$string = str_replace ('&', '&amp;', $string);
	$string = str_replace ('\'', '&#039;', $string);
	$string = stripslashes($string);
	$string = nl2br($string);	

	return $string;
}

function textFromEditor($text) {
	
	// to be removed
	return $text;

}
// End 2

// 3. from DB to Form
function htmlInput($string)
{
	$string = trim($string);
	$string = stripslashes($string);
	$string = replaceChar($string);
	$string = htmlspecialchars($string);
	//$string = preg_replace("/:/", "&#58;", $string);
	
	return $string;
}

// textarea -> FCKEditor
function htmlInputEditor($string)
{
	$string = trim($string);
	$string = stripslashes($string);
	$string = str_replace ('rel="external"', 'target="_blank"', $string);
	//$string = replaceChar2($string);
	return $string;
}

function codeInput($string) {

	$string = preg_replace("/:/", "&#58;", $string);
	return $string;

}

// End 3

// 4. from DB to Text (ex. email, export file)

function enclosedField($string){
	
	$string = '"'.$string.'"';
	return $string;
}
// End 4

function jsVar($string)
{
	$string = str_replace("\n",'',$string);
	$string = str_replace("\r",'',$string);
	$string = str_replace("\t",'',$string);
	
	$string = str_replace("'","\'",$string);
	$string = str_replace("&#039;",'\\\'',$string);
	$string = str_replace("\"","\'",$string);
	
	return $string;
}

/*
	HTML entities conversion (but not tags conversion)
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
