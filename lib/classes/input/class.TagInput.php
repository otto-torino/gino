<?php
/**
 * @file class.TagInput.php
 * @brief Contiene la definizione ed implementazione della classe Gino.TagInput
 *
 * @copyright 2016-2019 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

/**
 * @brief Input form di tipo Tag
 *
 * @copyright 2016-2019 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
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
	 * @param array $options opzioni array associativo di opzioni di Gino.Input::input_label()
	 * @return string
	 */
	public static function input($name, $value, $label, $options=array()) {
		
	    $form_inline = gOpt('form_inline', $options, false);
	    
		// moocomplete
		$registry = registry::instance();
		$registry->addJs(SITE_JS.'/jQComplete.js');
		//$registry->addJs(SITE_JS.'/MooComplete.js');
		$registry->addCss(CSS_WWW.'/MooComplete.css');
		
		// all tags
		$tags = GTag::getAllTags();
		$js_tags_list = "['".implode("','", jsVar($tags))."']";
		
		$link_layer = "<span class=\"fa fa-cloud link\" 
onclick=\"
var win = new gino.layerWindow({overlay: false, title: '".jsVar(_('Tag cloud'))."', html: '".jsVar(TagBuild::tagCloud())."'}); 
win.display();\"></span>";
		
		$tag_options = ['id' => $name];
		if(!$form_inline) {
		    $tag_options['text_add'] = $link_layer;
		}
		
		$opt = is_array($options) && count($options) ? array_merge($options, $tag_options): $tag_options;
		
		$field = Input::input_label($name, 'text', $value, $label, $opt);
		
		$field .= "<script>";
		// moocomplete script
		//$field .= "window.addEvent('load', function() {
        $field .= "$(function () {";
        $field .= "var tag_input = MooComplete('".$name."', {
	    		list: $js_tags_list, // elements to use to suggest.
	    		mode: 'tag', // suggestion mode (tag | text)
	    		size: 8 // number of elements to suggest
			});
		});\n";
		// clound functionality
		$field .= "var addTag = function(el) {
            var tag = $(el).text();
            var field = $('#".$name."');
            if(field.val().substr(field.val().length - 1) == ',' || field.val() == '') {
                field.val(field.val() + tag);
            }
            else {
                field.val(field.val() + ',' + tag);
            }
        }";
		$field .= "</script>";
		
		if($form_inline) {
		    $field .= "<div class=\"link-tag-cloud\">".$link_layer."</div>";
		}
		
		return $field;
	}
}
