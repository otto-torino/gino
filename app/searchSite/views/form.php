<?php
namespace Gino\App\SearchSite;
/**
 * @file form.php
 * @brief Template form di ricerca nel sito
 *
 * Le variabili a disposizione sono:
 * - **form_action**: l'url dell'attributo action del form per accedere ai risultati della ricerca
 * - **choices**: bool, dice se sono possibili delle scelte di ricerca
 * - **check_options**: se $choices è vero mostra il pannello con le scelte di ricerca
 * 
 * @version 1.0.0
 * @copyright 2005-2020 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 * 
 * Nota bene!
 * La vista carica un javascript che gestisce la comparsa/scomparsa del pannello con le opzioni
 * di ricerca, per funzionare gli id del bottone di check deve essere 'search_site_check'!
 * E naturalmente deve essere stampata la variabile $check_options.
 */
?>
<? //@cond no-doxygen ?>
<script type="application/javascript">
function openHideSearchSite() {
	var d = $("#fulltextsearch").css('display');
	if(d == 'block') {
		$('#fulltextsearch').css('display', 'none');
	}
	else {
		$('#fulltextsearch').css('display', 'block');
	}
}
</script>

<div><span style="cursor: pointer;" onclick="openHideSearchSite()" class="fa fa-search"></span></div>

<div id="fulltextsearch" style="display: none;">
	<form method="post" class="form-inline searchsite-form" action="<?= $form_action ?>" role="search">
    	<?php if($choices): ?>
        <div class='form-group'>
            <input type="button" id="search_site_check" value="" />
        </div>
    	<?php endif ?>
    	<div class='form-group mr-1'>
        	<input type="text" class="form-control" name="search_site" placeholder="<?= _('Cerca')?>" />
    	</div>
    	
		<div class='form-group'>
        	<button type="submit" class="btn btn-default">
				<span class="fa fa-search"></span>
			</button>
    	</div>
    	<?php if($choices): ?>
        	<?= $check_options ?>
    	<?php endif ?>
	</form>
</div>
<? // @endcond ?>
