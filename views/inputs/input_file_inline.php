<?php
namespace Gino;
/**
* @file input_file.php
* @brief Vista del tag input di tipo file
*
* Variabili disponibili:
* - **additional_class**: string
* - **label_for**: string
* - **label_string**: string
* - **label_class**: string
* - **input**: string
* - **form_file_check**: boolean
* - **checkbox_delete**: string
* - **link_file**: string
* - **input_hidden**: string
* - **text_add**: string
* - **helper**: string
* 
* @copyright 2018 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
* @authors Marco Guidotti guidottim@gmail.com
* @authors abidibo abidibo@gmail.com
*/
?>
<? //@cond no-doxygen ?>
<label class="<?php if($label_class): ?><?= $label_class ?><?php endif ?>" for="<?= $label_for ?>">
	<?= $label_string ?> 
	<?php if($helper): ?><?= $helper ?><?php endif ?>
</label>
<div class="input-group">
	<?= $input ?>

	<?php if($form_file_check): ?>
		<div class="form-file-check">
		<?php if($checkbox_delete): ?>
			<?= $checkbox_delete ?> <?= _("elimina") ?>
		<?php endif ?>
		<?= _("file caricato") ?>: <b><?= $link_file ?></b>
		</div>
	<?php endif ?>
	<?= $input_hidden ?>
</div>
<? // @endcond ?>
