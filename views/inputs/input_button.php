<?php
namespace Gino;
/**
* @file input_button.php
* @brief Vista di un button
*
* Variabili disponibili:
* - **classes**: string
* - **name**: string
* - **value**: string
* - **type**: string (@a submit | @a button)
* - **id**: string
* - **onclick**: string
* 
* @copyright 2018-2019 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
* @authors Marco Guidotti guidottim@gmail.com
* @authors abidibo abidibo@gmail.com
*/
?>
<? //@cond no-doxygen ?>
<button type="<?= $type ?>" name="<?= $name ?>" class="btn btn-primary <?php if($classes): ?><?= $classes ?><?php endif ?>" 
<?php if($id): ?>id="<?= $id ?>"<?php endif ?> 
<?php if($onclick): ?>onclick="<?= $onclick ?>"<?php endif ?>>
	<?= $value ?>
</button>
<? // @endcond ?>
