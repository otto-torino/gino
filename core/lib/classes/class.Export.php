<?php
/**
 * @file class.Export.php
 * @brief Contiene la definizione ed implementazione della classe Gino.Export
 * 
 * @copyright 2005-2017 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

/**
 * @brief Libreria per l'esportazione di tabelle o dati
 * 
 * @copyright 2005-2017 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 * 
 * ##UTILIZZO
 * L'utilizzo della libreria prevede l'inclusione del file lib/classes/class.Export.php
 * @code
 * require_once(CLASSES_DIR.OS.'class.Export.php');
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
 * $obj_export = new Export();
 * $obj_export->setData($items);
 * return $obj_export->exportData('export.csv');
 * @endcode
 */
class Export {

    private $_s = ",";

    private $_table;
    
    /**
     * @brief Indica se mostrare l'intestazione delle colonne
     * @var boolean
     */
    private $_head = TRUE;
    
    private $_fields = '*';
    private $_rids = '*';
    private $_order;
    
    /**
     * @brief Codice identificativo del tipo di file da esportare
     * @var string
     */
    private $_filetype;
    
    /**
     * @brief Codici identificativi dei tipi di file che è possibile esportare
     * @var array
     */
    private static $filetype_values = array('csv');
    
    /**
     * @brief Tipologia di output adottata
     * @var string
     */
    private $_output;
    
    /**
     * @brief Tipologie di output possibili
     * @var array
     */
    private static $output_values = array('stream', 'file');

    /**
     * @brief Dati da esportare
     * @var array
     */
    private $_data;
    
    function __construct() {
    	
    	// default values
    	$this->_filetype = 'csv';
    	$this->_output = 'stream';
    }
    
    /**
     * @brief Imposta la tipologia di output
     * @param string $value
     */
    public function setOutput($value) {
    	
    	if(is_string($value) && in_array($value, self::$output_values)) {
    		$this->_output = $value;
    	}
    }
    
    /**
     * @brief Imposta il tipo di file da esportare
     * @param string $value
     */
    public function setFiletype($value) {
    	 
    	if(is_string($value) && in_array($value, self::$filetype_values)) {
    		$this->_filetype = $value;
    	}
    }

    /**
     * @brief Imposta la proprietà @a $_table
     * @param string $table nome della tabella da esportare
     * @return void
     */
    public function setTable($table) {
        $this->_table = $table;
    }

    /**
     * @brief Imposta la proprietà @a $_s
     * @param string $s separatore dei campi, default ','
     * @return void
     */
    public function setSeparator($s) {
        $this->_s = $s;
    }

    /**
     * @brief Imposta la proprietà @a $_fields
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
     * @brief Imposta la proprietà @a $_head
     * @param boolean $value
     * @return void
     */
    public function setHead($value) {
        
    	if(is_bool($value)) {
    		$this->_head = $value;
    	}
    }

    /**
     * @brief Imposta la proprietà @a $_rids
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
     * Imposta la proprietà @a $_order
     * @param string $order il campo per l'ordinamento dei risultati
     * @return void
     */
    public function setOrder($order) {
        $this->_order = $order;
    }

    /**
     * @brief Imposta la proprietà @a $_data
     * @param array $value dati da esportare (parametro competitivo a $_table):
     *   @code
     *   array(
     *     0=>array("head1", "head2", "head3"), 
     *     1=>array("value1 record 1", "value 2 record 1", "value 3 record 1"), 
     *     2=>array("value1 record 2", "value 2 record 2", "value 3 record 2")
     *   )
     *   @endcode
     * @return void
     */
    public function setData($value) {
        
    	if(is_array($value)) {
    		$this->_data = $value;
    	}
    }

    /**
     * @brief Esporta il file
     * 
     * @see self::exportCsv()
     * @param string $filename the name of the file written (the absolute path if the output is file)
     * @return \Gino\Http\ResponseFile se il file viene inviato in output || TRUE se il file viene salvato su fs
     */
    public function exportData($filename) {

        if($this->_filetype == 'csv') {
        	return $this->exportCsv($filename);
        }
        else {
        	return null;
        }
    }

    /**
     * @brief Esporta un file csv
     * 
     * @param string $filename the name of the file written (the absolute path if the output is file)
     * @return \Gino\Http\ResponseFile se il file viene inviato in output || TRUE se il file viene salvato su fs
     */
    private function exportCsv($filename) {

        $data = $this->getData();

        $csv = '';
        foreach($data as $row) {
            $cell = array();
            foreach($row as $v) $cell[] = enclosedField($v);
            $csv .= implode($this->_s, $cell)."\r\n";
        }
        
        if($this->_output == 'stream') {
            $response = Loader::load('http/ResponseFile', array($csv, 'plain/text', $filename, array('file_is_content' => TRUE)), '\Gino\Http\\');
            $response->setDispositionType('Attachment');
            $response->setHeaders(array(
                'Pragma' => 'no-cache'
            ));
            
            return $response;
        }
        elseif($this->_output == 'file') {
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

    	if($this->_data) {
        	return $this->_data;
        }
        else {
        	return null;
        }
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
     * @return string
     */
     protected function removeBOM($str=''){

         if(substr($str, 0,3) == pack("CCC",0xef,0xbb,0xbf)) {
             $str = substr($str, 3);
         }
         return $str;
     }
}
