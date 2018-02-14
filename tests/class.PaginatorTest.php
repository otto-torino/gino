<?php
/**
 * @file class.PaginatorTest.php
 * @brief Contiene la definizione ed implementazione della classe Gino.PHPUnitTest.PaginatorTest
 *
 * @copyright 2018 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

namespace Gino\PHPUnitTest;

use PHPUnit\Framework\TestCase;

use \Gino\Loader;
use \Gino\Paginator;

require_once 'include.php';

Loader::import('class', '\Gino\Singleton');
Loader::import('class', '\Gino\Db');
Loader::import('class', '\Gino\Session');
Loader::import('class/http', '\Gino\Http\Request');
Loader::import('class', '\Gino\Paginator');

function setPage($page) {
    $request = \Gino\Http\Request::instance();
    $request->GET['p'] = $page;
}

/**
 * @brief Classe di tipo PHPUnit.Framework.TestCase per testare la classe Gino.Paginator
 * 
 * @copyright 2014-2018 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class PaginatorTest extends TestCase {

    /**
     * Test metodo getCurrentPage
     * Il metodo restituisce il numero di pagina corrente
     */
    public function test_getCurrentPage() {
        // pagina 1
        setPage(1);
        $paginator = new Paginator(100, 10);
        $page = $paginator->getCurrentPage();
        $this->assertEquals(1, $page, 'current page errata pagina 1');

        // numero di pagina negativo, deve dare 1
        setPage(-20);
        $paginator = new Paginator(100, 10);
        $page = $paginator->getCurrentPage();
        $this->assertEquals(1, $page, 'current page errata con pagina negativa');

        // numero di pagina maggiore, deve dare 10
        setPage(20);
        $paginator = new Paginator(100, 10);
        $page = $paginator->getCurrentPage();
        $this->assertEquals(10, $page, 'current page errata con pagina maggiore');

        // senza elementi, deve dare 1
        setPage(20);
        $paginator = new Paginator(0, 10);
        $page = $paginator->getCurrentPage();
        $this->assertEquals(1, $page, 'current page errata senza elementi');
    }

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
        // no items
        setPage(1);
        $paginator = new Paginator(0, 10);
        $limit = $paginator->limit();
        $this->assertEquals(array(0, 0), $limit, 'limit errato con nessun elemento');
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

    /**
     * Test metodo pages
     * Il metdo pages ricava le pagine da mostrare nella navigazione, inserendo i tre puntini '...' tra 
     * pagine non contigue. Di default l'intorno mostrato della pagina corrente è di 4 pagine, due per lato.
     */
    public function test_pages() {
        // page 1
        setPage(1);
        $paginator = new Paginator(98, 10);
        $pages = $paginator->pages();
        $this->assertEquals(array('1', '2', '3', '...', '10'), $pages, 'pages errato pagina 1');

        // page 5
        setPage(5);
        $paginator = new Paginator(98, 10);
        $pages = $paginator->pages();
        $this->assertEquals(array('1', '...', '3', '4', '5', '6', '7', '...', '10'), $pages, 'pages errato pagina 5');
    }
}
