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
 * @copyright 2005-2018 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */
?>
<? //@cond no-doxygen ?>
<?php
if(!function_exists('\Gino\App\Menu\printVoice')) {
    function printVoice($v, $selected, $dropdown_voice=false) {
        
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
            if($dropdown_voice) {
                return "<a class=\"dropdown-item\" href=\"$url\" $ext>".$v['label']."</a>\n";
            }
            else {
                return "<li class=\"nav-item".($active ? ' active' : '')."\"><a class=\"nav-link\" href=\"$url\" $ext>".$v['label']."</a></li>\n";
            }
        }
        else {
            $dropdown_id = "navbarDropdownMenu".$v['id'];
            $buffer = "<li class=\"nav-item dropdown\" >";
            $buffer .= "<a href=\"$url\" class=\"nav-link dropdown-toggle\" id=\"$dropdown_id\" data-toggle=\"dropdown\" role=\"button\" aria-haspopup=\"true\" aria-expanded=\"false\">";
            $buffer .= $v['label']."</a>";
            
            // submenu
            $buffer .= "<div class=\"dropdown-menu\" aria-labelledby=\"$dropdown_id\">\n";
            foreach($v['sub'] as $sv) {
                $buffer .= printVoice($sv, $selected, true);
            }
            $buffer .= "</div></li>\n";
            return $buffer;
        }
    }
}
?>

<ul class="navbar-nav main-menu">
<?php
$i = 0;
foreach($tree as $v) {
	echo printVoice($v, $selected);
	$i++;
}
if($admin_voice) {
    echo "<li class=\"nav-item\"><a class=\"nav-link\" href=\"$admin_voice\">"._("Amministrazione")."</a></li>\n";
}
if($logout_voice) {
    echo "<li class=\"nav-item\"><a class=\"nav-link\" href=\"$logout_voice\">"._("Logout")."</a></li>\n";
}
?>
</ul>
<? // @endcond ?>
