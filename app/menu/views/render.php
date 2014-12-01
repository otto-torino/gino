<?php
/**
 * @file render.php
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
 * @copyright 2005-2014 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */
?>
<? namespace Gino.App.Menu; ?>
<? //@cond no-doxygen ?>
<?php
if(!function_exists('adminPrintVoice')) {
  function printVoice($v, $selected, $i) {

    $class = $selected == $v['id'] ? " class=\"selected\"" : "";
    if(!count($v['sub'])) return "<li".$class."><a href=\"".$v['url']."\"".($v['type'] == 'ext' ? " rel=\"external\"" : "")."$class>".$v['label']."</a></li>\n";
    else {
      $buffer = "<li".$class."><a href=\"".$v['url']."\"".($v['type'] == 'ext' ? " rel=\"external\"" : "")."$class>".$v['label']."</a><ul>\n";
      foreach($v['sub'] as $sv) $buffer .= printVoice($sv, $selected, null);
      $buffer .= "</ul></li>\n";

      return $buffer;
    }
  }
}
?>
<ul class="menu-main nav navbar-nav navbar-right">
    <?php
    $i = 0;
    foreach($tree as $v) {
      echo printVoice($v, $selected, $i);
      $i++;
    }
    ?>
    <li><a href="#" style="padding: 8px 15px"><img src="img/ico_home.png" alt="home" /></a></li>
</ul>
<? // @endcond ?>
