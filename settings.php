<?php
/**
 * @file settings.php
 * @brief Definizione dei percorsi dell'applicazione
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

/**
 * Include il file di configurazione dell'applicazione
 */
include('configuration.php');

/**
 * Separatore di directory
 * Definito dal sistema operativo
 */
define('OS', DIRECTORY_SEPARATOR);

// Percorsi Assoluti

define('CACHE_DIR', SITE_ROOT.OS.'cache');
define('LIB_DIR', SITE_ROOT.OS.'lib');
define('CLASSES_DIR', LIB_DIR.OS.'classes');
define('FIELDS_DIR', CLASSES_DIR.OS.'fields');
define('APP_DIR', SITE_ROOT.OS.'app');
define('INCLUDE_DIR', LIB_DIR.OS.'include');
define('PLUGIN_DIR', LIB_DIR.OS.'plugin');
define('CSS_DIR', SITE_ROOT.OS.'css');
define('TPL_DIR', SITE_ROOT.OS.'templates');
define('VIEWS_DIR', SITE_ROOT.OS.'views');
define('GRAPHICS_DIR', SITE_ROOT.OS.'graphics');
define('CONTENT_DIR', SITE_ROOT.OS.'contents');
define('FONTS_DIR', SITE_ROOT.OS.'fonts');
define('DOC_DIR', CONTENT_DIR.OS.'documents');
define('TMP_DIR', '/tmp');
define('TMP_SITE_DIR', SITE_ROOT.OS.'tmp');

/*
	Home Site
*/
$home_file = basename($_SERVER['SCRIPT_NAME']);
define('HOME_FILE', SITE_WWW.'/'.$home_file);

/*
	Web Path Directories
*/
define('CSS_WWW', SITE_WWW.'/css');
define('CSS_BASE', CSS_WWW.'/main.css');

define('SITE_APP', SITE_WWW.'/app');
define('SITE_IMG', SITE_WWW.'/img');
define('SITE_GRAPHICS', SITE_WWW.'/graphics');
define('SITE_LIB', SITE_WWW.'/lib');
define('SITE_JS', SITE_LIB.'/js');
define('SITE_CUSTOM_CKEDITOR', SITE_LIB.'/custom_ckeditor');

define('CONTENT_WWW', SITE_WWW.'/contents');
define('DOC_WWW', CONTENT_WWW.'/documents');

define('EVT_NAME', 'evt');
define('CONTROLLER_CLASS_PREFIX', 'class_');
define('MODEL_CLASS_PREFIX', 'class.');
define('CORE_CLASS_PREFIX', 'class.');
define('INSTANCE_PREFIX', '_instance_');

?>
