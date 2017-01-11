<?php
/**
 * @file settings.php
 * @brief Definizione dei percorsi dell'applicazione
 *
 * @copyright 2005-20017 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

namespace Gino;

/** Separatore di directory definito dal sistema operativo */
define('OS', DIRECTORY_SEPARATOR);

// Percorsi Assoluti

/** Percorso assoluto alla directory cache */
define('CACHE_DIR', SITE_ROOT.OS.'cache');

/** Percorso assoluto alla directory lib */
define('LIB_DIR', SITE_ROOT.OS.'lib');

/** Percorso assoluto alla directory che contiene le classi che non fanno parte di moduli */
define('CLASSES_DIR', LIB_DIR.OS.'classes');

/** Percorso assoluto alla directory che contiene le classi che descrivono campi di db */
define('FIELDS_DIR', CLASSES_DIR.OS.'fields');

/** Percorso assoluto alla directory che contiene le directory dei moduli */
define('APP_DIR', SITE_ROOT.OS.'app');

/** Percorso assoluto alla directory che contiene plugins */
define('PLUGIN_DIR', LIB_DIR.OS.'plugin');

/** Percorso assoluto alla directory che contiene css */
define('CSS_DIR', SITE_ROOT.OS.'css');

/** Percorso assoluto alla directory che contiene templates */
define('TPL_DIR', SITE_ROOT.OS.'templates');

/** Percorso assoluto alla directory che contiene viste generiche di sistema */
define('VIEWS_DIR', SITE_ROOT.OS.'views');

/** Percorso assoluto alla directory che contiene file di grafica */
define('GRAPHICS_DIR', SITE_ROOT.OS.'graphics');

/** Percorso assoluto alla directory che contiene upload degli utenti */
define('CONTENT_DIR', SITE_ROOT.OS.'contents');

/** Percorso assoluto alla directory che contiene fonts files */
define('FONTS_DIR', SITE_ROOT.OS.'fonts');

/** Percorso assoluto alla directory per la creazione di file temporanei */
define('TMP_DIR', '/tmp');

// percorsi relativi

// @cond no-doxygen
$home_file = basename($_SERVER['SCRIPT_NAME']);
// @endcond

/** Percorso relativo dello SCRIPT_FILE */
define('HOME_FILE', SITE_WWW.'/'.$home_file);

/** Percorso relativo alla directory dei css */
define('CSS_WWW', SITE_WWW.'/css');

/** Percorso relativo alla directory che contiene i moduli */
define('SITE_APP', SITE_WWW.'/app');

/** Percorso relativo alla directory che contiene immagini si sistema */
define('SITE_IMG', SITE_WWW.'/img');

/** Percorso relativo alla directory che contiene file di grafica */
define('SITE_GRAPHICS', SITE_WWW.'/graphics');

/** Percorso relativo alla directory lib */
define('SITE_LIB', SITE_WWW.'/lib');

/** Percorso relativo alla directory che contiene librerie javascript */
define('SITE_JS', SITE_LIB.'/js');

/** Percorso relativo alla directory che contiene file di customizzazione ckeditor */
define('SITE_CUSTOM_CKEDITOR', SITE_LIB.'/custom_ckeditor');

/** Percorso relativo alla directory che contiene file uploadati dall'utente */
define('CONTENT_WWW', SITE_WWW.'/contents');

// prefissi

/** Prefisso classi di tipo Gino.Controller */
define('CONTROLLER_CLASS_PREFIX', 'class_');

/** Prefisso classi di tipo Gino.Model */
define('MODEL_CLASS_PREFIX', 'class.');

/** Prefisso classi non interne a moduli */
define('CORE_CLASS_PREFIX', 'class.');

// Include il file di configurazione dell'applicazione
include('configuration.php');
