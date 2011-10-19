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

include(LIB_DIR.OS.'func.var.php');
include(LIB_DIR.OS.'Mobile_Detect.php');

function searchNameFile($dir){
	
	$filenames = array();
	if(is_dir($dir))
	{
		$dp = opendir($dir);
		while($file = readdir($dp))
		{
			if($file != "." AND $file != "..")
			{
				$filenames[] = $file;
			}
		}
	}
	
	return $filenames;
}

function download($full_path)
{
	if($fp = fopen($full_path, "r"))
	{
		$fsize = filesize($full_path);
		$path_parts = pathinfo($full_path);
		$extension = strtolower($path_parts["extension"]);

		header("Pragma: public");
		header('Expires: 0');
		header('Content-Description: File Transfer');
		header("Content-type: application/download");
		header("Content-Disposition: attachment; filename=\"".$path_parts["basename"]."\"");
		header("Content-length: ".$fsize);
		header("Cache-control: private");

		ob_clean();
		flush();

		@readfile($full_path);
		fclose($fp);
	}
}

/**
 * Controlla le estensioni dei file
 *
 * @param string $filename
 * @param array $extensions
 * @return bool
 * 
 * se $extensions è vuoto => true
 */
function extension($filename, $extensions){

	$ext = str_replace('.','',strrchr($filename, '.'));
	$count = 0;
	if(is_array($extensions) AND sizeof($extensions) > 0)
	{
		foreach($extensions AS $value)
		{
			if(strtolower($ext) == strtolower($value))
			$count++;
		}

		if($count > 0) return true; else return false;
	}
	else return true;
}

function email_control($email)
{
	return !preg_match("#^[a-z0-9_-]+[a-z0-9_.-]*@[a-z0-9_-]+[a-z0-9_.-]*\.[a-z]{2,5}$#", $email) ? false : true;
}

function dateToDbDate($date, $s='/') {

	if(!$date) return null;
	$date_array = explode($s, $date);
	return $date_array[2].'-'.$date_array[1].'-'.$date_array[0];

}

// $num_year	numero di cifre dell'anno
function dbDateToDate($db_date, $s='/', $num_year=4) {
	if(empty($db_date) || $db_date=='0000-00-00') return '';
	$date_array = explode('-', $db_date);
	$year = substr($date_array[0], -$num_year);
	return $date_array[2].$s.$date_array[1].$s.$year;
}

// $num_year	numero di cifre dell'anno
function dbDatetimeToDate($datetime, $s='/', $num_year=4) {
	$datetime_array = explode(" ", $datetime);
	return dbDateToDate($datetime_array[0], $s, $num_year);
}

function dbDatetimeToTime($datetime) {
	$datetime_array = explode(" ", $datetime);
	return $datetime_array[1];
}

function dbTimeToTime($db_time) {
	if(empty($db_time) || $db_time=='00:00:00') return '';
	$db_time = substr($db_time, 0, 5);
	return $db_time;
}

function timeToDbTime($time) {

	if(!$time) return null;
	
	if(preg_match("#(,)+#", $time)) $s = ',';
	elseif(preg_match("#(:)+#", $time)) $s = ':';
	else $s = '';
	
	if($s AND preg_match("#^[0-9]{1,2}($s){0,1}[0-9]{0,2}($s){0,1}[0-9]{0,2}$#", $time))
	{
		$a_time = explode($s, $time);
		
		if(sizeof($a_time) > 0)
		{
			$hour = array_key_exists(0, $a_time) ? $a_time[0] : '00';
			$minutes = array_key_exists(1, $a_time) ? $a_time[1] : '00';
			$seconds = array_key_exists(2, $a_time) ? $a_time[2] : '00';
		}
	}
	elseif(preg_match("#^[0-9]{0,2}$#", $time))
	{
		return "$time:00:00";
	}
	
	return "$hour:$minutes:$seconds";
}

// Numbers

function dbNumberToNumber($number, $decimals=2)
{
	if(!empty($number))
		$number = number_format($number, $decimals, ',', '.');
	
	return $number;
}

// Con MySQL il separatore dei decimali è il '.'
function numberToDB($number)
{
	$number = str_replace(',', '.', $number);
	return $number;
}

// integer|float
function isNumeric($variable)
{
	if(empty($variable)) return true;
	
	if(!ereg("^[0-9\,\.]+$", $variable)) return false;
	
	if(is_numeric(numberToDB($variable))) return true; else return false;
}
// End

function isValid($type, $var)
{
	if(empty($var)) return true;
	
	$valid = false;
	
	switch ($type) {
		case "IP":
		if (ereg('^([0-9]{1,3}\.){3}[0-9]{1,3}$',$var)) {
			$valid = true;
		}
		break;
		case "URL":
		if (ereg("^[a-zA-Z0-9\-\.]+\.[a-zA-Z0-9_\-\.]+$",$var)) {
			$valid = true;
		}
		break;
		case "Email":
		if (preg_match('#^[a-zA-Z0-9_\-\.]+@[a-zA-Z0-9_\-]+\.[a-zA-Z0-9_\-\.]+$#', $var)) {
			$valid = true;
		}
		break;
		case "ISBN":
		if (ereg("^[0-9]{9}[[0-9]|X|x]$",$var)) {
			$valid = true;
		}
		break;
		case "Date":
		if (ereg("^([0-9][0-2]|[0-9])\/([0-2][0-9]|3[01]|[0-9])\/[0-9]{4}|([0-9][0-2]|[0-9])-([0-2][0-9]|3[01]|[0-9])-[0-9]{4}$",$var)) {
			$valid = true;
		}
		break;
		case "Time":
		if (ereg("^[0-9]{2}[:][0-9]{2}$",$var)) {
			$valid = true;
		}
		break;
		case "HexColor":
		if (ereg('^#?([a-f]|[A-F]|[0-9]){3}(([a-f]|[A-F]|[0-9]){3})?$',$var)) {
			$valid = true;
		}
		break;
	}
	return $valid;
}

/**
* Truncates html text.
*
* Cuts a string to the length of $length and replaces the last characters
* with the ending if the text is longer than length. 
*Can strip tags or controlo their closure
*
* @param string  $html Html string to truncate.
* @param integer $length Length of returned string, including ellipsis.
* @param string  $ending Ending to be appended to the trimmed string.
* @param boolean $strip_tags If true, html tags are replaced by nothing
* @param boolean $cut_words If false, returned string will not be cut mid-word
* @param boolean $cut_images If true, returned string will not contain images
* @param array   $options
*       - endingPosition (in|out): ending characthers are positioned in the html structure or out of the html structure (after all tags are closed)
* @return string Trimmed string.
*/
function cutHtmlText($html, $length, $ending, $strip_tags, $cut_words, $cut_images, $options=null) {
	
	/*
		regular expressions to intercept tags
	*/
	$opened_tag = "<\w+\s*([^>]*[^\/>]){0,1}>";  // i.e. <p> <b> ...
	$closed_tag = "<\/\w+\s*[^>]*>";				// i.e. </p> </b> ...
	$openended_tag = "<\w+\s*[^>]*\/>";			// i.e. <br/> <img /> ...	
	$cutten_tag = "<\w+\s*[^>]*$";				// i.e. <img src="" 
	$reg_expr_img = "/<img\s*[^>]*\/>/is";      
	/* 
		Check: if text is shorter than length (tags excluded) return $html
		with or without tags
	*/
	$reg_expr = "/$opened_tag|$closed_tag|$openended_tag/is";
	$text = preg_replace($reg_expr, '', $html);
	if (strlen($text) <= $length) {
		if(!$strip_tags) {
			if($cut_images) {
				$html = preg_replace($reg_expr_img, "", $html);
			}
			return $html;
		}
		else return $text;
	}
	
	/*
		else if $strip_tags is false...
	*/
	if(!$strip_tags) {
	
		// splits all html-tags to scanable lines
		$reg_expr = "/(<\/?\w+\s*[^>]*\/?>)?([^<>]*)/is";
 		preg_match_all($reg_expr, $html, $lines, PREG_SET_ORDER);
 		/*
 			now 
 			- in $lines[$i] are listed all the matches with the regular expression:
 			  $lines[0]: first match
 			  $lines[1]: second match ...
 			  
 			- $lines[$i][0] contains the wide matching string
 			- $lines[$i][1] contains the matching with (<\/?\w+\s*[^>]*\/?>), that is opened or    
 			  closed ore openclosed tags
 			- $lines[$i][2]contains the matching with ([^<>]*) that is the text inside the tag
 			  or between a tag and another
 		*/
 		$total_length = 0;
 		$tags_opened = array();
  		$partial_html = '';
 		
 		foreach ($lines as $line_matchings) {
    		/*
    			$line_matchings[1] contains tags
    			$line_matchings[2] contains text contained in tags
    			
    			Check: what kind of tag is? open, close, openclose?
    		*/
   			if (!empty($line_matchings[1])) {
   				$strip_this_tag = 0;
   				$reg_expr_oc = "/".$openended_tag."$/is";
   				$reg_expr_o = "/<(\w+)\s*([^>]*[^\/>]){0,1}>$/is";
   				$reg_expr_c = "/<\/(\w+)>$/is";
   				// search img tags
   				if(preg_match($reg_expr_img, $line_matchings[1]) && $cut_images) {
                	$strip_this_tag = 1;
                }
                // search openended tags
                elseif (preg_match($reg_expr_oc, $line_matchings[1])) {
                	// nothing: doesn't encrease the count of characters
                	// and doesn't need a closure
                }
                // search opened tags
                elseif(preg_match($reg_expr_o, $line_matchings[1], $tag_matchings)) {
                	// open tag
                	// add tag to the beginning of $open_tags list
 					array_unshift($tags_opened, strtolower($tag_matchings[1]));
                }
                // search closed tags
                elseif(preg_match($reg_expr_c, $line_matchings[1], $tag_matchings)) {
                	// close tag
                	// delete tag from $open_tags list (as it has been already closed)
                	$pos = array_search($tag_matchings[1], $tags_opened);
  					if ($pos !== false) {
  						unset($tags_opened[$pos]);
  					}
                }
                // add html-tag to $truncate'd text
				if(!$strip_this_tag) $partial_html .= $line_matchings[1];
   				
   			}
   			/*
   				Calculate the lenght of the text inside tags and replace considering html entities one size characters
   			*/
   			$reg_exp_entities = '/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i';
   			$content_length = strlen(preg_replace($reg_exp_entities, ' ', $line_matchings[2]));
   			
   			if ($total_length+$content_length> $length) {
   			
   				$left = $length - $total_length;
   				$entities_length = 0;
   				
   				// search for html entities (l'entities conta come un carattere, ma nell'html ne uccupa di più, quindi dobbiamo fare in modo di includere completament l'entities, cioè il suo codice e contarlo interamente come un singolo carattere: scaliamo uno da $left ed aggiungiamo $entities_length all alunghezza della substring)
				if(preg_match_all($reg_exp_entities, $line_matchings[2], $entities, PREG_OFFSET_CAPTURE)) {
					// calculate the real length of all entities in the legal range
					foreach ($entities[0] as $entity) {
						if ($entity[1]+1-$entities_length <= $left) {
							$left--;
							$entities_length += strlen($entity[0]);
						}
						else {
							// no more characters left
							break;
						}
					}
				}
				
				$partial_html .= substr($line_matchings[2], 0, $left+$entities_length);
				// maximum lenght is reached, so get off the loop
  				break;
				  			
   			}
   			else {
				$partial_html .= $line_matchings[2];
  				$total_length += $content_length;
			}
   			
   			// if the maximum length is reached, get off the loop
			if($total_length>= $length) break;

		}
	}
	else {
		// considero solamente il testo puro
     		$partial_html = substr($text, 0, $length);
	}
	
	// if the words shouldn't be cut in the middle...
    	if (!$cut_words) {
       		//search the last occurance of a space or an end tag
       		$spacepos = strrpos($partial_html, ' ');
       		$endtagpos = strrpos($partial_html, '>');
       		if(isset($spacepos) || isset($endtagpos)) {
       			//cut the text in this position
       			$cutpos = ($spacepos<$endtagpos)? ($endtagpos+1) : $spacepos;
       			$partial_html = substr($partial_html, 0, $cutpos);
       		}
    	}
	
	if(isset($options['endingPosition']) && $options['endingPosition']=='in')
		$partial_html .= $ending;

	/*
		Se non ho strippato i tag devo chiudere tutti quelli rimasti aperti
	*/
	if(!$strip_tags) 
    		// close all unclosed html tags
    		foreach ($tags_opened as $tag) 
    			$partial_html .= '</' . $tag . '>';
	
	// add the ending characters to the partial text
	if(!isset($options['endingPosition']) || $options['endingPosition']=='out')
		$partial_html .= $ending;
   
    	return $partial_html;	

}

/**
 * Limita i caratteri di una stringa
 *
 * @param string $string
 * @param integer $max_char
 * @param boolean $word_complete	true: mantiene l'ultima parola completa (utile nei select)
 * @param boolean $file				true: mostra l'estensione finale del file
 * @return string
 */
function cutString($string, $max_char, $word_complete=true, $file=false)
{
	if($file)
	{
		$ext = strrchr($string, '.');
		$string = substr($string, 0, -strlen($ext));
		$string_new = $string.$ext;
	}
	else $string_new = $string;
	
	if(strlen($string) > $max_char){
		
		$cut_string = substr($string, 0, $max_char);
		
		if($word_complete)
		{
			$last_space = strrpos($cut_string, " ");
			$string_new = substr($cut_string, 0, $last_space);
		}
		else $string_new = $cut_string;
		
		$string_new .= "...";
		if($file) $string_new .= $ext;
		
		return $string_new;
	}
	
	return $string_new;
}

function baseFileName($filename) {
	$baseFile = '';
	$filename_a = explode(".", $filename);
	for($i=0, $limit=count($filename_a); $i<$limit-1; $i++) {
		$baseFile .= $filename_a[$i];
	}
	return $baseFile;
}

function listProv() {

	$list = array(
	"AG"=>'Agrigento',
	"AL"=>'Alessandria',
	"AN"=>'Ancona',
	"AO"=>'Aosta',
	"AR"=>'Arezzo',
	"AP"=>'Ascoli Piceno',
	"AT"=>'Asti',
	"AV"=>'Avellino',
	"BA"=>'Bari',
	"BT"=>'Barletta-Andria-Trani',
	"BG"=>'Bergamo',
	"BI"=>'Biella',
	"BL"=>'Belluno',
	"BN"=>'Benevento',
	"BO"=>'Bologna',
	"BZ"=>'Bolzano',
	"BS"=>'Brescia',
	"BR"=>'Brindisi',
	"CA"=>'Cagliari',
	"CL"=>'Caltanissetta',
	"CB"=>'Campobasso',
	"CI"=>'Carbonia-Iglesias',
	"CE"=>'Caserta',
	"CT"=>'Catania',
	"CZ"=>'Catanzaro',
	"CH"=>'Chieti',
	"CO"=>'Como',
	"CS"=>'Cosenza',
	"CR"=>'Cremona',
	"KR"=>'Crotone',
	"CN"=>'Cuneo',
	"EN"=>'Enna',
	"FM"=>'Fermo',
	"FE"=>'Ferrara',
	"FI"=>'Firenze',
	"FG"=>'Foggia',
	"FC"=>'Forlì-Cesena',
	"FR"=>'Frosinone',
	"GE"=>'Genova',
	"GO"=>'Gorizia',
	"GR"=>'Grosseto',
	"IM"=>'Imperia',
	"IS"=>'Isernia',
	"SP"=>'La Spezia',
	"AQ"=>'L\'Aquila',
	"LT"=>'Latina',
	"LE"=>'Lecce',
	"LC"=>'Lecco',
	"LI"=>'Livorno',
	"LO"=>'Lodi',
	"LU"=>'Lucca',
	"MC"=>'Macerata',
	"MN"=>'Mantova',
	"MS"=>'Massa-Carrara',
	"MT"=>'Matera',
	"ME"=>'Messina',
	"MI"=>'Milano',
	"MO"=>'Modena',
	"MB"=>'Monza e della Brianza',
	"NA"=>'Napoli',
	"NO"=>'Novara',
	"NU"=>'Nuoro',
	"OG"=>'Ogliastra',
	"OT"=>'Olbia-Tempio',
	"OR"=>'Oristano',
	"PD"=>'Padova',
	"PA"=>'Palermo',
	"PR"=>'Parma',
	"PV"=>'Pavia',
	"PG"=>'Perugia',
	"PU"=>'Pesaro e Urbino',
	"PE"=>'Pescara',
	"PC"=>'Piacenza',
	"PI"=>'Pisa',
	"PT"=>'Pistoia',
	"PN"=>'Pordenone',
	"PZ"=>'Potenza',
	"PO"=>'Prato',
	"RG"=>'Ragusa',
	"RA"=>'Ravenna',
	"RC"=>'Reggio Calabria',
	"RE"=>'Reggio Emilia',
	"RI"=>'Rieti',
	"RN"=>'Rimini',
	"RM"=>'Roma',
	"RO"=>'Rovigo',
	"SA"=>'Salerno',
	"VS"=>'Medio Campidano',
	"SS"=>'Sassari',
	"SV"=>'Savona',
	"SI"=>'Siena',
	"SR"=>'Siracusa',
	"SO"=>'Sondrio',
	"TA"=>'Taranto',
	"TE"=>'Teramo',
	"TR"=>'Terni',
	"TO"=>'Torino',
	"TP"=>'Trapani',
	"TN"=>'Trento',
	"TV"=>'Treviso',
	"TS"=>'Trieste',
	"UD"=>'Udine',
	"VA"=>'Varese',
	"VE"=>'Venezia',
	"VB"=>'Verbano-Cusio-Ossola',
	"VC"=>'Vercelli',
	"VR"=>'Verona',
	"VV"=>'Vibo Valentia',
	"VI"=>'Vicenza',
	"VT"=>'Viterbo'
	);

	return $list;
}

function share($site, $url, $title=null, $description=null) {
	
	$buffer = '';
	
	if($site=='facebook') {

		$buffer = "<a name=\"fb_share\" type=\"button_count\" share_url=\"$url\" href=\"http://www.facebook.com/sharer.php\">Share</a><script src=\"http://static.ak.fbcdn.net/connect.php/js/FB.Share\" type=\"text/javascript\"></script>";
		
		//$buffer = "<iframe src=\"http://www.facebook.com/plugins/like.php?href=".urlencode($url)."&amp;layout=standard&amp;show_faces=true&amp;width=450&amp;action=like&amp;colorscheme=light&amp;height=80\" scrolling=\"no\" frameborder=\"0\" style=\"border:none; overflow:hidden; width:450px; height:80px;\" allowTransparency=\"true\"></iframe>";
	}
	elseif($site=='twitter') {
		$buffer = "<a href=\"http://twitter.com/home?status=Currentlyreading ".urlencode($url)."\" title=\""._("condividi su Twitter")."\"><img src=\"".SITE_IMG."/share_twitter.jpg\" alt=\"Share on Twitter\"></a>";
	}
	elseif($site=='linkedin') {
		$buffer = "<a href=\"http://www.linkedin.com/shareArticle?mini=true&url=".urlencode($url)."&title=".urlencode($title)."&source=".urlencode(pub::variable('head_title'))."\"><img src=\"".SITE_IMG."/share_linkedin.jpg\" alt=\"Share on LinkedIn\"></a>";
	}
	elseif($s=='googleplus') {
		$buffer = "<g:plusone size=\"small\" width=\"90\"></g:plusone><script type=\"text/javascript\">(function() { var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true; po.src = 'https://apis.google.com/js/plusone.js'; var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s); })();</script>";
	}

	return $buffer;

}

function shareAll($social, $url, $title=null, $description=null) {

	if($social==="all") $social = array("facebook", "twitter", "linkedin", "digg", "googleplus");

	$items = array();
	foreach($social as $s) {
		if($s=='facebook') {
			$items[] = "<a name=\"fb_share\" type=\"button_count\" share_url=\"$url\" href=\"http://www.facebook.com/sharer.php\">Share</a><script src=\"http://static.ak.fbcdn.net/connect.php/js/FB.Share\" type=\"text/javascript\"></script>";	
		}
		elseif($s=='twitter') {
			$items[] = "<a href=\"http://twitter.com/home?status=Currentlyreading ".urlencode($url)."\" title=\""._("condividi su Twitter")."\"><img src=\"".SITE_IMG."/share_twitter.jpg\" alt=\"Share on Twitter\"></a>";
		}
		elseif($s=='linkedin') {
			$items[] = "<a href=\"http://www.linkedin.com/shareArticle?mini=true&url=".urlencode($url)."&title=".urlencode($title)."&source=".urlencode(pub::variable('head_title'))."\"><img src=\"".SITE_IMG."/share_linkedin.jpg\" alt=\"Share on LinkedIn\"></a>";
		}
		elseif($s=='googleplus') {
			$items[] = "<g:plusone size=\"small\" width=\"90\"></g:plusone><script type=\"text/javascript\">(function() { var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true; po.src = 'https://apis.google.com/js/plusone.js'; var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s); })();</script>";
		}
		elseif($s=='digg') {
			$items[] = "<a href=\"http://digg.com/submit?phase=2&amp;url=".$url."&amp;title=".$title."\"><img src=\"".SITE_IMG."/share_digg.png\" alt=\"Share on LinkedIn\"></a>";
		}
	}

	$buffer = implode(" ", $items);

	return "<div class=\"share\">".$buffer."</div>";
}

function traslitterazione($numero, $decimale=false)
{
    $unita          = array("","uno","due","tre","quattro","cinque","sei","sette","otto","nove");
    $decina1        = array("dieci","undici","dodici","tredici","quattordici","quindici","sedici","diciassette","diciotto","diciannove");
    $decine         = array("","dieci","venti","trenta","quaranta","cinquanta","sessanta","settanta","ottanta","novanta");
    $decineTroncate = array("","","vent","trent","quarant","cinquant","sessant","settant","ottant","novant");
    $centinaia      = array("","cento","duecento","trecento","quattrocento","cinquecento","seicento","settecento","ottocento","novecento");
 
    $numero = str_replace(',', '.', $numero);        // in modo da uniformare
    $separa = explode('.', $numero);
    if(sizeof($separa) > 0)
    {
            $intero = $separa[0];
            $decimale = ($decimale && sizeof($separa) == 2) ? $separa[1] : null;
    }
    
    // Inizializzo variabile contenente il risultato
    $risultato = "";
 
    // Faccio padding a 9 cifre
    $stringa = str_pad($intero, 9, "0", STR_PAD_LEFT);
 
    // Per ogni gruppo di tre cifre faccio il conto
    for($i=0;$i<9;$i=$i+3)
    {
        // Uso una variabile temporanea
        $tmp = "";
 
        // Centinaia
        $tmp .= $centinaia[$stringa[$i]];      
 
        // Decine da 2 a 9
        if($stringa[$i+1] != "1")
        {
            if($stringa[$i+2] == "1" || $stringa[$i+2] == "8")
                $tmp = $tmp . $decineTroncate[$stringa[$i+1]];
            else
                $tmp = $tmp . $decine[$stringa[$i+1]];     
 
            $tmp = $tmp . $unita[$stringa[$i+2]];
        }
        else // Undici, dodici, tredici, ecc...
        {
            $tmp .= $decina1[$stringa[$i+2]];
        }
 
        // Aggiungo suffissi quando necessario
        if($tmp != "" && $i==0)
                    $tmp .= "milioni";
 
        if($tmp != "" && $i==3)
                    $tmp .= "mila";
 
        // Aggiungo a risultato finale
        $risultato .= $tmp;
 
        // Caso speciale "mille" / "un milione" -> RISOLVE BUG "unmilioneunomilauno"
        if($i == 0 && $stringa[$i] == "0" && $stringa[$i+1] == "0")
            $risultato = str_replace("unomilioni","unmilione",$risultato);
        if($i == 3 && $stringa[$i] == "0" && $stringa[$i+1] == "0")
            $risultato = str_replace("unomila","mille",$risultato);
    }
 
    // ZERO!
    if($risultato == "")
        return "zero";
    else
    {
            if($decimale)
                    $risultato = $risultato.'/'.$decimale;
            return  $risultato;
    }
}

?>
