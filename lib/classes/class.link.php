<?php
class Link {
	
	private $_permalinks;
	private $_compressed_form, $_field_id;
	
	function __construct(){
		
		$permalinks = pub::variable('permalinks');
		$this->_permalinks = $permalinks == 'yes' ? true : false;
		
		$this->_compressed_form = true;	// non mostra il nome del campo ID ma direttamente il valore: page/displayItem/3
		$this->_field_id = 'id';		// nome della chiave del campo ID
	}
	
	/**
	 * Costruisce un collegamento parziale o completo
	 * 
	 * @param string		$class		nome della classe/istanza
	 * @param string		$method		nome del metodo
	 * @param string|array	$params1	parametri principali
	 * 									string: il separatore è '&' (es. id=4&ctg=2)
	 * 									array: key=>value (array('id'=>4, 'ctg'=>2))
	 * @param string|array	$params2	parametri secondari
	 * 									string: il separatore è '&' (es. order=desc&start=20)
	 * 									array: key=>value (array('order'=>'desc', 'start'=>20))
	 * @param array			$options
	 * 		boolean all 		link completo (http://...)
	 * 		string code 		tipo di evento (di default 'evt')
	 * 		boolean basename 	mostra il nome del file php (index.php)
	 * @return string
	 * 
	 * @example
	 * Richiamare il metodo listReferenceGINO della classe pagelist:
	 * $this->_list->($this->_plink->aLink($this->_instanceName, 'viewList', $ctg_par, $order_par, array('basename'=>false)));
	 */
	public function aLink($class, $method, $params1=null, $params2=null, $options=array()){
		
		$all = array_key_exists('all', $options) ? $options['all'] : false;
		$code = array_key_exists('code', $options) ? $options['code'] : EVT_NAME;
		$basename = array_key_exists('basename', $options) ? $options['basename'] : true;
		
		if($this->_permalinks)
		{
			$link = "{$class}/{$method}";
			
			if($params1 != null)
				$link .= $this->opLinkToPerm($params1);
			
			if($params2 != null)
				$link .= $this->opLinkToPerm($params2, true);
		}
		else
		{
			$link = $basename ? "index.php?" : '';
			
			$link .= "{$code}[{$class}-{$method}]";
			
			if($params1 != null)
			{
				if(is_string($params1) && $params1 != '')
				{
					$params1 = $this->conformParams($params1);
					$link .= "&$params1";
				}
				elseif(is_array($params1) && sizeof($params1) > 0)
				{
					$string_params1 = '';
					$i = 1;
					$end = sizeof($params1);
					foreach($params1 AS $key=>$value)
					{
						$string_params1 .= "$key=$value";
						if($i < $end) $string_params1 .= '&';
						$i++;
					}
					$link .= "&$string_params1";
				}
			}
			if($params2 != null)
			{
				if(is_string($params2) && $params2 != '')
				{
					$params2 = $this->conformParams($params2);
					$link .= "&$params2";
				}
				elseif(is_array($params2) && sizeof($params2) > 0)
				{
					$string_params2 = '';
					$i = 1;
					$end = sizeof($params2);
					foreach($params2 AS $key=>$value)
					{
						$string_params2 .= "$key=$value";
						if($i < $end) $string_params2 .= '&';
						$i++;
					}
					$link .= "&$string_params2";
				}
			}
		}
		
		return $link;
	}
	
	/**
	 * Per aggiungere dei parametri all'indirizzo, dopo averlo già impostato col metodo aLink()
	 * (utilizzato nella classe pagelist per il parametro 'start')
	 * 
	 * @param string	$link
	 * @param string	$params		(es. start=2)
	 * @param boolean	$secondary
	 * @return string
	 */
	public function addParams($link, $params, $secondary=true){
		
		$params = $this->conformParams($params);
		
		if($params != '')
		{
			if($this->_permalinks)
			{
				if($secondary)
				{
					if(preg_match("#^(.*)\?([a-zA-Z0-9+/.=]+)$#", $link, $matches))
					{
						$p_secondary = base64_decode($matches[2]);
						$p_secondary = $p_secondary.'&'.$params;
						$p_secondary = base64_encode($p_secondary);
						
						$link = preg_replace("#^(.*)\?([a-zA-Z0-9+/.=]+)$#", "$1?$p_secondary", $link);
					}
					else
					{
						$last = substr($link, -2);
						if($last != '/?')
						{
							if($last[1] == '?')
							{
								$link = substr($link, 0, -1);
								$link = $link.'/?';
							}
							elseif($last[1] == '/')
								$link = $link.'?';
							else
								$link = $link.'/?';
						}
						
						$link .= base64_encode($params);
					}
				}
				else
				{
					$tmp_link = '';
					$array = explode('&', $params);
					if(sizeof($array) > 0)
					{
						foreach($array AS $item)
						{
							$a_item = explode("=", $item);
							$tmp_link .= '/'.$a_item[0].'/'.$a_item[1];
						}
					}
					
					if(preg_match("#^(.*)\?([a-zA-Z0-9+/.=]+)$#", $link, $matches))
					{
						$tmp_link = $this->conformParams($tmp_link, '/');
						$link = preg_replace("#^(.*)\?([a-zA-Z0-9+/.=]+)$#", "$1$tmp_link/?$2", $link);
					}
					else $link .= $tmp_link;
				}
			}
			else
			{
				$link = $link.'&'.$params;
			}
		}
		return $link;
	}
	
	/**
	 * Presenta un collegamento prendendo l'indirizzo da un campo del DB (classe menu)
	 * 
	 * @param string $link		esempi: page/displayItem/8, page/displayItem/id/8, index.php?evt[page-displayItem]&id=6
	 * @return string
	 */
	public static function linkFromDB($link){
		
		$url = SITE_WWW.'/'.$link;
		return $url;
	}
	
	/**
	 * Imposta le condizioni di ricerca di un indirizzo nelle voci di menu
	 * 
	 * @param string $link
	 * @return string
	 */
	public function alternativeLink($link){
		
		$where = "link LIKE '%$0%'";
		$string = '';
		
		$array = array();
		$array[] = preg_replace("#^.+$#", $where, $link);
		$items = explode('/', $link);
		
		if(sizeof($items) == 3 && $this->_compressed_form)
		{
			$item = preg_replace("#^.+$#", $where, $items[0].'/'.$items[1].'/'.$this->_field_id.'/'.$items[2]);
			$array[] = $item;
		}
		if(sizeof($array) > 1)
		{
			$string .= '(';
			$string .= implode(' OR ', $array);
			$string .= ')';
		}
		elseif(sizeof($array) == 1)
			$string .= $array[0];
		
		return $string;
	}
	
	/**
	 * Converte un indirizzo a/da un permalink
	 * 
	 * @param string $params	valori da URL (es. $_SERVER['REQUEST_URI'])
	 * @param array $options
	 * 		string vserver			variabile del server web alla quale fare riferimento (utilizzando QUERY_STRING viene scartato il valore codificato base64)
	 * 		boolean pToLink			conversione dal formato permalink a quello di gino (di default è il contrario)
	 * 		boolean basename		opzione per il metodo permalinkToLink; se vero antepone il basename al link (vedi class.skin.php)
	 * 		boolean setServerVar	reimposta le variabili del server indicate nel metodo setServerVar(). Operazione effettuata dalla classe document
	 * @return string
	 * 
	 * @example: evt[page-displayItem]&id=5 <-> page/displayItem/5, page/displayItem/id/5
	 */
	public function convertLink($params, $options=array()){
		
		$pToLink = array_key_exists('pToLink', $options) ? $options['pToLink']: false;
		$vserver = array_key_exists('vserver', $options) ? $options['vserver']: '';
		$basename = array_key_exists('basename', $options) ? $options['basename'] : false;
		$setServerVar = array_key_exists('setServerVar', $options) ? $options['setServerVar'] : false;
		
		if($vserver != '')
		{
			if($vserver == 'QUERY_STRING')
			{
				$query_string = $params;
			}
			elseif($vserver == 'REQUEST_URI')
			{
				$search = preg_quote(SITE_WWW.OS);
				$query_string =  preg_replace("#^$search#", "", $params);
			}
		}
		else
		{
			$query_string = $params;
		}
		
		if($pToLink || !$this->_permalinks)
		{
			$link = $this->permalinkToLink($query_string, $basename);
			
			if($setServerVar)
				$this->setServerVar($link);
		}
		elseif($this->_permalinks)
		{
			$link = $this->linkToPermalink($query_string);
		}
		
		return $link;
	}
	
	private function linkToPermalink($query_string){
		
		$link = $query_string;
		
		if(preg_match("#^.*".EVT_NAME."\[(.+)-(.+)\](.*)#is", $query_string, $matches))
		{
			$link = $matches[1].'/'.$matches[2];
			$link .= $this->opLinkToPerm($matches[3]);
		}
		
		return $link;
	}
	
	private function permalinkToLink($query_string, $basename){
		
		$basename = $basename ? 'index.php?' : '';
		
		$link = $query_string;
		$array = explode('/', $query_string);
		
		if(sizeof($array) == 2)
		{
			$link = $basename.EVT_NAME."[$array[0]-$array[1]]";
			$_GET[EVT_NAME] = array("$array[0]-$array[1]"=>1);	// $_GET['evt'][classe-metodo] = 1
			$_REQUEST[EVT_NAME] = array("$array[0]-$array[1]"=>1);
		}
		elseif(sizeof($array) > 2)
		{
			$link = $basename.EVT_NAME."[$array[0]-$array[1]]";
			$key = true;
			$string_get = '';
			for($i=2, $end=sizeof($array); $i<$end; $i++)
			{
				if(preg_match("#^\?([a-zA-Z0-9+/.=]+)$#", $array[$i], $matches))	// parametri secondari
				{
					$params = base64_decode($matches[1]);
					$link .= "&".$params;
					$string_get .= "&".$params;
				}
				elseif($end == 3 && $this->_compressed_form)	// page/displayItem/3
				{
					$link .= "&{$this->_field_id}=".$array[$i];
					$string_get .= "&{$this->_field_id}=".$array[$i];
				}
				else
				{
					if($key)
					{
						$link .= "&".$array[$i]."=";
						$key = false;
						$string_get .= "&".$array[$i]."=";
					}
					else
					{
						$link .= $array[$i];
						$key = true;
						$string_get .= $array[$i];
					}
				}
			}
			
			// Ridefinizione delle variabili GET / REQUEST
			$_GET[EVT_NAME] = array("$array[0]-$array[1]"=>1);	// $_GET['evt'][classe-metodo] = 1
			$_REQUEST[EVT_NAME] = array("$array[0]-$array[1]"=>1);
			
			if($string_get != '')
				$string_get = substr($string_get, 1);
			
			$a_string_get = explode('&', $string_get);
			
			if(sizeof($a_string_get) > 0)
			{
				foreach($a_string_get AS $value)
				{
					if($value != '')
					{
						$a_value = explode('=', $value);
						$_GET[$a_value[0]] = $a_value[1];
						$_REQUEST[$a_value[0]] = $a_value[1];
					}
				}
			}
		}
		
		return $link;
	}
	
	private function setServerVar($query_string){
		
		$_SERVER['QUERY_STRING'] = $query_string;
	}
	
	private function opLinkToPerm($params, $secondary=false){
		
		$link = '';
		
		if($secondary)
		{
			if(is_string($params) && $params != '')
			{
				$params = $this->conformParams($params);
				$link .= "/?";
				$link .= base64_encode($params);
			}
			elseif(is_array($params) && sizeof($params) > 0)
			{
				$string_params = '';
				$i = 1;
				$end = sizeof($params);
				foreach($params AS $key=>$value)
				{
					$string_params .= "$key=$value";
					if($i < $end) $string_params .= '&';
					$i++;
				}
				$link .= "/?";
				$link .= base64_encode($string_params);
			}
			return $link;
		}
		
		if(is_string($params) && $params != '')
		{
			$params = $this->conformParams($params);
			$array = explode("&", $params);
			if(sizeof($array) > 0)
			{
				foreach($array AS $item)
				{
					$a_item = explode('=', $item);
					
					if(sizeof($array) == 1 && $a_item[0] == $this->_field_id && $this->_compressed_form)
						$link .= '/'.$a_item[1];
					else
						$link .= '/'.$a_item[0].'/'.$a_item[1];
				}
			}
		}
		elseif(is_array($params) && sizeof($params) > 0)
		{
			foreach($params AS $key=>$value)
			{
				if(sizeof($params) == 1 && $key == $this->_field_id && $this->_compressed_form)
					$link .= '/'.$value;
				else
					$link .= '/'.$key.'/'.$value;
			}
		}
		
		return $link;
	}
	
	private function conformParams($string, $char='&'){
		
		$control = substr($string, 0, 1);
		$string = $control == $char ? substr($string, 1) : $string;
		return $string;
	}
}
?>
