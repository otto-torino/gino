<?php
/**
 * @file plugin.charts.php
 * @brief Contiene la classe plugin_charts
 * 
 * @copyright 2017-2019 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

/**
 * @namespace Gino.Plugin
 * @description Namespace che comprende classi di tipo plugin
 */
namespace Gino\Plugin;

/**
 * @brief Interfaccia alla libreria Google Chart Tools
 * 
 * @see https://developers.google.com/chart/
 * @see https://developers.google.com/chart/interactive/docs/gallery/piechart#example
 * @see https://developers.google.com/chart/interactive/docs/gallery/areachart#configuration-options
 * 
 * @copyright 2017-2019 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 * 
 * ##DESCRIZIONE
 * ---------------
 * Sono gestiti soltanto due tipo di grafico, a linea e a torta.
 * 
 * ##MODI DI UTILIZZO
 * ---------------
 * I valori che compongono i grafici devono essere dei numeri.
 * 
 * Esempio di grafico a torta:
 * @code
 * $columns = array();
 * $columns[] = array('string', 'Polls');
 * $columns[] = array('number', 'Answers');
 * 
 * $rows = array();
 * foreach ($answers as $answer) {
 *   $rows[] = array($answer->name, $answer->choices);
 * }
 * 
 * require_once PLUGIN_DIR.OS.'plugin.charts.php';
 * $chart = new \Gino\Plugin\plugin_charts();
 * 
 * $chart_js = $chart->gChartJs($columns, $rows, ['chart_type' => 'pie']);
 * $chart_div = $chart->gChartShow();
 * @endcode
 * 
 * ##COLONNE DEL GRAFICO
 * ---------------
 * Le colonne devono essere definite nel seguente formato: \n
 * @code
 * $columns = array(array(datatype, name)[, ])
 * @endcode
 * dove @a datatype può assumere i valori [string|number] e dove il primo elemento identifica il grafico. \n
 * 
 * Esempio per un grafico a linea: 
 * @code
 * data.addColumn('string', 'Temperature');
 * data.addColumn('number', 'Jan');		// first line (the data type is a number)
 * data.addColumn('number', 'Feb');		// second line (the data type is a number)
 * @endcode
 * 
 * Esempio per un grafico a torta: 
 * @code
 * data.addColumn('string', 'Task');
 * data.addColumn('number', 'Hours per Day');
 * @endcode
 * 
 * ##RIGHE DEL GRAFICO
 * ---------------
 * Le righe devono essere definite in uno dei seguenti formati: \n
 * @code
 * // line chart
 * $columns = array(array(value_x_axis, value_col1, value_col2, ...)[, ])
 * // pie chart
 * $columns = array(array(label_item, value_item)[, ])
 * @endcode
 * 
 * Esempio per un grafico a linea, dove ogni riga riporta il riferimento all'asse delle x e i valori delle due linee indicate nelle colonne: 
 * @code
 * data.addRows([
 *   ['4h', -5, 10],
 *   ['5h', 0,  11],
 *   ['6h', 7,  11],
 *   ['7h', 10, 12],
 *   ['8h', 10, 12],
 *   ['10h', 11, 13],
 *   ['11h', 12, 14],
 *   ['12h', 14, 15]
 * ]);
 * @endcode
 * 
 * Esempio per un grafico a torta, dove ogni riga è un elemento: 
 * @code
 * data.addRows([
 *   ['Jan', 10],
 *   ['Feb', 2],
 *   ['Mar', 3],
 *   ['Apr', 5],
 *   ['May', 3]
 * ]);
 * @endcode
 */
class plugin_charts {

    /**
	 * @brief Valore id del tag del contenuto
	 * @var string
	 */
	private $_id;
	
	/**
	 * Constructor
	 * 
	 * @param array $options array associativo di opzioni
	 *   - @b id (string): valore id del tag del contenuto (default draw_chart)
	 * @return void
	 */
	function __construct($options=array()) {
		
		$this->_id = \Gino\gOpt('id', $options, 'draw_chart');
	}
	
	public function setContentId($value) {
	    
	    if(is_string($value)) {
	        $this->_id = $value;
	    }
	}
	
	public function loader() {
	    
	    $src = "https://www.gstatic.com/charts/loader.js";
	    return "<script type=\"text/javascript\" src=\"$src\"></script>";
	}
	
	/**
	 * @brief Script per generare i grafici
	 * 
	 * @param array $graphs array associativo di opzioni nella forma [function_name => gChartJs()]
	 * @return string
	 */
	public function script($graphs=[]) {
	    
	    $buffer = '';
	    
	    if(count($graphs)) {
	        
	        $buffer = "
            <script type=\"text/javascript\">
	        
            // Load Charts and the corechart package.
            google.charts.load('current', {'packages':['corechart']});
            ";
	        
	        foreach ($graphs as $function => $graph) {
	            
	            // Draw the pie chart and bar chart when Charts is loaded.
	            $buffer .= "google.charts.setOnLoadCallback(".$function.");\n";
	            
	            $buffer .= "
                function ".$function."()
                {\n";
	            $buffer .= $graph;
	            $buffer .= "}\n";
	        }
	        $buffer .= "</script>";
	    }
	    
	    return $buffer;
	}
	
	/**
	 * @brief Definisce la funzione di un grafico
	 * 
	 * @param array $columns
	 * @param array $rows
	 * @param array $options
	 *   - @b chart_type (string): tipo di grafico
	 *     - @a line, grafico a linee (default)
	 *     - @a pie, grafico a torta
	 *   - @b format_values (array): formato degli elementi di una riga (default ['string', 'int'])
	 *   - @b div_id (string): valore id del tag del contenuti (sovrascrive il valore definito nel costruttore)
	 *   - @b width (integer): default 500
	 *   - @b height (integer): default 240
	 *   - @b legend_position (string): posizionamento della legenda; accetta i valori 'none', 'left', 'right' (default)
	 *   - @b title (string): titolo del grafico
	 *   - @b title_font_size (integer): dimensione del font del titolo (default 14)
	 *   - @b colors (string): elenco personalizzato dei colori; ad esempio: "'#e0440e', '#e6693e', '#ec8f6e', '#f3b49f', '#f6c7b6'"
	 *   - @b is_3d (string): effetto 3D (default 'false')
	 *   - @b line_width (string): spessore delle linee
	 *   - @b background_color (string): colore di sfondo del grafico (default '#fff')
	 *   - @b h_axis (string): parametri dell'asse x, ad esempio: "title: 'Year',  titleTextStyle: {color: '#333'}, maxValue: 7"
	 *   - @b v_axis (string): parametri dell'asse y, ad esempio: "maxValue: 13, minValue: 0"
	 * @return string
	 */
	public function gChartJs($columns, $rows, $options=array()) {
	    
	    $chart_type = \Gino\gOpt('chart_type', $options, 'line');
	    $format_values = \Gino\gOpt('format_values', $options, ['string', 'int']);
	    $div_id = \Gino\gOpt('div_id', $options, $this->_id);
	    $width = \Gino\gOpt('width', $options, 500);
	    $height = \Gino\gOpt('height', $options, 240);
	    $legend_position = \Gino\gOpt('legend_position', $options, 'right');
	    $title = \Gino\gOpt('title', $options, '');
	    $title_font_size = \Gino\gOpt('title_font_size', $options, 14);
	    $colors = \Gino\gOpt('colors', $options, null);
	    $is_3d = \Gino\gOpt('is_3d', $options, 'false');
	    $line_width = \Gino\gOpt('line_width', $options, null);
	    $background_color = \Gino\gOpt('background_color', $options, '#fff');
	    $h_axis = \Gino\gOpt('h_axis', $options, null);
	    $v_axis = \Gino\gOpt('v_axis', $options, null);
	    
	    if($chart_type == 'line') {
	        $visualization = 'LineChart';
	    }
	    elseif ($chart_type == 'pie') {
	        $visualization = 'PieChart';
	    }
	    
	    $buffer = $this->formatDataTable($columns, $rows, $format_values);
	    
	    // options chart
	    $buffer .= "
		var options = {
			width: ".$width.",
			height: ".$height.",
			is3D: ".$is_3d.",
			legend: { position: '".$legend_position."' },
			legendBackgroundColor: {stroke:'black', fill:'#eee', strokeSize: 1},
			title: '".$title."',
			backgroundColor: '".$background_color."',
			titleFontSize: ".$title_font_size.",";
	    
	    if($colors) {
	        $buffer .= "colors: [".$colors."],";
	    }
	    if($line_width) {
	        $buffer .= "lineWidth: ".$line_width.",";
	    }
	    if($h_axis) {
	        $buffer .= "hAxis: {".$h_axis."},";
	    }
	    if($v_axis) {
	        $buffer .= "vAxis: {".$v_axis."},";
	    }
	    
	    $buffer .= "};";
	    // /options chart
	    
	    $buffer .= "
		var chart = new google.visualization.".$visualization."(document.getElementById('".$div_id."'));
		chart.draw(data, options);
        ";
	    
	    return $buffer;
	}
	
	/**
	 * @brief Formatta i dati del grafico
	 * 
	 * @param array $columns
	 * @param array $rows
	 * @param array $format_values formato degli elementi di una riga
	 * @return string
	 */
	private function formatDataTable($columns, $rows, $format_values) {
	    
	    $buffer = "var data = new google.visualization.DataTable();\n";
		
		foreach($columns AS $array)
		{
			$buffer .= "data.addColumn(";
			if(count($array)) {
			    foreach ($array as $a) {
			        $buffer .= "'".$a."',";
			    }
			}
			$buffer .= ");\n";
		}
		$buffer .= "data.addRows([";
		foreach($rows AS $array)
		{
			$buffer .= "[";
			if(count($array)) {
			    $i = 0;
			    foreach ($array as $a) {
			        
			        if(array_key_exists($i, $format_values) && $format_values[$i] == 'string') {
			            $buffer .= "'".$a."',";
			        }
			        else {
			            $buffer .= $a.",";
			        }
			        $i++;
			    }
			}
			$buffer .= "],\n";
		}
		$buffer .= "]);\n";
		
		return $buffer;
	}
	
	/**
	 * @brief Contenitore del grafico
	 * 
	 * @param array $options array associativo di opzioni
	 *   - @b div_id (string): valore id del contenitore (sovrascrive il valore definito nel costruttore)
	 *   - @b title (string)
	 * @return string
	 */
	public function gChartShow($options=[]) {
		
		$div_id = \Gino\gOpt('div_id', $options, $this->_id);
		$title = \Gino\gOpt('title', $options, null);
		
		$buffer = '';
		if($title) {
		    $buffer .= "<div class=\"title_graph\">$title</div>";
		}
		$buffer .= "<div id=\"".$div_id."\"></div>";
		
		return $buffer;
	}
}
?>
