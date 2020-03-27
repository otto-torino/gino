<?php
namespace Gino;
/**
* @file modal.php
* @brief Vista della modale
*
* Variabili disponibili:
* - **modal_id**: string
* - **modal_title_id**: string
* - **vertically_centered**: boolean
* - **size_modal**: string
* - **title**: string
* - **body**: string
* - **close_button**: boolean
* - **save_button**: boolean
* 
* @copyright 2018-2019 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
* @authors Marco Guidotti guidottim@gmail.com
* @authors abidibo abidibo@gmail.com
*/
?>
<? //@cond no-doxygen ?>
<div class="modal fade" 
id="<?= $modal_id ?>" tabindex="-1" role="dialog"
aria-labelledby="<?= $modal_title_id ?>" aria-hidden="true">
	<div class="modal-dialog
	<?php if($vertically_centered): ?> modal-dialog-centered<?php endif ?>
	<?php if($size_modal): ?> <?= $size_modal ?><?php endif ?>" role="document">
		<!-- Modal content-->
		<div class="modal-content">
			<div class="modal-header">
				<h5 id="modalTitle" class="modal-title" id="<?= $modal_title_id ?>"><?= $title ?></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span>
				</button>
			</div>
            <!-- Body -->
			<div id="modalBody" class="modal-body">
				<?= $body ?>
			</div>
            <!-- Footer -->
			<div class="modal-footer">
			<?php if($close_button): ?>
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			<?php endif ?>
			<?php if($save_button): ?>
				<button type="button" class="btn btn-primary">Save changes</button>
			<?php endif ?>
			</div>
		</div>
	</div>
</div>

<? // @endcond ?>
