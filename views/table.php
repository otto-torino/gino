<?php
namespace Gino;
/**
 * @file table.php
 * @brief Template utilizzato per visualizzare le tabelle
 *
 * @copyright 2013-2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 * 
 * Variabili disponibili:
 * - @b form_start: (string) apertura form (se presente)
 * - @b form_end: (string) chiusura form (se presente)
 * - @b class: classe css della tabella
 * - @b caption: (string) caption, opzionale
 * - @b tr_class: (string) classe css di una riga evidenziata
 * - @b multiple_heads: (bool) molteplici righe di intestazione
 * - @b heads: (array) array di intestazioni o array di array di intestazioni (multiple_heads), 
 *                     ciascun elemento può essere una stringa (testo intestazione) o un array associativo:
 *                      - @b class: classe css da applicare al tag th
 *                      - @b text: testo intestazione
 * - @b rows: (array) array di righe di tabella. Ciascuna riga è una array di celle. 
 *                    Ciascuna cella può essere una stringa (testo della cella) o un array associativo:
 *                      - @b evidence: (bool) se TRUE la riga usa la classe tr_class
 *                      - @b header: (bool) se TRUE la cella è da considerarsi intestazione (th)
 *                      - @b colspan: (int) attributo colspan
 *                      - @b title: (string) attributo title
 *                      - @b class: classe css della cella
 *                      - @b text: testo della cella
 * - @b foots: (string|array) table footer, può essere un array di testi (uno per ogni cella), o una stringa (unica cella con colspan adatto)
 *
 */
?>
<? //@cond no-doxygen ?>
<? if(isset($form_start)) echo $form_start; ?>
<table class="<?= $class ?>"<?= isset($id) ? " id=\"".$id."\"" : '' ?>>
<? if(isset($caption) and $caption): ?>
<caption><?= $caption ?></caption>
<? endif ?>
    <thead>
        <?php
        if(isset($heads) && (!isset($multiple_heads) or !$multiple_heads)) {
            echo "<tr>";
            foreach($heads as $h) {
                $class = (is_array($h) && isset($h['class'])) ? " class=\"".$h['class']."\"" : "";
                $text = (is_array($h) && isset($h['text'])) ? $h['text'] : $h;
                echo "<th".$class.">$text</th>";
            }
            echo "</tr>";
        }
        if(isset($heads) && isset($multiple_heads) && $multiple_heads) {
            foreach($heads as $rh) {
                echo "<tr>";
                    foreach($rh as $h) {
                        $cell_colspan = (is_array($h) && isset($h['colspan']) && $h['colspan']) ? " colspan=\"".$h['colspan']."\"" : "";
                        $class = (is_array($h) && isset($h['class'])) ? " class=\"".$h['class']."\"" : "";
                        $text = (is_array($h) && isset($h['text'])) ? $h['text'] : $h;
                        echo "<th$cell_colspan".$class.">$text</th>";
                    }
                echo "</tr>";
            }
        }
        ?>
    </thead>
    <tbody>
        <?php
            foreach($rows as $row) {
                if(isset($row['evidence']) && $row['evidence'] && $tr_class)
                {
                    $selected = "class=\"".$tr_class."\"";
                    unset($row['evidence']);
                }
                else $selected = '';
                echo "<tr $selected>\n";
                foreach($row as $cell) {
                    $cell_tag = (is_array($cell) && isset($cell['header']) && $cell['header']) ? "th" : "td";
                    $cell_colspan = (is_array($cell) && isset($cell['colspan']) && $cell['colspan']) ? " colspan=\"".$cell['colspan']."\"" : "";
                    $cell_rowspan = (is_array($cell) && isset($cell['rowspan']) && $cell['rowspan']) ? " rowspan=\"".$cell['rowspan']."\"" : "";
                    $title = (is_array($cell) && isset($cell['title'])) ? " title=\"".$cell['title']."\"" : '';
                    $text = (is_array($cell) && isset($cell['text'])) ? $cell['text'] : $cell;
                    if(is_array($cell) && isset($cell['class'])) echo "<$cell_tag$cell_rowspan$cell_colspan$title class=\"".$cell['class']."\">".$text."</$cell_tag>\n";
                    else echo "<$cell_tag$cell_rowspan$cell_colspan$title>$text</$cell_tag>\n";
                }
                echo "</tr>\n";
            }
        ?>
    </tbody>
    <tfoot>
        <tr>
        <?php
            if(isset($foots) && is_array($foots)) foreach($foots as $f) echo "<td>$f</td>";
            elseif(isset($foots)) echo "<td colspan=\"".count($rows[0])."\">$foots</td>";
        ?>
        </tr>
    </tfoot>
</table>
<? if(isset($form_end)) echo $form_end; ?>
<? // @endcond ?>
