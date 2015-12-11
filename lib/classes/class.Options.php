<?php
/**
 * @file class.Options.php
 * @brief Contiene la definizione ed implementazione della classe Gino.Options
 * 
 * @copyright 2005-2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;
use \Gino\App\SysClass\ModuleApp;

/**
 * @brief Gestisce le opzioni di classe, costruendo il form ed effettuando l'action
 *
 * Le opzioni che possono essere associate a ciascun campo sono:
 * 
 *   - @b label (string): nome della label
 *   - @b value (mixed): valore di default
 *   - @b required (boolean): campo obbligatorio
 *   - @b trnsl (boolean): campo che necessita di traduzione
 *   - @b section (boolean): segnala l'inizio di un blocco di opzioni
 *   - @b section_title (string): nome del blocco di opzioni
 *   - @b section_description (string): descrizione del blocco di opzioni
 *
 * @copyright 2005-2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class Options {

    private $_db;
    private $_class, $_module_app, $_class_prefix;
    private $_tbl_options;
    private $_instance;
    private $_title;
    private $_home;

    private $_action;

    /**
     * @brief Costruttore
     * @param \Gino\Controller $controller istanza di Gino.Controller
     * @return istanza di Gino.Options
     */
    function __construct($controller){

        if(!is_a($controller, '\Gino\Controller')) {
            throw new \Exception(_('Il primo argomento deve essere una classe di tipo Controller'));
        }

        Loader::import('sysClass', 'ModuleApp');

        $this->_db = Db::instance();
        $this->_title = _("Opzioni");

        $this->setData($controller);

        $this->_action = cleanVar($_REQUEST, 'action', 'string', '');

        $this->_home = HOME_FILE;
    }

    /**
     * @brief Imposta alcune variabili utilizzate dalla classe
     * @throws \Exception se il nome della classe del Controller non è definita
     * @param \Gino\Controller $controller istanza di Gino.Controller
     * @return void
     */
    private function setData($controller) {

        $this->_instance = $controller->getInstance();
        $this->_instance_name = $controller->getInstanceName();
        $class_name = $controller->getClassName();

        if($class_name) {
            $this->_class_name = $class_name;
            $this->_class = get_app_name_class_ns($class_name);
        }
        else {
            throw new \Exception("Classe ".$class_name." inesistente");
        }

        $this->_module_app = \Gino\App\SysClass\ModuleApp::getFromName(get_name_class($class_name));
        $this->_class_prefix = $this->_module_app->tbl_name;
        $this->_tbl_options = $this->_class_prefix.'_opt';

        $registry = Registry::instance();
        $this->_return_link = method_exists($this->_class, "manageDoc") 
            ? $registry->router->link($this->_instance_name, "manageDoc", array(), array('block' => 'options'))
            : $registry->router->link($this->_instance_name, "manage".ucfirst($class_name), array(), array('block' => 'options'));
    }

    /**
     * @brief Controlla se il campo è editabile
     * @return True se editabile, FALSE altrimenti
     */
    private function editableField($field) {
        return ($field != 'id' && $field != 'instance');
    }

    /**
     * @brief Interfaccia per la gestione delle opzioni di una istanza/modulo (Form)
     *
     * Come informazioni sui campi sono necessarie: \n
     *   - @b name (string): nome del campo
     *   - @b type (string): tipo di campo
     *   - @b length (integer): numero massimo di caratteri
     * @see Gino.Db::fieldInformations()
     *
     * @return interfaccia
     */
    public function manageDoc(){

        $registry = Registry::instance();
        $request = $registry->request;
        
        if($request->checkGETKey('trnsl', '1')) {
            return $registry->trd->manageTranslation($request);
        }

        if($this->_action == 'insert' || $this->_action == 'modify') return $this->actionOptions();

        $gform = Loader::load('Form', array(array('form_id'=>'gform')));
        $gform->load('dataform');

        $class_instance = ($this->_instance) ? new $this->_class($this->_instance) : new $this->_class();
        $table_info = $this->_db->fieldInformations($this->_tbl_options);
        $required = '';

        $a = $this->_db->select('*', $this->_tbl_options, "instance='".$this->_instance."'");
        if($a and sizeof($a)>0) {
            foreach($a as $b) {
                $id = $b['id'];
                foreach($table_info AS $f) {
                    if($this->editableField($f->name)) {
                        // Required
                        $field_option = $class_instance->_optionsLabels[$f->name];
                        if(is_array($field_option) AND array_key_exists('label', $field_option))
                        {
                            if(array_key_exists('required', $field_option) AND $field_option['required'] == true)
                                $required .= $f->name.",";
                        }
                        else $required .= $f->name.",";

                        ${$f->name} = htmlInput($b[$f->name]);
                    }
                }
            }
            $action = 'modify';
            $submit = _("modifica");
        }
        else {
            $id = '';
            foreach($table_info AS $f) {
                if($this->editableField($f->name)) {

                    ${$f->name} = '';

                    // Required
                    $field_option = $class_instance->_optionsLabels[$f->name];
                    if(is_array($field_option) AND array_key_exists('label', $field_option))
                    {
                        if(array_key_exists('required', $field_option) AND $field_option['required'] == true)
                            $required .= $f->name.",";

                        if(array_key_exists('value', $field_option) AND $field_option['value'] != '')
                            ${$f->name} = $field_option['value'];
                    }
                    else $required .= $f->name.",";
                }
            }
            $action = 'insert';
            $submit = _("inserisci");
        }

        $label = $this->_module_app->ml('label');

        if(method_exists($this->_class, 'manageDoc')) $function = 'manageDoc';
        else $function = 'manage'.ucfirst($this->_class_name);

        if($required) $required = substr($required, 0, strlen($required)-1);
        $GINO = $gform->open($this->_home."?evt[".$this->_instance_name."-$function]&block=options", '', $required);
        $GINO .= \Gino\Input::hidden('func', 'actionOptions');
        $GINO .= \Gino\Input::hidden('action', $action);

        foreach($table_info AS $f) {

            if($this->editableField($f->name)) {

                $field_option = $class_instance->_optionsLabels[$f->name];

                if(is_array($field_option) && array_key_exists('section', $field_option) && $field_option['section'])
                {
                    $section_title = array_key_exists('section_title', $field_option) ? $field_option['section_title'] : '';
                    $section_title = "<h2>$section_title</h2>";
                    if($section_description = gOpt('section_description', $field_option, null)) {
                        $section_title .= "<div>$section_description</div>";
                    }
                    $GINO .= $section_title;
                }

                if(is_array($field_option) AND array_key_exists('label', $field_option))
                {
                    $field_label = $field_option['label'];
                    $field_required = array_key_exists('required', $field_option) ? $field_option['required'] : false;
                    $field_trnsl = array_key_exists('trnsl', $field_option) ? $field_option['trnsl'] : true;
                }
                else
                {
                    $field_label = $field_option;
                    $field_required = true;
                    $field_trnsl = true;
                }

                if($f->type == 'char') {
                    $GINO .= \Gino\Input::input_label($f->name, 'text', ${$f->name}, $field_label, array("required"=>$field_required, "size"=>40, "maxlength"=>$f->length, "trnsl"=>$field_trnsl, "trnsl_table"=>$this->_tbl_options, "field"=>$f->name, "trnsl_id"=>$id));
                }
                elseif($f->type == 'text') {
                    $GINO .= \Gino\Input::textarea_label($f->name, ${$f->name},  $field_label, array("cols"=>'50', "rows"=>4, "required"=>$field_required, "trnsl"=>$field_trnsl, "trnsl_table"=>$this->_tbl_options, "field"=>$f->name, "trnsl_id"=>$id));
                }
                elseif($f->type == 'int' && $f->length>1) {
                    $GINO .= \Gino\Input::input_label($f->name, 'text', ${$f->name},  $field_label, array("required"=>$field_required, "size"=>$f->length, "maxlength"=>$f->length));
                }
                elseif(($f->type == 'int' && $f->length == 1) || $f->type == 'bool') {
                    $GINO .= \Gino\Input::radio_label($f->name, ${$f->name}, array(1=>_("si"),0=>_("no")), 'no',  $field_label, array("required"=>$field_required));
                }
                elseif($f->type == 'date') {
                    $GINO .= \Gino\Input::input_date($f->name, dbDateToDate(${$f->name}, '/'),  $field_label, array("required"=>$field_required));
                }
                else $GINO .= "<p>"._("ATTENZIONE! Tipo di campo non supportato")."</p>";
            }
        }
        $GINO .= \Gino\Input::input_label('submit_action', 'submit', $submit, '', array("classField"=>"submit"));
        $GINO .= $gform->close();

        $view = new view();
        $view->setViewTpl('section');
        $dict = array(
            'class' => 'admin',
            'title' => _('Opzioni'),
            'content' => $GINO
        );

        return $view->render($dict);
    }

    /**
     * @brief Processa il form di opzioni
     * @return Gino.Gttp.Redirect
     */
    public function actionOptions() {

        $registry = registry::instance();
        $request = $registry->request;
        $gform = Loader::load('Form', array('gform', 'post', false));
        $gform->saveSession('dataform');
        $req_error = $gform->checkRequired();

        $action = cleanVar($request->POST, 'action', 'string', '');

        $table_info = $this->_db->fieldInformations($this->_tbl_options);

        $data = array();
        $par_query = $par1_query = $par2_query = '';
        foreach($table_info AS $f) {
            if($this->editableField($f->name)) {
                if($f->type == 'int') {
                    ${$f->name} = cleanVar($request->POST, $f->name, 'int', '');
                }
                elseif($f->type == 'date') {
                    ${$f->name} = dateToDbDate(cleanVar($request->POST, $f->name, 'string', ''), '/');
                }
                else ${$f->name} = cleanVar($request->POST, $f->name, 'string', '');

                $data[$f->name] = ${$f->name};
            }
        }

        if($req_error > 0)
            return error::errorMessage(array('error'=>1), $this->_return_link);

        if($action == 'insert') {
            $this->_db->insert(array_merge(array('instance' => $this->_instance), $data), $this->_tbl_options);
        }
        elseif($action == 'modify') {
            $this->_db->update($data, $this->_tbl_options, "instance='".$this->_instance."'");
        }

        return new \Gino\Http\Redirect($this->_return_link);
    }
}
