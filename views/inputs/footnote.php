<?php
namespace Gino;
/**
 * @file footnote.php
 * @brief Vista che stampa le note di un input form
 *
 * Variabili disponibili:
 * - **collapse_id**: string. Riferimento dell'accordion.
 * - **button_label**: string
 * - **note**: string. Note
 *
 * @copyright 2019 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */
?>
<? //@cond no-doxygen ?>
<div class="footnote">
	<a class="btn btn-outline-secondary btn-sm" data-toggle="collapse" href="#<?= $collapse_id ?>" role="button" 
aria-expanded="false" aria-controls="<?= $collapse_id ?>">
	<?= $button_label ?>
	</a>
	<div class="collapse annotations" id="<?= $collapse_id; ?>">
    		<div class="card card-body"><?= $note ?></div>
	</div>
</div>
<? // @endcond ?>
