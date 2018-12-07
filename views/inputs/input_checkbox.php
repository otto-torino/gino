<?php
namespace Gino;
/**
* @file input_checkbox.php
* @brief Vista del tag input checkbox
*
* Variabili disponibili:
* - **additional_class**: string
* - **legend_string**: string
* - **legend_class**: string
* - **checkboxes**: string
* - **inline**: boolean
* - **text_add**: string
* 
* @copyright 2018 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
* @authors Marco Guidotti guidottim@gmail.com
* @authors abidibo abidibo@gmail.com
*/
?>
<? //@cond no-doxygen ?>

<div class="form-group row <?php if($additional_class): ?><?= $additional_class ?><?php endif ?>">
	<div class="col-sm-2 <?php if($legend_class): ?><?= $legend_class ?><?php endif ?>"><?= $legend_string ?></div>
	
	<div class="col-sm-10">
		<?php if(count($checkboxes)): ?>
			<?php foreach($checkboxes as $checkbox): ?>
				<div class="form-check <?php if($inline): ?>form-check-inline<?php endif ?>">
					<?= $checkbox['input'] ?>
					<?= $checkbox['label'] ?>
				</div>
			<?php endforeach; ?>
		<?php endif ?>
		
		<?php if($text_add): ?>
			<div class="form-textadd"><?= $text_add ?></div>
		<?php endif ?>
		
	</div>
</div>
<? // @endcond ?>
