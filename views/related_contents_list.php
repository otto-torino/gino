<ul class="related_contents">
    <? foreach($related_contents as $content_type => $links): ?>
        <li>
            <?= $content_type ?>
            <ul>
                <? $i = 0; ?>
                <? foreach($links as $link): ?>
                    <? if($i < 3): ?>
                        <li><?= $link ?></li>
                        <? $i++ ?>
                    <? endif ?>
                <? endforeach ?>
            </ul>
        </li>
    <? endforeach ?>
</ul>
