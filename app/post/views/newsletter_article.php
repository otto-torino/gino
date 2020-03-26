<?php
namespace Gino\App\Post;
/**
* @file newsletter.php
* @brief Template per la visualizzazione dei post all'interno di newsletter
*
* Variabili disponibili:
* - **item**: \Gino\App\News\Article, istanza di @ref Gino.App.Post.Item
*/
?>
<? //@cond no-doxygen ?>
<section>
    <h1><?= \Gino\htmlChars($item->ml('title')) ?></h1>
    <?= \Gino\htmlChars($item->ml('text')) ?>
 </section>
<? // @endcond ?>
