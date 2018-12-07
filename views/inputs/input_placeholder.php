<?php
namespace Gino;
/**
* @file input_placeholder.php
* @brief Vista del contenitore delle righe di input
* 
* Variabili disponibili:
* - **additional_class**: string
* - **label_string**: string
* - **label_class**: string
* - **value**: string
* 
* @copyright 2018 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
* @authors Marco Guidotti guidottim@gmail.com
* @authors abidibo abidibo@gmail.com
*/
?>
<? //@cond no-doxygen ?>
<div class="form-group row <?php if($additional_class): ?><?= $additional_class ?><?php endif ?>">
	<label class="col-sm-2 col-form-label <?php if($label_class): ?><?= $label_class ?><?php endif ?>">
    	<?= $label_string ?>
    </label>
	
	<div class="col-sm-10 form-noinput">
		<?= $value ?>
	</div>
</div>
<? // @endcond ?>
