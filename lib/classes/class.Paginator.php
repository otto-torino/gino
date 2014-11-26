<?php
/**
 * @file class.Paginator.php
 * @brief Contiene la definizione ed implementazione delal classe Gino.Paginator
 *
 * @copyright Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

namespace Gino;

use \Gino\Http\Request;

/**
 * @brief Gestisce la paginazione di elementi
 * @description dati il numero di elementi totali ed il numero di elementi per pagina, ricava i limiti per
 *              creare il sottoinsieme di elementi da mostrare e gestisce la navigazione tra le pagine.
 *
 * @copyright 2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class Paginator {

    private $_items_number,
            $_items_for_page,
            $_pages_number,
            $_current_page;

    /**
     * @brief Costruttore
     * @param int $items_number numero totale di item
     * @param $items_for_page numero di items per pagina
     * @return istanza di Gino.Paginator
     */
    function __construct($items_number, $items_for_page) {

        $this->_items_number = $items_number;
        $this->_items_for_page = $items_for_page;
        $this->_pages_number = ceil($items_number / $items_for_page);

        $this->setCurrentPage();

    }

    /**
     * @brief Imposta la pagina corrente
     * @return void
     */
    private function setCurrentPage() {
        $request = Request::instance();
        $p = \Gino\cleanVar($request->GET, 'p', 'int');

        if(is_null($p) or $p < 1) {
            $this->_current_page = 1;
        }
        elseif($p > $this->_pages_number) {
            $this->_current_page = $this->_pages_number;
        }
        else {
            $this->_current_page = $p;
        }
    }

    /**
     * @brief Limiti items selezionati, 1 based
     * @return array(limite inferiore, numero superiore), il limite inferiore parte da 1
     */
    public function limit() {
        $inf = ($this->_current_page - 1) * $this->_items_for_page;
        $sup = min($inf + $this->_items_for_page, $this->_items_number);

        return array($inf + 1, $sup);
    }

    /**
     * @brief LIMIT CLAUSE, 0 based
     * @return array(limite inferiore, numero items per pagina), il limite inferiore parte da 0
     */
    public function limitQuery() {
        $inf = ($this->_current_page - 1) * $this->_items_for_page;
        return array($inf, $this->_items_for_page);
    }

    /**
     * @brief Riassunto elementi pagina corrente
     * @return codice html riassunto, es 10-20 di 100
     */
    public function summary() {
        $limit = $this->limit();
        return sprintf("%s-%s di %s", $limit[0], $limit[1], $this->_items_number);
    }

    /**
     * @brief Controllo per la navigazione delle pagine
     * @return codice html
     */
    public function navigator() {
        $pages = array();
        // intervallo inferiore
        for($i = max($this->_current_page - $this->_interval, 1); $i < $this->_current_page, $i++) {
            $pages[] = $i;
        }
        $pages[] = $this->_current_page;
        // intervallo superiore
        for($i = $this->_current_page + 1; $i < min($this->_current_page + $this->_interval, $this->_pages_number), $i++) {
            $pages[] = $i;
        }

        var_dump($pages);

    }
}
