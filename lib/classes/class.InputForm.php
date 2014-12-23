<?php
/**
 * @file class.InputForm.php
 * @brief Contiene la definizione ed implementazione della classe Gino.InputForm
 *
 * @copyright 2005-2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

/**
 * @brief Interfaccia ai metodi che che generano gli elementi input nella classe Form()
 *
 * @copyright 2005-2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class InputForm {

    private $_form;

    /**
     * Costruttore
     *
     * @param \Gino\Form $form istanza di Gino.Form
     * @return void
     */
    function __construct(\Gino\Form $form) {

        $this->_form = $form;
    }

    /**
     * @brief Input hidden
     * 
     * @see Gino.Form::hidden()
     * @param string $name
     * @param mixed $value
     * @param array $options opzioni del metodo hidden() della classe Form
     * @return widget html
     */
    public function hidden($name, $value, $options) {

        return $this->_form->hidden($name, htmlInput($value), $options);
    }

    /**
     * @brief Etichetta e valore al di fuori di un input
     * 
     * @see Gino.Form::noinput()
     * @param string $label
     * @param mixed $value
     * @param array $options opzioni del metodo noinput() della classe Form
     * @return widget html
     */
    public function noinput($label, $value, $options) {

        return $this->_form->noinput($label, htmlChars($value), $options);
    }

    /**
     * @brief Input text
     * 
     * @see Gino.Form::input()
     * @see Gino.Form::cinput()
     * @param string $name
     * @param mixed $value
     * @param mixed $label
     * @param array $options opzioni dei metodi input() e cinput() della classe Form
     * @return widget html
     */
    public function text($name, $value, $label, $options) {

        $value = $this->_form->retvar($name, htmlInput($value));

        return $this->_form->cinput($name, 'text', $value, $label, $options);
    }

    /**
     * @brief Input password
     * 
     * @see Gino.Form::input()
     * @see Gino.Form::cinput()
     * @param string $name
     * @param mixed $value
     * @param mixed $label
     * @param array $options opzioni dei metodi input() e cinput() della classe Form
     * @return widget html
     */
    public function password($name, $value, $label, $options) {

        $value = $this->_form->retvar($name, htmlInput($value));

        return $this->_form->cinput($name, 'password', $value, $label, $options);
    }

    /**
     * @brief Input email
     * 
     * @see Gino.Form::input()
     * @see Gino.Form::cinput()
     * @param string $name
     * @param mixed $value
     * @param mixed $label
     * @param array $options opzioni dei metodi input() e cinput() della classe Form
     * @return widget html
     */
    public function email($name, $value, $label, $options) {

        $value = $this->_form->retvar($name, htmlInput($value));

        return $this->_form->cinput($name, 'email', $value, $label, $options);
    }

    /**
     * @brief Input file
     * 
     * @see Gino.Form::input()
     * @see Gino.Form::cfile()
     * @param string $name
     * @param mixed $value
     * @param mixed $label
     * @param array $options opzioni dei metodi input() e cfile() della classe Form
     * @return widget html
     */
    public function file($name, $value, $label, $options) {

        return $this->_form->cfile($name, htmlInput($value), $label, $options);
    }

    /**
     * @brief Textarea
     * 
     * @see Gino.Form::textarea()
     * @see Gino.Form::ctextarea()
     * @param string $name
     * @param mixed $value
     * @param mixed $label
     * @param array $options opzioni dei metodi textarea() e ctextarea() della classe Form
     * @return widget html
     */
    public function textarea($name, $value, $label, $options) {

        $value = $this->_form->retvar($name, htmlInput($value));

        return $this->_form->ctextarea($name, $value, $label, $options);
    }

    /**
     * @brief Input select
     * 
     * @see Gino.Form::select()
     * @see Gino.Form::cselect()
     * @param string $name
     * @param mixed $value
     * @param mixed $data
     *   - string, query della FOREIGN_KEY
     *   - array, insieme di elementi (chiave=>valore) da utilizzare per popolare l'input (associato alla chiave @a enum)
     * @param mixed $label
     * @param array $options opzioni dei metodi select() e cselect() della classe Form
     * @return widget html
     */
    public function select($name, $value, $data, $label, $options) {

        return $this->_form->cselect($name, $value, $data, $label, $options);
    }

    /**
     * @brief Radio button
     * 
     * @see Gino.Form::radio()
     * @see Gino.Form::cradio()
     * @param string $name
     * @param mixed $value
     * @param mixed $data
     *   - string, query della FOREIGN_KEY
     *   - array, insieme di elementi (chiave=>valore) da utilizzare per popolare l'input (associato alla chiave @a enum)
     * @param mixed $default
     * @param mixed $label
     * @param array $options opzioni dei metodi radio() e cradio() della classe Form
     * @return widget html
     */
    public function radio($name, $value, $data, $default, $label, $options) {

        $value = $this->_form->retvar($name, htmlInput($value));

        return $this->_form->cradio($name, $value, $data, $default, $label, $options);
    }

    /**
     * @brief Input date con calendario
     * 
     * @see Gino.Form::cinput_date()
     * @param string $name
     * @param mixed $value
     * @param mixed $label
     * @param array $options opzioni dei metodi cinput_date() e input() della classe Form
     * @return widget html
     */
    public function date($name, $value, $label, $options=array()) {

        $value = $this->_form->retvar($name, htmlInput(dbDateToDate($value, "/")));

        return $this->_form->cinput_date($name, $value, $label, $options);
    }

    /**
     * @brief Input checkbox
     * 
     * @param string $name
     * @param boolean $checked valore selezionato (associato alla chiave @a checked)
     * @param mixed $value
     * @param string $label
     * @param array $options opzioni dei metodi ccheckbox() e checkbox() della classe Form
     * @return widget html
     */
    public function checkbox($name, $checked, $value, $label, $options=array()) {

        return $this->_form->ccheckbox($name, $checked, $value, $label, $options);
    }

    /**
     * @brief Input checkbox multipli
     * 
     * @param string $name
     * @param array $value array dei valori degli elementi selezionati
     * @param mixed $data
     *   - string, query della FOREIGN_KEY
     *   - array, insieme di elementi (chiave=>valore) da utilizzare per popolare l'input (associato alla chiave @a enum)
     * @param string $label
     * @param array $options opzioni del metodo multipleCheckbox() della classe Form
     * @return widget html
     */
    public function multicheck($name, $value, $data, $label, $options=array()) {

        return $this->_form->multipleCheckbox($name, $value, $data, $label, $options);
    }

    /**
     * @brief Input editor html
     * 
     * @param string $name
     * @param mixed $value
     * @param string $label
     * @param array $options opzioni del metodo fcktextarea() della classe Form
     * @return widget html
     */
    public function editor($name, $value, $label, $options=array()) {

        $value = $this->_form->retvar($name, htmlInputEditor($value));

        return $this->_form->fcktextarea($name, $value, $label, $options);
    }
}
