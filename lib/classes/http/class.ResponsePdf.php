<?php
/**
 * @file class.ResponsePdf.php
 * @brief Contiene la definizione ed implementazione della classe Gino.Http.ResponsePdf
 *
 * @copyright 2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

namespace Gino\Http;

/**
 * @brief Subclass di \Gino\Http\Response per gestire risposte in formato pdf
 *
 * @copyright 2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class ResponsePdf extends Response {

    /**
     * @brief Costruttore
     * @param mixed $content contenuto della risposta. Se diverso da stringa viene codificato in json
     * @param array $kwargs array associativo di opzioni
     *   - di \Gino\Plugin\gino_mpdf::pdfFromPage()
     *   - di \Gino\Plugin\plugin_mpdf::setPhpParams()
     * @return istanza di \Gino\Http\ResponsePdf
     */
    function __construct($content, array $kwargs = array()) {

    	require_once PLUGIN_DIR.OS.'plugin.mpdf.php';
    	
    	\Gino\Plugin\plugin_mpdf::setPhpParams($kwargs);
    	$obj_pdf = new \Gino\Plugin\gino_mpdf();
    	return $obj_pdf->pdfFromPage($content, $kwargs);
    }
}
