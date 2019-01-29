<?php
namespace Gino;
/**
* @file input_textarea_inline.php
* @brief Vista del tag textarea
*
* Variabili disponibili:
* - **additional_class**: string
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
<label class="<?php if($label_class): ?><?= $label_class ?><?php endif ?>" for="<?= $label_for ?>">
	<?= $label_string ?>
</label>
<?= $input ?>
<? // @endcond ?>
