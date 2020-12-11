<?php
/**
 * @file class.ResponsePdf.php
 * @brief Contiene la definizione ed implementazione della classe Gino.Http.ResponsePdf
 *
 * @copyright 2015-2017 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

namespace Gino\Http;

/**
 * @brief Subclass di \Gino\Http\Response per gestire risposte in formato pdf
 *
 * @copyright 2015-2017 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 * 
 * ##Esempio di gestione di una risposta Pdf
 * 
 * Per generare il pdf di una pagina html si può chiamare l'interfaccia di visualizzazione passandogli il parametro [?pdf=1]. \n
 * Il codice esemplificativo potrebbe essere il seguente:
 * @code
 * $pdf = \Gino\cleanVar($request->GET, 'pdf', 'int', '');
 * // [code]
 * if($pdf)
 * {
 *   \Gino\Loader::import('class/http', '\Gino\Http\ResponsePdf');
 *   return new \Gino\Http\ResponsePdf($render, array(
 *     'css_file'=>array('css/mpdf.css'),
 *     'filename'=>'doc.pdf'
 *   ));
 * }
 * @endcode
 * Dove $render è il classico $view->render($dict).
 */
class ResponsePdf extends Response {

    /**
     * @brief Costruttore
     * @param mixed $content contenuto della risposta. Se diverso da stringa viene codificato in json
     * @param array $kwargs array associativo di opzioni
     *   - di \Gino\Plugin\gino_mpdf::pdfFromPage()
     *   - di \Gino\Plugin\plugin_mpdf::setPhpParams()
     * @return redirect or null
     */
    function __construct($content, array $kwargs = array()) {

    	require_once PLUGIN_DIR.OS.'plugin.mpdf.php';
    	
    	\Gino\Plugin\plugin_mpdf::setPhpParams($kwargs);
    	$obj_pdf = new \Gino\Plugin\gino_mpdf();
    	return $obj_pdf->pdfFromPage($content, $kwargs);
    }
}
