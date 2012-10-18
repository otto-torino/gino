<?php
/** @mainpage gino - CMS framework
 * 
 * gino è sviluppato con tecnologia PHP e fornisce tutti gli strumenti necessari per creare un sito web e gestire i contenuti in modo semplice ed efficace.
 * 
 * Si consiglia di visitare il <a href="http://gino.otto.to.it">sito del progetto</a> per trovare ulteriori informazioni.   
 * 
 * I moduli e i plugin di gino sono disponibili su github nel <a href="http://www.github.com/otto-torino">repository otto-torino</a>.
 * 
 * @b REQUISITI
 *   - php >= 5.3   
 *   - mysql >= 5   
 *   - web server: apache (consigliato), nginx, lighttpd, o microsoft IIS
 * 
 * sul server web apache devono essere abilitati i seguenti moduli
 *   - deflate
 *   - expires
 *   - headers
 *   - rewrite
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
define('SITE_ROOT', realpath(dirname(__FILE__)));

$siteroot = preg_match("#^[a-zA-Z][:\\\]+#", SITE_ROOT) ? preg_replace("#\\\#", "/", SITE_ROOT) : SITE_ROOT;
// Rispetto a Linux, in Windows $_SERVER['DOCUMENT_ROOT'] termina con '/'
$docroot = (substr($_SERVER['DOCUMENT_ROOT'], -1) == '/') ? substr_replace($_SERVER['DOCUMENT_ROOT'], '', -1) : $_SERVER['DOCUMENT_ROOT'];

/**
 * Percorso relativo dell'applicazione a partire dalla root directory
 */
define('SITE_WWW', preg_replace("#".preg_quote($docroot)."?#", "", $siteroot));

/**
 * Include le variabili con i percorsi dell'applicazione
 */
include('settings.php');

/**
 * Include la classe singleton
 */
include(LIB_DIR.OS."singleton.php");

/**
 * Include la classe session
 */
include(LIB_DIR.OS."session.php");

/**
 * Include il file definito nella variabile CORE
 */
include(CORE);
?>