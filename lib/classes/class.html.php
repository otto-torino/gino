<?php
/**
 * @file class.html.php
 * @brief Contiene la classe html
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

/**
 * @brief Contenitore per sopperire alla mancanza di template
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class html {

	public static function separator() {
	
		$buffer = "<div class=\"separator\">";
		$buffer .= "<div class=\"liLineLeft\"></div>";
		$buffer .= "<div class=\"liLineCenter\"></div>";
		$buffer .= "<div class=\"liLineRight\"></div>";
		$buffer .= "<div class=\"null\"></div>";
		$buffer .= "</div>\n";

		return $buffer;
	}
}

?>
