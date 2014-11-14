<?php

namespace Gino;

/**
 * @file class.Logger.php
 * @brief Contiene la definizione ed implementazione della classe Logger
 * 
 * @copyright 2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

/**
 * @brief Classe per la notifica di log di sistema
 * @description Gestisce un logger per errori e warning. Se la costante DEBUG è settata a TRUE stampa a video errori e warnings.
 *              Se DEBUG è impostata a FALSE invia una mai agli amministratori di sistema.
 * 
 * @copyright 2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class Logger {

    /**
     * @brief Invia un messaggio agli amministratori del sistema se DEBUG è FALSE
     * @param string $subject titolo messaggio da inviare
     * @param string $message corpo messaggio da inviare
     * @return vero se la mail è stata correttamente spedita, falso altrimenti
     */
    public static function messageReportAdmins($subject, $message) {

        if(DEBUG) return FALSE;
        $admins = unserialize(ADMINS);
        if(!is_array($admins) or !count($admins)) return FALSE;

        $registry = registry::instance();

        $subject = $registry->sysconf->head_title.' - '.$subject;
        $object = "<h1>".$subject."</h1>";
        $object .= $message;
        $object .= self::systemVariablesHtml();

        $headers = "From: " . $registry->sysconf->email_from_app . "\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

        return mail(implode(',', $admins), $subject, $object, $headers);

    }

    /**
     * @brief Invia lo stack trace di una exception agli amministratori del sistema se DEBUG è FALSE
     * @param \Exception $exception oggetto Exception
     * @return vero se la mail è stata correttamente spedita, falso altrimenti
     */
    public static function exceptionReportAdmins($exception) {

        if(DEBUG) return FALSE;

        $admins = unserialize(ADMINS);
        if(!is_array($admins) or !count($admins)) return FALSE;

        $registry = registry::instance();

        $subject = $registry->sysconf->head_title.' - '.$exception->getMessage();
        $object = self::stackTraceHtml($exception);
        $headers = "From: " . $registry->sysconf->email_from_app . "\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

        return mail(implode(',', $admins), $subject, $object, $headers);

    }

    /**
     * @brief Html che mostra il valore di variabili di sistema SESSION, SERVER, REQUEST
     * @return html
     */
    private static function systemVariablesHtml() {
        $view = new View(null, 'logger_system_variables');
        return $view->render(array());
    }

    /**
     * @brief Gestore di eccezioni
     * @description in DEBUG attivo stampa a video il trace, in produzione invia una mail con il trace agli ADMINS
     * @param Exception $exception oggetto Exception
     * return void
     */
    public static function manageException($exception) {
        if(DEBUG) {
            echo self::stackTraceHtml($exception);
            exit;
        }
        else {
            self::exceptionReportAdmins($exception);
            Error::raise500();
        }
    }

    /**
     * @brief Html che mostra lo stack trace di una Exception
     * @param Exception $exception oggetto Exception
     * @return documento html con stack trace
     */
    private static function stackTraceHtml($exception) {
        $view = new View(null, 'logger_stack_trace');
        $dict = array(
            'exception' => $exception,
            'registry' => registry::instance(),
            'system_variables_html' => self::systemVariablesHtml()
        );
        return $view->render($dict);

    }
}
