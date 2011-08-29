<?php
/*================================================================================
    Gino - a generic CMS framework
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
/*
CLASS NAME:  GESTORE TRADUZIONI
LANGUAGE:    PHP
AUTHOR:      Marco GUIDOTTI
EMAIL:       marco.guidotti@otto.to.it
VERSION:     2.0
DATE:        04/08/2004, 11/2008

VARIABLES

$lng: lingua valida in sessione
$lngDft: lingua di default (se non trovata $lng)

TABLE STRUCTURE

CREATE TABLE IF NOT EXISTS `language` (
  `label` varchar(10) NOT NULL,
  `language` varchar(50) NOT NULL DEFAULT '',
  `code` varchar(5) NOT NULL DEFAULT '',
  `main` enum('no','yes') NOT NULL DEFAULT 'no',
  `active` enum('no','yes') NOT NULL DEFAULT 'yes',
  `flag` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `language_translation` (
  `tbl_id_value` int(11) DEFAULT NULL,
  `tbl` varchar(200) DEFAULT NULL,
  `field` varchar(200) DEFAULT NULL,
  `language` varchar(5) DEFAULT NULL,
  `text` text
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
*/
class translation
{
	private $_db;
	
	private $_lng;
	private $_lngDft;
	
	private $_tbl_translation;
	
	function __construct($language, $languageDefault) {
	
		$this->_db = new DB;
		
		$this->_lng = $language;
		$this->_lngDft = $languageDefault;
		
		$this->_tbl_translation = 'language_translation';
	}
	
	public function selectTXT($table, $field, $reference, $id_name='id')
	{
		$dft_text = $this->_db->getFieldFromId($table, $field, $id_name, $reference);
		
		if($this->_lng == $this->_lngDft) return $dft_text;
		else
		{
			$query = "SELECT text FROM ".$this->_tbl_translation." WHERE tbl='$table' AND field='$field' AND tbl_id_value='$reference' AND language='".$this->_lng."'";
			$a = $this->_db->selectquery($query);
			if(sizeof($a) > 0)
			{
				foreach($a AS $b)
				{
					$text = $b['text'];
				}
				if(!empty($text)) return $text;
			}		
		}
		
		return $dft_text;	
	}
	
	public function listItemOrdered($query, $id_name, $tbl, $ord_field, $ord_type) {
		
		// get all id from query, ordered casually
		$ids = array();
		
		$a = $this->_db->selectquery($query);
		if(sizeof($a) > 0)
		{
			foreach($a AS $b)
			{
				$ids[] = $b[$id_name];
			}
		}
		
		// construct key($id) => value($sel_field) array
		$ids_field = array();
		
		foreach($ids as $id) {
			$ids_field[$id] = $this->selectTXT($tbl, $ord_field, $id, $id_name);
		}
		
		// ordering the final array
		($ord_type == 'desc') ? arsort($ids_field) : asort($ids_field);
		
		return $ids_field;
	}
}
?>
