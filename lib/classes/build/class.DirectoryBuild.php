<?php
/**
 * @file class.DirectoryBuild.php
 * @brief Contiene la definizione ed implementazione della classe Gino.DirectoryBuild
 *
 * @copyright 2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

/**
 * @brief Gestisce i campi di tipo DIRECTORY
 *
 * @copyright 2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class DirectoryBuild extends Build {

    /**
     * Proprietà dei campi specifiche del tipo di campo
     */
    protected $_path, $_prefix, $_default_name;

    /**
     * @brief Costruttore
     *
     * @see Gino.Build::__construct()
     * @param array $options array associativo di opzioni del campo del database
     *   - opzioni generali definite come proprietà nella classe Build()
     *   - @b path (string): percorso assoluto della directory superiore
     *   - @b prefix (string): prefisso da aggiungere al nome della directory
     *   - @b default_name (array): valori per il nome di default
     *     - @a field (string): nome dell'input dal quale ricavare il nome della directory (default id)
     *     - @a maxlentgh (integer): numero di caratteri da considerare nel nome dell'input (default 15)
     *     - @a value_type (string): tipo di valore (default string)
     */
    function __construct($options) {

        parent::__construct($options);

        $this->_path = isset($options['path']) ? $options['path'] : '';
        if(!$this->_path) {
            throw new \Exception(_('Parametro path inesistente'));
        }
        if(substr($this->_path, -1) !== OS) {
            $this->_path .= OS;
        }
        $this->_prefix = isset($options['prefix']) ? $options['prefix'] : '';
        $this->_default_name = isset($options['default_name']) ? $options['default_name'] : array();
    }

    /**
     * @brief Getter della proprietà path
     * @return proprietà path
     */
    public function getPath() {

        return $this->_path;
    }

    /**
     * @brief Setter della proprietà path
     * @param string $value
     * @return void
     */
    public function setPath($value) {

        $this->_path = $value;
    }

    /**
     * @brief Getter della proprietà prefix
     * @return proprietà prefix
     */
    public function getPrefix() {

        return $this->_prefix;
    }

    /**
     * @brief Setter della proprietà prefix
     * @param string $value
     * @return void
     */
    public function setPrefix($value) {

        $this->_prefix = $value;
    }

    /**
     * @brief Ripulisce un input per l'inserimento in database
     * @see Gino.Field::clean()
     */
    public function clean($options=null) {

        $value_type = isset($options['value_type']) ? $options['value_type'] : $this->_value_type;
        $method = isset($options['method']) ? $options['method'] : $_POST;
        $escape = gOpt('escape', $options, true);

        $value = cleanVar($method, $this->_name, $value_type, null, array('escape'=>$escape));

        if(!$value)
            $value = $this->defaultName($options);

        if($value != $this->_value)
            $value = $this->checkName($value);

        return $value;
    }

    /**
     * @brief Nome di default della directory
     * @return nome directory o null
     */
    private function defaultName($options){

        $request = \Gino\Http\Request::instance();
        if($this->_default_name)
        {
            $field = array_key_exists('field', $this->_default_name) ? $this->_default_name['field'] : 'id';
            $maxlentgh = array_key_exists('maxlentgh', $this->_default_name) ? $this->_default_name['maxlentgh'] : 15;
            $value_type = array_key_exists('value_type', $this->_default_name) ? $this->_default_name['value_type'] : 'string';

            $method = isset($options['method']) ? $options['method'] : $request->POST;
            $value = cleanVar($method, $field, $value_type, null);

            $name_dir = substr($value, 0, $maxlentgh);
            $name_dir = preg_replace("#[^a-zA-Z0-9_\.-]#", "_", $name_dir);
            return $name_dir;
        }
        else return null;
    }

    /**
     * @brief Sostituisce nel nome di una directory i caratteri diversi da [a-zA-Z0-9_.-] con il carattere underscore (_)
     * 
     * Se il nome della directory è presente lo salva aggiungendogli un numero progressivo
     * 
     * @param string $name_dir nome della directory
     * @return nome directory 'friendly'
     */
    private function checkName($name_dir) {

        $name_dir = preg_replace("#[^a-zA-Z0-9_\.-]#", "_", $name_dir);
        $name_dir = $this->_prefix.$name_dir;

        $files = scandir($this->_path);
        $i=1;

        while(in_array($name_dir, $files)) { $name_dir = substr($name_dir, 0, strrpos($name_dir, '.')+1).$i.substr($name_dir, strrpos($name_dir, '.')); $i++; }

        return $name_dir;
    }

    /**
     * @brief Valida il valore del campo e crea la directory se non esiste
     * @param string $value
     * @return TRUE se valido, FALSE o errore altrimenti
     */
    public function validate($value){

        if($value == $this->_value)    // directory preesistente
        {
            return TRUE;
        }
        elseif($value)
        {
            if(!$this->_model->id)
            {
                if(!mkdir($this->_path.$value))
                    return array('error'=>32);
            }
            else
            {
                if(!$this->_value)
                {
                    if(!mkdir($this->_path.$value))
                        return array('error'=>32);
                }
                else
                {
                    if(!rename($this->_path.$this->_value, $this->_path.$value))
                        return array('error'=>32);
                }
            }

            return TRUE;
        }
        else return TRUE;
    }

    /**
     * @brief Eliminazione della directory
     * @return TRUE o errore
     */
    public function delete() {

        if($this->_value and is_dir($this->_path.$this->_value)) {
            if(!\Gino\deleteFileDir($this->_path.$this->_value))
                return array('error'=>_("errore nella eliminazione della directory"));
        }

        return TRUE;
    }
}
