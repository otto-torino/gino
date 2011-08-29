<?php

/*
 * SQL CODE TO LOUNCH IN MYSQL SERVER (case unsensitive replace function)
 *
DELIMITER $$

DROP FUNCTION IF EXISTS `replace_ci`$$
CREATE FUNCTION `replace_ci` ( str TEXT,needle CHAR(255),str_rep CHAR(255))
RETURNS TEXT
DETERMINISTIC
BEGIN
DECLARE return_str TEXT;
SELECT replace(lower(str),lower(needle),str_rep) INTO return_str;
RETURN return_str;
END$$

DELIMITER ;
 */

class search {

	private $_table;

	function __construct($table, $opts=array()) {
	
		$this->_table = $table;
		$this->_highlight_range = isset($opts['highlight_range']) ? $opts['highlight_range'] : 120;

	}

	private function clearSearchString($search_string) {

		$unconsidered = array("lo", "l", "il", "la", "i", "gli", "le", "uno", "un", "una", "un", "su", "sul", "sulla", "sullo", "sull", "in", "nel", "nello", "nella", "nell", "con", "di", "da", "dei", "d",  "della", "dello", "del", "dell", "che", "a", "dal", "è", "e", "per", "non", "si", "al", "ai", "allo", "all", "al", "o");

		$clean_string = strtolower($search_string);

		$clean_string = preg_replace("#\b(".implode("|", $unconsidered).")\b#", "", $clean_string);
		$clean_string = preg_replace("#\W|(\s+)#", " ", $clean_string);

		$clean_string = preg_quote($clean_string);
	
		return $clean_string;

	}

	private function getKeywords($search_string) {
		
		$clean_string = $this->clearSearchString($search_string);

		$empty_array = array(""," ");

		return  array_diff(array_unique(explode(" ", $clean_string)), $empty_array);

	}

	public function makeQuery($selected_fields, $required_clauses, $weight_clauses){
	
		$final_keywords = 0;

		$selected = array();
		foreach($selected_fields as $f) {
			$selected[] = is_array($f) ? $f['field'] : $f;
		}
		$relevance = "(";
		$occurrences = "(";
		$sqlwhere_r = "";
		$sqlwhere_w = "";
		$sql_where = '';
		foreach($required_clauses as $f=>$fp) {
			if(is_array($fp)) {
				if(isset($fp['inside']) && $fp['inside']) $sqlwhere_r .= "$f LIKE '%".$fp['value']."%' AND ";
				elseif(isset($fp['begin']) && $fp['begin']) $sqlwhere_r .= "$f LIKE '".$fp['value']."%' AND ";
				elseif(isset($fp['end']) && $fp['end']) $sqlwhere_r .= "$f LIKE '%".$fp['value']."' AND ";
				elseif(isset($fp['field']) && $fp['field']) $sqlwhere_r .= "$f=".$fp['value']." AND ";
				else $sqlwhere_r .= "$f='".$fp['value']."' AND ";
			}
			else {
				$sqlwhere_r .= "$f='$fp' AND ";
			}
		}
		foreach($weight_clauses as $f=>$fp) {
			$search_keywords = $this->getKeywords($fp['value']);
			$final_keywords += count($search_keywords);

			foreach($search_keywords as $keyw) {
				$occurrences .= "(LENGTH($f)-LENGTH(replace_ci($f,'$keyw','')))/LENGTH('$keyw') + ";
				if(isset($fp['inside']) && $fp['inside']) {
					$relevance .= "(INSTR($f, '".$keyw."')>0)*".$fp['weight']." + ";
					$sqlwhere_w .= "$f LIKE '%".$keyw."%' OR ";
				}
				else {
					$relevance .= "(($f REGEXP '[[:<:]]".$keyw."[[:>:]]')>0)*".$fp['weight']." + ";
					$sqlwhere_w .= "$f REGEXP '[[:<:]]".$keyw."[[:>:]]' OR ";
				}
			}
		}
		if($final_keywords) $sqlwhere_w = substr($sqlwhere_w, 0, strlen($sqlwhere_w)-4);
		$relevance .= "0)";
		$occurrences .= "0)";
		if($sqlwhere_r || $sqlwhere_w) {
			$sqlwhere = "WHERE ";
			if($sqlwhere_r) $sqlwhere .= $sqlwhere_r;
			if($sqlwhere_w) $sqlwhere .= "(".$sqlwhere_w.")";
			else $sqlwhere = substr($sqlwhere, 0, strlen($sqlwhere)-5);
		}
		$query = "SELECT ".implode(",", $selected).", $relevance AS relevance, $occurrences AS occurrences FROM $this->_table $sqlwhere ORDER BY relevance DESC, occurrences DESC";
			
		return $final_keywords ? $query : false;

	}

	public function getSearchResults($dbObj, $selected_fields, $required_clauses, $weight_clauses) {
	
		$res = array();

		$query = $this->makeQuery($selected_fields, $required_clauses, $weight_clauses);
		if($query===false) return array();
		$rows = $dbObj->selectquery($query);
		if(sizeof($rows)>0) {
			$i = 0;
			foreach($rows as $row) {
				$res[$i] = array(); 
				foreach($selected_fields as $f) {
					$res[$i]['relevance'] = $row['relevance'];
					$res[$i]['occurrences'] = $row['occurrences'];
					if(is_array($f) && isset($f['highlight']) && $f['highlight']) {
						$fp = $weight_clauses[$f['field']];
						$search_keywords = $this->getKeywords($fp['value']);
						$rexp = (isset($fp['inside']) && $fp['inside']) ? implode("|", $search_keywords) : "\b".implode("\b|\b", $search_keywords)."\b";
						if(preg_match("#(.|\n){0,$this->_highlight_range}($rexp)(.|\n){0,$this->_highlight_range}#ui", cutHtmlText($row[preg_replace("#.*?\.#", "", $f['field'])], 50000000, '', true, false, true), $matches)) {
							$res[$i][$f['field']] = preg_replace("#".$rexp."#i", "<span class=\"evidence\">$0</span>", $matches[0]);
						}
						else $res[$i][$f['field']] = '';
					}
					else $res[$i][$f] = $row[preg_replace("#.*?\.#", "", $f)];
				}
				$i++;
			}
		}

		return $res;
	
	}


}

?>
