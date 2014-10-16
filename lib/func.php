<?php
/**
 * @file func.php
 * @brief Racchiude le funzioni generali utilizzate da gino
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

/**
 * Include le librerie per il trattamento dei valori da e per un input/database
 */
include(LIB_DIR.OS.'func.var.php');

/**
 * Include le librerie per il riconoscimento browser
 */
include(LIB_DIR.OS.'func.browser.php');

/**
 * Ricava il percorso relativo a partire da un percorso assoluto
 * 
 * @param string $abspath percorso assoluto
 */
function relativePath($abspath) {
	$path = SITE_WWW.preg_replace("#".preg_quote(SITE_ROOT)."#", "", $abspath);
	
	if(OS=="\\") return preg_replace("#".preg_quote("\\")."#", "/", $path);
	
	return $path;
}

/**
 * Restituisce l'elemento di un array corrispondente alla chiave data oppure un valore di default 
 * 
 * @param string $opt_name nome della chiave
 * @param array $opt_array array associativo
 * @param mixed $default valore di default
 * @return l'elemento corrispondente alla chiave data oppure il default
 */
function gOpt($opt_name, $opt_array, $default) {

	return isset($opt_array[$opt_name]) ? $opt_array[$opt_name] : $default;
}

/**
 * Trasforma un array in un oggetto
 * 
 * @param array $array
 * @return object
 */
function arrayToObject($array) {
	
	$object = new stdClass();
	if (is_array($array) && count($array) > 0)
	{
		foreach ($array as $name=>$value)
		{
			$name = strtolower(trim($name));
			if(!empty($name)) {
				$object->$name = $value;
			}
		}
	}
	return $object;
}

/**
 * File contenuti in una directory
 * 
 * @param string $dir percorso della directory (se @a dir è un percorso relativo, verrà aperta la directory relativa alla directory corrente)
 * @return array
 */
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

/**
 * Gestisce il download di un file
 * 
 * @param string $full_path percorso del file
 * @return void
 */
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
 * Verifica se il file ha una estensione valida, ovvero presente nell'elenco delle estensioni.
 *
 * @param string $filename nome del file
 * @param array $extensions elenco delle estensioni valide
 * @return boolean
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

/**
 * Verifica la validità dell'indirizzo email
 * 
 * @param string $email indirizzo email
 * @return boolean
 */
function email_control($email) {
	
	return !preg_match("#^[a-z0-9_-]+[a-z0-9_.-]*@[a-z0-9_-]+[a-z0-9_.-]*\.[a-z]{2,5}$#", $email) ? false : true;
}

/**
 * Verifica la validità dell'indirizzo email
 * 
 * Di default verifica la corrispondenza dell'indirizzo email alle specifiche dello standard RFC-2822. 
 * 
 * @param string $value indirizzo email
 * @param mixed $regexp se presente verifica la corrispondenza di un indirizzo con una espressione regolare
 *   - tipo boolean, se vero attiva una espressione regolare restrittiva
 *   - tipo string, espressione regolare personalizzata
 * @return boolean
 */
function checkEmail($value, $regexp=null) {
	
	if(is_bool($regexp) && $regexp)
	{
		$check = preg_match("#^[0-9a-z]([-_.]?[0-9a-z])*@[0-9a-z]([-.]?[0-9a-z])*.[a-z]{2,4}$#", $value) ? true : false;
	}
	elseif(is_string($regexp))
	{
		$check = preg_match("#$regexp#", $value) ? true : false;
	}
	else
	{
		$check = filter_var($value, FILTER_VALIDATE_EMAIL);
	}
	return $check;
}

/**
 * Formatta la data per il database (YYYY-MM-DD)
 * 
 * @param string $date valore della data (DD/MM/YYYY), generalmente da input form
 * @param string $s separatore utilizzato nella data
 * @return string
 */
function dateToDbDate($date, $s='/') {

	if(!$date) return null;
	
	if(preg_match("#\\".$s."#", $date))
	{
		$date_array = explode($s, $date);
		return $date_array[2].'-'.$date_array[1].'-'.$date_array[0];
	}
	else return $date;
}

/**
 * Converte il formato della data da database (campo DATE) in un formato di facile visualizzazione (DD/MM[/YYYY])
 * 
 * @param mixed $db_date valore del campo date; string (YYYY-MM-DD) or object(DateTime)
 * @param string $s separatore utilizzato nella data
 * @param integer $num_year numero di cifre dell'anno da mostrare
 * @return string
 */
function dbDateToDate($db_date, $s='/', $num_year=4) {
	
	if(is_object($db_date))
		$db_date = $db_date->format('Y-m-d');
	
	if(empty($db_date) || $db_date=='0000-00-00') return '';
	$date_array = explode('-', $db_date);
	$year = substr($date_array[0], -$num_year);
	return $date_array[2].$s.$date_array[1].$s.$year;
}

/**
 * Converte il formato della data da database (campo DATETIME) in un formato di facile visualizzazione (DD/MM[/YYYY])
 * 
 * @param mixed $datetime valore del campo datetime; string (YYYY-MM-DD HH:MM:SS) or object(DateTime)
 * @param string $s separatore utilizzato nella data
 * @param integer $num_year numero di cifre dell'anno da mostrare
 * @return string
 */
function dbDatetimeToDate($datetime, $s='/', $num_year=4) {
	
	if(is_object($datetime))
	{
		$date = $datetime->format('Y-m-d');
	}
	else
	{
		$datetime_array = explode(" ", $datetime);
		$date = $datetime_array[0];
	}
	
	return dbDateToDate($date, $s, $num_year);
}

/**
 * Riporta l'orario di un campo DATETIME (HH:MM:SS)
 * 
 * @param mixed $datetime valore del campo datetime; string (YYYY-MM-DD HH:MM:SS) or object(DateTime)
 * @return string
 */
function dbDatetimeToTime($datetime) {
	
	if(is_object($datetime))
	{
		$time = $datetime->format('H:i:s');
	}
	else
	{
		$datetime_array = explode(" ", $datetime);
		$time = $datetime_array[1];
	}
	
	return $time;
}

/**
 * Mostra l'orario (HH:MM[:SS])
 * 
 * @param mixed $db_time valore del campo time o dell'output della funzione dbDatetimeToTime; string (HH:MM:SS) or object(DateTime)
 * @param boolean $seconds visualizzazione dei secondi
 * @return string
 */
function dbTimeToTime($db_time, $seconds=false) {
	
	if(is_object($db_time))
		$db_time = $db_time->format('H:i:s');
	
	if(empty($db_time) || $db_time=='00:00:00') return '';
	if(!$seconds)
		$db_time = substr($db_time, 0, 5);
	return $db_time;
}

/**
 * Formatta l'orario per il database (HH:MM:SS)
 * 
 * @param string $time orario ([00][{,|:}00][{,|:}00])
 * @return string
 */
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

/**
 * Formatta un numero con il raggruppamento delle centinaia
 * 
 * @code
 * number_format(float $number, int $decimals = 0, string $dec_point = ',', string $thousands_sep = '.')
 * @endcode
 * 
 * @param float $number numero
 * @param integer $decimals numero di decimali
 * @return string
 */
function dbNumberToNumber($number, $decimals=2)
{
	if(!empty($number))
		$number = number_format($number, $decimals, ',', '.');
	
	return $number;
}

/**
 * Formatta un numero per il database (il separatore decimale è il punto)
 * 
 * @param string $number numero
 * @return float
 */
function numberToDB($number)
{
	$number = str_replace(',', '.', $number);
	return $number;
}

/**
 * Controlla se una variabile è un numero o una stringa numerica
 * 
 * @param mixed $variable valore della variabile (string|integer|float)
 * @return boolean
 */
function isNumeric($variable)
{
	if(empty($variable)) return true;
	
	if(!ereg("^[0-9\,\.]+$", $variable)) return false;
	
	if(is_numeric(numberToDB($variable))) return true; else return false;
}

/**
 * Calcola l'intervallo di tempo in secondi tra due valori datetime
 * 
 * @param string $firstTime datetime iniziale
 * @param string $lastTime datetime finale
 * @return integer
 */
function timeDiff($firstTime, $lastTime){
	
	if(!$lastTime)
		return null;
	
	// converte in unix timestamp
	$firstTime = strtotime($firstTime);
	$lastTime = strtotime($lastTime);
	
	$timeDiff = $lastTime-$firstTime;
	
	return $timeDiff;
}

/**
 * Calcola la differenza di tempo tra due datetime in più formati
 * 
 * @param string $interval indica il tipo di numero da ricavare, accetta i valori:
 *   - @b yyyy - numero totale di anni
 *   - @b q - numero totale di quarti
 *   - @b m - numero totale di mesi
 *   - @b y - differenza tra numero di giorni (ad es. 1st Jan 2004 è "1", il primo giorno. 2nd Feb 2003 è "33". La differenza è "-32".)
 *   - @b d - numero totale di giorni
 *   - @b w - numero totale di giorni della settimana
 *   - @b ww - numero totale di settimane
 *   - @b h - numero totale di ore
 *   - @b n - numero totale di minuti
 *   - @b s - numero totale di secondi (default)
 * @param string $datefrom datetime iniziale
 * @param string $dateto datetime finale
 * @param boolean $using_timestamps indica se i valori di $datefrom e $dateto sono in formato timestamp (default false)
 * @return integer
 */
function dateDiff($interval, $datefrom, $dateto, $using_timestamps=false) {
    
	if (!$using_timestamps) {
		$datefrom = strtotime($datefrom, 0);
		$dateto = strtotime($dateto, 0);
	}
	$difference = $dateto - $datefrom; // differenza in secondi

	switch($interval) {
     
	case 'yyyy':
		$years_difference = floor($difference / 31536000);
		if (mktime(date("H", $datefrom), date("i", $datefrom), date("s", $datefrom), date("n", $datefrom), date("j", $datefrom), date("Y", $datefrom)+$years_difference) > $dateto) {
			$years_difference--;
		}
		if (mktime(date("H", $dateto), date("i", $dateto), date("s", $dateto), date("n", $dateto), date("j", $dateto), date("Y", $dateto)-($years_difference+1)) > $datefrom) {
			$years_difference++;
		}
		$datediff = $years_difference;
		break;

	case "q":
		$quarters_difference = floor($difference / 8035200);
		while (mktime(date("H", $datefrom), date("i", $datefrom), date("s", $datefrom), date("n", $datefrom)+($quarters_difference*3), date("j", $dateto), date("Y", $datefrom)) < $dateto) {
			$months_difference++;
		}
		$quarters_difference--;
		$datediff = $quarters_difference;
		break;

	case "m":
		$months_difference = floor($difference / 2678400);
		while (mktime(date("H", $datefrom), date("i", $datefrom), date("s", $datefrom), date("n", $datefrom)+($months_difference), date("j", $dateto), date("Y", $datefrom)) < $dateto) {
			$months_difference++;
		}
		$months_difference--;
		$datediff = $months_difference;
		break;

	case 'y':
		$datediff = date("z", $dateto) - date("z", $datefrom);
		break;

	case "d":
		$datediff = floor($difference / 86400);
		break;

	case "w":
		$days_difference = floor($difference / 86400);
		$weeks_difference = floor($days_difference / 7); // Complete weeks
		$first_day = date("w", $datefrom);
		$days_remainder = floor($days_difference % 7);
		$odd_days = $first_day + $days_remainder; // Do we have a Saturday or Sunday in the remainder?
		if($odd_days > 7) { // domenica
			$days_remainder--;
		}
		if($odd_days > 6) { // sabato
			$days_remainder--;
		}
		$datediff = ($weeks_difference * 5) + $days_remainder;
		break;

	case "ww":
		$datediff = floor($difference / 604800);
		break;

	case "h":
		$datediff = floor($difference / 3600);
		break;

	case "n":
		$datediff = floor($difference / 60);
		break;

	default:
		$datediff = $difference;
		break;
	}    

	return $datediff;
}

/**
 * Calcola la differenza di tempo tra due datetime in più formati
 * 
 * @param string $start_date datetime iniziale
 * @param string $end_date datetime finale (default now)
 * @param array $options
 *   array associativo di opzioni
 *   - @b diff (string): tipo di output
 *     - @a s, differenza in secondi (default)
 *     - @a i, differenza in minuti
 *     - @a h. differenza in ore
 * @return integer
 * 
 * Utilizza la classe DateTime.
 */
function getDateDiff($start_date, $end_date=null, $options=array()) {
	
	$get_diff = gOpt('diff', $options, 's');
	
	if(!$end_date) $end_date = date("Y-m-d H:i:s");
	
	$start_date = new DateTime($start_date);
	$end_date = new DateTime($end_date);
	
	$since_start = $start_date->diff($end_date);	// DateInterval object
	
	$days_tot = $since_start->days;	// total days (for example 1837)
	/*
	$diff_years = $since_start->y;
	$diff_months = $since_start->m;
	$diff_days = $since_start->d;
	$diff_hours = $since_start->h;
	$diff_minutes = $since_start->i;
	$diff_seconds = $since_start->s;
	*/
	
	$hours = $since_start->days * 24 * 60;
	$hours += $since_start->h;
	if($get_diff == 'h') return $hours;
	
	$minutes += $hours * 60;
	$minutes += $since_start->i;
	if($get_diff == 'i') return $minutes;
	
	$seconds = $minutes * 60;
	$seconds += $since_start->s;
	return $seconds;
}

/**
 * Verifica se il valore della variabile è conforme al tipo di controllo indicato
 * 
 * @param string $type tipo di controllo da eseguire
 *   - @b IP: indirizzo IP
 *   - @b URL: indirizzo URL
 *   - @b Email: indirizzo email
 *   - @b ISBN: codice ISBN
 *   - @b Date: data
 *   - @b Time: orario
 *   - @b HexColor: codice colore esadecimale
 * @param string $var valore della variabile
 * @return boolean
 */
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
 * Accorcia un testo HTML alla lunghezza desiderata (length)
 * 
 * Inoltre sostituisce l'ultimo carattere con il valore ending se il testo è più lungo di length. Può strippare i TAG.
 * 
 * @param string $html stringa HTML da accorciare
 * @param integer $length lunghezza della stringa da riportare, incluse le ellissi
 * @param string $ending finale da aggiungere alla stringa accorciata
 * @param boolean $strip_tags se vero, i TAG HTML saranno sostituiti da niente
 * @param boolean $cut_words se falso, l'ultima parola della stringa non sarà tagliata
 * @param boolean $cut_images se vero, la stringa non conterrà immagini
 * @param array $options
 *   array associativo di opzioni
 *   - @b endingPosition (string) [in|out]: posizionamento dei caratteri dell'ending nella struttura html o al di fuori della struttura html (dopo che sono stati chiusi tutti i TAG)
 * @return string
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
 * @param string $string testo da accorciare
 * @param integer $max_char numero massimo di caratteri
 * @param boolean $word_complete se vero, mantiene l'ultima parola completa (utile nei select)
 * @param boolean $file se vero, mostra l'estensione finale del file
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

/**
 * Testo a capo dopo un dato numero di caratteri
 * 
 * @param string $string testo da trattare
 * @param integer $max_char numero massimo di caratteri per riga
 * @return string
 */
function textToHead($string, $max_char) {
	
	if($string && strlen($string) > $max_char)
	{
		$array = str_split($string, $max_char);
		$string = implode("<br />", $array);
	}
	return $string;
}

/**
 * Ricava il nome del file senza l'estensione
 * @param string $filename nome del file
 * @return string
 */
function baseFileName($filename) {
	$baseFile = '';
	$filename_a = explode(".", $filename);
	for($i=0, $limit=count($filename_a); $i<$limit-1; $i++) {
		$baseFile .= $filename_a[$i];
	}
	return $baseFile;
}

/**
 * Elenco delle province
 * @return array (sigla=>capoluogo)
 */
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

/**
 * Ritorna un indirizzo della condivisione per i social network
 * 
 * @param string $site tipo di condivisione (facebook, twitter, linkedin, googleplus)
 * @param string $url indirizzo da condividere
 * @param string $title titolo della condivisione
 * @param string $description descrizione
 * @return string
 */
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

/**
 * Ritorna gli indirizzi delle condivisioni per i social network
 * 
 * @param array $social elenco delle tipologie di condivisione (facebook, twitter, linkedin, googleplus, digg); col valore @a all vengono mostrate tutte le condivisioni
 * @param string $url indirizzo da condividere
 * @param string $title titolo della condivisione
 * @param string $description descrizione
 * @return string
 */
function shareAll($social, $url, $title=null, $description=null) {

    $registry = registry::instance();

    $all = array("facebook", "twitter", "linkedin", "digg", "googleplus");
    $st_all = array('sharethis', 'facebook', 'twitter', 'linkedin', 'googleplus', 'reddit', 'pinterest', 'tumblr', 'digg', 'delicious', 'evernote', 'google_reader', 'email');
    $st_all_large = array('sharethis_large', 'facebook_large', 'twitter_large', 'linkedin_large', 'googleplus_large', 'reddit_large', 'pinterest_large', 'tumblr_large', 'digg_large', 'delicious_large', 'evernote_large', 'google_reader_large', 'email_large');
    $display_text = array(
        'sharethis' => 'ShareThis', 
        'facebook' => 'Facebook',
        'twitter' => 'Twitter',
        'linkedin' => 'LinkedIn', 
        'googleplus' => 'Google +', 
        'reddit' => 'Reddit', 
        'pinterest' => 'Pinterest', 
        'tumblr' => 'Tumblr', 
        'digg' => 'Digg', 
        'delicious' => 'Delicious', 
        'evernote' => 'Evernote', 
        'google_reader' => 'Google Reader', 
        'email' => 'Email'
    );

    if($social==="all") {
        $social = $all;
    }
    else if($social==="st_all") {
        $social = $st_all;
    }
    else if($social==="st_all_large") {
        $social = $st_all_large;
    }

    $items = array();

    if($registry->sysconf->sharethis_public_key) {
        foreach($social as $s) {
            $items[] = "<span class=\"st_".$s."\" displayText=\"".$display_text[preg_replace('#_large#', '', $s)]."\"></span>";
        }
    }
    else {
        foreach($social as $s) {
            if($s=='facebook') {
                $items[] = "<a name=\"fb_share\" type=\"button_count\" share_url=\"$url\" href=\"http://www.facebook.com/sharer.php\">Share</a><script src=\"http://static.ak.fbcdn.net/connect.php/js/FB.Share\" type=\"text/javascript\"></script>";	
            }
            elseif($s=='twitter') {
                $items[] = "<a href=\"http://twitter.com/home?status=Currentlyreading ".urlencode($url)."\" title=\""._("condividi su Twitter")."\"><img src=\"".SITE_IMG."/share_twitter.jpg\" alt=\"Share on Twitter\"></a>";
            }
            elseif($s=='linkedin') {
                $items[] = "<a href=\"http://www.linkedin.com/shareArticle?mini=true&url=".urlencode($url)."&title=".urlencode($title)."&source=".urlencode($registry->sysconf->head_title)."\"><img src=\"".SITE_IMG."/share_linkedin.jpg\" alt=\"Share on LinkedIn\"></a>";
            }
            elseif($s=='googleplus') {
                $items[] = "<g:plusone size=\"small\" width=\"90\"></g:plusone><script type=\"text/javascript\">(function() { var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true; po.src = 'https://apis.google.com/js/plusone.js'; var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s); })();</script>";
            }
            elseif($s=='digg') {
                $items[] = "<a href=\"http://digg.com/submit?phase=2&amp;url=".$url."&amp;title=".$title."\"><img src=\"".SITE_IMG."/share_digg.png\" alt=\"Share on LinkedIn\"></a>";
            }
        }
    }

    $buffer = implode(" ", $items);

    return "<div class=\"share\">".$buffer."</div>";
}

/**
 * Converte un numero in cifre
 * 
 * @param mixed $numero valore da convertire (float|integer)
 * @param boolean $decimale se vero, mostra il decimale ([/00])
 * @return string
 */
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
