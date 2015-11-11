<?php
/**
 * @file render_menu_admin.php
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
 *
 * @copyright 2005-2015 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */
?>
<? namespace Gino\App\Menu; ?>
<? //@cond no-doxygen ?>
<?php
if(!function_exists('\Gino\App\Menu\printVoice')) {
  function printVoice($v, $selected, $i) {
    $active = $selected == $v['id'] ? true : false;
    if(!count($v['sub'])) return "<li class=\"".($active ? 'active' : '')."\"><a href=\"".$v['url']."\"".($v['type'] == 'ext' ? " rel=\"external\"" : "").">".$v['label']."</a></li>\n";
    else {
        $buffer = "<li class=\"dropdown".($active ? ' active' : '')."\" onclick=\"$(this).getParent().getChildren('li.open').each(function(item) { if(item != $(this)) item.removeClass('open'); }.bind(this)); $(this).toggleClass('open');\">";
        $buffer .= "<a class=\"dropdown-toggle\" data-toggle=\"dropdown\" href=\"".$v['url']."\"".($v['type'] == 'ext' ? " rel=\"external\"" : "").">".$v['label']." <span class=\"caret\"></span></a>";
        $buffer .= "<ul class=\"dropdown-menu\">\n";
        foreach($v['sub'] as $sv) $buffer .= printVoice($sv, $selected, null);
        $buffer .= "</ul></li>\n"; 

        return $buffer;
    }
  }
}
?>
<ul class="menu-admin nav navbar-nav navbar-right">
    <?php
    $i = 0;
    foreach($tree as $v) {
      echo printVoice($v, $selected, $i);
      $i++;
    }
    ?>
    <!-- <li><a href="#" style="padding: 8px 15px"><img src="img/ico_home.png" alt="home" /></a></li> -->
</ul>
<? // @endcond ?>

