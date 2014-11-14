<?php
/**
 * @file admin.php
 * @brief Pagina amministrativa
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
define('SITE_ROOT', realpath(dirname(__FILE__)));

// Per compatibilità con l'ambiente Windows (-> $_SERVER['DOCUMENT_ROOT'] termina con '/')
$siteroot = preg_match("#^[a-zA-Z][:\\\]+#", SITE_ROOT) ? preg_replace("#\\\#", "/", SITE_ROOT) : SITE_ROOT;
$docroot = preg_match("#^[a-zA-Z][:\\\]+#", $_SERVER['DOCUMENT_ROOT']) ? preg_replace("#\\\#", "/", $_SERVER['DOCUMENT_ROOT']) : $_SERVER['DOCUMENT_ROOT'];
$docroot = (substr($docroot, -1) == '\\') ? substr_replace($docroot, '', -1) : $docroot;
define('SITE_WWW', preg_replace("#".preg_quote($docroot)."?#", "", $siteroot));

//define('SITE_WWW', preg_replace("#".preg_quote($_SERVER['DOCUMENT_ROOT'])."?#", "", SITE_ROOT));	// only MySQL

include('settings.php');
header("Location:http://".$_SERVER['HTTP_HOST'].SITE_WWW."/index.php?evt[index-admin_page]");
exit();
?>