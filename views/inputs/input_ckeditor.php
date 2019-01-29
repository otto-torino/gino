<?php
namespace Gino;
/**
* @file input_textarea.php
* @brief Vista del tag textarea con CKeditor
*
* Variabili disponibili:
* - **text_note**: string
* - **img_previewer**: string
* - **label_for**: string
* - **label_string**: string
* - **label_class**: string
* - **input**: string
* - **trnsl_input**: string
* - **text_add**: string
* 
* @copyright 2018 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
* @authors Marco Guidotti guidottim@gmail.com
* @authors abidibo abidibo@gmail.com
*/
?>
<? //@cond no-doxygen ?>
<div class="form-group row">
	<div class="form-ckeditor">
		<?php if($label_string or $label_for): ?>
			<label for="<?= $label_for ?>" class="col-sm-2 col-form-label <?php if($label_class): ?><?= $label_class ?><?php endif ?>">
    			<?= $label_string ?>
    		</label>
		<?php endif ?>
		
		<?php if($text_note): ?>
			<div><?= $text_note ?></div>
		<?php endif ?>
		<?= $img_previewer ?>
		
		<div class="col-sm-10">
			<?= $input ?>
			<?= $trnsl_input ?>
			<?php if($text_add): ?>
				<div class="form-textadd"><?= $text_add ?></div>
			<?php endif ?>
		</div>
	</div>
</div>
<? // @endcond ?>
