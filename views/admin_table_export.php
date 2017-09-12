<?php
namespace Gino;
/**
 * @file admin_table_export.php
 * @brief Template interfaccia esportazione record in area amministrativa
 *
 * Variabili disponibili:
 * - **form_action**: string
 * - **hidden**: string
 * - **checkbox**: string
 *
 * @copyright 2017 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */
?>
<? //@cond no-doxygen ?>

<section class="admin">
	<form name="export_data" method="post" action="<?= $form_action ?>">
		<?= $hidden ?>
		<p><?= _("Seleziona i campi che vuoi esportare. Verranno esportati soltanto i record che rispondono alle condizioni impostate nella ricerca.") ?></p>
		<p><?= $checkbox ?></p>
		<input type="button" value="<?= _("seleziona tutti") ?>" onclick="SetAllCheckBoxes('export_data', 'fields[]', true)" />
		<input type="button" value="<?= _("deseleziona tutti") ?>" onclick="SetAllCheckBoxes('export_data', 'fields[]', false)" />
		
		<input type="submit" name="submit" value=<?= _("esporta") ?> />
	</form>
</section>
<? // @endcond ?>
