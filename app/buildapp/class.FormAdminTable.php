<?php
/**
 * @file class.FormAdminTable.php
 * @brief Contiene la definizione ed implementazione della classe Gino.App.BuildApp.FormAdminTable
 *
 * @copyright 2017 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino\App\BuildApp;

use Gino\AdminTable;
use Gino\Error;

require_once(CLASSES_DIR.OS.'class.AdminTable.php');

/**
 * @brief @brief Estende la classe Gino.AdminTable
 * 
 * @copyright 2017 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class FormAdminTable extends AdminTable {

	/**
	 * @brief Directory contenente i file di schema dai quali generare i file dell'applicazione
	 * @var string
	 */
	private $_frame_dir;
	
	/**
	 * @brief Directory contenente i file di schema per un modulo istanziabile
	 * @var string
	 */
	private $_frame_dir_instance;
	
	/**
	 * @brief Directory contenente i file di schema per un modulo non istanziabile
	 * @var string
	 */
	private $_frame_dir_not_instance;
	
	/**
	 * @brief Directory dell'applicazione generata
	 * @var string
	 */
	private $_new_app_dir;
	
	/**
	 * @brief Directory delle viste dell'applicazione generata
	 * @var string
	 */
	private $_new_views_dir;
	
	/**
	 * @brief Directory dei contenuti dell'applicazione generata
	 * @var string
	 */
	private $_new_content_dir;
	
	/**
	 * @brief Valori delle variabili di sostituzione
	 * @var string
	 */
	private $_var_controller, $_var_namespace, $_var_model_name, $_var_model_label, $_var_model_reference;
	
	/**
	 * @brief Valori delle variabili di sostituzione relative al ManyToManyThroughField
	 * @var string
	 */
	private $_var_m2mtf_name, $_var_m2mtf_name_ucfirst, $_var_m2mtf_model_name, $_var_m2mtf_model_label, $_var_m2mtf_model_reference;
	
	function __construct($controller, $opts = array()) {
		
		parent::__construct($controller, $opts);
		
		$this->_frame_dir = CONTENT_DIR.OS.'buildapp'.OS;
		$this->_frame_dir_instance = $this->_frame_dir.'instance'.OS;
		$this->_frame_dir_not_instance = $this->_frame_dir.'not-instance'.OS;
		
		$this->setVariables();
	}
	
	/**
	 * @brierf Imposta i valori delle varibili di sostituzione
	 * @param \Gino\App\BuildApp\Item $model istanza di Gino.App.BuildApp.Item
	 */
	private function setVariables($model=null) {
		
		if($model === null) {
			$this->_var_controller = null;
			$this->_var_namespace = null;
			$this->_var_model_name = null;
			$this->_var_model_label = null;
			$this->_var_model_reference = null;
			$this->_var_m2mtf_name = null;
			$this->_var_m2mtf_name_ucfirst = null;
			$this->_var_m2mtf_model_name = null;
			$this->_var_m2mtf_model_label = null;
			$this->_var_m2mtf_model_reference = null;
		}
		else {
			$this->_var_controller = lcfirst($model->controller_name);
			$this->_var_namespace = ucfirst($model->controller_name);
			$this->_var_model_name = ucfirst($model->model_name);
			$this->_var_model_label = ucfirst($model->model_label);
			$this->_var_model_reference = lcfirst($model->model_name);
			$this->_var_m2mtf_name = lcfirst($model->m2mtf_name);
			$this->_var_m2mtf_name_ucfirst = ucfirst($model->m2mtf_name);
			$this->_var_m2mtf_model_name = ucfirst($model->m2mtf_model_name);
			$this->_var_m2mtf_model_label = ucfirst($model->m2mtf_model_label);
			$this->_var_m2mtf_model_reference = lcfirst($model->m2mtf_model_name);
		}
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Gino\AdminTable::action()
	 * 
	 * Custom: al salvataggio del modello Gino.App.BuildApp.Item crea le directory del modulo da creare 
	 * e i file necessari al suo funzionamento.
	 */
    public function action($model_form, $options_form, $options_field) {
    	
    	$model = $model_form->getModel();
    	
    	$insert = !$model->id;
    	$popup = \Gino\cleanVar($this->_request->POST, '_popup', 'int');
    	
    	// link error
    	$link_error = $this->editUrl(array(), array());
    	$options_form['link_error'] = $link_error;
    	
    	// CUSTOM
    	$controller_name = \Gino\cleanVar($this->_request->POST, 'controller_name', 'string');
    	$controller_name = lcfirst($controller_name);
    	
    	$this->_new_app_dir = APP_DIR.OS.$controller_name;
    	$this->_new_views_dir = APP_DIR.OS.$controller_name.OS.'views';
    	$this->_new_content_dir = CONTENT_DIR.OS.$controller_name;
    	
    	if(file_exists($this->_new_app_dir)) {
    		throw new \Exception(sprintf(_("impossibile creare l'applicazione, la directory app/%s è già presente"), $controller_name));
    	}
    	// /CUSTOM
    	
    	$action_result = $model_form->save($options_form, $options_field);
    	
    	// CUSTOM
    	if($action_result) {
    		mkdir($this->_new_app_dir);
    		mkdir($this->_new_views_dir);
    		mkdir($this->_new_content_dir);
    		
    		$this->buildFile($model);
    	}
    	// /CUSTOM
    	
    	// link success
    	if(isset($options_form['link_return']) and $options_form['link_return']) {
    		$link_return = $options_form['link_return'];
    	}
    	else {
    		if(isset($this->_request->POST['save_and_continue']) and !$insert) {
    			$link_return = $this->editUrl(array(), array());
    		}
    		elseif(isset($this->_request->POST['save_and_continue']) and $insert) {
    			$link_return = $this->editUrl(array('edit' => 1, 'id' => $model->id), array('insert'));
    		}
    		else {
    			$link_return = $this->editUrl(array(), array('insert', 'edit', 'id'));
    		}
    	}
    	if($action_result === TRUE and $popup) {
    		$script = "<script>opener.gino.dismissAddAnotherPopup(window, '$model->id', '".htmlspecialchars((string) $model, ENT_QUOTES)."' );</script>";
    		return new \Gino\Http\Response($script, array('wrap_in_document' => FALSE));
    	}
    	elseif($action_result === TRUE) {
    		return new \Gino\Http\Redirect($link_return);
    	}
    	else {
    		return Error::errorMessage($action_result, $link_error);
    	}
    }
    
    /**
     * @brief Genera i file dell'app
     * 
     * @param \Gino\App\BuildApp\Item $model istanza di Gino.App.BuildApp.Item
     * @throws \Exception
     * @return NULL
     */
    private function buildFile($model) {
    	
    	$this->setVariables($model);
    	
    	// define if many to many through field
    	if($model->m2mtf && $model->m2mtf_name && $model->m2mtf_model_name) {
    	    $m2mtf = true;
    	    $codefile = '_m2mtf';
    	}
    	else {
    	    $m2mtf = false;
    	    $codefile = '';
    	}
    	
    	$array = array();
    	
    	// CSS
    	$frame_file = 'controller.css';
    	$new_file = $this->_var_controller.'.css';
    	$array[$frame_file] = $new_file;
    	
    	// INI
    	$frame_file = 'controller.ini';
    	$new_file = $this->_var_controller.'.ini';
    	$array[$frame_file] = $new_file;
    	
    	// SQL
    	$frame_file = 'tables'.$codefile.'.sql';
    	$new_file = $this->_var_controller.'.sql';
    	$array[$frame_file] = $new_file;
    	
    	// Controller
    	$frame_file = 'class_controller'.$codefile.'.txt';
    	$new_file = 'class_'.$this->_var_controller.'.php';
    	$array[$frame_file] = $new_file;
    	
    	// Model
    	$frame_file = 'class.Model'.$codefile.'.txt';
    	$new_file = 'class.'.$this->_var_model_name.'.php';
    	$array[$frame_file] = $new_file;
    	
    	// Category
    	$frame_file = 'class.Category.txt';
    	$new_file = 'class.Category.php';
    	$array[$frame_file] = $new_file;
    	
    	// ManytoMany Through Model
    	$frame_file = 'class.M2mtf.txt';
    	$new_file = 'class.'.$this->_var_m2mtf_model_name.'.php';
    	$array[$frame_file] = $new_file;
    	
    	// Views
    	$array['archive.txt'] = 'archive.php';
    	$array['detail'.$codefile.'.txt'] = 'detail.php';
    	
    	// elenco file che si trovano nelle directory instance/not-instance
    	$schema_files = [
    	    'archive.txt', 
    	    'detail.txt', 
    	    'detail_m2mtf.txt', 
    	    'tables.sql', 
    	    'tables_m2mtf.sql', 
    	    'class.M2mtf.txt', 
    	    'class.Category.txt', 
    	    'class.Model.txt', 
    	    'class.Model_m2mtf.txt',
    	    'class_controller.txt',
    	    'class_controller_m2mtf.txt'
    	];
    	
    	if(is_array($array)) {
    		
    		foreach($array AS $frame_file => $new_file) {
    			
    		    // retrieves the directory that contains the schema files
    			if($model->istantiable && in_array($frame_file, $schema_files)) {
    			
    				$frame_dir = $this->_frame_dir_instance;	
    			}
    			elseif(!$model->istantiable && in_array($frame_file, $schema_files)) {
    			
    				$frame_dir = $this->_frame_dir_not_instance;
    			}
    			else {
    				$frame_dir = $this->_frame_dir;
    			}
    			
    			// Reads entire file into a string
    			$file_content = file_get_contents($frame_dir.$frame_file);
    			
    			preg_match_all("#{{[^}]+}}#", $file_content, $matches);
    			$content = $this->parseFile($model, $frame_file, $file_content, $matches);
    			
    			if($frame_file == 'archive.txt' or $frame_file == 'detail.txt' or $frame_file == 'detail_m2mtf.txt') {
    				$new_dir = $this->_new_views_dir;
    			}
    			else {
    				$new_dir = $this->_new_app_dir;
    			}
    			
    			if(!($fo = @fopen($new_dir.OS.$new_file, 'wb'))) {
    				throw new \Exception(sprintf(_("impossibile creare il file %s"), $new_file));
    			}
    			
    			fwrite($fo, $content);
    			fclose($fo);
    		}
    	}
    	return null;
    }
    
    /**
     * @brief Parserizza i file schema
     * 
     * @param \Gino\App\BuildApp\Item $model istanza di Gino.App.BuildApp.Item
     * @param string $file_name nome del file da leggere e riscrivere
     * @param string $content contenuto del file
     * @param array $matches matches delle variabili da sostituire
     * @return string, template parserizzato
     */
    private function parseFile($model, $file_name, $content, $matches) {
    
    	if(isset($matches[0])) {
    		foreach($matches[0] as $m) {
    			
    			$replace = $this->replaceVar($file_name, $m, $model);
    			$content = preg_replace("#".preg_quote($m)."#", $replace, $content);
    		}
    	}
    	
    	return $content;
    }
    
    /**
     * @brief Sostituisce i codici presenti nei file schema col valore corretto
     *
     * @param string $file_name nome del file da leggere e riscrivere
     * @param string $match codice di cui è stata trovata una corrispondenza nella seguente istruzione di self::buildFile():
     *   @code
     *   preg_match_all("#{{[^}]+}}#", $file_content, $matches);
     *   @endcode
     * @param \Gino\App\BuildApp\Item $model istanza di Gino.App.BuildApp.Item
     * @return string, replace del parametro proprietà
     */
    private function replaceVar($file_name, $match, $model) {
        
        $matches = $this->matches();
        
        if($file_name == 'controller.css') {
            
            $content = '';
            
            $codes = ['MODEL', 'CONTROLLER'];
            foreach ($codes AS $c) {
                if(array_key_exists($c, $matches)) {
                    
                    if(preg_match("#{{".$c."}}#", $match)) {
                        $content = preg_replace("#{{".$c."}}#", $matches[$c], $match);
                    }
                }
            }
            return $content;
        }
        elseif($file_name == 'controller.ini') {
            
            if($model->istantiable) {
                $string_replace = 'Doc';
            }
            else {
                $string_replace = $namespace;
            }
            
            $content = '';
            if(preg_match("#{{METHODNAME}}#", $match)) {
                $content = preg_replace("#{{METHODNAME}}#", $string_replace, $match);
            }
            return $content;
        }
        elseif($file_name == 'tables.sql' or $file_name == 'tables_m2mtf.sql') {
            
            $content = '';
            $codes = ['TABLEKEY', 'MODELREFERENCE', 'M2MTFMODELREFERENCE'];
            foreach ($codes AS $c) {
                if(array_key_exists($c, $matches)) {
                    
                    if(preg_match("#{{".$c."}}#", $match)) {
                        $content = preg_replace("#{{".$c."}}#", $matches[$c], $match);
                    }
                }
            }
            return $content;
        }
        elseif($file_name == 'class_controller.txt' or $file_name == 'class_controller_m2mtf.txt') {
            
            $content = '';
            $codes = ['CONTROLLER', 'CONTROLLER_NS', 'TABLEKEY', 'MODEL', 'MODELREFERENCE', 'M2MTFMODELNAME', 'M2MTFMODELREFERENCE'];
            foreach ($codes AS $c) {
                if(array_key_exists($c, $matches)) {
                    
                    if(preg_match("#{{".$c."}}#", $match)) {
                        $content = preg_replace("#{{".$c."}}#", $matches[$c], $match);
                    }
                }
            }
            
            // custom
            if(preg_match("#{{METHODNAME}}#", $match)) {
                $content = preg_replace("#{{METHODNAME}}#", $this->_var_namespace, $match);
            }
            return $content;
        }
        elseif($file_name == 'class.Model.txt' or $file_name == 'class.Model_m2mtf.txt') {
            
            $content = '';
            $codes = [
                'CONTROLLER', 
                'CONTROLLER_NS', 
                'TABLEKEY', 
                'MODEL', 
                'MODEL_LABEL', 
                'MODELREFERENCE', 
                'M2MTFNAME',
                'M2MTFMODELNAME', 
                'M2MTFMODELLABEL',
                'M2MTFMODELREFERENCE',
                'M2MTFNAMEUCFIRST'
            ];
            foreach ($codes AS $c) {
                if(array_key_exists($c, $matches)) {
                    
                    if(preg_match("#{{".$c."}}#", $match)) {
                        $content = preg_replace("#{{".$c."}}#", $matches[$c], $match);
                    }
                }
            }
            return $content;
        }
        elseif($file_name == 'class.M2mtf.txt') {
            
            $content = '';
            $codes = [
                'CONTROLLER',
                'CONTROLLER_NS',
                'TABLEKEY',
                'MODEL',
                'MODEL_LABEL',
                'MODELREFERENCE',
                'M2MTFNAME',
                'M2MTFMODELNAME',
                'M2MTFMODELLABEL',
                'M2MTFMODELREFERENCE',
                'M2MTFNAMEUCFIRST'
            ];
            foreach ($codes AS $c) {
                if(array_key_exists($c, $matches)) {
                    
                    if(preg_match("#{{".$c."}}#", $match)) {
                        $content = preg_replace("#{{".$c."}}#", $matches[$c], $match);
                    }
                }
            }
            return $content;
        }
        elseif($file_name == 'class.Category.txt') {
            
            $content = '';
            $codes = [
                'CONTROLLER',
                'CONTROLLER_NS',
                'TABLEKEY',
            ];
            foreach ($codes AS $c) {
                if(array_key_exists($c, $matches)) {
                    
                    if(preg_match("#{{".$c."}}#", $match)) {
                        $content = preg_replace("#{{".$c."}}#", $matches[$c], $match);
                    }
                }
            }
            return $content;
        }
        elseif($file_name == 'archive.txt' or $file_name == 'detail.txt' or $file_name == 'detail_m2mtf.txt') {
            
            $content = '';
            $codes = [
                'CONTROLLER',
                'CONTROLLER_NS',
                'MODEL',
                'M2MTFNAME',
                'M2MTFMODELNAME',
            ];
            foreach ($codes AS $c) {
                if(array_key_exists($c, $matches)) {
                    
                    if(preg_match("#{{".$c."}}#", $match)) {
                        $content = preg_replace("#{{".$c."}}#", $matches[$c], $match);
                    }
                }
            }
            return $content;
        }
        else {
            return null;
        }
    }
    
    /**
     * @brief Elenco delle corrispondenze tra i codici di sostituzione e il loro valore corrispettivo
     * @return string[]
     * 
     * Codici di sostituzione: \n
     * - CONTROLLER, nome del Controller (iniziale minuscola)
     * - CONTROLLER_NS, nome del Namespace, che corrisponde al nome del Controller con l'iniziale maiuscola
     * - TABLEKEY, nome del prefisso delle tabelle (corrisponde al nome del controller)
     * - MODEL, nome del Modello (iniziale maiuscola)
     * - MODELREFERENCE, nome del modello utilizzato per definire le tabelle del modello
     * - MODEL_LABEL, label del modello
     * - M2MTFNAME, nome del campo ManytoManyThrough (ad esempio images)
     * - M2MTFMODELNAME, nome del Modello agganciato al campo ManytoManyThrough (ad esempio Image)
     * - M2MTFMODELLABEL, label del Modello agganciato al campo ManytoManyThrough
     * - M2MTFMODELREFERENCE, nome del modello M2MTF utilizzato per definire le tabelle (ad esempio image)
     * - M2MTFNAMEUCFIRST, nome del campo ManytoManyThrough con il primo carattere maiuscolo (ad esempio Images)
     * 
     * Codici di sostituzione con valori che variano in relazione al tipo di file: \n
     * - METHODNAME, identifica il nome di un metodo, ad esempio il suffisso del metodo del Costruttore che gestisce l'interfaccia di amministrazione del modulo
     */
    private function matches() {
        
        $matches = [
            "CONTROLLER" => $this->_var_controller,
            "CONTROLLER_NS" => $this->_var_namespace,
            "TABLEKEY" => $this->_var_controller,
            "MODEL" => $this->_var_model_name,
            "MODELREFERENCE" => $this->_var_model_reference,
            "MODEL_LABEL" => $this->_var_model_label,
            "M2MTFNAME" => $this->_var_m2mtf_name,
            "M2MTFMODELNAME" => $this->_var_m2mtf_model_name,
            "M2MTFMODELREFERENCE" => $this->_var_m2mtf_model_reference,
            "M2MTFMODELLABEL" => $this->_var_m2mtf_model_label,
            "M2MTFNAMEUCFIRST" => $this->_var_m2mtf_name_ucfirst,
        ];
        
        return $matches;
    }
}
