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
            $_interval,
            $_current_page;

    /**
     * @brief Costruttore
     * @param int $items_number numero totale di item
     * @param $items_for_page numero di items per pagina
     * @param array $kwargs array associativo
     *              - interval: int, default 5. Numero di pagine da mostrare nella navigazione nell'intorno di quella corrente
     * @return istanza di Gino.Paginator
     */
    function __construct($items_number, $items_for_page, array $kwargs = array()) {

        $this->_items_number = $items_number;
        $this->_items_for_page = $items_for_page;
        $this->_pages_number = (int) ceil($items_number / $items_for_page);

        $this->_interval = isset($kwargs['interval']) ? (int) $kwargs['interval'] : 5;

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
        $pages[] = array(1, $this->urlPage(1));
        // intervallo inferiore
        $inf_interval = max($this->_current_page - $this->_interval, 2);
        if($inf_interval > 2) {
            $pages[] = '...';
        }
        for($i = $inf_interval; $i < $this->_current_page; $i++) {
            $pages[] = array($i, $this->urlPage($i));
        }
        if($this->_current_page !== 1 and $this->_current_page !== $this->_pages_number) {
            $pages[] = array($this->_current_page, null);
        }
        // intervallo superiore
        $sup_interval = min($this->_current_page + $this->_interval, $this->_pages_number - 1);
        for($i = $this->_current_page + 1; $i <= $sup_interval; $i++) {
            $pages[] = array($i, $this->urlPage($i));
        }
        if($sup_interval < $this->_pages_number - 1) {
            $pages[] = '...';
        }
        $pages[] = array($this->_pages_number, $this->urlPage($this->_pages_number));

        // controllers
        $next = null;
        $prev = null;
        if($this->_current_page > 1) {
            $prev = $this->urlPage($this->_current_page - 1);
        }
        if($this->_current_page < $this->_pages_number) {
            $next = $this->urlPage($this->_current_page + 1);
        }

        $view = new \Gino\View(null, 'paginator_navigator');
        return $view->render(array(
            'pages' => $pages,
            'prev' => $prev,
            'next' => $next
        ));
    }

    /**
     * @brief Codice html completo della paginazione
     * @description Include la navigazione ed il sommario
     * @return codice html paginazione
     */
    public function pagination() {
        $view = new \Gino\View(null, 'paginator_pagination');
        return $view->render(array(
            'summary' => $this->summary(),
            'navigator' => $this->navigator()
        ));
    }

    /**
     * @brief Url che porta alla pagina data
     * @param int $p numero pagina
     * @return url o null se è la pagina corrente
     */
    private function urlPage($p) {

        $registry = \Gino\Registry::instance();
        if($p == $this->_current_page) return null;
        return $registry->router->transformPathQueryString(array('p' => $p));
    }

}
