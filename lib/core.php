<?php

include(LIB_DIR.OS."main.php");

/*
 * headers, get text, languages and static classes include
 */
$main = new Main();

/*
 * ajax requests
 */
include(METHOD_POINTER);

/*
 * print document
 */
ob_start();
$document = new document();
$document->render();
ob_end_flush();

?>
