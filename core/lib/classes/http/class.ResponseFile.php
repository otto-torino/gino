<?php
/**
 * @file class.ResponseFile.php
 * @brief Contiene la definizione ed implementazione della classe Gino.Http.ResponseFile
 */

namespace Gino\Http;

/**
 * @brief Subclass di \Gino\Http\Response per gestire stream di file
 *
 * @copyright 2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class ResponseFile extends Response {

    protected $_content_disposition,
              $_filename;

    /**
     * @brief Costruttore
     * @param string $file path assoluto al file o contenuto del file (in tal caso settare a TRUE kwargs['file_is_content'])
     * @param string $content_type
     * @param string $filename
     * @param array $kwargs array associativo
     *              - disposition_type: string, disposition type header
     *              - file_is_content: bool, se TRUE il parametro $file viene considerato essere il contenuto del file e non il path assoluto
     * @return void
     */
    function __construct($file, $content_type, $filename, array $kwargs = array()) {

        $file_is_content = isset($kwargs['file_is_content']) ? $kwargs['file_is_content'] : FALSE;

        if(!$file_is_content) {
            if($fp = fopen($file, "r")) {
                ob_start();
                @readfile($file);
                $content = ob_get_clean();
                fclose($fp);
            }
        }
        else {
            $content = $file;
        }

        parent::__construct($content, $kwargs);

        $this->_disposition_type = isset($kwargs['disposition_type']) ? $kwargs['disposition_type'] : 'inline';
        $this->_filename = $filename;
        $this->setContentType($content_type);
    }

    /**
     * @bief Setter del disposition type
     * @param string $disposition_type
     * @return void
     */
    public function setDispositionType($disposition_type) {
        $this->_disposition_type = $disposition_type;
    }

    /**
     * @brief Invia gli header della richiesta HTTP
     * @return void
     */
    protected function sendHeaders() {

        parent::sendHeaders();
        // content disposition
        header(sprintf('Content-Disposition: %s; filename=%s', $this->_disposition_type, $this->_filename));
    }

}
