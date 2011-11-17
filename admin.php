<?php
define('SITE_ROOT', realpath(dirname(__FILE__)));
define('SITE_WWW', preg_replace("#".preg_quote($_SERVER['DOCUMENT_ROOT'])."?#", "", SITE_ROOT));

include('settings.php');
header("Location:http://".$_SERVER['HTTP_HOST'].SITE_WWW."/index.php?evt[index-admin_page]");
exit();
?>