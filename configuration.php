<?php
/**
 * @file configuration.php
 * @brief File di configurazione
 * 
 * Contiene i parametri dell'applicazione
 * 
 * @copyright 2005-2019 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

/**
 * @brief Opzione Debug
 * @description Quando il debug è attivo i file statici non vengono uniti e minificati e non vengono
 *              inviate email all'amministratore in caso di errore di sistema. Settare a FALSE in produzione
 *              per ricevere notifiche di errori e comprimere i file statici.
 * 
 * @var boolean
 */
define("DEBUG", TRUE);

/**
 * #brief Opzione debug delle query di inserimento e modifica
 * @description Quando il debug è attivo vengono stampate a video le query di inserimento e modifica del modello.
 * Le query vengono eseguite e lo script terminato.
 *
 * @var boolean
 */
define('DEBUG_ACTION_QUERY', FALSE);

/**
 * @brief Opzione statistiche
 * @description Quando il debug è attivo mostra una barra a scomparsa con le statistiche di esecuzione dello 
 *              script e delle query.
 * 
 * @var boolean
 */
define("SHOW_STATS", FALSE);

/**
 * @brief Amministratori sistema
 * @description Ricevono notifiche di errori di sistema. Il valore deve essere necessariamente un array serializzato
 */
define("ADMINS", serialize(array('marco.guidotti@otto.to.it')));

// Database

/**
 * Utilizzo della libreria PDO
 * @see Db::instance()
 * @var boolean
 */
define("USE_PDO", true);

/**
 * Tipo di database
 * @see Db::instance()
 * @var string (mysql, sqlsrv)
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
 * @see \Gino\Plugin\pdo::getCharset()
 */
define("DB_CHARSET", 'utf8');

// Query cache

/**
 * Tipologia di cache delle query
 * 
 * @see \Gino\Plugin\plugin_phpfastcache::cacheType()
 * @var string
 */
define("QUERY_CACHE_TYPE", 'auto');

/**
 * Parametri di connessione ad alcune tipologie di cache
 * @var array
 */
define("QUERY_CACHE_SERVER", null);

/**
 * Directory di salvataggio dei file di cache
 * @var string
 */
define("QUERY_CACHE_PATH", null);

/**
 * Tipologia di fallback della cache
 * @var string
 */
define("QUERY_CACHE_FALLBACK", null);

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

/**
 * Numero minimo di frequenze dei tag necessarie per poterli visualizzare nel tag cloud
 * @var integer
 */
define('TAG_CLOUD_MIN_NUMBER_FREQUENCES', 0);

/**
 * @brief Definitions for the administrative area
 * @see Gino.App.Index.index
 */

/* elenco delle applicazioni installate nel formato [app_name|instance_name] => icon_name */
define('INSTALLED_APPS', serialize(array(
	'sysconf' => 'cogs',
	'language' => 'language',
	'statistics' => 'pie-chart',
	'layout' => 'th',
	'buildapp' => 'cube',
	'graphics' => 'picture-o',
	'attachment' => 'file',
	'page' => 'file-text-o',
	'searchSite' => 'search',
	'instruments' => 'wrench',
	'auth' => 'user',
	'mainMenu' => 'bars',
	'menu_admin' => 'bars',
	'module' => 'puzzle-piece',
	'sysClass' => 'cubes',
)));

/* elenco delle applicazioni da non visualizzare */
define('HIDDEN_APPS', serialize(array('graphics', 'topbar', 'topbaradmin', 'menu_admin', )));

/* visualizzare le applicazioni nascoste */
define('VIEW_HIDDEN_APPS', false);

/**
 * @brief Maps Key
 */
define('GOOGLE_MAPS_KEY', null);
define('OPENSTREETMAP_KEY', null);

/**
 * @brief Google Analytics
 * @see Gino.App.Statistics.statistics
 */

/**
 * @brief Nome del file della chiave dell'account di servizio
 * @var string
 */
define('GOOGLE_ANALYTICS_VIEW_FILE', null);

/**
 * @brief ID dell'account di servizio
 * @var string
 */
define('GOOGLE_ANALYTICS_VIEW_ACCOUNT', null);

/**
 * @brief View ID di Google Analytics
 * @var integer
 */
define('GOOGLE_ANALYTICS_VIEW_ID', null);

/**
 * @brief Imposta i Middleware
 */
define('MIDDLEWARE', [
    'HEADERS' => true   // @see Gino.Middleware.Headers
]);

/**
 * JWT
 */
define('JWT_SECRET', 'MY-SECRET-KEY');

?>
