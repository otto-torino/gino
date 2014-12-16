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
    <!-- google analytics --> 
    <?= \Gino\Javascript::analytics() ?>
  </head>
  <body>
    <!-- top bar -->
    <nav class="navbar-wrapper navbar-default navbar-fixed-top" role="navigation">
      <div class="container">
        <h1 class="hidden">Menu</h1>
        <!-- Brand and toggle get grouped for better mobile display -->
        <div class="navbar-header">
          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="menu-main-container">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="#"><img src="img/logo.png" style="width: 105px; height: 50px;" /></a>
        </div>
        <!-- Collect the nav links, forms, and other content for toggling -->
        <div class="collapse navbar-collapse" id="menu-main-container">
          {module classid=4 func=render}
        </div><!-- /.navbar-collapse -->
      </div>
    </nav>
    <div class="container bg-white">
      {module id=0}
    </div>
  </body>
</html>
