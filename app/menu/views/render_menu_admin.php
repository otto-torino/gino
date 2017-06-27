<?php
namespace Gino\App\Menu;
/**
 * @file render.php
 * @brief Template visualizzazione menu
 *
 * Variabili disponibili:
 * - **selected**: id voce selezionata
 * - **tree**: array che contiene il tree delle voci di menu. Ciascuna voce è un array associativo con chiavi:
 *              - id: int, id voce
 *              - sub: array, tree sottovoci (ricorsivo)
 *              - label: string, voce di menu
 *              - type: string, int|ext tipo link,
 *              - url: string, url
 * - **admin_voice**: voce di menu che rimanda all'area amministrativa
 * - **logout_voice**: voce di menu che effettua il logout
 *
 * @copyright 2005-2017 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */
?>
<? //@cond no-doxygen ?>
<?php
if(!function_exists('\Gino\App\Menu\printVoice')) {
	function printVoice($v, $selected) {
    
		$active = $selected == $v['id'] ? true : false;
  	
		if($v['url']) {
			$url = $v['url'];
		} else {
			$url= '#';
		}
		if($v['type'] == 'ext') {
			$ext = "rel=\"external\"";
		} else {
			$ext = '';
		}
    
		if(!count($v['sub'])) {
			return "<li class=\"".($active ? 'active' : '')."\"><a href=\"$url\" $ext>".$v['label']."</a></li>\n";
		}
		else {
			$buffer = "<li class=\"dropdown".($active ? ' active' : '')."\" >";
			$buffer .= "<a href=\"$url\" class=\"dropdown-toggle\" data-toggle=\"dropdown\" role=\"button\" aria-haspopup=\"true\" aria-expanded=\"false\">".$v['label']." 
 			<span class=\"caret\"></span></a>";

			// submenu
			$buffer .= "<ul class=\"dropdown-menu\">\n";
			foreach($v['sub'] as $sv) {
				$buffer .= printVoice($sv, $selected);
			}
			$buffer .= "</ul></li>\n";
			return $buffer;
		}
	}
}
?>
<ul class="nav navbar-nav navbar-right main-menu">
<?php
$i = 0;
foreach($tree as $v) {
	echo printVoice($v, $selected);
	$i++;
}
if($admin_voice) {
	echo "<li><a href=\"$admin_voice\">"._("Amministrazione")."</a></li>\n";
}
if($logout_voice) {
	echo "<li><a href=\"$logout_voice\">"._("Logout")."</a></li>\n";
}
?>
</ul>

<script>
//MooTools
window.addEvent('domready',function() {
    Element.prototype.hide = function() {
        // Do nothing
    };
});

if($$('ul.main-menu li.active').length) {
	$$('ul.main-menu li.active').getParents('li').each(function(li) {
		li.addClass('active');
	})
}
</script>
<? // @endcond ?>
