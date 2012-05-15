<?php
/*================================================================================
    Gino 1.0 - a generic CMS framework
    Copyright (C) 2005  Otto Srl - written by Marco Guidotti

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

   For additional information: <opensource@otto.to.it>
================================================================================*/

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