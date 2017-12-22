<?php
/**
 * @file template-html-email.php
 * @brief Template di esempio per la costruzione di una email html
 * 
 * Sono disponibili le seguenti variabili:
 * - **title**: titolo
 * - **subtitle**: sottotitolo
 * - **items**: elementi da mostrare
 * - **image**: percorso dell'immagine (recuperata dalla rete)
 *
 * @version 1.0.0
 * @copyright 2015 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */
?>
<html>
	<head>
		<title></title>
		<style>
			body {
				background-color: #FFF;
				width:100% !important;
			}
			section#content {
				width: 400px;
				height: 600px;
				background-color: #F2F2F2;
				/*background: url('http://domain.com/images/image_bg.jpg');*/
				border: 1px solid black;
				margin: 0 auto;
				padding: 2px;
			}
			section#content header h1 {
				text-align: center;
			}
			article {
				padding: 10px 0px;
			}
		</style>
	</head>
	<body>
		<section id="content">
			<header>
				<h1 class="left"><?= $title ?></h1>
				<? if($subtitle): ?>
					<h2><?= $subtitle ?></h2>
				<? endif ?>
				<img src="cid:img1" />
     		</header>
     		<? if(count($items)): ?>
     			<? foreach($items as $item): ?>
     				<article>
     					<?= $item ?>
     				</article>
     			<? endforeach ?>
    		 <? endif ?>
    		<footer>
			<?php  if($image): ?>
 				<img src="<?= $image ?>" />
    		<? endif ?>
    		</footer>
		</section>
	</body>
</html>