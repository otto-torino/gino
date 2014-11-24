<?php
/**
 * @file class.link.php
 * @brief Contiene la classe Link
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

/**
 * @brief Libreria che si occupa di convertire gli indirizzi nel formato permalink e viceversa
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class Link {
	
	private $_permalinks;
	
	/**
	 * Nell'indirizzo non mostra il nome del campo ID ma direttamente il valore, ad esempio page/displayItem/3
	 * 
	 * @var boolean (default @a true)
	 */
	private $_compressed_form;
	
	/**
	 * Nome della chiave del campo ID
	 * 
	 * @var string (default @a id)
	 */
	private $_field_id;
	
	function __construct(){
		
		$db = db::instance();
		
		$this->_permalinks = true;
		
		$this->_compressed_form = true;
		$this->_field_id = 'id';
	}
	
	/**
	 * Costruisce un collegamento parziale o completo
	 * 
	 * @see opLinkToPerm()
	 * @see conformParams()
	 * @param string $class nome della classe/istanza
	 * @param string $method nome del metodo
	 * @param mixed $params1 parametri principali in formato stringa o array
	 *   - string: il separatore è '&' (es. id=4&ctg=2)
	 *   - array: i parametri sono nel formato chiave=>valore, es. 
	 *   @code
	 *   $this->_plink->aLink('page', 'view', array('id'=>4, 'ctg'=>2);
	 *   @endcode
	 * @param mixed	$params2 parametri secondari in formato stringa o array (nel formato chiave=>valore); i parametri secondari vengono sempre encodati
	 *   - string: il separatore è '&' (es. order=desc&start=20)
	 *   - array: es. array('order'=>'desc', 'start'=>20)
	 * @param array $options
	 *   array associativo di opzioni
	 *   - @b absolute (boolean): link completo (http://...)
	 *   - @b code (string): tipo di evento (di default 'evt')
	 *   - @b basename (boolean): mostra il nome del file php (index.php)
	 *   - @b permalink (boolean): utilizza i permalink, default come impostato da proprietà di classe
	 * @return string
	 * 
	 * Come esempio, per richiamare il metodo listReferenceGINO() della classe pagelist: 
	 * @code
	 * $this->_list->listReferenceGINO($this->_plink->aLink($this->_instanceName, 'viewList', $ctg_par, $order_par, array('basename'=>false)));
	 * @endcode
	 */
	public function aLink($class, $method, $params1=null, $params2=null, $options=array()){
		
		$absolute = array_key_exists('absolute', $options) ? $options['absolute'] : false;
		$code = array_key_exists('code', $options) ? $options['code'] : 'evt';
		$basename = array_key_exists('basename', $options) ? $options['basename'] : true;
		$permalink = array_key_exists('permalink', $options) ? $options['permalink'] : $this->_permalinks;
		
		if($permalink)
		{
			$link = "{$class}/{$method}";
			
			if($params1 != null)
			{
				$param_sec_exist = $params2 != null ? true : false;
				$link .= $this->opLinkToPerm($params1, array('param_sec_exist'=>$param_sec_exist));
			}
			
			if($params2 != null)
				$link .= $this->opLinkToPerm($params2, array('param_sec'=>true));
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

        if($absolute) {
            $registry = registry::instance();
            $url_root = $registry->pub->getRootUrl();
            return $url_root.$link;
        }
        else {
		    return $link;
        }
	}
	
	/**
	 * Per aggiungere dei parametri all'indirizzo, dopo averlo già impostato col metodo aLink()
	 * 
	 * Metodo utilizzato nella classe pagelist per il parametro @a start
	 * 
	 * @param string $link indirizzo
	 * @param string $params parametri da aggiungere (ad es. start=2)
	 * @param boolean $secondary parametri secondari
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
	 * Presenta un collegamento prendendo l'indirizzo da un campo del database
	 * 
	 * Metodo utilizzato nella classe menu
	 * 
	 * @param string $link indirizzo
	 * @return string
	 * 
	 * Esempi di valori del parametro link
	 * @code
	 * page/displayItem/8
	 * page/displayItem/id/8
	 * index.php?evt[page-displayItem]&id=6
	 * @endcode
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

		if($link == '') {
			return "url='/'";
		}
		
		$where = "url LIKE '%$0%'";
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
	 * @param string $params valori da URL (es. $_SERVER['REQUEST_URI'])
	 * @param array $options
	 *   array associativo di opzioni
	 *   - @b vserver (string): variabile del server web alla quale fare riferimento
	 *     - QUERY_STRING, utilizzando QUERY_STRING viene scartato il valore codificato base64
	 *     - REQUEST_URI
	 *   - @b pToLink (boolean): conversione dal formato permalink a quello di gino (di default è il contrario)
	 *   - @b basename (boolean): opzione per il metodo permalinkToLink(); se vero antepone il basename al link (vedi class.skin.php)
	 *   - @b boolean setServerVar (boolean): reimposta le variabili del server indicate nel metodo setServerVar(). Operazione richiesta dalla classe document
	 *   - @b boolean SetDataVar (boolean): reimposta le variabili GET e REQUEST. Operazione richiesta dalla classe document
	 * @return string
	 * 
	 * @code
	 * evt[page-displayItem]&id=5 <-> page/displayItem/5, page/displayItem/id/5
	 * @endcode
	 */
	public function convertLink($params, $options=array()){
		
		$pToLink = array_key_exists('pToLink', $options) ? $options['pToLink']: false;
		$vserver = array_key_exists('vserver', $options) ? $options['vserver']: '';
		$basename = array_key_exists('basename', $options) ? $options['basename'] : false;
		$setServerVar = array_key_exists('setServerVar', $options) ? $options['setServerVar'] : false;
		$setDataVar = array_key_exists('setDataVar', $options) ? $options['setDataVar'] : false;
		
		if($vserver != '')
		{
			if($vserver == 'QUERY_STRING')
			{
				$query_string = $params;
			}
			elseif($vserver == 'REQUEST_URI')
			{
				$search = preg_quote(SITE_WWW.'/');
				$query_string =  preg_replace("#^$search#", "", $params);
			}
		}
		else
		{
			$query_string = $params;
		}
		
		if($pToLink || !$this->_permalinks)
		{
			$link = $this->permalinkToLink($query_string, $basename, $setDataVar);
			
			if($setServerVar)
				$this->setServerVar($link);
		}
		elseif($this->_permalinks)
		{
			$link = $this->linkToPermalink($query_string);
		}
		
		return $link;
	}
	
	/**
	 * Conversione di un indirizzo dal formato dell'applicazione al formato permalink
	 * 
	 * @param string $query_string valori da URL ricavati dalla variabile del server web indicata dal metodo convertLink()
	 * @return string
	 */
	private function linkToPermalink($query_string){
		
		$link = $query_string;
		
		if(preg_match("#^.*".'evt'."\[(.+)-(.+)\](.*)#is", $query_string, $matches))
		{
			$link = $matches[1].'/'.$matches[2];
			$link .= $this->opLinkToPerm($matches[3]);
		}
		
		return $link;
	}
	
	/**
	 * Conversione di un indirizzo dal formato permalink al formato dell'applicazione
	 * 
	 * @param string $query_string valori da URL ricavati dalla variabile del server web indicata dal metodo convertLink()
	 * @param boolean $basename se vero antepone il basename al link (vedi class.skin.php)
	 * @param boolean $setDataVar reimposta le variabili del server indicate nel metodo setServerVar(). Operazione richiesta dalla classe document
	 * @return string
	 */
	private function permalinkToLink($query_string, $basename, $setDataVar=false){
		
		$basename = $basename ? 'index.php?' : '';
		
		$link = $query_string;
		$array = explode('/', $query_string);
		if($array[count($array) - 1] == '') {
			unset($array[count($array) - 1]);
		}

		$secondary_params = '';
		if(count($array) && substr($array[count($array) - 1], 0, 1) == '?') {
			$secondary_params = array_pop($array);
		}

		$array_size = count($array);

		if($array_size >= 2) {
			$link = $basename.EVT_NAME."[$array[0]-$array[1]]";
			$string_get = '';
		
			if($secondary_params)	// parametri secondari
			{
				$params = base64_decode(substr($secondary_params, 1));
				$link .= "&".$params;
				$string_get .= "&".$params;
			}

			$key = true;
			for($i=2, $end=$array_size; $i<$end; $i++)
			{
				if($end == 3 && $this->_compressed_form)	// page/displayItem/3
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
			if($setDataVar)
			{
				$_GET[EVT_NAME] = array("$array[0]-$array[1]"=>1);	// $_GET['evt'][classe-metodo] = 1
				$_REQUEST[EVT_NAME] = array("$array[0]-$array[1]"=>1);
			}
			
			if($string_get != '')
				$string_get = substr($string_get, 1);
			
			$a_string_get = explode('&', $string_get);
			
			if(sizeof($a_string_get) > 0)
			{
				foreach($a_string_get AS $value)
				{
					if($value != '' && $setDataVar)
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
	
	/**
	 * Costruisce un collegamento in formato permalink
	 * 
	 * @param mixed $params parametri principali o secondari in formato stringa o array (nel formato chiave=>valore); vedere le descrizioni presenti nel metodo aLink()
	 * @param array $options
	 *   array associativo di opzioni
	 *   - @b param_sec (boolean): indica se il metodo viene richiamato con parametri secondari
	 *   - @b param_sec_exist (boolean): indica se nel link sono presenti parametri secondari
	 * @return string
	 */
	private function opLinkToPerm($params, $options=array()){
		
		$secondary = array_key_exists('param_sec', $options) ? $options['param_sec']: false;
		$secondary_exist = array_key_exists('param_sec_exist', $options) ? $options['param_sec_exist']: false;
		
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
					
					if(sizeof($array) == 1 && $a_item[0] == $this->_field_id && $this->_compressed_form && !$secondary_exist)
						$link .= '/'.$a_item[1];
					elseif(!array_key_exists(1, $a_item))
						$link .= '/'.$a_item[0].'/';
					else
						$link .= '/'.$a_item[0].'/'.$a_item[1];
				}
			}
		}
		elseif(is_array($params) && sizeof($params) > 0)
		{
			foreach($params AS $key=>$value)
			{
				if(sizeof($params) == 1 && $key == $this->_field_id && $this->_compressed_form && !$secondary_exist)
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

	/**
	 * Costruisce l'indirizzo di un redirect e lo effettua
	 * @param string $file indirizzo del redirect, corrispondente al nome del file base dell'applicazione (ad es. la proprietà @a $_home)
	 * @param string $EVT parte dell'indirizzo formata da nome istanza/classe e nome metodo (nel formato @a nomeistanza-nomemetodo)
	 * @param string $params parametri aggiuntivi della request (ad es. var1=val1&var2=val2)
	 * @return void
	 */
	public static function HttpCall($file, $EVT, $params){

		if(!empty($params))
		{
			if(!empty($EVT)) $sign = '&'; else $sign = '?';

			$params = $sign.$params;
		}

		if(!empty($EVT)) $event = "?evt[$EVT]";
		else $event = '';

		header("Location:http://".$_SERVER['HTTP_HOST'].$file.$event.$params);
		exit();
	}

	public function redirect($class, $method, $params1=null, $params2=null, $options=array()) {
		
		header("Location: ".$this->alink($class, $method, $params1, $params2, $options));
		exit();
	}

}
?>
