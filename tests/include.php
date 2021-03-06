<?php
/**
* @file include.php
* @brief Include classi e funzioni per l'esecuzione di unit tests
*/

// @see index.php
define('SITE_ROOT', realpath(dirname(__FILE__).DIRECTORY_SEPARATOR.'..'));
define('SITE_WWW', '');

require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'settings.php';
require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'const.php';
require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'global.php';
require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'functions'.DIRECTORY_SEPARATOR.'func.php';
require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'class.Loader.php';
