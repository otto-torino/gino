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
class sysfunc extends AbstractEvtClass{

	function __construct(){

		parent::__construct();

	}
	
	/*
	 * Funzioni che possono essere richiamate da menu e messe all'interno del template;
	 * array ("function" => array("label"=>"description", "role"=>"privileges"))
	 */
	public static function outputFunctions() {

		$list = array(
			"Autenticazione" => array("label"=>_("Boxino di login"), "role"=>'1'),
			"tableLogin" => array("label"=>_("Boxino di login a tabella"), "role"=>'1'),
			"credits" => array("label"=>_("Credits"), "role"=>'1')
		);

		return $list;
	}

	public function Autenticazione($bool=false, $classname='index'){

		$GINO = "<div class=\"auth\">\n";
		$GINO .= "<div class=\"auth_title\">"._("login:")."</div>";
		$GINO .= "<div class=\"auth_content\">"; 
		$GINO .= $this->_access->AccessForm();
		
		$registration = new account('', $classname);
		$GINO .= $registration->linkRegistration($bool);
		
		$GINO .= "</div>\n";
		$GINO .= "</div>\n";
		
		return $GINO;
	}
	
	public function tableLogin($bool=false, $classname='index'){

		$GINO = "<form action=\"\" method=\"post\" id=\"formauth\" name=\"formauth\">\n";
		$GINO .= "<input type=\"hidden\" id=\"action\" name=\"action\" value=\"auth\" />\n";
		$GINO .= "<table>";
		$GINO .= "<tr class=\"authTitle\">";
		$GINO .= "<td></td><td>"._("Area riservata")."</td>";
		$GINO .= "</tr>";
		$GINO .= "<tr class=\"authForm\">";
		$GINO .= "<td class=\"afLabel\">".($this->_u_username_email?"email":"user")."</td>";
		$GINO .= "<td class=\"afField\"><input type=\"text\" id=\"user\" name=\"user\" size=\"25\" maxlength=\"50\" class=\"auth\" /></td>";
		$GINO .= "</tr>";
		$GINO .= "<tr class=\"authForm\">";
		$GINO .= "<td class=\"afLabel\">"._("password")."</td>";
		$GINO .= "<td class=\"afField\"><input type=\"password\" name=\"pwd\" size=\"25\" maxlength=\"15\" class=\"auth\" /></td>";
		$GINO .= "</tr>";
		$GINO .= "<tr class=\"authForm\">";
		$GINO .= "<td class=\"afLabel\"></td>";
		$GINO .= "<td class=\"afField\"><input type=\"submit\" class=\"generic\" name=\"login_user\" value=\""._("login")."\" /></td>";
		$GINO .= "</tr>";
		if($this->_u_aut_registration OR $bool) {
			$class = $classname=='index' ? 'user':$classname;
			$GINO .= "<tr class=\"authRegTitle\">";
			$GINO .= "<td></td><td>"._("Registrazione")."</td>";
			$GINO .= "</tr>";
			$GINO .= "<tr class=\"authRegForm\">";
			$GINO .= "<td class=\"arfLabel\"></td>";
			$GINO .= "<td class=\"arfField\"><input onclick=\"location.href='".$this->_home."?evt[$class-registration]'\" type=\"button\" class=\"generic\" name=\"login_user\" value=\""._("sign up")."\" /></td>";
			$GINO .= "</tr>";
		}
		$GINO .= "</table>";
		$GINO .= "</form>";
		
		return $GINO;
	}

	public function credits() {
	
		$credits = "<a class=\"otto\" href=\"http://www.otto.to.it\" target=\"_blank\">&#160;</a>";
		$credits .= "<div class=\"null\"></div>";

		return $credits;


	}

	/*
	 * Funzioni utilizzate da classi molteplici, metodo alternativo al wrapper
	 */

	public static function manageCss($mdl, $class) {

		$db = new db;

		if($mdl) {
			$query = "SELECT name, label FROM ".TBL_MODULE." WHERE id='$mdl'";
			$a = $db->selectquery($query);
			if(sizeof($a)>0) {
				$name = $a[0]['name']; 
				$label = $a[0]['label']; 
			}
		}
		else {
			$name = $class;
			$label = $db->getFieldFromId(TBL_MODULE_APP, 'label', 'name', $class);
		}

		$css = new css('module', array("class"=>$class, "module"=>$mdl, "name"=>$name, "label"=>$label));

		$GINO = $css->manageModuleCss();

		return $GINO;

	}

	public static function manageOptions($mdl, $class) {
	
		$options = new options($class, $mdl);

		return $options->manageDoc();
	
	}
	
	public static function manageEmail($mdl, $class) {
	
		$email = new email($class, $mdl);

		return $email->manageDoc();
	
	}
	
	public static function managePermissions($mdl, $class) {
	
		$admin = new admin($class, $mdl);

		return $admin->manageDoc();
	
	}


}
?>
