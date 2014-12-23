<?php
/**
* @file logger_stack_trace.php
* @brief Template che stampa lo stack trace di una Exception.
* @description Quando la costante DEBUG in @ref configuration.php è settata a TRUE viene mostrato a video,
*              altrimenti viene inviato per mail agli amministratori, costante ADMIN.
*
* Variabili disponibili:
* - **registry**: \Gino\Registry, istanza di Gino.Registry
* - **exception**: \Exception, istanza dell'Exception
* - **system_variables_html**: html, dump delle variabili di sistema ($_SERVER, $_SESSION, $_REQUEST)
*
* @see Gino.Logger
* @copyright 2014 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
* @authors Marco Guidotti guidottim@gmail.com
* @authors abidibo abidibo@gmail.com
*/
?>
<? namespace Gino; ?>
<? //@cond no-doxygen ?>
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
<? // @endcond ?>
