<!DOCTYPE html>
<html lang="<?= LANG ?>">
  <head>
    <meta charset="utf-8" />
    <base href="<?= $registry->request->root_absolute_url ?>" />
    <title><?= $registry->title ?></title>
    <meta name="description" content="<?= $registry->description ?>" />
    <meta name="keywords" content="<?= $registry->keywords ?>" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <!-- other meta set from modules -->
    <?=  $registry->variables('meta') ?>
    <!-- other link tags set from modules -->
    <?=  $registry->variables('head_links') ?>
    <!-- system css -->
    <?=  $registry->variables('css') ?>
    <!-- system js -->
    <?=  $registry->variables('js') ?>
    <?= \Gino\Document::errorMessages() ?>
    <link rel="shortcut icon" href="<?= $this->_registry->favicon ?>" />
    <link href='http://fonts.googleapis.com/css?family=Roboto:300,900,700,300italic' rel='stylesheet' type='text/css' />
    <!-- Gino onload function -->
    <?= \Gino\Javascript::vendor() ?> 
    <?= \Gino\Javascript::onLoadFunction() ?>  
    <?= \Gino\Javascript::analytics() ?>  
  </head>
  <body>
    <div class="container">
      {module id=0}
	</div>
  </body>
</html>
