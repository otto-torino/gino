<?php
/** @mainpage gino - CMS framework
 * 
 * gino è sviluppato con tecnologia PHP e fornisce tutti gli strumenti necessari per creare un sito web e gestire i contenuti in modo semplice ed efficace.
 * 
 * Visitare il <a href="http://gino.otto.to.it">sito del progetto</a> per trovare ulteriori informazioni, e in particolare consultare il <a href="http://gino.otto.to.it/wiki/gino/index.html">wiki</a> per visualizzare i requisiti necessari. \n
 * I moduli e i plugin di gino sono disponibili su github nel <a href="http://www.github.com/otto-torino">repository otto-torino</a>.
 */

/**
 * @file index.php
 * @brief Pagina principale
 * 
 * Definisce i percorsi alla root directory
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

/**
 * Percorso assoluto alla root directory
 */
define('SITE_ROOT', dirname(realpath(__FILE__)));

$siteroot = preg_match("#^[a-zA-Z][:\\\]+#", SITE_ROOT) ? preg_replace("#\\\#", "/", SITE_ROOT) : SITE_ROOT;

// Per compatibilità con l'ambiente Windows (-> $_SERVER['DOCUMENT_ROOT'] termina con '/')
$docroot = preg_match("#^[a-zA-Z][:\\\]+#", $_SERVER['DOCUMENT_ROOT']) ? preg_replace("#\\\#", "/", $_SERVER['DOCUMENT_ROOT']) : $_SERVER['DOCUMENT_ROOT'];
$docroot = (substr($docroot, -1) == '\\') ? substr_replace($docroot, '', -1) : $docroot;

//$docroot = (substr($_SERVER['DOCUMENT_ROOT'], -1) == '/') ? substr_replace($_SERVER['DOCUMENT_ROOT'], '', -1) : $_SERVER['DOCUMENT_ROOT'];	// only MySQL

/**
 * Percorso relativo dell'applicazione a partire dalla root directory
 */
define('SITE_WWW', preg_replace("#".preg_quote($docroot)."?#", "", $siteroot));

/**
 * Include le variabili con i percorsi dell'applicazione
 */
include('settings.php');

/**
 * Include le costanti utilizzate da tutto il sistema
 */
include_once(LIB_DIR.OS."const.php");
/**
 * Include funzioni utilizzate da tutto il sistema
 */
include_once(LIB_DIR.OS."func.php");

/**
 * Include la classe utilizzata per caricare classi di sistema e modelli. I controller sono caricati in maniera automatica
 */
include(CLASSES_DIR.OS."class.Loader.php");

/**
 * core dell'applicazione
 */
$core = loader::load('Core');

/**
 * Include la libreria per la cattura di chiamate ajax
 */
include(LIB_DIR.OS.'methodPointer.php');

// renderizza il documento
$core->renderApp();

// debug database
$db = db::instance();
echo $db->getInfoQuery();
