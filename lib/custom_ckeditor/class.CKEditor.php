<?php
/**
 * @file class.CKEditor.php
 * @brief Contiene la definizione ed implementazione della classe Gino.CKEditor
 *
 * @copyright 2019 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

/**
 * @brief Classe di interfaccia con l'editor visuale CKEditor
 * 
 * @copyright 2019 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 * 
 * ## INFORMAZIONI
 * La versione installata di CKEditor è la 4.11.2.
 * L'indirizzo della libreria è https://ckeditor.com/ckeditor-4/
 * Documentazione: https://ckeditor.com/docs/ckeditor4/latest/index.html
 * L'indirizzo del builder dove si può personalizzare e scaricare l'editor: https://ckeditor.com/cke4/builder
 * 
 */
class CKEditor {

    /**
     * @brief Inizializza l'editor sovrascrivendo il tag Textarea
     * 
     * @todo Differenziare tipi diversi di Toolbar?
     * @param string $name valore id del tag textarea
     * @param string $value
     * @param array $options array associativo di opzioni
     *   - @b toolbar (string): nome della toolbar
     *   - @b width (mixed): larghezza dell'editor
     *      - This configuration option accepts an integer (to denote a value in pixels) 
     *      - or any CSS-defined length unit, including percent (%)
     *   - @b height (mixed): altezza dell'editor
     *      - This configuration option accepts an integer (to denote a value in pixels) 
     *      - or any CSS-defined length unit except percent (%) values which are not supported
     * @return string, script js
     */
    public static function replace($name, $value, $options=array()){
    
    	$toolbar = gOpt('toolbar', $options, null);
    	$width = gOpt('width', $options, '100%');
    	$height = gOpt('height', $options, 300);
    
    	if(empty($value)) {
    	    $value = '';
    	}
    	if(!$toolbar) {
    	    $toolbar = 'Full';
    	}
    	
    	$registry = Registry::instance();
    
    	$registry->addCustomJs(SITE_WWW.'/ckeditor/ckeditor.js', array('compress'=>false, 'minify'=>false));
    	
    	// language: 'it'
    	$buffer = "<script>
    	CKEDITOR.replace('$name', {
            customConfig: '".SITE_CUSTOM_CKEDITOR."/custom_config.js',
            contentsCss: '".SITE_CUSTOM_CKEDITOR."/custom_contents.css',

            /*toolbarGroups: [
                { name: 'document',	   groups: [ 'mode', 'document'] },
                { name: 'clipboard',   groups: [ 'clipboard', 'undo' ] },
                '/',
                { name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
                { name: 'links' }
            ],*/
            toolbar: '$toolbar',
            width: '$width',
            height: '$height',
        });
        </script>";
    	
    	return $buffer;
    }
    
}
