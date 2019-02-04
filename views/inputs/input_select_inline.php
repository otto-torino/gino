<?php
namespace Gino;
/**
* @file input_select_input.php
* @brief Vista del tag input di tipo select
*
* Variabili disponibili:
* - **additional_class**: string
* - **label_for**: string
* - **label_string**: string
* - **label_class**: string
* - **input**: string
* - **text_add**: string
* - **add_related**: string
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
	<?php if($add_related): ?><?= $add_related ?><?php endif ?>
</label>
<?= $input ?>
<? // @endcond ?>
