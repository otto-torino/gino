<?php
/**
 * @file class.Cache.php
 * @brief Contiene la definizione ed implementazione delle classi Gino.Cache, Gino.OutputCache, Gino.DataCache
 *
 * @copyright 2005-2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

/**
 * @brief Classe che viene estesa da Gino.OutputCache() e Gino.DataCache()
 *
 * @copyright 2005-2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class Cache {

    protected $_registry;
    protected $_ds, $_fld, $_prefix;
    protected $_grp, $id, $_tc;
    protected $_enabled;

    /**
     * @brief Costtruttore
     * @return istanza di Gino.Cache
     */
    function __construct() {

        $this->_registry = registry::instance();
        $this->_ds = OS;
        $this->_fld = CACHE_DIR;
        $this->_prefix = 'cache_';
        $this->_enabled = $this->_registry->db->getFieldFromId(TBL_SYS_CONF, 'enable_cache', 'id', 1);
    }

    /**
     * @brief Scrive i dati sul file self::getFilename()
     * @see self::getFilename()
     * @param string $data
     * @return void
     */
    protected function write($data) {

        $filename = $this->getFilename();

        if($fp = @fopen($filename, "xb")) {
            if(flock($fp, LOCK_EX)) fwrite($fp, $data);
            fclose($fp);
            touch($filename, time());
        }
    }

    /**
     * @brief Legge il contenuto del file self::getFilename()
     * @see self::getFilename()
     * @return contenuto file
     */
    protected function read() {

        return file_get_contents($this->getFilename());
    }

    /**
     * @brief Nome file di cache
     * @return nome file
     */
    protected function getFilename() {

        return $this->_fld . $this->_ds . $this->_prefix . $this->_grp ."_". md5($this->_id);
    }

    /**
     * @brief Controlla se il file di cache esiste ed è valido
     * @return TRUE se il file di cache è presente e valido, FALSE altrimenti (e lo elimina se presente)
     */
    protected function isCached() {

        $filename = $this->getFilename();
        if($this->_enabled && file_exists($filename) && time() < (filemtime($filename) + $this->_tc)) return TRUE; 
        else @unlink($filename);

        return FALSE;
    }

    /**
     * @brief Elimina il file di cache corrsipondente al gruppo e id dati
     * @param string $grp gruppo
     * @param string $id
     * @return risultato operazione, bool
     */
    public function delete($grp, $id) {
        $this->_grp = $grp;
        $this->_id = $id;

        $filename = $this->getFilename();
        if(is_file($filename)) {
            return @unlink($filename);
        }
        return TRUE;
    }
}

/**
 * @brief Memorizza gli output (text, html, xml) scrivendo su file
 * 
 * Esempio di utilizzo
 * @code
 * $GINO = "previous text-";
 * $cache = new outputCache($GINO);
 * if($cache->start("group_name", "id", 3600)) {
 *
 *    $buffer = "some content-";
 *
 *    $cache->stop($buffer);
 *
 * }
 * $GINO .= "next text";
 * @endcode
 *
 * --> result: $GINO = "previous text-somec content-next text";
 *
 * se il contenuto è in cache l'if statement viene skippato ed il contenuto è concatenato alla variabile $buffer (attraverso il metodo self::stop)
 * se il contenuto non è in cache viene eseguito il codice interno all'if statement, il contenuto viene preparato e salvato in cache e quindi aggiunto alla variabile $buffer (attraverso il metodo self::stop)
 * @copyright 2005-2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class OutputCache extends Cache {

    /**
     * @brief Costruttore
     * @param string $buffer contenuto al quale appendere il contenuto da mettere/prelevare dalla cache
     * @param bool $enabled abilitazione cache, default TRUE
     * @return istanza di Gino.OutputCache
     */
    function __construct(&$buffer, $enabled = TRUE) {

        parent::__construct();
        $this->_buffer = &$buffer;
        $this->_enabled = $enabled;
    }

    /**
     * @brief Inizia il processo di cache
     * @description Se il contenuto è in cache lo accoda a $buffer e ritorna FALSE, altrimenti ritorna TRUE (e si entra nell'if statement)
     * @param string $grp gruppo
     * @param string $id
     * @return TRUE se il file è in cache, FALSE altrimenti
     */
    public function start($grp, $id, $tc) {

        $this->_grp = $grp;
        $this->_id = $id;
        $this->_tc = $tc;

        if($this->isCached()) {
            $this->_buffer .= $this->read();
            return FALSE;
        }

        return TRUE;
    }

    /**
     * @brief Finalizza il processo di cache
     * @description Se la cache è abilitata salva il contenuto in un file, appende il contenuto alla variabile $buffer
     * @return void
     */
    public function stop($data) {

        if($this->_enabled) $this->write($data);
        $this->_buffer .= $data;
    }
}

/**
 * @brief Memorizza le strutture dati scrivendo su file
 *
 * Esempio di utilizzo
 * @code
 * $cache = new dataCache();
 * if(!$data = $cache->get('group_name', 'id', 3600)) {
 *   $data = someCalculations();
 *   $cache->save($data);
 * }
 * @endcode
 *
 * Se i dati sono in cache sono ritornati dal metodo self::get e l'if statement viene skippato, altrimenti i dati sono ricavati e salvati nella cache
 * @copyright 2005-2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 *
 */
class DataCache extends cache {

    /*
     * @brief Costruttore
     * @param bool $enabled abilitazione cache, default TRUE
     * @return istanza di Gino.DataCache
     */
    function __construct($enabled = TRUE) {

        parent::__construct();
        $this->_enabled = $enabled;
    }

    /**
     * @brief Fornisce i dati in cache o ritorna FALSE se non presenti o scaduti
     * @param string $grp gruppo
     * @param string $id
     * @return dati deserializzati o FALSE
     */
    public function get($grp, $id, $tc) {

        $this->_grp = $grp;
        $this->_id = $id;
        $this->_tc = $tc;

        if($this->isCached()) return unserialize($this->read());
        return FALSE;
    }

    /**
     * @brief Salva i dati serializzati su file se la cache è abilitata
     * @return void
     */
    public function save($data) {

        if($this->_enabled) $this->write(serialize($data));
    }
}
