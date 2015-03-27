<?php
/**
 * @file const.php
 * @brief Alcune costanti utilizzate dal sistema
 *
 * @copyright 2005-2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

namespace Gino;

/** Nome della tabella che contiene le istanze di moduli */
define("TBL_MODULE", 'sys_module');

/** Nome della tabella che contiene i moduli installati */
define("TBL_MODULE_APP", 'sys_module_app');

/** Nome della tabella che contiene la configurazione del sistema */
define("TBL_SYS_CONF", 'sys_conf');

/** Nome della tabella che contiene il log degli accessi */
define("TBL_LOG_ACCESS", 'sys_log_access');

/** Nome della tabella utenti */
define("TBL_USER", 'auth_user');

/** Nome della tabella informazioni aggiuntive utenti */
define("TBL_USER_ADD", 'auth_user_add');

/** Nome della tabella di join utenti - gruppi */
define("TBL_USER_GROUP", 'auth_user_group');

/** Nome della tabella dei gruppi di utenti */
define("TBL_GROUP", 'auth_group');

/** Nome della tabella di join gruppi - permessi */
define("TBL_GROUP_PERMISSION", 'auth_group_perm');

/** Nome della tabella dei permessi */
define("TBL_PERMISSION", 'auth_permission');

/** Nome della tabella di join utenti - permessi */
define("TBL_USER_PERMISSION", 'auth_user_perm');

/** Nome della tabella dei profili di registrazione utenti */
define("TBL_REGISTRATION_PROFILE", 'auth_registration_profile');

/** Nome della tabella di associazione profili di registrazione utenti - gruppi */
define("TBL_REGISTRATION_PROFILE_GROUP", 'auth_registration_profile_group');

/** Nome della tabella delle richieste di registrazione */
define("TBL_REGISTRATION_REQUEST", 'auth_registration_request');

/** Nome della tabella lingue */
define("TBL_LANGUAGE", 'language');

/** Nome della tabella traduzioni */
define("TBL_TRANSLATION", 'language_translation');

/** Nome della tabella nazioni */
define("TBL_NATION", 'nation');
