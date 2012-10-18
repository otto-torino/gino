<?php
/**
 * @file class.javascript.php
 * @brief Contiene la classe javascript
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

/**
 * @brief Contiene i metodi per includere alcuni javascript
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class javascript {

	/**
	 * Include il file javascript con la libreria delle mappe
	 * 
	 * @return string
	 */
	public static function abiMapLib() {
		
		$buffer = "<script type=\"text/javascript\" src=\"".SITE_JS."/abiMap.js\"></script>\n";
		return $buffer;
	}

	/**
	 * Include il file javascript con la libreria slimbox
	 * 
	 * @return string
	 */
	public static function slimboxLib() {
		
		$buffer = "<script type=\"text/javascript\" src=\"".SITE_JS."/slimbox.js\"></script>\n";
		return $buffer;
	}

	/**
	 * Funzioni javascript caricate all'interno della sezione HEAD dell'html
	 * 
	 * @param object $skinObj skin associata alla pagina
	 * @return string
	 */
	public static function onLoadFunction($skinObj) {

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
}

?>
