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
define('SITE_WWW', preg_replace("#".preg_quote($_SERVER['DOCUMENT_ROOT'])."?#", "", SITE_ROOT));

include('settings.php');
header("Location:http://".$_SERVER['HTTP_HOST'].SITE_WWW."/index.php?evt[index-admin_page]");
exit();
?>