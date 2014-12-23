<?php
/**
 * @file view/view.php
 * @ingroup page
 * @brief Template per la vista delle pagine
 *
 * Variabili disponibili:
 * - **section_id**: attributo id del tag section
 * - **page**: oggetto pageEntry
 * - **tpl**: template del post deciso da opzioni
 * - **enable_comments**: abilitazione commenti
 * - **form_comment**: form inserimento commento
 *
 * @copyright 2012-2014 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */
?>
<? namespace Gino\App\Page; ?>
<? //@cond no-doxygen ?>
<section id="<?= $section_id ?>">
    <?= $tpl ?>
    <? if($enable_comments): ?>
        <h2><?= _('Commenti') ?></h2>
        <p><a class="link" name="comments" onclick="javascript:$('page_form_comment').toggle();$('form_reply').value = '0';"><?= _('Inserisci un commento') ?></a></p>
        <div id="page_form_comment" style="display: none;"><?= $form_comment ?></div>
        <? if(count($comments)): ?>
            <dl class="comments_list">
            <? foreach($comments as $c): ?>
                <dt style="margin-left: <?= 20 * $c['recursion'] ?>px">
                    <?= sprintf("Pubblicato da %s il %s", $c['web'] ? "<a href=\"".$c['web']."\">".$c['author']."</a>" : $c['author'],  $c['datetime']) ?>
                    <? if($c['reply']): ?>
                     <?= sprintf(_('in risposta a %s'), $c['reply']) ?>
                    <? endif ?>
                     [<a href="<?= $url ?>#comments" onclick="$('page_form_comment').show();$('form_reply').value = '<?= $c['id'] ?>'" name="comment<?= $c['id'] ?>">rispondi</a>]
                </dt>
                <dd style="margin-left: <?= (20 * $c['recursion']) + 30 ?>px">
                    <img class="left" src="http://www.gravatar.com/avatar/<?= $c['avatar'] ?>?s=50&d=mm" />
                    <?= $c['text'] ?>
                    <div class="null"></div>
                </dd>
            <? endforeach ?>
            </dl>
        <? endif ?>
    <? endif ?>
</section>
<? // @endcond ?>
