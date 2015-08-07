<?php
/**
 * @file class.UnitWidget.php
 * @brief Contiene la definizione ed implementazione della classe Gino.UnitWidget
 *
 * @copyright 2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

/**
 * @brief Insieme di campi di un modello
 *
 * @copyright 2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class UnitWidget extends Widget {

	/**
	 * @see Gino.Widget::printInputForm()
	 */
	public function printInputForm($form, $options) {
		
		parent::printInputForm($form, $options);
		
		$controller = \Gino\gOpt('controller', $options, null);
		$m2m_model = \Gino\gOpt('m2m_model', $options, null);
		$inputs = \Gino\gOpt('inputs', $options, array());
		$remove_fields = \Gino\gOpt('remove_fields', $options, array());
		
		$admin_table = Loader::load('AdminTable', array($controller, array()));
		
		$buffer = "<div id=\"m2mthrough-fieldset_".$this->_name."\">";
		
		if(count($inputs))
		{
			foreach($inputs AS $input)
			{
				$buffer .= "<fieldset>";
				$buffer .= "<legend><span data-clone-ctrl=\"minus\" class=\"link fa fa-minus-circle\"></span> ".ucfirst($m2m_model->getModelLabel())."</legend>";
				$buffer .= "<div>";
				$buffer .= $admin_table->modelForm($input, array('only_inputs' => true, 'removeFields' => $remove_fields), array());
				$buffer .= "</div>";
				$buffer .= "</fieldset>";
			}
		}
		
		$buffer .= "<fieldset>";
		$buffer .= "<legend><span data-clone-ctrl=\"plus\" class=\"link fa fa-plus-circle\"></span> ".ucfirst($m2m_model->getModelLabel())."</legend>";
		$buffer .= "<div class=\"hidden\" data-clone=\"1\">";
		
		$buffer .= $admin_table->modelForm($m2m_model, array('only_inputs' => true), array());
		$buffer .= "</div>";
		$buffer .= "</fieldset>";
		$buffer .= "</div>";
		
		$buffer .= "<script>";
		$buffer .= "gino.m2mthrough('m2mthrough-fieldset_".$this->_name."', '".$this->_name."')";
		$buffer .= "</script>";
		
		return $buffer;
	}
}
