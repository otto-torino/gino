<?php
/**
 * @file class.phpzip.php
 * @brief Contiene la classe phpzip
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

/**
 * @brief Libreria che fornisce le interfacce per l'utilizzo dei file in formato ZIP
 * 
 * Utilizza la libreria interna ZipArchive
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 * 
 * Esempio di estrazione di un file Zip
 * @code
 * ...
 * $upload_file = $_FILES[$input_name]['name'];
 * $upload_tmp = $_FILES[$input_name]['tmp_name'];
 * 
 * $zip = new phpzip();
 * $directory = $zip->extractZip($upload_file, $upload_tmp, array('return_dir'=>true, 'extract_dir'=>CONTENT_DIR.OS.'tmp', 'link_error'=>$link_error));
 * $list_file = $zip->parseDirectory($directory);
 * 
 * if(sizeof($list_file) > 0)
 * {
 *   foreach($list_file AS $path_to_file)
 *   {
 *     $source_file = basename($path_to_file);
 *     $source_dir = dirname($path_to_file);
 *     
 *     $filecopy = $zip->moveExtractFile($path_to_file, $dest_dir, array('jpg', 'png'), array('resize'=>true, 'max_file_size'=>$this->_max_file_size, 
 *     'prefix_file'=>$this->_prefix_img, 'prefix_thumb'=>$this->_prefix_thumb, 'new_width'=>$this->_img_width, 'thumb_width'=>$this->_thumb_width));
 * ...
 * @endcode
 */
class phpzip extends pub {

	private $_default_dir;
	
	function __construct(){
		
		$this->_default_dir = TMP_DIR;
	}
	
	private function goodPathDir($directory){
		
		$directory = (substr($directory, -1) != '/' && $directory != '') ? $directory.'/' : $directory;
		return $directory;
	}
	
	/**
	 * Rimuove tutti i caratteri ad eccezione di numeri, lettere, _.-, da una stringa, sostituendoli con un underscore
	 * 
	 * @param stringa $filename nome del file
	 * @param string $prefix eventuale testo da aggiungere in testa al nome del file
	 * @return string
	 */
	private function checkFilename($filename, $prefix) {
	
		$filename = preg_replace("#[^a-zA-Z0-9_\.-]#", "_", $filename);
		return $prefix.$filename;
	}
	
	/**
	 * Genera un nome casuale per la directory temporanea di estrazione dei file
	 * 
	 * @return string
	 */
	private function randDir(){
		
		$rand = md5(microtime().rand(0,999999));
		if(empty($rand)) $rand = 'tmpzip';
		return $rand;
	}
	
	/**
	 * Operazioni sul file Zip nella procedura di upload
	 * 
	 * Procedura:
	 *   - upload
	 *   - creazione di una directory dedicata
	 *   - apertura del file
	 *   - estrazione dei contenuti
	 * 
	 * @param string $upload_file nome del file (ad es. $_FILES["file"]["name"])
	 * @param string $upoad_tmp nome del file temporaneo (ad es. $_FILES["file"]["tmp_name"])
	 * @param array $options
	 *   array associativo di opzioni
	 *   - @b extract_dir (string): directory di estrazione dei file (default: @a _default_dir)
	 *   - @b return_dir (boolean): se vero, il metodo ritorna la directory di estrazione dei file (default: @a false)
	 *   - @b link_error (string): indirizzo da richiamare in caso di errore (default: @a _home)
	 * @return void or string
	 */
	public function extractZip($upload_file, $upoad_tmp, $options=array()){

		$extract_dir = array_key_exists('extract_dir', $options) ? $options['extract_dir'] : $this->_default_dir;
		$return_dir = array_key_exists('return_dir', $options) ? $options['return_dir'] : false;
		$link_error = array_key_exists('link_error', $options) ? $options['link_error'] : $this->_home;
		
		$extract_dir = $this->goodPathDir($extract_dir).$this->randDir();
		$file_zip = $extract_dir.'/'.$upload_file;
		
		if(!is_dir($extract_dir)) mkdir($extract_dir);
		
		if(!move_uploaded_file($upoad_tmp, $file_zip))
			exit(error::errorMessage(array('error'=>16), $link_error));
		
		$zip = new ZipArchive;
		$res = $zip->open($file_zip);
		if ($res === true)
		{
         	$zip->extractTo($extract_dir);
         	$zip->close();
     	}
     	else
     	{
			$this->deleteFileDir($extract_dir, true);
			exit(error::errorMessage(array('error'=>_("impossibile scompattare il pacchetto")), $link_error));
     	}
     	
     	if($return_dir)
     	{
     		@unlink($file_zip);
     		return $extract_dir;
     	}
	}
	
	/**
	 * Crea un file zip da una un elenco di file
	 * 
	 * @param array $list_file elenco di file col loro percorso assoluto
	 * @param array $options
	 *   array associativo di opzioni
	 *   - @b zip_dir (string): dove vengono create le directory temporanee contenenti i file zip
	 *   - @b suffix_dir (string): suffisso della directory temporanea
	 *   - @b prefix_file (string): prefisso del file zip
	 *   - @b name_file (string): nome file zip
	 *   - @b strip_path (boolean): se bisogna salvare i file senza la directory radice
	 * @return void
	 */
	public function createZip($list_file=array(), $options=array()){
		
		$zip_dir = array_key_exists('zip_dir', $options) ? $options['zip_dir'] : $this->_default_dir;
		$suffix_tmp_dir = array_key_exists('suffix_dir', $options) ? '_'.$options['suffix_dir'] : '';
		$prefix_file = array_key_exists('prefix_file', $options) ? $options['prefix_file'] : '';
		$name_file = array_key_exists('name_file', $options) ? $options['name_file'] : 'doc';
		$strip_path = array_key_exists('strip_path', $options) ? $options['strip_path'] : true;
		
		if(sizeof($list_file) == 0) exit();
		
		$zip = new ZipArchive();
		
		$directoryToZip = $this->goodPathDir($zip_dir).$this->randDir().$suffix_tmp_dir;
		
		if(mkdir($directoryToZip))
		{
			$zipName = $prefix_file.$name_file.".zip";
			$zipName = $directoryToZip.'/'.$zipName;
			
			if($zip->open($zipName, ZIPARCHIVE::CREATE) === TRUE) {
				
				foreach($file_list AS $path_to_file)
				{
					$path_file = dirname($path_to_file);
					
					if($strip_path)
					{
						$filename = preg_replace("#$path_file#", "", $path_to_file);
						// add the file $path_to_file (/path/to/file) as $filename (file) 
						$zip->addFile($path_to_file, $filename);
					}
					else
						$zip->addFile($path_to_file);
				}
				$zip->close();
			}
			else 
			{
				$this->deleteFileDir($directoryToZip, true);
				exit('download fallito');
			}
			
			download($zipName);
			$this->deleteFileDir($directoryToZip, true);
			exit();

		}
		exit();
	}
	
	/**
	 * Elenco dei file presenti in una directory
	 * 
	 * @param string $rootPath percorso della directory principale
	 * @param string $separator separatore delle directory
	 * @return array
	 */
	public function parseDirectory($rootPath, $separator='/'){
		
		$fileArray=array();
		$handle = opendir($rootPath);
		while(($file = @readdir($handle)) !== false) {
			if($file !='.' && $file !='..' && $file != '__MACOSX')
			{
				if(is_dir($rootPath.$separator.$file)){
					$array = $this->parseDirectory($rootPath.$separator.$file);
					$fileArray = array_merge($array, $fileArray);
				}
				else {
					$fileArray[] = $rootPath.$separator.$file;
				}
			}
		}
		return $fileArray;
	}
	
	/**
	 * Copia un file in una data directory
	 * 
	 * @see Form::saveImage()
	 * @param string $path_to_file percorso assoluto del file da copiare
	 * @param string $directory directory nella quale copiare i file
	 * @param array $valid_extension elenco delle estensioni valide
	 * @param array $options
	 *   array associativo di opzioni
	 *   - @b max_file_size (integer): dimensione massima del file da copiare (default: @a _max_file_size)
	 *   - @b verify_name (boolean): verifica se il nome del file è presente nella directory (non viene creato un nuovo record) (default: true)
	 *   - @b resize (boolean): ridimensionamento dell'immagine, nel caso di immagine jpg/png (default: false)
	 *   - @b prefix_file (string): [resize true] eventuale testo che precede il nome del file
	 *   - @b prefix_thumb (string): [resize true] eventuale testo che precede il nome del thumbnail
	 *   - @b new_width (integer): [resize true] se vuoto non ridimensiona
	 *   - @b new_height (integer): [resize true] se vuoto non ridimensiona
	 *   - @b thumb_width (integer): [resize true] se vuoto non ridimensiona
	 *   - @b thumb_height (integer): [resize true] se vuoto non ridimensiona
	 * @return null or string (percorso completo al file)
	 */
	public function moveExtractFile($path_to_file, $directory, $valid_extension, $options=array()){

		$max_file_size = array_key_exists('max_file_size', $options) ? $options['max_file_size'] : $this->_max_file_size;
		$verify_name = array_key_exists('verify_name', $options) ? $options['verify_name'] : true;
		$resize = array_key_exists('resize', $options) ? $options['resize'] : false;
		$prefix_file = array_key_exists('prefix_file', $options) ? $options['prefix_file'] : '';
		$prefix_thumb = array_key_exists('prefix_thumb', $options) ? $options['prefix_thumb'] : '';
		$new_width = array_key_exists('new_width', $options) ? $options['new_width'] : 0;
		$new_height = array_key_exists('new_height', $options) ? $options['new_height'] : 0;
		$thumb_width = array_key_exists('thumb_width', $options) ? $options['thumb_width'] : 0;
		$thumb_height = array_key_exists('thumb_height', $options) ? $options['thumb_height'] : 0;
		
		$resize_ext = array('jpg', 'jpeg', 'png');
		
		$filename = basename($path_to_file);
		$filename_size = filesize($path_to_file);
		$ext = $this->extensionFile($path_to_file);
		
		if(in_array($ext, $resize_ext) AND $resize)
			$prefix = $prefix_file;
		else
			$prefix = '';
		
		$filename = $this->checkFilename($filename, $prefix);
		
		// Controlli
		if(is_int($filename_size) > is_int($max_file_size)) {
			return null;
		}
		
		if(!in_array($ext, $valid_extension)) {
			return null;
		}
		
		if($verify_name) {
			if(is_dir($directory)) {
				if($dh = opendir($directory)) {
					while (($file = readdir($dh)) !== false) {
						if($file == $filename)
							return null;
					}
					closedir($dh);
				}
			}
			else return null;
		}
		// End
		
		$copy = copy($path_to_file, $directory.$filename);
		
		if($copy AND in_array($ext, $resize_ext) AND $resize)
		{
			if(!empty($prefix_thumb))
			{
				if(preg_match("/^($prefix_thumb).+$/", $filename, $matches))
				$filename = substr_replace($filename, '', 0, strlen($prefix_thumb));
			}
			
			if(!empty($prefix_file))
			{
				if(preg_match("/^($prefix_file).+$/", $filename, $matches))
				$filename = substr_replace($filename, '', 0, strlen($prefix_file));
			}
			$form = new Form('', '', '');
			$copy = $form->saveImage($filename, $directory, $prefix_file, $prefix_thumb, $new_width, $new_height, $thumb_width, $thumb_height);
		}
		
		if($copy) return $directory.$filename;
		else return null;
	}
}
?>