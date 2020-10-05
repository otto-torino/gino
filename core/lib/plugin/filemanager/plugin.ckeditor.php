<?php
/**
 * @file plugin.ckeditor.php
 * @brief Plugin per la gestione dei file nell'editor html
 * 
 * @copyright 2016 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

/**
 * @namespace Gino.Plugin.Filemanager
 * @description Namespace che comprende classi di tipo plugin.filemanager
 */
namespace Gino\Plugin\Filemanager;

require_once('./config.php');
require_once('./util.php');
require_once('./io.php');

\session_name(SESSION_NAME);
\session_start();

// Verify session
if(!$_SESSION['user_id'] || !$_SESSION['user_staff']) {
	exit();
}

/**
 * @brief Classe per la gestione dei file in ckeditor
 * 
 * @copyright 2016 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class plugin_ckeditor extends config {
	
	private $_dir_abs;
	private $_dir_rel;
	
	private $_callback;
	private $_ckeditor_instance;
	private $_lang_code;
	private $_type;
	private $_action;
	
	private $_allowed_ext;
	private $_denied_ext;
	
	/**
	 * Costruttore
	 * 
	 * @return void
	 */
	function __construct() {
		
		parent::__construct();
		
		if(!$this->_enabled) {
			SendUploadResults(1, '', '', 'This file uploader is disabled. Please check the "lib/plugin/filemanager/config.php" file');
		}
		
		$this->getUrlParam();
		$this->setProperties();
	}
	
	public function wrapper() {
		
		if($this->_action == 'browse') {
			$this->browse();
		}
		elseif ($this->_action == 'upload') {
			$this->upload();
		}
		else {
			exit();
		}
	}
	
	private function getUrlParam() {
		
		// The reference number of an anonymous function that should be used in the CKEDITOR.tools.callFunction (a random number)
		$this->_callback = isset($_GET['CKEditorFuncNum']) ? clean_int($_GET['CKEditorFuncNum']) : null;
		
		// The name of a CKEditor instance (might be used to load a specific configuration file or anything else)
		$this->_ckeditor_instance = isset($_GET['CKEditor']) ? clean_text($_GET['CKEditor']): null;
		
		// Language code (might be used to provide localized messages)
		$this->_lang_code = isset($_GET['langCode']) ? clean_text($_GET['langCode']): null;
		
		$this->_type = \strtolower(clean_text($_GET['type']));
		$this->_action = clean_text($_GET['action']);
	}
	
	private function setProperties() {
		
		if(!IsAllowedType($this->_type, $this->_allowed_types)) {
			SendUploadResults(1, '', '', 'Invalid type specified');
		}
		
		$prop_absolute_path = '_'.$this->_type.'_absolute_path';
		$prop_relative_path = '_'.$this->_type.'_path';
		$prop_allowed_ext = '_'.$this->_type.'_allowed_extensions';
		$prop_denied_ext = '_'.$this->_type.'_denied_extensions';
		
		$this->_dir_abs = $this->{$prop_absolute_path};
		$this->_dir_rel = $this->{$prop_relative_path};
		$this->_allowed_ext = $this->{$prop_allowed_ext};
		$this->_denied_ext = $this->{$prop_denied_ext};
	}
	
	private function browse() {
		
		$buffer = "
		<!DOCTYPE html>
		<html lang=\"en\">
		<head>
    		<meta charset=\"UTF-8\">
    		<title>Browsing Files</title>
			<script>
        	// Helper function to get parameters from the query string.
        	function getUrlParam( paramName ) {
            	var reParam = new RegExp( '(?:[\?&]|&)' + paramName + '=([^&]+)', 'i' );
            	var match = window.location.search.match( reParam );

           		return ( match && match.length > 1 ) ? match[1] : null;
        	}

        	// Simulate user action of selecting a file to be returned to CKEditor.
        	function returnFileUrl(filename) {

            	var funcNum = getUrlParam( 'CKEditorFuncNum' );
            	var fileUrl = '".$this->_dir_rel."'+filename;
            	window.opener.CKEDITOR.tools.callFunction( funcNum, fileUrl );
            	window.close();
        	}
    		</script>
        </head>
		<body>";
		
		$buffer .= "<h1 style=\"text-align: center;\">File Browser</h1>";
		
		$directory = $this->_dir_abs;
		
		if (is_dir($directory)) {
			if ($directory_handle = opendir($directory)) {
				while (($file = readdir($directory_handle)) !== false) {
					if((!is_dir($file)) & ($file!=".") & ($file!="..")) {
						
						$buffer .= "<div style=\"display: inline; padding: 4px;\"><a href=\"\" onclick=\"returnFileUrl('".$file."')\">";
						
						if($this->_type == 'file') {
							$buffer .= $file;
						} elseif($this->_type == 'image') {
							$imgsrc = '../../../'.$this->_dir_rel.$file;
							$buffer .= "<img src=\"$imgsrc\" alt=\"$file\" title=\"$file\" />";
						}
						$buffer .= "</a></div>";
					}
				}
				closedir($directory_handle);
			}
		}
		
		$buffer .= "</body>
		</html>";
		
		echo $buffer;
	}
	
	// Notice the last paramter added to pass the CKEditor callback function
	//private function checkUpload( $resourceType, $currentFolder, $sCommand, $CKEcallback = '' ) {
	//private function checkUpload($sFileName, $sFileTmp, $sServerDir, $Config=array()) {
	private function upload() {
	
		if(isset($_FILES['upload']) && !is_null($_FILES['upload']['tmp_name'])) {
			
			$sFileName = $_FILES['upload']['name'] ;
			$sFileTmp = $_FILES['upload']['tmp_name'];
			
			$sFileName = SanitizeFileName($sFileName, $this->_force_single_extension);
		}
		else {
			SendUploadResults(1, '', '', 'The file is not loaded');
		}
		
		$sErrorNumber = '0';
		$sOriginalFileName = $sFileName;

		// Get the extension.
		$sExtension = substr($sFileName, (strrpos($sFileName, '.') + 1));
		$sExtension = strtolower($sExtension);

		if($this->_type == 'image' && $this->_secure_image_uploads)
		{
			if(($isImageValid = IsImageValid($sFileTmp, $sExtension)) === false) {
				$sErrorNumber = '202';
			}
		}

		if(is_array($this->_html_extensions) && count($this->_html_extensions))
		{
			if (!IsHtmlExtension($sExtension, $this->_html_extensions) && 
				($detectHtml = DetectHtml($sFileTmp)) === true) {
				$sErrorNumber = '202';
			}
		}

		// Check if it is an allowed extension
		if (!$sErrorNumber && IsAllowedExt($sExtension, $this->_allowed_ext, $this->_denied_ext))
		{
			$iCounter = 0;
 
			while (true)
			{
				$sFilePath = $this->_dir_abs . $sFileName;
				
				if (is_file($sFilePath))	// rename file
				{
					$iCounter++;
					$sFileName = RemoveExtension($sOriginalFileName) . '(' . $iCounter . ').' . $sExtension;
					$sErrorNumber = '201';
				}
				else
				{
					\move_uploaded_file($sFileTmp, $sFilePath);
 
					if (is_file($sFilePath))
					{
						if (!$this->_chmod_on_upload) {
							break;
						}
 
						$permissions = 0777;
 
						if ($this->_chmod_on_upload) {
							$permissions = $this->_chmod_on_upload;
						}
 
						$oldumask = umask(0);
						\chmod($sFilePath, $permissions);
						\umask($oldumask);
					}
					break;
				}
			}

			if (file_exists($sFilePath))
			{
				//previous checks failed, try once again
				if (isset($isImageValid) && $isImageValid === -1 && IsImageValid($sFilePath, $sExtension) === false)
				{
					@unlink($sFilePath);
					$sErrorNumber = '202';
				}
				else if (isset($detectHtml) && $detectHtml === -1 && DetectHtml($sFilePath) === true)
				{
					@unlink($sFilePath);
					$sErrorNumber = '202';
				}
			}
		}
		else {
			$sErrorNumber = '202';
		}

		$sFileUrl = CombinePaths($this->_dir_rel, $sFileName);
		
		if(!$this->_callback)
		{
			SendUploadResults($sErrorNumber, $sFileUrl, $sFileName);
		}
		else
		{
			//issue the CKEditor Callback
			SendCKEditorResults($sErrorNumber, $this->_callback, $sFileUrl, $sFileName);
		}
		exit;
	}
}

$plugin_ckeditor = new plugin_ckeditor();
$plugin_ckeditor->wrapper();

?>