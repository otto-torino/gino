<?php
namespace Gino\App\Calendar;
/**
* @file detail.php
* @brief Template per la vista dettaglio evento
*
* Variabili disponibili:
* - **instance_name**: string, nome istanza modulo
* - **item**: \Gino\App\Events\Event istanza evento Gino.App.Calendar.Item
* - **breadcrumbs**: string
*
* @version 1.0.0
* @copyright 2017 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
* @author Marco Guidotti guidottim@gmail.com
* @author abidibo abidibo@gmail.com
*/
?>
<? //@cond no-doxygen ?>
<!-- Breadcrumbs -->
<? if($breadcrumbs): ?>
	<?= $breadcrumbs ?>
<? endif ?>

<section id="detail-calendar-<?= $instance_name ?>" itemscope itemtype="http://schema.org/Event">
	<h1 itemprop="name"><?= \Gino\htmlChars($item->ml('name')) ?></h1>
	<p class="events-time">
		<time itemprop="startDate" content="<?= $item->startDateIso() ?>"><?= \Gino\htmlChars($item->beginLetterDate()); ?></time>
		<? if($item->duration > 1): ?>
		 - <time itemprop="endDate" content="<?= $item->endDateIso() ?>"><?= \Gino\htmlChars($item->endLetterDate()); ?></time>
        <? endif ?>
	</p>
	<div><?= _('Orario') ?>: <?= \Gino\dbTimeToTime($item->time_start) ?> - <?= \Gino\dbTimeToTime($item->time_end) ?></div>
	<div itemprop="location"><?= \Gino\htmlChars($item->getPlaceValue()) ?></div>
	
	<div itemprop="description"><?= \Gino\htmlChars($item->ml('description')) ?></div>
</section>
<? // @endcond ?>
