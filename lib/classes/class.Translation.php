<?php
/**
 * @file class.translation.php
 * @brief Contiene la classe translation
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

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

  private $_registry;
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

    $this->_registry = registry::instance();
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
			$records = $this->_db->select('text', $this->_tbl_translation, "tbl='$table' AND field='$field' AND tbl_id_value='$reference' AND language='".$this->_lng."'");
			if(count($records))
			{
				foreach($records AS $r)
				{
					$text = $r['text'];
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

	/**
	 * Form per l'inserimento e la modifica delle traduzioni
	 * 
	 * @see $_access_user
	 * @return print
	 * 
	 * Il metodo viene richiamato da una request ajax avviata dalla funzione javascript prepareTrlForm().
	 */
	public function formTranslation() {
	 	
	 	$lng_code = cleanVar($_POST, 'lng_code', 'string', '');
	 	$tbl = cleanVar($_POST, 'tbl', 'string', '');
	 	$field = cleanVar($_POST, 'field', 'string', '');
	 	$type = cleanVar($_POST, 'type', 'string', '');
	 	$id_value = cleanVar($_POST, 'id_value', 'int', '');
	 	$width = cleanVar($_POST, 'width', 'string', '');
	 	$fck_toolbar = cleanVar($_POST, 'fck_toolbar', 'string', '');
	 	
	 	$myform = loader::load('Form', array('gform', 'post', true));
	 	
    	$rows = $this->_registry->db->select('text', TBL_TRANSLATION, "tbl_id_value='$id_value' AND tbl='$tbl' AND field='$field' AND language='$lng_code'");
	 	if($rows and count($rows))
		{
			foreach($rows AS $row) {
				if($type == 'input' || $type == 'textarea') $text = htmlInput($row['text']);
				elseif($type == 'fckeditor') $text = htmlInputEditor($row['text']);
			}
			$action = 'modify';
		}
		else {
			$text = '';
			$action = 'insert';
		}
	 	
	 	$GINO = "<div style=\"margin-top:10px;\">";
    	$GINO .= "<p>";
	 	
		$url = $this->_registry->pub->getPtUrl().'&save=1';
		$onclick = "gino.translations.callAction('".$url."', '$type', '$tbl', '$field', '$id_value', false, '$lng_code', '$action')";
	 	
	 	if($type == 'input') {
	 		$GINO .= $myform->input('trnsl_'.$field, 'text', $text, array("size"=>$width, "id"=>'trnsl_'.$field));
	 	}
	 	elseif($type == 'textarea') {
			$GINO .= $myform->textarea('trnsl_'.$field, $text, array("cols"=>$width, "rows"=>4, "id"=>'trnsl_'.$field));
	 	}
	 	elseif($type == 'fckeditor') {
		  $onclick = "gino.translations.callAction('".$url."', '$type', '$tbl', '$field', '$id_value', true, '$lng_code', '$action')";
			
			//$GINO .= $myform->textarea('trnsl_'.$field, $text, array("cols"=>40, "rows"=>4, "id"=>'trnsl_'.$field));
      $GINO .= $myform->editorHtml('trnsl_'.$field, $text, $fck_toolbar, '100%', 300);
	 	}
	 	$onclick = "onclick=\"$onclick\"";

    	$GINO .= "</p>";
	 	
		$GINO .= "<p>".$myform->input('submit', 'button', _("applica"), array("classField"=>"submit", "js"=>$onclick))."</p>";

		$GINO .= "</div>";

	 	echo $GINO;
	 	exit();
	 }

  /**
	 * Inserimento e la modifica delle traduzioni
	 * 
	 * @see $_access_2
	 */
	public function actionTranslation() {
	 	
	 	$action = cleanVar($_POST, 'action', 'string', '');
		$type = cleanVar($_POST, 'type', 'string', '');
		if($type == 'input' || $type == 'textarea') {
			$text = cleanVar($_POST, 'text', 'string', '');
		}
		elseif($type == 'fckeditor') {
			
			$text = cleanVarEditor($_POST, 'text', '');
			
			// Verificare (old version) -> combinata con ...&text='+escape(CKEDITOR.instances... (riga 902)
			//$text = utf8_urldecode($text);
		}
	 	$lng_code = cleanVar($_POST, 'lng_code', 'string', '');
	 	$tbl = cleanVar($_POST, 'tbl', 'string', '');
	 	$field = cleanVar($_POST, 'field', 'string', '');
	 	$id_value = cleanVar($_POST, 'id_value', 'int', '');

	 	if($action == 'insert') {
      $res = $this->_registry->db->insert(array(
        'tbl_id_value' => $id_value,
        'tbl' => $tbl,
        'field' => $field,
        'language' => $lng_code,
        'text' => $text
      ), TBL_TRANSLATION);
	 	}
	 	elseif($action == 'modify') {
      $res = $this->_registry->db->update(array(
        'tbl_id_value' => $id_value,
        'tbl' => $tbl,
        'field' => $field,
        'language' => $lng_code,
        'text' => $text
      ), TBL_TRANSLATION, "tbl_id_value='$id_value' AND tbl='$tbl' AND field='$field' AND language='$lng_code'");
	 	}
	 	
	 	exit();
	}


	/**
	 * Elimina una traduzione
	 * 
	 * @param string $tbl nome della tabella con il campo da tradurre
	 * @param integer $tbl_id valore dell'ID del record di riferimento per la traduzione
	 * @return boolean
	 */
	public static function deleteTranslations($tbl, $tbl_id) {
	 	
		$db = db::instance();
		$result = $tbl_id == 'all'
		? $db->delete(TBL_TRANSLATION, "tbl='".$tbl."'")
		: $db->delete(TBL_TRANSLATION, "tbl='".$tbl."' AND tbl_id_value='".$tbl_id."'");

		return $result;
	}

}
?>
