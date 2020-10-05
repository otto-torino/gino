<?php
/**
 * @file config.ldap.php
 * @brief File di configurazione per l'accesso a LDAP
 * 
 * Contiene le impostazioni per la ricerca degli utenti ldap e i parametri di connessione
 * 
 * @copyright 2013 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
 namespace Gino\App\Auth;

/**
 * Indirizzo del server
 * 
 * @var string (@example ldap://server.ldap.local)
 */
define('LDAP_HOST', '');

/**
 * Numero della porta di connessione (ldap 389, ldaps 636)
 * 
 * @var integer
 */
define('LDAP_PORT', '389');

/**
 * Parametri di connessione al server
 * 
 * @var string
 */
define('LDAP_BASE_DN', '');

/**
 * Parametri di ricerca
 * 
 * @var string
 */
define('LDAP_SEARCH_DN', '');

/**
 * Username dell'applicazione
 * 
 * @var string
 */
define('LDAP_APP_USERNAME', '');

/**
 * Password dell'applicazione
 * 
 * @var string
 */
define('LDAP_APP_PASSWORD', '');

/**
 * Nome del dominio (per la costruzione degli indirizzi email, account+dominio)
 * 
 * @var string (es.: \@example.it)
 */
define('LDAP_DOMAIN', '');

/**
 * Numero della versione del protocollo
 * 
 * @var integer
 */
define('LDAP_PROTOCOL_VERSION', '');

/**
 * Nome del filtro di ricerca dell'utente
 * 
 * @var string
 */
define('LDAP_FILTER_SEARCH', '');

?>
