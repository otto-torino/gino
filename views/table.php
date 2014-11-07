<?php
/**
 * @file table.php
 * @brief Template utilizzato per visualizzare le tabelle
 *
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 * 
 * Available variables:
 * - @b form_start: (string) form header (if there exists)
 * - @b form_end: (string) form closes (if there exists)
 * - @b class: table css class
 * - @b caption: (optional) table caption
 * - @b tr_class: (string) css class of the highlighted item
 * - @b heads: array of table headers, each element may be a string (header text) or an associative array:
 *   - @b class: css class of the th element 
 *   - @b text: header text 
 * - @b rows: array of table rows. Each row is an array of cells. Each cell may be a string (cell text) or an array:
 *   - @b evidence: (bool) the table row uses the class tr_class
 *   - @b header: (bool) is the cell an header? 
 *   - @b colspan: (int) cell colspan attribute 
 *   - @b title: title attribute of the cell 
 *   - @b class: css class of the cell 
 *   - @b text: cell text 
 * - @b foots: table footer, may be an array of texts (one foreach cell), or a string (is displayed in an unique cell with a colspan attribute)
 * 
 */
?>
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
