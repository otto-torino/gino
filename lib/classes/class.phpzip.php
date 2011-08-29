<?php
/*
 * Es. Multimedia: extractFileZip()
 * 
 * extractZip() -> upload + crea una directory in cui scompattare il file zip + open zip + extract zip
 * parseDirectory() -> elenco file presenti in una directory
 * moveExtractFile() -> copia del file nella directory di destinazione
 * 
 * 
 	private function extractFileZip($id, $input_name, $options=array()){
		
		$name = array_key_exists('name', $options) ? $options['name'] : '';
		$max_size = array_key_exists('max_size', $options) ? $options['max_size'] : $this->_max_file_size;
		$link_error = array_key_exists('link_error', $options) ? $options['link_error'] : '';
		
		$valid_extensions = array_merge($this->_valid_image, $this->_valid_audio, $this->_valid_video);
		
		$upload_file = $_FILES[$input_name]['name'];
		$upload_tmp = $_FILES[$input_name]['tmp_name'];
		$upload_size = $_FILES[$input_name]['size'];
		
		if($upload_size > $max_size)
			exit(error::errorMessage(array('error'=>33), $link_error));
		
		$zip = new phpzip();
		$directory = $zip->extractZip($upload_file, $upload_tmp, array('return_dir'=>true, 'extract_dir'=>CONTENT_DIR.OS.'tmp', 'link_error'=>$link_error));
		$list_file = $zip->parseDirectory($directory);
		
		$not_copied = array();
		
		if(sizeof($list_file) > 0)
		{
			$ctg_dir = $this->pathBaseDir('abs').$this->_db->getFieldFromId(multimediaCtg::$_tbl_ctg, 'directory', 'id', $id).$this->_os;
			
			foreach($list_file AS $path_to_file)
			{
				$source_file = basename($path_to_file);
				$source_dir = dirname($path_to_file);
				
				$filecopy = $zip->moveExtractFile($path_to_file, $ctg_dir, $valid_extensions, array('resize'=>true, 'max_file_size'=>$this->_max_file_size, 
				'prefix_file'=>$this->_prefix_img, 'prefix_thumb'=>$this->_prefix_thumb, 'new_width'=>$this->_img_width, 'thumb_width'=>$this->_thumb_width));
				
				if($filecopy)
				{
					$filename = basename($filecopy);
					$field_name = empty($name) ? $filename : $name;
					
					$type = $this->identifyType($filename);
					
					$date = date("Y-m-d");
					$query_media = "INSERT INTO ".multimediaItem::$_tbl_doc." (instance, type, ctg, name, date, image) VALUES ('".$this->_instance."', '$type', $id, '$field_name', '$date', '$filename')";
					$result = $this->_db->actionquery($query_media);
					
					if(!$result)
						@unlink($filecopy);
				}
				else
				{
					$not_copied[] = $source_file;
				}
			}
		}
		
		// eliminazione directory temporanea
		if(is_dir($directory))
			$this->deleteFileDir($directory, true);
		
		return $not_copied;
	}
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
	
	private function checkFilename($filename, $prefix) {
	
		$filename = preg_replace("#[^a-zA-Z0-9_\.-]#", "_", $filename);
		return $prefix.$filename;
	}
	
	private function randDir(){
		
		$rand = md5(microtime().rand(0,999999));
		if(empty($rand)) $rand = 'tmpzip';
		return $rand;
	}
	
	/**
	 * Operazioni sul file Zip: upload, creazione directory dedicata, apertura, estrazione 
	 * 
	 * @param string $upload_file		$_FILES["file"]["name"]
	 * @param string $upoad_tmp			$_FILES["file"]["tmp_name"]
	 * @param array $options
	 * 
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
	 * Create file zip from list
	 * 
	 * @param array $list_file		file list (absolute path)
	 * @param array $options
	 * 
	 * zip_dir			dove vengono create le directory temporanee contenenti i file zip
	 * suffix_dir		suffisso della directory temporanea
	 * prefix_file		prefisso del file zip
	 * name_file		nome file zip
	 * strip_path		se bisogna salvare i file senza la directory radice
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
	 * Elenco file presenti in una directory
	 * 
	 * @param string $rootPath
	 * @param string $separator
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
	
	/*
	//Function to Zip entire directory with all its files and subdirectories
	public function zipDirectory($dirName, $outputDir) {
		
		if (!is_dir($dirName)){
			trigger_error("CreateZipFile FATAL ERROR: Could not locate the specified directory $dirName", E_USER_ERROR);
		}
		
		$tmp = $this->parseDirectory($dirName);
		$count=count($tmp);
		//$this->addDirectory($outputDir);
		
		for ($i=0;$i<$count;$i++)
		{
			$fileToZip = trim($tmp[$i]);
			$newOutputDir = substr($fileToZip, 0, (strrpos($fileToZip,'/')+1));
			$outputDir = $outputDir.$newOutputDir;
			$fileContents = file_get_contents($fileToZip);
			//$this->addFile($fileContents,$fileToZip);
		}
	}
	*/
	
	/**
	 * Copia dei file nella directory definitiva
	 * 
	 * @param string $path_to_file		percorso assoluto del file da copiare
	 * @param string $directory			dove copiare
	 * @param array $valid_extension
	 * @param array $options
	 * 
	 * Opzioni:
	 * --------------
	 * max_file_size
	 * verify_name		verifica se il nome del file è presente nella directory (non viene creato un nuovo record)
	 * resize			ridimensionamento (se si tratta di una immagine jpg/png)
	 * 
	 * nel caso di resize:
	 * prefix_file
	 * prefix_thumb
	 * new_width		se vuoto non ridimensiona
	 * new_height		se vuoto non ridimensiona
	 * thumb_width		se vuoto non ridimensiona
	 * thumb_height		se vuoto non ridimensiona
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