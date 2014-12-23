<?php
/**
 * @file class.Singleton.php
 * @brief Contiene la definizione ed implemetazione della classe Gino.Singleton
 *
 * @copyright 2013-2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

/**
 * @brief Classe astratta per implementazione del Singleton pattern
 *
 * @description Garantisce che che non vengano create istanze multiple delle classi che la estendono.
 *              Quando una classe richiede un'istanza di una classe di tipo Gino.Singleton riceve sempre la stessa
 *              istanza, uguale a quella che ricevono tutte le altre classi.
 *              Il pattern Singleton permette di implementare al meglio le classi che rappresentano il controllo del database,
 *              la http request, il registro di sistema e la sessione.
 * @see Gino.Registry
 * @see Gino.Session
 * @see Gino.Db
 * @see Gino.Http.Request
 * @copyright 2013-2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
abstract class singleton {

    protected static $_instances = array();

    /**
     * @brief Costruttore
     * @description Il costruttore è definito come metodo protetto in modo che classi client
     *              non possano ottenere nuove istanze di una class Gino.Singleton attraverso di esso.
     */
    protected function __construct() {
    }

    /**
     * @brief Metodo per recuperare istanze Singleton
     *
     * @description Per ogni classe Singleton ritorna sempre la stessa istanza
     * @return object
     */
    public static function instance() {

        $class = get_called_class();
        if(array_key_exists($class, self::$_instances) === FALSE) {
            self::$_instances[$class] = new static();
        }

        return self::$_instances[$class];
    }

    /**
     * @brief Metodo per recuperare istanze Singleton rispetto alla classe fornita
     *
     * @description Per ogni classe Singleton e classe fornita ritorna sempre la stessa istanza
     * @param string $main_class nome della classe che richiede l'istanza Singleton
     * @return object
     */
    public static function instance_to_class($main_class) {

        $class = get_called_class().'_'.$main_class;
        if(array_key_exists($class, self::$_instances) === FALSE) {
            self::$_instances[$class] = new static($main_class);
        }

        return self::$_instances[$class];
    }

    /**
     * @brief I Singleton non possono essere clonati
     */
    public function __clone() {
        throw new \Exception(_("Impossibile clonare un Singleton"));
    }

    /**
     * @brief I Singleton non possono essere serializzati
     */
    public function __sleep() {
        throw new \Exception(_("Impossibile serializzare Singleton"));
    }

    /**
     * @brief I Singleton non possono essere serializzati
     */
    public function __wakeup() {
        throw new \Exception(_("Impossibile serializzare Singleton"));
    }
}
