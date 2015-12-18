<?php
/**
 * @file class.Translation.php
 * @brief Contiene la definizione ed implementazione della classe Gino.Translation
 * 
 * @copyright 2005-2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

/**
 * @brief Classe per la gestione delle traduzioni
 *
 * Le traduzioni vengono cercate nella lingua di navigazione. Se non presenti viene restituita la traduzione nella lingua di default.
 * La lingua di navigazione è quella ricavata dallo user agent del client, oppure impostata in sessione a seguito di scelta dell'utente.
 * La lingua di default è quella impostata come tale da interfaccia.
 *
 * @copyright 2005-2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class Translation {

    private $_registry;
    private $_db;

    /** Lingua valida in sessione */
    private $_lng;

    /** Lingua di default (se non viene trovata $_lng) */
    private $_lngDft;

    /** tabella che conserva le traduzioni */
    private $_tbl_translation;

    /**
     * @brief Costruttore
     * @param string $language lingua di navigazione
     * @param string $language_dft lingua di default
     * @return istanza di Gino.Translation
     */
    function __construct($language, $language_dft) {

        $this->_registry = Registry::instance();
        $this->_db = Db::instance();

        $this->_lng = $language;
        $this->_lngDft = $language_dft;

        $this->_tbl_translation = 'language_translation';
    }

    /**
     * @brief Traduzione di un campo di tabella
     *
     * Se non è presente la traduzione viene mostrato il testo nella lingua di default
     *
     * @param string $table nome della tabella del testo da tradurre
     * @param string $field nome del campo da tradurre
     * @param mixed $reference valore del campo di rieferimento 
     * @param string $id_name nome del campo di riferimento
     * @return traduzione
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
     * @brief Gestisce le traduzioni nei form
     * 
     * @param object $request oggetto Request
     * @return oggetto Response o null
     */
    public function manageTranslation($request) {
    	
    	Loader::import('class/http', '\Gino\Http\ResponseNotFound');
    	
    	if(!$request->checkGETKey('trnsl', '1'))
    		return new \Gino\Http\ResponseNotFound();
		
    	if($request->checkGETKey('save', '1')) {
			
			$res = $this->actionTranslation($request);
			$content = $res ? _("operazione riuscita") : _("errore nella compilazione");
			return new \Gino\Http\Response($content);
		}
		else {
			return new \Gino\Http\Response($this->formTranslation());
		}
    }

    /**
     * @brief Ordina i risultati di una query facendo riferimento ai testi tradotti
     * 
     * @param string $query query
     * @param string $id_name nome del campo di riferimento
     * @param string $tbl nome della tabella del testo da tradurre
     * @param string $ord_field nome del campo da tradurre e in base al quale ordinare
     * @param string $ord_type tipo di ordinamento (asc, desc)
     * @return risultati ordinati
     */
    public function listItemOrdered($query, $id_name, $tbl, $ord_field, $ord_type) {

        // get all id from query, ordered casually
        $ids = array();

        $a = $this->_db->select(null, null, null, array('custom_query'=>$query));
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
     * @brief Form per l'inserimento e la modifica delle traduzioni
     *
     * Il metodo viene richiamato da una request ajax avviata dalla funzione javascript prepareTrlForm().
     *
     * @return form inserimento traduzione
     */
    public function formTranslation() {
        
		$lng_code = cleanVar($_POST, 'lng_code', 'string', '');
        $tbl = cleanVar($_POST, 'tbl', 'string', '');
        $field = cleanVar($_POST, 'field', 'string', '');
        $type = cleanVar($_POST, 'type', 'string', '');
        $id_value = cleanVar($_POST, 'id_value', 'int', '');
        $width = cleanVar($_POST, 'width', 'string', '');
        $toolbar = cleanVar($_POST, 'toolbar', 'string', '');

        $rows = $this->_registry->db->select('text', TBL_TRANSLATION, "tbl_id_value='$id_value' AND tbl='$tbl' AND field='$field' AND language='$lng_code'");
        if($rows and count($rows))
        {
            foreach($rows AS $row) {
                if($type == 'input' || $type == 'textarea') {
                	$text = htmlInput($row['text']);
                }
                elseif($type == 'editor') {
                	$text = htmlInputEditor($row['text']);
                }
            }
            $action = 'modify';
        }
        else {
            $text = '';
            $action = 'insert';
        }
        
        $GINO = "<div style=\"margin-top:10px;\">";
        $GINO .= "<p>";

        $url = $this->_registry->request->absolute_url.'&save=1';
        $onclick = "gino.translations.callAction('".$url."', '$type', '$tbl', '$field', '$id_value', false, '$lng_code', '$action')";

        if($type == 'input') {
            $GINO .= Input::input('trnsl_'.$field, 'text', $text, array("size"=>$width, "id"=>'trnsl_'.$field));
        }
        elseif($type == 'textarea') {
            $GINO .= Input::textarea('trnsl_'.$field, $text, array("cols"=>$width, "rows"=>4, "id"=>'trnsl_'.$field));
        }
        elseif($type == 'editor') {
            $onclick = "gino.translations.callAction('".$url."', '$type', '$tbl', '$field', '$id_value', true, '$lng_code', '$action')";
            
            $GINO .= Input::textarea('trnsl_'.$field, $text, array(
            	'ckeditor' => true, 
            	'ckeditor_toolbar' => $toolbar, 
            	'ckeditor_container' => false, 
            	'height' => 300, 
            	'width' => '100%'
            ));
        }
        $onclick = "onclick=\"$onclick\"";
        $GINO .= "</p>";
        $GINO .= "<p>".Input::input('submit', 'button', _("applica"), array("classField"=>"submit", "js"=>$onclick))."</p>";
        $GINO .= "</div>";

        return $GINO;
     }

    /**
     * @brief Inserimento e modifica delle traduzioni
     * @return risultato operazione
     *
     */
    public function actionTranslation(\Gino\Http\Request $request) {

        $action = cleanVar($request->POST, 'action', 'string', '');
        $type = cleanVar($request->POST, 'type', 'string', '');
        if($type == 'input' || $type == 'textarea') {
            $text = cleanVar($request->POST, 'text', 'string', '');
        }
        elseif($type == 'editor') {
            $text = cleanVarEditor($request->POST, 'text', '');
        }
        $lng_code = cleanVar($request->POST, 'lng_code', 'string', '');
        $tbl = cleanVar($request->POST, 'tbl', 'string', '');
        $field = cleanVar($request->POST, 'field', 'string', '');
        $id_value = cleanVar($request->POST, 'id_value', 'int', '');

        $res = false;

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

        return $res;
    }

    /**
     * @brief Eliminazione traduzione
     * 
     * @param string $tbl nome della tabella con il campo da tradurre
     * @param integer $tbl_id valore dell'ID del record di riferimento per la traduzione
     * @return risultato operazione, bool
     */
    public static function deleteTranslations($tbl, $tbl_id) {

        $db = Db::instance();
        $result = $tbl_id == 'all'
            ? $db->delete(TBL_TRANSLATION, "tbl='".$tbl."'")
            : $db->delete(TBL_TRANSLATION, "tbl='".$tbl."' AND tbl_id_value='".$tbl_id."'");

        return $result;
    }

}
