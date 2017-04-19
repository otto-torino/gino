<?php
namespace Gino;
/**
* @file logger_system_variables.php
* @brief Template che stampa un dump delle variabili di sistema ($_SERVER, $_SESSION, $_REQUEST).
*
* @see Gino.Logger
* @copyright 2014 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
* @authors Marco Guidotti guidottim@gmail.com
* @authors abidibo abidibo@gmail.com
*/
?>
<? //@cond no-doxygen ?>
<h2><?= _('SERVER') ?></h2>
<?php
ob_start();
var_dump($_SERVER);
$result = ob_get_clean();
echo $result;
?>

<h2><?= _('SESSIONE') ?></h2>
<?php
ob_start();
var_dump($_SESSION);
$result = ob_get_clean();
echo $result;
?>

<h2><?= _('REQUEST') ?></h2>
<?php
ob_start();
var_dump($_REQUEST);
$result = ob_get_clean();
echo $result;
?>
<? // @endcond ?>
