<?php
/**
 * @file class_layout.php
 * @brief Contiene la definizione ed implementazione della classe Gino.App.Layout.layout
 *
 * @copyright 2005-2018 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

/**
 * @namespace Gino.App.Layout
 * @description Namespace dell'applicazione Layout, che gestisce i layout del sistema.
 *              Consente la modifica dei template, css e viste generali utilizzate da tutto il sistema.
 */
namespace Gino\App\Layout;

use \Gino\View;
use \Gino\Document;
use Gino\Http\Response;
use \Gino\Http\Redirect;
use \Gino\App\Page\PageEntry;
use \Gino\App\Module\ModuleInstance;
use \Gino\App\SysClass\ModuleApp;
use \Gino\App\Auth\Permission;

/**
 * @brief Gestisce il layout dell'applicazione raggruppando le funzionalità fornite dalle librerie dei css, template e skin
 *
 * @see Gino.Css
 * @see Gino.Template
 * @see Gino.Skin
 *
 * Fornisce le interfacce per la modifica dei file di frontend generali di gino: \n
 *   - file css presenti nella directory @a css
 *   - file delle viste presenti nella directory @a views
 *
 * ## PROCESSO DI GESTIONE DEL LAYOUT A BLOCCHI
 * Lo schema del layout viene stampato dal metodo template::manageTemplate() che legge il file di template e identifica porzioni di codice tipo:
 * @code
 * <div id="nav_3_1_1" style="width:100%;">
 * {module sysclassid=8 func=printFooterPublic}
 * </div>
 * @endcode
 * Queste porzioni di codice vengono passate con la funzione preg_replace_callback() al metodo Gino.Template::renderNave() che recupera il tipo di blocco nello schema del template utilizzando delle funzioni di preg_match(). \n
 * L'elenco dei moduli/pagine disponibili viene gestito dal metodo Gino.App.Layout.layout::modulesList().
 *
 * ## LAYOUT FREE
 * I layout free sono gestiti direttamente editando il file php del template. Anche in questo caso si usa un meta linguaggio per inserire output di moduli nelle posizioni desiderate.
 *
 * @copyright 2005-2018 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class layout extends \Gino\Controller {

    function __construct() {

        parent::__construct();
    }

    /**
     * @brief Interfaccia amministrativa per la gestione del layout
     * @param \Gino\Http\Request $request istanza di Gino.Http.Request
     * @return Gino.Http.Response
     */
    public function manageLayout(\Gino\Http\Request $request) {

        $this->requirePerm('can_admin');

        $block = \Gino\cleanVar($request->GET, 'block', 'string', null);

        $link_dft = ['link' => $this->linkAdmin(), 'label' => _('Informazioni')];
        $link_tpl = ['link' => $this->linkAdmin(array(), 'block=template'), 'label' => _('Template')];
        $link_skin = ['link' => $this->linkAdmin(array(), 'block=skin'), 'label' => _('Skin')];
        $link_css = ['link' => $this->linkAdmin(array(), 'block=css'), 'label' => _('CSS')];
        $link_view = ['link' => $this->linkAdmin(array(), 'block=view'), 'label' => _('Viste')];

        $sel_link = $link_dft;

        if($block == 'template') {
            $backend = $this->manageTemplate($request);
            $sel_link = $link_tpl;
        }
        elseif($block == 'skin') {
            $backend = $this->manageSkin($request);
            $sel_link = $link_skin;
        }
        elseif($block == 'css') {
            $backend = $this->manageCss($request);
            $sel_link = $link_css;
        }
        elseif($block == 'view') {
            $backend = $this->manageView($request);
            $sel_link = $link_view;
        }
        else {
            $backend = $this->info();
        }

        if(is_a($backend, '\Gino\Http\Response')) {
            return $backend;
        }

        $view = new View();
        $view->setViewTpl('tabs');
        $dict = array(
            'title' => _('Layout'),
            'links' => array($link_view, $link_css, $link_skin, $link_tpl, $link_dft),
            'selected_link' => $sel_link,
            'content' => $backend
        );

        $document = new Document($view->render($dict));
        return $document();
    }

    /**
     * @brief Interfaccia di amministrazione dei template
     * @param \Gino\Http\Request $request istanza di Gino.Http.Request
     * @return Gino.Http.Response or string
     */
    private function manageTemplate(\Gino\Http\Request $request) {

        $id = \Gino\cleanVar($request->REQUEST, 'id', 'int');
        $action = \Gino\cleanVar($request->GET, 'action', 'string');
        
        $tpl = \Gino\Loader::load('Template', array($id));

        if($request->checkGETKey('trnsl', '1'))
        {
            return $this->_trd->manageTranslation($request);
        }

        if($action == 'mngblocks') {

            $content = $tpl->tplBlockForm($request);
            return new Response($content);
        }
        elseif($action == 'mngtpl') {

            $css_id = \Gino\cleanVar($request->POST, 'css', 'int');
            $css = \Gino\Loader::load('Css', array('layout', array('id'=>$css_id)));
            return new Response($tpl->manageTemplate($css, $id));
        }
        elseif($action == 'insert' || $action == 'modify') {

            $free = \Gino\cleanVar($request->GET, 'free', 'int', null);
            if($free) {
                $buffer = $tpl->formFreeTemplate();
            }
            else {
                $buffer = $tpl->formTemplate();
            }
        }
        elseif($action == 'delete') {
            $buffer = $tpl->formDelTemplate();
        }
        elseif($action == 'outline') {
            $buffer = $tpl->formOutline();
        }
        elseif($action=='addblocks') {

            $content = $tpl->addBlockForm(null, $request);
            return new Response($content);
        }
        elseif($action=='copy') {
            $buffer = $tpl->formCopyTemplate();
        }
        elseif($action=='copytpl') {
            return $tpl->actionCopyTemplate($request);
        }
        else {
            $buffer = $this->templateList();
        }

        return $buffer;
    }

    /**
     * @brief Interfaccia di amministrazione delle skin
     * @param \Gino\Http\Request $request istanza di Gino.Http.Request
     * @return Gino.Http.Response o html
     */
    private function manageSkin(\Gino\Http\Request $request) {

        $id = \Gino\cleanVar($request->REQUEST, 'id', 'int', '');
        $skin = \Gino\Loader::load('Skin', array($id));
        $action = \Gino\cleanVar($request->GET, 'action', 'string');
        
        if($action == 'insert' || $action == 'modify') {
            if($request->checkGETKey('trnsl', '1'))
            {
                return $this->_trd->manageTranslation($request);
            }
            else
            {
                $buffer = $skin->formSkin();
            }
        }
        elseif($action == 'sortup') {
            $skin->sortUp();
            return new Redirect($this->linkAdmin(array(), "block=skin"));
        }
        elseif($action == 'delete') {
            $buffer = $skin->formDelSkin();
        }
        else {
            $buffer = $this->skinList();
        }

        return $buffer;
    }

    /**
     * @brief Interfaccia di amministrazione dei CSS
     * @param \Gino\Http\Request $request istanza di Gino.Http.Request
     * @return Gino.Http.Response o html
     */
    private function manageCss($request) {

        $id = \Gino\cleanVar($request->REQUEST, 'id', 'int', '');
        $action = \Gino\cleanVar($request->GET, 'action', 'string');

        $css = \Gino\Loader::load('Css', array($id));
        
        if($action == 'insert' or $action == 'modify') {

            if($request->checkGETKey('trnsl', '1'))
            {
                return $this->_trd->manageTranslation($request);
            }
            else
            {
                $buffer = $css->formCssLayout();
            }
        }
        elseif($action == 'delete') {
            $buffer = $css->formDelCssLayout();
        }
        elseif($action == 'edit') {
            $fname = \Gino\cleanVar($request->GET, 'fname', 'string', null);
            $buffer = $this->formFiles($fname, 'css');
        }
        else {
            $buffer = $this->cssList();
        }

        return $buffer;
    }

    /**
     * @brief Interfaccia di amministrazione delle viste
     * @param \Gino\Http\Request $request istanza di Gino.Http.Request
     * @return Gino.Http.Response o html
     */
    private function manageView($request) {

        $action = \Gino\cleanVar($request->GET, 'action', 'string');
        if($action == 'edit') {
            $fname = \Gino\cleanVar($request->GET, 'fname', 'string', null);
            $buffer = $this->formFiles($fname, 'view');
        }
        else {
            $buffer = $this->viewList();
        }

        return $buffer;
    }

    /**
     * @brief Lista delle skin
     * @return string, lista skin
     */
    private function skinList() {

        \Gino\Loader::import('class', '\Gino\Template');
        \Gino\Loader::import('class', '\Gino\Css');

        $link_insert = sprintf('<a href="%s">%s</a>', $this->linkAdmin(array(), 'block=skin&action=insert'), \Gino\icon('insert', array('text' => _("nuova skin"), 'scale'=>2)));

        $skin_list = \Gino\Skin::objects(null, array('order' => 'priority'));
        if(count($skin_list)) {
            $view_table = new View();
            $view_table->setViewTpl('table');
            $view_table->assign('heads', array(
                _('Etichetta'),
                _('Template'),
                _('Css'),
            	_("Regole"),
                _('Autenticazione'),
                _('Cache'),
                ''
            ));
            $tbl_rows = array();

            $i = 0;
            foreach($skin_list as $skin) {
                $link_modify = sprintf('<a href="%s">%s</a>', $this->linkAdmin(array(), "block=skin&id={$skin->id}&action=modify"), \Gino\icon('modify'));
                $link_delete = sprintf('<a href="%s">%s</a>', $this->linkAdmin(array(), "block=skin&id={$skin->id}&action=delete"), \Gino\icon('delete'));
                $link_sort = $i ? sprintf('<a href="%s">%s</a>', $this->linkAdmin(array(), "block=skin&id={$skin->id}&action=sortup"), \Gino\icon('sort-up')) : '';
                $tpl = new \Gino\Template($skin->template);
                
                $css = new \Gino\Css(null, array('type' => 'layout', 'id' => $skin->css));
                
                $settings = array();
                if($skin->highest) {
                	$settings[] = "<b>"._("maximum")."</b>";
                }
                if($skin->session) {
                	$settings[] = _("sessione");
                }
                if($skin->urls) {
                	$settings[] = _("url");
                }
                if($skin->rexp) {
                	$settings[] = _("regexp");
                }
                $settings = implode("<br />", $settings);
                
                $tbl_rows[] = array(
                    $skin->ml('label'),
                    $tpl->ml('label'),
                    $css->label ? $css->label." (".$css->filename.")" : null,
                	$settings,
                    $skin->auth == 'yes' ? _('si') : ($skin->auth == 'no' ? _('no') : _('si & no')),
                    $skin->cache ? _('si') : _('no'),
                    array('text' => implode(' &#160; ', array($link_modify, $link_delete, $link_sort)), 'class' => 'nowrap')
                );
                $i++;
            }
            $view_table->assign('class', 'table table-striped', 'table-hover');
            $view_table->assign('rows', $tbl_rows);
            $buffer = "<p class=\"backoffice-info\">".sprintf(_("Le skin sono elencate in ordine di priorità decrescente. 
Tuttavia, l'ordine visualizzato non tiene conto delle regole sulle sessioni e dell'opzione <i>Priorità massima</i> 
che possono essere state impostate nelle skin e che sovrascrivono l'ordine visualizzato.%s
La presenza di queste regole nelle skin può essere identificata nella colonna <i>Regole</i> dell'elenco.%s
Per modificare le priorità cliccare sull'icona a forma di triangolo."), "<br />", "<br />")."</p>";
            $buffer .= $view_table->render();
        }
        else {
            $buffer = "<p>"._("Non risultano skin registrate")."</p>\n";
        }

        $view = new View();
        $view->setViewTpl('section');
        $dict = array(
            'title' => _('Elenco skin'),
            'class' => null,
            'header_links' => $link_insert,
            'content' => $buffer
        );

        return $view->render($dict);
    }

    /**
     * @brief Lista dei template
     * @return string, lista template
     */
    private function templateList() {

        $link_insert = sprintf('<a href="%s">%s</a>', $this->linkAdmin(array(), 'block=template&action=insert'), \Gino\icon('insert', array('text' => _("nuovo template a blocchi"), 'scale'=>2)));
        $link_insert_free = sprintf('<a href="%s">%s</a>', $this->linkAdmin(array(), 'block=template&action=insert&free=1'), \Gino\icon('code', array('text' => _("nuovo template libero"), 'scale'=>2)));

        $tpl_list = \Gino\Template::objects(null, array('order' => 'label'));
        if(count($tpl_list)) {
            $view_table = new View();
            $view_table->setViewTpl('table');
            $view_table->assign('heads', array(
            _('Etichetta'),
            _('File'),
            _('Descrizione'),
            ''
            ));
            $tbl_rows = array();
            foreach($tpl_list as $tpl) {

                $link_modify = sprintf('<a href="%s">%s</a>', $this->linkAdmin(array(), "block=template&id={$tpl->id}&action=modify&free=".$tpl->free), \Gino\icon('modify', array('text' => _("modifica il template"))));
                $link_delete = sprintf('<a href="%s">%s</a>', $this->linkAdmin(array(), "block=template&id={$tpl->id}&action=delete"), \Gino\icon('delete'));
                $link_outline = sprintf('<a href="%s">%s</a>', $this->linkAdmin(array(), "block=template&id={$tpl->id}&action=outline"), \Gino\icon('layout', array('text' => _("modifica lo schema"))));
                $link_copy = sprintf('<a href="%s">%s</a>', $this->linkAdmin(array(), "block=template&id={$tpl->id}&action=copy"), \Gino\icon('copy', array('text' => _("crea una copia"))));

                $links = $tpl->free
                    ? array($link_delete, $link_modify)
                    : array($link_delete, $link_copy, $link_modify, $link_outline);

                $tbl_rows[] = array(
                    $tpl->ml('label'),
                    $tpl->filename,
                    $tpl->ml('description'),
                    implode(' &#160; ', $links)
                );
            }
            $view_table->assign('class', 'table table-striped', 'table-hover');
            $view_table->assign('rows', $tbl_rows);
            $buffer = $view_table->render();
        }
        else {
            $buffer = "<p>"._("Non risultano template registrati")."</p>\n";
        }

        $view = new View();
        $view->setViewTpl('section');
        $dict = array(
        'title' => _('Elenco template'),
            'class' => null,
        'header_links' => array($link_insert, $link_insert_free),
        'content' => $buffer
        );

        return $view->render($dict);
    }

    /**
     * @brief Lista dei css
     * @return string, lista css
     */
    private function cssList() {

        $link_insert = sprintf('<a href="%s">%s</a>', $this->linkAdmin(array(), 'block=css&action=insert'), \Gino\icon('insert', array('text' => _("nuovo file css"), 'scale'=>2)));

        $view_table = new View();
        $view_table->setViewTpl('table');
        $view_table->assign('heads', array(
            _('Etichetta'),
            _('File'),
            _('Descrizione'),
            ''
        ));
        $tbl_rows = array();

        $dir = CSS_DIR;
        $files = array();
        if(is_dir($dir)) {
            if($dh = opendir($dir)) {
                while (($file = readdir($dh)) !== false) {
                    if($file != "." && $file != ".." && preg_match('#^[0-9a-zA-Z]+[0-9a-zA-Z_.\-]+\.css$#', $file)) {
                        $files[] = $file;
                    }
                }
                closedir($dh);
            }
        }

        foreach($files as $file) {
            $css = \Gino\Css::getFromFilename($file);
            $link_edit = sprintf('<a href="%s">%s</a>', $this->linkAdmin(array(), "block=css&fname=$file&action=edit"), \Gino\icon('write', array('text' => _('modifica file'))));
            if($css and $css->id) {

                $link_modify = sprintf('<a href="%s">%s</a>', $this->linkAdmin(array(), "block=css&id={$css->id}&action=modify"), \Gino\icon('modify'));
                $link_delete = sprintf('<a href="%s">%s</a>', $this->linkAdmin(array(), "block=css&id={$css->id}&action=delete"), \Gino\icon('delete'));
                $tbl_rows[] = array(
                    \Gino\htmlChars($css->ml('label')),
                    $file,
                    \Gino\htmlChars($css->ml('description')),
                    implode(' &#160; ', array($link_edit, $link_modify, $link_delete))
                );
            }
            else {
                $tbl_rows[] = array(
                    _('CSS di sistema'),
                    $file,
                    '',
                    $link_edit
                );
            }
        }

        $view_table->assign('class', 'table table-striped', 'table-hover');
        $view_table->assign('rows', $tbl_rows);

        $buffer = "<div class=\"backoffice-info\">";
        $buffer .= "<p>"._('In questa sezione è possibile modificare fogli di stile di sistema (propri di gino), e fogli di stile custom, inseribili ed eliminabili da questa interfaccia. I fogli di stile di sistema non sono eliminabili in quanto inclusi automaticamente all\'interno del documento.')."</p>";
        $buffer .= "</div>";
        $buffer .= $view_table->render();

        $view = new View();
        $view->setViewTpl('section');
        $dict = array(
            'title' => _('Elenco fogli di stile'),
            'class' => null,
            'header_links' => $link_insert,
            'content' => $buffer
        );

        return $view->render($dict);
    }

    /**
     * @brief Lista delle viste
     * @return string, lista viste
     */
    private function viewList() {

        $view_table = new View();
        $view_table->setViewTpl('table');
        $view_table->assign('heads', array(
            _('file'),
            ''
        ));
        $tbl_rows = array();

        $dir = VIEWS_DIR;
        $files = array();
        if(is_dir($dir)) {
            if($dh = opendir($dir)) {
                while (($file = readdir($dh)) !== false) {
                    if($file != "." && $file != ".." && preg_match('#^[0-9a-zA-Z]+[0-9a-zA-Z_.\-]+\.php$#', $file)) {
                        $files[] = $file;
                    }
                }
                closedir($dh);
            }
        }

        sort($files);

        foreach($files as $file) {
            $link_edit = sprintf('<a href="%s">%s</a>', $this->linkAdmin(array(), "block=view&fname=$file&action=edit"), \Gino\icon('write', array('text' => _('modifica file'))));
            $tbl_rows[] = array(
                    $file,
                    $link_edit
            );
        }

        $view_table->assign('class', 'table table-striped', 'table-hover');
        $view_table->assign('rows', $tbl_rows);

        $buffer = "<div class=\"backoffice-info\">";
        $buffer .= "<p>"._('Queste sono le viste generali utilizzate da tutto il sistema e da molti moduli, pertanto eventuali modifiche si ripercuoteranno sulla visualizzazione di molte parti del sito.')."</p>";
        $buffer .= "<p>"._('Per modificare la visualizzazione di viste appartenenti a singoli moduli accedere all\'apposita interfaccia nella sezione di amministrazione del modulo.')."</p>";
        $buffer .= "</div>";
        $buffer .= $view_table->render();

        $view = new View();
        $view->setViewTpl('section');
        $dict = array(
            'title' => _('Elenco viste generali di sistema'),
            'class' => null,
            'content' => $buffer
        );

        return $view->render($dict);
    }

    /**
     * @brief Informazioni modulo
     * @return string, informazioni
     */
    private function info() {

        \Gino\Loader::import('class', array('\Gino\Css', '\Gino\Template', '\Gino\Skin'));

        $GINO = "<p>"._("In questa sezione è possibile gestire il layout del sito. Ad ogni request viene associata una skin, 
la quale caricherà il template associato ed eventualmente un foglio di stile. I passi da seguire per personalizzare il layout 
di una pagina o sezione del sito sono i seguenti").":</p>";
        $GINO .= "<ul>";
        $GINO .= "<li>"._("Creare ed uploadare un foglio di stile se necessario")."</li>";
        $GINO .= "<li>"._("Creare un template a blocchi utilizzando il motore di <i>gino</i> (file .tpl) oppure un template libero (file .php)")."</li>";
        $GINO .= "<li>"._("Creare una skin alla quale associare il template ed eventualmente il foglio di stile. 
La skin viene poi associata alla pagina o alla sezione desiderata definendo indirizzi, espressioni regolari di indirizzi 
oppure variabili di sessione.")."</li>";
        $GINO .= "<li>"._("Impostare la priorità della skin spostandola in alto o in basso.")."</li>";
        $GINO .= "</ul>";
        $GINO .= \Gino\Css::layoutInfo();
        $GINO .= \Gino\Template::layoutInfo();
        $GINO .= \Gino\Skin::layoutInfo();
        $GINO .= "<h2>"._('Viste')."</h2>";
        $GINO .= "<p>"._('In questa sezione si possono modificare le viste di sistema di gino. Sono viste generali utilizzate da buona parte dei moduli e dalla stessa area amministrativa.')."</p>";

        $view = new View();
        $view->setViewTpl('section');
        $dict = array(
            'title' => _('Layout'),
            'class' => null,
            'content' => $GINO
        );

        return $view->render($dict);
    }

    /**
     * @brief Processa il form di inserimento/modifica skin
     * @see Gino.Skin::formSkin()
     * @param \Gino\Http\Request $request istanza di Gino.Http.Request
     * @return Gino.Http.Redirect
     */
    public function actionSkin(\Gino\Http\Request $request) {

        $this->requirePerm('can_admin');

        $id = \Gino\cleanVar($request->POST, 'id', 'int', '');
        
        $skin = \Gino\Loader::load('Skin', array($id));
        
        return $skin->actionSkin($request);
    }

    /**
     * @brief Processa il form di eliminazione skin
     * @see Gino.Skin::formDelSkin()
     * @param \Gino\Http\Request $request istanza di Gino.Http.Request
     * @return Gino.Http.Redirect
     */
    public function actionDelSkin(\Gino\Http\Request $request) {

        $this->requirePerm('can_admin');

        $id = \Gino\cleanVar($request->POST, 'id', 'int', '');
        
        if(!$id) {
            throw new \Gino\Exception\Exception404();
        }
        $skin = \Gino\Loader::load('Skin', array($id));

        return $skin->actionDelSkin($request);
    }

    /**
     * @brief Processa il form di inserimento/modifica css
     * @see Gino.Css::formCssLayout()
     * @param \Gino\Http\Request $request istanza di Gino.Http.Request
     * @return Gino.Http.Redirect
     */
    public function actionCss(\Gino\Http\Request $request) {

        $this->requirePerm('can_admin');

        $id = \Gino\cleanVar($request->POST, 'id', 'int', '');
        
        $css = \Gino\Loader::load('Css', array($id));

        return $css->actionCssLayout($request);
    }

    /**
     * @brief Processa il form di eliminazione css
     * @see Gino.Css::formDelCssLayout()
     * @param \Gino\Http\Request $request istanza di Gino.Http.Request
     * @return Gino.Http.Redirect
     */
    public function actionDelCss(\Gino\Http\Request $request) {

        $this->requirePerm('can_admin');

        $id = \Gino\cleanVar($request->POST, 'id', 'int', '');
        
        if(!$id) {
            throw new \Gino\Exception\Exception404();
        }
        $css = \Gino\Loader::load('Css', array($id));

        return $css->actionDelCssLayout($request);
    }

    /**
     * @brief Processa il form di inserimento/modifica template
     * @see Gino.Template::formTemplate()
     * @param \Gino\Http\Request $request istanza di Gino.Http.Request
     * @return Gino.Http.Redirect
     */
    public function actionTemplate(\Gino\Http\Request $request) {

        $this->requirePerm('can_admin');

        $id = \Gino\cleanVar($request->POST, 'id', 'int', '');
        $free = \Gino\cleanVar($request->POST, 'free', 'int', '');

        $tpl = \Gino\Loader::load('Template', array($id));
        
        if($free) {
            return $tpl->actionFreeTemplate($request);
        }
        else {
            return $tpl->actionTemplate($request);
        }
    }

    /**
     * @brief Processa il form di eliminazione template
     * 
     * @see Gino.Template::formDelTemplate()
     * @see Gino.Template::actionDelTemplate()
     * @param \Gino\Http\Request $request istanza di Gino.Http.Request
     * @return Gino.Http.Redirect
     */
    public function actionDelTemplate(\Gino\Http\Request $request) {

        $this->requirePerm('can_admin');

        $id = \Gino\cleanVar($request->POST, 'id', 'int', '');
        
        if(!$id) {
            throw new \Gino\Exception\Exception404();
        }
        $tpl = \Gino\Loader::load('Template', array($id));

        return $tpl->actionDelTemplate($request);
    }
    
    /**
     * @brief Fornisce le informazioni per accedere a una pagina e per poterla implementare nel template
     * 
     * @param \Gino\App\Page\PageEntry object $page oggetto pagina
     * @return array array(url => (string), perm => (string), code => (string))
     */
    public static function getPageData($page) {
    	
    	$access_txt = '';
        if($page->private) {
        	$access_txt .= _("visualizzazione pagine private")."<br />";
        }
        if($page->users) {
        	$access_txt .= _("pagina limitata ad utenti selezionati");
        }
        if(!$page->private and !$page->users) {
        	$access_txt .= _('pagina pubblica');
        }

        $code_full = "{module pageid=".$page->id." func=full}";
        $url = $page->getUrl();
        
        return array(
        	'url' => $url,
        	'perm' => $access_txt,
        	'code' => $code_full
        );
    }
    
    /**
     * @brief Fornisce le informazioni per accedere a un metodo e per poterlo implementare nel template
     * @description I metodi sono quelli elencati nel metodo outputFunctions() del controller, ovvero i metodi pubblici che forniscono un output per il front-end. \n
     * 
     * @param string $method_name nome del metodo (chiave del metodo outputFunctions)
     * @param array $method_info informazioni sul metodo (valore del metodo outputFunctions)
     * @param object $module oggetto \Gino\App\Module\ModuleInstance o \Gino\App\SysClass\ModuleApp
     * @param boolean $sys_module modulo di sistema (default false); col valore @a true si indica un modulo non istanziabile
     * @return null or array array(info => (string), perm => (string), code => (string))
     */
    public static function getMethodData($method_name, $method_info, $module, $sys_module=false) {
    	
    	$class_name = $module->className();
    	
    	$method_check = parse_ini_file(APP_DIR.OS.$module->className().OS.$class_name.".ini", TRUE);
    	$public_method = @$method_check['PUBLIC_METHODS'][$method_name];
    	
    	if(!isset($public_method))
    	{
    		$permissions_code = $method_info['permissions'];
    		$permissions = array();
    		if($permissions_code and count($permissions_code)) {
    			foreach($permissions_code as $permission_code) {
    				
    				if(!preg_match('#\.#', $permission_code)) {
    					$permission_code = $class_name.'.'.$permission_code;
    				}
    				$p = Permission::getFromFullCode($permission_code);
    				$permissions[] = $p->label;
    			}
    		}
    		
    		$param = $sys_module ? 'sysclassid' : 'classid';
    		
    		$code = "{module $param=".$module->id." func=".$method_name."}";
    		
    		return array(
    			'info' => $method_info['label'],
    			'perm' => count($permissions) ? implode(', ', $permissions) : _('pubblico'),
    			'code' => $code
    		);
    	}
    	else {
    		return null;
    	}
    }
    
    /**
     * @brief Dati dei moduli per l'inserimento nelle voci di menu
     * 
     * @see Gino.App.Menu.menu::printItemsClass()
     * @see Gino.App.Auth.Permission::getFromFullCode()
     * @param object $module Gino.App.SysClass.ModuleApp or Gino.App.Module.ModuleInstance
     * @return NULL|array
     */
    public static function infoModule($module) {
        
        \Gino\Loader::import('auth', 'Permission');
        
        $class = $module->classNameNs();
        $class_name = $module->className();
        
        $data = [];
        
        if(is_a($module, 'ModuleApp')) {
            $instance_name = $class_name;
            $instance = 0;
        }
        else {
            $instance_name = $module->name;
            $instance = $module->id;
        }
        
        if(method_exists($class, 'outputFunctions')) {
            $list = call_user_func(array($class, 'outputFunctions'));
            
            //@todo aggiungere controllo che sia nell'ini
            foreach($list as $func => $desc) {
                $method_check = parse_ini_file(APP_DIR.OS.$class_name.OS.$class_name.".ini", TRUE);
                $public_method = @$method_check['PUBLIC_METHODS'][$func];
                if(isset($public_method)) {
                    
                    $permissions_code = $desc['permissions'];
                    $description = $desc['label'];
                    $permissions = array();
                    $perms_js = array();
                    if($permissions_code and count($permissions_code)) {
                        foreach($permissions_code as $permission_code) {
                            if(!preg_match('#\.#', $permission_code)) {
                                $permission_code = $class_name.'.'.$permission_code;
                            }
                            $p = \Gino\App\Auth\Permission::getFromFullCode($permission_code);
                            $permissions[] = $p->label;
                            $perms_js[] = $p->id;
                        }
                    }
                    
                    $registry = \Gino\Registry::instance();
                    
                    $url = $registry->router->link($instance_name, $func);
                    
                    $button = "<input data-perm=\"".implode(';', $perms_js)."\" type=\"button\" value=\""._("aggiungi dati")."\" onclick=\"
                                $('url').set('value', '".$url."');
                                $$('.form-multicheck input[type=checkbox][value]').removeProperty('checked');
                                perms = $(this).get('data-perm');
                                if(perms) {
                                    perms.split(';').each(function(p) {
                                        $$('input[value=' + p + ',".$instance."]').setProperty('checked', 'checked');
                                    })
                                }
                                location.hash = 'top';
                            \" />\n";
                    
                    $data[] = array(
                        \Gino\htmlChars($module->label),
                        $description,
                        $url,
                        implode(', ', $permissions),
                        $button
                    );
                }
            }
        }
        
        return $data;
    }
    
    /**
     * @brief Dati delle pagine per l'inserimento nelle voci di menu
     *
     * @see Gino.App.Menu.menu::printItemsPage()
     * @see Gino.App.Auth.Permission::getFromFullCode()
     * @param object $module Gino.App.Page.PageEntry
     * @return array
     */
    public static function infoPage($page) {
        
        \Gino\Loader::import('auth', 'Permission');
        
        $page_perm = '';
        if($page->private) {
            $page_perm .= _("pagina privata");
        }
        if($page->private && $page->users) {
            $page_perm .= " / ";
        }
        if($page->users) {
            $page_perm .= _("pagina limitata ad utenti selezionati");
        }
        
        $p = \Gino\App\Auth\Permission::getFromFullCode('page.can_view_private');
        
        $button = "<input data-private=\"".$page->private."\" type=\"button\" value=\""._("aggiungi dati")."\" onclick=\"
        $('url').set('value', '".$page->getUrl()."');
        $$('.form-multicheck input[type=checkbox][value]').removeProperty('checked');
        var private = $(this).get('data-private');
        if(private.toInt()) {
            $$('input[value=".$p->id.",0]').setProperty('checked', 'checked');
        }
        location.hash = 'top';
        \" />\n";
        
        return array(
            \Gino\htmlChars($page->title),
            $page->getUrl(),
            $page_perm,
            $button
        );
    }

    /**
     * @brief Elenco dei moduli di sistema, istanze di moduli di sistema e delle pagine disponibili come blocchi all'interno del template
     *
     * @description I metodi il cui output è disponibile all'inserimento nel template sono quelli definiti nel metodo outputFunctions della classe
     *              di tipo Gino.Controller e che non sono presenti nel file <nome_modulo>.ini, e quindi non richiamabili da url.
     *              Questo metodo è richiamato nell'interfaccia di gestione di template di tipo free
     * @return Gino.Http.Response
     */
    public function modulesCodeList() {

        $this->requirePerm('can_admin');

        \Gino\Loader::import('page', 'PageEntry');
        \Gino\Loader::import('auth', 'Permission');
        \Gino\Loader::import('module', 'ModuleInstance');
        \Gino\Loader::import('sysClass', 'ModuleApp');

        $view_table = new View();
        $view_table->setViewTpl('table');
        $view_table->assign('class', 'table table-striped table-hover table-bordered table-layout');
        $tbl_rows = array();

        /*
         * Pages
         */
        $tbl_rows[] = array(
            array('text' => _('Pagine'), 'colspan'=>4, 'class'=>'header', 'header'=>true)
        );
        $tbl_rows[] = array(
            array('text' =>_('titolo'), 'header' => true),
            array('text' =>_('vista'), 'header' => true),
            array('text' =>_('permessi'), 'header' => true),
            array('text' =>_('codice'), 'header' => true)
        );

        $pages = PageEntry::objects(null, array('where' => "published='1'", 'order' => 'title'));
        if(count($pages)) {
            foreach($pages as $page) {
                
            	$page_code = self::getPageData($page);
            	
                $tbl_rows[] = array(
                    \Gino\htmlChars($page->title),
                    _("Pagina completa"),
                    $page_code['perm'],
                    $page_code['code']
                );
            }
        }

        /*
         * Modules sys_module
         */
        $tbl_rows[] = array(
            array('text' => _('Istanze di moduli'), 'colspan'=>4, 'class'=>'header', 'header'=>TRUE)
        );
        $tbl_rows[] = array(
            array('text' =>_('nome'), 'header' => TRUE),
            array('text' =>_('vista'), 'header' => TRUE),
            array('text' =>_('permessi'), 'header' => TRUE),
            array('text' =>_('codice'), 'header' => TRUE)
        );

        $modules = ModuleInstance::objects(null, array('where' => "active='1'", 'order' => 'label'));
        if(count($modules)) {
            foreach($modules as $module) {
                
            	$class = $module->classNameNs();
                $output_functions = method_exists($class, 'outputFunctions') 
                    ? call_user_func(array($class, 'outputFunctions'))
                    : array();

                if(count($output_functions)) {
                    foreach($output_functions as $func=>$data) {
                        
                    	$method_code = self::getMethodData($func, $data, $module);
                    	if($method_code) {
                    		$tbl_rows[] = array_merge(array(array('text' => \Gino\htmlChars($module->label))), $method_code);
                    	}
                    }
                }
            }
        }

        /*
         * Modules sys_module_app
         */
        $tbl_rows[] = array(
          array('text' => _('Moduli di sistema'), 'colspan'=>4, 'class'=>'header', 'header'=>true)
        );
        $tbl_rows[] = array(
          array('text' =>_('nome'), 'header' => true),
          array('text' =>_('vista'), 'header' => true),
          array('text' =>_('permessi'), 'header' => true),
          array('text' =>_('codice'), 'header' => true)
        );

        $modules_app = ModuleApp::objects(null, array('where' => "instantiable='0' AND active='1'", 'order' => 'label'));
        if(count($modules_app)) {
            foreach($modules_app as $module_app) {
                $class = $module_app->classNameNs();
                $output_functions = method_exists($class, 'outputFunctions') 
                    ? call_user_func(array($class, 'outputFunctions'))
                    : array();

                if(count($output_functions)) {
                    foreach($output_functions as $func=>$data) {
                    	
                    	$method_code = self::getMethodData($func, $data, $module_app, true);
                    	if($method_code) {
                    		$tbl_rows[] = array_merge(array(array('text' => \Gino\htmlChars($module_app->label))), $method_code);
                    	}
                    }
                }
            }
        }

        /*
         * Url module
         */
        $tbl_rows[] = array(
            array('text' => _('Moduli segnaposto'), 'colspan'=>4, 'class'=>'header', 'header'=>true)
        );
        $tbl_rows[] = array(
            array('text' =>_('nome'), 'colspan'=>2, 'header' => true),
            array('text' =>_('permessi'), 'header' => true),
            array('text' =>_('codice'), 'header' => true)
        );
        $code = "{module id=0}";
        $tbl_rows[] = array(
            array(
                'text' => _("Modulo da url"),
                'colspan' => 2
            ),
            _("Prende i permessi del modulo chiamato"),
            $code
        );

        $buffer = "<div>";
        $view_table->assign('rows', $tbl_rows);
        $buffer .= $view_table->render();
        $buffer .= "</div>";

        return new Response($buffer);
    }

    /**
     * @brief Elenco dei moduli di sistema, istanze di moduli di sistema e delle pagine disponibili come blocchi all'interno del template
     *
     * @description I metodi il cui output è disponibile all'inserimento nel template sono quelli definiti nel metodo outputFunctions della classe
     *              di tipo Gino.Controller e che non sono presenti nel file <nome_modulo>.ini, e quindi non richiamabili da url.
     *              Questo metodo è richiamato nell'interfaccia di gestione di template di tipo a blocchi, e consente l'inserimento dell'output 
     *              all'interno della struttura con un click.
     * @return Gino.Http.Response
     */
    public function modulesList(\Gino\Http\Request $request) {

        $this->requirePerm('can_admin');

        \Gino\Loader::import('page', 'PageEntry');
        \Gino\Loader::import('auth', 'Permission');
        \Gino\Loader::import('module', 'ModuleInstance');
        \Gino\Loader::import('sysClass', 'ModuleApp');

        $nav_id = \Gino\cleanVar($request->GET, 'nav_id', 'string', '');
        $refillable_id = \Gino\cleanVar($request->GET, 'refillable_id', 'string', '');
        $fill_id = \Gino\cleanVar($request->GET, 'fill_id', 'string', '');

        $view_table = new View();
        $view_table->setViewTpl('table');
        $view_table->assign('class', 'table table-striped table-hover table-bordered table-layout');
        $tbl_rows = array();

        /*
         * Pages
         */
        $tbl_rows[] = array(
            array('text' => _('Pagine'), 'colspan'=>3, 'class'=>'header', 'header'=>true)
        );
        $tbl_rows[] = array(
            array('text' =>_('titolo'), 'header' => true),
            array('text' =>_('vista'), 'header' => true),
            array('text' =>_('permessi'), 'header' => true)
        );

        $pages = PageEntry::objects(null, array('where' => "published='1'", 'order' => 'title'));
        if(count($pages)) {
            foreach($pages as $page) {
                
            	$page_code = self::getPageData($page);
            	$url = $page_code['url'];
            	$code_full = $page_code['code'];
            	
            	$tbl_rows[] = array(
            		\Gino\htmlChars($page->ml('title')),
            		"<span class=\"link\" onclick=\"gino.ajaxRequest('post', '$url', '', '".$fill_id."', {'script':true});closeAll('$nav_id', '$refillable_id', '".\Gino\jsVar(\Gino\htmlChars($page->title))."', '$code_full')\";>"._("Pagina completa")."</span>",
            		$page_code['perm']
            	);
            }
        }

        /*
         * Modules sys_module
         */
        $tbl_rows[] = array(
            array('text' => _('Istanze di moduli'), 'colspan'=>3, 'class'=>'header', 'header'=>true)
        );
        $tbl_rows[] = array(
            array('text' =>_('nome'), 'header' => true),
            array('text' =>_('vista'), 'header' => true),
            array('text' =>_('permessi'), 'header' => true)
        );

        $modules = ModuleInstance::objects(null, array('where' => "active='1'", 'order' => 'label'));
        if(count($modules)) {
            foreach($modules as $module) {
                $class = $module->classNameNs();
                $output_functions = method_exists($class, 'outputFunctions') 
                    ? call_user_func(array($class, 'outputFunctions'))
                    : array();

                if(count($output_functions)) {
                	
                	$count = count($output_functions);
                	$methods = array();
                	
                    $first = true;
                    foreach($output_functions as $func=>$data)
                    {
                    	$method_check = parse_ini_file(APP_DIR.OS.$module->className().OS.$module->className().".ini", TRUE);
                    	$public_method = @$method_check['PUBLIC_METHODS'][$func];
                    	
                    	if(isset($public_method)) {
                    		$count--;
                    	}
                    	else {
                    		$methods[$func] = $data;
                    	}
                    }
                    
                    if(count($methods))
                    {
                    	foreach ($methods AS $func=>$data)
                    	{
                    		$method_code = self::getMethodData($func, $data, $module);
                    		if($method_code) {
                    		
                    			$code = $method_code['code'];
                    			
                    			$row = array(
                    				"<span class=\"link\" onclick=\"gino.ajaxRequest('post', '".$this->link($module->name, $func)."', '', '".$fill_id."', {'script':true});closeAll('$nav_id', '$refillable_id', '".\Gino\htmlChars($module->label)." - ".\Gino\jsVar($data['label'])."', '$code')\";>{$data['label']}</span>",
                    				$method_code['perm']
                    			);
                    			
                    			if($first) {
                    				$tbl_rows[] = array_merge(array(array('text' => \Gino\htmlChars($module->label), 'rowspan' => $count)), $row);
                    				$first = false;
                    			}
                    			else {
                    				$tbl_rows[] = $row;
                    			}
                    		}
                        }
                    }
                }
            }
        }

        /*
         * Modules sys_module_app
         */
        $tbl_rows[] = array(
            array('text' => _('Moduli di sistema'), 'colspan'=>3, 'class'=>'header', 'header'=>true)
        );
        $tbl_rows[] = array(
            array('text' =>_('nome'), 'header' => true),
            array('text' =>_('vista'), 'header' => true),
            array('text' =>_('permessi'), 'header' => true)
        );

        $modules_app = ModuleApp::objects(null, array('where' => "instantiable='0' AND active='1'", 'order' => 'label'));
        if(count($modules_app)) {
            foreach($modules_app as $module_app) {
                $class = $module_app->classNameNs();
                $output_functions = method_exists($class, 'outputFunctions') 
                    ? call_user_func(array($class, 'outputFunctions'))
                    : array();

                if(count($output_functions)) {
                	
                	$count = count($output_functions);
                	$methods = array();
                	
                	$first = true;
                    foreach($output_functions as $func=>$data)
                    {
                    	$method_check = parse_ini_file(APP_DIR.OS.$module_app->className().OS.$module_app->className().".ini", TRUE);
                    	$public_method = @$method_check['PUBLIC_METHODS'][$func];
                    	
                    	if(isset($public_method)) {
                    		$count--;
                    	}
                    	else {
                    		$methods[$func] = $data;
                    	}
                    }
                    
                    if(count($methods))
                    {
                    	foreach ($methods AS $func=>$data)
                    	{
                    		$method_code = self::getMethodData($func, $data, $module_app, true);
                    		if($method_code) {
                    		
                    			$code = $method_code['code'];
                    			
                    			$row = array(
                    				"<span class=\"link\" onclick=\"gino.ajaxRequest('post', '".$this->link($module_app->name, $func)."', '', '".$fill_id."', {'script':true});closeAll('$nav_id', '$refillable_id', '".\Gino\htmlChars($module_app->label)." - ".\Gino\jsVar($data['label'])."', '$code')\";>{$data['label']}</span>",
                    				$method_code['perm']
                    			);
                    			
                    			if($first) {
                    				$tbl_rows[] = array_merge(array(array('text' => \Gino\htmlChars($module_app->label), 'rowspan' => $count)), $row);
                    				$first = false;
                    			}
                    			else {
                    				$tbl_rows[] = $row;
                    			}
                    		}
                        }
                    }
                }
            }
        }

        /*
         * Url module
         */
        $tbl_rows[] = array(
            array('text' => _('Moduli segnaposto'), 'colspan'=>3, 'class'=>'header', 'header'=>true)
        );
        $tbl_rows[] = array(
            array('text' =>_('nome'), 'colspan'=>2, 'header' => true),
            array('text' =>_('permessi'), 'header' => true)
        );
        $code = "{module id=0}";
        $tbl_rows[] = array(
            array(
                'text' => "<span class=\"link mdlTitle\" onclick=\"closeAll('$nav_id', '$refillable_id', '"._("Modulo da url")."', '$code')\";>"._("Modulo da url")."</span>",
                'colspan' => 2
            ),
            _("Prende i permessi del modulo chiamato")
        );

        $buffer = "<div>";
        $view_table->assign('rows', $tbl_rows);
        $buffer .= $view_table->render();
        $buffer .= "</div>";

        return new Response($buffer);
    }

    /**
     * @brief Form di modifica files (css, viste)
     * @param string $filename
     * @param string $code css|view
     */
    private function formFiles($filename, $code) {

        $this->_registry->addJs(SITE_JS."/CodeMirror/codemirror.js");
        $this->_registry->addCss(CSS_WWW."/codemirror.css");

        if($code == 'css')
        {
            $this->_registry->addJs(SITE_JS."/CodeMirror/css.js");
            $title = sprintf(_("Modifica il foglio di stile \"%s\""), $filename);
            $dir = CSS_DIR;
            $block = "css";
            $options = "{
                lineNumbers: true,
                matchBrackets: true,
                indentUnit: 4,
                indentWithTabs: true,
                enterMode: \"keep\",
                tabMode: \"shift\"
            }";
        }
        elseif($code == 'view')
        {
            $this->_registry->addJs(SITE_JS."/CodeMirror/htmlmixed.js");
            $this->_registry->addJs(SITE_JS."/CodeMirror/matchbrackets.js");
            $this->_registry->addJs(SITE_JS."/CodeMirror/css.js");
            $this->_registry->addJs(SITE_JS."/CodeMirror/xml.js");
            $this->_registry->addJs(SITE_JS."/CodeMirror/clike.js");
            $this->_registry->addJs(SITE_JS."/CodeMirror/php.js");
            $title = sprintf(_("Modifica la vista \"%s\""), $filename);
            $dir = VIEWS_DIR;
            $block = "view";
            $options = "{
                lineNumbers: true,
                matchBrackets: true,
                mode: \"application/x-httpd-php\",
                indentUnit: 4,
                indentWithTabs: true,
                enterMode: \"keep\",
                tabMode: \"shift\"
            }";
        }

        $buffer = '';
        $pathToFile = $dir.OS.$filename;
        $action = 'modify';
        $link_return = $this->linkAdmin(array(), 'block='.$block);

        if(is_file($pathToFile))
        {
            $gform = \Gino\Loader::load('Form', array());
            $gform->load('dataform');
            $buffer = $gform->open($this->link($this->_class_name, 'actionFiles'), '', '', array('form_id'=>'gform'));
            $buffer .= \Gino\Input::hidden('fname', $filename);
            $buffer .= \Gino\Input::hidden('code', $code);
            $buffer .= \Gino\Input::hidden('action', $action);

            $contents = file_get_contents($pathToFile);
            $buffer .= "<div class=\"form-group\">";
            $buffer .= "<textarea id=\"codemirror\" class=\"form-no-check\" name=\"file_content\" style=\"width:98%; padding-top: 10px; padding-left: 10px; height:580px;overflow:auto;\">".$contents."</textarea>\n";
            $buffer .= "</div>";

            $buffer .= "<div class=\"form-group\">";
            $buffer .= \Gino\Input::submit('submit_action', _("salva"));
            $buffer .= \Gino\Input::submit('submit_action', _("annulla"), ['onclick' => "location.href='$link_return'\" class=\"generic\""]);
            $buffer .= "</div>";

            $buffer .= "<script>var myCodeMirror = CodeMirror.fromTextArea(document.getElementById('codemirror'), $options);</script>";

            $buffer .= $gform->close();
        }

        $view = new View();
        $view->setViewTpl('section');
        $dict = array(
            'title' => $title,
            'class' => null,
            'content' => $buffer
        );

        return $view->render($dict);
    }

    /**
     * @brief Processa il form di modifica dei file
     * @see self::formFiles()
     * @param \Gino\Http\Request $request istanza di Gino.Http.Request
     * @return Gino.Http.Redirect
     */
    public function actionFiles(\Gino\Http\Request $request) {

        $this->requirePerm('can_admin');

        $action = \Gino\cleanVar($request->POST, 'action', 'string');
        $filename = \Gino\cleanVar($request->POST, 'fname', 'string');
        $code = \Gino\cleanVar($request->POST, 'code', 'string');

        if($code == 'css')
        {
            $dir = CSS_DIR;
            $block = "css";
        }
        elseif($code == 'view')
        {
            $dir = VIEWS_DIR;
            $block = "view";
        }

        if(is_file($dir.OS.$filename))
        {
            $file_content = $_POST['file_content'];
            if($fo = fopen($dir.OS.$filename, 'wb'))
            {
                fwrite($fo, $file_content);
                fclose($fo);
            }
        }

        return new Redirect($this->linkAdmin(array(), 'block='.$block));
    }
}
