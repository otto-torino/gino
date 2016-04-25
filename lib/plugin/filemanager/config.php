<?php
/**
 * @file config.php
 * @brief Filemanager configuration
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

if (!defined('DIRECTORY_SEPARATOR')) {
	define('DIRECTORY_SEPARATOR', strtoupper(substr(PHP_OS, 0, 3) == 'WIN') ? '\\' : '/');
}

define('SITE_ROOT', realpath(dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'));
define('CK_CONTENT_DIR', SITE_ROOT.DIRECTORY_SEPARATOR.'contents'.DIRECTORY_SEPARATOR.'ckeditor');
define('CK_CONTENT_WWW', 'contents/ckeditor/');

require_once(SITE_ROOT.DIRECTORY_SEPARATOR.'configuration.php');

/**
 * @brief Classe per la definizione delle impostazioni del filemanager di CKEditor
 *
 * @copyright 2016 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 *
 * ##Example link from CKEditor
 * http://domain.net/lib/plugin/plugin.ckeditor.php?type=image&action=browse&CKEditor=text&CKEditorFuncNum=1&langCode=it
 * 
 * ##Configuration settings for each Resource Type
 * 
 * ###AllowedExtensions
 * The possible extensions that can be allowed.
 * If it is empty then any file type can be uploaded.
 * 
 * ###DeniedExtensions
 * The extensions that won't be allowed.
 * If it is empty then no restrictions are done here.
 * 
 * ###{File}TypesPath
 * The virtual folder relative to the document root where these resources will be located.
 * Attention: It must end with a slash: '/'
 * 
 * ###{File}TypesAbsolutePath
 * The physical path to the above folder. It must be an absolute path.
 * It must end with a slash: '/'
 * 
 */
class config {
	
	/**
	 * Enable connector
	 * @var boolean
	 */
	protected $_enabled;
	
	protected $_absolute_path, $_relative_path;
	
	/**
	 * Perform additional checks for image files. 
	 * If set to true, validate image size (using getimagesize)
	 * @var boolean
	 */
	protected $_secure_image_uploads;
	
	/**
	 * Allowed Resource Types ('file', 'image', 'flash', 'media')
	 * @var array
	 */
	protected $_allowed_types;
	
	/**
	 * For security, HTML is allowed in the first Kb of data for files having the following extensions only
	 * @var array
	 */
	protected $_html_extensions;
	
	/**
	 * Due to security issues with Apache modules, it is recommended to leave the following setting enabled
	 * @var boolean
	 */
	protected $_force_single_extension;
	
	/**
	 * The possible extensions that can be allowed
	 * @var array
	 */
	protected $_file_allowed_extensions, $_image_allowed_extensions, $_flash_allowed_extensions, $_media_allowed_extensions;
	
	/**
	 * The extensions that won't be allowed
	 * @var array
	 */
	protected $_file_denied_extensions, $_image_denied_extensions, $_flash_denied_extensions, $_media_denied_extensions;
	
	/**
	 * The virtual folder relative to the document root where these resources will be located
	 * @var string
	 */
	protected $_file_path, $_image_path, $_flash_path, $_media_path;
	
	/**
	 * The absolute path to the above folder
	 * @var string
	 */
	protected $_file_absolute_path, $_image_absolute_path, $_flash_absolute_path, $_media_absolute_path;
	
	/**
	 * After file is uploaded, sometimes it is required to change its permissions so that it was possible to access it at the later time
	 * 
	 * If possible, it is recommended to set more restrictive permissions, like 0755. Set to 0 to disable this feature.
	 * Note: not needed on Windows-based servers
	 * @var integer
	 */
	protected $_chmod_on_upload;
	
	/**
	 * Used when creating folders that does not exist
	 *
	 * @see comments above
	 * @var integer
	 */
	protected $_chmod_on_folder_create;
	
	/**
	 * Costruttore
	 *
	 * @return void
	 */
	function __construct(){
	
		$this->_enabled = true;
		$this->_secure_image_uploads = true;
		$this->_allowed_types = array('file', 'image');
		$this->_html_extensions = array("html", "htm", "xml", "xsd", "txt", "js");
		$this->_force_single_extension = true;
		
		$this->_absolute_path = CK_CONTENT_DIR;
		$this->_relative_path = CK_CONTENT_WWW;
		
		$this->_file_allowed_extensions = array('7z', 'aiff', 'asf', 'avi', 'bmp', 'csv', 'doc', 'fla', 'flv', 'gif', 'gz', 'gzip', 'jpeg', 'jpg', 'mid', 'mov', 'mp3', 'mp4', 'mpc', 'mpeg', 'mpg', 'ods', 'odt', 'pdf', 'png', 'ppt', 'pxd', 'qt', 'ram', 'rar', 'rm', 'rmi', 'rmvb', 'rtf', 'sdc', 'sitd', 'swf', 'sxc', 'sxw', 'tar', 'tgz', 'tif', 'tiff', 'txt', 'vsd', 'wav', 'wma', 'wmv', 'xls', 'xml', 'zip');
		$this->_file_denied_extensions = array();
		$this->_file_path = $this->_relative_path.'file/';
		$this->_file_absolute_path = $this->_absolute_path.DIRECTORY_SEPARATOR.'file/';
		
		$this->_image_allowed_extensions = array('bmp','gif','jpeg','jpg','png');
		$this->_image_denied_extensions = array();
		$this->_image_path = $this->_relative_path.'image/';
		$this->_image_absolute_path = $this->_absolute_path.DIRECTORY_SEPARATOR.'image/';
		
		$this->_flash_allowed_extensions = array('swf','flv');
		$this->_flash_denied_extensions = array();
		$this->_flash_path = $this->_relative_path.'flash/';
		$this->_flash_absolute_path = $this->_absolute_path.DIRECTORY_SEPARATOR.'flash/';
		
		$this->_media_allowed_extensions = array('aiff', 'asf', 'avi', 'bmp', 'fla', 'flv', 'gif', 'jpeg', 'jpg', 'mid', 'mov', 'mp3', 'mp4', 'mpc', 'mpeg', 'mpg', 'png', 'qt', 'ram', 'rm', 'rmi', 'rmvb', 'swf', 'tif', 'tiff', 'wav', 'wma', 'wmv');
		$this->_media_denied_extensions = array();
		$this->_media_path = $this->_relative_path.'media/';
		$this->_media_absolute_path = $this->_absolute_path.DIRECTORY_SEPARATOR.'media/';
		
		$this->_chmod_on_upload = 0755;
		$this->_chmod_on_folder_create = 0777;
	}
}

?>
