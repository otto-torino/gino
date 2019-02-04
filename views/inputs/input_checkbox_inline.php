<?php
namespace Gino;
/**
* @file input_checkbox_inline.php
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
<?php if(count($checkboxes)): ?>
	<?php foreach($checkboxes as $checkbox): ?>
	<div class="form-check <?php if($inline): ?>form-check-inline<?php endif ?>">
		<?= $checkbox['input'] ?>
		<?= $checkbox['label'] ?>
	</div>
	<?php endforeach; ?>
<?php endif ?>
<? // @endcond ?>
