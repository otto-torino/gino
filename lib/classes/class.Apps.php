<?php
/**
 * @file class.Apps.php
 * @brief Contiene la definizione ed implementazione della classe Gino.Apps
 * 
 * @copyright 2017 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

/**
 * @brief Permette di mantenere disponibili i valori delle istanze
 * @description La classe è di tipo Gino.Singleton per garantire l'esistenza di una sola istanza a runtime.
 * 
 * @copyright 2017 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class Apps extends Singleton {

	/**
	 * @brief Array associativo che contiene le istanze
	 * @access private
	 */
	private $vars = array();
	
	/**
	 * @brief Imposta il valore di una variabile
	 *
	 * @param string $index nome della variabile
	 * @param mixed $value valore della variabile
	 * @return void
	 *
	 */
	public function __set($index, $value) {
		$this->vars[$index] = $value;
	}
	
	/**
	 * @brief Ritorna il valore di una variabile
	 *
	 * @param string $index nome della variabile
	 * @return mixed, valore variabile o null se non definita
	 */
	public function __get($index) {
		return isset($this->vars[$index]) ? $this->vars[$index] : null;
	}
	
	/**
	 * @brief Controlla se è stata caricata una istanza
	 *
	 * @param string $instance nome dell'istanza
	 * @return TRUE se è definita, FALSE altrimenti
	 */
	public function instanceExists($instance) {
		
		return (bool) isset($this->vars[$instance]);
	}
}
