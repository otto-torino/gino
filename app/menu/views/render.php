<?php
namespace Gino\App\Menu;
/**
 * @file render_mainMenu.php
 * @brief Template visualizzazione menu
 *
 * Variabili disponibili:
 * - **instance_name**: nome istanza menu
 * - **title**: titolo menu
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
    $href = $v['url'] ? "href=\"".$v['url']."\"".($v['type'] == 'ext' ? " rel=\"external\"" : "") : '';
    
    if(!count($v['sub'])) {
		return "<li class=\"".($active ? 'active' : '')."\"><a $href>".$v['label']."</a></li>\n";
    }
    else {
    	/*
    	 * Quando esiste un submenu aggiunge la classe "open" al tag LI principale 
    	 * che avrà le classi dropdown e open.
    	 * Il submenu ha la classe UL.dropdown-menu.
    	 * La voce selezionata ha la classe LI.active.
    	 */
    	$buffer = "
		<script>
		var mngOpen = function(e) {
			$(this).getParent().getChildren('li.open').each(function(item) { if(item != $(this)) item.removeClass('open'); }.bind(this));
		
			if(!($(this).hasClass('open') && e.target.getProperty('class') == 'dropdown-toggle') || (e.target.getParent('li') == this)) {
				$(this).toggleClass('open');
			}
		}
		</script>";
    	
    	$buffer .= "<li class=\"dropdown".($active ? ' active' : '')."\" onclick=\"mngOpen.bind(this)(event);\">";
    	$buffer .= "<div class=\"voice\">";
    	
    	if(!$v['url'] or $v['url'] == '#') {
    		$label_voice = "<a class=\"voice-without-link\">".$v['label']."</a>";
    	}
    	else {
    		$label_voice = "<a $href>".$v['label']."</a>";
    	}
    	$buffer .= $label_voice." <span class=\"dropdown-toggle caret\" data-toggle=\"dropdown\"></span>";
    	$buffer .= "</div>";
    	/*
    	// Bootstrap (voce principale senza link)
    	$buffer .= "<li class=\"dropdown".($active ? ' active' : '')."\" onclick=\"mngOpen.bind(this)(event);\">";
    	$buffer .= "<a $href class=\"dropdown-toggle\" data-toggle=\"dropdown\" role=\"button\" aria-haspopup=\"true\" aria-expanded=\"false\">".$v['label']."
 		<span class=\"caret\"></span></a>";
    	*/
    	
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
<ul class="menu-main nav navbar-nav navbar-left">
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
if($$('ul.menu-main li.active').length) {
	$$('ul.menu-main li.active').getParents('li').each(function(li) {
		li.addClass('active');
	})
}
</script>
<? // @endcond ?>
