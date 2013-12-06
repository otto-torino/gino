<?php
/**
 * Stampa il menu
 * Variabili disponibili:
 * $title (string): titolo del menu
 * $instance_name (string): nome dell'istanza del menu
 * $selected (int): id della voce selezionata
 * $tree (array): tree delle voci di menu. array di voci, ciascuna delle quali è un array associativo con chiavi=>valori:
 *   id (int): id della voce
 *   type (int|ext): link interno o esterno
 *   label (string): nome della voce di menu
 *   url (string): url della voce di menu
 *   sub (array): array di voci figlie. queste voci sono array associativi con le stesse identiche chiavi
 */
if(!function_exists('printVoice')) {
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
  <nav id="menu_<?= $instance_name ?>">
  <h1 class="hidden"><?= $title ?></h1>
  <ul>
  <?php
    $i = 0;
    foreach($tree as $v) {
      echo printVoice($v, $selected, $i);		
      $i++;
    }
  ?>
  </ul>
</nav>
