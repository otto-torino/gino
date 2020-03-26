<?php
namespace Gino\App\Calendar;
/**
* @file showlist.php
* @brief Template per la vista elenco appuntamenti
*
* Variabili disponibili:
* - **items**: array, eventi @ref Gino.App.Calendar.Item
* - **modal**: boolean, visualizzazione del dettaglio in una modale
* - **month**: string, mese visualizzato
*
* @version 1.0.0
* @copyright 2017 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
* @author Marco Guidotti guidottim@gmail.com
* @author abidibo abidibo@gmail.com
*/
?>
<? //@cond no-doxygen ?>
<? if(count($items)): ?>
	<h2><?= sprintf(_("Appuntamenti di %s"), $month) ?></h2>
	<div class="table-responsive-vertical">
		<table class="table table-striped table-hover">
			<thead>
				<tr>
					<th><?= _('Data') ?></th>
					<th><?= _('Evento') ?></th>
					<th><?= _('Orario') ?></th>
					<th><?= _('Sala') ?></th>
				</tr>
			</thead>
			<tbody>
			<? foreach($items as $item): ?>
				<tr>
					<td data-title="<?= _('Data') ?>"><?= \Gino\dbDateToDate($item->date, '/') ?></td>
					<td data-title="<?= _('Evento') ?>">
					<? if($modal): ?>
						<?= $item->getModalUrl() ?><?= $item->ml('name') ?></a>
					<? else: ?>
						<a href="<?= $item->getUrl() ?>"><?= \Gino\htmlChars($item->ml('name')) ?></a>
					<? endif ?>
					</td>
					<td data-title="<?= _('Orario') ?>"><?= \Gino\dbTimeToTime($item->time_start) ?> - <?= \Gino\dbTimeToTime($item->time_end) ?></td>
					<td data-title="<?= _('Sala') ?>"><?= \Gino\htmlChars($item->getPlaceValue()) ?></td>
				</tr>
			<? endforeach ?>
		</tbody>
	</table>
</div>
<? endif ?>
<? // @endcond ?>
