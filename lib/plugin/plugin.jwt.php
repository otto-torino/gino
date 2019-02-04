<?php
/**
 * @file plugin.jwt.php
 * @brief Contiene la classe plugin_jwt
 * 
 * @copyright 2018 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

/**
 * @namespace Gino.Plugin
 * @description Namespace che comprende classi di tipo plugin
 */
namespace Gino\Plugin;

require_once SITE_ROOT . '/vendor/autoload.php';

use \Firebase\JWT\JWT;

/**
 * @brief Interfaccia alla libreria PHP-JWT
 * 
 * @see https://github.com/firebase/php-jwt
 * @see https://www.dyclassroom.com/json-web-tokens/jwt-project-firebase-php-jwt-introduction
 * 
 * @copyright 2018 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 * 
 * https://scotch.io/tutorials/the-anatomy-of-a-json-web-token
 * 
 * #INSTALLATION
 * ---------------
 * Official installation method is via composer and its packagist package mpdf/mpdf (@link https://packagist.org/packages/firebase/php-jwt).
 * @code
 * $ composer require firebase/php-jwt
 * @endcode
 * 
 * #DESCRIZIONE
 * ---------------
 * Json Web Token (JWT) è uno standard, abbastanza recente, uscito all’inizio del 2015, 
 * che consente al server di scrivere in un messaggio (payload) chi è l’utente loggato (e altre info di contorno), 
 * incapsulare questo messaggio all’interno di un token e darlo al client che lo utilizzerà da ora in poi 
 * per le successive chiamate al server, informandolo così di chi sta effettuando la chiamata. 
 * Questo consentirà al server di avere già le informazioni di autenticazione direttamente nel token stesso, 
 * evitando così dover passare dal database o di usare le sessioni per memorizzare le informazioni sull’autenticazione.
 * 
 * L’idea che c’è alla base del Json Web Token è che dopo l’autenticazione, il server prepara un token all’interno del quale racchiude un payload 
 * in cui viene dichiarato in maniera esplicita chi è l’utente loggato. 
 * Dentro il token, oltre il payload viene inserita la firma dal server (costituto dal payload stesso criptato 
 * con la sua chiave segreta in codifica hash 256). 
 * Il client riceve il token e se vuole sarà libero di leggere il payload contenuto ma non potrà modificarlo 
 * poiché se lo facesse il token sarà invalidato dal server. 
 * Il client dovrà comunicare al server il token ricevuto per tutte le successive chiamate in cui è richiesta l’autenticazione. 
 * Il server riceverà il token ed estrapolerà il payload ma prima si assicurerà che il token sia stato firmato e autentificato 
 * con la sua chiave privata. 
 * Poiché il token contiene il payload con tutte le informazioni necessarie all’autenticazione (es. iduser), il server potrà evitare 
 * di passare ogni volta dal database per verificare a quale utente corrisponde quel token (ottimo per la scalabilità).
 * 
 * ##Meccanismo di creazione del Token
 * Un JSON Web Token consiste di tre parti: Header, Payload e Signature. Ad esempio:
 * 1. The Header is set to the following:
 * @code
 * {
 *   "typ": "JWT",
 *   "alg": "HS256"
 * }
 * @endcode
 * L’oggetto json viene codificato usando la funzione encodebase64 e come prima parte del nostro token abbiamo ad esempio:
 * eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9
 * 
 * 2. The Payload will look something like the following:
 * @code
 * {
 *   "userid": "u1",
 *   "iat": 1523798197,
 *   "exp": 1523798257
 * }
 * @endcode
 * Il payload viene anche esso codificato con la funzione encodebase64 ottenendo la seconda parte del nostro token:
 * eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiYWRtaW4iOnRydWV9
 * 
 * 3. Signature
 * @code
 * HMACSHA256 {
 *   base64UrlEncode(header) + "." +
 *   base64UrlEncode(payload),
 *   MY-KEY-SECRET
 * }
 * @endcode
 * La firma non è altro che il risultato di una funzione hash 256 che prende in input la codifica base64 dell’header 
 * concatenandola con un punto alla codifica base64 del payload, il tutto codificato con la nostra “chiave segreta” 
 * che solo il server conoscerà!
 * Il risultato finale è la concatenazione di queste 3 parti appena viste.
 * 
 * #MODI DI UTILIZZO
 * ---------------
 * @code
 * require_once(PLUGIN_DIR.OS.'plugin.jwt.php');
 * @endcode
 * 
 * IMPORTANT:
 * You must specify supported algorithms for your application. See
 * https://tools.ietf.org/html/draft-ietf-jose-json-web-algorithms-40
 * for a list of spec-compliant algorithms.
 * 
 * $jwt = JWT::encode($token, $key);
 * $token = JWT::decode($jwt, $key, ['HS256']);
 * 
 * You can add a leeway to account for when there is a clock skew times between
 * the signing and verifying servers. It is recommended that this leeway should
 * not be bigger than a few minutes.
 * 
 * Source: http://self-issued.info/docs/draft-ietf-oauth-json-web-token.html#nbfDef
 * 
 * JWT::$leeway = 60; // $leeway in seconds
 * $token = JWT::decode($jwt, $key, ['HS256']);
 * 
 * ###Registered Claims (Richieste registrate da impostare nel Payload)
 * 
 * Claims that are not mandatory whose names are reserved for us (richieste che non sono obbligatorie e i cui nomi sono riservati).
 * These include:
 * iat – timestamp of token issuing.
 * jti – A unique string, could be used to validate a token, but goes against not having a centralized issuer authority.
 * iss – A string containing the name or identifier of the issuer application. 
 *      Can be a domain name and can be used to discard tokens from other applications.
 * nbf – Timestamp of when the token should start being considered valid. Should be equal to or greater than iat.
 * exp – Timestamp of when the token should cease to be valid. Should be greater than iat and nbf.
 * 
 * ###Example
 * 
 * @code
 * $tokenId    = base64_encode(mcrypt_create_iv(32));
 * $issuedAt   = time();
 * $notBefore  = $issuedAt + 10;             // Adding 10 seconds
 * $expire     = $notBefore + 60;            // Adding 60 seconds
 * $serverName = $config->get('serverName'); // Retrieve the server name from config file
 * 
 * $data = [
 *   'iat'  => $issuedAt,         // Issued at: time when the token was generated
 *   'jti'  => $tokenId,          // Json Token Id: an unique identifier for the token
 *   'iss'  => $serverName,       // Issuer
 *   'nbf'  => $notBefore,        // Not before
 *   'exp'  => $expire,           // Expire
 *   'data' => [                  // Data related to the signer user
 *     'userId'   => $rs['id'],   // userid from the users table
 *     'userName' => $username,   // User name
 *   ]
 * ];
 * @endcode
 * 
 * ###Example with RS256 (openssl)
 * 
 * @code
 * $privateKey = <<<EOD
 * -----BEGIN RSA PRIVATE KEY-----
 * MIICXAIBAAKBgQC8kGa1pSjbSYZVebtTRBLxBz5H4i2p/llLCrEeQhta5kaQu/Rn
 * [...]
 * B2zNzvrlgRmgBrklMTrMYgm1NPcW+bRLGcwgW2PTvNM=
 * -----END RSA PRIVATE KEY-----
 * EOD;
 * 
 * $publicKey = <<<EOD
 * -----BEGIN PUBLIC KEY-----
 * MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQC8kGa1pSjbSYZVebtTRBLxBz5H
 * ...
 * ehde/zUxo6UvS7UrBQIDAQAB
 * -----END PUBLIC KEY-----
 * EOD;
 * 
 * $token = array(
 *     "iss" => "example.org",
 *     "aud" => "example.com",
 *     "iat" => 1356999524,
 *     "nbf" => 1357000000
 * );
 * 
 * $jwt = JWT::encode($token, $privateKey, 'RS256');
 * $decoded = JWT::decode($jwt, $publicKey, array('RS256'));
 * @endcode
 * 
 */
class plugin_jwt {

    /**
	 * @brief Json Token Id: an unique identifier for the token
	 * @var string
	 */
    private $_tokenId;
	
	/**
	 * @brief The secret server key for the JWT signature
	 * @var string
	 */
	private $_secret_server_key;
	
	/**
	 * @brief Algoritmi supportati
	 * @var string or array
	 */
	private $_algorithms;
	
	/**
	 * @brief Seconds for leeway
	 * 
	 * @var integer
	 */
	private $_seconds;
	
	/**
	 * @brief Leeway
	 * @description Add a leeway to account for when there is a clock skew times between the signing and verifying servers. 
	 * It is recommended that this leeway should not be bigger than a few minutes.
	 * 
	 * @var boolean
	 */
	private $_leeway;
	
	/**
	 * Constructor
	 * 
	 * @param array $options array associativo di opzioni
	 *   - @b algorithms (string or array), default @a HS256
	 *   - @b seconds (integer)
	 * @return void
	 */
	function __construct($options=array()) {
		
		$this->_algorithms = \Gino\gOpt('algorithms', $options, 'HS256');
		$this->_seconds = \Gino\gOpt('seconds', $options, 60);
		
		$this->_secret_server_key = JWT_SECRET;
		$this->_tokenId = base64_encode("ilermjkdnvdf345hf23jkrb");
		
		$this->_leeway = false;
	}
	
	public function generate_key($len = 16) {
	
	    $data = \openssl_random_pseudo_bytes($len);
	    
	    $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0010
	    $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10
	    
	    return vsprintf('%s%s%s%s%s%s%s%s', str_split(bin2hex($data), 4));
	}
	
	public function setSecretServerKey($key) {
	    
	    $this->_secret_server_key = (string)$key;
	}
	
	public function setAlgorithms($algorithms) {
	    
	    if(is_array($algorithms) or is_string($algorithms)) {
	        $this->_algorithms = $algorithms;
	    }
	}
	
	private function setPayload($user) {
	    
	    $issuedAt = time();
	    $notBefore = $issuedAt;
	    $expire = 3600;             // 1h
	    //$notBefore = $issuedAt + 10;    // Adding 10 seconds
	    //$expire = $notBefore + 60;      // Adding 60 seconds
	    
	    $payload = [
	        'iat'  => $issuedAt,        // Issued at: time when the token was generated
	        'jti'  => $this->_tokenId,  // Json Token Id: an unique identifier for the token
	        'iss'  => "localhost",      // Issuer
	        'nbf'  => $notBefore,       // Not before
	        //'exp'  => $expire,        // Expire
	        'data' => [                 // Data related to the signer user
	            'userId'   => $user->id,
	            'userName' => $user->firstname.' '.$user->lastname,
	            'userEmail' => $user->email,
	            'isSuperUser' => $user->is_admin
	        ]
	    ];
	    
	    return $payload;
	}
	
	/**
	 * @brief Crea il Token JWT
	 * 
	 * @param \Gino\App\Auth\User $user
	 * @return string
	 */
	public function JWT_encode($user) {
	    
	    return \Firebase\JWT\JWT::encode($this->setPayload($user), $this->_secret_server_key, $this->_algorithms);
	}
	
	/**
	 * @brief Decodifica il Token JWT
	 * 
	 * @param string $jwt
	 * @return object
	 * 
	 * object
	 *   public 'iat' => int
	 *   public 'jti' => string
	 *   public 'iss' => string
	 *   public 'nbf' => int
	 *   public 'data' => object
	 *     public 'userId' => int
	 *     public 'userName' => string
	 *     public 'userEmail' => string
	 *     public 'isSuperUser' => boolean
	 * 
     * To get an associative array, you will need to cast it as such:
     * $token_array = (array) $token;
     * 
     * @todo verificare se si può intercettare il throw new (ws/vendor/firebase/php-jwt/src/JWT.php -> decode())
	 */
	public function JWT_decode($jwt) {
	    
	    if($this->_leeway) {
	        \Firebase\JWT\JWT::$leeway = $this->_seconds;
	    }
	    
	    if(is_string($this->_algorithms)) {
	        $algorithms = [$this->_algorithms];
	    }
	    else {
	        $algorithms = $this->_algorithms;
	    }
	    
	    return \Firebase\JWT\JWT::decode($jwt, $this->_secret_server_key, $algorithms);
	}
	
	/**
	 * @brief Verifica il token
	 * 
	 * @param object $token
	 * @return boolean
	 */
	public function verifyToken($token) {
	    
	    if($token->jti == $this->_tokenId) {
	        return true;
	    }
	    else {
	        return false;
	    }
	}
}
?>
