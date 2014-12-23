<?php
/**
 * @file class.Export.php
 * @brief Contiene la definizione ed implementazione della classe Gino.Export
 * 
 * @copyright 2005-2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

/**
 * @brief Libreria per l'esportazione di tabelle o dati
 * 
 * @copyright 2005-2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 * 
 * ##UTILIZZO
 * L'utilizzo della libreria prevede l'inclusione del file
 * @code
 * require_once(CLASSES_DIR.OS.'class.export.php');
 * @endcode
 *
 * ###ESEMPIO
 *
 * @code
 * $items = array(
 *  array('value1', 'value2', 'value3'), 
 *  array('value4', 'value5', 'value6')
 * );
 *
 * $obj_export = new export();
 * $obj_export->setData($items);
 * $obj_export->exportData('export.csv', 'csv');
 * @endcode
 */
class Export {

    private $_s = ",";

    private $_table;
    private $_head = TRUE;
    private $_fields = '*';
    private $_rids = '*';
    private $_order;

    private $_data;

    /**
     * @brief Imposta la proprietà @a _table
     * @param string $table nome della tabella da esportare
     * @return void
     */
    public function setTable($table) {
        $this->_table = $table;
    }

    /**
     * @brief Imposta la proprietà @a _s
     * @param string $s separatore dei campi, default ','
     * @return void
     */
    public function setSeparator($s) {
        $this->_s = $s;
    }

    /**
     * @brief Imposta la proprietà @a _fields
     * @param mixed $fields
     *   campi da esportare
     *   - @b *: tutti i campi
     *   - @b * -(field1,field2): tutti i campi eccetto field1 e field2
     *   - @b field1,field2: solamente i campi field1 e field2
     *   - @b array("field1", "field2"): solamente i campi field1 e field2
     * @return void
     */
    public function setFields($fields) {
        $this->_fields = $fields;
    }

    /**
     * @brief Imposta la proprietà @a _head
     * @param boolean $head indica se mostrare o meno l'intestazione delle colonne
     * @return void
     */
    public function setHead($head) {
        $this->_head = $head;
    }

    /**
     * @brief Imposta la proprietà @a _rids
     * @param mixed $rids
     *   id dei record da esportare:
     *   - @b *: tutti i record
     *   - @b 1,3,5: records con id=1, id=3 e id=5
     *   - @b array(1,3,5): records con id=1, id=3 e id=5
     * @return void
     */
    public function setRids($rids) {
        $this->_rids = $rids;
    }

    /**
     * Imposta la proprietà @a _order
     * @param string $order il campo per l'ordinamento dei risultati
     * @return void
     */
    public function setOrder($order) {
        $this->_order = $order;
    }

    /**
     * @brief Imposta la proprietà @a _data
     * @param array $data dati da esportare (parametro competitivo a _table):
     *   @code
     *   array(0=>array("head1", "head2", "head3"), 
     *     1=>array("value1 record 1", "value 2 record 1", "value 3 record 1"), 
     *     2=>array("value1 record 2", "value 2 record 2", "value 3 record 2")
     *   )
     *   @endcode
     * @return void
     */
    public function setData($data) {
        $this->_data = $data;
    }

    /**
     * @brief Esporta il file
     * 
     * Attualmente è prevista soltanto l'esportazione di file CSV
     * 
     * @see self::exportCsv()
     * @param string $filename the name of the file written (the absolute path if the output is file)
     * @param string $extension the file extension
     * @param string $output (file|stream)
     * @return \Gino\Http\ResponseFile se il file viene inviato in output || TRUE se il file viene salvato su fs
     */
    public function exportData($filename, $extension, $output='stream') {

        if($extension=='csv') return $this->exportCsv($filename, $output);
    }

    /**
     * @brief Esporta un file csv
     * 
     * @param string $filename the name of the file written (the absolute path if the output is file)
     * @param string $output (file|stream)
     * @return \Gino\Http\ResponseFile se il file viene inviato in output || TRUE se il file viene salvato su fs
     */
    private function exportCsv($filename, $output) {

        $data = $this->getData();

        $csv = '';
        foreach($data as $row) {
            $cell = array();
            foreach($row as $v) $cell[] = enclosedField($v);
            $csv .= implode($this->_s, $cell)."\r\n";
        }

        if($output=='stream') {
            $response = Loader::load('http/ResponseFile', array($csv, 'plain/text', $filename, array('file_is_content' => TRUE)), '\Gino\Http\\');
            $response->setDispositionType('Attachment');
            $response->setHeaders(array(
                'Pragma' => 'no-cache'
            ));
            return $response;
        }
        elseif($output=='file') {
            $fo = fopen($filename, "w");
            fwrite($fo, $csv);
            fclose($fo);
            return TRUE;
        }
    }

    /**
     * @brief Dati da esportare in formato array
     * @return array di dati
     */
    private function getData() {

        if($this->_data) return $this->_data;
        if(!$this->_table) return array();

        $data = array();
        $head_fields = $this->getHeadFields();
        if(count($head_fields) && $this->_head) $data[] = $head_fields;

        if($this->_rids=='*') $where = '';
        elseif(is_array($this->_rids) && count($this->_rids)) 
            $where = "WHERE id='".implode("' OR id='", $this->_rids)."'";
        elseif(is_string($this->_rids) && strlen($this->_rids)>0)    
            $where = "WHERE id='".implode("' OR id='", explode(",",$this->_rids))."'";

        $order = $this->_order ? " ORDER BY ".$this->_order:"";

        $query_data = "SELECT ".implode(",", $head_fields)." FROM ".$this->_table." $where $order";
        $res = mysql_query($query_data);
        while($row = mysql_fetch_array($res, MYSQL_NUM)) 
            $data[] = $row;

        return $data;
    }

    /**
     * @brief array di intestazioni delle colonne
     * @return array intestazioni
     */
    private function getHeadFields() {

        if($this->_head && is_string($this->_fields) && preg_match("#\*#", $this->_fields)) {
            preg_match("#\* -\((.*)\)#", $this->_fields, $matches);
            $excluded_fields = isset($matches[1]) ? explode(",",$matches[1]):array();
            $head_fields = array();
            $query = "SHOW COLUMNS FROM ".$this->_table;
            $res = mysql_query($query);
            while($row = mysql_fetch_assoc($res)) {
                $results[] = $row;
            }
            mysql_free_result($res);
            foreach($results as $r) 
                if(!in_array($r['Field'], $excluded_fields)) $head_fields[] = $r['Field'];

        }
        elseif(is_string($this->_fields)) $head_fields = explode(",",$this->_fields);
        elseif(is_array($this->_fields)) $head_fields = $this->_fields;

        return $head_fields;
    }

    /**
     * @brief Crea un file con caratteristiche specifiche di encoding
     *
     * -- Procedura di esportazione di un file
     *
     * 1. I valori da database devono passare attraverso le funzioni utf8_encode() e enclosedField():
     *
     * @code
     * $firstname = enclosedField(utf8_encode($b['firstname']));    //-> TESTO
     * $date = utf8_encode($b['date']);                             //-> DATA
     * $number = $b['number'];                                      //-> NUMERO
     * @endcode
     *
     * 2. Creare il file sul filesystem:
     *
     * @code
     * $filename = $this->_doc_dir.'/'.$filename;
     * if(file_exists($filename)) unlink($filename);
     * $this->writeFile($filename, $output, 'csv');
     * @endcode
     *
     * 3. Effettuare il download del file:
     *
     * @code
     * $filename = 'export.csv';
     * header("Content-type: application/csv \r \n");
     * header("Content-Disposition: inline; filename=$filename");
     * echo $output;
     * exit();
     * @endcode

     * @param string $filename percorso assoluto al file
     * @param string $content contenuto del file
     * @param string $type tipologia di file
     *   - @b utf8
     *   - @b iso8859
     *   - @b csv: in questo caso utilizzare la funzione utf8_encode() sui valori da DB
     * @return void
     *
     */
     protected function writeFile($filename, $content, $type) {

         $dhandle = fopen($filename, "wb");

         if($type == 'utf8')
         {
             # Add byte order mark
             fwrite($dhandle, pack("CCC",0xef,0xbb,0xbf));
         }
         else 
         {
             if($type == 'iso8859')
             {
                 # From UTF-8 to ISO-8859-1
                 $content = mb_convert_encoding($content, "ISO-8859-1", "UTF-8");
             }
             elseif($type == 'csv')
             {
                 # UTF-8 Unicode CSV file that opens properly in Excel
                 $content = chr(255).chr(254).mb_convert_encoding( $content, 'UTF-16LE', 'UTF-8');
             }
         }

        fwrite($dhandle, $content);
        fclose($dhandle);
    }

    /**
     * @brief Rimuove il BOM (Byte Order Mark)
     *
     * @param string $str
     * @return stringa senza BOM
     */
     protected function removeBOM($str=''){

         if(substr($str, 0,3) == pack("CCC",0xef,0xbb,0xbf)) {
             $str = substr($str, 3);
         }
         return $str;
     }
}
