<?php
/**
 * @file class.pub.php
 * @brief Contiene la classe pub
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

/**
 * @brief Metodi generali
 * 
 * Contiene metodi generali utilizzati dalle classi che estendono la classe @b AbstractEvtClass o la classe @b pub
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class pub {

  /**
   * Costruttore
   * 
   * Definisce delle proprietà utilizzate dalle classi che estendono la classe @b AbstractEvtClass o la classe @b pub
   */
  function __construct(){
    
    $this->_db = db::instance();
    $this->session = session::instance();

  }

  /**
   * Url assoluto/relativo alla ROOT del sito
   * @param bool $abs ritorna il percorso assoluto se settato a vero
   */
  public function getRootUrl($abs = true) {

    $root_url = SITE_WWW;
    if(substr($root_url, -1) !== '/') {
      $root_url .= '/';
    }
    if($abs) {
      $root_url = "http://".$_SERVER['HTTP_HOST'].$root_url;
    }

    return $root_url;
  }

  /**
   * Url relativo della pagina corrente
   */
  public function getUrl() {

    return $_SERVER['REQUEST_URI'];

  }

  /**
   * Url relativo della pagina corrente trasformato in ajax
   */
  public function getPtUrl() {

    return preg_replace("#\?evt#", "?pt", $_SERVER['REQUEST_URI']);

  }

  /**
   * Esporta i percorsi web del metodo setUrl()
   * 
   * @param string $value chiave del percorso da recuperare
   *   - @b path: proprietà @a _url_path
   *   - @b login: proprietà @a _url_path_login
   *   - @b root: proprietà @a _url_root
   * @return string
   */
  public function getUrl__($value){
    
    if($value == 'path')
      return $this->_url_path;
    elseif($value == 'login')
      return $this->_url_path_login;
    elseif($value == 'root')
      return $this->_url_root;
    else
      return null;
  }
  
  /**
   * Valore di un campo delle impostazioni di sistema
   * @param string $field nome del campo della tabella sys_conf
   * @return mixed
   * @TODO provare ad eliminarlo usando solo registry->sysconf
   */
  public static function getConf($field){
    
    $session = session::instance();
    $trd = new translation($session->lng, $session->lngDft);

    return $trd->selectTXT("sys_conf", "$field", 1);
  }
  
  /**
   * Operazione di serializzazione
   * 
   * Viene creato nella directory dei contenuti dell'istanza il file @a ser_nomeistanza.txt
   * 
   * @param string $instanceName nome dell'istanza
   * @param object $object oggetto da serializzare
   * @return void
   */
  protected function obj_serialize($instanceName, $object){
    
    $filename = $this->pathData('abs', $instanceName).$this->_os.'ser_'.$instanceName.'.txt';
    
    $file = fopen($filename, "w");
    $ser = serialize($object);
    fwrite($file, $ser);
    fclose($file);
  }
  
  /**
   * Operazione di deserializzazione
   * 
   * @param string $instanceName nome dell'istanza
   * @return void
   */
  protected function obj_unserialize($instanceName){
    
    $filename = $this->pathData('abs', $instanceName).$this->_os.'ser_'.$instanceName.'.txt';
    
    $file = fopen($filename, "r");
    $content = file_get_contents($filename);
    $object = unserialize($content);
    fclose($file);
    
    return $object;
  }
  
  /**
   * Codifica i parametri url
   * 
   * @param string $params parametri url
   * @return string
   */
  protected function encode_params($params){
    
    if(!empty($params))
    {
      $params = preg_replace('/=/', ':', $params);
      $params = preg_replace('/&/', ';;', $params);
    }
    return $params;
  }
  
  /**
   * Decodifica i parametri url
   * 
   * @param string $params parametri url
   * @return string
   */
  protected function decode_params($params){
    
    if(!empty($params))
    {
      $params = preg_replace('/:/', '=', $params);
      $params = preg_replace('/;;/', '&', $params);
    }
    return $params;
  }
  
  /**
   * Indirizzo per il redirect
   *
   * @param string $params parametri url (es. var1=1&var2=2)
   * @return array
   */
  protected function urlRedirect($params=''){
    
    // Return True
    if(!empty($this->session->url_access))
    {
      $url = '?'.$this->session->url_access;
    }
    else $url = '';
    
    if(!empty($params) AND !empty($url))
    {
      $url .= "&".$params;
    }
    elseif(!empty($params) AND empty($url))
    {
      $url .= "?".$params;
    }
    
    // Return False
    if(!empty($this->session->url_error))
    {
      $url_error = $this->session->url_error;
      
      if($url_error == 'auth') $url_error = $this->_url_path_login.'&';
      else $url_error = $this->_url_path.'?'.$url_error.'&';
    }
    else $url_error = $this->_url_path.'?';	// autenticazione dalla home page
    // End
    
    $url = "http://".$this->_url_path.$url;
    $url_error = "http://".$url_error;
    
    return array($url, $url_error);
  }
  
  /**
   * Inclusione di file Javascript relativi a singole classi
   * Se il percorso non è specificato i file devono essere inseriti nelle directory di classe (@a app)
   *
   * @param string $file nome del file
   * @param string $id valore identificativo
   * @param string $path percorso relativo del file
   * @param array  $opts opzioni
   *   array associativo di opzioni
   *   - @b onload (boolean): il file javascript viene chiamato con l'onLoad
   * @return string
   */
  public function scriptAsset($file, $id, $path='', $opts=null) {
    
    if(empty($file))
    {
      $file = $this->_class_name.'.js';
    }
    
    if(empty($path))
    {
      $file = $this->_class_www.'/'.$file;
    }
    else
    {
      if(substr($path, -1) != '/') $path .= '/';
      $file = $path.$file;
    }
    $GINO = '';

    $GINO .= "<script type=\"text/javascript\">\n";
    
    $onload = isset($opts['onload']) ? $opts['onload'] : '';
    $GINO .= "if(!\$defined($$('script[id=$id]')[0])) new Asset.javascript('$file', {id: '".$id."'".($onload ? ", 'onLoad': $onload" : "")."});";
    
    $GINO .= "</script>";

    return $GINO;
  }
  
  /**
   * Icone
   * 
   * @param string $name codice dell'icona
   *   - @b admin
   *   - @b attach
   *   - @b back
   *   - @b cart
   *   - @b check
   *   - @b close
   *   - @b config
   *   - @b content
   *   - @b duplicate
   *   - @b css
   *   - @b delete
   *   - @b detail
   *   - @b download
   *   - @b email
   *   - @b export
   *   - @b feed
   *   - @b group
   *   - @b help
   *   - @b home
   *   - @b input
   *   - @b insert
   *   - @b language
   *   - @b layout
   *   - @b link
   *   - @b list
   *   - @b minimize
   *   - @b modify
   *   - @b new
   *   - @b newpdf
   *   - @b palette
   *   - @b password
   *   - @b pdf
   *   - @b permission
   *   - @b print
   *   - @b return
   *   - @b revision
   *   - @b search
   *   - @b sort
   *   - @b view
   * @param string $text testo della proprietà @a title del tag IMG (sostituisce il testo di default)
   * @param string $tiptype col valore @a full si attiva il selettore @a icon_tooltipfull che richiama il javascript associato
   * @return string
   */
  public static function icon($name, $options = array()){

    $text = gOpt('text', $options, '');
    $tiptype = gOpt('text', $options, 'base');
    $scale = gOpt('scale', $options, '1');

    $class = ''; // @todo only fa
    
    switch ($name) {
      
      // Ordine alfabetico
      case 'admin':
        $icon = 'ico_admin.gif';
        $title = _("amministrazione");
        break;
      case 'attach':
        $icon = 'ico_attach.gif';
        $title = _("allegati");
        break;
      case 'back':
        $icon = 'ico_back.gif';
        $title = _("inizio");
        break;
      case 'cart':
        $icon = 'ico_cart.gif';
        $title = _("metti nel carrello");
        break;
      case 'check':
        $icon = 'ico_check.gif';
        $title = _("check");
        break;
      case 'code':
        $class = 'fa fa-code';
        $title = _("codice");
        break;
      case 'close':
        $icon = 'ico_close.gif';
        $title = _("chiudi");
        break;
      case 'config':
        $icon = 'ico_config.gif';
        $title = _("opzioni");
        break;
      case 'content':
        $icon = 'ico_content.gif';
        $title = _("contenuti");
        break;
      case 'copy':
        $icon = 'ico_duplicate.gif';
        $class = 'fa-copy';
        $title = _("duplica");
        break;
      case 'css':
        $icon = 'ico_CSS.gif';
        $title = _("css");
        break;
      case 'delete':
        $icon = 'ico_trash.gif';
        $class = 'fa-trash-o';
        $title = _("elimina");
        break;
      case 'detail':
        $icon = 'ico_detail.gif';
        $title = _("dettaglio");
        break;
      case 'download':
        $icon = 'ico_download.gif';
        $title = _("download");
        break;
      case 'email':
        $icon = 'ico_email.gif';
        $title = _("email");
        break;
      case 'export':
        $icon = 'ico_export.gif';
        $class = 'fa-save';
        $title = _("esporta");
        break;
      case 'feed':
        $icon = 'icoRSS_black.png';
        $title = _("feed rss");
        break;
      case 'group':
        $icon = 'ico_group.gif';
        $class = 'fa-group';
        $title = _("gruppi");
        break;
      case 'help':
        $icon = 'ico_help.gif';
        $class = 'fa-question';
        $title = _("help in linea");
        break;
      case 'home':
        $icon = 'ico_home.gif';
        $title = _("home");
        break;
      case 'input':
        $icon = 'ico_input.gif';
        $title = _("input");
        break;
      case 'insert':
        $icon = 'ico_insert.gif';
        $class = 'fa-plus-circle';
        $title = _("nuovo");
        break;
      case 'language':
        $icon = 'ico_language.gif';
        $title = _("traduzione");
        break;
      case 'layout':
        $icon = 'ico_layout.gif';
        $class = 'fa-th';
        $title = _("layout");
        break;
      case 'link':
        $icon = 'ico_link.gif';
        $title = _("link");
        break;
      case 'list':
        $icon = 'ico_list.gif';
        $title = _("elenco");
        break;
      case 'minimize':
        $icon = 'ico_minimize.gif';
        $title = _("riduci a icona");
        break;
      case 'modify':
        $icon = 'ico_modify.gif';
        $class = 'fa-edit';
        $title = _("modifica");
        break;
      case 'new':
        $icon = 'ico_new.gif';
        $title = _("novità");
        break;
      case 'newpdf':
        $icon = 'ico_newPDF.gif';
        $title = _("crea PDF");
        break;
      case 'palette':
        $icon = 'ico_palette.gif';
        $title = _("palette colori");
        break;
      case 'password':
        $icon = 'ico_password.gif';
        $class = 'fa-key';
        $title = _("password");
        break;
      case 'pdf':
        $icon = 'ico_pdf.gif';
        $title = _("pdf");
        break;
      case 'permission':
        $icon = 'ico_permission.gif';
        $class = 'fa-gears';
        $title = _("permessi");
        break;
      case 'print':
        $icon = 'ico_print.gif';
        $title = _("stampa");
        break;
      case 'return':
        $icon = 'ico_return.gif';
        $title = _("indietro");
        break;
      case 'revision':
        $icon = 'ico_revision.gif';
        $title = _("revisione");
        break;
      case 'search':
        $icon = 'ico_search.gif';
        $class = 'fa-search';
        $title = _("ricerca");
        break;
      case 'sort':
        $icon = 'ico_sort.gif';
        $class = 'fa-sort';
        $title = _("ordina");
        break;
      case 'sort-up':
        $class = 'fa-sort-up';
        $title = _("sposta in alto");
        break;
      case 'write':
        $class = 'fa-file-text-o';
        $title = _("scrivi");
        break;
      case 'view':
        $icon = 'ico_view.gif';
        $title = _("visualizza");
        break;
      default:
        $icon = '';
        $title = '';
    }
    
    $GINO = '';
    if($class) {
      if(!empty($text)) $alt_text = $text; else $alt_text = $title;
      if($scale != 1) {
        $class .= " fa-".$scale.'x';
      }
      $GINO .= "<span class=\"icon fa $class icon-tooltip".($tiptype=='full'?_("full"):"")."\" title=\"$alt_text\" ></span>";
    }
    else {
    if(!empty($icon))
    {
      if(!empty($text)) $alt_text = $text; else $alt_text = $title;
      $GINO .= "<img class=\"icon icon_tooltip".($tiptype=='full'?_("full"):"")."\" src=\"".SITE_IMG."/$icon\" title=\"$alt_text\" />";
    }
    }
    
    return $GINO;
  }
  
  /**
   * Elimina ricorsivamente i file e le directory
   *
   * @param string $dir percorso assoluto alla directory
   * @param boolean $delete_dir per eliminare o meno le directory
   * @return void
   */
  public function deleteFileDir($dir, $delete_dir=true){
  
    if(is_dir($dir))
    {
      if(substr($dir, -1) != '/') $dir .= $this->_os;	// Append slash if necessary
      
      if($dh = opendir($dir))
      {
        while(($file = readdir($dh)) !== false)
        {
          if($file == "." || $file == "..") continue;
          
          if(is_file($dir.$file)) @unlink($dir.$file);
          else $this->deleteFileDir($dir.$file, true);
        }
        
        if($delete_dir)
        {
          closedir($dh);
          @rmdir($dir);
        }
      }
    }
  }
  
  /**
   * Elimina il file indicato
   * 
   * Viene richiamato dalla classe mFile.
   *
   * @param string $path_to_file percorso assoluto al file
   * @param string $home (proprietà @a $_home)
   * @param string $redirect (class-function)
   * @param string $param_link parametri url (es. id=3&ref=12&)
   * @return boolean
   */
  public function deleteFile($path_to_file, $home, $redirect, $param_link){
    
    if(is_file($path_to_file))
    {
      if(!@unlink($path_to_file))
      {
        if(!empty($redirect)) EvtHandler::HttpCall($home, $redirect, $param_link.'error=17');
        else return false;
      }
    }
    return true;
  }
  
  /**
   * Dimensione in KB di un file
   * @param string $bytes numero di byte con virgola (,)
   * @return integer
   */
  protected function dimensionFile($bytes){
  
    $kb = (int)($bytes);
    if($kb == 0) $kb = 1;
    
    return $kb;
  }
  
  /**
   * Nome dell'estensione di un file
   *
   * @param string $filename nome del file
   * @return string
   */
  protected function extensionFile($filename){
    
    $extension = strtolower(str_replace('.','',strrchr($filename, '.')));
    // $extension = end(explode('.', $filename))
    return $extension;
  }
  
  /**
   * Controlla se l'estensione di un file è valida
   *
   * @param string $filename nome del file
   * @param array $extensions elenco dei formati di file permessi
   * @return boolean
   */
  protected function verifyExtension($filename, $extensions){
    
    $ext = $this->extensionFile($filename);
    
    if(sizeof($extensions) > 0 AND !empty($ext))
    {
      if(in_array($ext, $extensions)) return true; else return false;
    }
    else return false;
  }
  
  /**
   * Verifica la validità del supporto PNG
   * 
   * @return boolean
   */
  public function enabledPng(){
    
    if (function_exists('gd_info'))
    {
      $array = gd_info();
      return $array['PNG Support'];
    }
    else return false;
  }
  
  /**
   * Verifica la validità della classe @a ZipArchive
   * 
   * @return boolean
   */
  public function enabledZip(){
    
    if (class_exists('ZipArchive'))
      return true;
    else
      return false;
  }
  
  /**
   * Cripta la password dell'utente
   * 
   * @param string $string
   * @param string $crypt metodo di criptazione; default: proprietà @a _crypt (impostazioni di sistema) 
   * @return string
   */
  public function cryptMethod($string, $crypt){

    $method = $crypt;
    $crypt_string = $method($string);

    return $crypt_string;
  }

  /**
   * Invio email
   * 
   * @param string $to indirizzo del destinatario
   * @param string $subject oggetto del messaggio
   * @param string $object testo del messaggio
   * @param string $from indirizzo del mittente; default: proprietà _email_from
   * @param string $type
   * @return void 
   */
  protected function emailSend($to, $subject, $object, $from=''){
    
    $m_to = $to;
    $m_subject = $subject;
    $m_object = $object;
    
    if(empty($from)) $from = $this->_email_from;
    $m_from = "From: ".$from;
    
    \mail($m_to, $m_subject, $m_object, $m_from);
  }
  
  /**
   * Testo della policy di una email
   * 
   * @return string
   */
  protected function emailPolicy(){
    
    $GINO = "\n\n"._("Indirizzo web").": http://".$_SERVER['HTTP_HOST'].$this->_site_www."\n---------------------------------------------------------------\n"._("La presente email è stata inviata con procedura automatica. Si prega di non rispondere alla presente email.")."\n\n"._("Per problemi o segnalazioni potete scrivere a ").$this->_email_send;
    return $GINO;
  }
  
  /**
   * Crea un file con caratteristiche specifiche di encoding
   *
   * @param string $filename percorso assoluto al file
   * @param string $content contenuto del file
   * @param string $type tipologia di file
   *   - @b utf8
   *   - @b iso8859
   *   - @b csv: in questo caso utilizzare la funzione utf8_encode() sui valori da DB
   * @return void
   * 
   * -- Procedura di esportazione di un file
   * 
   * 1. I valori da database devono passare attraverso le funzioni utf8_encode() e enclosedField():
   * 
   * @code
   * $firstname = enclosedField(utf8_encode($b['firstname']));	//-> TESTO
   * $date = utf8_encode($b['date']);								//-> DATA
   * $number = $b['number'];										//-> NUMERO
   * @endcode
   * 
   * 2. Creare il file sul filesystem:
   * 
   * @code
   * $filename = $this->_doc_dir.'/'.$filename;
   * if(file_exists($filename)) unlink($filename);
   * $this->writeFile($filename, $output, 'csv');
   * @endcode
   * 
   * 3. Effettuare il download del file:
   * 
   * @code
   * $filename = 'export.csv';
   * header("Content-type: application/csv \r \n");
   * header("Content-Disposition: inline; filename=$filename");
   * echo $output;
   * exit();
   * @endcode
   */
  protected function writeFile($filename, $content, $type) {
    
    $dhandle = fopen($filename, "wb");
    
    if($type == 'utf8')
    {
      # Add byte order mark
      fwrite($dhandle, pack("CCC",0xef,0xbb,0xbf));
    }
    else 
    {
      if($type == 'iso8859')
      {
        # From UTF-8 to ISO-8859-1
        $content = mb_convert_encoding($content, "ISO-8859-1", "UTF-8");
      }
      elseif($type == 'csv')
      {
        # UTF-8 Unicode CSV file that opens properly in Excel
        $content = chr(255).chr(254).mb_convert_encoding( $content, 'UTF-16LE', 'UTF-8');
      }
    }
    
    fwrite($dhandle, $content);
    fclose($dhandle);
  }
  
  /**
   * Rimuove il BOM (Byte Order Mark)
   * 
   * @param string $str
   * @return string
   */
  protected function removeBOM($str=''){
    
    if(substr($str, 0,3) == pack("CCC",0xef,0xbb,0xbf)) {
      $str = substr($str, 3);
    }
    return $str;
  }
}
?>
