<?php
/**
 * @file class.pagelist.php
 * @brief Contiene la classe PageList
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

/**
 * @brief Gestisce la paginazione dei contenuti
 * 
 * Una delle prime classi di gino, risale al 04/08/2004
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 * 
 * ###Esempio 1 (Query)
 * @code
 * $queryTotElements = "SELECT id FROM tbl_doc WHERE ...";
 * $this->_list = new PageList($doc_for_page, $queryTotElements);
 * $query = "SELECT * FROM tbl_doc WHERE ... ORDER BY date DESC LIMIT ".$this->_list->start().", ".$this->_list->rangeNumber."";
 * $a = $this->_db->selectquery($query);
 * if(sizeof($a) > 0) {
 *   foreach($a AS $b) { ... }
 *   $GINO .= $this->_list->listReferenceGINO(evt[".$this->_className."-viewList]&id=$id&...");
 * }
 * @endcode
 * 
 * ###Esempio 2 (Array)
 * @code
 * $tot_items = eventItem::getTotItems($this->_instance, $options);
 * $list = new PageList($this->_doc_for_page, $tot_items, 'array');
 * $items = eventItem::getOrderedItems($this->_instance, $options);
 * $end = $list->start()+$list->rangeNumber > count($items) ? count($items) : $list->start()+$list->rangeNumber;
 * $htmlList = new htmlList(array("numItems"=>($end-$list->start()), "separator"=>true));
 * $GINO .= $htmlList->start();
 * foreach($items as $item) { ... }
 * $GINO .= $htmlList->end();
 * $GINO .= $list->listReferenceGINO("pt[$this->_instanceName-ajaxAdminItems]", true, implode('&', $postvar), "list_$this->_instance", "list_$this->_instance", true, 'updateTooltips');
 * @endcode
 */
class PageList{

	/**
	 * Numero di elementi per pagina
	 */
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
	
	// Numero di pagine adiacenti a quella corrente visualizzate come link (escluse la prima e l'ultima)
	private $_vpage_num;
	
	private $_filename, $_url, $_variables, $_symbol;

	private $_ico_less, $_ico_more;

	// parametri chiamate ajax
	private $_ajax, $_postvar, $_ref_id, $_load_id, $_script, $_callback, $_cbparams;
	
	/**
	 * Costruttore
	 * 
	 * @param integer $items_for_page numero di elementi per pagina
	 * @param mixed $queryTot
	 *   - @a string:
	 *       - query che ricava il numero totale di elementi (ad es. SELECT field FROM table WHERE condition)
	 *       - numero degli elementi
	 *   - @a array:
	 *       - elenco dei riferimenti degli elementi (elementi object), ad esempio: eventItem::getTotItems($instance, $options);
	 * @param string $type tipo del parametro queryTot (@a array)
	 * @param array $options
	 *   array asspciativo di opzioni
	 *   - @b permalink_primary (boolean): indica se il parametro @a start deve essere gestito come parametro primario con i permalink
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

		$this->_ico_more = "&raquo;";
		$this->_ico_less = "&laquo;";

	}
	
	/**
	 * Recupera il valore dello start
	 * @return integer
	 */
	public function start() 
	{
		$start = cleanVar($_REQUEST, 'start', 'int', '');
		
		return $start>0?$start:0;
	}
	
	/**
	 * Stampa il resoconto della pagina
	 * @return string
	 */
	public function reassumedPrint()
	{
		$printTBL = '';

		if($this->_tot > 0)
		{
			$end = $this->_start+$this->_items_for_page - 1;
			if($end > $this->_tot) $end = $this->_tot;
			$printTBL .= $this->_start.' - '.$end.' '._("di").' '.$this->_tot."\n";
		}
		
		return "<div class=\"pagination-summary\">".$printTBL."</div>";
	}

	private function pageLink($label, $params, $link=true, $opt=null) {

        $registry = registry::instance();

		if(gOpt('add_no_permalink', $opt, false)) {
			if($params != '') {
				$this->_variables = preg_replace("#&?start=[^&]*#", "", $this->_variables);
				$url = preg_match("#\?#", $this->_variables) ? $this->_variables.'&'.$params : $this->_variables.'?'.$params;
			}	
			return "<a href=\"$url\">".$label."</a>";	// OLD: href=\"".$this->_url.$this->_symbol."$params\"
		}
		elseif(!$this->_ajax && $link)
		{
			if($params != '')
			{
				$plink = new Link();
				$secondary = $this->_permalink_primary ? false : true;
				$url = $plink->addParams($this->_variables, $params, $secondary);
				//if(!$registry->sysconf->permalinks)
				//	$url = $this->_filename."?".$url;
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

	/**
	 * Stampa i collegamenti con le pagine precedenti e successive
	 * 
	 * @param string $variables parametro dell'indirizzo indicante lo script da richiamare per le pagine precedenti/seguenti (es. pt[$this->_instanceName-ajaxAdminItems])
	 * @param boolean $ajax indica se il collegamento tra le pagine avviene attraverso una request ajax
	 * @param mixed $postvar variabili da aggiungere all'indirizzo (es. p1=var1&p2=var2)
	 * @param string $ref_id dove caricare lo script ajax
	 * @param string $load_id parametro per request ajax
	 * @param boolean $script parametro per request ajax
	 * @param string $callback parametro per request ajax
	 * @param string $cbparams parametro per request ajax
	 * @param array $opt
	 *   array associativo di opzioni (parametri per request ajax)
	 *   - @b cache
	 *   - @b add_no_permalink (boolean)
	 * @return string
	 */
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
		
		$BUFFER = "<ul class=\"pagination\">\n";
		
		if($this->_last == 1 || $this->_last == 0) return "";
				
		for($i=$this->_actual; $i>1; $i--) {
			if($i == $this->_last) $LOWPART .= "";
			elseif($i == $this->_actual) $LOWPART = "<li class=\"active\">".$this->pageLink($i, "start=".($i-1)*$this->_items_for_page, true, $opt)."</li>".$LOWPART;
			elseif($i>$this->_actual - $this->_vpage_num - 1) $LOWPART = "<li>".$this->pageLink($i, "start=".($i-1)*$this->_items_for_page, true, $opt)."</li>".$LOWPART;
			else $this->_less = 1;
		}
		if($this->_less) $LOWPART = "<li class=\"pagelistdots\">...</li>".$LOWPART;
		
		for($i=$this->_actual+1; $i<$this->_last; $i++) {
			if($i<$this->_actual + $this->_vpage_num +1) $HIGHPART .= "<li>".$this->pageLink($i, "start=".($i-1)*$this->_items_for_page, true, $opt)."</li>";
			else $this->_more = 1;
		}
		if($this->_more) $HIGHPART .= "<li class=\"pagelistdots\">...</li>";
		
		$BUFFER .= ($this->_actual == $this->_first)? "":"<li>".$this->pageLink($this->_ico_less, "start=".($this->_actual-2)*$this->_items_for_page, true, $opt)."</li>";
		$class_first = ($this->_actual == $this->_first)? "active" : "";
		$BUFFER .= "<li class=\"$class_first\">".$this->pageLink($this->_first, "start=0", true, $opt)."</li>";
		$BUFFER .= $LOWPART.$HIGHPART;
		$class_last = ($this->_actual == $this->_last)? "active" : "";
		$BUFFER .= "<li class=\"$class_last\">".$this->pageLink($this->_last, "start=".($this->_last-1)*$this->_items_for_page, true, $opt)."</li>";
		$BUFFER .= ($this->_actual == $this->_last)? "":"<li>".$this->pageLink($this->_ico_more, "start=".($this->_actual*$this->_items_for_page), true, $opt)."</li>";
		
		$BUFFER .= "</ul>\n";
		
		return $BUFFER;
	}
}
?>
