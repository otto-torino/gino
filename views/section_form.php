<?php
namespace Gino;
/**
* @file section_form.php
* @brief Template del contenitore di un form
*
* Variabili disponibili:
* - **title**: string, titolo
* - **form_description**: html, testo spiegazioni
* - **form** html, il form
* @copyright 2016 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
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
    <div><?= $form_description ?></div>
<?php endif ?>
<?= $form ?>
</section>
<? // @endcond ?>
