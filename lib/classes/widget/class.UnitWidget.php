<?php
/**
 * @file class.UnitWidget.php
 * @brief Contiene la definizione ed implementazione della classe Gino.UnitWidget
 *
 * @copyright 2015-2019 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

/**
 * @brief Insieme di campi di un modello
 *
 * @copyright 2015-2019 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class UnitWidget extends Widget {

	/**
	 * @see Gino.Widget::inputValue()
	 */
	public function inputValue($value, $options=array()) {
	
		return $value;
	}
	
	/**
	 * @see Gino.Widget::printInputForm()
	 * 
	 * @param array $options array associativo di opzioni
	 *   - @b m2m_model (object)
	 *   - @b inputs (array)
	 *   - @b remove_fields (array)
	 */
	public function printInputForm($options) {
		
		parent::printInputForm($options);
		
		$m2m_model = \Gino\gOpt('m2m_model', $options, null);
		$inputs = \Gino\gOpt('inputs', $options, array());
		$remove_fields = \Gino\gOpt('remove_fields', $options, array());
		
		$buffer = "<div id=\"m2mthrough-fieldset_".$this->_name."\">";
		
		if(count($inputs))
		{
			foreach($inputs AS $input)
			{
				$buffer .= "<fieldset>";
				$buffer .= "<legend><span data-clone-ctrl=\"minus\" class=\"link fa fa-minus-circle\"></span> ".ucfirst($m2m_model->getModelLabel())."</legend>";
				$buffer .= "<div>";
				
				$mform = Loader::load('ModelForm', array($input));
				$buffer .= $mform->view(array('only_inputs' => true, 'removeFields' => $remove_fields), array());
				
				$buffer .= "</div>";
				$buffer .= "</fieldset>";
			}
		}
		
		$buffer .= "<fieldset>";
		$buffer .= "<legend><span data-clone-ctrl=\"plus\" class=\"link fa fa-plus-circle\"></span> ".ucfirst($m2m_model->getModelLabel())."</legend>";
		$buffer .= "<div class=\"hidden\" data-clone=\"1\">";
		
		$mform = Loader::load('ModelForm', array($m2m_model));
		$buffer .= $mform->view(array('only_inputs' => true), array());
		
		$buffer .= "</div>";
		$buffer .= "</fieldset>";
		$buffer .= "</div>";
		
		$buffer .= "<script>";
		$buffer .= "(function($) {";
		$buffer .= "gino.m2mthrough('m2mthrough-fieldset_".$this->_name."', '".$this->_name."')";
		$buffer .= "})(jQuery);";
		$buffer .= "</script>";
		
		return $buffer;
	}
}
