<?php
namespace Gino;
/**
* @file admin_table_form.php
* @brief Template della pagina di inserimento/modifica record in area amministrativa
*
* Variabili disponibili:
* - **title**: string, titolo
* - **form_description**: html, testo spiegazioni
* - **form** html, il form
* @copyright 2013-2016 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
* @authors Marco Guidotti guidottim@gmail.com
* @authors abidibo abidibo@gmail.com
*/
?>
<? //@cond no-doxygen ?>
<section>
<?php if($title): ?>
    <h1><?= $title ?></h1>
<?php endif ?>

<?php if($form_description): ?>
    <div class="backoffice-info"><?= $form_description ?></div>
<?php endif ?>
<?= $form ?>
</section>
<? // @endcond ?>
