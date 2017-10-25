<?php
/**
 * @file class_buildapp.php
 * @brief Contiene la definizione ed implementazione della classe Gino.App.BuildApp.buildapp.
 *
 * @version 1.0
 * @copyright 2017 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

/**
 * @namespace Gino.App.BuildApp
 * @description Namespace dell'applicazione BuildApp, per la creazione di app
 */
namespace Gino\App\BuildApp;

use Gino\Http\Request;
use Gino\Http\Response;
use Gino\Http\Redirect;
use \Gino\View;
use \Gino\Document;

require_once('class.Item.php');
require_once('class.FormAdminTable.php');

/**
 * @brief Classe di tipo Gino.Controller per la creazione di nuove applicazioni.
 * 
 * ##CARATTERISTICHE
 * Permette di creare applicazioni istanziabili e non istanziabili. 
 * La procedura crea le directory app/newapp, app/newapp/views e contents/newapp, e crea tutti i file necessari al funzionamento del modulo 
 * parserizzando dei file schema che si trovano nella directory contents/buildapp/.
 * Il file SQL per creare le tabelle dell'applicazione viene caricato nella directory app/newapp col nome newapp.sql.
 * 
 * ##PERMESSI
 * - amministrazione modulo (@a can_admin)
 * 
 * @copyright 2017 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class buildapp extends \Gino\Controller {

	/**
     * @brief Costruttore
     * @return istanza di Gino.App.BuildApp.buildapp
     */
    function __construct() {

        parent::__construct();
        
    }

    /**
     * @brief Restituisce alcune proprietà della classe
     * @return lista delle proprietà utilizzate
     */
    public static function getClassElements() {

        return array(
            "tables"=>array(
                'buildapp_item',
            ),
            "css"=>array(
                'buildapp.css'
            ),
            'views' => array(
                
            ),
            "folderStructure"=>array (
                CONTENT_DIR.OS.'buildapp'=> null
            )
        );
    }

    /**
     * @brief Definizione dei metodi pubblici che forniscono un output per il front-end
     *
     * Questo metodo viene letto dal motore di generazione dei layout (prende i metodi non presenti nel file ini) e dal motore di generazione di 
     * voci di menu (presenti nel file ini) per presentare una lista di output associati all'istanza di classe.
     *
     * @return array, METHOD_NAME => array('label' => (string) [label], 'permissions' => (array) [permissions list in the format classname.code_perm])
     */
    public static function outputFunctions() {

        $list = array(
            
        );

        return $list;
    }

    /**
     * @brief Percorso base alla directory dei contenuti
     *
     * @param string $path tipo di percorso (default abs)
     *   - abs, assoluto
     *   - rel, relativo
     * @return percorso
     */
    public function getBasePath($path='abs'){

        $directory = '';

        if($path == 'abs')
            $directory = $this->_data_dir.OS;
        elseif($path == 'rel')
            $directory = $this->_data_www.'/';

        return $directory;
    }

    /**
     * @brief Interfaccia di amministrazione del modulo
     * @param \Gino\Http\Request $request istanza di Gino.Http.Request
     * @return Gino.Http.Response interfaccia di back office
     */
    public function manageBuildapp(\Gino\Http\Request $request) {

        \Gino\Loader::import('class', '\Gino\AdminTable');

        $block = \Gino\cleanVar($request->GET, 'block', 'string', '');
        $action = \Gino\cleanVar($request->GET, 'action', 'string', '');

        $this->requirePerm(array('can_admin'));
        
        $link_frontend = sprintf('<a href="%s">%s</a>', $this->linkAdmin(array(), 'block=frontend'), _('Frontend'));
        $link_dft = sprintf('<a href="%s">%s</a>', $this->linkAdmin(), _('Applicazioni'));

        $sel_link = $link_dft;

        if($block == 'frontend') {
            $backend = $this->manageFrontend();
            $sel_link = $link_frontend;
        }
        else {
            $backend = $this->manageItem($request);
        }

        $links_array = array($link_frontend, $link_dft);

        if(is_a($backend, '\Gino\Http\Response')) {
            return $backend;
        }

        $view = new View();
        $view->setViewTpl('tab');
        $dict = array(
            'title' => _('Moduli generati'),
            'links' => $links_array,
            'selected_link' => $sel_link,
            'content' => $backend
        );

        $document = new Document($view->render($dict));
        return $document();
    }

    /**
     * @brief Interfaccia di amministrazione delle pagine
     *
     * @param \Gino\Http\Request $request istanza di Gino.Http.Request
     * @return Gino.Http.Redirect oppure html, interfaccia di back office delle pagine,
     */
    private function manageItem($request) {

        // Controllo validità del nome del controller
        $url = $this->link($this->_instance_name, 'checkControllerName');
        $div_id = 'check_name';
        
        $availability = "&nbsp;&nbsp;<span class=\"link\" onclick=\"gino.ajaxRequest('post', '$url', 'id='+$('id').getProperty('value')+'&cname='+$('controller_name').getProperty('value'), '$div_id')\">"._("verifica disponibilità")."</span>";
        $availability .= "<div id=\"$div_id\" style=\"display:inline; margin-left:10px; font-weight:bold;\"></div>\n";

        $description = _("Il processo di creazione genera le directory app/newapp e contents/newapp, caricando tutti i file necessari al funzionamento del modulo, 
        		tra i quali il file SQL (newapp.sql) per creare le tabelle dell'applicazione.
        		Il modulo deve essere successivamente installato manualmente nei %sModuli di sistema%s.");
        
        $admin_table = new \Gino\App\BuildApp\FormAdminTable($this, array(
        	'allow_insertion' => true,
        	'delete_deny' => null,
        	'edit_deny' => 'all',
        ));
        $backend = $admin_table->backOffice(
            'Item',
            array(
                'list_display' => array('id', 'label', 'controller_name', 'description', 'creation_date'),
                'list_title' => _("Elenco applicazioni"), 
                'filter_fields' => array('label', 'controller_name', 'description'), 
            	'list_description' => sprintf($description, "<a href=\"sysClass/manageSysClass/\" rel=\"external\">", "</a>")
            ),
        	array(
                'removeFields' => null
            ),
            array(
                'id' => array(
                    'id' => 'id'
                ),
            	'controller_name' => array(
            		'id' => 'controller_name', 
            		'text_add' => $availability,
            	),
            )
        );

        return $backend;
    }
    
    /**
     * @brief Controlla l'unicità del valore del campo controller_name
     * @param \Gino\Http\Request $request istanza di Gino.Http.Request
     * @return Gino.Http.Response (disponibile / non disponibile)
     */
    public function checkControllerName(\Gino\Http\Request $request) {
    
    	$id = \Gino\cleanVar($request->POST, 'id', 'int', '');
    	$controller_name = \Gino\cleanVar($request->POST, 'cname', 'string', '');
    	
    	if(!$controller_name)
    	{
    		$valid = FALSE;
    	}
    	else
    	{
    		$where_add = $id ? " AND id!='$id'" : '';
    		
    		$modules = \Gino\App\SysClass\ModuleApp::getModuleList();
    		if(in_array($controller_name, $modules)) {
    			$valid = false;
    		}
    		else {
    			$valid = true;
    		}
    	}
    
    	$content = $valid ? _("valido") : _("non valido");
    
    	return new \Gino\Http\Response($content);
    }
}
