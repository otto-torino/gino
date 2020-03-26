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
* - **search_form**: html, form di ricerca
* - **open_form**: bool, TRUE se il form deve essere mostrato espanso perché compilato
* - **pagination**: html, paginazione
*
* @version 1.0.0
* @copyright 2017 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
* @author Marco Guidotti guidottim@gmail.com
* @author abidibo abidibo@gmail.com
*/
?>
<? //@cond no-doxygen ?>
<section id="archive-calendar-<?= $instance_name ?>">
    <h1><?= _('Archivio appuntamenti') ?> <span class="fa fa-search link" style="margin-right: 10px;" onclick="if($('events_form_search').style.display == 'block') $('events_form_search').style.display = 'none'; else $('events_form_search').style.display = 'block';"></span></h1>
    <div id="events_form_search" style="display: <?= $open_form ? 'block' : 'none'; ?>;">
        <?= $search_form ?>
    </div>
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
