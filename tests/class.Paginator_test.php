<?php

namespace Gino;

require_once('include.php');

Loader::import('class', '\Gino\Singleton');
Loader::import('class', '\Gino\Db');
Loader::import('class', '\Gino\Session');
Loader::import('class/http', '\Gino\Http\Request');
Loader::import('class', '\Gino\Paginator');

function setPage($page) {
    $request = \Gino\Http\Request::instance();
    $request->GET['p'] = $page;
}

class PaginatorTest extends \PHPUnit_Framework_TestCase {

    /**
     * Test metodo limit
     * Il metodo limit restiuisce gli estremi di selezione a partire da 1
     */
    public function test_limit() {
        // page 1
        setPage(1);
        $paginator = new Paginator(100, 10);
        $limit = $paginator->limit();
        $this->assertEquals(array(1, 10), $limit, 'limit errato pagina 1');
        // page 2
        setPage(2);
        $paginator = new Paginator(100, 10);
        $limit = $paginator->limit();
        $this->assertEquals(array(11, 20), $limit, 'limit errato pagina 2');
        // last page
        setPage(20);
        $paginator = new Paginator(98, 10);
        $limit = $paginator->limit();
        $this->assertEquals(array(91, 98), $limit, 'limit errato ultima pagina');
    }

    /**
     * Test metodo limitQuery
     * Il metodo limitQuery restiuisce l'estremo inferiore della selezione (a partire da 0) ed il numero di elementi da selezionare
     * Viene utilizzato per la condizione LIMIT delle query
     */
    public function test_limitQuery() {
        // page 1
        setPage(1);
        $paginator = new Paginator(100, 10);
        $limit = $paginator->limitQuery();
        $this->assertEquals(array(0, 10), $limit, 'limit query errato pagina 1');
        // page 2
        setPage(2);
        $paginator = new Paginator(100, 10);
        $limit = $paginator->limitQuery();
        $this->assertEquals(array(10, 10), $limit, 'limit query errato pagina 2');
        // last page
        setPage(20);
        $paginator = new Paginator(98, 10);
        $limit = $paginator->limitQuery();
        $this->assertEquals(array(90, 10), $limit, 'limit query errato ultima pagina');
    }

    /**
     * Test metodo summary
     * Il metdo summary stampa il sommario dei risultati slezionati, es 1-10 di 100
     */
    public function test_summary() {
        // page 1
        setPage(1);
        $paginator = new Paginator(100, 10);
        $summary = $paginator->summary();
        $this->assertEquals('1-10 di 100', $summary, 'summary errato pagina 1');
        // page 2
        setPage(2);
        $paginator = new Paginator(100, 10);
        $summary = $paginator->summary();
        $this->assertEquals('11-20 di 100', $summary, 'summary errato pagina 2');
        // last page
        setPage(20);
        $paginator = new Paginator(98, 10);
        $summary = $paginator->summary();
        $this->assertEquals('91-98 di 98', $summary, 'summary errato ultima pagina');
    }
}
