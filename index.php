<?php
/**
 * @file index.php
 * @brief Entry point di gino CMS
 *
 * @description Entry point dell'applicazione.
 *              Definisce i percorsi alla root directory ed inizializza la classe Gino.Core che fornisce la risposta HTTP.
 *              Raccoglie eventuali eccezioni e le tratta attraverso la classe Gino.Logger che ha comportamenti diversi a
 *              seconda del valore della costante DEBUG impostata nel file @ref settings/config.inc
 */

/**
 * @namespace Gino
 * @brief Namespace principale di gino.
 * @description Contiene tutte le funzioni e classi proprie di gino al suo interno o in uno dei suoi sotto namespaces.
 */
namespace Gino;

/** 
 * @const SITE_ROOT Percorso assoluto alla root directory
 */
define('SITE_ROOT', dirname(realpath(__FILE__)));

// Include le variabili con i percorsi dell'applicazione
include 'core/base/paths.php';

// Include i nomi delle tabelle utilizzate da tutto il sistema
include_once CORE_BASE_DIR.OS."tables.php";

// Include le funzioni definite nel namespace globale
include_once CORE_BASE_DIR.OS."global.php";

// Include funzioni utilizzate da tutto il sistema
include_once FUNCTIONS_DIR.OS."func.php";

// Include la classe utilizzata per caricare classi di sistema e modelli. I controller sono caricati in maniera automatica
include CLASSES_DIR.OS."class.Loader.php";

// Debug and query stats
if(DEBUG && SHOW_STATS)
{
	function getmicrotime(){
		list($microsec, $sec) = explode(" ", microtime());
		return ((float) $microsec + (float) $sec);
	}

	$starttime = getmicrotime();
}

//@cond no-doxygen

// core dell'applicazione
$core = Loader::load('Core');

// risposta http
try {
    $core->answer();
}
catch(\Exception $e) {
    Logger::manageException($e);
}

// @endcond

if(DEBUG && SHOW_STATS) {

	$endtime = getmicrotime();
	$totaltime = $endtime - $starttime;

	$db = db::instance();

	$debug = "<section class=\"debug\" onclick=\"if($(this).hasClass('active')) $(this).removeClass('active'); else $(this).addClass('active');\">";
	$debug .= "<h1>Debug</h1>";
	$debug .= "<h2>Script execution time</h2>";
	$debug .= "<p>This page was created in ".$totaltime." seconds</p>";
	$debug .= "<h2>Database</h2>";
	$debug .= $db->getInfoQuery();

	echo $debug;
}
