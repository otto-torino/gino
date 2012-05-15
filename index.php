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

define('SITE_ROOT', realpath(dirname(__FILE__)));

$siteroot = preg_match("#^[a-zA-Z][:\\\]+#", SITE_ROOT) ? preg_replace("#\\\#", "/", SITE_ROOT) : SITE_ROOT;
// Rispetto a Linux, in Windows $_SERVER['DOCUMENT_ROOT'] termina con '/'
$docroot = (substr($_SERVER['DOCUMENT_ROOT'], -1) == '/') ? substr_replace($_SERVER['DOCUMENT_ROOT'], '', -1) : $_SERVER['DOCUMENT_ROOT'];

define('SITE_WWW', preg_replace("#".preg_quote($docroot)."?#", "", $siteroot));

include('settings.php');
include(LIB_DIR.OS."singleton.php");
include(LIB_DIR.OS."session.php");
include(CORE);
?>