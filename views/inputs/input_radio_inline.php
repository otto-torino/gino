<?php
namespace Gino;
/**
* @file input_radio_inline.php
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
* @copyright 2018 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
* @authors Marco Guidotti guidottim@gmail.com
* @authors abidibo abidibo@gmail.com
*/
?>
<? //@cond no-doxygen ?>
<?php if(count($radios)): ?>
	<?php foreach($radios as $radio): ?>
		<!-- disabled -->
		<div class="form-check <?php if($inline): ?>form-check-inline<?php endif ?>">
			<?= $radio['input'] ?>
			<?= $radio['label'] ?>
		</div>
	<?php endforeach; ?>
<?php endif ?>
<? // @endcond ?>
