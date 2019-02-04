<?php
namespace Gino;
/**
* @file input_radio.php
* @brief Vista del tag input radio
*
* Variabili disponibili:
* - **additional_class**: string
* - **legend_string**: string
* - **legend_class**: string
* - **radios**: string
* - **inline**: boolean
* - **text_add**: string
* 
* @copyright 2018-2019 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
* @authors Marco Guidotti guidottim@gmail.com
* @authors abidibo abidibo@gmail.com
*/
?>
<? //@cond no-doxygen ?>
<fieldset class="form-group">
	<div class="row <?php if($additional_class): ?><?= $additional_class ?><?php endif ?>">
		<legend class="col-sm-2 col-form-label <?php if($legend_class): ?><?= $legend_class ?><?php endif ?>">
    		<?= $legend_string ?>
   		</legend>
		
		<div class="col-sm-10">
			<?php if(count($radios)): ?>
				<?php foreach($radios as $radio): ?>
					<div class="form-check <?php if($inline): ?>form-check-inline<?php endif ?>">
						<?= $radio['input'] ?>
						<?= $radio['label'] ?>
					</div>
				<?php endforeach; ?>
			<?php endif ?>
			
			<?php if($text_add): ?>
				<div class="form-textadd"><?= $text_add ?></div>
			<?php endif ?>
			
		</div>
	</div>
</fieldset>
<? // @endcond ?>
