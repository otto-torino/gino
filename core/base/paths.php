<?php
/**
 * @file paths.php
 * @brief Definizione dei percorsi dell'applicazione
 */

namespace Gino;

//@cond no-doxygen
$siteroot = preg_match("#^[a-zA-Z][:\\\]+#", SITE_ROOT)
? preg_replace("#\\\#", "/", SITE_ROOT)
: SITE_ROOT;

// Per compatibilità con l'ambiente Windows ($_SERVER['DOCUMENT_ROOT'] termina con '/')
$docroot = preg_match("#^[a-zA-Z][:\\\]+#", $_SERVER['DOCUMENT_ROOT'])
? preg_replace("#\\\#", "/", $_SERVER['DOCUMENT_ROOT'])
: $_SERVER['DOCUMENT_ROOT'];

$docroot = (substr($docroot, -1) == '\\') ? substr_replace($docroot, '', -1) : $docroot;
$site_www = preg_replace("#".preg_quote($docroot)."?#", "", $siteroot);
// @endcond

/** @const SITE_WWW Percorso relativo dell'applicazione a partire dalla root directory */
define('SITE_WWW', $site_www);

/** Separatore di directory definito dal sistema operativo */
define('OS', DIRECTORY_SEPARATOR);

/**
 * Carattere separatore di istanza e metodo negli indirizzi web
 * Se si cambia questo carattere occorre modificare le stringhe dei campi @a rexp e @a urls della tabella sys_layout_skin 
 * dove sia presente il carattere separatore.
 */
define('URL_SEPARATOR', '.');

// Percorsi Assoluti

/** Percorso assoluto alla directory cache */
define('CACHE_DIR', SITE_ROOT.OS.'cache');

/** Percorso assoluto alla directory delle impostazioni */
define('SETTINGS_DIR', SITE_ROOT.OS.'settings');

// Percorso assoluto alla directory che contiene le app
define('APP_DIR', SITE_ROOT.OS.'app');

/** Percorso assoluto alla directory che contiene css */
define('CSS_DIR', SITE_ROOT.OS.'css');

/** Percorso assoluto alla directory che contiene templates */
define('TPL_DIR', SITE_ROOT.OS.'templates');

/** Percorso assoluto alla directory che contiene viste generiche di sistema */
define('VIEWS_DIR', SITE_ROOT.OS.'views');

/** Percorso assoluto alla directory che contiene upload degli utenti */
define('CONTENT_DIR', SITE_ROOT.OS.'contents');

/** Percorso assoluto alla directory che contiene fonts files */
define('FONTS_DIR', SITE_ROOT.OS.'fonts');

/** Percorso assoluto alla directory per la creazione di file temporanei */
define('TMP_DIR', '/tmp');

/**
 * Percorsi assoluti - Core
 */

// Directory core
define('CORE_DIR', SITE_ROOT.OS.'core');

// Directory che contiene le app di sistema
define('CORE_APP_DIR', CORE_DIR.OS.'app');

// Directory che contiene i file con le impostazioni di base
define('CORE_BASE_DIR', CORE_DIR.OS.'base');

// Percorso assoluto alla directory che contiene le risorse
define('RESOURCES_DIR', CORE_DIR.OS.'resources');

// Percorso assoluto alla directory che contiene le librerie
define('LIBRARIES_DIR', RESOURCES_DIR.OS.'libraries');

/** Percorso assoluto alla directory che contiene le funzioni generali */
define('FUNCTIONS_DIR', RESOURCES_DIR.OS.'functions');

/** Percorso assoluto alla directory che contiene le classi che non fanno parte di moduli */
define('CLASSES_DIR', RESOURCES_DIR.OS.'classes');

/** Percorso assoluto alla directory che contiene le classi che descrivono campi di db */
define('FIELDS_DIR', CLASSES_DIR.OS.'fields');

/** Percorso assoluto alla directory che contiene plugins */
define('PLUGIN_DIR', RESOURCES_DIR.OS.'plugin');

/** Percorso assoluto alla directory che contiene i file di customizzazione di CKEditor */
define('CUSTOM_CKEDITOR_DIR', RESOURCES_DIR.OS.'custom_ckeditor');

/**
 * Percorsi relativi
 */

// @cond no-doxygen
$home_file = basename($_SERVER['SCRIPT_NAME']);
// @endcond

/** Percorso relativo dello SCRIPT_FILE */
define('HOME_FILE', SITE_WWW.'/'.$home_file);

/** Percorso relativo alla directory dei css */
define('CSS_WWW', SITE_WWW.'/css');

/** Percorso relativo alla directory che contiene le app */
define('SITE_APP', SITE_WWW.'/app');

/** Percorso relativo alla directory che contiene immagini si sistema */
define('SITE_IMG', SITE_WWW.'/img');

/**
 * Percorsi relativi - core
 */

// Directory core
define('SITE_CORE', SITE_WWW.'/core');

// Directory delle app di sistema
define('SITE_CORE_APP', SITE_CORE.'/app');

// Directory che contiene i file con le impostazioni di base
define('SITE_CORE_BASE', SITE_CORE.'/base');

// Percorso relativo alla directory che contiene le risorse
define('SITE_RESOURCES', SITE_CORE.'/resources');

// Percorso relativo alla directory che contiene le librerie
define('SITE_LIBRARIES', SITE_RESOURCES.'/libraries');

/** Percorso relativo alla directory che contiene librerie javascript */
define('SITE_JS', SITE_RESOURCES.'/js');

/** Percorso relativo alla directory che contiene file di customizzazione di CKEditor */
define('SITE_CUSTOM_CKEDITOR', SETTINGS_DIR.'/custom_ckeditor');

/** Percorso relativo alla directory che contiene file uploadati dall'utente */
define('CONTENT_WWW', SITE_WWW.'/contents');

/**
 * Prefissi
 */

/** Prefisso classi di tipo Gino.Controller */
define('CONTROLLER_CLASS_PREFIX', 'class_');

/** Prefisso classi di tipo Gino.Model */
define('MODEL_CLASS_PREFIX', 'class.');

/** Prefisso classi non interne a moduli */
define('CORE_CLASS_PREFIX', 'class.');

// Include il file di configurazione dell'applicazione
include SETTINGS_DIR.OS.'config.inc';
