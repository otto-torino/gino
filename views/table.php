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
 * - @b class: table css class
 * - @b caption: (optional) table caption
 * - @b heads: array of table headers, each element may be a string (header text) or an associative array:
 *   - @b class: css class of the th element 
 *   - @b text: header text 
 * - @b rows: array of table rows. Each row is an array of cells. Each cell may be a string (cell text) or an array:
 *   - @b header: (bool) is the cell an header? 
 *   - @b colspan: (int) cell colspan attribute 
 *   - @b title: title attribute of the cell 
 *   - @b class: css calss of the cell 
 *   - @b text: cell text 
 * - @b foots: table footer, may be an array of texts (one foreach cell), or a string (is displayed in an unique cell with a colspan attribute)
 * 
 */
?>
<table class="<?= $class ?>">
<? if(isset($caption)): ?>
<caption><?= $caption ?></caption>
<? endif ?>
	<thead>
		<tr>
		<?php
			if(isset($heads)) foreach($heads as $h) {
				$class = (is_array($h) && isset($h['class'])) ? " class=\"".$h['class']."\"" : "";
				$text = (is_array($h) && isset($h['text'])) ? $h['text'] : $h;
				echo "<th".$class.">$text</th>";
			}
		?>
		</tr>
	</thead>
	<tbody>
		<?php
			foreach($rows as $row) {
				echo "<tr>\n";
				foreach($row as $cell) {
					$cell_tag = (is_array($cell) && isset($cell['header']) && $cell['header']) ? "th" : "td";
					$cell_colspan = (is_array($cell) && isset($cell['colspan']) && $cell['colspan']) ? " colspan=\"".$cell['colspan']."\"" : "";
					$title = (is_array($cell) && isset($cell['title'])) ? " title=\"".$cell['title']."\"" : '';
					$text = (is_array($cell) && isset($cell['text'])) ? $cell['text'] : $cell;
					if(is_array($cell) && isset($cell['class'])) echo "<$cell_tag$cell_colspan$title class=\"".$cell['class']."\">".$text."</$cell_tag>\n";
					else echo "<$cell_tag>$text</$cell_tag>\n";
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
