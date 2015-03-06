<?php
/**
 * @file configuration.php
 * @brief File di configurazione
 * 
 * Contiene i parametri dell'applicazione
 * 
 * @copyright 2005-2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

/**
 * @brief Opzione Debug
 * @description Quando il debug è attivo i file statici non vengono uniti e minificati e non vengono
 *              inviate email all'amministratore in caso di errore di sistema. Settare a FALSE in produzione
 *              per ricevere notifiche di errori e comprimere i file statici.
 */
define("DEBUG", TRUE);

/**
 * @brief Amministratori sistema
 * @description Ricevono notifiche di errori di sistema. Il valore deve essere necessariamente un array serializzato
 */
define("ADMINS", serialize(array('marco.guidotti@otto.to.it', 'stefano.contini@otto.to.it')));

// Database

/**
 * Tipo di database
 * @see db::instance()
 */
define("DBMS", 'mysql');

/**
 * Nome del server database
 */
define("DB_HOST", "localhost");

/**
 * Numero della porta di connesione del server database
 */
define("DB_PORT", "3306");

/**
 * Nome del database
 */
define("DB_DBNAME", "dbgino");

/**
 * Nome dell'utente del database
 * @var string
 */
define("DB_USER", "root");

/**
 * Password dell'utente del database
 */
define("DB_PASSWORD", "");

/**
 * Schema del database
 */
define("DB_SCHEMA", "");

/**
 * Codifica del database
 */
define("DB_CHARSET", 'utf-8');

// Structure

/**
 * Nome della sessione
 */
define('SESSION_NAME', 'GINO_SESSID');

// Other

/**
 * Dimensione massima dei file per l'upload (2Mb => 2*1024*1024)
 */
define('MAX_FILE_SIZE', 5242880);

?>
