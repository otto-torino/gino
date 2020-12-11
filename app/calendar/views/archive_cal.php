<?php
namespace Gino\App\Calendar;
/**
* @file archive.php
* @brief Template per la vista archivio appuntamenti
*
* Variabili disponibili:
* - **instance_name**: string, nome istanza modulo
* - **controller**: object Gino.App.Calendar.calendar
* - **items**: array, eventi @ref Gino.App.Calendar.Item
* - **ctg**: mixed, categoria @ref Gino.App.Calendar.Category o null
* - **pagination**: html, paginazione
* - **search_form**: html, form di ricerca
* - **link_form**: html
*/
?>
<? //@cond no-doxygen ?>
<section id="archive-calendar-<?= $instance_name ?>">
    <h1><?= _('Archivio appuntamenti') ?> <?= $link_form ?></h1>
    <?= $search_form ?>
    
    <? if(count($items)): ?>
        <table class='table table-striped table-hover'>
            <tr>
                <th><?= _('Data') ?></th>
                <th><?= _('Sala') ?></th>
                <th><?= _('Incontro') ?></th>
            </tr>
            <? foreach($items as $item): ?>
            <tr>
                <td><?= \Gino\dbDateToDate($item->date, '/') ?></td>
                <td><?= $item->getPlaceValue() ?></td>
                <td><a href="<?= $item->getUrl() ?>"><?= \Gino\htmlChars($item->ml('name')) ?></a></td>
            </tr>
            <? endforeach ?>
        </table>
        <?= $pagination ?>
    <? else: ?>
        <p><?= _('Non risultano appuntamenti') ?></p>
    <? endif ?>
</section>
<? // @endcond ?>
