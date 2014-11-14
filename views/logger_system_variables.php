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
