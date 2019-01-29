<?php
namespace Gino;
/**
* @file input_mcheckbox.php
* @brief Vista del tag input multiple checkbox
*
* Variabili disponibili:
* - **additional_class**: string
* - **legend_string**: string
* - **legend_class**: string
* - **checkboxes**: string
* - **add_related**: string
* - **helper**: string
* 
* @copyright 2018 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
* @authors Marco Guidotti guidottim@gmail.com
* @authors abidibo abidibo@gmail.com
*/
?>
<? //@cond no-doxygen ?>

<div class="form-group row <?php if($additional_class): ?><?= $additional_class ?><?php endif ?>">
	<div class="col-sm-2 <?php if($legend_class): ?><?= $legend_class ?><?php endif ?>">
		<?= $legend_string ?> 
		<?php if($helper): ?><?= $helper ?><?php endif ?> 
		<?php if($add_related): ?><?= $add_related ?><?php endif ?>
	</div>
	
	<div class="col-sm-10">
		<?= $checkboxes ?>
	</div>
</div>
<? // @endcond ?>
