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
<div class="form-group row <?php if($additional_class): ?><?= $additional_class ?><?php endif ?>">
	<?php if($label_string or $label_for): ?>
		<label for="<?= $label_for ?>" class="col-sm-2 col-form-label <?php if($label_class): ?><?= $label_class ?><?php endif ?>">
    		<?= $label_string ?> <?php if($helper): ?><?= $helper ?><?php endif ?>
    	</label>
	<?php endif ?>
	
	<div class="col-sm-10">
		<?= $input ?>
		
		<?php if($form_file_check): ?>
			<div class="form-file-check">
			<?php if($checkbox_delete): ?>
				<?= $checkbox_delete ?> <?= _("elimina") ?>
			<?php endif ?>
			<?= _("file caricato") ?>: <b><?= $link_file ?></b>
    		</div>
		<?php endif ?>
		
		<?php if($text_add): ?>
			<div class="form-textadd"><?= $text_add ?></div>
		<?php endif ?>
		
		<?= $input_hidden ?>
	</div>
</div>
<? // @endcond ?>
