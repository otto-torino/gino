<?php
/**
 * @file class.translation.php
 * @brief Contiene la classe translation
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

/**
 * @brief Gestisce le traduzioni
 * 
 * Una delle prime classi di gino, risale al 04/08/2004, rivista il 11/2008
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class translation
{
	private $_db;
	
	/**
	 * Lingua valida in sessione
	 */
	private $_lng;
	
	/**
	 * Lingua di default (se non viene trovata $_lng)
	 */
	private $_lngDft;
	
	private $_tbl_translation;
	
	function __construct($language, $languageDefault) {
	
		$this->_db = db::instance();
		
		$this->_lng = $language;
		$this->_lngDft = $languageDefault;
		
		$this->_tbl_translation = 'language_translation';
	}
	
	/**
	 * Testo di un campo tradotto nella lingua selezionata
	 * 
	 * Se non è presente la traduzione viene mostrato il testo nella lingua di default
	 * 
	 * @param string $table nome della tabella del testo da tradurre
	 * @param string $field nome del campo da tradurre
	 * @param mixed $reference valore del campo di rieferimento 
	 * @param string $id_name nome del campo di riferimento
	 * @return string
	 */
	public function selectTXT($table, $field, $reference, $id_name='id')
	{
		$dft_text = $this->_db->getFieldFromId($table, $field, $id_name, $reference);
		
		if($this->_lng == $this->_lngDft) return $dft_text;
		else
		{
			$query = "SELECT text FROM ".$this->_tbl_translation." WHERE tbl='$table' AND field='$field' AND tbl_id_value='$reference' AND language='".$this->_lng."'";
			$a = $this->_db->selectquery($query);
			if(sizeof($a) > 0)
			{
				foreach($a AS $b)
				{
					$text = $b['text'];
				}
				if(!empty($text)) return $text;
			}		
		}
		
		return $dft_text;	
	}
	
	/**
	 * Ordina i risultati di una query facendo riferimento ai testi tradotti
	 * 
	 * @param string $query query
	 * @param string $id_name nome del campo di riferimento
	 * @param string $tbl nome della tabella del testo da tradurre
	 * @param string $ord_field nome del campo da tradurre e in base al quale ordinare
	 * @param string $ord_type tipo di ordinamento (asc, desc)
	 * @return array
	 */
	public function listItemOrdered($query, $id_name, $tbl, $ord_field, $ord_type) {
		
		// get all id from query, ordered casually
		$ids = array();
		
		$a = $this->_db->selectquery($query);
		if(sizeof($a) > 0)
		{
			foreach($a AS $b)
			{
				$ids[] = $b[$id_name];
			}
		}
		
		// construct key($id) => value($sel_field) array
		$ids_field = array();
		
		foreach($ids as $id) {
			$ids_field[$id] = $this->selectTXT($tbl, $ord_field, $id, $id_name);
		}
		
		// ordering the final array
		($ord_type == 'desc') ? arsort($ids_field) : asort($ids_field);
		
		return $ids_field;
	}
}
?>
