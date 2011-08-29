<?php
/**
 * --------------------------------------------------------------
 * Classe Multifile
 * --------------------------------------------------------------
 * Classe che permette di gestire l'upload multiplo di file.
 * 
 * --------------------------------------------------------------
 * 1. Tabella
 * --------------------------------------------------------------
 * 
 * Per poter utilizzare questa classe è necessario creare una tabella così strutturata:
 * 
CREATE TABLE `news_file` (
`id` int(11) NOT NULL auto_increment,
`reference` int(11) NOT NULL,
`filename` varchar(100) NOT NULL,
`description` varchar(200) NOT NULL,
PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=1 ;
 *
 * Per consuetudine si chiede di utilizzare come nome '[riferimento_tbl_classe]_file'
 * 
 * --------------------------------------------------------------
 * 2. Visualizzare i file
 * --------------------------------------------------------------
$mfile = new mFile('', '', '', '');
$options = array('params'=>"opt=3&amp;key=2", 'ajax'=>true);
$buffer .= $mfile->mfileList($id, $table_file, $this->_className, 'mdownloader', $options);
 *
 * --------------------------------------------------------------
 * 3. Form
 * --------------------------------------------------------------
Esempi:
(1)
$mfile = new mFile('fileUpload', 'gform', '', array('prefix_id'=>'m_', 'text'=>true));
$buffer .= $mfile->jsUploadLib();
$options = array();
$options1 = array('size'=>20, 'maxlength'=>100, 'extensions'=>$this->_valid_image);
$options2 = array('size'=>20, 'maxlength'=>200);
$buffer .= $gform->cell($mfile->mForm('mfile', $id, $options, $options1, $options2));
(2)
$mfile = new mFile('fileUpload', 'gform', $obj->getDataDir().OS.$this->id, array('prefix_id'=>'m_'));
$buffer .= $mfile->jsUploadLib();
$options = array();
$options1 = array('size'=>20, 'maxlength'=>100, 'extensions'=>$this->_valid_image);
$buffer .= $gform->cell($mfile->mForm('mfile', $this->id ? $this->id : 0, $options, $options1));
$buffer .= $gform->cell($mfile->mfileDelList($this->id, $this->_tbl_data."_file", $obj->getInstanceName(), '', '', true));

dove:
$options1 -> opzioni di cfile()
$options2 -> opzioni di cinput()

ALTERNATIVE
--------------------
-> al posto di:
$buffer .= $mfile->jsUploadLib();

-> si può cercare di utilizzare (input change):
$result_div = 'm_file'.$id;
$buffer .= $mfile->jsInputLib('attached', 'id', '', '', $result_div, $this->_tbl_base_file);
// in questo ultimo caso dovrò richiamare
$buffer .= "<div id=\"$result_div\">".$mfile->mFormFromInput($refid, $action, $this->_tbl_base_file, $options, $options1, $options2)."</div>\n";

PER VISUALIZZARE L'ELENCO DEI FILE PRESENTI CON LA POSSIBILITA' DI ELIMINARLI
--------------------
$buffer .= $gform->cell($mfile->mfileDelList($this->id, $this->_tbl_data."_file", $obj->getInstanceName(), '', '', true));

mfileDelList() richiama l'istanza $obj->getInstanceName() e, non avendo indicato un metodo specifico,
il metodo di default mfileDeleteAttached() che occorre inserire nella classe dell'istanza.

	public function mfileDeleteAttached() {
	
		$this->accessGroup('ALL');

		$mfile = new mFile('', '', $this->_data_dir.$this->_os.$c->id, array('text'=>true));
		$buffer = $mfile->mfileDelAction();
		return $buffer;
	}
	
mfileDelAction() rimanda a 'mfileDeleteAttached'

 * --------------------------------------------------------------
 * 4. Action
 * -------------------------------------------------------------- 
 * 
$mfile = new mFile('', '', $this->_data_dir.$this->_os.$c->id, array('text'=>true));
// Upload Multifile
$options = array('chmod_dir'=>0755, 'overwrite'=>1);
$upload = $mfile->mAction('mfile', $this->_data_dir.$this->_os.$c->id, $link_error, $options);
$mfile->dbUploadAction($upload, $c->id, $this->_tbl_base_file);

ALTERNATIVA
--------------------
$mfile = new mFile();
$upload = $mfile->mAction('mfile', $directory, $link_error, array('chmod_dir'=>0755, 'overwrite'=>0, 'valid_ext'=>$this->_valid_image));

if(sizeof($upload) > 0)
{
	foreach($upload AS $key=>$value)
	{
		$item = new multimediaItem($id);
		$item->instance = $this->_instance;
		$item->image = $key;
		...
		if($item->updateDbData())
		{
			$form = new Form('', '', '');
			$resize = $form->createImage($item->image, $directory, array('prefix_file'=>$this->_prefix_img, 'prefix_thumb'=>$this->_prefix_thumb, 'file_dim'=>$this->_img_width, 'thumb_dim'=>$this->_thumb_width));
		}
		else unlink($directory.$item->image);

 * 
 * -------------------------------
 * SCHEMA METODI
 * -------------------------------
 * 
 * jsInputLib()		-> [default] mFormFromInput()
 * mFormFromInput()	-> mForm()
 * mfileDelList()	-> [default] mfileDelAction()
 * 					-> jsDeleteLib() // comprende il js deleteFile($id) che attiva mfileDelAction()
 * 
 * Per modificare il metodo di default indicare nel metodo che lo richiama il nome del metodo ad hoc e della classe relativa
 * 
 * -------------------------------
 * ESEMPIO AJAX per richiamare il form
 * -------------------------------
 * 
 * $GINO .= $this->jsLib();
 * $GINO .= "<div id=\"f_attach\"></div>\n";	// style=\"display:none;\" (utilizzando nel js showHide($(result)))
 * 
 * $GINO .= "<span onclick=\"fAttach($id, 0, 'f_attach', '".$this->_act_insert."', '', '');\" title=\""._("form allegati")."\">".$this->icon('attach', _("gestione allegati"))."</span>";
 * 
 * -> in jsLib():
 * 
		$GINO .= "<script type=\"text/javascript\">\n";
		
		$GINO .= "function fAttach(ref, id, result, action, method, params) {
			
			var output = $(result);
			var call = '';
			//showHide(output);
			
			if(action == '".$this->_act_delete."') call = 'deleteAttach';
			else call = 'formAttach';
			
			var url = '".$this->_home."?pt[".$this->_className."-'+call+']';
			var data = 'ref='+ref+'&id='+id+'&action='+action+'&div='+result+'&m='+method+'&p='+params;
			sendPost(url, data, result);
		};\n";
		
		$GINO .= "</script>\n";
 *
 * --------------------------------------------------------------
 * Esempi
 * --------------------------------------------------------------
 * ago-energia (class_project.php, class_supplier.php)
 * circolodeilettori (class_multimedia.php)
 * cgems (class_book.php)
 */

class mFile {
	
	private $_home, $_db, $_className;
	private $_form_name, $_dir, $_description;
	private $_js_function_name;
	private $_id_prefix, $_id_name, $_id_hidden_block, $_id_main_block, $_id_print_block;
	private $_current_upload;
	
	private $_base_method, $_base_result;
	private $_options, $_form_label_width, $_form_field_width;
	private $_max_file_size, $_extension_denied;
	private $_act_delete;
	
	/**
	 * Costruttore
	 *
	 * @param string $func_name		nome della funzione javascript
	 * @param string $form_name		nome del form
	 * @param string $dir			percorso assoluto dei file		
	 * @param array $options
	 * 
	 * Options:
	 * @param boolean text			descrizione del file
	 * @param string prefix_id		prefisso per i nomi dei div
	 */
	function __construct($func_name='', $form_name='', $dir='', $options=null){
		
		$this->_db = new Db();
		$this->_className = get_class($this);
		$this->_form_name = $form_name;
		$this->_dir = $dir;
		
		!empty($func_name) ? $this->_js_function_name = $func_name : $this->_js_function_name = 'addFileUpload';
		
		$this->setDimensions($options);
		$this->_description = isset($options['text']) ? $options['text'] : false;
		$this->_id_prefix = isset($options['prefix_id']) ? $options['prefix_id'] : '';
		
		$this->_id_name = $this->_id_prefix.'addupload';
		$this->_id_hidden_block = $this->_id_prefix.'attachment';
		$this->_id_main_block = $this->_id_prefix.'attachments';
		$this->_id_print_block = $this->_id_prefix.'attachmentmarker';
		$this->_current_upload = $this->_id_prefix.'currentUploads';
		
		$this->_base_method = 'mfileDeleteAttached';	// metodo eliminazione file
		$this->_base_result = 'file_list';	// div elenco file
		
		$this->_max_file_size = MAX_FILE_SIZE;
		$this->_extension_denied = array(
		'php', 'phps', 'js', 'py', 'asp', 'rb', 'cgi', 'cmd', 'sh', 'exe', 'bin'
		);

		$this->_act_delete = 'delete';
	}
	
	private function setOptions($options) {
		$this->_options = $options;
	}

	private function option($opt) {

		return isset($this->_options[$opt]) ? $this->_options[$opt]:null;
	}
	
	private function setDimensions($options) {
		
		$this->_form_label_width = isset($options['form_label_width'])? $options['form_label_width']:FORM_LABEL_WIDTH;
		$this->_form_field_width = isset($options['form_field_width'])? $options['form_field_width']:FORM_FIELD_WIDTH;
	}
	
	// Directory finale: $this->_dir.$this->_os.$id.$this->_os
	private function dirUpload($directory){
		
		$directory = (substr($directory, -1) != '/' && $directory != '') ? $directory.'/' : $directory;
		return $directory;
	}
	
	private function pathDirectory($id){
		
		$dir = $this->_dir.$this->_os.$id.$this->_os;
		return $dir;
	}
	
	private function checkFilename($filename, $prefix='') {
	
		$filename = preg_replace("#[^a-zA-Z0-9_\.-]#", "_", $filename);
		return $prefix.$filename;
	}
	
	/**
	 * Visualizzazione file con possibilità di collegamento
	 *
	 * @param integer $id
	 * @param string $table
	 * @param string $class_name
	 * @param string $func_name
	 * @return string
	 * 
	 * Options:
	 * @param string name_id			nome dell'identificatore
	 * @param string params				parametri aggiuntivi (es: opt=3&amp;key=2)
	 * @param boolean view_text			visualizzare la descrizione (default: false)
	 * @param boolean ajax				chiamata 'pt' vs 'evt' (default: false)
	 * @param boolean download			permettere il download dei file (default: true)
	 * @param integer max_char			numero max di caratteri del nome del file (viene mostrata l'estensione)
	 */
	public function mfileList($id, $table, $class_name, $func_name, $options=null){
		
		$this->setOptions($options);
		
		$o_name_id = $this->option('name_id') ? $this->option('name_id') : 'id';
		$o_params = $this->option('params') ? $this->option('params') : '';
		$o_view_text = $this->option('view_text') ? $this->option('view_text') : false;
		$o_ajax = $this->option('ajax') ? $this->option('ajax') : false;
		$o_download = $this->option('download') ? $this->option('download') : true;
		$o_max_char = $this->option('max_char') ? $this->option('max_char') : 0;
		
		$GINO = '';
		
		if(!empty($id))
		{
			$query = "SELECT * FROM $table WHERE reference='$id'";
			$a = $this->_db->selectquery($query);
			if(sizeof($a) > 0)
			{
				$GINO .= "<table class=\"listfile\">";
				
				foreach($a AS $b)
				{
					$fid = $b['id'];
					$filename = htmlChars($b['filename']);
					$description = htmlChars($b['description']);
					
					if($o_max_char > 0) $filename_mod = cutString($filename, $o_max_char, false, true);
					else $filename_mod = $filename;
					
					if(!empty($o_params)) $o_params = "&amp;$o_params";
					$pointer = $o_ajax ? 'pt' : 'evt'; 
					
					if($o_download)
					$link = "<a href=\"".$this->_home."?".$pointer."[$class_name-$func_name]&amp;$o_name_id=$fid".$o_params."\" title=\"$filename\">$filename_mod</a>";
					else $link = $filename;
					
					$GINO .= "<tr>";
					$GINO .= "<td style=\"border-width:0px;\">$link</td>";
					if($o_view_text) $GINO .= "<td style=\"border-width:0px;\">$description</td>";
					$GINO .= "</tr>";
				}
				$GINO .= "</table>";
			}
		}
		
		return $GINO;
	}
	
	// Form con passaggio dal metodo jsInputLib()
	public function mFormFromInput($refid=0, $action='', $table='', $options=null, $options_file=null, $options_text=null){
		
		$GINO = '';
		
		if(!empty($refid) AND !empty($action) AND !empty($table))
		{
			$id = $refid;
		}
		else	// from jsInputLib()
		{
			$id = cleanVar($_POST, 'rid', 'int', '');
			$action = cleanVar($_POST, 'action', 'string', '');
			$table = cleanVar($_POST, 'tbl', 'string', '');
		}
		
		$GINO .= $this->mForm('mfile', $id, $options, $options_file, $options_text);
		
		return $GINO;
	}
	
	/**
	 * Form
	 *
	 * @param string $name_file			input name
	 * @param integer $id				id di riferimento
	 * @param array $options
	 * @param array $options_file
	 * @param array $options_text
	 * @return string
	 * 
	 * Options:
	 * 
	 * 1. $options
	 * @param string label_file		label del blocco principale
	 * @param string label_text		label del blocco principale
	 * 
	 * 2. $options_file -> opzioni di cfile()
	 * 3. $options_text -> opzioni di cinput()
	 */
	public function mForm($name_file, $id=0, $options=null, $options_file=null, $options_text=null){
		
		$this->setOptions($options);
		$label_file = $this->option('label_file') ? $this->option('label_file') : _("Aggiungi file");
		$label_text = $this->option('label_text') ? $this->option('label_text') : _("Descrizione");
		
		$name_text = $name_file.'_txt';
		$name_file_array = $name_file.'[]';
		$name_text_array = $name_text.'[]';
		
		$GINO = '';
		
		$gform = new Form($this->_form_name, '', false);
		
		// Blocco nascosto (codice di aggiunta file)
		$file_add = $gform->input($name_file_array, 'file', '', $options_file);
		if($this->_description)
			$file_add .= "<br />".$gform->input($name_text_array, 'text', '', $options_text);
		$file_add .= "&nbsp;<span class=\"link\" onclick=\"javascript:$(this).getParent('div').dispose();\">".pub::icon('delete')."</span>";
		
		$GINO .= "<div id=\"".$this->_id_hidden_block."\" style=\"display:none\">";
		$GINO .= $gform->startTable();
		$GINO .= $gform->noinput('', $file_add);
		$GINO .= $gform->endTable();
		$GINO .= "</div>";
		// End
		
		// Blocco principale
		$GINO .= "<div id=\"".$this->_id_main_block."\">";
		
		$link_action = "&nbsp;<a id=\"".$this->_id_name."\" href=\"javascript:{$this->_js_function_name}('".$name_file."','".$name_text."','$id')\">".pub::icon('insert')."</a>";
		// window.{$this->_js_function_name}
		
		$GINO .= $gform->startTable();
		$options_file['text_add'] = $link_action;
		$GINO .= $gform->cfile($name_file_array, '', $label_file, $options_file);
		
		if($this->_description)
			$GINO .= $gform->cinput($name_text_array, 'text', '', $label_text, $options_text);
		
		$GINO .= $gform->endTable();
		$GINO .= "<div id=\"".$this->_id_print_block."$id\"></div>";	// stampa del blocco nascosto
		$GINO .= "</div>";
		// End
		
		return $GINO;
	}
	
	/*
	 * Elenco file associati a un record con link di eliminazione
	 * 
	 * instance:	nome dell'istanza
	 */
	public function mfileDelList($id, $table, $instance, $method='', $result='', $start=false){
		
		if($start AND empty($instance)) return '';
		if(empty($method)) $method = $this->_base_method;
		if(empty($result)) $result = $this->_base_result;
		
		$result = $result.$id;
		
		$GINO = '';
		
		if($start)
			$GINO .= $this->jsDeleteLib($instance, $method, $result, $table, $this->_dir);
		
		$GINO .= $this->fileDelList($id, $table, $result);
		
		return $GINO;
	}
	
	private function fileDelList($id, $table, $result){
		
		$gform = new Form($this->_form_name, '', false);
		
		$GINO = "<div id=\"$result\">";
		$GINO .= $gform->startTable();
		
		if(!empty($id))
		{
			$query = "SELECT * FROM $table WHERE reference='$id'";
			$a = $this->_db->selectquery($query);
			if(sizeof($a) > 0)
			{
				$text1 = _("File associati");
				$text2 = "<table>";
				
				foreach($a AS $b)
				{
					$fid = $b['id'];
					$filename = htmlChars($b['filename']);
					$description = htmlChars($b['description']);
					
					$link_delete = "<span onclick=\"deleteFile($fid)\" style=\"cursor:pointer; text-decoration:underline; text-align:right\">".pub::icon('delete')."</span>";
					
					$text2 .= "<tr>";
					$text2 .= "<td style=\"border-width:0px;\">$filename</td>";
					$text2 .= "<td style=\"border-width:0px;\">$link_delete</td>";
					$text2 .= "</tr>";
				}
				$text2 .= "</table>";
				
				$GINO .= $gform->noinput($text1, $text2);
			}
		}
		$GINO .= $gform->endTable();
		$GINO .= "</div>";
		
		return $GINO;
	}
	
	// Metodo default di eliminazione di un file
	public function mfileDelAction($method=''){
		
		$fid = cleanVar($_POST, 'fid', 'int', '');
		$action = cleanVar($_POST, 'action', 'string', '');
		$table = cleanVar($_POST, 'tbl', 'string', '');
		$dir = cleanVar($_POST, 'dir', 'string', '');
		
		$instance = '';	// il nome dell'istanza è già presente nel JavaScript caricato durante la prima visualizzazione
		if($method == '')
			$method = $this->_base_method;
		
		$GINO = '';
		
		if(!empty($fid) AND !empty($table) AND $action == $this->_act_delete)
		{
			if(!is_dir($dir)) return '';
			
			$directory = $this->dirUpload($dir);
			
			$query = "SELECT reference, filename FROM $table WHERE id='$fid'";
			$a = $this->_db->selectquery($query);
			if(sizeof($a) > 0)
			{
				foreach($a AS $b)
				{
					$refid = $b['reference'];
					$filename = $b['filename'];
					
					$pub = new pub();
					$result = $pub->deleteFile($directory.$filename, $this->_home, '', '');
					if($result)
					{
						$query_delete = "DELETE FROM $table WHERE id='$fid'";
						$result_query = $this->_db->actionquery($query_delete);
						
						if(!$result_query)
						$GINO .= "<p>"._("errore nella query di eliminazione del file")." '$filename'</p>";
					}
					else
					{
						$GINO .= "<p>"._("non è stato possibile eliminare il file")." '$filename'</p>";
					}
				}
				
				$result_div = $this->_base_result.$refid;
				
				$GINO .= $this->mfileDelList($refid, $table, $instance, $method, $result_div);
			}
		}
		
		return $GINO;
	}
	
	// Invia al metodo '$method' le variabili: fid, action, tbl, dir
	private function jsDeleteLib($instance, $method, $result, $table, $directory) {
	
		$GINO = '';
		$GINO .= "<script type=\"text/javascript\">\n";
		$GINO .= "window.deleteFile=function(id) {
					if(id != null)
					{
						var check=show_alert();
						
						if(check)
						{
							var url = '".$this->_home."?pt[".$instance."-".$method."]';
							var data = 'fid='+id+'&action=".$this->_act_delete."&tbl=$table&dir=$directory';
							ajaxRequest('post', url, data, '".$result."');
						}
					}
				};
				
				function show_alert(){
				
					var r=confirm(\"sei sicuro?\");
					if (r==true)
  					{
  						return true;
  					}
  					else
  					{
  						return false;
  					}
				};
				
				function show_prompt(){
				
					var nome=prompt(\"Per favore inserisci il tuo nome\",\"Colonnello Kurtz\");
					if (nome!=null && nome!=\"\")
  					{
  						document.write(\"Ciao \" + nome + \"! Bentornato.\");
  					}
				};
				";
		
		$GINO .= "</script>\n";
		
		return $GINO;
	}
	
	/**
	 * Action Upload Multiplo
	 *
	 * @param string $name			input file name
	 * @param string $dir_upload	directory di upload (/path/to/directory/)
	 * @param string $link_error	reindirizzamento causa errore
	 * 
	 * @return array ('filename'=>'description')
	 * 
	 * Options: 
	 * 
	 * @param integer overwrite		1=sovrascrivi i files, 0=non sovrascrivere
	 * @param integer max_size		dimensione massima upload in KB
	 * @param integer check_type	1=controlla il tipo di file, 0=non controllare			-> attiva 'types_allowed'
	 * @param array types_allowed	array per alcuni tipi di file (mime types)
	 * @param integer check_denied	1=controlla l'estensione del file, 0=non controllare	-> verifica '$this->_extension_denied'
	 * @param integer debug			1=stampa informazioni di debug, 0=non stampare
	 * @param integer chmod_dir		permessi della directory (0700)
	 * @param array valid_ext		estensioni valide
	 * @param boolean check_name	verifica i caratteri del nome del file
	 */
	public function mAction($name, $dir_upload, $link_error, $options=null){
		
		$this->setOptions($options);
		
		$text = $this->_description;
		
		$overwrite = !is_null($this->option('overwrite')) ? $this->option('overwrite') : 1;
		$max_size = $this->option('max_size') ? $this->option('max_size') : $this->_max_file_size;
		$check_type = !is_null($this->option('check_type')) ? $this->option('check_type') : 1;
		$types_allowed = $this->option('types_allowed') ? $this->option('types_allowed') : 
		array(
			"text/plain",
			"text/html",
			"text/xml",
			"image/jpeg",
			"image/gif",
			"image/png",
			"video/mpeg",
			"audio/midi",
			"application/x-zip-compressed",
			"application/vnd.ms-excel",
			"application/x-msdos-program",
			"application/octet-stream"
		);
		$check_denied = !is_null($this->option('check_denied')) ? $this->option('check_denied') : 1;
		$debug = !is_null($this->option('debug')) ? $this->option('debug') : 0;
		$chmod_dir = $this->option('chmod_dir') ? $this->option('chmod_dir') : 0700;
		$valid_ext = $this->option('valid_ext') ? $this->option('valid_ext') : array();
		$check_name = !is_null($this->option('check_name')) ? $this->option('check_name') : true;
		
		$log = '';
		
		if($text)
		{
			$name_desc = $name.'_txt';
			$description = cleanVar($_POST, $name_desc, 'array', '');
		}
		$dir_upload = $this->dirUpload($dir_upload);
		
		$data = array();
		$data_txt = array();
		
		// Verifica presenza file
		$number_file = count($_FILES[$name]['tmp_name']);
		
		for($i=0; $i<$number_file; $i++)
		{
			if($_FILES[$name]['size'][$i] == 0)
			{
				$log .= "L'upload del file <strong>{$_FILES[$name]['name'][$i]}</strong> non è andato a buon fine!<br />\n";
				
				unset($_FILES[$name]['name'][$i]);
				unset($_FILES[$name]['type'][$i]);
				unset($_FILES[$name]['size'][$i]);
				unset($_FILES[$name]['error'][$i]);
				unset($_FILES[$name]['tmp_name'][$i]);
			}
		}
		$number_file = count($_FILES[$name]['tmp_name']);
		// End
		
		if(count($_FILES[$name]['name']) > 0)
		{
			$log .= "Hai caricato $number_file file(s)";
			$log .= "<br /><br />\n";
			
			foreach($_FILES[$name]['name'] as $key=>$value)
			{
				if ($debug == 1)
				{
					$log .= "Nome file: <strong>".$_FILES[$name]['name'][$key]."</strong><br />\n";
					$log .= "Tipo file: <strong>".$_FILES[$name]['type'][$key]."</strong><br />\n";
					$log .= "Dimensione: <strong>".$_FILES[$name]['size'][$key]." byte</strong><br />\n";
					$log .= "Nome temporaneo: <strong>".$_FILES[$name]['tmp_name'][$key]."</strong><br />\n";
					if($text) $log .= "Descrizione: <strong>".$description[$key]."</strong><br />\n";
				}
				
				$filename = $check_name ? $this->checkFilename($_FILES[$name]['name'][$key]) : $_FILES[$name]['name'][$key];
				
				if(is_uploaded_file($_FILES[$name]['tmp_name'][$key]))
				{
					if($_FILES[$name]['size'][$key] <= $max_size)
					{
						if(
							($check_type == 0 || 
							($check_type == 1 && in_array( $_FILES[$name]['type'][$key], $types_allowed)))
							&&
							($check_denied == 0 || 
							($check_denied == 1 && !extension($filename, $this->_extension_denied)))
							&&
							(sizeof($valid_ext) == 0 ||
							(sizeof($valid_ext) > 0 && extension($filename, $valid_ext)))
							&&
							!preg_match('#%00#', $filename)
						)
						{
							if(!is_dir($dir_upload) && $dir_upload != '')
							{
								if(!@mkdir($dir_upload, $chmod_dir))
								{
									$log .= "Errore nella creazione della directory <strong>$dir_upload</strong>";
									if($debug == 0)
										exit(error::errorMessage(array('error'=>27), $link_error));
								}
							}
							if(!file_exists($dir_upload.$filename) || $overwrite == 1)
							{
								if(@move_uploaded_file($_FILES[$name]['tmp_name'][$key], $dir_upload.$filename))
								{
									$log .= "File <strong>{$filename}</strong> trasferito!";
									$data[] = $filename;
									if($text) $data_txt[] = $description[$key]; else $data_txt[] = $filename;
								}
								else
								{
									$log .= "Errore nel trasferimento del file <strong>$filename</strong>";
									if($debug == 0)
										exit(error::errorMessage(array('error'=>28), $link_error));
								}
									
							} else
								$log .= "Il file <strong>$filename</strong> è esistente!";
						} else 
							$log .= "Il tipo di file <strong>".$_FILES[$name]['type'][$key]."</strong> non è consentito!";
					} else
						$log .= "La dimensione del file <strong>".$_FILES[$name]['type'][$key]."</strong> non è consentita!";
				}
				else
				{
					$log .= "Errore nel trasferimento del file <strong>$filename</strong>";
					if($debug == 0)
						exit(error::errorMessage(array('error'=>28), $link_error));
				}
				
				$log .= "<hr />\n";
			}
			
			if ($debug == 1) { echo $log; exit(); }
			
			if(sizeof($data) > 0 AND sizeof($data_txt) > 0) $array = array_combine($data, $data_txt);
			else $array = array();
		}
		else $array = array();
		
		return $array;
	}
	
	/**
	 * Inserimento su DB dei risultati dell'upload
	 *
	 * @param array $upload			result of mAction()
	 * @param integer $reference
	 * @param string $table
	 */
	public function dbUploadAction($upload, $reference, $table){
		
		if(sizeof($upload) > 0)
		{
			foreach($upload AS $key=>$value)
			{
				if($this->_description) $desc = $value; else $desc = '';
				$query = "INSERT INTO $table (reference, filename, description) VALUES ($reference, '$key', '$desc')";
				$result = $this->_db->actionquery($query);
			}
		}
	}
	
	/**
	 * Copia i file uploadati da una directory a un'altra
	 * -> utile quando un file proveniente da un form deve essere associato a più record e inserito
	 * in directory distinte attinenti questi record 
	 * 
	 * @param array $upload			output del metodo 'multipleUploadAction()'
	 * @param string $dir_source	directory sorgente (file da copiare)
	 * @param string $dir_dest		directory di destinazione
	 * @param string redirect
	 * @param string $link_error
	 */
	public function copyUploadedFile($upload, $dir_source, $dir_dest, $redirect, $link_error){
		
		if(sizeof($upload) > 0)
		{
			$dir_source = $this->dirUpload($dir_source);
			$dir_dest = $this->dirUpload($dir_dest);
			
			if(!is_dir($dir_dest) && $dir_dest != '')
			{
				if(!@mkdir($dir_dest, 0755))
					EvtHandler::HttpCall($this->_home, $redirect, $link_error.'error=27');
			}
			
			foreach($upload AS $key=>$value)
			{
				if(file_exists($dir_source.$key))
				{
					if(!@copy($dir_source.$key, $dir_dest.$key))
						EvtHandler::HttpCall($this->_home, $redirect, $link_error.'error=28');
				}
			}
		}
		return null;
	}
	
	/*
		Javascript
	*/
	
	/**
	 * Javascript per presentazione campo multifile a partire da un input
	 *
	 * @param string $input_name		nome dell'input di scelta
	 * @param string $ref_id			ID di riferimento (input name)
	 * @param string $instance			nome istanza
	 * @param string $method			nome funzione che recupera i dati
	 * @param string $result			nome DIV all'interno del quale vengono scritti i risultati
	 * @param string $table				nome della tabella dei file
	 * @return string
	 * 
	 * Invia alla funzione '$func_name' le variabili: rid, action, tbl, opt
	 */
	public function jsInputLib($input_name, $ref_id, $instance='', $method='', $result, $table) {
	
		$GINO = '';
		
		if(empty($instance)) $instance = $this->_className;
		if(empty($method)) $method = 'mfileAttached';
		
		$GINO .= $this->jsUploadLib();
		
		$GINO .= "<script type=\"text/javascript\">\n";
		$GINO .= "window.addEvent('domready', function() {
					
					if($('".$input_name."') != null)
					{
						var myvar;
						
						$('".$input_name."').addEvent('change', function(e) {
							
							myvar = $(this).getProperty('value');
							
							// reimposta l'input
							$(this).setProperty('value', myvar);
							
							ref_id = $('".$ref_id."').getProperty('value');
							action = $('action').getProperty('value');
							table = $('$table').getProperty('value');
							
							var url = '".$this->_home."?pt[$instance-$method]';
							var data = 'rid='+ref_id+'&action='+action+'&tbl='+table+'&opt='+myvar;
							ajaxRequest('post', url, data, '".$result."');
						}
					)
				};
			});\n";
		$GINO .= "</script>\n";
		
		return $GINO;
	}
	
	public function jsUploadLib($text=''){
		
		if(empty($text)) $text = _("Aggiungi file");
		
		$GINO = "<script type=\"text/javascript\">\n";
		$GINO .= "
var {$this->_current_upload} = 0;	// current # of attachment sections on the web page

// For some reason when a div is taken out, the form will scroll to the top on both Firefox and IE
var scrollPosVert = 0;		// stores the current scroll position on the form

// SCROLL FUNCTIONS
function saveScrollPos(offset){
	scrollPosVert=(document.all)?document.body.scrollTop:window.pageYOffset-offset;
}

function setScrollPos(){
	window.scrollTo(0, scrollPosVert);
	setTimeout('window.scrollTo(0, scrollPosVert)',1);
}

window.".$this->_js_function_name."=function(fileFieldName,descFieldName,refId){
	
	var max = 0;				// maximum # of attachments allowed
	var positionNewAttach = '".$this->_id_print_block."';	// attachmentmarker

	var nameFile = fileFieldName;
	var nameDesc = descFieldName;
	{$this->_current_upload}++;
	
	if(refId != '')
		positionAttachment=positionNewAttach+refId;
	
	if ({$this->_current_upload}>0)
		document.getElementById('".$this->_id_name."').childNodes[0].data='$text';
	
	// First, clone the hidden attachment section
	var newFields = document.getElementById('".$this->_id_hidden_block."').cloneNode(true);	// attachment
	newFields.id = '';
	// Make the new attachments section visible
	newFields.style.display = 'block';
	
	// loop through tags in the new Attachment section and set ID and NAME properties
	var newField = newFields.childNodes;
	for (var i=0;i<newField.length;i++)
	{
		if (newField[i].name==nameFile)
		{
			newField[i].id=nameFile+{$this->_current_upload};
			newField[i].name=nameFile+'[]';
			//newField[i].name=nameFile+{$this->_current_upload};
		}
		if (newField[i].name==nameDesc)
		{
			newField[i].id=nameDesc+{$this->_current_upload};
			newField[i].name=nameDesc+'[]';
			//newField[i].name=nameDesc+{$this->_current_upload};
		}
	}
	// Insert our new Attachment section into the Attachments Div on the form...
	var insertHere = document.getElementById(positionAttachment);
	insertHere.parentNode.insertBefore(newFields,insertHere);
}

// This function removes an attachment from the form
// and updates the ID and Name properties of all other
// Attachment sections
function removeFile(container, item){
	// get the ID number of the upload section to remove
	var tmp = item.getElementsByTagName('input')[0];
	var basefieldname = '';
	basefieldname = nameFile;
	var iRemove=Number(tmp.id.substring(basefieldname.length, tmp.id.length));
	// Shift all INPUT field IDs and NAMEs down by one (for fields with a higher ID than the one being removed)
	var x = document.getElementById('".$this->_id_main_block."').getElementsByTagName('input');
	for (i=0;i<x.length;i++){
		basefieldname=nameFile;
		var iEdit = Number(x[i].id.substring(basefieldname.length, x[i].id.length));
		if (iEdit>iRemove){
			x[i].id=basefieldname+(iEdit-1);
			x[i].name=basefieldname+(iEdit-1);
		}
	}

	// Run through all the DropCap divs (the number to the right of the attachment section) and update that number...
	x=document.getElementById('".$this->_id_main_block."').getElementsByTagName('div');
	for (i=0;i<x.length;i++){
		// Verify this is actually the \"dropcap\" div
		if (x[i].id.substring(0, String('dropcap').length)=='dropcap'){
			ID = Number(x[i].id.substring(String('dropcap').length, x[i].id.length));
			// check to see if current attachment had a higher ID than the one we're
			// removing (and thus needs to have its ID dropped)
			if (ID>iRemove){
				x[i].id='dropcap'+(ID-1);
				x[i].childNodes[0].data=(ID-1);
			}
		}
	}

	{$this->_current_upload}--;
	saveScrollPos(0);
	container.removeChild(item);
	setScrollPos();
	document.getElementById('".$this->_id_name."').style.visibility='visible';
	if ({$this->_current_upload}==0)
		document.getElementById('".$this->_id_name."').childNodes[0].data='$text';
}
		";
		$GINO .= "</script>\n";
		
		return $GINO;
	}
}
?>
