<?php
/**
 * @file global.php
 * @brief Set di funzioni definite nel ROOT namespace
 *
 * @copyright 2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

namespace {

    if(!extension_loaded('gettext'))
    {
        /**
         * @brief Funzione per traduzioni che ritorna la stringa stessa
         * @description La funzione viene definita per evitare errori nel caso in cui le gettext non fossero abilitate
         * @param string $str stringa da tradurre
         * @return string, stringa non tradotta
         */
        function _($str){
            return $str;
        }
    }
    else
    {
        $domain='messages';
        bindtextdomain($domain, "./languages");
        bind_textdomain_codeset($domain, 'UTF-8');
        textdomain($domain);
    }

    /**
     * @brief Recupera il nome della classe senza namespace
     *
     * @param object|string $class oggetto o nome completo della classe
     * @return string, nome classe senza namespace
     */
    function get_name_class($class) {

        if(!$class) return null;

        if(is_object($class)) return get_name_class(get_class($class));

        if(substr($class, -1) == "\\")
        {
            $class = substr_replace($class, '', -1, 1);
        }

        $a_class = explode('\\', $class);

        return end($a_class);
    }

    /**
     * @brief Imposta il nome della classe col suo namespace
     *
     * @param string $class nome della classe
     * @param string $namespace nome del namespace, default \Gino
     * @return string
     */
    function set_name_class($class, $namespace = '\Gino\\') {

        if(!$namespace) return $class;

        if(substr($namespace, -1) != "\\")
        {
            $namespace = $namespace."\\";
        }

        return $namespace.$class;
    }

    /**
     * @brief Nome del namespace di una classe di tipo Gino.Controller
     *
     * @param string $controller_name nome della classe controller
     * @return string
     */
    function get_app_namespace($controller_name) {

        $ns = '\Gino\App\\'.ucfirst($controller_name);
        return $ns;
    }

    /**
     * @brief Nome della classe di tipo Gino.Controller con namespace completo
     * 
     * @param string $controller_name nome della classe controller
     * @return string
     */
    function get_app_name_class_ns($controller_name) {

        return get_app_namespace($controller_name).'\\'.$controller_name;
    }

    /**
     * @brief Nome della classe di tipo Gino.Model con namespace completo
     *
     * @param string $controller_name nome della classe controller
     * @param string $model_name nome del modello
     * @return string
     */
    function get_model_app_name_class_ns($controller_name, $model_name) {
        return get_app_namespace($controller_name).'\\'.$model_name;
    }
}
