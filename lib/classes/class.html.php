<?php

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
