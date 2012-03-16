<?php
/*----------------------------------------------------------------------

CLASS NAME:  ORDINAMENTO IN LISTE
LANGUAGE:    PHP5
DATE:        04/08/2004

condizioni per l'utilizzo:
1. in input -> query che ricava il numero totale di elementi
(SELECT field FROM table WHERE condition)
2. in output -> la query che recupera gli elementi deve avere
le istruzioni "LIMIT $this->start(), $this->rangeNumber"
(inizio, intervallo).
----------------------------------------------------------------------*/

class PageList{

	public $rangeNumber;

	private $_db;
	private $_actual;
	private $_last;
	private $_first;
	private $_start;
	private $_tot;
	private $_items_for_page, $_permalink_primary;
	private $_more;
	private $_less;
	
	/*
	 * Numero di pagine adiacenti a quella corrente visualizzate come link (escluse la prima e l'ultima)
	 */
	private $_vpage_num;
	
	private $_filename, $_url, $_variables, $_symbol;

	private $_ico_less, $_ico_more;

	// parametri chiamate ajax
	private $_ajax, $_postvar, $_ref_id, $_load_id, $_script, $_callback, $_cbparams;
	
	/**
	 * 
	 * @param int		$items_for_page
	 * @param string	$queryTot
	 * @param string	$type
	 * @param array		$options:
	 * 			permalink_primary [boolean]: indica se il parametro 'start' deve essere gestito come parametro primario con i permalink
	 */
	function __construct($items_for_page, $queryTot, $type, $options=array()) {
		
		$permalink_primary = array_key_exists('permalink_primary', $options) ? $options['permalink_primary'] : false;
		
		$start = $this->start();
		$this->_items_for_page = $items_for_page;
		$this->rangeNumber = $this->_items_for_page;
		$this->_permalink_primary = $permalink_primary;

		$this->_db = db::instance();
		
		$this->_vpage_num = 1;
		$this->_more = 0;
		$this->_less = 0;
		$this->_first = 1;
		
		$this->_start = $start+1;
		$this->_actual = ceil($this->_start / $this->_items_for_page);
		
		if($type=='array') {
			$this->_tot = is_array($queryTot) ? sizeof($queryTot):$queryTot;
			$this->_last = ceil($this->_tot/$this->_items_for_page);
		}
		else {
			$a = $this->_db->selectQuery($queryTot);
			if(!$a) {
				$this->_last = 1;
				$this->_tot = 0;
			}
			else
			{
				$this->_tot = sizeof($a);
				$this->_last = ceil($this->_tot / $this->_items_for_page);
			}
		}

		$this->_ico_more = "<img style=\"margin-bottom:3px\" src=\"img/plist_dx.gif\" alt=\">>\" />";
		$this->_ico_less = "<img style=\"margin-bottom:3px\" src=\"img/plist_sx.gif\" alt=\"<<\" />";

	}
	
	public function start()
	{
		$start = cleanVar($_REQUEST, 'start', 'int', '');
		
		return $start<0?0:$start;
	}
	
	public function reassumedPrint()
	{
		$printTBL = '';

		if($this->_tot > 0)
		{
			$end = $this->_start+$this->_items_for_page - 1;
			if($end > $this->_tot) $end = $this->_tot;
			$printTBL .= $this->_start.' - '.$end.' '._("di").' '.$this->_tot."\n";
		}
		
		return $printTBL;
	}

	private function pageLink($label, $params, $link=true, $opt=null) {

		if(!$this->_ajax && $link)
		{
			if($params != '')
			{
				$plink = new Link();
				$secondary = $this->_permalink_primary ? false : true;
				$url = $plink->addParams($this->_variables, $params, $secondary);
				if(pub::variable('permalinks') == 'no')
					$url = $this->_filename."?".$url;
			}
			return "<a href=\"$url\">".$label."</a>";	// OLD: href=\"".$this->_url.$this->_symbol."$params\"
		}
		elseif(!$this->_ajax && !$link) return $label;
		else {
			if(!$link) return $label;
			else {
				$this->_url = $this->_filename."?".$this->_variables;
				
				if($this->_postvar) $params = $this->_postvar."&".$params;
				$onclick = "ajaxRequest('post', '".$this->_url."', '$params', '$this->_ref_id', {'load':'$this->_load_id', 'script':$this->_script, 'cache':".((isset($opt['cache']) && $opt['cache'])?'true':'false').($this->_callback ? ", callback:".$this->_callback:"").($this->_cbparams ? ", callback_params:".$this->_cbparams:"")."})";
				$GINO = "<span class=\"link\" onclick=\"$onclick\">".$label."</span>";
				return $GINO;
			}	
		}
	}

	public function listReferenceGINO($variables, $ajax=false, $postvar='', $ref_id='', $load_id='', $script=false, $callback=null, $cbparams=null, $opt=null) {
		
		$this->_filename = basename($_SERVER['PHP_SELF']);
		$this->_variables = $variables;
		$this->_symbol = $variables ? '&' : '';

		$this->_postvar = '';
		if(is_array($postvar)) {
			foreach($postvar as $k=>$v) {
				if(is_array($v)) 
					foreach($v as $vv) $this->_postvar .= "&".$k."[]=".$vv;
				else $this->_postvar .= "&$k=".addslashes($v);
			}
			$this->_postvar = substr($this->_postvar, 1);
		}
		else $this->_postvar = $postvar;

		$this->_ajax = $ajax;
		$this->_ref_id = $ref_id;
		$this->_load_id = $load_id;
		if($script) $this->_script = 'true';
		else $this->_script = 'false';
		$this->_callback = $callback;
		$this->_cbparams = $cbparams;

		$BUFFER = "";
		$LOWPART = "";
		$HIGHPART = "";
		
		$BUFFER = "<div class=\"area_link\">\n";
		
		if($this->_last == 1) return "";
				
		for($i=$this->_actual; $i>1; $i--) {
			if($i == $this->_last) $LOWPART .= "";
			elseif($i == $this->_actual) $LOWPART = "<span class=\"pagelist_selected\">".$this->pageLink($i, "start=".($i-1)*$this->_items_for_page, false, $opt)."</span>".$LOWPART;
			elseif($i>$this->_actual - $this->_vpage_num - 1) $LOWPART = "<span class=\"pagelist\">".$this->pageLink($i, "start=".($i-1)*$this->_items_for_page, true, $opt)."</span>".$LOWPART;
			else $this->_less = 1;
		}
		if($this->_less) $LOWPART = "<span class=\"pagelistdots\">...</span>".$LOWPART;
		
		for($i=$this->_actual+1; $i<$this->_last; $i++) {
			if($i<$this->_actual + $this->_vpage_num +1) $HIGHPART .= "<span class=\"pagelist\">".$this->pageLink($i, "start=".($i-1)*$this->_items_for_page, true, $opt)."</span>";
			else $this->_more = 1;
		}
		if($this->_more) $HIGHPART .= "<span class=\"pagelistdots\">...</span>";
		
		$BUFFER .= _("Pag. &nbsp;");
		$BUFFER .= ($this->_actual == $this->_first)? "":"<span class=\"pagelistarrow\">".$this->pageLink($this->_ico_less, "start=".($this->_actual-2)*$this->_items_for_page, true, $opt)."</span>";
		$class_first = ($this->_actual == $this->_first)? "pagelist_selected" : "pagelist";
		$link_first = ($this->_actual == $this->_first)? false:true;
		$BUFFER .= "<span class=\"$class_first\">".$this->pageLink($this->_first, "start=0", $link_first, $opt)."</span>";
		$BUFFER .= $LOWPART.$HIGHPART;
		$class_last = ($this->_actual == $this->_last)? "pagelist_selected" : "pagelist";
		$link_last = ($this->_actual == $this->_last)? false:true; 
		$BUFFER .= "<span class=\"$class_last\">".$this->pageLink($this->_last, "start=".($this->_last-1)*$this->_items_for_page, $link_last, $opt)."</span>";
		$BUFFER .= ($this->_actual == $this->_last)? "":"<span class=\"pagelistarrow\">".$this->pageLink($this->_ico_more, "start=".($this->_actual*$this->_items_for_page), true, $opt)."</span>";
		
		$BUFFER .= "</div>\n";
		
		return $BUFFER;
	}
}


/*
EXAMPLE

1) query

	$queryTotElements = "SELECT id FROM tbl_doc WHERE ...";
	$this->_list = new PageList($doc_for_page, $queryTotElements);
	
	$query = "SELECT * FROM tbl_doc WHERE ...
	ORDER BY date DESC LIMIT ".$this->_list->start().", ".$this->_list->rangeNumber."";
	$a = $this->_db->selectquery($query);
	if(sizeof($a) > 0)
	{
		foreach($a AS $b) { ... }
		
		$GINO .= $this->_list->listReferenceGINO(evt[".$this->_className."-viewList]&amp;id=$id&amp;...");
	}

2) array

	$query = "SELECT id FROM tbl_category WHERE ...";
	$list_item = $this->_trd->listItemOrdered($query, 'id', 'tbl_category_text', 'asc');	// asc | desc
	
	if(sizeof($list_item) > 0)
	{
		$this->_list = new PageList($doc_for_page, $list_item, 'array');
		$list_item_range = array_slice($list_item, $this->_list->start(), $this->_list->rangeNumber, true);
		
		foreach($list_item_range AS $key=>$value) { ... }
		
		$GINO .= $this->_list->listReferenceGINO("evt[".$this->_className."-manageCat]");
	}
*/
?>
