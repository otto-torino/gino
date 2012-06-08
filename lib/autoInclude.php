<?php
/**
 * @file autoInclude.php
 * @brief Include automaticamente (se richiesti) i file dei moduli presenti nella directory @a app
 *
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

/**
 * Include i file dei moduli presenti nella directory @a app
 * @see settings.php
 * @param string $class nome della classe
 */
function __autoload($class)
{
   	if(is_dir(APP_DIR.OS.$class))
   	{
   		$dir = APP_DIR.OS.$class.OS;
   		
   		include_once($dir.CLASS_PREFIX.$class.'.php');
   		
   		if (!class_exists($class, false))
		trigger_error("Unable to load class: $class", E_USER_WARNING);
   	}
}

include_class();

/**
 * Include i file php presenti nella directory @a include
 * @see settings.php
 * @param string $dir percorso della directory
 */
function include_class($dir='')
{
   	if(empty($dir)) $dir = INCLUDE_DIR.OS;
   	
	if(is_dir($dir))
	{
   		if($dh = opendir($dir))
		{
			while(($file = readdir($dh)) !== false)
			{
				if($file == "." || $file == "..") continue;
				
				if(is_file($dir.$file))
				{
					$extension = strtolower(str_replace('.','',strrchr($file, '.')));
					
					if($extension == 'php') include_once($dir.$file);
				}
			}
		}
   	}
}
?>