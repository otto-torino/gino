<?php
/**
 * @file class.Search.php
 * @brief Contiene la definizione ed implementazione della classe Gino.Search
 * 
 * @copyright 2005-2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

namespace Gino;

/**
 * @brief Libreria per ricerche full text pesate sulle tabelle
 * 
 * Codice SQL da eseguire sul database MySQL
 * @code
 * DELIMITER $$
 * 
 * DROP FUNCTION IF EXISTS `replace_ci`$$
 * CREATE FUNCTION `replace_ci` ( str TEXT,needle CHAR(255),str_rep CHAR(255))
 * RETURNS TEXT
 * DETERMINISTIC
 * BEGIN
 * DECLARE return_str TEXT;
 * SELECT replace(lower(str),lower(needle),str_rep) INTO return_str;
 * RETURN return_str;
 * END$$
 * 
 * DELIMITER ;
 * @endcode
 * 
 * @copyright 2005-2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class Search {

    private $_table;

    /**
     * @brief Costruttore
     *
     * @param string $table testo del FROM in una query SQL (ad esempio: page AS p, page_block AS pb)
     * @param array $opts
     *   array associativo di opzioni
     *   - @b highlight_range (integer)
     * @return istanza di Gino.Search
     */
    function __construct($table, $opts=array()) {

        $this->_table = $table;
        $this->_highlight_range = isset($opts['highlight_range']) ? $opts['highlight_range'] : 120;
    }

    /**
     * @brief Ripulisce la stringa di ricerca
     * @description Elimina parole con poco significato
     * @param string $search_string
     * @return stringa ripulita
     */
    private function clearSearchString($search_string) {

        $unconsidered = array("lo", "l", "il", "la", "i", "gli", "le", "uno", "un", "una", "un", "su", "sul", "sulla", "sullo", "sull", "in", "nel", "nello", "nella", "nell", "con", "di", "da", "dei", "d",  "della", "dello", "del", "dell", "che", "a", "dal", "è", "e", "per", "non", "si", "al", "ai", "allo", "all", "al", "o");

        $clean_string = strtolower($search_string);

        $clean_string = preg_replace("#\b(".implode("|", $unconsidered).")\b#", "", $clean_string);
        $clean_string = preg_replace("#\W|(\s+)#", " ", $clean_string);

        $clean_string = preg_quote($clean_string);

        return $clean_string;
    }

    /**
     * @brief Ricava le parole chiave da una stringa di ricerca
     * @param string $search_string
     * @return array di parole chiave
     */
    private function getKeywords($search_string) {

        $clean_string = $this->clearSearchString($search_string);

        $empty_array = array(""," ");

        return  array_diff(array_unique(explode(" ", $clean_string)), $empty_array);
    }

    /**
     * @brief Costruisce la query di una ricerca full text
     * 
     * @param array $selected_fields campi da selezionare nella ricerca (costruzione del SELECT), ad esempio
     *   @code
     *   array("p.item_id", array("highlight"=>true, "field"=>"p.title"), array("highlight"=>true, "field"=>"p.subtitle"), array("highlight"=>true, "field"=>"pb.text"))
     *   @endcode
     * @param array $required_clauses tipo di ricerca sul testo (costruzione del WHERE), ad esempio
     *   @code
     *   array("p.item_id"=>array("field"=>true, "value"=>"pb.item"))
     *   @endcode
     * @param array $weight_clauses (costruzione del WHERE e rilevanza dei risultati), ad esempio
     *   @code
     *   array("p.title"=>array("weight"=>3), "p.subtitle"=>array("weight"=>2), "pb.text"=>array("weight"=>1))
     *   @endcode
     * @return query
     */
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

    /**
     * @brief Risultati di una ricerca full text
     *
     * @see self::makeQuery()
     * @param object $dbObj istanza del database (db::instance())
     * @param array $selected_fields campi da selezionare nella ricerca
     * @param array $required_clauses tipo di ricerca sul testo
     * @param array $weight_clauses
     * @return array di risultati, ciascun risultato è un array con chiavi relevance, occurrences, <field-name>
     */
    public function getSearchResults($dbObj, $selected_fields, $required_clauses, $weight_clauses) {

        $res = array();

        $query = $this->makeQuery($selected_fields, $required_clauses, $weight_clauses);
        if($query === false) return array();
        $rows = $dbObj->select(null, null, null, array('custom_query'=>$query));
        if($rows and sizeof($rows)>0) {
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
