<?php
namespace Gino\App\SearchSite;
/**
 * @file results.php
 * @brief Template risultati ricerca nel sito
 *
 * Le variabili a disposizione sono:
 * - **title**: bool, titolo
 * - **results_num**: int, numero risultati
 * - **content**: html, risultati
 */
?>
<? //@cond no-doxygen ?>
<section class="search-results">
    <header>
        <h1 class="left"><?= $title ?></h1>
        <div class="right">
        <?php if($results_num): ?>
        	<?= $results_num ?>
    	<?php endif ?>
        </div>
        <div class="clear"></div>
    </header>
	<?= $content ?>
</section>
<? // @endcond ?>
