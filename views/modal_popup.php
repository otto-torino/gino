<?php
namespace Gino;
/**
* @file modal_popup.php
* @brief Vista della modale in formato popup
* 
* Variabili disponibili:
* - **modal_id**: string
* - **title**: string
* - **body**: string
* 
* @copyright 2018 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
* @authors Marco Guidotti guidottim@gmail.com
* @authors abidibo abidibo@gmail.com
*/
?>
<? //@cond no-doxygen ?>
<div class="modal fade" id="<?= $modal_id ?>" role="dialog">
	<div class="modal-dialog">
	
        <!-- Modal content-->
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title"><?= $title ?></h4>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span>
				</button>
			</div>
            <!-- Body -->
			<div class="modal-body">
				<?= $body ?>
			</div>
            <!-- Footer -->
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>
<? // @endcond ?>
