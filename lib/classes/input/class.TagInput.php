<?php
/**
 * @file class.TagInput.php
 * @brief Contiene la definizione ed implementazione della classe Gino.TagInput
 *
 * @copyright 2016-2018 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

/**
 * @brief Input form di tipo Tag
 *
 * @copyright 2016-2018 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class TagInput extends Input {

	/**
	 * @brief Input che mette a disposizione dell'utente la visualizzazione dei tag
	 *
	 * @param string $name nome
	 * @param string $value valore
	 * @param string $label label
	 * @param array $options opzioni
	 * @return string
	 */
	public static function input($name, $value, $label, $options=array()) {
		 
		// moocomplete
		$registry = registry::instance();
		$registry->addJs(SITE_JS.'/MooComplete.js');
		$registry->addCss(CSS_WWW.'/MooComplete.css');
		
		// all tags
		$tags = GTag::getAllTags();
		$js_tags_list = "['".implode("','", jsVar($tags))."']";
		
		$text_add = "<span class=\"fa fa-cloud link\" onclick=\"var win = new gino.layerWindow({overlay: false, title: '".jsVar(_('Tag cloud'))."', html: '".jsVar(TagBuild::tagCloud())."'}); win.display();\"></span>";
		
		$tag_options = array('id' => $name, 'text_add' => $text_add);
		$opt = is_array($options) && count($options) ? array_merge($options, $tag_options): $tag_options;
		
		$field = Input::input_label($name, 'text', $value, $label, $opt);
		
		$field .= "<script>";
		// moocomplete script
		$field .= "window.addEvent('load', function() {
        	var tag_input = new MooComplete('".$name."', {
	    		list: $js_tags_list, // elements to use to suggest.
	    		mode: 'tag', // suggestion mode (tag | text)
	    		size: 8 // number of elements to suggest
			});
		});\n";
		// clound functionality
		$field .= "var addTag = function(el) {
            var tag = el.get('text');
            var field = $('".$name."');
            if(field.value.substr(field.value.length - 1) == ',' || field.value == '') {
                field.value = field.value + tag;
            }
            else {
                field.value = field.value + ',' + tag;
            }
        }";
		$field .= "</script>";
		
		return $field;
	}
}
