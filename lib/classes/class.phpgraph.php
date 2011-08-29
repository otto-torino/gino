<?php
/**
 * Inserire i metodi di creazione di grafici direttamente nella classe phpgraph 
 * 
 * Esempio:

 	public function createGraph(){
		
		$year = cleanVar($_GET, 'year', 'int', '');
		if(empty($year)) return '';
		
		$data1 = $data2 = $data3 = array();
		$month = $this->monthList();
		foreach($month AS $key=>$value)
		{
			$data1[$value] = $this->monthData1($key, $year);
			$data2[$value] = $this->monthData2($key, $year);
			$data3[$value] = $this->monthData3($key, $year);
		}
		// End
		
		$graph = new PHPGraphLib(700,400);    // !! N.B.
		$graph->addData($data1, $data2, $data3);
		
		// Istogrammi
		$graph->setBarColor("navy", "green", "maroon");
		//$graph->setGradient("green", "olive");
		
		// Linee
		$graph->setBars(false);
		$graph->setLine(true);
		$graph->setLineColor("navy", "green", "maroon");
		
		// Assi
		$graph->setupYAxis(12, "black");
		$graph->setupXAxis(20);
		//$graph->setXValuesHorizontal(true);
		$graph->setTextColor("black");
		
		// Data Point
		$graph->setDataPoints(true);
		$graph->setDataPointColor("navy");
		$graph->setDataPointSize(4);	// default: 6
		$graph->setDataValues(false);
		$graph->setDataValueColor("navy");
		//$graph->setGoalLine(10000);
		//$graph->setGoalLineColor("red");
		
		// Grid
		$graph->setGrid(false);
		
		// Title
		$graph->setTitle("Schema generale $year");
		$graph->setTitleLocation("left");
		$graph->setTitleColor("black");
		
		// Legend
		$graph->setLegend(true);
		$graph->setLegendOutlineColor("white");
		$graph->setLegendTitle("po", "acquisti", "vendite");
		
		$graph->createGraph();
	}
 */

class phpgraph {

	function __construct($file=null) {
		
		if(is_null($file)) $file = 'phpgraphlib.php';
		include_once(LIB_DIR.OS.'phpgraphlib'.OS.$file);
	}
	
	public function monthList(){
		
		$month = array('01'=>_("Gen"), '02'=>_("Feb"), '03'=>_("Mar"), '04'=>_("Apr"), '05'=>_("Mag"), '06'=>_("Giu"), '07'=>_("Lug"), '08'=>_("Ago"), '09'=>_("Set"), '10'=>_("Ott"), '11'=>_("Nov"), '12'=>_("Dic"));
		return $month;
	}
	
	public function yearList($start=null, $end=null){
		
		if(is_null($start)) $start = date("Y");
		if(is_null($end)) $end = $start+10;
		
		$a_year = array();
		for($i=2008, $end=2020; $i<$end; $i++)
		{
			$a_year[$i] = $i;
		}
		return $a_year;
	}
}
?>
