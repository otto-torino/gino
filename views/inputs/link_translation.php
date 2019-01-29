<?php
namespace Gino;
/**
* @file link_translation.php
* @brief Vista del link della traduzione di un input
*
* Variabili disponibili:
* - **classes**: string
* - **onclick**: string
* - **label**: string
* 
* @copyright 2018 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
* @authors Marco Guidotti guidottim@gmail.com
* @authors abidibo abidibo@gmail.com
*/
?>
<? //@cond no-doxygen ?>
<span class="btn btn-outline-primary <?php if($classes): ?><?= $classes ?><?php endif ?>" onclick="<?= $onclick ?>">
	<?= $label ?>
</span>
<? // @endcond ?>
