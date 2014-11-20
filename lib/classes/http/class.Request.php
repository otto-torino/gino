<?php
/**
 * @file class.Request.php
 * @brief Contiene la definizione ed implementazione della classe \Gino\Http\Request
 *
 * @copyright 2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

namespace Gino\Http;

use \Gino\Loader;
use \Gino\Singleton;
use \Gino\Session;
use \Gino\App\Auth\User;

/**
 * @brief Wrapper di una richiesta HTTP
 *
 * Contiene tutte le informazioni importanti di una richiesta HTTP. La classe è un singleton quindi tutte le classi
 * che la utilizzano si scambiano la stessa istanza. Le proprietà sono pubbliche e aperte in lettura e scrittura.
 *
 * @copyright 2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class Request extends Singleton {

    public $GET,
           $POST,
           $REQUEST,
           $COOKIES,
           $FILES,
           $META,
           $method,
           $path,
           $url,
           $request_uri,
           $query_string,
           $absolute_url,
           $root_absolute_url,
           $session,
           $user;

    /**
     * @brief Costruttore
     * @description Il costruttore è protetto in modo da garantire il pattern Singleton
     * @return nuova istanza di \Gino\Http\Request
     */
    protected function __construct() {

        $this->GET = $_GET;
        $this->POST = $_POST;
        $this->REQUEST = $_REQUEST;
        $this->COOKIES = $_COOKIE;
        $this->FILES = $_FILES;

        $this->META = array(
            'CONTENT_LENGTH' => $this->valueOrNull($_SERVER, 'CONTENT_LENGTH'),
            'CONTENT_TYPE' => $this->valueOrNull($_SERVER, 'CONTENT_TYPE'),
            'REMOTE_ADDR' => $this->valueOrNull($_SERVER, 'REMOTE_ADDR'),
            'REMOTE_HOST' => $this->valueOrNull($_SERVER, 'REMOTE_HOST'),
            'REQUEST_SCHEME' => $this->valueOrNull($_SERVER, 'REQUEST_SCHEME'),
            'SERVER_NAME' => $this->valueOrNull($_SERVER, 'SERVER_NAME'),
            'SERVER_PORT' => $this->valueOrNull($_SERVER, 'SERVER_PORT'),
            'SERVER_PROTOCOL' => $this->valueOrNull($_SERVER, 'SERVER_PROTOCOL'),
            'SCRIPT_NAME' => $this->valueOrNull($_SERVER, 'SCRIPT_NAME'),
            'HTTP_HOST' => $this->valueOrNull($_SERVER, 'HTTP_HOST'),
            'HTTP_USER_AGENT' => $this->valueOrNull($_SERVER, 'HTTP_USER_AGENT'),
            'HTTP_REFERER' => $this->valueOrNull($_SERVER, 'HTTP_REFERER'),
            'HTTP_COOKIE' => $this->valueOrNull($_SERVER, 'HTTP_COOKIE'),
        );

        $this->request_uri = $_SERVER['REQUEST_URI'];
        $this->query_string = $_SERVER['QUERY_STRING'];
        $this->path = preg_replace("#".preg_quote(SITE_WWW)."#", '', $this->request_uri);
        $this->url = $this->path; // viene ridefinito dalla classe \Gino\Router che chiama self::updateUrl se si esegue l'url rewriting
        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->absolute_url = sprintf('%s://%s%s', $this->META['REQUEST_SCHEME'] ? $this->META['REQUEST_SCHEME'] : 'http', $this->META['HTTP_HOST'], $this->request_uri);
        $this->root_absolute_url = sprintf('%s://%s%s', $this->META['REQUEST_SCHEME'] ? $this->META['REQUEST_SCHEME'] : 'http', $this->META['HTTP_HOST'], SITE_WWW);

        $this->session = Session::instance();

        Loader::import('auth', 'User');
        $this->user = new User($this->session->user_id ? $this->session->user_id : null);

    }

    public function updateUrl() {
        $this->url = $this->META['SCRIPT_NAME'];
        if(count($this->GET)) {
            if(isset($this->GET['evt'])) {
                $this->url .= '?evt['.key($this->GET['evt']).']';
            }
        }

        var_dump($this->url);
    }

    /**
     * @brief Connessione sicura https
     * @return TRUE se la connessione è sicura, FALSE altrimenti
     */
    public function isSecure() {
        return
            (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || $_SERVER['SERVER_PORT'] == 443;
    }

    /**
     * @brief Controlla se la chiave key di GET ha valore value
     * @param string $key chiave
     * @param mixed $value valore
     * @return TRUE se GET[key] = value, FALSE altrimenti
     */
    public function checkGETKey($key, $value) {
        return isset($this->GET[$key]) and $this->GET[$key] === $value;
    }

    /**
     * @brief Controlla se la chiave key di POST ha valore value
     * @param string $key chiave
     * @param mixed $value valore
     * @return TRUE se POST[key] = value, FALSE altrimenti
     */
    public function checkPOSTKey($key, $value) {
        return isset($this->POST[$key]) and $this->POST[$key] === $value;
    }

    /**
     * @brief Valore associato alla chiave data di un array o null
     * @param array $array
     * @param string $key
     * @return valore associato alla $key data o null
     */
    private function valueOrNull(array $array, $key) {
        return isset($array[$key]) ? $array[$key] : null;
    }

}
