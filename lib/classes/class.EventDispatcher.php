<?php
/**
 * @file class.EventDispatcher.php
 * @brief Contiene la classe EventDispatcher per la gestione di segnali tra oggetti
 *
 * @copyright 2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

/**
 * @brief Classe per la gestione di segnali (eventi) tra oggetti
 * @see Singleton
 *
 * @copyright 2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class EventDispatcher extends Singleton {

    private $_listeners = array();
    private $_emitter_listeners = array();

    /**
     * @brief Registra un listener per l'ascolto di un evento notificato da un emitter
     * @param mixed $emitter oggetto che emette il segnale
     * @param string $event_name nome evento
     * @param callable un callable, vedi call_user_func. Riceve come parametri il nome dell'evento ed un parametro passato dall'emitter
     * @return void
     */
    public function listenEmitter($emitter, $event_name, $callable) {
        $key = get_class($emitter).'-'.$event_name;
        if(!isset($this->_emitter_listeners[$key])) {
            $this->_emitter_listeners[$key] = array();
        }
        $this->_emitter_listeners[$key][] = $callable;
    }

    /**
     * @brief Registra un listener per l'ascolto di un evento notificato da qualunque emitter
     * @param string $event_name nome evento
     * @param callable un callable, vedi call_user_func. Riceve come parametri l'oggetto che emette, il nome dell'evento ed un parametro passato dall'emitter
     * @return void
     */
    public function listen($event_name, $callable) {
        $key = $event_name;
        if(!isset($this->_listeners[$key])) {
            $this->_listeners[$key] = array();
        }
        $this->_listeners[$key][] = $callable;
    }

    /**
     * @brief Notifica di un evento da parte di un emitter a tutti i listener
     * @param string $emitter nome classe dell'emitter
     * @param string $event_name nome evento
     * @param mixed $params argomenti ulteriori da passare al listener
     */
    public function emit($emitter, $event_name, $params = null) {
        // emitter events listeners
        $key = get_class($emitter).'-'.$event_name;
        if(isset($this->_emitter_listeners[$key])) {
            foreach($this->_emitter_listeners[$key] as $callable) {
                call_user_func($callable, $event_name, $params);
            }
        }

        // events listeners
        $key = $event_name;
        if(isset($this->_listeners[$key])) {
            foreach($this->_listeners[$key] as $callable) {
                call_user_func($callable, $emitter, $event_name, $params);
            }
        }
    }

}
