<?php
/**
 * @file class.Javascript.php
 * @brief Contiene la definizione ed implementazione della classe Gino.Javascript
 * 
 * @copyright 2005-2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

/**
 * @brief Contiene i metodi per includere alcuni javascript
 * 
 * @copyright 2005-2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class Javascript {

    /**
     * @brief Include il file javascript con la libreria delle mappe
     * @return codice html
     */
    public static function abiMapLib() {

        $buffer = "<script type=\"text/javascript\" src=\"".SITE_JS."/abiMap.js\"></script>\n";
        return $buffer;
    }

    /**
     * @brief Include il file javascript con la libreria slimbox
     * @return codice html
     */
    public static function slimboxLib() {

        $buffer = "<script type=\"text/javascript\" src=\"".SITE_JS."/slimbox.js\"></script>\n";
        return $buffer;
    }

    /**
     * @brief Funzioni javascript caricate all'interno della sezione HEAD dell'html
     * @param \Gino\Skin $skinObj istanza di Gino.Skin associata alla pagina
     * @return codice html
     */
    public static function onLoadFunction($skinObj = null) {

        $buffer = "<script type=\"text/javascript\">\n";

        $buffer .= "function updateTooltips() {

                $$('*[class$=tooltipfull]').each(function(el) {
                    if(el.getProperty('title')) {
                        var title = el.getProperty('title').split('::')[0];
                        var text = el.getProperty('title').split('::')[1];

                        el.store('tip:title', title);
                        el.store('tip:text', text);
                    }
                });

                var myTips = new Tips('[class$=tooltip]', {className: 'tipsbase'});

                var myTipsFull = new Tips('[class$=tooltipfull]', {
                    className: 'tipsfull',
                    hideDelay: 50,
                    showDelay: 50
                });

            }";

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
            updateTooltips();
            externalLinks();
        };\n";

        $buffer .= "function createScriptElement(src) {\n
                var element = document.createElement(\"script\");\n
                     element.src = src;\n
                    document.body.appendChild(element);\n
            }\n";
        $buffer .= "function onLoadFunction() {\n
                createScriptElement('".SITE_JS."/slimbox.js');
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
     * @return codice html
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
     * @brief Codice html inclusione libreriua sharethis (sharethis.com)
     * @return codice html
     */
    private static function sharethis($key) {

        $buffer = "<script type=\"text/javascript\">var switchTo5x=true;</script>\n";
        $buffer .= "<script type=\"text/javascript\" src=\"http://w.sharethis.com/button/buttons.js\"></script>\n";
        $buffer .= "<script type=\"text/javascript\">stLight.options({publisher: \"".$key."\", doNotHash: false, doNotCopy: false, hashAddressBar: false});</script>\n";

        return $buffer;
    }

    /**
     * @brief Include il codice per attivare google analytics
     * @return codice html
     */
    public static function analytics() {

        $buffer = '';

        $registry = registry::instance();
        if($registry->sysconf->google_analytics) {
            $buffer = "<script type=\"text/javascript\">";
            $buffer .= "var _gaq = _gaq || [];";
            $buffer .= "_gaq.push(['_setAccount', '".$registry->sysconf->google_analytics."']);";
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
