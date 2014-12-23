<?php
/**
* @file 403.php
* @brief Template della pagina 403, Forbidden
*
* Variabili disponibili:
* - **title**: string, titolo
* - **message**: string, messaggio
* @see Gino.Exception.Exception403
* @copyright 2014 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
* @authors Marco Guidotti guidottim@gmail.com
* @authors abidibo abidibo@gmail.com
*/
?>
<? namespace Gino; ?>
<? //@cond no-doxygen ?>
<section>
<h1><?= $title ?></h1>
<p><?= $message ?></p>
</section>
<? // @endcond ?>
