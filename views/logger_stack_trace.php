<! DOCTYPE html>
<html>
    <head>
        <title><?= $registry->sysconf->head_title ?></title>
        <!-- system css -->
        <?=  $registry->variables('css') ?>
    </head>
    <body>
        <h1><?= _('Exception:').' '.$exception->getMessage() ?></h1>
        <p><b><?= _('File') ?></b>: <?= $exception->getFile() ?></p>
        <p><b><?= _('Linea') ?></b>: <?= $exception->getLine() ?></p>
        <h2>Stack Trace</h2>
        <table border="1" class="table table-striped table-hover table-bordered" style="text-align:left; border-collapse:collapse;">
            <tr style="vertical-align: top;">
                <th><?= _('file') ?></th>
                <th><?= _('linea') ?></th>
                <th><?= _('funzione') ?></th>
                <th><?= _('classe') ?></th>
                <th><?= _('argomenti') ?></th>
            </tr>
            <? foreach($exception->getTrace() as $trace): ?>
            <tr style="vertical-align: top;">
                <td><?= isset($trace['file']) ? $trace['file'] : '' ?></td>
                <td><?= isset($trace['line']) ? $trace['line'] : '' ?></td>
                <td><?= isset($trace['function']) ? $trace['function'] : '' ?></td>
                <td><?= isset($trace['class']) ? $trace['class'] : '' ?></td>
                <td>
                    <? if(isset($trace['args'])): ?>
                        <?php
                            ob_start();
                            var_dump($trace['args']);
                            $result = ob_get_clean();
                            echo $result;
                        ?>
                    <? endif ?>
                </td>
            </tr>
            <? endforeach ?>
        </table>
        <? if(isset($system_variables_html)): ?>
            <?= $system_variables_html ?>
        <? endif ?>
    </body>
</html>
