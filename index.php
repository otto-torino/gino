<?php
/** @mainpage gino - CMS framework
 *
 * gino è sviluppato con tecnologia PHP e fornisce tutti gli strumenti necessari per creare un sito web e gestire i contenuti in modo semplice ed efficace.
 *
 * Visitare il <a href="http://gino.otto.to.it">sito del progetto</a> per trovare ulteriori informazioni, e in particolare consultare il <a href="http://gino.otto.to.it/wiki/gino/index.html">wiki</a> per visualizzare i requisiti necessari. \n
 * I moduli e i plugin di gino sono disponibili su github sotto l'account <a href="http://www.github.com/otto-torino">otto-torino</a>.
 */

/**
 * @file index.php
 * @brief Pagina principale
 *
 * @description Entry point dell'applicazione.
 *              Definisce i percorsi alla root directory ed inizializza la classe Core che fornisce la risposta HTTP.
 *
 * @copyright 2005-2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

/**
 * @namespace Gino
 * @brief Namespace principale di gino
 * @description Tutte le classi di gino appartengono a questo namespace o sub-namespace
 */
namespace Gino;

/**
 * Percorso assoluto alla root directory
 */
define('SITE_ROOT', dirname(realpath(__FILE__)));

$siteroot = preg_match("#^[a-zA-Z][:\\\]+#", SITE_ROOT)
    ? preg_replace("#\\\#", "/", SITE_ROOT)
    : SITE_ROOT;

/**
 * Per compatibilità con l'ambiente Windows (-> $_SERVER['DOCUMENT_ROOT'] termina con '/')
 */
$docroot = preg_match("#^[a-zA-Z][:\\\]+#", $_SERVER['DOCUMENT_ROOT']) 
    ? preg_replace("#\\\#", "/", $_SERVER['DOCUMENT_ROOT'])
    : $_SERVER['DOCUMENT_ROOT'];

$docroot = (substr($docroot, -1) == '\\') ? substr_replace($docroot, '', -1) : $docroot;

/** Percorso relativo dell'applicazione a partire dalla root directory */
define('SITE_WWW', preg_replace("#".preg_quote($docroot)."?#", "", $siteroot));

/** Include le variabili con i percorsi dell'applicazione */
include('settings.php');

/** Include le costanti utilizzate da tutto il sistema */
include_once(LIB_DIR.OS."const.php");

/** Include le funzioni definite nel namespace globale */
include_once(LIB_DIR.OS."global.php");

/** Include funzioni utilizzate da tutto il sistema */
include_once(LIB_DIR.OS."func.php");

/** Include la classe utilizzata per caricare classi di sistema e modelli. I controller sono caricati in maniera automatica */
include(CLASSES_DIR.OS."class.Loader.php");

/** core dell'applicazione */
$core = Loader::load('Core');

/** risposta HTTP */
try {
    $core->answer();
}
catch(\Exception $e) {
    Logger::manageException($e);
}
