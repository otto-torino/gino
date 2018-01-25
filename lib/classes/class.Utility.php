<?php
/**
 * @file class.Utility.php
 * @brief Contiene la definizione ed implementazione della classe Gino.Utility
 * 
 * @copyright 2018 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

namespace Gino;

/**
 * @brief Libreria di utility
 * 
 * @copyright 2018 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class Utility {

    /**
     * @brief Costruttore
     *
     * @param array $opts array associativo di opzioni
     * @return void
     */
    function __construct($opts = []) {
        
    }

    /**
     * @brief Send a test email
     * 
     * @see Gino.Plugin.plugin_phpmailer::sendMail
     * @param string to_email_address
     * @param string from_email_address
     * @param array $options
     *   - @b use_mail (boolean): indica se utilizzare la funzione mail() di PHP per inviare una email (default @a true)
     *   - @b ishtml (boolean): dichiara che è una email html (default @a false)
     *   - @b noreply_email_address (string)
     *   - @b noreply_email_name (string)
     * @return string
     */
    public function sendTestEmail($to_email_address, $from_email_address, $options=[]) {
        
        $use_mail = \Gino\gOpt('use_mail', $options, true);
        $ishtml = \Gino\gOpt('ishtml', $options, false);
        $noreply_email_address = \Gino\gOpt('noreply_email_address', $options, null);
        $noreply_email_name = \Gino\gOpt('noreply_email_name', $options, null);
        
        if(!$to_email_address or !$from_email_address) {
            throw new \Exception(_("Impostare l'indirizzo di invio e di destinazione"));
        }
        
        require_once PLUGIN_DIR.OS.'plugin.phpmailer.php';
        
        $string = _("Invio email di prova.");
        $string .= "\n\r";
        $string .= _("L'email è stata inviata il giorno").": ".date("Y-m-d H:i:s");
        
        $mailer = new \Gino\Plugin\plugin_phpmailer();
        $send = $mailer->sendMail(
            $to_email_address,
            $from_email_address,
            _("Test email"),
            $string,
            [
                'use_mail' => $use_mail,
                'debug' => 2,
                'reply_email' => $noreply_email_address,
                'reply_name' => $noreply_email_name,
                'ishtml' => $ishtml,
            ]
            );
        
        if($send) {
            $message = _("email inviata");
        }
        else {
            $message = _("email non inviata");
        }
        
        return $message;
    }
}
