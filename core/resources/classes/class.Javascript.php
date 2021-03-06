<?php
/**
 * @file class.Javascript.php
 * @brief Contiene la definizione ed implementazione della classe Gino.Javascript
 */
namespace Gino;

/**
 * @brief Contiene i metodi per includere alcuni javascript
 */
class Javascript {

    /**
     * @brief Include il file javascript con la libreria delle mappe
     * @return string
     */
    public static function abiMapLib() {

        $buffer = "<script type=\"text/javascript\" src=\"".SITE_LIBRARIES."/Maps/abiMap.js\"></script>\n";
        return $buffer;
    }
    
    /**
     * @brief Script per la conversione di un indirizzo in longitudine/latitudine
     * @param array $options
     *   - @b button_id (string): valore id del bottone (default map_coord)
     *   - @b map_id (string): valore id della mappa (default map_address)
     * @return string
     */
    public static function scriptConvertAddress($options=array()) {
    	
    	$button_id = gOpt('button_id', $options, 'map_coord');
    	$map_id = gOpt('map_id', $options, 'map_address');
    	
    	$buffer = self::abiMapLib();
    	$buffer .= "<script type=\"text/javascript\">";
    	$buffer .= "function convert() {
			var addressConverter = new AddressToPointConverter('".$button_id."', 'lat', 'lng', $('".$map_id."').value, {'canvasPosition':'over'});
        	addressConverter.showMap();
        }\n";
    	$buffer .= "</script>";
    	
    	return $buffer;
    }
    
    /**
     * @brief Input localizzazione
     * @param array $options
     *   - @b button_id (string): valore id del bottone (default map_coord)
     *   - @b map_id (string): valore id della mappa (default map_address)
     *   - @b map_key (string): Google Map Key
     *   - @b label (string|array): label dell'input form
     * @return string
     */
    public static function inputConvertAddress($options=array()) {
    	
    	$button_id = gOpt('button_id', $options, 'map_coord');
    	$map_id = gOpt('map_id', $options, 'map_address');
    	$map_key = gOpt('map_key', $options, GOOGLE_MAPS_KEY);
    	$label = gOpt('label', $options, array(_("Indirizzo localizzazione"), _("es: torino, piazza castello<br />utilizzare 'converti' per calcolare latitudine e longitudine")));
    	
    	$gmk = $map_key ? "key=".$map_key."&" : '';
    	
    	$onclick = "onclick=\"Asset.javascript('https://maps.google.com/maps/api/js?".$gmk."sensor=true&callback=convert')\"";
    	
    	$convert_button = \Gino\Input::input($button_id, 'button', _("converti"), array("id" => $button_id, "classField" => "btn btn-outline-primary", "js" => $onclick));
    	
    	$input = \Gino\Input::input_label($map_id, 'text', '', $label,
    		array("size" => 40, "maxlength" => 200, "id" => $map_id, "text_add" => "<p>".$convert_button."</p>"));
    	
    	return $input;
    }

    /**
     * @brief Funzioni javascript caricate all'interno della sezione HEAD dell'html
     * @param \Gino\Skin $skinObj istanza di Gino.Skin associata alla pagina
     * @return string
     */
    public static function onLoadFunction($skinObj = null) {

        $buffer = "<script type=\"text/javascript\">\n";

        $buffer .= "function externalLinks() {
            if (!document.getElementsByTagName) return;
            var anchors = document.getElementsByTagName('a');
            for (var i=0; i<anchors.length; i++) {
                var anchor = anchors[i];
                if (anchor.getAttribute('href') && anchor.getAttribute('rel') == 'external') {
                    anchor.target = '_blank';
                    if (!anchor.title) anchor.title = '"._("Il link apre una nuova finestra")."';
                }
                else if (anchor.getAttribute('href') && anchor.getAttribute('href').match(/^#top/)) {
                    var attrvalue = anchor.getAttribute('href');
                    anchor.addEvent('click', function(){document.location.hash=attrvalue});
                    anchor.setAttribute('href', 'javascript:;');
                }
            }
        };\n";

        $buffer .= "function parseFunctions() {
            externalLinks();
        };\n";

        $buffer .= "function createScriptElement(src) {\n
                var element = document.createElement(\"script\");\n
        		element.src = src;\n
        		document.body.appendChild(element);\n
        }\n";
        $buffer .= "function onLoadFunction() {\n
                parseFunctions();\n";
        $buffer .= "}\n";
        $buffer .= "if (window.addEventListener)\n
        		window.addEventListener(\"load\", onLoadFunction, false);\n
        		else if (window.attachEvent)\n
        		window.attachEvent(\"onload\", onLoadFunction);\n
        		else window.onload = onLoadFunction;\n";
        
        $buffer .= "</script>\n";

        return $buffer;
    }

    /**
     * @brief Include librerie js di terze parti
     * @return string
     */
    public static function vendor() {
        $buffer = '';

        $registry = registry::instance();
        if($registry->sysconf->sharethis_public_key) {
            $buffer .= self::sharethis($registry->sysconf->sharethis_public_key);
        }

        return $buffer;
    }

    /**
     * @brief Codice html inclusione libreria sharethis (sharethis.com)
     * @return string
     */
    private static function sharethis($key) {

        $buffer = "<script type=\"text/javascript\">var switchTo5x=true;</script>\n";
        $buffer .= "<script type=\"text/javascript\" src=\"https://ws.sharethis.com/button/buttons.js\"></script>\n";
        $buffer .= "<script type=\"text/javascript\">stLight.options({publisher: \"".$key."\", doNotHash: false, doNotCopy: false, hashAddressBar: false});</script>\n";

        return $buffer;
    }

    /**
     * @brief Include il codice per attivare google analytics
     * @return string
     */
    public static function analytics() {

        $buffer = '';

        $registry = registry::instance();
        if($registry->sysconf->google_analytics) {
            $buffer = "<script type=\"text/javascript\">";
            $buffer .= "var _gaq = _gaq || [];";
            $buffer .= "_gaq.push(['_setAccount', '".$registry->sysconf->google_analytics."']);";
            $buffer .= "_gaq.push(['_gat._anonymizeIp']);";
            $buffer .= "_gaq.push(['_trackPageview']);";
            $buffer .= "(function() {
                var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
                ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
                var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
            })();";
            $buffer .= "</script>";
        }

        return $buffer;
    }
}
