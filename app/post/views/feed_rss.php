<?php
namespace Gino\App\Post;
/**
* @file feed_rss.php
* @brief Template feed RSS
*
* Variabili disponibili:
* - **title**: string, titolo feed
* - **description**: string, descrizione feed
* - **request**: \Gino\Http\Request, istanza di Gino.Http.Request
* - **items**: array, array di oggetti Gino.App.Post.Item
*/
?>
<? //@cond no-doxygen ?>
<?= '<?xml version="1.0" encoding="utf-8"?>' ?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
    <channel>
        <atom:link href="<?= $request->absolute_url ?>" rel="self" type="application/rss+xml" />
        <title><?= $title ?></title>
        <link><?= $request->root_absolute_url ?></link>
        <description><?= $description ?></description>
        <language><?= $request->session->lng ?></language>
        <docs>http://blogs.law.harvard.edu/tech/rss</docs>
        <?php if(count($items) > 0): ?>
        <?php foreach($items as $n): ?>
            <?php $id = \Gino\htmlChars($n->id); ?>
            <?php $title = \Gino\htmlChars($n->ml('title')); ?>
            <?php $text = \Gino\htmlChars($n->ml('text')); ?>
            <?php $text = str_replace("src=\"", "src=\"".$request->root_absolute_url, $text); ?>
            <?php $date = new \DateTime($n->date); $pubdate = $date->format(\DateTime::RSS); ?>
            <item>
            	<pubDate><?= $pubdate ?></pubDate>	<!-- Sat, 07 Sep 2002 0:00:01 GMT -->
                <title><?= $title ?></title>
                <link><?= $request->root_absolute_url . $n->getUrl() ?></link>
                <description>
                <![CDATA[
                <?= $text ?>
                ]]>
                </description>
                <guid><?= $request->root_absolute_url . $n->getUrl() ?></guid>
            </item>
        <?php endforeach ?>
        <?php endif ?>
    </channel>
</rss>
<? // @endcond ?>
